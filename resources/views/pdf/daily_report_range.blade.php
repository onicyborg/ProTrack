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
@foreach($items as $item)
    @php
        $dailyReport = $item['dailyReport'];
        $executionDays = $item['executionDays'] ?? null;
        $manpowerByRole = $item['manpowerByRole'] ?? [];
    @endphp

    @include('pdf.partials.daily_report_content')

    @if(!$loop->last)
        <div style="page-break-after: always;"></div>
    @endif
@endforeach
</body>
</html>
