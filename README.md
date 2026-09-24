# Sistem Audit IT POLBAN

Sistem Audit IT POLBAN adalah aplikasi berbasis web yang dikembangkan untuk membantu pelaksanaan dan pengelolaan proses audit teknologi informasi di lingkungan Politeknik Negeri Bandung (POLBAN).

Aplikasi ini dirancang untuk mendukung proses audit mulai dari pengelolaan pengguna, framework audit, periode audit, pertanyaan audit, pelaksanaan audit, penilaian, temuan, tindak lanjut, laporan hasil audit, monitoring, hingga pengarsipan hasil audit.

---

# Tentang Sistem

Sistem Audit IT POLBAN merupakan sistem informasi berbasis web yang digunakan untuk membantu proses audit teknologi informasi secara terstruktur dan terdokumentasi.

Sistem mendukung proses audit yang meliputi:

1. Pengelolaan akun pengguna.
2. Pengelolaan framework audit.
3. Pengelolaan periode audit.
4. Pengelolaan template pertanyaan audit.
5. Pembuatan dan konfigurasi audit.
6. Pelaksanaan audit oleh auditor.
7. Pengisian jawaban audit.
8. Penilaian hasil audit.
9. Pengelolaan temuan audit.
10. Pengelolaan tindak lanjut.
11. Monitoring proses audit.
12. Pengelolaan laporan hasil audit.
13. Pengarsipan hasil audit.
14. Pencatatan aktivitas pengguna.

---

# Tujuan Sistem

Sistem dikembangkan untuk membantu proses audit teknologi informasi agar dapat dilakukan secara lebih terstruktur dan terdokumentasi.

Sistem diharapkan dapat membantu pengguna dalam:

- Mengelola data pengguna.
- Mengelola framework audit.
- Mengelola periode audit.
- Mengelola pertanyaan audit.
- Melaksanakan proses audit.
- Mengelola jawaban dan penilaian audit.
- Mencatat temuan audit.
- Mengelola tindak lanjut.
- Memantau perkembangan audit.
- Mengelola laporan hasil audit.
- Mengarsipkan hasil audit.
- Mencatat aktivitas pengguna.

---

# Fitur Utama

## Manajemen Akun

Digunakan untuk mengelola akun pengguna dan role pengguna dalam sistem.

## Manajemen Framework

Digunakan untuk mengelola framework yang digunakan dalam proses audit.

## Manajemen Periode

Digunakan untuk mengelola periode pelaksanaan audit.

## Template Pertanyaan Audit

Digunakan untuk mengelola pertanyaan yang dapat digunakan dalam proses audit.

## Pelaksanaan Audit

Digunakan oleh auditor untuk melaksanakan proses audit berdasarkan audit yang telah dikonfigurasi.

## Penilaian Audit

Digunakan untuk melakukan penilaian terhadap jawaban audit.

## Temuan Audit

Digunakan untuk mencatat dan mengelola temuan yang ditemukan selama proses audit.

## Tindak Lanjut

Digunakan untuk mengelola tindak lanjut terhadap temuan audit.

## Monitoring Audit

Digunakan untuk memantau proses audit dan perkembangan tindak lanjut.

## Laporan Hasil Audit

Digunakan untuk melihat dan mengelola laporan hasil audit.

## Arsip Audit

Digunakan untuk menyimpan dan mengakses hasil audit yang telah selesai.

## Activity Log

Digunakan untuk mencatat aktivitas tertentu yang dilakukan oleh pengguna dalam sistem.

---

# Role Pengguna

Sistem memiliki empat role pengguna utama.

## 1. Administrator

Administrator bertanggung jawab terhadap pengelolaan data dan konfigurasi sistem.

Fungsi utama:

- Manajemen akun pengguna.
- Manajemen framework.
- Manajemen periode.
- Manajemen template pertanyaan audit.
- Pengelolaan data pendukung sistem.

## 2. Auditor

Auditor bertanggung jawab terhadap pelaksanaan proses audit.

Fungsi utama:

- Melihat audit yang ditugaskan.
- Melaksanakan proses audit.
- Mengisi jawaban audit.
- Melakukan penilaian.
- Mengelola temuan audit.
- Menyelesaikan proses audit sesuai konfigurasi.

## 3. Auditee

Auditee merupakan pihak yang menjadi objek audit.

Fungsi utama:

- Melihat audit yang ditujukan kepada auditee.
- Memberikan jawaban atau tanggapan terhadap pertanyaan audit.
- Melihat temuan audit.
- Memberikan tindak lanjut terhadap temuan.
- Mengelola informasi tindak lanjut sesuai kewenangan.

## 4. Pimpinan

Pimpinan digunakan untuk memantau proses dan hasil audit.

Fungsi utama:

- Melihat dashboard audit.
- Melihat laporan hasil audit.
- Melihat detail hasil audit.
- Memantau tindak lanjut.
- Melihat arsip audit.
- Melihat aktivitas tertentu dalam sistem.

---

# Teknologi yang Digunakan

| Teknologi | Keterangan |
|---|---|
| PHP | Bahasa pemrograman utama |
| CodeIgniter 4 | Framework aplikasi |
| MySQL | Database |
| Composer | Dependency management |
| HTML | Struktur halaman |
| CSS | Tampilan antarmuka |
| JavaScript | Interaksi halaman |
| Git | Version control |
| GitHub | Repository dan kolaborasi |

---

# Persyaratan Sistem

Sebelum menjalankan aplikasi, pastikan komputer telah memiliki:

- PHP 8.3 atau lebih baru.
- Composer.
- MySQL atau MariaDB.
- Git.
- XAMPP atau Laragon.
- Web browser.

## Memeriksa PHP

```bash
php -v
```

## Memeriksa Composer

```bash
composer -V
```

## Memeriksa Git

```bash
git --version
```

Pastikan MySQL juga sedang berjalan melalui XAMPP atau Laragon.

---

# Struktur Project

Struktur utama project:

```text
sistem_audit_ti_polban/
│
├── app/
│   ├── Config/
│   ├── Controllers/
│   ├── Database/
│   ├── Filters/
│   ├── Models/
│   └── Views/
│
├── public/
├── tests/
├── writable/
│
├── .env.example
├── .gitignore
├── composer.json
├── composer.lock
├── LICENSE
├── README.md
├── spark
└── ...
```

## Folder dan File Penting

| Folder/File | Fungsi |
|---|---|
| `app/` | Source code utama aplikasi |
| `app/Controllers/` | Controller aplikasi |
| `app/Models/` | Model dan pengolahan data |
| `app/Views/` | Tampilan aplikasi |
| `app/Config/` | Konfigurasi dan routing |
| `public/` | Folder public aplikasi |
| `tests/` | File pengujian |
| `writable/` | Cache, log, dan file yang dihasilkan aplikasi |
| `.env.example` | Template konfigurasi environment |
| `composer.json` | Konfigurasi dependency |
| `composer.lock` | Versi dependency |
| `spark` | Command Line Interface CodeIgniter |
| `README.md` | Dokumentasi project |

---

# Instalasi dan Menjalankan Project

Bagian ini menjelaskan cara mendapatkan project dari GitHub hingga aplikasi dapat dijalankan secara lokal.

## 1. Clone Repository

Masuk ke folder tempat project akan disimpan.

### Laragon

```powershell
cd C:\laragon\www
```

### XAMPP

```powershell
cd C:\xampp\htdocs
```

Kemudian clone repository:

```bash
git clone https://github.com/syahrulbachri/sistem_audit_ti_polban.git
```

Setelah selesai, folder project akan dibuat:

```text
sistem_audit_ti_polban
```

---

## 2. Masuk ke Folder Project

```bash
cd sistem_audit_ti_polban
```

Periksa isi folder.

Pada PowerShell:

```powershell
dir
```

Pastikan terdapat file seperti:

```text
app
public
tests
writable
composer.json
composer.lock
spark
README.md
.env.example
```

---

## 3. Install Dependency

Project menggunakan Composer untuk mengelola dependency.

Jalankan:

```bash
composer install
```

Perintah tersebut akan membaca:

```text
composer.json
composer.lock
```

dan menginstal dependency yang dibutuhkan.

Setelah berhasil, folder:

```text
vendor/
```

akan tersedia.

> Folder `vendor` tidak perlu dibuat secara manual.

---

## 4. Membuat File .env

Repository menyediakan:

```text
.env.example
```

File tersebut digunakan sebagai template konfigurasi environment.

### Windows PowerShell

```powershell
Copy-Item .env.example .env
```

### Windows CMD

```cmd
copy .env.example .env
```

### Linux/macOS

```bash
cp .env.example .env
```

Setelah berhasil, akan terdapat:

```text
.env
.env.example
```

File `.env` digunakan untuk konfigurasi lokal komputer.

---

## 5. Konfigurasi Database

Buka file:

```text
.env
```

Kemudian pastikan konfigurasi database sesuai dengan database lokal.

Konfigurasi default:

```ini
CI_ENVIRONMENT = development

database.default.hostname = localhost
database.default.database = db_audit_it_polban
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
```

Jika konfigurasi MySQL berbeda, sesuaikan:

```text
hostname
database
username
password
port
```

Contoh jika MySQL menggunakan password:

```ini
database.default.password = password_database
```

---

## 6. Membuat Database

Database yang digunakan oleh aplikasi adalah:

```text
db_audit_it_polban
```

Database dapat dibuat melalui phpMyAdmin.

Buka:

```text
http://localhost/phpmyadmin
```

Kemudian buat database dengan nama:

```text
db_audit_it_polban
```

Atau gunakan SQL:

```sql
CREATE DATABASE db_audit_it_polban;
```

---

## 7. Import Database

Setelah database dibuat, import file:

```text
db_audit_it_polban.sql
```

File SQL tersebut digunakan untuk membuat struktur tabel dan data yang dibutuhkan aplikasi.

### Menggunakan phpMyAdmin

1. Buka phpMyAdmin.
2. Pilih database `db_audit_it_polban`.
3. Pilih menu **Import**.
4. Pilih file `db_audit_it_polban.sql`.
5. Jalankan proses import.
6. Tunggu hingga proses selesai.
7. Pastikan tabel database telah berhasil dibuat.

> File `db_audit_it_polban.sql` disediakan pada paket pengumpulan project di Google Drive.

---

## 8. Menjalankan Aplikasi

Pastikan:

- PHP sudah tersedia.
- Composer sudah tersedia.
- MySQL sedang berjalan.
- File `.env` sudah dibuat.
- Konfigurasi `.env` sudah benar.
- Database sudah dibuat.
- Database sudah diimport.
- `composer install` sudah berhasil.

Kemudian jalankan:

```bash
php spark serve
```

Jika berhasil, CodeIgniter akan menjalankan development server.

---

## 9. Membuka Aplikasi

Buka browser dan akses:

```text
http://localhost:8080
```

Aplikasi Sistem Audit IT POLBAN akan ditampilkan.

Untuk menghentikan server:

```text
Ctrl + C
```

---

# Informasi Project

**Nama Project:** Sistem Audit IT POLBAN

**Framework:** CodeIgniter 4

**Bahasa Pemrograman:** PHP

**Database:** MySQL

**Version Control:** Git

**Repository:** GitHub

**Environment:** XAMPP / Laragon

---