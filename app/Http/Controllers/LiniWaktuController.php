<?php

namespace App\Http\Controllers;

use App\Models\LiniWaktu;
use App\Http\Requests\LiniWaktuRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Example Controller untuk CRUD LiniWaktu dengan Percentage Validation
 * 
 * NOTE: This is an example controller. Adjust based on your actual requirements.
 */
class LiniWaktuController extends Controller
{
    /**
     * Store a newly created LiniWaktu with percentage validation
     * 
     * @param LiniWaktuRequest $request - Auto-validates percentage constraints
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(LiniWaktuRequest $request)
    {
        try {
            // Data sudah tervalidasi oleh LiniWaktuRequest
            // Model akan auto-validate lagi saat save() untuk double-check
            $liniWaktu = LiniWaktu::create($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Lini Waktu berhasil dibuat',
                'data' => $liniWaktu
            ], 201);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Update LiniWaktu with percentage validation
     * 
     * @param LiniWaktuRequest $request
     * @param LiniWaktu $liniWaktu
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(LiniWaktuRequest $request, LiniWaktu $liniWaktu)
    {
        try {
            // Update dengan data yang sudah tervalidasi
            $liniWaktu->update($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Lini Waktu berhasil diupdate',
                'data' => $liniWaktu->fresh()
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Example: Batch update percentages with transaction
     * 
     * This ensures all updates succeed or none at all
     */
    public function batchUpdatePercentages(Request $request)
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.id' => 'required|exists:lini_waktu,id',
            'updates.*.percentages' => 'required|array',
        ]);

        \DB::beginTransaction();
        
        try {
            $results = [];
            
            foreach ($request->updates as $update) {
                $liniWaktu = LiniWaktu::findOrFail($update['id']);
                $liniWaktu->fill($update['percentages']);
                
                // Validate before saving
                $liniWaktu->validatePercentages();
                $liniWaktu->save();
                
                $results[] = $liniWaktu;
            }
            
            \DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Batch update berhasil',
                'data' => $results
            ]);
            
        } catch (ValidationException $e) {
            \DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Batch update failed - validation error',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Batch update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Example: Get percentage summary for validation preview
     * 
     * This can be used for frontend to show validation errors before submit
     */
    public function validatePercentagesPreview(Request $request)
    {
        $data = $request->all();
        
        // Calculate totals
        $resultAndProses = floatval($data['percentage_result'] ?? 0) + floatval($data['percentage_proses'] ?? 0);
        
        $resultSubTotal = 
            floatval($data['percentage_revenue'] ?? 0) +
            floatval($data['percentage_scaling'] ?? 0) +
            floatval($data['percentage_datin'] ?? 0) +
            floatval($data['percentage_hsi'] ?? 0) +
            floatval($data['percentage_wireline'] ?? 0) +
            floatval($data['percentage_wifi'] ?? 0) +
            floatval($data['percentage_cyc'] ?? 0) +
            floatval($data['percentage_cr'] ?? 0) +
            floatval($data['percentage_profit'] ?? 0) +
            floatval($data['percentage_customer'] ?? 0);
        
        $prosesSubTotal = 
            floatval($data['percentage_maps'] ?? 0) +
            floatval($data['percentage_lop'] ?? 0) +
            floatval($data['percentage_capability'] ?? 0) +
            floatval($data['percentage_cc'] ?? 0);
        
        // Validation checks
        $isValid = true;
        $errors = [];
        
        if (round($resultAndProses, 3) !== 100.0) {
            $isValid = false;
            $errors[] = "Result + Process must equal 100% (Current: {$resultAndProses}%)";
        }
        
        if (round($resultSubTotal, 3) !== round(floatval($data['percentage_result'] ?? 0), 3)) {
            $isValid = false;
            $errors[] = "Result sub-totals must equal Result% (Sub-total: {$resultSubTotal}%, Expected: {$data['percentage_result']}%)";
        }
        
        if (round($prosesSubTotal, 3) !== round(floatval($data['percentage_proses'] ?? 0), 3)) {
            $isValid = false;
            $errors[] = "Process sub-totals must equal Process% (Sub-total: {$prosesSubTotal}%, Expected: {$data['percentage_proses']}%)";
        }
        
        return response()->json([
            'is_valid' => $isValid,
            'errors' => $errors,
            'summary' => [
                'result_and_proses' => $resultAndProses,
                'result_subtotal' => $resultSubTotal,
                'proses_subtotal' => $prosesSubTotal,
            ]
        ]);
    }
}
