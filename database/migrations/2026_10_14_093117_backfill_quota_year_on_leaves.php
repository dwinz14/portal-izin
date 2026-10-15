<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $finalAt = DB::table('approval_histories')
            ->whereIn('status', ['approved', 'revision_accepted'])
            ->select('leave_id', DB::raw('MAX(created_at) as final_at'))
            ->groupBy('leave_id')
            ->pluck('final_at', 'leave_id');

        $balanceKeys = DB::table('user_leave_balances')
            ->select('user_id', 'leave_type_id', 'year')
            ->get()
            ->mapWithKeys(fn($b) => ["{$b->user_id}:{$b->leave_type_id}:{$b->year}" => true]);

        $updated = 0;
        $skipped = 0;

        DB::table('leaves')
            ->join('leave_types', 'leave_types.id', '=', 'leaves.leave_type_id')
            ->where('leaves.status_final', 'approved')
            ->where('leave_types.quota', '>', 0)
            ->whereNull('leaves.quota_year')
            ->select('leaves.id', 'leaves.user_id', 'leaves.leave_type_id', 'leaves.created_at')
            ->chunkById(500, function ($rows) use ($finalAt, $balanceKeys, &$updated, &$skipped) {
                foreach ($rows as $row) {
                    $year = Carbon::parse($finalAt[$row->id] ?? $row->created_at)->year;

                    if (! isset($balanceKeys["{$row->user_id}:{$row->leave_type_id}:{$year}"])) {
                        $skipped++;
                        continue;
                    }

                    DB::table('leaves')->where('id', $row->id)->update(['quota_year' => $year]);
                    $updated++;
                }
            }, 'leaves.id', 'id');

        Log::info("Backfill leaves.quota_year selesai: {$updated} terisi, {$skipped} dilewati (tanpa saldo terkait).");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
