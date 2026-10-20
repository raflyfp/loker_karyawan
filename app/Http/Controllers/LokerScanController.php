<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScanLokerRequest;
use App\Models\Loker;
use App\Services\LokerAllocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LokerScanController extends Controller
{
    /**
     * Endpoint API untuk memproses scan barcode (USB keyboard / kamera).
     */
    public function scan(ScanLokerRequest $request, LokerAllocationService $allocationService): JsonResponse
    {
        try {
            $result = $allocationService->processScan(
                $request->input('barcode'),
                $request->input('gender'),
                $request->input('idempotency_key')
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (ValidationException $e) {
            $firstMessage = collect($e->errors())->flatten()->first() ?? 'Terjadi kesalahan validasi.';
            return response()->json([
                'success' => false,
                'message' => $firstMessage,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint API status peta seluruh 100 loker laki-laki dan 100 loker perempuan.
     */
    public function status(): JsonResponse
    {
        $lokers = Loker::with(['alokasiAktif.karyawan:id,nama,nik'])
            ->orderBy('gender_group')
            ->orderBy('nomor_urut')
            ->get();

        $male = [];
        $female = [];

        foreach ($lokers as $loker) {
            $isOccupied = $loker->alokasiAktif !== null;
            $data = [
                'id' => $loker->id,
                'nomor' => $loker->nomor_urut,
                'kode' => $loker->kode_loker,
                'is_active' => (bool) $loker->is_active,
                'status' => !$loker->is_active ? 'disabled' : ($isOccupied ? 'occupied' : 'available'),
                'karyawan_nama' => $isOccupied ? $loker->alokasiAktif->karyawan->nama : null,
                'karyawan_nik' => $isOccupied ? $loker->alokasiAktif->karyawan->nik : null,
                'alokasi_id' => $isOccupied ? $loker->alokasiAktif->id : null,
            ];

            if ($loker->gender_group === 'L') {
                $male[] = $data;
            } else {
                $female[] = $data;
            }
        }

        return response()->json([
            'success' => true,
            'summary' => [
                'total_male' => count($male),
                'occupied_male' => collect($male)->where('status', 'occupied')->count(),
                'available_male' => collect($male)->where('status', 'available')->count(),
                'disabled_male' => collect($male)->where('status', 'disabled')->count(),
                'total_female' => count($female),
                'occupied_female' => collect($female)->where('status', 'occupied')->count(),
                'available_female' => collect($female)->where('status', 'available')->count(),
                'disabled_female' => collect($female)->where('status', 'disabled')->count(),
            ],
            'male' => $male,
            'female' => $female,
        ]);
    }
}
