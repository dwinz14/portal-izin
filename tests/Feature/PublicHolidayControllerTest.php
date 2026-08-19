<?php

namespace Tests\Feature;

use App\Exports\HolidayTemplateExport;
use App\Models\PublicHoliday;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Tests\TestCase;

class PublicHolidayControllerTest extends TestCase
{
    use RefreshDatabase;

    private function hrdUser(): User
    {
        return User::factory()->create(['role' => 'hrd']);
    }

    public function test_hrd_can_store_a_holiday(): void
    {
        $response = $this->actingAs($this->hrdUser())->post('/hrd/holidays', [
            'date' => '2026-08-17',
            'name' => 'Hari Kemerdekaan Republik Indonesia',
            'type' => 'national_holiday',
        ]);

        $response->assertRedirect(route('hrd.holidays.index', ['year' => 2026]));

        $this->assertDatabaseHas('public_holidays', [
            'date' => '2026-08-17',
            'year' => 2026,
            'type' => 'national_holiday',
        ]);
    }

    public function test_import_parses_pasted_lines_and_upserts(): void
    {
        $this->actingAs($this->hrdUser())->post('/hrd/holidays/import', [
            'lines' => implode("\n", [
                "2026-01-01\tTahun Baru 2026 Masehi\tnasional",
                '2026-03-20 | Cuti Bersama Idul Fitri 1447 H | cuti_bersama',
                '2026-03-21 | Hari Raya Idul Fitri 1447 H',
                '2026-12-25 | Hari Raya Natal | national_holiday',
            ]),
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('public_holidays', ['date' => '2026-01-01', 'type' => 'national_holiday']);
        $this->assertDatabaseHas('public_holidays', ['date' => '2026-03-20', 'type' => 'joint_leave']);
        $this->assertDatabaseHas('public_holidays', ['date' => '2026-03-21', 'type' => 'national_holiday']);
        $this->assertDatabaseHas('public_holidays', ['date' => '2026-12-25', 'type' => 'national_holiday']);
    }

    public function test_import_reports_invalid_lines(): void
    {
        $this->actingAs($this->hrdUser())->post('/hrd/holidays/import', [
            'lines' => "bukan-tanggal | Test\n2026-01-01 | Tahun Baru | nasional\n2026-01-02 | X | tipe-aneh",
        ])->assertSessionHas('error');

        $this->assertDatabaseHas('public_holidays', ['date' => '2026-01-01']);
        $this->assertDatabaseMissing('public_holidays', ['date' => '2026-01-02']);
    }

    public function test_toggle_activates_draft_from_sync(): void
    {
        $draft = PublicHoliday::create([
            'date' => '2026-01-01',
            'name' => 'Tahun Baru 2026 Masehi',
            'type' => 'national_holiday',
            'is_active' => false,
        ]);

        $this->actingAs($this->hrdUser())
            ->patch("/hrd/holidays/{$draft->id}/toggle")
            ->assertSessionHas('success');

        $this->assertTrue($draft->fresh()->is_active);
    }

    public function test_duplicate_date_rejected_on_store(): void
    {
        PublicHoliday::create([
            'date' => '2026-01-01',
            'name' => 'Tahun Baru 2026 Masehi',
            'type' => 'national_holiday',
        ]);

        $this->actingAs($this->hrdUser())
            ->from('/hrd/holidays/create')
            ->post('/hrd/holidays', [
                'date' => '2026-01-01',
                'name' => 'Duplikat',
                'type' => 'national_holiday',
            ])
            ->assertSessionHasErrors('date');

        $this->assertSame(1, PublicHoliday::where('date', '2026-01-01')->count());
    }

    public function test_hrd_can_download_excel_template(): void
    {
        Excel::fake();

        $this->actingAs($this->hrdUser())
            ->get(route('hrd.holidays.import.template', ['year' => 2026]))
            ->assertOk();

        Excel::assertDownloaded('template-hari-libur-2026.xlsx');
    }

    public function test_api_preview_shows_fetched_holidays_without_importing(): void
    {
        Http::fake([
            'api-hari-libur.vercel.app/*' => Http::response([
                'status' => 'success',
                'code' => 200,
                'data' => [
                    ['date' => '2026-01-01', 'description' => 'Tahun Baru 2026 Masehi'],
                    ['date' => '2026-03-20', 'description' => 'Cuti Bersama Hari Raya Idul Fitri 1447 H'],
                ],
                'message' => 'Holidays Found',
            ]),
        ]);

        $this->actingAs($this->hrdUser())
            ->post(route('hrd.holidays.import.apiPreview'), ['year' => 2026])
            ->assertOk()
            ->assertSee('Tahun Baru 2026 Masehi')
            ->assertSee('Cuti Bersama Hari Raya Idul Fitri 1447 H')
            ->assertSee('Cuti Bersama');

        $this->assertDatabaseCount('public_holidays', 0);
    }

    public function test_api_import_confirm_persists_new_and_skips_existing(): void
    {
        PublicHoliday::create([
            'date' => '2026-03-20',
            'name' => 'Cuti Bersama Hari Raya Idul Fitri 1447 H',
            'type' => 'joint_leave',
        ]);

        Http::fake([
            'api-hari-libur.vercel.app/*' => Http::response([
                'status' => 'success',
                'code' => 200,
                'data' => [
                    ['date' => '2026-01-01', 'description' => 'Tahun Baru 2026 Masehi'],
                    ['date' => '2026-03-20', 'description' => 'Cuti Bersama Hari Raya Idul Fitri 1447 H'],
                    ['date' => 'invalid', 'description' => 'Baris Rusak'],
                ],
                'message' => 'Holidays Found',
            ]),
        ]);

        $this->actingAs($this->hrdUser())
            ->post(route('hrd.holidays.import.apiPreview'), ['year' => 2026])
            ->assertOk();

        $this->post(route('hrd.holidays.import.confirm'))
            ->assertRedirect(route('hrd.holidays.index', ['year' => 2026]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('public_holidays', [
            'date' => '2026-01-01',
            'name' => 'Tahun Baru 2026 Masehi',
            'type' => 'national_holiday',
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('public_holidays', [
            'date' => '2026-03-20',
            'name' => 'Cuti Bersama Hari Raya Idul Fitri 1447 H',
            'type' => 'joint_leave',
        ]);

        $this->assertDatabaseMissing('public_holidays', ['date' => 'invalid']);
        $this->assertSame(2, PublicHoliday::count());
    }

    public function test_api_preview_handles_connection_error(): void
    {
        Http::fake([
            'api-hari-libur.vercel.app/*' => fn () => throw new ConnectionException('Connection timed out'),
        ]);

        $this->actingAs($this->hrdUser())
            ->from(route('hrd.holidays.index'))
            ->post(route('hrd.holidays.import.apiPreview'), ['year' => 2026])
            ->assertRedirect(route('hrd.holidays.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('public_holidays', 0);
    }

    public function test_api_preview_handles_empty_data(): void
    {
        Http::fake([
            'api-hari-libur.vercel.app/*' => Http::response([
                'status' => 'success',
                'code' => 200,
                'data' => [],
                'message' => 'No holidays',
            ]),
        ]);

        $this->actingAs($this->hrdUser())
            ->from(route('hrd.holidays.index'))
            ->post(route('hrd.holidays.import.apiPreview'), ['year' => 2026])
            ->assertRedirect(route('hrd.holidays.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('public_holidays', 0);
    }

    public function test_excel_preview_parses_uploaded_csv(): void
    {
        $csv = "Tanggal,Nama Hari Libur,Tipe\n"
            ."2026-05-01,Hari Buruh Internasional,nasional\n"
            ."2026-05-15,Cuti Bersama Kenaikan Yesus Kristus,cuti_bersama\n";

        $file = UploadedFile::fake()->createWithContent('skb-2026.csv', $csv);

        $this->actingAs($this->hrdUser())
            ->post(route('hrd.holidays.import.excelPreview'), ['file' => $file])
            ->assertOk()
            ->assertSee('Hari Buruh Internasional')
            ->assertSee('Cuti Bersama Kenaikan Yesus Kristus');

        $this->assertDatabaseCount('public_holidays', 0);
    }

    public function test_importing_the_downloaded_template_succeeds(): void
    {
        $raw = Excel::raw(new HolidayTemplateExport(2026), \Maatwebsite\Excel\Excel::XLSX);
        $file = UploadedFile::fake()->createWithContent('template-hari-libur-2026.xlsx', $raw);

        $this->actingAs($this->hrdUser())
            ->post(route('hrd.holidays.import.excelPreview'), ['file' => $file])
            ->assertOk()
            ->assertSee('Tahun Baru 2026 Masehi')
            ->assertSee('Cuti Bersama Hari Raya Idul Fitri 2026')
            ->assertDontSee('Format file tidak dikenali');

        $this->assertDatabaseCount('public_holidays', 0);
    }

    public function test_excel_preview_accepts_file_without_header_row(): void
    {
        $csv = "2026-05-01,Hari Buruh Internasional,nasional\n"
            ."2026-05-15,Cuti Bersama Kenaikan Yesus Kristus,cuti_bersama\n";

        $file = UploadedFile::fake()->createWithContent('skb-2026.csv', $csv);

        $this->actingAs($this->hrdUser())
            ->post(route('hrd.holidays.import.excelPreview'), ['file' => $file])
            ->assertOk()
            ->assertSee('Hari Buruh Internasional')
            ->assertSee('Cuti Bersama Kenaikan Yesus Kristus');

        $this->assertDatabaseCount('public_holidays', 0);
    }

    public function test_excel_preview_accepts_title_row_above_headers(): void
    {
        $csv = "SKB 3 Menteri Hari Libur Nasional 2026\n"
            ."Tanggal,Nama Hari Libur,Tipe\n"
            ."2026-08-17,Hari Kemerdekaan Republik Indonesia,nasional\n";

        $file = UploadedFile::fake()->createWithContent('skb-2026.csv', $csv);

        $this->actingAs($this->hrdUser())
            ->post(route('hrd.holidays.import.excelPreview'), ['file' => $file])
            ->assertOk()
            ->assertSee('Hari Kemerdekaan Republik Indonesia')
            ->assertDontSee('Format file tidak dikenali');

        $this->assertDatabaseCount('public_holidays', 0);
    }

    public function test_excel_import_confirm_persists_valid_rows(): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['Tanggal', 'Nama Hari Libur', 'Tipe'],
            ['2026-06-01', 'Hari Lahir Pancasila', 'nasional'],
            ['2026-06-16', 'Tahun Baru Islam 1448 Hijriyah', ''],
            ['bukan-tanggal', 'Baris Rusak', 'nasional'],
        ]);

        $writer = new XlsxWriter($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $xlsx = ob_get_clean();

        $file = UploadedFile::fake()->createWithContent('skb-2026.xlsx', $xlsx);

        $this->actingAs($this->hrdUser())
            ->post(route('hrd.holidays.import.excelPreview'), ['file' => $file])
            ->assertOk()
            ->assertSee('Hari Lahir Pancasila');

        $this->post(route('hrd.holidays.import.confirm'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('public_holidays', [
            'date' => '2026-06-01',
            'name' => 'Hari Lahir Pancasila',
            'type' => 'national_holiday',
            'is_active' => 1,
        ]);
        $this->assertDatabaseHas('public_holidays', [
            'date' => '2026-06-16',
            'name' => 'Tahun Baru Islam 1448 Hijriyah',
            'type' => 'national_holiday',
        ]);
        $this->assertSame(2, PublicHoliday::count());
    }

    public function test_excel_preview_rejects_unsupported_extension(): void
    {
        $file = UploadedFile::fake()->create('data.txt', 100);

        $this->actingAs($this->hrdUser())
            ->from(route('hrd.holidays.index'))
            ->post(route('hrd.holidays.import.excelPreview'), ['file' => $file])
            ->assertRedirect(route('hrd.holidays.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('public_holidays', 0);
    }

    public function test_confirm_without_pending_import_fails_gracefully(): void
    {
        $this->actingAs($this->hrdUser())
            ->from(route('hrd.holidays.index'))
            ->post(route('hrd.holidays.import.confirm'))
            ->assertRedirect(route('hrd.holidays.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('public_holidays', 0);
    }

    public function test_import_cancel_clears_pending_preview(): void
    {
        Http::fake([
            'api-hari-libur.vercel.app/*' => Http::response([
                'status' => 'success',
                'code' => 200,
                'data' => [
                    ['date' => '2026-01-01', 'description' => 'Tahun Baru 2026 Masehi'],
                ],
                'message' => 'Holidays Found',
            ]),
        ]);

        $this->actingAs($this->hrdUser())
            ->post(route('hrd.holidays.import.apiPreview'), ['year' => 2026])
            ->assertOk();

        $this->post(route('hrd.holidays.import.cancel'))
            ->assertRedirect(route('hrd.holidays.index'));

        $this->post(route('hrd.holidays.import.confirm'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('public_holidays', 0);
    }
}
