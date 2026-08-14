<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalHistory extends Model
{
    protected $fillable = [
        'leave_id',
        'approved_by',
        'role',
        'step',
        'status',
        'catatan',
    ];

    public function leave()
    {
        return $this->belongsTo(Leave::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Helper untuk mendapatkan label status yang lebih readable
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'revision_requested' => 'Meminta Revisi Tanggal',
            'revision_accepted' => 'Menerima Revisi',
            'revision_rejected' => 'Menolak Revisi',
            default => ucfirst($this->status),
        };
    }

    // Helper untuk mendapatkan context role
    public function getRoleContextAttribute()
    {
        if (is_null($this->step)) {
            return 'Sebagai Pemohon Cuti';
        }

        return $this->step === 1 ? 'Sebagai Pengganti' : 'Sebagai Atasan';
    }

    // Helper untuk icon context
    public function getRoleIconAttribute()
    {
        if ($this->approved_by === $this->leave->user_id) {
            return '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />';
        }

        if ($this->approved_by === $this->leave->pengganti_id) {
            return '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />';
        }

        return '<path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />';
    }

    // Helper untuk warna badge role
    public function getRoleBadgeColorAttribute()
    {
        if ($this->approved_by === $this->leave->user_id) {
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        }

        if ($this->approved_by === $this->leave->pengganti_id) {
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400';
        }

        return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400';
    }

    // Helper untuk mendapatkan class badge status
    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'approved', 'revision_accepted' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
            'rejected', 'revision_rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
            'revision_requested' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
        };
    }

    // Helper untuk icon status
    public function getStatusIconAttribute()
    {
        return match ($this->status) {
            'approved' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
            'rejected' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
            'revision_requested' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />',
            'revision_accepted' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
            'revision_rejected' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />',
            default => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />',
        };
    }

    // Helper untuk deskripsi lengkap aktivitas
    public function getActivityDescriptionAttribute()
    {
        $requesterName = $this->leave->user->name;

        if ($this->approved_by === $this->leave->user_id) {
            // Aktivitas sebagai pemohon
            return match ($this->status) {
                'revision_accepted' => "Anda menerima revisi tanggal dari atasan",
                'revision_rejected' => "Anda menolak revisi tanggal dari atasan",
                default => "Aktivitas sebagai pemohon",
            };
        }

        if ($this->approved_by === $this->leave->pengganti_id) {
            // Aktivitas sebagai pengganti
            return match ($this->status) {
                'approved' => "Menyetujui : {$requesterName}",
                'rejected' => "Menolak : {$requesterName}",
                default => "Aktivitas sebagai pengganti",
            };
        }

        // Aktivitas sebagai atasan
        return match ($this->status) {
            'approved' => "Menyetujui : {$requesterName}",
            'rejected' => "Menolak : {$requesterName}",
            'revision_requested' => "Revisi tanggal untuk cuti : {$requesterName}",
            default => "Aktivitas sebagai atasan",
        };
    }
}
