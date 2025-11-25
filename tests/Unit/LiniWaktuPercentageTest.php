<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\LiniWaktu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

/**
 * Unit Tests for LiniWaktu Percentage Constraints
 * 
 * Run: php artisan test --filter=LiniWaktuPercentageTest
 */
class LiniWaktuPercentageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Valid percentages should save successfully
     */
    public function test_valid_percentages_save_successfully()
    {
        $liniWaktu = LiniWaktu::create([
            'quartal' => 'Q1',
            'tahun' => 2024,
            'nik_am' => '12345',
            'bulan_awal' => '2024-01-01',
            'bulan_akhir' => '2024-03-31',
            
            // Valid: Result + Process = 100%
            'percentage_result' => 70.0,
            'percentage_proses' => 30.0,
            
            // Valid: Result sub-totals = 70%
            'percentage_revenue' => 70.0,
            'percentage_scaling' => 0.0,
            'percentage_datin' => 0.0,
            'percentage_hsi' => 0.0,
            'percentage_wireline' => 0.0,
            'percentage_wifi' => 0.0,
            'percentage_cyc' => 0.0,
            'percentage_cr' => 0.0,
            'percentage_profit' => 0.0,
            'percentage_customer' => 0.0,
            
            // Valid: Process sub-totals = 30%
            'percentage_maps' => 30.0,
            'percentage_lop' => 0.0,
            'percentage_capability' => 0.0,
            'percentage_cc' => 0.0,
        ]);

        $this->assertDatabaseHas('lini_waktu', [
            'id' => $liniWaktu->id,
            'percentage_result' => 70.0,
            'percentage_proses' => 30.0,
        ]);
    }

    /**
     * Test: Invalid result + proses (not 100%) should fail
     */
    public function test_invalid_result_plus_proses_fails()
    {
        $this->expectException(ValidationException::class);

        LiniWaktu::create([
            'quartal' => 'Q1',
            'tahun' => 2024,
            'nik_am' => '12345',
            'bulan_awal' => '2024-01-01',
            'bulan_akhir' => '2024-03-31',
            
            // Invalid: 60 + 30 = 90% (not 100%)
            'percentage_result' => 60.0,
            'percentage_proses' => 30.0,
            
            'percentage_revenue' => 60.0,
            'percentage_maps' => 30.0,
        ]);
    }

    /**
     * Test: Invalid result sub-totals should fail
     */
    public function test_invalid_result_subtotals_fails()
    {
        $this->expectException(ValidationException::class);

        LiniWaktu::create([
            'quartal' => 'Q1',
            'tahun' => 2024,
            'nik_am' => '12345',
            'bulan_awal' => '2024-01-01',
            'bulan_akhir' => '2024-03-31',
            
            'percentage_result' => 70.0,
            'percentage_proses' => 30.0,
            
            // Invalid: Sub-total = 60% but should be 70%
            'percentage_revenue' => 60.0, // Only 60 allocated
            'percentage_scaling' => 0.0,
            // ... rest are 0
            
            'percentage_maps' => 30.0,
        ]);
    }

    /**
     * Test: Invalid process sub-totals should fail
     */
    public function test_invalid_process_subtotals_fails()
    {
        $this->expectException(ValidationException::class);

        LiniWaktu::create([
            'quartal' => 'Q1',
            'tahun' => 2024,
            'nik_am' => '12345',
            'bulan_awal' => '2024-01-01',
            'bulan_akhir' => '2024-03-31',
            
            'percentage_result' => 70.0,
            'percentage_proses' => 30.0,
            
            'percentage_revenue' => 70.0,
            
            // Invalid: Sub-total = 20% but should be 30%
            'percentage_maps' => 20.0, // Only 20 allocated
            'percentage_lop' => 0.0,
            'percentage_capability' => 0.0,
            'percentage_cc' => 0.0,
        ]);
    }

    /**
     * Test: Complex valid distribution
     */
    public function test_complex_valid_distribution()
    {
        $liniWaktu = LiniWaktu::create([
            'quartal' => 'Q2',
            'tahun' => 2024,
            'nik_am' => '12345',
            'bulan_awal' => '2024-04-01',
            'bulan_akhir' => '2024-06-30',
            
            'percentage_result' => 65.0,
            'percentage_proses' => 35.0,
            
            // Result breakdown (total = 65%)
            'percentage_revenue' => 20.0,
            'percentage_scaling' => 15.0,
            'percentage_datin' => 10.0,
            'percentage_hsi' => 5.0,
            'percentage_wireline' => 5.0,
            'percentage_wifi' => 3.0,
            'percentage_cyc' => 3.0,
            'percentage_cr' => 2.0,
            'percentage_profit' => 1.0,
            'percentage_customer' => 1.0,
            
            // Process breakdown (total = 35%)
            'percentage_maps' => 15.0,
            'percentage_lop' => 10.0,
            'percentage_capability' => 5.0,
            'percentage_cc' => 5.0,
        ]);

        $this->assertEquals(65.0, $liniWaktu->percentage_result);
        $this->assertEquals(35.0, $liniWaktu->percentage_proses);
    }

    /**
     * Test: Update with invalid percentages should fail
     */
    public function test_update_with_invalid_percentages_fails()
    {
        $liniWaktu = LiniWaktu::create([
            'quartal' => 'Q1',
            'tahun' => 2024,
            'nik_am' => '12345',
            'bulan_awal' => '2024-01-01',
            'bulan_akhir' => '2024-03-31',
            'percentage_result' => 70.0,
            'percentage_proses' => 30.0,
            'percentage_revenue' => 70.0,
            'percentage_maps' => 30.0,
        ]);

        $this->expectException(ValidationException::class);

        // Try to update with invalid percentages
        $liniWaktu->update([
            'percentage_result' => 60.0, // Now total = 90%
        ]);
    }
}
