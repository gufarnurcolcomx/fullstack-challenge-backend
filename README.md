# Sistem Absensi - Laravel 10 + Sanctum + MySQL

Aplikasi backend untuk sistem absensi berbasis Laravel.  
Dibuat oleh **Gufron Ardi Nugroho**

GitHub: https://github.com/gufarnurcolcomx  
Email: gufarnur@gmail.com

## Fitur

- Register dan Login menggunakan Laravel Sanctum
- Clock-in dan Clock-out absensi harian
- Manajemen data Karyawan
- Manajemen data Departemen
- Dashboard dan Profil Pengguna
- Database menggunakan MySQL
- Tidak menggunakan seeder (user bisa registrasi langsung)
- Tidak memerlukan `php artisan storage:link`

## Instalasi

1. Clone repository
git clone https://github.com/gufarnurcolcomx/fullstack-challenge-backend.git
cd back_end

2. Install dependency Laravel
composer install

3. Salin file .env dan atur konfigurasi
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=back_end
DB_USERNAME=root
DB_PASSWORD=

4. Generate app key dan migrasi database
php artisan key:generate
php artisan migrate

5. php artisan serve
php artisan serve

API Endpoint
Autentikasi
| Method | Endpoint            | Keterangan                | Auth  |
| ------ | ------------------- | ------------------------- | ----- |
| POST   | /api/register       | Registrasi akun pengguna  | Tidak |
| POST   | /api/login          | Login dan dapatkan token  | Tidak |
| POST   | /api/logout         | Logout dan hapus token    | Ya    |
| GET    | /api/dashboard      | Tampilkan data dashboard  | Ya    |
| GET    | /api/profile        | Tampilkan profil pengguna | Ya    |
| POST   | /api/profile/update | Update data profil        | Ya    |

Absensi
Base URL: /api/absensi
| Method | Endpoint           | Keterangan                | Auth  |
| ------ | ------------------ | ------------------------- | ----- |
| GET    | /search-perusahaan | Cari nama perusahaan      | Tidak |
| GET    | /search-employee   | Cari karyawan             | Tidak |
| POST   | /absen/masuk       | Clock-in absensi          | Tidak |
| POST   | /absen/keluar      | Clock-out absensi         | Tidak |
| GET    | /absen/status      | Cek status absen hari ini | Tidak |

Karyawan
Base URL: /api/employees
| Method | Endpoint     | Keterangan            | Auth |
| ------ | ------------ | --------------------- | ---- |
| GET    | /            | Daftar semua karyawan | Ya   |
| GET    | /{id}        | Detail karyawan       | Ya   |
| POST   | /store       | Tambah karyawan       | Ya   |
| PUT    | /update/{id} | Ubah data karyawan    | Ya   |
| DELETE | /delete/{id} | Hapus karyawan        | Ya   |

Departemen
Base URL: /api/departements
| Method | Endpoint     | Keterangan              | Auth |
| ------ | ------------ | ----------------------- | ---- |
| GET    | /            | Daftar semua departemen | Ya   |
| GET    | /{id}        | Detail departemen       | Ya   |
| POST   | /store       | Tambah departemen       | Ya   |
| PUT    | /update/{id} | Ubah data departemen    | Ya   |
| DELETE | /delete/{id} | Hapus departemen        | Ya   |


Stack Teknologi
 -   Laravel 10
 -   Laravel Sanctum
 -   MySQL
 -   RESTful API
 -   php >8.2