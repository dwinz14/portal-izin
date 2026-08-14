<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,

            'user'         => [
                'nik'        => $this->user->nik ?? null,
                'name'       => $this->user->name ?? null,
                'division'   => $this->user->division->nama_divisi ?? null,
                'position'   => $this->user->position->nama_jabatan ?? null,
                'office'     => $this->user->office->nama_kantor ?? null,
            ],

            'leave_type'   => $this->leaveType->name ?? null,

            'start_date'   => $this->start_date,
            'end_date'     => $this->end_date,
            'reason'       => $this->alasan,
            'status'       => $this->status_final,

            'created_at'   => optional($this->created_at)->toDateTimeString(),
            'updated_at'   => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
