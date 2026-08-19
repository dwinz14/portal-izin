<?php

namespace Tests\Feature;

use App\Models\PublicHoliday;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
