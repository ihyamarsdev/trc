# TRC (Your Project Name)

![License](https://img.shields.io/badge/license-MIT-blue.svg)

## Deskripsi

TRC adalah proyek yang dirancang untuk membantu sistem administrasi sekolah yang dibawahi oleh The Rasyidu Center. Proyek ini bertujuan untuk memudahkan manajemen, keuangan dan memberikan solusi untuk sistem yang sudah ada.

## Fitur

-   **Fitur CRUD**: Melakukan CRUD berdasarkan data sekolah
-   **Fitur Kalkulasi Keuangan**: Melakukan Kalkulasi Keuangan Finance.
-   **Fitur Export File**: Melakukan Export File excel dan Doc.

## Prerequisites

Sebelum memulai, pastikan Anda memiliki hal-hal berikut:

-   [Node.js](https://nodejs.org/) (versi terbaru)
-   [NPM](https://www.npmjs.com/) (versi terbaru)
-   [Composer](https://getcomposer.org/) (versi terbaru)
-   [PHP](https://www.php.net/) (versi 8)
-   [Laravel](https://laravel.com/docs/11.x/releases) (versi 11)
-   [Filament](https://filamentphp.com/) (versi 3.0)

## Instalasi

Ikuti langkah-langkah berikut untuk menginstal dan menjalankan proyek ini secara lokal:

1. **Clone repositori:**
    ```bash
    git clone https://github.com/ihyamarsdev/trc.git
    ```
2. **Masuk ke direktori proyek:**
    ```
    cd trc
    ```
3. **Install Dependencies:**
    ```
    npm install
    composer install
    ```
4. **Copy .env.example:**
    ```
    cp .env.example .env
    ```
5. **Tambahkan Database Jika Menggunakan Selain Sqlite:**
    ```
    DB_CONNECTION=nama-connection-db
    # DB_HOST=host-db
    # DB_PORT=pot-db
    # DB_DATABASE=nama-db
    # DB_USERNAME=username-db
    # DB_PASSWORD=password-db
    ```
6. **Lakukan Migrasi:**
    ```
    php artisan migrate
    ```
7. **Jalankan Aplikasi:**
    ```
    php artisan serve
    ```

## Kontribusi

Kontribusi sangat diterima! Silakan ikuti langkah-langkah berikut untuk berkontribusi:

1. Fork repositori ini.
2. Buat cabang baru (git checkout -b nama-branch).
3. Lakukan perubahan dan commit (git commit -m 'Add some nama-feature').
4. Push ke cabang (git push origin feature/nama-branch).
5. Buat Pull Request.

## Lisensi

Proyek ini dilisensikan di bawah **MIT License**.

## Kontak

Ihya Muhammad Adam R - @ihyamarsdev - ihyamars@gmail.com

Link Proyek: https://github.com/ihyamarsdev/trc
