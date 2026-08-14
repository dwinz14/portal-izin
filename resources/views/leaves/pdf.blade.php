<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Form Cuti - {{ $leave->user->name }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* Basic A4-friendly styles */
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #222;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 10px 20px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .company {
            text-align: left;
        }

        .company h2 {
            margin: 0;
            font-size: 16px;
        }

        .meta {
            text-align: right;
            font-size: 11px;
            color: #555;
        }

        hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 8px 0 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        th,
        td {
            padding: 6px 8px;
            vertical-align: top;
        }

        .tbl-key {
            width: 30%;
            color: #333;
            font-weight: 600;
        }

        .tbl-val {
            width: 70%;
        }

        .section-title {
            background: #f5f5f5;
            padding: 6px 8px;
            font-weight: 700;
            margin-top: 8px;
        }

        .approvals {
            margin-top: 8px;
        }

        .approvals table {
            border: 1px solid #ddd;
        }

        .approvals th,
        .approvals td {
            border: 1px solid #eee;
            padding: 8px;
        }

        .small {
            font-size: 11px;
            color: #666;
        }

        .signature {
            margin-top: 24px;
            display: flex;
            justify-content: space-between;
        }

        .sig-box {
            width: 30%;
            text-align: center;
        }

        .sig-box .name {
            margin-top: 60px;
            text-decoration: underline;
            font-weight: 600;
        }

        .note {
            font-size: 11px;
            color: #666;
            margin-top: 12px;
        }

        img {
            max-height: 50px;
            width: auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="company">
                @if ($logoData)
                    <img src="{{ $logoData }}" alt="Logo Perusahaan">
                @else
                    <div style="font-weight: bold; font-size: 14px;">Logo Perusahaan</div>
                @endif
                <h2>PT BPR Artha Pamenang</h2>
                <div class="small">Form Pengajuan Cuti</div>
            </div>

            <div class="meta">
                <div><strong>No:</strong> CUTI-{{ $leave->id }}</div>
                <div><strong>Tanggal Cetak:</strong> {{ $generated_at }}</div>
            </div>
        </div>

        <hr>

        <div class="section-title">Informasi Pemohon</div>
        <table>
            <tr>
                <td class="tbl-key">Nama</td>
                <td class="tbl-val">{{ ucwords($leave->user->name ?? '-') }}</td>
            </tr>
            <tr>
                <td class="tbl-key">Divisi / Jabatan</td>
                <td class="tbl-val">{{ strtoupper($leave->user->division->nama_divisi ?? '-') }} /
                    {{ strtoupper($leave->user->position->nama_jabatan ?? '-') }}
                </td>
            </tr>
            <tr>
                <td class="tbl-key">Kantor</td>
                <td class="tbl-val">{{ strtoupper($leave->user->office->nama_kantor ?? '-') }}
                </td>
            </tr>
            <tr>
                <td class="tbl-key">Jenis Cuti</td>
                <td class="tbl-val">{{ ucwords($leave->leaveType->name ?? '-') }}</td>
            </tr>
            <tr>
                <td class="tbl-key">Periode</td>
                <td class="tbl-val">{{ $leave->start_date }} s/d {{ $leave->end_date }} ({{ $leave->total_hari }}
                    hari)</td>
            </tr>
            <tr>
                <td class="tbl-key">Alasan</td>
                <td class="tbl-val">{{ $leave->alasan ?? '-' }}</td>
            </tr>
        </table>

        <div class="section-title">Pengganti & Atasan</div>
        <table>
            <tr>
                <td class="tbl-key">Pengganti</td>
                <td class="tbl-val">{{ ucwords(optional($leave->pengganti)->name ?? '-') }}</td>
            </tr>
            <tr>
                <td class="tbl-key">Atasan Langsung</td>
                <td class="tbl-val">
                    {{ strtoupper(optional($leave->approvals->where('step', 2)->first()->approver)->name ?? '-') }}
                </td>
            </tr>
        </table>

        <div class="section-title">Riwayat Persetujuan</div>
        <div class="approvals">
            @php
                $statusLabels = [
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                    'revision_requested' => 'Revisi Diminta',
                    'revision_accepted' => 'Revisi Disetujui',
                    'revision_rejected' => 'Revisi Ditolak',
                ];
                $i = 1;
            @endphp

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($histories as $h)
                        <tr>
                            <td style="text-align:center;">{{ $i++ }}</td>
                            <td>{{ ucwords(optional($h->approver)->name ?? '—') }}</td>
                            <td style="text-align:center;">
                                {{ $statusLabels[$h->status] ?? strtoupper($h->status) }}
                            </td>
                            <td style="text-align:center;">
                                {{ $h->created_at->format('d M Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center;">
                                Belum ada riwayat persetujuan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- <div class="signature">
            <div class="sig-box">
                <div>Tanda Terima</div>
                <div class="small">Pemohon</div>
                <div class="name">{{ $leave->user->name ?? '-' }}</div>
            </div>

            <div class="sig-box">
                <div>Tanda Persetujuan</div>
                <div class="small">Atasan Langsung</div>
                <div class="name">{{ optional($leave->approvals->where('step', 2)->first()->approver)->name ?? '-' }}
                </div>
            </div>

        </div> --}}

        <div class="note">
            <strong>Catatan:</strong> Dokumen ini di-generate otomatis dari sistem. Periksa bukti dan riwayat
            persetujuan sebelum diproses lebih lanjut.
        </div>

        <div class="footer">
            Generated by Sistem Manajemen Cuti - {{ $generated_at }}
        </div>
    </div>
</body>

</html>
