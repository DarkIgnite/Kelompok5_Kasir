# TODO Backend & Security — KasirAja

Tolong implementasikan fitur-fitur yang masih kurang di bawah ini. Pastikan validasi dilakukan di **Backend (Controller/Policy)** dan bukan hanya menyembunyikan tombol di Frontend (Blade).

## 1. Role-Based Edit & Delete Restrictions (CRUD Pengguna)
**File terkait:** `app/Models/User.php`, `app/Http/Controllers/UserController.php`, `resources/views/pengguna/index.blade.php`

**Hierarki Role:** `owner` > `admin` / `manager` > `kasir`

**Aturan Wajib (Backend & Frontend):**
- **Tidak bisa hapus diri sendiri:** User yang sedang login (`Auth::id()`) tidak boleh menghapus akunnya sendiri.
- **Proteksi Atasan / Owner:**
  - `admin` / `manager` **tidak boleh** mengedit atau menghapus akun dengan role `owner`.
  - `admin` / `manager` tidak boleh mengubah role user lain menjadi `owner` (privilege escalation).
  - Hanya `owner` yang boleh mengedit/menghapus `admin`/`manager` dan `kasir`.
- **Enforcement:**
  - Di `UserController@update` dan `UserController@destroy`: cek hierarki role sebelum eksekusi. Jika tidak berhak, kembalikan `abort(403)` atau redirect back dengan flash error.
  - Di Blade (`pengguna/index.blade.php`): sembunyikan atau disable tombol Edit/Hapus sesuai hak akses user yang sedang login.
- **Cek Bug Password:** Di `UserController@update`, update kolom `password` **hanya jika** `$request->filled('password')`, dan pastikan tidak terjadi *double-hash* jika model `User.php` sudah memakai cast `'password' => 'hashed'`.

## 2. Filter Rentang Waktu Dashboard (Hari / Minggu / Bulan)
**File terkait:** `app/Http/Controllers/DashboardController.php`, `resources/views/dashboard.blade.php`

**Aturan Wajib:**
- Endpoint `route('dashboard.data')` harus menerima query parameter `?start=YYYY-MM-DD&end=YYYY-MM-DD&per=hari|minggu|bulan`.
- Filter waktu harus meng-update:
  1. Grafik batang **Tren Penjualan** (`monthlySales` dan `chartPills`).
  2. **3 KPI Card pertama** (`total_penjualan`, `total_terjual`, `total_transaksi`) sesuai rentang tanggal terpilih.
- **Peringatan Stok** (card ke-4) tetap menampilkan kondisi stok gudang saat ini (tidak ikut terfilter tanggal).
- Terapkan batas aman (maksimal 150 batang) agar mode `hari`/`minggu` tidak bikin grafik error/penuh.

## 3. Cleanup Sistem Toast Notification
**File terkait:** `resources/views/dashboard.blade.php` (dan layout/CRUD lain jika ada)

**Aturan Wajib:**
- Hapus atau rapikan fungsi `showToast()` yang memanggil `#toast-container` yang sudah tidak terpakai, atau seragamkan notifikasi menggunakan flash alert standar yang sudah ada di halaman CRUD.