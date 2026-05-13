<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Buku Induk Kelas - {{ $kelas->nama }}</title>
    <style>
        @page { size: A4; margin: 0; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; color: #000; background: white; margin: 0; padding: 0; }

        .a4-page {
            width: 210mm; height: 295mm; background: white;
            position: relative; overflow: hidden;
            page-break-after: always;
        }
        .a4-page:last-child { page-break-after: auto; }
        .a4-page.page1 { padding: 5mm 8mm; }
        .a4-page.page2 { padding: 5mm 8mm; }
        .a4-page.page3 { padding: 10mm 15mm; }

        .dotted-line {
            border-bottom: 1px dotted #000; flex-grow: 1;
            height: 0.8rem; margin-left: 0.25rem;
            font-size: 9pt; line-height: 0.85rem;
            overflow: visible; white-space: nowrap;
        }
        .section-title { font-weight: 700; text-transform: uppercase; }
        .colon-align { width: 12px; text-align: center; display: inline-block; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 700; }

        .p1-title { font-size: 14pt; font-weight: bold; text-align: center; margin-bottom: 1rem; }
        .p1-header-info { font-size: 9.5pt; text-transform: uppercase; }
        .p1-header-grid { display: grid; grid-template-columns: 1.5fr 1fr 3.2cm; gap: 1rem; align-items: start; }
        .p1-code-grid { display: grid; grid-template-columns: max-content 12px 1fr; align-items: end; row-gap: 2px; }
        .p1-nomor-urut { border: 1px solid #000; text-align: center; width: 3cm; }
        .p1-nomor-urut-title { font-weight: bold; font-size: 8pt; padding: 2px 0; border-bottom: 1px solid #000; }
        .p1-section-title { font-size: 11pt; margin-top: 18pt; margin-bottom: 4pt; }
        .p1-label { font-size: 10.5pt; line-height: 1.15; }
        .p1-content-indent { padding-left: 44px; }
        .p1-section-indent { padding-left: 15px; }
        .p1-w-label-main { width: 195px; }
        .p1-w-label-sub { width: 163px; }
        .p1-w-label-sub2 { width: 131px; }
        .p1-w-num { width: 2rem; }
        .p1-photo-wrapper { position: absolute; right: 8mm; width: 3.2cm; z-index: 10; }
        .p1-photo-box {
            border: 1px solid #000; width: 3cm; height: 4cm;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            text-align: center; background-color: #d1d5db;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
            margin: 0 auto;
        }
        .p1-photo-caption { font-size: 8pt; line-height: 1.1; text-align: center; width: 3.2cm; margin-top: 4px; margin: 4px auto 0 auto; }
        .p1-flex { display: flex; }
        .p1-flex-ml8 { display: flex; margin-left: 2rem; }
        .p1-relative { position: relative; }
        .p1-section-header { display: flex; margin-left: -28px; }
        .p1-section-letter { width: 36px; }
        .p1-right-pad { padding-right: 4cm; }
        .p1-ml-sub { margin-left: 2rem; }

        .p2-content-indent { padding-left: 44px; }
        .p2-section-title { font-size: 11pt; margin-top: 18pt; margin-bottom: 4pt; }
        .p2-section-indent { padding-left: 32px; }
        .p2-row-main { display: grid; grid-template-columns: 32px 195px 12px 1fr; align-items: baseline; }
        .p2-row-sub { display: grid; grid-template-columns: 32px 195px 12px 1fr; align-items: baseline; }
        .p2-sub-item { display: flex; gap: 6px; }
        .p2-w-label-main { width: 195px; }
        .p2-mt-2 { margin-top: 0.5rem; }
        .p2-mt-6 { margin-top: 1.5rem; }
        .p2-mb-2 { margin-bottom: 0.5rem; }
        .p2-table {
            width: 100%; border-collapse: collapse; font-size: 9pt;
            margin-bottom: 20px; margin-top: 10px;
        }
        .p2-table th, .p2-table td { border: 1px solid #000; padding: 4px; text-align: center; vertical-align: middle; }
        .p2-table th {
            font-weight: bold; background-color: #6b7280; color: white;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }

        .p3-header { margin-bottom: 0.25rem; text-align: center; }
        .p3-header h1 { font-weight: bold; font-size: 10.5pt; }
        .p3-info { font-size: 8pt; line-height: 1.25; margin: 0.25rem 0; }
        .p3-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; align-items: start; }
        .p3-info-left { display: grid; grid-template-columns: max-content 12px 1fr; align-items: end; row-gap: 1px; }
        .p3-info-right { display: grid; grid-template-columns: max-content 12px 1fr; align-items: end; row-gap: 1px; margin-left: 2.5rem; }
        .p3-section-title { font-weight: 700; text-transform: uppercase; font-size: 9pt; margin-top: 4pt; margin-bottom: 0; }
        .p3-table {
            width: 100%; border-collapse: collapse; font-size: 7.5pt;
            line-height: 1.15; margin: 0;
        }
        .p3-table th {
            border: 1px solid #000; padding: 1px 2px; text-align: center; vertical-align: middle;
            font-weight: bold; background-color: #d1d5db; color: black;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .p3-table td { border: 1px solid #000; padding: 1px 2px; text-align: left; vertical-align: middle; }
        .p3-table td:first-child { text-align: center; }
        .p3-text-left-pad { text-align: left; padding-left: 4px !important; }
        .p3-tema-row {
            display: flex; align-items: end; font-size: 8pt; font-weight: bold;
            border: 1px solid #000; border-bottom: 0; padding: 2px 4px 1px 4px;
        }
        .p3-tema-row-last { border-bottom: 0; }
        .p3-dotted-line-tema { border-bottom: 1px dotted #000; flex-grow: 1; height: 0.6rem; margin-left: 4px; }
        .p3-footer-bg { font-weight: bold; background-color: #d1d5db; text-align: center; text-transform: uppercase; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    </style>
</head>
<body>
    @foreach($studentsData as $index => $data)
        @php
            $student = $data['student'];
            $grade = $data['grade'];
            $gradeMap = $data['gradeMap'];
            $attendanceSummary = $data['attendanceSummary'];
        @endphp
        @include('prints.SMP.Merdeka.partials.page1')
        @include('prints.SMP.Merdeka.partials.page2')
        @if($semester === 'Genap')
            @include('prints.SMP.Merdeka.partials.page3-genap')
        @else
            @include('prints.SMP.Merdeka.partials.page3-ganjil')
        @endif
    @endforeach
</body>
</html>
