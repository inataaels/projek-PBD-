Cara pakai
1. Import script database BukuKita Anda, lalu import patch.sql.
2. Salin folder ini ke htdocs (XAMPP) atau www (Laragon), mis. htdocs/bukukita.
3. Sesuaikan user/password MySQL di config.php.
4. Buka http://localhost/bukukita/login.php
   Login awal: admin_bukukita / contoh_hash_admin, kasir01 / contoh_hash_kasir, gudang01 / contoh_hash_gudang
   (password otomatis di-hash saat pertama login)
Alur: Data Master -> Pengadaan -> Penerimaan (stok bertambah) -> Penjualan / Retur (stok berkurang)
