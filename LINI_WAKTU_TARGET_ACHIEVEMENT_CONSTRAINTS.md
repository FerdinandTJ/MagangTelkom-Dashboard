# Lini Waktu Target Achievement Constraints

## Overview

Dokumen ini menjelaskan **validation constraints** untuk field achievement di table `lini_waktu_target`. Constraints ini memastikan bahwa nilai `ach_result` dan `ach_proses` selalu konsisten dengan komponen-komponennya.

## Validation Rules

### Constraint 1: ach_result

**Rule**: Nilai `ach_result` HARUS sama dengan jumlah dari 10 field achievement result berikut:

```php
ach_result = 
    ach_revenue_plan +
    ach_scaling +
    ach_sales_datin +
    ach_hsi +
    ach_wireline +
    ach_wifi +
    ach_cyc +
    ach_cr +
    ach_profit +
    ach_nps
```

**Contoh Valid**:
```php
[
    'ach_revenue_plan' => 100.000,  // Result KPI 1
    'ach_scaling' => 95.000,        // Result KPI 2
    'ach_sales_datin' => 90.000,    // Result KPI 3
    'ach_hsi' => 85.000,            // Result KPI 4
    'ach_wireline' => 110.000,      // Result KPI 5
    'ach_wifi' => 105.000,          // Result KPI 6
    'ach_cyc' => 98.000,            // Result KPI 7
    'ach_cr' => 92.000,             // Result KPI 8
    'ach_profit' => 88.000,         // Result KPI 9
    'ach_nps' => 87.000,            // Result KPI 10
    'ach_result' => 950.000,        // ✅ Sum = 950%
]
```

**Contoh Invalid**:
```php
[
    // ... 10 fields yang totalnya 950%
    'ach_result' => 900.000,  // ❌ Tidak sesuai dengan sum (950%)
]
```

### Constraint 2: ach_proses

**Rule**: Nilai `ach_proses` HARUS sama dengan jumlah dari 4 field achievement process berikut:

```php
ach_proses = 
    ach_maps +
    ach_lop +
    ach_capability +
    ach_cc
```

**Contoh Valid**:
```php
[
    'ach_maps' => 95.000,           // Process KPI 1
    'ach_lop' => 100.000,           // Process KPI 2
    'ach_capability' => 90.000,     // Process KPI 3
    'ach_cc' => 95.000,             // Process KPI 4
    'ach_proses' => 380.000,        // ✅ Sum = 380%
]
```

**Contoh Invalid**:
```php
[
    // ... 4 fields yang totalnya 380%
    'ach_proses' => 400.000,  // ❌ Tidak sesuai dengan sum (380%)
]
```

## Field Descriptions

### Result Achievement Fields (10 fields)

| Field | Description | Notes |
|-------|-------------|-------|
| `ach_revenue_plan` | Achievement Revenue Plan (%) | Target revenue pencapaian |
| `ach_scaling` | Achievement Scaling (%) | Target scaling pencapaian |
| `ach_sales_datin` | Achievement Sales Datin (%) | Target sales data internet |
| `ach_hsi` | Achievement HSI (%) | High Speed Internet |
| `ach_wireline` | Achievement Wireline (%) | Target wireline |
| `ach_wifi` | Achievement WiFi (%) | Target WiFi |
| `ach_cyc` | Achievement CYC (%) | Customer Yield per Customer |
| `ach_cr` | Achievement CR (%) | Customer Retention |
| `ach_profit` | Achievement Profit (%) | Target profit |
| `ach_nps` | Achievement NPS (%) | Net Promoter Score |

### Process Achievement Fields (4 fields)

| Field | Description | Notes |
|-------|-------------|-------|
| `ach_maps` | Achievement MAPS (%) | MAPS achievement |
| `ach_lop` | Achievement LOP (%) | Length of Payment |
| `ach_capability` | Achievement Capability (%) | Capability building |
| `ach_cc` | Achievement CC (%) | Customer Count |

### Summary Fields (2 fields)

| Field | Description | Calculation |
|-------|-------------|-------------|
| `ach_result` | Total Result Achievement (%) | Sum of 10 result fields |
| `ach_proses` | Total Process Achievement (%) | Sum of 4 process fields |

## Implementation

### 1. Pivot Model: LiniWaktuTarget

File: `app/Models/LiniWaktuTarget.php`

Model pivot dengan automatic validation pada event `saving`:

```php
protected static function booted(): void
{
    static::saving(function (LiniWaktuTarget $pivot) {
        $pivot->validateAchievements();
    });
}
```

**Helper Methods**:
- `validateAchievements()`: Validates both constraints
- `calculateAchResult()`: Auto-calculate result sum
- `calculateAchProses()`: Auto-calculate process sum
- `autoCalculateAchievements()`: Set both ach_result and ach_proses

### 2. Custom Validation Rule

File: `app/Rules/LiniWaktuTargetAchievementValidation.php`

Reusable validation rule untuk Form Requests:

```php
use App\Rules\LiniWaktuTargetAchievementValidation;

'ach_result' => [
    'required',
    'numeric',
    new LiniWaktuTargetAchievementValidation($request->all(), 'result')
],
'ach_proses' => [
    'required',
    'numeric',
    new LiniWaktuTargetAchievementValidation($request->all(), 'proses')
],
```

### 3. Form Request Validation

File: `app/Http/Requests/LiniWaktuTargetRequest.php`

Comprehensive form validation dengan auto-calculation:

```php
protected function prepareForValidation(): void
{
    if (!$this->has('ach_result')) {
        $achResult = /* sum of 10 result fields */;
        $this->merge(['ach_result' => round($achResult, 3)]);
    }
    
    if (!$this->has('ach_proses')) {
        $achProses = /* sum of 4 process fields */;
        $this->merge(['ach_proses' => round($achProses, 3)]);
    }
}
```

## Usage Examples

### Example 1: Auto-Calculate Achievements

```php
use App\Models\LiniWaktuTarget;

$pivot = new LiniWaktuTarget([
    'lini_waktu_id' => 1,
    'target_id' => 1,
    // Set individual achievement fields
    'ach_revenue_plan' => 100.000,
    'ach_scaling' => 95.000,
    // ... set all 14 achievement fields
]);

// Auto-calculate totals
$pivot->autoCalculateAchievements();

// Now ach_result and ach_proses are set correctly
$pivot->save(); // ✅ Passes validation
```

### Example 2: Manual Calculation

```php
$achResult = 
    $data['ach_revenue_plan'] +
    $data['ach_scaling'] +
    $data['ach_sales_datin'] +
    $data['ach_hsi'] +
    $data['ach_wireline'] +
    $data['ach_wifi'] +
    $data['ach_cyc'] +
    $data['ach_cr'] +
    $data['ach_profit'] +
    $data['ach_nps'];

$achProses = 
    $data['ach_maps'] +
    $data['ach_lop'] +
    $data['ach_capability'] +
    $data['ach_cc'];

$pivot = LiniWaktuTarget::create([
    'lini_waktu_id' => $liniWaktuId,
    'target_id' => $targetId,
    // ... individual achievement fields
    'ach_result' => round($achResult, 3),
    'ach_proses' => round($achProses, 3),
]);
```

### Example 3: Using Form Request

```php
use App\Http\Requests\LiniWaktuTargetRequest;

public function store(LiniWaktuTargetRequest $request)
{
    // Validation sudah dijalankan, termasuk achievement constraints
    // ach_result dan ach_proses sudah auto-calculated di prepareForValidation()
    
    $pivot = LiniWaktuTarget::create($request->validated());
    
    return response()->json($pivot, 201);
}
```

## Error Handling

### Validation Exception Messages

**Constraint 1 Error**:
```
Total dari achievement result (950.000%) harus sama dengan ach_result (900.000%). 
Field yang dijumlahkan: ach_revenue_plan, ach_scaling, ach_sales_datin, ach_hsi, 
ach_wireline, ach_wifi, ach_cyc, ach_cr, ach_profit, ach_nps
```

**Constraint 2 Error**:
```
Total dari achievement proses (380.000%) harus sama dengan ach_proses (400.000%). 
Field yang dijumlahkan: ach_maps, ach_lop, ach_capability, ach_cc
```

### Catching Validation Errors

```php
use Illuminate\Validation\ValidationException;

try {
    $pivot->save();
} catch (ValidationException $e) {
    // Get error messages
    $errors = $e->errors();
    
    // Example: ["ach_result" => ["Total dari achievement result ..."]]
    foreach ($errors as $field => $messages) {
        foreach ($messages as $message) {
            echo "$field: $message\n";
        }
    }
}
```

## Testing

### Unit Tests

File: `tests/Unit/LiniWaktuTargetAchievementTest.php`

**Test Coverage**:
1. ✅ Valid achievements save successfully
2. ✅ Invalid ach_result fails validation
3. ✅ Invalid ach_proses fails validation
4. ✅ Auto-calculate helpers work correctly
5. ✅ Complex valid distribution with decimals
6. ✅ Update with invalid achievements fails
7. ✅ Zero values are valid

**Running Tests**:
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Unit/LiniWaktuTargetAchievementTest.php

# Run specific test method
php artisan test --filter=test_valid_achievements_save_successfully
```

## Seeder Implementation

### Updated DummyDataSeeder

File: `database/seeders/DummyDataSeeder.php`

**Key Changes**:
```php
// Calculate individual achievement percentages
$achRevenuePlan = round(($achievementRate * 100), 3);
$achScaling = round(($achievementRate * 100), 3);
// ... calculate all 14 fields

// Calculate ach_result (sum of 10 result fields)
$achResult = round(
    $achRevenuePlan + $achScaling + $achSalesDatin + $achHsi + 
    $achWireline + $achWifi + $achCyc + $achCr + $achProfit + $achNps,
    3
);

// Calculate ach_proses (sum of 4 process fields)
$achProses = round(
    $achMaps + $achLop + $achCapability + $achCc,
    3
);

// Insert with calculated totals
'ach_result' => $achResult,  // ✅ Now valid
'ach_proses' => $achProses,  // ✅ Now valid
```

### Creating New Seeders

**Template for valid seeder data**:
```php
// Generate individual values
$achFields = [
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
    'ach_maps' => 95.000,
    'ach_lop' => 100.000,
    'ach_capability' => 90.000,
    'ach_cc' => 95.000,
];

// Calculate totals
$achFields['ach_result'] = round(array_sum([
    $achFields['ach_revenue_plan'],
    $achFields['ach_scaling'],
    $achFields['ach_sales_datin'],
    $achFields['ach_hsi'],
    $achFields['ach_wireline'],
    $achFields['ach_wifi'],
    $achFields['ach_cyc'],
    $achFields['ach_cr'],
    $achFields['ach_profit'],
    $achFields['ach_nps'],
]), 3);

$achFields['ach_proses'] = round(array_sum([
    $achFields['ach_maps'],
    $achFields['ach_lop'],
    $achFields['ach_capability'],
    $achFields['ach_cc'],
]), 3);

// Verify constraints before inserting
assert($achFields['ach_result'] === 950.000, 'ach_result must equal sum of result fields');
assert($achFields['ach_proses'] === 380.000, 'ach_proses must equal sum of process fields');

DB::table('lini_waktu_target')->insert($achFields);
```

### Running Seeders

```bash
# Fresh migration with seeders
php artisan migrate:fresh --seed

# Run specific seeder
php artisan db:seed --class=DummyDataSeeder

# Verify data integrity after seeding
php artisan tinker
>>> DB::table('lini_waktu_target')->get()->each(function($row) {
...   $resultSum = $row->ach_revenue_plan + $row->ach_scaling + /* ... all 10 fields */;
...   echo "Result: {$resultSum} === {$row->ach_result}\n";
... });
```

## Best Practices

### 1. Always Use Auto-Calculate

```php
// ✅ GOOD: Let the model calculate
$pivot = new LiniWaktuTarget($data);
$pivot->autoCalculateAchievements();
$pivot->save();

// ❌ BAD: Manual calculation prone to errors
$pivot->ach_result = $data['ach_revenue_plan'] + /* ... */;
$pivot->ach_proses = $data['ach_maps'] + /* ... */;
```

### 2. Use Form Requests

```php
// ✅ GOOD: Form request handles validation and calculation
public function store(LiniWaktuTargetRequest $request) {
    return LiniWaktuTarget::create($request->validated());
}

// ❌ BAD: Manual validation
public function store(Request $request) {
    // Manual calculation and validation
}
```

### 3. Handle Validation Errors

```php
// ✅ GOOD: Catch and handle validation exceptions
try {
    $pivot->save();
} catch (ValidationException $e) {
    return response()->json(['errors' => $e->errors()], 422);
}

// ❌ BAD: Let exception propagate unhandled
$pivot->save(); // May crash without proper error handling
```

### 4. Precision Matters

```php
// ✅ GOOD: Use round() with 3 decimals
$achResult = round($sum, 3);

// ❌ BAD: Floating point precision issues
$achResult = $sum; // May fail validation due to 0.001 difference
```

## Related Files

### Models
- `app/Models/LiniWaktuTarget.php` - Pivot model dengan validation
- `app/Models/LiniWaktu.php` - Updated to use pivot model
- `app/Models/TargetAccountM.php` - Updated to use pivot model

### Validation
- `app/Rules/LiniWaktuTargetAchievementValidation.php` - Custom validation rule
- `app/Http/Requests/LiniWaktuTargetRequest.php` - Form request validation

### Database
- `database/migrations/2025_11_13_065645_add_achievement_columns_to_lini_waktu_target.php` - Migration
- `database/seeders/DummyDataSeeder.php` - Updated seeder dengan valid data

### Tests
- `tests/Unit/LiniWaktuTargetAchievementTest.php` - Comprehensive unit tests

### Documentation
- `LINI_WAKTU_TARGET_ACHIEVEMENT_CONSTRAINTS.md` - This file
- `LINI_WAKTU_PERCENTAGE_CONSTRAINTS.md` - Related constraints for lini_waktu table

## Summary

**Key Points**:
1. ✅ `ach_result` MUST equal sum of 10 result achievement fields
2. ✅ `ach_proses` MUST equal sum of 4 process achievement fields
3. ✅ Validation enforced at model level (automatic on save)
4. ✅ Custom validation rule available for form requests
5. ✅ Auto-calculate helpers prevent manual calculation errors
6. ✅ Comprehensive unit tests ensure reliability
7. ✅ Seeder generates valid test data
8. ✅ Clear error messages guide developers

**Migration from Old System**:
- Old: Random `ach_result` and `ach_proses` values (invalid)
- New: Calculated values ensuring data integrity
- Update existing data: Use `autoCalculateAchievements()` helper
