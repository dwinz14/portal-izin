<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    /**
     * Catat satu aktivitas user ke Spatie ActivityLog.
     *
     * @param string     $eventType   Kode event: 'auth.login', 'leave.submitted', dll.
     * @param string     $description Deskripsi human-readable yang tampil di timeline.
     * @param Model|null $subject     Model terkait (Leave, AttendanceRequest, dll.) — opsional.
     * @param array      $metadata    Data konteks tambahan disimpan di properties JSON.
     */
    public static function log(
        string $eventType,
        string $description,
        ?Model $subject  = null,
        array  $metadata = [],
    ): void {
        try {
            $meta = self::getEventMeta($eventType);

            $builder = activity('user_activity')
                ->causedBy(auth()->user())
                ->withProperties(array_merge([
                    'event_type' => $eventType,
                    'category'   => $meta['category'],
                    'color'      => $meta['color'],
                ], $metadata));

            if ($subject !== null) {
                $builder->performedOn($subject);
            }

            $builder->log($description);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning(
                '[ActivityLogger] Gagal mencatat aktivitas: ' . $e->getMessage(),
                ['event_type' => $eventType]
            );
        }
    }

    /**
     * Versi log tanpa auth()->user() — untuk event yang terjadi di luar request HTTP
     * (misal: scheduler, queue job). Memerlukan causer eksplisit.
     */
    public static function logAs(
        Model  $causer,
        string $eventType,
        string $description,
        ?Model $subject  = null,
        array  $metadata = [],
    ): void {
        try {
            $meta = self::getEventMeta($eventType);

            $builder = activity('user_activity')
                ->causedBy($causer)
                ->withProperties(array_merge([
                    'event_type' => $eventType,
                    'category'   => $meta['category'],
                    'color'      => $meta['color'],
                ], $metadata));

            if ($subject !== null) {
                $builder->performedOn($subject);
            }

            $builder->log($description);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning(
                '[ActivityLogger] Gagal mencatat aktivitas: ' . $e->getMessage(),
                ['event_type' => $eventType]
            );
        }
    }

    /** Kembalikan metadata (category + color) berdasarkan event_type. */
    public static function getEventMeta(string $eventType): array
    {
        return match ($eventType) {
            'auth.login'                   => ['category' => 'auth',       'color' => 'blue'],
            'auth.logout'                  => ['category' => 'auth',       'color' => 'slate'],
            'auth.password_reset'          => ['category' => 'auth',       'color' => 'amber'],
            'auth.password_changed'        => ['category' => 'auth',       'color' => 'amber'],
            'auth.otp_verified'            => ['category' => 'auth',       'color' => 'green'],
            'leave.submitted'              => ['category' => 'leave',      'color' => 'indigo'],
            'leave.cancelled'              => ['category' => 'leave',      'color' => 'red'],
            'leave.revision_accepted'      => ['category' => 'leave',      'color' => 'green'],
            'leave.revision_rejected'      => ['category' => 'leave',      'color' => 'red'],
            'approval.leave_approved'      => ['category' => 'approval',   'color' => 'green'],
            'approval.leave_rejected'      => ['category' => 'approval',   'color' => 'red'],
            'approval.revision_requested'  => ['category' => 'approval',   'color' => 'amber'],
            'approval.attendance_approved' => ['category' => 'approval',   'color' => 'green'],
            'approval.attendance_rejected' => ['category' => 'approval',   'color' => 'red'],
            'attendance.submitted'         => ['category' => 'attendance', 'color' => 'purple'],
            'attendance.cancelled'         => ['category' => 'attendance', 'color' => 'red'],
            'profile.updated'              => ['category' => 'profile',    'color' => 'slate'],
            'admin.user_approved'          => ['category' => 'admin',      'color' => 'green'],
            'admin.user_rejected'          => ['category' => 'admin',      'color' => 'red'],
            'admin.quota_generated'        => ['category' => 'admin',      'color' => 'blue'],
            default                        => ['category' => 'other',      'color' => 'gray'],
        };
    }
}
