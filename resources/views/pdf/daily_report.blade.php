<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daily Report</title>
    <style>
        @page { margin: 18px 18px 18px 18px; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 10px; color: #000; }
        .title { text-align: center; font-weight: 700; font-size: 13px; letter-spacing: 0.4px; margin-bottom: 10px; }
        table { border-collapse: collapse; width: 100%; }
        .header td { padding: 1px 0; vertical-align: top; }
        .label { width: 86px; }
        .colon { width: 10px; text-align: center; }
        .value { }
        .thin { border: 0.5px solid #000; }
        .thin td, .thin th { border: 0.5px solid #000; padding: 2px 3px; vertical-align: top; line-height: 1.2; }
        .section-head { font-weight: 700; text-transform: uppercase; }
        .section-head td { padding: 2px 3px; }
        .no { width: 22px; text-align: center; }
        .sat { width: 36px; text-align: center; }
        .vol { width: 52px; text-align: right; }
        .ket { width: 62px; }
        .jenis { }
        .row-h td { height: 16px; }
        .mt-8 { margin-top: 8px; }
        .mt-10 { margin-top: 10px; }
        .signature { height: 90px; }
        .sig-title { text-align: center; font-weight: 700; padding: 6px 0; }
        .sig-name { text-align: center; padding-top: 2px; }
        .sig-line { width: 70%; margin: 0 auto; border-top: 0.5px solid #000; height: 1px; }
    </style>
</head>
<body>
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
        <td class="value">-</td>
    </tr>
    <tr>
        <td class="label">Rincian Kegiatan</td>
        <td class="colon">:</td>
        <td class="value">-</td>
    </tr>
    <tr>
        <td class="label">Lokasi Kegiatan</td>
        <td class="colon">:</td>
        <td class="value">-</td>
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

</body>
</html>
