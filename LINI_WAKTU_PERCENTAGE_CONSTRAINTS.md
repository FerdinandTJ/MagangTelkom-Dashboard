# Lini Waktu Percentage Constraints

## 📋 Overview

Model `LiniWaktu` memiliki constraint validasi untuk memastikan persentase KPI (Key Performance Indicators) dijumlahkan dengan benar.

## ✅ Validation Rules

### **Rule 1: Result + Process = 100%**
```
percentage_result + percentage_proses = 100%
```

**Contoh:**
- ✅ Valid: `percentage_result = 70%` dan `percentage_proses = 30%` (Total: 100%)
- ❌ Invalid: `percentage_result = 60%` dan `percentage_proses = 30%` (Total: 90%)

---

### **Rule 2: Result Sub-Percentages**
```
Sum of Result KPIs = percentage_result
```

**Result Sub-Percentages:**
- `percentage_revenue` - Bobot Revenue
- `percentage_scaling` - Bobot Scaling  
- `percentage_datin` - Bobot Datin
- `percentage_hsi` - Bobot HSI
- `percentage_wireline` - Bobot Wireline
- `percentage_wifi` - Bobot WiFi
- `percentage_cyc` - Bobot CYC (Customer Yield Contribution)
- `percentage_cr` - Bobot CR (Customer Retention)
- `percentage_profit` - Bobot Profit
- `percentage_customer` - Bobot Customer

**Contoh:**
```php
percentage_result = 70%

// Sub-percentages harus = 70%
percentage_revenue = 20%
percentage_scaling = 15%
percentage_datin = 10%
percentage_hsi = 8%
percentage_wireline = 7%
percentage_wifi = 5%
percentage_cyc = 2%
percentage_cr = 1%
percentage_profit = 1%
percentage_customer = 1%
// Total = 70% ✅
```

---

### **Rule 3: Process Sub-Percentages**
```
Sum of Process KPIs = percentage_proses
```

**Process Sub-Percentages:**
- `percentage_maps` - Bobot MAPS
- `percentage_lop` - Bobot LOP (Level of Process)
- `percentage_capability` - Bobot Capability
- `percentage_cc` - Bobot CC (Company Count)

**Contoh:**
```php
percentage_proses = 30%

// Sub-percentages harus = 30%
percentage_maps = 10%
percentage_lop = 10%
percentage_capability = 5%
percentage_cc = 5%
// Total = 30% ✅
```

---

## 🛠️ Implementation

### **Model Level Validation**

Validasi otomatis dilakukan saat `save()` atau `update()` pada model `LiniWaktu`:

```php
use App\Models\LiniWaktu;

// Akan throw ValidationException jika constraint tidak terpenuhi
$liniWaktu = new LiniWaktu([
    'percentage_result' => 70,
    'percentage_proses' => 30,
    // ... sub-percentages
]);

$liniWaktu->save(); // Auto-validate
```

### **Custom Validation Rule**

Untuk form validation, gunakan custom rule:

```php
use App\Rules\LiniWaktuPercentageValidation;

$request->validate([
    'percentage_result' => ['required', 'numeric', 'min:0', 'max:100', new LiniWaktuPercentageValidation('result_process')],
    'percentage_proses' => ['required', 'numeric', 'min:0', 'max:100'],
    // ... other fields
]);
```

**Available Validation Types:**
1. `'result_process'` - Validate Rule 1 (result + proses = 100%)
2. `'result_breakdown'` - Validate Rule 2 (result sub-percentages)
3. `'process_breakdown'` - Validate Rule 3 (process sub-percentages)

---

## 🔍 Error Handling

### **ValidationException Messages:**

**Rule 1 Error:**
```
The sum of percentage_result (60%) and percentage_proses (30%) must equal 100%. Current: 90%
```

**Rule 2 Error:**
```
The sum of result sub-percentages (65%) must equal percentage_result (70%)
```

**Rule 3 Error:**
```
The sum of process sub-percentages (25%) must equal percentage_proses (30%)
```

---

## 📊 Example Data Structure

```php
[
    // Main Percentages (MUST = 100%)
    'percentage_result' => 70.0,
    'percentage_proses' => 30.0,
    
    // Result Sub-Percentages (MUST = 70%)
    'percentage_revenue' => 20.0,
    'percentage_scaling' => 15.0,
    'percentage_datin' => 10.0,
    'percentage_hsi' => 8.0,
    'percentage_wireline' => 7.0,
    'percentage_wifi' => 5.0,
    'percentage_cyc' => 2.0,
    'percentage_cr' => 1.0,
    'percentage_profit' => 1.0,
    'percentage_customer' => 1.0,
    
    // Process Sub-Percentages (MUST = 30%)
    'percentage_maps' => 10.0,
    'percentage_lop' => 10.0,
    'percentage_capability' => 5.0,
    'percentage_cc' => 5.0,
]
```

---

## ⚠️ Important Notes

1. **Decimal Precision**: Percentages menggunakan `decimal(6,3)` - max 3 desimal
2. **Rounding**: Validasi menggunakan `round(..., 3)` untuk menghindari floating point issues
3. **Auto-validation**: Validasi terjadi otomatis saat `save()` atau `update()`
4. **Transaction**: Gunakan DB transaction saat batch update untuk avoid partial saves

---

## 🧪 Testing Examples

### ✅ Valid Data
```php
$liniWaktu = LiniWaktu::create([
    'quartal' => 'Q1',
    'tahun' => 2024,
    'nik_am' => '12345',
    'percentage_result' => 70,
    'percentage_proses' => 30,
    'percentage_revenue' => 70, // All in revenue for simplicity
    'percentage_maps' => 30, // All in maps for simplicity
    // Other percentages = 0
]);
```

### ❌ Invalid Data (Will Throw Exception)
```php
// Case 1: Result + Proses ≠ 100%
$liniWaktu = LiniWaktu::create([
    'percentage_result' => 60,
    'percentage_proses' => 30, // Total = 90% ❌
]);

// Case 2: Sub-percentages don't match
$liniWaktu = LiniWaktu::create([
    'percentage_result' => 70,
    'percentage_proses' => 30,
    'percentage_revenue' => 60, // Only 60% allocated, need 70% ❌
]);
```

---

## 🔧 Maintenance

### Adding New KPIs

Jika ada KPI baru yang ditambahkan:

1. **Update Migration** - Tambah kolom `percentage_new_kpi`
2. **Update Model** - Tambah ke `$fillable`
3. **Update Validation** - Modify `validatePercentages()` method
4. **Update Documentation** - Update file ini

### Database Migration

```php
// Existing migration handles all percentage columns
// See: 2025_11_13_070114_add_percentage_columns_to_lini_waktu.php
```

---

## 🌱 Seeder Implementation

### **Updated Seeder**

`DummyDataSeeder.php` telah diupdate untuk memenuhi semua constraint:

```php
// ✅ Valid percentage distribution
'percentage_result' => 70.000,   // Result: 70%
'percentage_proses' => 30.000,   // Process: 30%
// Total: 100% ✅

// ✅ Result breakdown (total = 70%)
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
// Sub-total: 70% ✅

// ✅ Process breakdown (total = 30%)
'percentage_maps' => 15.000,
'percentage_lop' => 10.000,
'percentage_capability' => 3.000,
'percentage_cc' => 2.000,
// Sub-total: 30% ✅
```

### **Creating New Seeders**

Saat membuat seeder baru yang insert ke `lini_waktu`, pastikan:

1. **Rule 1**: `percentage_result + percentage_proses = 100`
2. **Rule 2**: Sum of result sub-percentages = `percentage_result`
3. **Rule 3**: Sum of process sub-percentages = `percentage_proses`

**Example Template:**
```php
// Define percentages that satisfy all constraints
$resultPercentage = 70.0;
$prosesPercentage = 30.0;

// Result breakdown (must sum to 70%)
$resultBreakdown = [
    'percentage_revenue' => 20.0,
    'percentage_scaling' => 15.0,
    'percentage_datin' => 10.0,
    'percentage_hsi' => 5.0,
    'percentage_wireline' => 5.0,
    'percentage_wifi' => 5.0,
    'percentage_cyc' => 3.0,
    'percentage_cr' => 3.0,
    'percentage_profit' => 2.0,
    'percentage_customer' => 2.0,
];

// Process breakdown (must sum to 30%)
$prosesBreakdown = [
    'percentage_maps' => 15.0,
    'percentage_lop' => 10.0,
    'percentage_capability' => 3.0,
    'percentage_cc' => 2.0,
];

// Verify before inserting
assert(array_sum($resultBreakdown) === $resultPercentage);
assert(array_sum($prosesBreakdown) === $prosesPercentage);
assert($resultPercentage + $prosesPercentage === 100.0);
```

### **Running Seeders**

```bash
# Fresh migration with seeding
php artisan migrate:fresh --seed

# Run specific seeder
php artisan db:seed --class=DummyDataSeeder

# Check seeded data
php artisan tinker
>>> \App\Models\LiniWaktu::first()->validatePercentages();
```

---

## 📝 Related Files

- **Model**: `app/Models/LiniWaktu.php`
- **Custom Rule**: `app/Rules/LiniWaktuPercentageValidation.php`
- **Migration**: `database/migrations/2025_11_13_070114_add_percentage_columns_to_lini_waktu.php`

---

## 🔗 See Also

- [Database Schema](DATABASE_RESTRUCTURE_GUIDE.md)
- [LiniWaktu Model Documentation](app/Models/LiniWaktu.php)
