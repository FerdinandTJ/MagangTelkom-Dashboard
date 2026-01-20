# Panduan Import Sheet NKI {year}

## 📋 Struktur Sheet NKI {year}

Sheet ini digunakan untuk mengisi **Achievement** dan **NKI Adjustment** untuk setiap Account Manager.

### Row 1-2: Data Persentase Threshold

| Row | Kolom | Index | Field Database | Contoh Nilai |
|-----|-------|-------|----------------|--------------|
| 1 | G | `$row[6]` | percentage_result | 0.75 (75%) |
| 1 | AK | `$row[36]` | percentage_proses | 0.60 (60%) |
| 2 | G | `$row[6]` | percentage_revenue | 0.85 |
| 2 | J | `$row[9]` | percentage_scaling | 0.80 |
| 2 | M | `$row[12]` | percentage_datin | 0.70 |
| 2 | P | `$row[15]` | percentage_hsi | 0.75 |
| 2 | S | `$row[18]` | percentage_wireline | 0.75 |
| 2 | V | `$row[21]` | percentage_wifi | 0.75 |
| 2 | Y | `$row[24]` | percentage_cyc | 0.75 |
| 2 | AB | `$row[27]` | percentage_cr | 0.75 |
| 2 | AE | `$row[30]` | percentage_profit | 0.75 |
| 2 | AH | `$row[33]` | percentage_customer | 0.75 |
| 2 | AK | `$row[36]` | percentage_maps | 0.75 |
| 2 | AN | `$row[39]` | percentage_lop | 0.75 |
| 2 | AQ | `$row[42]` | percentage_capability | 0.75 |
| 2 | AT | `$row[45]` | percentage_cc | 0.75 |

### Row 3: Header Kolom
Baris ini di-skip saat import.

### Row 4+: Data Per Account Manager

| Kolom | Index | Tabel | Field | Tipe Data | Keterangan |
|-------|-------|-------|-------|-----------|------------|
| **A** | `$row[0]` | - | Quartal | String | Q1/Q2/Q3/Q4 |
| **B** | `$row[1]` | - | NIK AM | String | **WAJIB**, validasi ke `account_managers` |
| **C** | `$row[2]` | - | Nama AM | String | Validasi nama sesuai NIK |
| **D** | `$row[3]` | account_manager_company | segment | String | Update segment (HQ-TWS, POTS, dll) |
| **E** | `$row[4]` | - | Witel | String | Validasi nama witel |
| **F** | `$row[5]` | - | Total Target Revenue | Decimal | Validasi sum target |
| **G** | `$row[6]` | - | Total Realisasi Revenue | Decimal | Validasi sum realisasi |
| **H** | `$row[7]` | lini_waktu_target | ach_revenue_plan | Decimal | Achievement Revenue |
| **I** | `$row[8]` | - | Total Target Scaling | Decimal | Validasi sum target |
| **J** | `$row[9]` | - | Total Realisasi Scaling | Decimal | Validasi sum realisasi |
| **K** | `$row[10]` | lini_waktu_target | ach_scaling | Decimal | Achievement Scaling |
| **L** | `$row[11]` | target_account_m | t_datin | Decimal | Target Datin |
| **M** | `$row[12]` | lini_waktu_target | r_datin | Decimal | Realisasi Datin |
| **N** | `$row[13]` | lini_waktu_target | ach_sales_datin | Decimal | Achievement Datin |
| **O** | `$row[14]` | target_account_m | t_hsi | Decimal | Target HSI |
| **P** | `$row[15]` | lini_waktu_target | r_hsi | Decimal | Realisasi HSI |
| **Q** | `$row[16]` | lini_waktu_target | ach_hsi | Decimal | Achievement HSI |
| **R** | `$row[17]` | target_account_m | t_wireline | Decimal | Target Wireline |
| **S** | `$row[18]` | lini_waktu_target | r_wireline | Decimal | Realisasi Wireline |
| **T** | `$row[19]` | lini_waktu_target | ach_wireline | Decimal | Achievement Wireline |
| **U** | `$row[20]` | target_account_m | t_wifi | Decimal | Target Wifi |
| **V** | `$row[21]` | lini_waktu_target | r_wifi | Decimal | Realisasi Wifi |
| **W** | `$row[22]` | lini_waktu_target | ach_wifi | Decimal | Achievement Wifi |
| **X** | `$row[23]` | target_account_m | t_cyc | Decimal | Target CYC |
| **Y** | `$row[24]` | lini_waktu_target | r_cyc | Decimal | Realisasi CYC |
| **Z** | `$row[25]` | lini_waktu_target | ach_cyc | Decimal | Achievement CYC |
| **AA** | `$row[26]` | target_account_m | t_cr | Decimal | Target CR |
| **AB** | `$row[27]` | lini_waktu_target | r_cr | Decimal | Realisasi CR |
| **AC** | `$row[28]` | lini_waktu_target | ach_cr | Decimal | Achievement CR |
| **AD** | `$row[29]` | target_account_m | t_profit | Decimal | Target Profit |
| **AE** | `$row[30]` | lini_waktu_target | r_profit | Decimal | Realisasi Profit |
| **AF** | `$row[31]` | lini_waktu_target | ach_profit | Decimal | Achievement Profit |
| **AG** | `$row[32]` | target_account_m | t_nps | Decimal | Target NPS |
| **AH** | `$row[33]` | lini_waktu_target | r_nps | Decimal | Realisasi NPS |
| **AI** | `$row[34]` | lini_waktu_target | ach_nps | Decimal | Achievement NPS |
| **AJ** | `$row[35]` | target_account_m | t_maps | Decimal | Target MAPS |
| **AK** | `$row[36]` | lini_waktu_target | r_maps | Decimal | Realisasi MAPS |
| **AL** | `$row[37]` | lini_waktu_target | ach_maps | Decimal | Achievement MAPS |
| **AM** | `$row[38]` | target_account_m | t_lop | Decimal | Target LOP |
| **AN** | `$row[39]` | lini_waktu_target | r_lop | Decimal | Realisasi LOP |
| **AO** | `$row[40]` | lini_waktu_target | ach_lop | Decimal | Achievement LOP |
| **AP** | `$row[41]` | target_account_m | t_capability | Decimal | Target Capability |
| **AQ** | `$row[42]` | lini_waktu_target | r_capability | Decimal | Realisasi Capability |
| **AR** | `$row[43]` | lini_waktu_target | ach_capability | Decimal | Achievement Capability |
| **AS** | `$row[44]` | target_account_m | t_cc | Decimal | Target CC |
| **AT** | `$row[45]` | lini_waktu_target | r_cc | Decimal | Realisasi CC |
| **AU** | `$row[46]` | lini_waktu_target | ach_cc | Decimal | Achievement CC |
| **AV** | `$row[47]` | lini_waktu_target | **ach_result** | Decimal | ⭐ **PENTING: Achievement Result** |
| **AW** | `$row[48]` | lini_waktu_target | **ach_proses** | Decimal | ⭐ Achievement Proses |
| **AX** | `$row[49]` | lini_waktu_target | **nki_adjustment** | Decimal | ⭐ NKI Adjustment |

---

## 🔄 Cara Kerja Import

### 1. Upload File Excel
- Menu: **Performance AM** → **Data Import Performance**
- Pilih Quarter: Q1/Q2/Q3/Q4
- Upload file: **template_upload.xlsx**

### 2. Proses Otomatis
Sistem akan membaca 3 sheet secara berurutan:
1. **Region and Witel** - Data region dan witel
2. **TWS {year}** - Data target dan realisasi per company
3. **NKI {year}** - Data achievement dan NKI adjustment per AM ← Sheet ini

### 3. Validasi
- **NIK AM** (kolom B) harus ada di tabel `account_managers`
- **Nama AM** harus sesuai dengan NIK
- **Witel** harus sesuai dengan data AM
- **lini_waktu** untuk NIK, quartal, tahun harus sudah ada
- **lini_waktu_target** (pivot) harus sudah ada

### 4. Update Data

**Setiap row Excel = data untuk 1 NIK AM (kolom B)**

**a. Update Segment:**
- Kolom **D** (segment) → update ke semua record `account_manager_company` untuk NIK AM di kolom B

**b. Update Target (target_account_m):**
- Kolom L, O, R, U, X, AA, AD, AG, AJ, AM, AP, AS → update target fields
- Update ke `target_account_m` yang terkait dengan **lini_waktu_target terbaru** untuk NIK AM di kolom B

**c. Update Realisasi & Achievement (lini_waktu_target):**
- Kolom M, P, S, V, Y, AB, AE, AH, AK, AN, AQ, AT → realisasi
- Kolom H, K, N, Q, T, W, Z, AC, AF, AI, AL, AO, AR, AU → achievement per parameter
- Kolom **AV, AW, AX** → **ach_result, ach_proses, nki_adjustment**
- **UPDATE hanya 1 lini_waktu_target** (yang dibuat paling terakhir) untuk NIK AM di kolom B

**d. Update Persentase Threshold (lini_waktu):**
- Row 1-2 → update 16 kolom percentage untuk seluruh quartal

---

## ⚙️ Logika Pemilihan lini_waktu_target

**Untuk setiap NIK AM (kolom B):**
1. Cari `lini_waktu` dengan `nik_am = NIK dari kolom B` + `quartal` + `tahun`
2. Cari **lini_waktu_target** dengan `lini_waktu_id` dari step 1
3. Ambil **HANYA 1 record** yang **created_at paling baru** (latest)
4. Update achievement fields ke record tersebut

**Contoh:**
- NIK **980080** (MAHARANI) punya 1 lini_waktu_target → update yang itu
- NIK **810057** (VINO) punya 9 lini_waktu_target → update **yang paling baru saja**

---

## ⭐ Field Kritis untuk Achievement

### 1. ach_result (Kolom AV / $row[47])
**Fungsi:** Menentukan apakah AM **ACHIEVE** atau **NOT ACHIEVE** untuk Result.

**Logika:**
- Setiap row Excel untuk 1 NIK AM
- Data masuk ke **lini_waktu_target** yang dibuat paling terakhir untuk NIK tersebut
- Perhitungan achievement di backend: `SUM(lini_waktu_target.ach_result)` untuk semua lini_waktu_target milik AM
- AM ACHIEVE jika: `SUM >= lini_waktu.percentage_result`

**Contoh MAHARANI:**
- NIK: 980080
- Punya **1 lini_waktu_target** (1 company)
- Threshold: `percentage_result = 0.75`
- Jika kolom AV diisi: `0.75` → lini_waktu_target.ach_result = 0.75
- SUM = 0.75 (karena hanya 1 record)
- 0.75 >= 0.75 → **ACHIEVE** ✅

**Contoh VINO:**
- NIK: 810057
- Punya **9 lini_waktu_target** (9 companies)
- Yang terupdate: **hanya 1 record** (yang paling baru)
- Jika kolom AV diisi: `0.75` → 1 record dapat ach_result = 0.75, 8 lainnya tetap 0
- SUM = 0.75 + 0 + 0 + 0 + 0 + 0 + 0 + 0 + 0 = 0.75
- 0.75 >= 0.75 → **ACHIEVE** ✅

### 2. ach_proses (Kolom AW / $row[48])
**Fungsi:** Menentukan apakah AM ACHIEVE untuk Proses.

**Perhitungan:**
```
AM dinyatakan ACHIEVE jika:
SUM(lini_waktu_target.ach_proses) >= lini_waktu.percentage_proses
```

### 3. nki_adjustment (Kolom AX / $row[49])
**Fungsi:** Nilai NKI final setelah adjustment.

**Perhitungan:**
```
NKI >100% jika: AVG(nki_adjustment) >= 100
NKI <100% jika: AVG(nki_adjustment) < 100
```

---

## 🐛 Kesalahan Umum

### ❌ AM tidak achieve padahal ach_result sudah diisi
**Penyebab:**
- Row Excel hanya update **1 lini_waktu_target** (yang terbaru) untuk NIK tersebut
- Jika AM punya banyak lini_waktu_target, yang lain tetap 0
- SUM semua ach_result bisa kurang dari threshold

**Contoh Masalah:**
- AM punya 5 companies (5 lini_waktu_target)
- Sheet NKI isi kolom AV = 0.10 untuk NIK AM ini
- Yang terupdate: 1 record = 0.10, 4 lainnya = 0
- SUM = 0.10 + 0 + 0 + 0 + 0 = 0.10
- Threshold = 0.75
- 0.10 < 0.75 → **NOT ACHIEVE** ❌

**Solusi:**
- Pastikan nilai di kolom AV **CUKUP TINGGI** untuk menutupi semua lini_waktu_target
- Atau jika butuh update ke semua lini_waktu_target, perlu modifikasi code
- Atau setiap AM hanya boleh punya 1 lini_waktu_target agar konsisten

---

## 🐛 Bug Fix: Update ke SEMUA lini_waktu_target

**Status:** ❌ **TIDAK DITERAPKAN** (sesuai requirements)

**Logika Saat Ini:**
- 1 row Excel = 1 NIK AM
- Update **HANYA 1 lini_waktu_target** (yang created_at paling baru)
- Jika AM handle multiple companies, hanya 1 yang terupdate

**Alternatif (jika diperlukan):**
Jika ingin update **SEMUA lini_waktu_target** untuk 1 NIK AM, perlu ubah code dari:
```php
$liniWaktuTarget = LiniWaktuTarget::where('lini_waktu_id', $liniWaktu->id)
    ->orderBy('created_at', 'desc')
    ->first();
```

Menjadi:
```php
$liniWaktuTargets = LiniWaktuTarget::where('lini_waktu_id', $liniWaktu->id)->get();
foreach ($liniWaktuTargets as $lwt) {
    $lwt->ach_result = $row[47];
    $lwt->save();
}
```

**Tapi ini TIDAK digunakan karena requirement adalah update 1 record terbaru saja.**

---

## 🐛 Bug Yang Sudah Diperbaiki

### ❌ Data masuk ke lini_waktu_target yang salah NIK
**Masalah:** Code tidak validasi NIK dengan benar

**Solusi:** 
- Sekarang code **SELALU** ambil NIK dari **kolom B** (`$row[1]`)
- Validasi NIK ke tabel `account_managers`
- Cari lini_waktu dengan `nik_am = NIK dari kolom B`
- Pastikan data masuk ke lini_waktu_target yang sesuai NIK

---

## 📝 Contoh Data Excel

### Row 1: Threshold Result & Proses
```
A    B    C    D    E    F    G      ... AK     ...
-    -    -    -    -    -    0.75   ... 0.60   ...
```

### Row 2: Threshold Lainnya
```
A    B    C    D    E    F    G      J      M      P      S      V     ...
-    -    -    -    -    -    0.85   0.80   0.70   0.75   0.75   0.75  ...
```

### Row 3: Header
```
Quartal | NIK AM | Nama AM | Segment | Witel | ... | Ach Result | Ach Proses | NKI
```

### Row 4+: Data AM (contoh MAHARANI)
```
Q1 | 980080 | MAHARANI SARASTIKA BODJAWATI | HQ-TWS | MATARAM | ... | 0.75 | 0.60 | 95.5
```

---

## ✅ Checklist untuk Upload Sukses

- [ ] Sheet bernama **"NKI 2026"** ada di Excel
- [ ] Row 1-2 berisi data persentase threshold
- [ ] Row 3 adalah header
- [ ] Row 4+ berisi data per AM dengan NIK valid
- [ ] Kolom AV (ach_result) diisi dengan nilai ≥ threshold agar achieve
- [ ] Kolom AW (ach_proses) diisi
- [ ] Kolom AX (nki_adjustment) diisi
- [ ] Semua AM yang ingin achieve punya nilai di kolom AV ≥ 0.75

---

## 🔍 Troubleshooting

### AM tidak achieve padahal seharusnya achieve
**Penyebab:**
1. Kolom AV (ach_result) tidak diisi atau 0
2. NIK AM tidak ditemukan di database
3. Data lini_waktu belum ada untuk AM tersebut
4. Data lini_waktu_target belum ada

**Solusi:**
1. Pastikan kolom AV untuk AM tersebut diisi ≥ threshold (0.75)
2. Cek NIK di Excel sesuai dengan database
3. Import ulang sheet TWS terlebih dahulu
4. Re-upload file Excel

### Data tidak masuk setelah upload
**Cek:**
1. Log file: `storage/logs/laravel.log`
2. Activity Log di halaman upload untuk melihat errors
3. Pastikan NIK dan Witel valid

---

## 💡 Tips

1. **Backup data** sebelum import ulang
2. **Test dengan 1 AM dulu** sebelum import semua
3. **Cek activity log** setelah upload untuk melihat status
4. **Re-upload** jika ada data yang tidak masuk (akan update data existing)
5. Untuk **manual update**, gunakan Artisan Tinker atau query SQL langsung ke database
