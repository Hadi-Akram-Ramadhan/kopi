# Cafe System

Sistem manajemen cafe sederhana dengan PHP native.

## Fitur

### Kasir
- Transaksi baru
- Lihat detail transaksi
- Print struk
- Lihat riwayat transaksi
- Lihat notifikasi

### Manajer
- Lihat laporan penjualan
- Export laporan ke Excel
- Export laporan ke PDF
- Lihat statistik penjualan
- Lihat notifikasi

### Admin
- Kelola user
- Kelola menu
- Lihat log aktivitas
- Lihat notifikasi

## Teknologi
- PHP 7.4+
- MySQL 5.7+
- Bootstrap 5
- Chart.js
- TCPDF
- PhpSpreadsheet

## Instalasi

1. Clone repository
```bash
git clone https://github.com/Hadi-Akram-Ramadhan/kopi.git
cd kopi
```

2. Import database
```bash
mysql -u root -p < database/cafe_db.sql
```

3. Konfigurasi database
Edit file `config/database.php` sesuai dengan konfigurasi database lo.

4. Install dependencies
```bash
composer install
```

5. Jalankan di web server
```bash
php -S localhost:8000
```

## Login Default

### Admin
- Username: admin
- Password: admin123

### Kasir
- Username: kasir1
- Password: kasir1

### Manajer
- Username: manajer1
- Password: manajer1

## Struktur Folder
```
cafe-system/
├── config/
│   ├── auth.php
│   └── database.php
├── database/
│   └── cafe_db.sql
├── pages/
│   ├── admin/
│   ├── kasir/
│   ├── manajer/
│   └── shared/
├── vendor/
├── composer.json
├── index.php
├── login.php
├── logout.php
└── README.md
```

## Kontribusi
1. Fork repository
2. Buat branch baru (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## Lisensi
Distributed under the MIT License. See `LICENSE` for more information.
