<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\LiniWaktuTarget;
use App\Models\LiniWaktu;
use App\Models\TargetAccountM;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

/**
 * Unit Tests: LiniWaktuTarget Achievement Constraints
 * 
 * Tests validation for:
 * 1. ach_result = sum of 10 result achievement fields
 * 2. ach_proses = sum of 4 process achievement fields
 */
class LiniWaktuTargetAchievementTest extends TestCase
{
    use RefreshDatabase;

    protected LiniWaktu $liniWaktu;
    protected TargetAccountM $target;

    /**
     * Setup test data before each test
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a LiniWaktu record
        $this->liniWaktu = LiniWaktu::create([
            'quartal' => 'Q1',
            'tahun' => 2024,
            'nik_am' => 'AM001',
            'bulan_awal' => '2024-01-01',
            'bulan_akhir' => '2024-03-31',
            'percentage_result' => 70.000,
            'percentage_revenue' => 20.000,
            'percentage_scaling' => 15.000,
            'percentage_datin' => 10.000,
            'percentage_hsi' => 5.000,
            'percentage_wireline' => 5.000,
            'percentage_wifi' => 5.000,
            'percentage_cyc' => 3.000,
            'percentage_cr' => 3.000,
            'percentage_profit' => 2.000,
            'percentage_customer' => 2.000,
            'percentage_proses' => 30.000,
            'percentage_maps' => 15.000,
            'percentage_lop' => 10.000,
            'percentage_capability' => 3.000,
            'percentage_cc' => 2.000,
        ]);

        // Create a TargetAccountM record
        $this->target = TargetAccountM::create([
            'account_manager_company_id' => 1,
            't_revenue' => 1000000,
            't_scalling' => 50000,
            't_datin' => 30000,
            't_hsi' => 20000,
            't_wireline' => 15000,
            't_wifi' => 10000,
            't_cyc' => 5000,
            't_cr' => 3000,
            't_profit' => 8000,
            't_nps' => 2000,
            't_maps' => 4000,
            't_lop' => 6000,
            't_capability' => 1000,
            't_cc' => 500,
            't_ngtma' => 0,
            't_sustain' => 0,
        ]);
    }

    /**
     * Test: Valid achievements save successfully
     */
    public function test_valid_achievements_save_successfully(): void
    {
        $pivot = new LiniWaktuTarget([
            'lini_waktu_id' => $this->liniWaktu->id,
            'target_id' => $this->target->id,
            // Result achievements (total = 900%)
            'ach_revenue_plan' => 100.000,
            'ach_scaling' => 95.000,
            'ach_sales_datin' => 90.000,
            'ach_hsi' => 85.000,
            'ach_wireline' => 110.000,
            'ach_wifi' => 105.000,
            'ach_cyc' => 98.000,
            'ach_cr' => 92.000,
            'ach_profit' => 88.000,
            'ach_nps' => 87.000,
            'ach_result' => 950.000, // Sum of above
            // Process achievements (total = 380%)
            'ach_maps' => 95.000,
            'ach_lop' => 100.000,
            'ach_capability' => 90.000,
            'ach_cc' => 95.000,
            'ach_proses' => 380.000, // Sum of above
        ]);

        $pivot->save();

        $this->assertDatabaseHas('lini_waktu_target', [
            'lini_waktu_id' => $this->liniWaktu->id,
            'target_id' => $this->target->id,
        ]);
    }

    /**
     * Test: Invalid ach_result (doesn't match sum) fails
     */
    public function test_invalid_ach_result_fails(): void
    {
        $this->expectException(ValidationException::class);

        $pivot = new LiniWaktuTarget([
            'lini_waktu_id' => $this->liniWaktu->id,
            'target_id' => $this->target->id,
            // Result achievements (total = 950%)
            'ach_revenue_plan' => 100.000,
            'ach_scaling' => 95.000,
            'ach_sales_datin' => 90.000,
            'ach_hsi' => 85.000,
            'ach_wireline' => 110.000,
            'ach_wifi' => 105.000,
            'ach_cyc' => 98.000,
            'ach_cr' => 92.000,
            'ach_profit' => 88.000,
            'ach_nps' => 87.000,
            'ach_result' => 900.000, // WRONG: Should be 950%
            // Process achievements (valid)
            'ach_maps' => 95.000,
            'ach_lop' => 100.000,
            'ach_capability' => 90.000,
            'ach_cc' => 95.000,
            'ach_proses' => 380.000,
        ]);

        $pivot->save(); // Should throw ValidationException
    }

    /**
     * Test: Invalid ach_proses (doesn't match sum) fails
     */
    public function test_invalid_ach_proses_fails(): void
    {
        $this->expectException(ValidationException::class);

        $pivot = new LiniWaktuTarget([
            'lini_waktu_id' => $this->liniWaktu->id,
            'target_id' => $this->target->id,
            // Result achievements (valid)
            'ach_revenue_plan' => 100.000,
            'ach_scaling' => 95.000,
            'ach_sales_datin' => 90.000,
            'ach_hsi' => 85.000,
            'ach_wireline' => 110.000,
            'ach_wifi' => 105.000,
            'ach_cyc' => 98.000,
            'ach_cr' => 92.000,
            'ach_profit' => 88.000,
            'ach_nps' => 87.000,
            'ach_result' => 950.000,
            // Process achievements (total = 380%)
            'ach_maps' => 95.000,
            'ach_lop' => 100.000,
            'ach_capability' => 90.000,
            'ach_cc' => 95.000,
            'ach_proses' => 400.000, // WRONG: Should be 380%
        ]);

        $pivot->save(); // Should throw ValidationException
    }

    /**
     * Test: Auto-calculate helpers work correctly
     */
    public function test_auto_calculate_achievements(): void
    {
        $pivot = new LiniWaktuTarget([
            'lini_waktu_id' => $this->liniWaktu->id,
            'target_id' => $this->target->id,
            // Result achievements
            'ach_revenue_plan' => 100.000,
            'ach_scaling' => 95.000,
            'ach_sales_datin' => 90.000,
            'ach_hsi' => 85.000,
            'ach_wireline' => 110.000,
            'ach_wifi' => 105.000,
            'ach_cyc' => 98.000,
            'ach_cr' => 92.000,
            'ach_profit' => 88.000,
            'ach_nps' => 87.000,
            // Process achievements
            'ach_maps' => 95.000,
            'ach_lop' => 100.000,
            'ach_capability' => 90.000,
            'ach_cc' => 95.000,
        ]);

        // Auto-calculate
        $pivot->autoCalculateAchievements();

        $this->assertEquals(950.000, $pivot->ach_result);
        $this->assertEquals(380.000, $pivot->ach_proses);

        // Now save should work
        $pivot->save();

        $this->assertDatabaseHas('lini_waktu_target', [
            'lini_waktu_id' => $this->liniWaktu->id,
            'target_id' => $this->target->id,
            'ach_result' => 950.000,
            'ach_proses' => 380.000,
        ]);
    }

    /**
     * Test: Complex valid distribution with decimals
     */
    public function test_complex_valid_distribution(): void
    {
        $pivot = new LiniWaktuTarget([
            'lini_waktu_id' => $this->liniWaktu->id,
            'target_id' => $this->target->id,
            // Result achievements with decimals
            'ach_revenue_plan' => 105.500,
            'ach_scaling' => 98.750,
            'ach_sales_datin' => 92.250,
            'ach_hsi' => 87.125,
            'ach_wireline' => 112.875,
            'ach_wifi' => 103.625,
            'ach_cyc' => 96.375,
            'ach_cr' => 94.125,
            'ach_profit' => 89.625,
            'ach_nps' => 85.750,
            'ach_result' => 966.000, // Sum with proper rounding
            // Process achievements
            'ach_maps' => 97.250,
            'ach_lop' => 102.500,
            'ach_capability' => 88.750,
            'ach_cc' => 93.500,
            'ach_proses' => 382.000, // Sum with proper rounding
        ]);

        $pivot->save();

        $this->assertDatabaseHas('lini_waktu_target', [
            'lini_waktu_id' => $this->liniWaktu->id,
            'target_id' => $this->target->id,
        ]);
    }

    /**
     * Test: Update with invalid achievements fails
     */
    public function test_update_with_invalid_achievements_fails(): void
    {
        // Create valid pivot first
        $pivot = LiniWaktuTarget::create([
            'lini_waktu_id' => $this->liniWaktu->id,
            'target_id' => $this->target->id,
            'ach_revenue_plan' => 100.000,
            'ach_scaling' => 95.000,
            'ach_sales_datin' => 90.000,
            'ach_hsi' => 85.000,
            'ach_wireline' => 110.000,
            'ach_wifi' => 105.000,
            'ach_cyc' => 98.000,
            'ach_cr' => 92.000,
            'ach_profit' => 88.000,
            'ach_nps' => 87.000,
            'ach_result' => 950.000,
            'ach_maps' => 95.000,
            'ach_lop' => 100.000,
            'ach_capability' => 90.000,
            'ach_cc' => 95.000,
            'ach_proses' => 380.000,
        ]);

        $this->expectException(ValidationException::class);

        // Try to update with invalid ach_result
        $pivot->ach_result = 999.000; // Wrong value
        $pivot->save(); // Should throw ValidationException
    }

    /**
     * Test: Zero values are valid
     */
    public function test_zero_achievements_valid(): void
    {
        $pivot = new LiniWaktuTarget([
            'lini_waktu_id' => $this->liniWaktu->id,
            'target_id' => $this->target->id,
            // All zeros
            'ach_revenue_plan' => 0,
            'ach_scaling' => 0,
            'ach_sales_datin' => 0,
            'ach_hsi' => 0,
            'ach_wireline' => 0,
            'ach_wifi' => 0,
            'ach_cyc' => 0,
            'ach_cr' => 0,
            'ach_profit' => 0,
            'ach_nps' => 0,
            'ach_result' => 0,
            'ach_maps' => 0,
            'ach_lop' => 0,
            'ach_capability' => 0,
            'ach_cc' => 0,
            'ach_proses' => 0,
        ]);

        $pivot->save();

        $this->assertDatabaseHas('lini_waktu_target', [
            'lini_waktu_id' => $this->liniWaktu->id,
            'target_id' => $this->target->id,
            'ach_result' => 0,
            'ach_proses' => 0,
        ]);
    }
}
