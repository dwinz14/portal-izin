<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Leave;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;

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
        $pdf = Pdf::loadView('leaves.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions(['defaultFont' => 'DejaVu Sans']);

        $filename = 'form-cuti-' . $leave->id . '-' . now()->format('Ymd_His') . '.pdf';

        return $request->query('download') === '1'
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }
}
