<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeavePenggantiChange extends Model
{
    protected $fillable = [
        'leave_id',
        'old_pengganti_id',
        'new_pengganti_id',
        'changed_by',
        'alasan',
    ];

    public function leave()
    {
        return $this->belongsTo(Leave::class);
    }

    public function oldPengganti()
    {
        return $this->belongsTo(User::class, 'old_pengganti_id');
    }

    public function newPengganti()
    {
        return $this->belongsTo(User::class, 'new_pengganti_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
