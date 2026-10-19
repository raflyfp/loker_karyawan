<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlokasiLoker;
use App\Models\Loker;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Tampilkan halaman laporan riwayat pemakaian loker beserta filter & statistik.
     */
    public function index(Request $request): View
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $gender = $request->input('gender');
        $status = $request->input('status');
        $search = trim($request->input('search', ''));

        $query = $this->buildReportQuery($startDate, $endDate, $gender, $status, $search);

        // Statistik / Metrik Laporan
        $totalUsage = (clone $query)->count();
        $activeUsage = (clone $query)->where('status', 'active')->count();
        $completedUsage = (clone $query)->where('status', 'completed')->count();

        // Hitung rata-rata durasi dalam menit untuk transaksi selesai
        $avgMinutes = (clone $query)
            ->where('status', 'completed')
            ->whereNotNull('waktu_keluar')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, waktu_masuk, waktu_keluar)) as avg_duration')
            ->value('avg_duration') ?? 0;

        $avgHours = floor($avgMinutes / 60);
        $avgRemainMinutes = round($avgMinutes % 60);
        $avgDurationFormatted = $avgMinutes > 0 
            ? "{$avgHours} jam {$avgRemainMinutes} menit" 
            : "-";

        // Top 5 Loker Paling Sering Digunakan
        $topLockers = (clone $query)
            ->select('loker_id', DB::raw('count(*) as total'))
            ->groupBy('loker_id')
            ->orderByDesc('total')
            ->with('loker')
            ->take(5)
            ->get();

        // Top 5 Karyawan Terbanyak Menggunakan Loker
        $topKaryawan = (clone $query)
            ->select('karyawan_id', DB::raw('count(*) as total'))
            ->groupBy('karyawan_id')
            ->orderByDesc('total')
            ->with('karyawan')
            ->take(5)
            ->get();

        // Data transaksi dipaginasi
        $alokasis = (clone $query)
            ->orderByDesc('waktu_masuk')
            ->paginate(25)
            ->withQueryString();

        return view('admin.report.index', compact(
            'alokasis',
            'startDate',
            'endDate',
            'gender',
            'status',
            'search',
            'totalUsage',
            'activeUsage',
            'completedUsage',
            'avgDurationFormatted',
            'topLockers',
            'topKaryawan'
        ));
    }

    /**
     * Export data laporan pemakaian loker ke format Excel (.xlsx).
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $gender = $request->input('gender');
        $status = $request->input('status');
        $search = trim($request->input('search', ''));

        $data = $this->buildReportQuery($startDate, $endDate, $gender, $status, $search)
            ->orderByDesc('waktu_masuk')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Pemakaian Loker');

        // Setup Header Judul Perusahaan & Laporan
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'PT SNA MEDIKA — SISTEM MANAJEMEN LOKER');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0060B2'));

        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A2', 'LAPORAN RIWAYAT PEMAKAIAN LOKER KARYAWAN');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);

        $periodeText = "Periode: " . date('d/m/Y', strtotime($startDate)) . " s/d " . date('d/m/Y', strtotime($endDate));
        if ($gender) {
            $periodeText .= " | Kelompok: " . ($gender === 'L' ? 'Laki-laki' : 'Perempuan');
        }
        if ($status) {
            $periodeText .= " | Status: " . ucfirst($status);
        }
        $periodeText .= " | Total Data: " . count($data) . " transaksi | Dicetak: " . now()->format('d/m/Y H:i:s');

        $sheet->mergeCells('A3:J3');
        $sheet->setCellValue('A3', $periodeText);
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));

        // Header Kolom Tabel
        $headers = [
            'A5' => 'NO',
            'B5' => 'TANGGAL',
            'C5' => 'JAM PINJAM (MASUK)',
            'D5' => 'JAM KEMBALI (KELUAR)',
            'E5' => 'DURASI',
            'F5' => 'NO. LOKER',
            'G5' => 'KELOMPOK',
            'H5' => 'NIK',
            'I5' => 'NAMA KARYAWAN',
            'J5' => 'STATUS',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Style Header Tabel
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0060B2'], // SNA Blue
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '004D8F'],
                ],
            ],
        ];
        $sheet->getStyle('A5:J5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(26);

        // Isi Data Baris
        $row = 6;
        $no = 1;

        foreach ($data as $item) {
            $durasiText = '-';
            if ($item->waktu_masuk) {
                if ($item->waktu_keluar) {
                    $diffMin = $item->waktu_masuk->diffInMinutes($item->waktu_keluar);
                    $h = floor($diffMin / 60);
                    $m = $diffMin % 60;
                    $durasiText = "{$h}j {$m}m";
                } elseif ($item->status === 'active') {
                    $diffMin = $item->waktu_masuk->diffInMinutes(now());
                    $h = floor($diffMin / 60);
                    $m = $diffMin % 60;
                    $durasiText = "{$h}j {$m}m (Berjalan)";
                }
            }

            $statusLabel = match ($item->status) {
                'active' => 'SEDANG AKTIF',
                'completed' => 'SELESAI',
                default => strtoupper($item->status),
            };

            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $item->waktu_masuk ? $item->waktu_masuk->format('d/m/Y') : '-');
            $sheet->setCellValue("C{$row}", $item->waktu_masuk ? $item->waktu_masuk->format('H:i:s') : '-');
            $sheet->setCellValue("D{$row}", $item->waktu_keluar ? $item->waktu_keluar->format('H:i:s') : '-');
            $sheet->setCellValue("E{$row}", $durasiText);
            $sheet->setCellValue("F{$row}", $item->loker->kode_loker ?? '-');
            $sheet->setCellValue("G{$row}", ($item->loker->gender_group ?? '') === 'L' ? 'Laki-laki' : 'Perempuan');
            $sheet->setCellValueExplicit("H{$row}", $item->karyawan->nik ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("I{$row}", $item->karyawan->nama ?? 'Tidak Diketahui');
            $sheet->setCellValue("J{$row}", $statusLabel);

            // Zebra styling untuk baris genap
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:J{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8FAFC');
            }

            $sheet->getRowDimension($row)->setRowHeight(20);
            $row++;
        }

        $lastRow = $row - 1;

        if ($lastRow >= 6) {
            // Border untuk seluruh tabel
            $sheet->getStyle("A6:J{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFE2E8F0'));

            // Alignment kolom
            $sheet->getStyle("A6:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B6:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F6:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H6:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I6:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("J6:J{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Auto-fit kolom width
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Laporan_Pemakaian_Loker_' . date('Ymd_His') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Helper query builder untuk filter laporan.
     */
    protected function buildReportQuery(?string $startDate, ?string $endDate, ?string $gender, ?string $status, ?string $search)
    {
        $query = AlokasiLoker::with(['karyawan', 'loker']);

        // Filter rentang tanggal
        if ($startDate && $endDate) {
            $query->whereBetween('waktu_masuk', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);
        } elseif ($startDate) {
            $query->whereDate('waktu_masuk', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('waktu_masuk', '<=', $endDate);
        }

        // Filter Gender Loker
        if ($gender && in_array($gender, ['L', 'P'])) {
            $query->whereHas('loker', fn($q) => $q->where('gender_group', $gender));
        }

        // Filter Status
        if ($status && in_array($status, ['active', 'completed', 'cancelled'])) {
            $query->where('status', $status);
        }

        // Pencarian Teks
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('karyawan', function ($sub) use ($search) {
                    $sub->where('nama', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                })->orWhereHas('loker', function ($sub) use ($search) {
                    $sub->where('kode_loker', 'like', "%{$search}%")
                        ->orWhere('nomor_urut', $search);
                });
            });
        }

        return $query;
    }
}
