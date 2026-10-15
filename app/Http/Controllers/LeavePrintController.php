<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Leave;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class LeavePrintController extends Controller
{
    /**
     * Print (generate) PDF form cuti.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Leave  $leave
     */
    public function print(Request $request, Leave $leave)
    {
        // Authorization: User hanya bisa print Cuti miliknya
        if (!(
            Auth::id() === $leave->user_id
        )) {
            abort(403, 'Anda tidak memiliki akses untuk mencetak form cuti ini.');
        }

        //  Hanya cuti approved yang boleh dicetak
        if (strtolower($leave->status_final) !== 'approved') {
            abort(403, 'Form cuti hanya bisa dicetak jika sudah disetujui.');
        }

        // Load relations (hindari N+1)
        $leave->load([
            'user:id,name,division_id,position_id,office_id',
            'user.division:id,nama_divisi',
            'user.position:id,nama_jabatan',
            'user.office:id,nama_kantor',
            'leaveType:id,name',
            'pengganti:id,name',
            'approvals.approver:id,name,role',
            'approvalHistories.approver:id,name,role',
        ]);

        // Gunakan data yang sudah di-load, tidak perlu query ulang
        $approvals = $leave->approvals;
        $histories = $leave->approvalHistories;

        // Cache logo secara permanen (lebih efisien)
        $logoData = Cache::rememberForever('logo_base64', function () {
            $path = public_path('img/logoprint.jpg');
            return file_exists($path)
                ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($path))
                : null;
        });

        $data = [
            'leave'        => $leave,
            'approvals'    => $approvals,
            'histories'    => $histories,
            'generated_at' => now()->format('d M Y H:i'),
            'logoData'     => $logoData ?? '',
        ];

        // Render view ke PDF
        $generated_at = now('Asia/Jakarta')
            ->locale('id')
            ->translatedFormat('d F Y H:i');

        // Kalau $data sudah berisi generated_at, timpa agar konsisten.
        $data['generated_at'] = $generated_at;

        // Render PDF satu kali saja.
        $pdf = Pdf::loadView('leaves.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
            ]);

        $generatedAt = Carbon::now('Asia/Jakarta')->locale('id');

        $footerDate = 'Dicetak pada ' .
            $generatedAt->translatedFormat('d F Y') .
            ', pukul ' .
            $generatedAt->format('H.i') .
            ' WIB';

        $footerStatus = 'Dokumen ini diterbitkan secara digital oleh sistem SIMIKA.';

        $canvas = $pdf->getDomPDF()->getCanvas();

        $canvas->page_script('
    if ($PAGE_NUM == $PAGE_COUNT) {
        $font = $fontMetrics->get_font("DejaVu Sans", "normal");
        $boldFont = $fontMetrics->get_font("DejaVu Sans", "bold");
        $italicFont = $fontMetrics->get_font("DejaVu Sans", "italic");

        $width = $pdf->get_width();

        $date = ' . json_encode($footerDate) . ';
        $status = ' . json_encode($footerStatus) . ';


        $dateWidth = $fontMetrics->get_text_width($date, $font, 8);
        $statusWidth = $fontMetrics->get_text_width($status, $boldFont, 8);

        $pdf->text(
            ($width - $dateWidth) / 2,
            799,
            $date,
            $font,
            8,
            array(0.53, 0.53, 0.53)
        );

        $pdf->text(
            ($width - $statusWidth) / 2,
            813,
            $status,
            $boldFont,
            8,
            array(0.14, 0.52, 0.33)
        );
    }
');

        $filename = 'form-cuti-' . $leave->id . '-' . now()->format('Ymd_His') . '.pdf';

        return $request->query('download') === '1'
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }
}
