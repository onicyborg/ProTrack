@php
    $project = $dailyReport->projectManager?->project;
    $client = $project?->client;

    $works = collect($dailyReport->works ?? []);
    $materials = collect($dailyReport->materials ?? []);
    $equipments = collect($dailyReport->equipments ?? []);
    $manpowers = collect($manpowerByRole ?? []);

    $reportDate = $dailyReport->report_date ? \Illuminate\Support\Carbon::parse($dailyReport->report_date) : null;
    $reportDateText = $reportDate ? $reportDate->format('d-M-y') : '-';

    $executionText = '-';
    if (!empty($executionDays)) {
        $executionText = $executionDays . ' Hari Kalender';
    }

    $maxWorkRows = 10;
    $maxMaterialRows = 10;
    $maxEquipmentRows = 4;
    $maxManpowerRows = 4;
@endphp

<div class="title">LAPORAN HARIAN KEMAJUAN PEKERJAAN</div>

<table class="header">
    <tr>
        <td class="label">Urusan</td>
        <td class="colon">:</td>
        <td class="value">{{ $client?->client_name ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label">Program</td>
        <td class="colon">:</td>
        <td class="value">{{ $project?->project_name ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label">Kegiatan</td>
        <td class="colon">:</td>
        <td class="value">{{ $dailyReport->kegiatan ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label">Rincian Kegiatan</td>
        <td class="colon">:</td>
        <td class="value">{{ $dailyReport->rincian_kegiatan ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label">Lokasi Kegiatan</td>
        <td class="colon">:</td>
        <td class="value">{{ $dailyReport->lokasi_kegiatan ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label">Kode Rekening</td>
        <td class="colon">:</td>
        <td class="value">{{ $project?->account_code ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label">Tahun Anggaran</td>
        <td class="colon">:</td>
        <td class="value">{{ $project?->budget_year ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label">Waktu Pelaksanaan</td>
        <td class="colon">:</td>
        <td class="value">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="width:70%; padding:0;">{{ $executionText }}</td>
                    <td style="width:30%; padding:0; text-align:right;">Tgl : {{ $reportDateText }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="mt-10" style="width:100%">
    <tr>
        <td style="width:50%; padding-right:6px; vertical-align:top;">
            <table class="thin">
                <tr class="section-head">
                    <td colspan="5">A. PEKERJAAN</td>
                </tr>
                <tr>
                    <th class="no">No</th>
                    <th class="jenis">Lokasi Pekerjaan/Kegiatan Pekerjaan</th>
                    <th class="sat">Sat</th>
                    <th class="vol">Volume</th>
                    <th class="ket">Ket</th>
                </tr>
                @for($i=1; $i <= $maxWorkRows; $i++)
                    @php $row = $works->get($i-1); @endphp
                    <tr class="row-h">
                        <td class="no">{{ $i }}</td>
                        <td class="jenis">{{ $row?->task?->task_name ?? '' }}</td>
                        <td class="sat">-</td>
                        <td class="vol">{{ $row?->volume ?? '' }}</td>
                        <td class="ket"></td>
                    </tr>
                @endfor
            </table>
        </td>
        <td style="width:50%; padding-left:6px; vertical-align:top;">
            <table class="thin">
                <tr class="section-head">
                    <td colspan="5">B. BAHAN-BAHAN</td>
                </tr>
                <tr>
                    <th class="no">No</th>
                    <th class="jenis">Jenis</th>
                    <th class="sat">Sat</th>
                    <th class="vol">Volume Bahan</th>
                    <th class="ket">Ket</th>
                </tr>
                @for($i=1; $i <= $maxMaterialRows; $i++)
                    @php $row = $materials->get($i-1); @endphp
                    <tr class="row-h">
                        <td class="no">{{ $i }}</td>
                        <td class="jenis">{{ $row?->projectMaterial?->material_name ?? '' }}</td>
                        <td class="sat">-</td>
                        <td class="vol">{{ $row?->volume ?? '' }}</td>
                        <td class="ket">{{ $row?->notes ?? '' }}</td>
                    </tr>
                @endfor
            </table>
        </td>
    </tr>
</table>

<table class="mt-8" style="width:100%">
    <tr>
        <td style="width:50%; padding-right:6px; vertical-align:top;">
            <table class="thin">
                <tr class="section-head">
                    <td colspan="4">C. PERALATAN</td>
                </tr>
                <tr>
                    <th class="no">No</th>
                    <th class="jenis">Jenis</th>
                    <th class="sat">Sat</th>
                    <th class="vol">Vol</th>
                </tr>
                @for($i=1; $i <= $maxEquipmentRows; $i++)
                    @php $row = $equipments->get($i-1); @endphp
                    <tr class="row-h">
                        <td class="no">{{ $i }}</td>
                        <td class="jenis">{{ $row?->projectEquipment?->equipment_name ?? '' }}</td>
                        <td class="sat">-</td>
                        <td class="vol">{{ $row?->volume ?? '' }}</td>
                    </tr>
                @endfor
            </table>
        </td>
        <td style="width:50%; padding-left:6px; vertical-align:top;">
            <table class="thin">
                <tr class="section-head">
                    <td colspan="5">D. TENAGA KERJA</td>
                </tr>
                <tr>
                    <th class="no">No</th>
                    <th class="jenis">Jenis</th>
                    <th class="sat">Sat</th>
                    <th class="vol">Jumlah</th>
                    <th class="ket">Ket</th>
                </tr>
                @for($i=1; $i <= $maxManpowerRows; $i++)
                    @php $row = $manpowers->get($i-1); @endphp
                    <tr class="row-h">
                        <td class="no">{{ $i }}</td>
                        <td class="jenis">{{ $row?->role_name ?? '' }}</td>
                        <td class="sat">Org</td>
                        <td class="vol">{{ $row?->total ?? '' }}</td>
                        <td class="ket"></td>
                    </tr>
                @endfor
            </table>
        </td>
    </tr>
</table>

<table class="thin mt-8">
    <tr class="section-head">
        <td colspan="4">E. CUACA</td>
    </tr>
    <tr>
        <th class="no">No</th>
        <th>Kondisi</th>
        <th style="width:120px">Waktu</th>
        <th style="width:190px">Keterangan</th>
    </tr>
    <tr>
        <td class="no">1</td>
        <td>{{ $dailyReport->weather_condition ?? '' }}</td>
        <td>{{ $dailyReport->weather_time ?? '' }}</td>
        <td>{{ $dailyReport->weather_notes ?? '' }}</td>
    </tr>
</table>

<table class="thin mt-8">
    <tr>
        <td style="width:50%; vertical-align:top;">
            <div class="sig-title">UNSUR TIM PENGAWAS</div>
            <div class="signature"></div>
            <div class="sig-line"></div>
            <div class="sig-name">{{ $dailyReport->supervisor?->employee_name ?? ($dailyReport->supervisor_name ?? '') }}</div>
        </td>
        <td style="width:50%; vertical-align:top;">
            <div class="sig-title">UNSUR TIM PELAKSANA</div>
            <div class="signature"></div>
            <div class="sig-line"></div>
            <div class="sig-name">{{ $dailyReport->executor?->employee_name ?? ($dailyReport->executor_name ?? '') }}</div>
        </td>
    </tr>
</table>
