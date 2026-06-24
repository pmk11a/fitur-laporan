<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $template->nama_nota }} - {{ $header['nobukti'] ?? $header['NOBUKTI'] ?? '' }}</title>
    <style>
        @page {
            size: {{ $template->paper_size ?? 'A4' }} {{ $template->orientation ?? 'portrait' }};
            margin: {{ $template->margins ?? '10mm' }};
        }

        * { box-sizing: border-box; }

        body {
            font-family: '{{ $template->font_family ?? 'Tahoma' }}', sans-serif;
            font-size: {{ $template->font_size ?? '10pt' }};
            color: #000;
            line-height: 1.3;
            margin: 0;
        }

        table { border-collapse: collapse; }

        .clearfix::after { content: ""; display: table; clear: both; }

        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .bold { font-weight: bold; }
        .italic { font-style: italic; }

        .nota-container { width: 100%; }

        .nota-header {
            border-bottom: 2px solid #000;
            padding-bottom: 4mm;
            margin-bottom: 4mm;
        }

        .nota-header-left {
            float: left;
            width: 55%;
        }

        .nota-header-right {
            float: right;
            width: 42%;
            text-align: left;
        }

        .nota-logo {
            max-height: 25mm;
            max-width: 60mm;
            margin-bottom: 2mm;
        }

        .nota-company-name {
            font-size: 16pt;
            font-weight: bold;
            margin: 0;
        }

        .nota-company-addr {
            font-size: 9pt;
            margin: 0;
        }

        .nota-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 2mm 0;
        }

        .info-table {
            width: 100%;
            margin: 3mm 0;
        }

        .info-table td {
            padding: 1px 4px;
            vertical-align: top;
        }

        .info-label {
            font-weight: normal;
            white-space: nowrap;
        }

        .info-value {
            min-width: 30mm;
        }

        .kepada-yth {
            margin: 3mm 0;
            padding: 2mm 0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }

        .kepada-yth-label {
            font-weight: bold;
            margin-bottom: 1mm;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2mm;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #000;
            padding: 4px 6px;
        }

        .detail-table th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .footer-summary {
            margin-top: 4mm;
            width: 100%;
        }

        .footer-summary td {
            padding: 2px 6px;
        }

        .footer-summary .summary-label {
            text-align: right;
            width: 70%;
        }

        .footer-summary .summary-value {
            text-align: right;
            width: 25%;
            white-space: nowrap;
        }

        .footer-summary .grand-total {
            font-weight: bold;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }

        .terbilang {
            margin-top: 4mm;
            padding: 2mm 0;
            font-style: italic;
        }

        .terbilang-label {
            font-weight: bold;
            font-style: normal;
        }

        .signatures {
            margin-top: 12mm;
            width: 100%;
            page-break-inside: avoid;
        }

        .signature-block {
            display: inline-block;
            width: 45%;
            text-align: center;
            vertical-align: top;
        }

        .signature-space {
            height: 15mm;
        }

        .signature-label {
            font-weight: bold;
        }

        .page-footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="nota-container">

        @include('nota.partials.header', [
            'config' => $config,
            'header' => $header,
            'perusahaan' => $perusahaan
        ])

        @if(!empty($config['kepada_yth']))
            @include('nota.partials.kepada-yth', [
                'config' => $config['kepada_yth'],
                'header' => $header
            ])
        @endif

        @if(!empty($config['info_baris']))
            @include('nota.partials.info-baris', [
                'items' => $config['info_baris'],
                'header' => $header
            ])
        @endif

        @include('nota.partials.table', [
            'columns' => $config['columns'] ?? [],
            'rows' => $rows
        ])

        @if(!empty($config['footer_summary']))
            @include('nota.partials.footer-summary', [
                'items' => $config['footer_summary'],
                'rows' => $rows,
                'header' => $header
            ])
        @endif

        @if(!empty($config['terbilang']))
            @include('nota.partials.terbilang', [
                'config' => $config['terbilang'],
                'header' => $header
            ])
        @endif

        @if(!empty($config['signatures']))
            @include('nota.partials.signature', [
                'signatures' => $config['signatures']
            ])
        @endif

    </div>
</body>
</html>
