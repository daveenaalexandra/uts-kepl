# PANDUAN LENGKAP UTS KEPL — Laravel REST API + Vue.js + GitHub Actions + Docker + Overleaf

> **Baca mulai Tahap 0 dan ikuti berurutan.** Dokumen ini dimaksudkan agar tugas bisa dikerjakan sendiri tanpa harus bolak-balik bertanya. Contoh aplikasi: **Sistem Manajemen Tugas (To-Do List)**. Bahasa perintah: **Windows PowerShell**. Jika menggunakan terminal Linux/macOS, ganti perintah `Copy-Item` dengan `cp`, `New-Item` dengan `touch`, dan `Expand-Archive` dengan `unzip`.
>
> **Status:** README, kode contoh, dan konfigurasi sudah disiapkan. **Bukan klaim aplikasi sudah dijalankan di komputermu, lulus CI, atau mendapat nilai tertentu.** Semua langkah pengujian dan screenshot harus dilakukan sendiri. Laravel ditetapkan pada **12.x** untuk konsistensi panduan, bukan otomatis memakai versi terbaru.

## Daftar isi

- [Rubrik dan hasil akhir](#rubrik-dan-hasil-akhir)
- [Tahap 0 — instal semua perangkat](#tahap-0--instal-semua-perangkat)
- [Tahap 1 — buat proyek](#tahap-1--buat-proyek)
- [Tahap 2 — jalankan Laravel REST API](#tahap-2--jalankan-laravel-rest-api)
- [Tahap 3 — Postman dan feature test](#tahap-3--postman-dan-feature-test)
- [Tahap 4 — jalankan frontend Vue](#tahap-4--jalankan-frontend-vue)
- [Tahap 5 — GitHub, branch, pull request, perlindungan branch](#tahap-5--github-branch-pull-request-perlindungan-branch)
- [Tahap 6 — CI dua job, GitHub Secrets, GHCR](#tahap-6--ci-dua-job-github-secrets-ghcr)
- [Tahap 7 — Dockerfile dan Docker Compose](#tahap-7--dockerfile-dan-docker-compose)
- [Tahap 8 — bonus FrankenPHP](#tahap-8--bonus-frankenphp)
- [Tahap 9 — laporan Overleaf](#tahap-9--laporan-overleaf)
- [Tahap 10 — pengumpulan](#tahap-10--pengumpulan)
- [Troubleshooting](#troubleshooting)
- [Lampiran source code lengkap](#lampiran-source-code-lengkap)

## Rubrik dan hasil akhir

| No. | Kriteria dari dosen | Nilai | Bukti yang harus ada |
| --- | --- | ---: | --- |
| 1 | Laravel RESTful API, login token, CRUD, JSON, Postman, feature test | 15 | route API, JSON Postman, output test hijau |
| 2 | Frontend Vue.js memakai API lewat Axios | 10 | UI login dan CRUD benar-benar bekerja |
| 3 | Repository GitHub, branch, pull request, branch protection, CI **dua job** | 15 | screenshot repo, PR merged, rule/status checks, dua job hijau |
| 4 | Job deploy di GitHub Actions, GitHub Secrets, image di GHCR | 15 | job `deploy` sukses + 2 image GHCR |
| 5 | Dockerfile backend dan frontend (**multi-stage**), `.dockerignore` | 20 | dua Dockerfile + file ignore + image build sukses |
| 6 | Docker Compose menghubungkan database, backend, frontend | 25 | `docker compose ps`, UI di `localhost:8080`, API dan MySQL bekerja |
| 7 | Bonus Laravel berjalan pada FrankenPHP | +10 | log/kontainer FrankenPHP dan API masih bekerja |
| 8 | **Laporan PDF wajib** | — | PDF Overleaf dengan bukti **asli**, bukan placeholder |

**Target penyerahan:** URL repo GitHub, tautan PR, tautan workflow CI/deploy, tautan paket GHCR, PDF laporan akhir, screenshot yang relevan. *Publikasi ke GHCR bukan otomatis men-deploy aplikasi ke server publik; yang diminta rubrik adalah job deploy dan image GHCR.*

### Struktur proyek akhir

```text
uts-kepl/
├── README.md                     ← panduan ini
├── .gitignore
├── .env.example                  ← contoh variabel Docker (aman untuk repo)
├── .env                          ← buat sendiri; JANGAN di-commit
├── compose.yaml
├── compose.frankenphp.yaml
├── .github/workflows/ci-cd.yml
├── backend/                      ← hasil composer create-project
│   ├── app/Http/Controllers/Api/{AuthController,TaskController}.php
│   ├── app/Models/{User,Task}.php
│   ├── database/migrations/*_create_tasks_table.php
│   ├── routes/api.php
│   ├── tests/Feature/TaskApiTest.php
│   ├── Dockerfile
│   ├── Dockerfile.frankenphp
│   ├── .dockerignore
│   └── docker/entrypoint.sh
├── frontend/                     ← hasil npm create vue
│   ├── src/App.vue
│   ├── src/main.js
│   ├── src/lib/api.js
│   ├── vite.config.js
│   ├── Dockerfile
│   ├── .dockerignore
│   └── docker/nginx.conf
└── docs/Overleaf/main.tex        ← template laporan; tambah assets/*.png
```

**PENTING:** ZIP pendamping berisi *file kode khusus tugas*, **bukan `vendor/` Laravel atau `node_modules/` Vue**. Laravel dan Vue perlu dibuat terlebih dahulu menggunakan CLI pada Tahap 1. Jangan menganggap ZIP langsung bisa dijalankan sebelum scaffolding dan instalasi dependensi.

## Tahap 0 — instal semua perangkat

1. Instal **Visual Studio Code**: https://code.visualstudio.com/ .
2. Instal **Git for Windows**: https://git-scm.com/downloads/win .
3. Instal **PHP minimal 8.2** (disarankan 8.3) + ekstensi `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, `fileinfo`; boleh melalui Laragon untuk memudahkan (https://laragon.org/). **XAMPP dengan PHP lama tidak cocok** untuk Laravel 12. Jalankan `php -m` untuk memeriksa ekstensi.
4. Instal **Composer**: https://getcomposer.org/download/ (pastikan memilih executable PHP yang benar saat instalasi).
5. Instal **Node.js 22 LTS** yang kompatibel dengan versi Vite hasil scaffolding dan npm: https://nodejs.org/ . Jika `npm create vue@latest` menyatakan versi Node tidak memenuhi syarat, perbarui Node.
6. Instal **Docker Desktop** (Windows WSL 2 / Linux containers): https://www.docker.com/products/docker-desktop/ . Aktifkan dan tunggu indikator *Engine running*.
7. Instal **Postman**: https://www.postman.com/downloads/ .
8. Buat akun **GitHub** dan **Overleaf**: https://github.com/ , https://www.overleaf.com/ .

Buka PowerShell, jalankan satu per satu:

```powershell
php -v
php -m
composer -V
node -v
npm -v
git --version
docker --version
docker compose version
```

✅ **LULUS bila** semuanya menampilkan versi tanpa pesan *not recognized*. `php -m` harus mencantumkan `pdo_sqlite`/`sqlite3` untuk tahap awal dan `mbstring`. **Jika belum**, perbaiki PATH, restart terminal, atau instal software yang masih kurang sebelum lanjut.

Screenshot opsional: hasil versi software (untuk bagian peralatan di laporan).

## Tahap 1 — buat proyek

**Gunakan PowerShell di VS Code:** menu **Terminal → New Terminal**. Jangan menyalin simbol prompt `PS C:\...>` dari dokumentasi.

### 1.1 Scaffold Laravel dan Vue

```powershell
cd C:\
mkdir uts-kepl
cd C:\uts-kepl
composer create-project laravel/laravel backend "^12.0"
npm create vue@latest frontend
```

Saat wizard Vue muncul: untuk contoh ini pilih **No** pada TypeScript, Router, Pinia, Vitest, E2E, ESLint, Prettier (boleh berbeda jika paham; README mengasumsikan proyek JS sederhana). Jika ditanya nama folder, gunakan `frontend`.

```powershell
cd frontend
npm install
npm install axios
cd ..
cd backend
php artisan install:api
cd ..
```

**`php artisan install:api` wajib dilakukan sebelum menyalin `routes/api.php` dari ZIP**, karena perintah tersebut memasang Sanctum dan mendaftarkan routing API pada Laravel 12. Jika muncul pertanyaan menjalankan migrasi, pilih `yes` jika SQLite sudah siap; kalau gagal, kita jalankan migrasi ulang di Tahap 2.

### 1.2 Salin file pendamping ke folder proyek

File ZIP ini bernama `UTS_KEPL_Panduan_dan_Kode.zip`. Simpan ke folder **Downloads**. Jalankan dari lokasi `C:\uts-kepl`:

```powershell
cd C:\uts-kepl
Expand-Archive -LiteralPath "$HOME\Downloads\UTS_KEPL_Panduan_dan_Kode.zip" -DestinationPath . -Force
```

Jika file ZIP berada di tempat lain, sesuaikan path yang ada di dalam tanda kutip. Langkah ini menambahkan file backend/frontend/CI/Docker dan **menimpa beberapa file skeleton Vue/Laravel**, seperti `backend/app/Models/User.php`, `frontend/src/App.vue`, dan `frontend/vite.config.js`. Ini **memang disengaja** untuk proyek yang baru dibuat; **jangan jalankan di proyek lama yang berisi pekerjaan penting**.

Buka folder `C:\uts-kepl` di VS Code. Pastikan berkas `backend/routes/api.php`, `frontend/src/App.vue`, `compose.yaml`, `.github/workflows/ci-cd.yml`, dan `README.md` ada.

✅ **LULUS bila** kedua folder Laravel/Vue lengkap dengan file scaffold (`backend/artisan`, `backend/composer.json`, `frontend/package.json`, `frontend/package-lock.json`) DAN file kode pendamping sudah ada.

> **Alternatif tanpa ZIP:** Di bagian [Lampiran source code lengkap](#lampiran-source-code-lengkap), isi setiap file disertakan. Buat file sesuai path dan salin kode per blok.

## Tahap 2 — jalankan Laravel REST API

### 2.1 Gunakan database SQLite untuk pengembangan lokal pertama

Laravel 12 skeleton normalnya dapat menggunakan SQLite. Untuk menghindari konflik MySQL sebelum Docker siap, tahap Laravel lokal memakai SQLite; nanti Docker otomatis memakai MySQL melalui environment Compose.

```powershell
cd C:\uts-kepl\backend
```

Buka `backend/.env` dengan VS Code dan pastikan nilai berikut (tambahkan/ganti jika perlu):

```dotenv
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

Jika `DB_DATABASE` relatif tidak berhasil pada konfigurasi spesifikmu, **hapus baris `DB_DATABASE`** agar Laravel memakai default `database/database.sqlite`, atau isi path absolut Windows. Jangan ubah `DB_CONNECTION=mysql` untuk tahap lokal ini. Gunakan **hanya satu** definisi setiap variabel di `.env`.

Buat file database jika belum ada:

```powershell
if (-not (Test-Path .\database\database.sqlite)) { New-Item -ItemType File -Path .\database\database.sqlite }
php artisan key:generate
php artisan migrate
php artisan route:list --path=api
```

**Periksa** daftar route berisi `api/register`, `api/login`, `api/logout`, `api/user`, dan route `api/tasks`. Jika route tidak muncul: jalankan ulang `php artisan install:api` dan pastikan `bootstrap/app.php` memiliki `api: __DIR__.'/../routes/api.php'` dalam `withRouting(...)`; jika Laravel meminta membuat ulang `routes/api.php`, **pertahankan isi file dari ZIP**.

### 2.2 Hidupkan backend

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

**JANGAN tutup terminal ini.** API berada di `http://127.0.0.1:8000/api`. Di browser, membuka `http://127.0.0.1:8000/api/tasks` tanpa token akan mengembalikan 401 (atau error autentikasi saat header Accept tidak meminta JSON); itu **normal**. Lanjut uji Postman dengan header JSON dan bearer token.

✅ **LULUS bila** migrasi sukses, `route:list` menampilkan route API, dan server Laravel berjalan tanpa error. Screenshot migrasi dan route list (untuk laporan).

### 2.3 Fungsi tiap file Laravel

- `backend/app/Models/User.php`: menyimpan relasi user→tasks dan trait Sanctum `HasApiTokens`.
- `backend/app/Models/Task.php`: model data tugas.
- `backend/database/migrations/...create_tasks_table.php`: tabel tasks, FK `user_id`.
- `backend/app/Http/Controllers/Api/AuthController.php`: registrasi, login, identitas user, logout.
- `backend/app/Http/Controllers/Api/TaskController.php`: JSON CRUD; setiap query dibatasi ke tugas user yang login.
- `backend/routes/api.php`: daftar endpoint; route tugas memakai `auth:sanctum`.

## Tahap 3 — Postman dan feature test

### 3.1 Buat Postman Environment

Buka Postman → **Environments** → **New**, nama `UTS Local`. Tambahkan:

| Variable | Initial/current value |
| --- | --- |
| `base_url` | `http://127.0.0.1:8000` |
| `token` | kosong dulu |
| `task_id` | kosong dulu |

Aktifkan environment tersebut. Untuk tiap request pilih **Body → raw → JSON**, header `Accept: application/json` dan (saat mengirim JSON) `Content-Type: application/json`.

### 3.2 Uji endpoint berurutan

**A. Registrasi — POST `{{base_url}}/api/register`**, Body:

```json
{ "name":"Mahasiswa UTS", "email":"uts@example.com", "password":"password123" }
```

Harus muncul **201 Created** berisi `user` dan `token`. Bila email sudah ada, pakai email lain.

**B. Login — POST `{{base_url}}/api/login`**, Body:

```json
{ "email":"uts@example.com", "password":"password123" }
```

Harus **200 OK** dengan `token`. Pada tab **Scripts → Post-response** (atau **Tests** di versi Postman lama) tempel:

```javascript
const result = pm.response.json();
pm.environment.set('token', result.token);
```

Token kini terisi otomatis. Untuk semua request yang dilindungi: tab **Authorization → Type: Bearer Token → Token: `{{token}}`**.

**C. Create tugas — POST `{{base_url}}/api/tasks`**, Body:

```json
{ "title":"Kerjakan UTS KEPL", "description":"Pengujian API Laravel", "status":"pending", "due_date":"2026-11-01" }
```

Harus **201**. Salin `data.id` ke variable Postman `task_id`, atau tambahkan post-response script:

```javascript
pm.environment.set('task_id', pm.response.json().data.id);
```

**D. Read semua — GET `{{base_url}}/api/tasks`**; harus **200** dan JSON `data: [...]`.

**E. Read detail — GET `{{base_url}}/api/tasks/{{task_id}}`**; harus **200**.

**F. Update — PATCH `{{base_url}}/api/tasks/{{task_id}}`**, Body:

```json
{ "title":"UTS berhasil dikerjakan", "status":"done" }
```

Harus **200**, status `done`.

**G. Delete — DELETE `{{base_url}}/api/tasks/{{task_id}}`**; harus **200**. GET detail ID tadi sekarang harus **404**.

**H. Logout — POST `{{base_url}}/api/logout`**; harus **200**. Ulangi GET tasks memakai token lama: harus **401 Unauthorized**.

**I. Kasus negatif wajib:** coba GET tasks tanpa bearer token → **401**; membuat tugas dengan title `""` → **422 Unprocessable Entity**; membuat user kedua dan mengakses ID tugas user pertama → **404**. Simpan screenshot beberapa request agar autentikasi dan keamanan terlihat.

### 3.3 Laravel Feature Test otomatis

Buka **terminal kedua**, jalankan:

```powershell
cd C:\uts-kepl\backend
Copy-Item .env.testing.example .env.testing
php artisan key:generate --env=testing
php artisan test
```

File `tests/Feature/TaskApiTest.php` sudah berisi lima pengujian untuk login, larangan guest, CRUD, pemisahan kepemilikan tugas, serta validasi. `RefreshDatabase` menggunakan DB SQLite testing di memori sehingga **tidak menghapus database lokal** bila `.env.testing` benar. Jangan menjalankan test dengan konfigurasi MySQL produksi.

✅ **LULUS bila** Postman menunjukkan hasil yang sesuai dan `php artisan test` **semua PASS**. Jangan klaim lulus jika belum diuji.

📸 Screenshot penting: `01-api-postman.png`, `11-laravel-tests.png`.

## Tahap 4 — jalankan frontend Vue

Backend terminal pertama harus tetap berjalan di port **8000**. Buka terminal baru:

```powershell
cd C:\uts-kepl\frontend
npm install
npm run dev -- --host 127.0.0.1
```

Buka alamat yang ditampilkan, biasanya **http://127.0.0.1:5173**. File `vite.config.js` mem-*proxy* `http://127.0.0.1:5173/api/...` ke Laravel `http://127.0.0.1:8000/api/...`, jadi **tidak perlu menggunakan URL `backend` dari browser** atau mengatur CORS manual pada contoh ini. `src/lib/api.js` mengirim permintaan lewat Axios dan menambahkan bearer token.

Lakukan uji dari UI:

1. Klik **Belum punya akun? Daftar**, masukkan nama, email baru, dan password minimal 8 karakter.
2. Setelah masuk, buat tugas judul `Mengerjakan Laporan UTS`.
3. Klik **Edit**, ubah judul; klik **Selesai** untuk mengubah status.
4. Klik **Hapus** dan konfirmasi. Pastikan daftar berubah sesuai operasi.
5. Klik **Logout**, lalu login lagi. Tugas yang belum dihapus harus tetap ada.
6. Tekan **F12 → Network**, lakukan tambah tugas, cek request ke `/api/tasks` dan respons **201**. Buka berkas `frontend/src/lib/api.js` untuk membuktikan penggunaan **Axios**.

✅ **LULUS bila** semua aksi bekerja tanpa error di Console/Network. 📸 `02-vue-tugas.png` + screenshot tab Network Axios bila perlu.

> Catatan: `localStorage` untuk token dipakai agar mudah diperagakan pada tugas. Pada aplikasi publik, pertimbangkan autentikasi cookie HttpOnly dan proteksi CSRF sesuai arsitektur. Jangan memakai akun/password sungguhan untuk demonstrasi.

## Tahap 5 — GitHub, branch, pull request, perlindungan branch

### 5.1 Inisialisasi Git dan unggah repository

Dari root `C:\uts-kepl` (bukan folder backend):

```powershell
cd C:\uts-kepl
git init -b main
git add .
git status
git commit -m "feat: initial Laravel Vue Docker UTS project"
```

**Wajib cek `git status`:** file `backend/.env`, root `.env`, `vendor/`, `node_modules/`, dan database SQLite **tidak boleh masuk Git**. Pastikan juga `backend/.env.testing.example` **terikut** (ini hanya konfigurasi contoh tanpa secret); jika terabaikan oleh `.gitignore` Laravel, gunakan `git add -f backend/.env.testing.example` sebelum commit. Jika belum pernah memakai Git, konfigurasi identitas:

```powershell
git config --global user.name "Nama Kamu"
git config --global user.email "email-github-kamu@example.com"
```

Jika commit pertama gagal karena identitas, jalankan dua perintah tersebut lalu ulang `git commit`.

Buka GitHub → tombol **New repository** → nama misalnya `uts-kepl-todo` → pilih Public (jika diizinkan dosen) → **jangan pilih Initialize with README** (karena README lokal sudah ada) → Create. Salin URL repo **kamu sendiri** lalu:

```powershell
git remote add origin https://github.com/USERNAME/uts-kepl-todo.git
git push -u origin main
```

Ganti `USERNAME` dengan username GitHub. Login GitHub biasanya lewat jendela browser/Git Credential Manager. Jangan masukkan password akun sebagai token biasa; gunakan metode login yang disarankan Git.

### 5.2 Branch dan pull request yang nyata

```powershell
git switch -c feature/dokumentasi-uts
```

Buka `README.md` dan tambahkan informasi nama mahasiswa/NIM di bagian paling bawah (atau revisi teks lain secara benar). Lalu:

```powershell
git add README.md
git commit -m "docs: add student project details"
git push -u origin feature/dokumentasi-uts
```

GitHub → **Pull requests → New pull request** → base `main`, compare `feature/dokumentasi-uts` → **Create pull request**. Tunggu hasil CI dua job yang dibuat oleh file `.github/workflows/ci-cd.yml`. Jika hijau, **Merge pull request → Confirm merge**.

**Kapan mengatur branch protection?** Setelah push pertama atau saat PR ada agar status check sudah muncul di GitHub. Buka **Settings → Rules → Rulesets** (atau **Branches → Add branch protection rule**, tergantung UI dan jenis akun). Target `main`, aktifkan:

- Require a pull request before merging.
- Require status checks to pass before merging: `backend-tests`, `frontend-build`.
- (Opsional) Block force pushes, require conversation resolution.

Simpan ruleset/rule dan lihat apakah **Active**. Perlindungan branch pada repo privat/tipe akun tertentu dapat memiliki batasan; periksa pesan yang muncul pada GitHub. Jika dibatasi, gunakan repo publik bila sesuai aturan dosen atau konsultasikan izin dosen. **Jangan mengklaim branch protection aktif bila UI menyebut aturan tidak aktif.**

📸 `03-github-pr.png` (PR dan branch) dan `04-branch-protection.png` (rule dan status check).

### 5.3 Setelah merge, sinkronkan lokal

```powershell
git switch main
git pull origin main
```

## Tahap 6 — CI dua job, GitHub Secrets, GHCR

Workflow sudah ada di `.github/workflows/ci-cd.yml`, memuat:

1. **`backend-tests`:** setup PHP 8.3 → `composer install` → key testing → `php artisan test`.
2. **`frontend-build`:** setup Node 22 → `npm ci` → `npm run build`.
3. **`deploy`:** jalan hanya setelah dua job di atas sukses dan ada **push ke `main`**, login GHCR → build dan push image backend serta frontend.

### 6.1 Periksa GitHub Actions

GitHub repo → tab **Actions** → pilih workflow **CI dan Publikasi GHCR** → klik run terbaru. Di run **pull request**, dua job test/build harus **hijau**; `deploy` *skipped* karena PR **normal**, bukan error. Pada run **push/merge ke `main`**, jika keduanya lulus, `deploy` seharusnya dijalankan. Saat pertama menjalankan workflow, mungkin harus mengaktifkan Actions lewat halaman GitHub terlebih dahulu.

📸 `05-ci-dua-job.png` memuat dua job sukses dalam satu workflow.

### 6.2 Konfigurasi GitHub Secret dan izin GHCR

Buka **Settings → Secrets and variables → Actions → Secrets → New repository secret**. Untuk contoh ini, GitHub **otomatis menyediakan `GITHUB_TOKEN`** (tidak perlu dibuat manual) yang digunakan workflow mengautentikasi ke `ghcr.io`. Di GitHub repo Settings → Actions → General → Workflow permissions, pastikan policy organisasi/repo mengizinkan `packages: write` bagi job deploy; job workflow sudah mengatur `permissions: packages: write`.

Jika dosen menuntut **custom GitHub Secret** untuk dibuktikan, buat secret opsional `GHCR_TOKEN` dengan GitHub Personal Access Token **classic** yang memiliki `write:packages` dan `read:packages`; gunakan hanya token milik sendiri dan sesuaikan akses SSO organisasi bila diperlukan. Workflow menggunakan `secrets.GHCR_TOKEN || secrets.GITHUB_TOKEN`, jadi **berjalan tanpa PAT kustom** pada kebanyakan repository. **Jangan pernah screenshot nilai token, menempel token ke README, atau commit PAT ke repo.** Karena token kustom dipakai bersama `${{ github.actor }}`, gunakan token milik akun yang memicu workflow; jika PR berasal dari akun lain, lebih aman tetap mengandalkan GITHUB_TOKEN.

**Langkah GitHub Secret ditunjukkan di laporan** dengan screenshot *nama* `GHCR_TOKEN` jika memang dibuat, tanpa menampakkan nilai rahasianya. Secret tidak otomatis membuktikan publikasi; perlu screenshot deploy sukses dan GHCR.

### 6.3 Periksa paket image GHCR

Setelah merge PR/push ke `main`, buka **Actions → run push terbaru → job `deploy`**. Jika hijau, buka profile GitHub → **Packages** atau sidebar repo **Packages**. Harus ada dua paket bernama mirip:

```text
ghcr.io/username/uts-kepl-todo-backend:latest
ghcr.io/username/uts-kepl-todo-frontend:latest
```

Gunakan **nama aktual** yang ditampilkan GitHub; workflow otomatis mengubah namespace image menjadi huruf kecil. Jika belum muncul, cek log job `deploy`, permissions package, pengaturan Actions, dan bahwa event berasal dari **push ke `main`**. **Jangan menyamakan image GHCR dengan aplikasi yang sudah online**: proyek ini menjalankan sistem secara lokal via Docker Compose.

📸 `06-gh-actions-publish.png` dan `07-ghcr-images.png`.

## Tahap 7 — Dockerfile dan Docker Compose

### 7.1 Cek multi-stage dan ignore

- `backend/Dockerfile`: **stage `build`** menginstal dependencies Composer; **stage `runtime`** menjalankan PHP 8.3 + Apache. Aplikasi disajikan dari `/public`, bukan root Laravel.
- `frontend/Dockerfile`: **stage `build`** memakai Node 22 + Vite; **stage `runtime`** memakai Nginx untuk file `dist/`.
- `backend/.dockerignore` dan `frontend/.dockerignore`: menghindari build context berisi secrets, `vendor/`, `node_modules/`, dan artefak tidak perlu.
- `frontend/docker/nginx.conf`: request browser `/api/*` di-*proxy* Nginx ke service `backend`, nama DNS internal Docker.
- `compose.yaml`: **tiga layanan** `database` (MySQL), `backend` (Laravel), `frontend` (Vue), dengan healthcheck database, lingkungan backend, dan volume MySQL.

### 7.2 Siapkan variabel rahasia Docker di root

```powershell
cd C:\uts-kepl
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

Salin hasil satu baris yang dimulai `base64:` ke **`C:\uts-kepl\.env`** (ini BUKAN `backend/.env`). Edit nilai contoh menjadi:

```dotenv
APP_KEY=base64:HASIL_PERINTAH_PHP_TADI
DB_PASSWORD=password_mysql_lokal_yang_kuat
MYSQL_ROOT_PASSWORD=password_root_mysql_lokal_yang_kuat
```

Jangan memakai teks `HASIL_PERINTAH_PHP_TADI` secara literal. Jangan gunakan tanda petik atau karakter `#` dalam password bila belum paham sintaks dotenv. Jangan unggah root `.env` ke GitHub atau dokumentasi.

### 7.3 Jalankan semua kontainer

Pastikan Docker Desktop **running**. Dari **root** proyek jalankan:

```powershell
docker compose config
docker compose up -d --build
docker compose ps
```

**Build pertama bisa lama** karena mengunduh image PHP, MySQL, Node, Nginx dan Composer. Tunggu sampai `database`, `backend`, `frontend` berjalan; database akan membutuhkan waktu untuk menjadi *healthy*. Lihat log bila tidak berjalan:

```powershell
docker compose logs --tail=100 database
docker compose logs --tail=100 backend
docker compose logs --tail=100 frontend
```

URL setelah sukses:

- **Aplikasi Vue:** http://localhost:8080
- **API Laravel langsung:** http://localhost:8000/api/login (POST saja; tidak untuk dibuka sebagai GET)
- **API via frontend/proxy:** http://localhost:8080/api/tasks (memerlukan token; 401 tanpa token berarti server API merespons)

**Uji nyata Docker:** registrasi akun **baru** pada UI `localhost:8080`, buat tugas, edit, ubah status, hapus, logout/login lagi. Lingkungan Docker memakai **MySQL** sendiri; tidak otomatis menampilkan data SQLite dari Tahap 2—ini normal. Buka Postman Environment dan ubah `base_url` menjadi `http://localhost:8080` untuk menguji request yang melewati Nginx.

Cek migrasi MySQL di container:

```powershell
docker compose exec backend php artisan migrate:status
docker compose exec backend php artisan route:list --path=api
```

📸 `08-docker-compose.png` (`docker compose ps`) dan `09-running-app.png` (UI berhasil memproses tugas lewat Docker).

### 7.4 Cara menghentikan dan memulai ulang

```powershell
docker compose down
docker compose up -d
```

**JANGAN** menggunakan `docker compose down -v` selama ingin mempertahankan data MySQL, karena `-v` akan **menghapus volume data database**. Jika mengganti file frontend/backend, gunakan `docker compose up -d --build` lagi; file source tidak di-*bind mount* ke container dalam contoh ini.

✅ **LULUS bila** dua Dockerfile benar-benar multi-stage, dua `.dockerignore` tersedia, tiga layanan Compose aktif, dan CRUD berjalan melalui alamat frontend Docker.

## Tahap 8 — bonus FrankenPHP

> Bonus opsional **setelah** Docker biasa berhasil. Jangan mengerjakan ini lebih dulu jika poin utama belum selesai.

File `backend/Dockerfile.frankenphp` menyediakan versi runtime Laravel pada **FrankenPHP**, dan file `compose.frankenphp.yaml` mengganti hanya konfigurasi build backend. Jalankan:

```powershell
cd C:\uts-kepl
docker compose -f compose.yaml -f compose.frankenphp.yaml up -d --build
docker compose -f compose.yaml -f compose.frankenphp.yaml ps
docker compose -f compose.yaml -f compose.frankenphp.yaml logs --tail=100 backend
```

Buka http://localhost:8080 dan uji registrasi atau login serta CRUD. **Perhatikan:** PHP runtime default image FrankenPHP biasanya menangani aplikasi di `/app/public`; jika ternyata menunjukkan 404 atau masalah port di versi image yang didapat, baca log FrankenPHP dan sesuaikan konfigurasi Caddy sebelum mengklaim bonus berhasil. Untuk kembali ke Apache biasa: `docker compose down`, kemudian `docker compose up -d --build`. Jika ingin memastikan build kembali ke mode Apache, gunakan `docker compose -f compose.yaml up -d --build --force-recreate` dan cek log/konfigurasinya.

📸 `10-frankenphp.png`: tangkapan layar log container backend yang menunjukkan FrankenPHP dan halaman API/frontend bekerja.

## Tahap 9 — laporan Overleaf

### 9.1 Unggah proyek LaTeX

Dalam ZIP pendamping ada `docs/Overleaf/main.tex`. Disediakan juga ZIP khusus bernama **`UTS_KEPL_Overleaf_Siap_Import.zip`** yang berisi `main.tex` dan folder `assets`. Untuk memulai:

1. Buka https://www.overleaf.com/ → **New Project → Upload Project**.
2. Unggah `UTS_KEPL_Overleaf_Siap_Import.zip`, atau buat *Blank Project* lalu unggah file `main.tex` dan buat folder `assets`.
3. Set compiler ke **pdfLaTeX**, lalu klik **Recompile**.
4. Ubah `[ISI: Nama mahasiswa]`, `[ISI: NIM]`, jurusan/kampus, nama mata kuliah, URL GitHub, versi software, nama GHCR, hasil uji, dan kesimpulan **sesuai kondisi nyata**. Penanda `[ISI: ...]` adalah placeholder yang harus dihapus/diganti.
5. Beri nama screenshot **persis sama** seperti tabel di bawah lalu unggah ke folder Overleaf `assets/`. Versi template ini **otomatis menampilkan gambar** bila filenya ada; bila belum ada, terlihat kotak placeholder.
6. Recompile → **Menu → Download PDF**. Baca ulang **semua halaman** untuk mencari placeholder, gambar terlalu kecil, kesalahan caption, atau klaim tanpa bukti.

### 9.2 Daftar bukti wajib

| File screenshot di Overleaf | Cara mendapatkannya | Poin |
| --- | --- | --- |
| `01-api-postman.png` | Postman login token dan CRUD status 200/201 | 1 |
| `02-vue-tugas.png` | Vue menampilkan form + daftar tugas | 2 |
| `03-github-pr.png` | Pull request branch feature → main | 3 |
| `04-branch-protection.png` | Settings Ruleset/rule aktif + status check wajib | 3 |
| `05-ci-dua-job.png` | GitHub Actions dua job backend dan frontend hijau | 3 |
| `06-gh-actions-publish.png` | Job `deploy` pada push main sukses | 4 |
| `07-ghcr-images.png` | Dua paket backend dan frontend di GHCR | 4 |
| `08-docker-compose.png` | Output `docker compose ps` menampilkan 3 service | 5,6 |
| `09-running-app.png` | Vue di `localhost:8080` dengan tugas di MySQL | 6 |
| `10-frankenphp.png` | Bonus: log/runtime FrankenPHP + API bekerja | +10 |
| `11-laravel-tests.png` | Output `php artisan test` PASS | 1 |

Screenshot tambahan yang disarankan: dua Dockerfile memperlihatkan `AS build`/`AS runtime`, kedua `.dockerignore`, Postman request tidak sah 401, respons JSON 422, konfigurasi Secret **tanpa nilai**, console Network Axios, dan output `docker compose exec backend php artisan migrate:status`.

**Aturan kejujuran laporan:** Jangan tulis `PASS`, `sukses`, `deployment selesai`, atau `nilai 100` jika belum benar-benar diperiksa. Unggah screenshot asli dari komputer/GitHub kamu, bukan ilustrasi contoh. Jangan tampilkan token, password, isi `.env`, atau informasi pribadi yang tidak perlu.

### 9.3 Struktur bab laporan

- **BAB I Pendahuluan:** latar belakang, rumusan masalah, tujuan, ruang lingkup.
- **BAB II Analisis dan Perancangan:** kebutuhan, tabel endpoint, skema tabel tasks, diagram arsitektur.
- **BAB III Implementasi:** Laravel API, Vue Axios, GitHub PR, CI/CD, Dockerfile, Compose, FrankenPHP jika ada.
- **BAB IV Pengujian:** tabel Postman, feature test, hasil frontend, Docker/CI dengan screenshot yang sesuai.
- **BAB V Penutup:** kesimpulan *berdasarkan hasil yang benar* dan saran.
- **Lampiran:** URL repo, PR, run Actions, GHCR, dokumentasi tambahan.

## Tahap 10 — pengumpulan

### Checklist final (centang sesudah benar-benar diuji)

- [ ] `php artisan route:list --path=api` menampilkan endpoint.
- [ ] Register/login mengembalikan token JSON; tanpa token mendapat 401.
- [ ] CRUD tugas di Postman berhasil; akses milik user lain dibatasi.
- [ ] `php artisan test` menampilkan PASS.
- [ ] Vue benar-benar memakai Axios dan CRUD bekerja.
- [ ] Repo GitHub ada dan link bisa dibuka dosen.
- [ ] Branch feature ada/historinya terekam, pull request **merged**.
- [ ] Rule branch `main` aktif dan mewajibkan pemeriksaan.
- [ ] CI `backend-tests` dan `frontend-build` hijau.
- [ ] Job `deploy` berhasil pada **push main**.
- [ ] GitHub Secrets dipahami/dikonfigurasi secara aman.
- [ ] **Dua image** backend/frontend muncul di GHCR.
- [ ] Dockerfile backend dan frontend memiliki minimal dua stages.
- [ ] Kedua `.dockerignore` ada.
- [ ] `docker compose ps` memperlihatkan database, backend, frontend.
- [ ] UI Docker di `localhost:8080` bisa menyimpan tugas ke MySQL.
- [ ] Bonus FrankenPHP diuji jika dikerjakan (opsional).
- [ ] Overleaf terisi nama, NIM, screenshot nyata, hasil test dan URL proyek.
- [ ] PDF akhir berhasil diunduh, dibaca dan diserahkan.
- [ ] Tidak ada `.env`, token, credentials, `vendor/`, `node_modules/` yang ter-commit.

### Contoh penyerahan

```text
Nama/NIM: [isi]
Repo GitHub: https://github.com/USERNAME/uts-kepl-todo
Pull Request: [link PR merged]
CI dan deploy: [link GitHub Actions run berhasil]
GHCR backend: [link paket]
GHCR frontend: [link paket]
Laporan PDF: [file PDF hasil Overleaf]
```

## Troubleshooting

| Gejala | Penyebab yang sering | Langkah penyelesaian |
| --- | --- | --- |
| `php` / `composer` not recognized | PHP/Composer belum terpasang atau PATH | Instal dan restart terminal; `where.exe php` |
| PHP Laravel minta versi lebih baru | PHP yang aktif terlalu tua | Gunakan PHP >=8.2; `php -v` dan `where.exe php` |
| `could not find driver` | Ekstensi SQLite/MySQL tidak aktif | `php -m`, aktifkan `pdo_sqlite` di php.ini, restart terminal |
| `No application encryption key` | APP_KEY kosong | `php artisan key:generate` (lokal); root Docker `.env` berisi kunci valid |
| `SQLite database ... does not exist` | File DB belum ada | `New-Item -ItemType File database/database.sqlite -Force`; ulang migrate |
| `No such file routes/api.php` / API 404 | `install:api` belum dipanggil | `php artisan install:api`, periksa bootstrap/app.php dan file `routes/api.php` |
| Postman HTTP 401 | Bearer token tidak dikirim, sudah logout, atau keliru | Login ulang, pilih Auth Bearer Token `{{token}}` |
| Postman HTTP 419 | Salah mekanisme session / memanggil route web | Gunakan route `/api/...`, `Accept: application/json`, Sanctum bearer token |
| Postman HTTP 422 | Data tidak memenuhi validasi | Cek `errors` JSON; title wajib, password ≥8, status pending/done |
| Vue `ECONNREFUSED` / proxy failed | Backend port 8000 mati | Jalankan `php artisan serve`, cek `vite.config.js` |
| Vue halaman kacau / style konflik | CSS bawaan Vue masih dipakai | Pastikan `frontend/src/main.js` sesuai ZIP (tanpa import `assets/main.css`) |
| `npm ci` gagal pada Actions/Docker | `package-lock.json` belum cocok atau belum di-commit | Di `frontend`: `npm install`; commit `package.json` + `package-lock.json` |
| CI `backend-tests` gagal SQLite | `.env.testing` / ekstensi tidak sesuai | Pastikan `.env.testing.example`, `APP_ENV=testing`, DB sqlite `:memory:` dan lihat log job |
| Branch protection check tidak ada | CI belum pernah jalan | Push branch, buat PR, lihat status job, kembali pilih required checks |
| Branch ruleset tidak aktif | Batasan plan repo, rule belum active | Lihat keterangan Settings; sesuaikan visibility/plan atau tanya dosen |
| GHCR `denied` / 403 | Token tidak punya izin package | Job `permissions: packages: write`, gunakan `GITHUB_TOKEN`, cek pengaturan org/repo |
| `deploy` dilewati (*skipped*) | Event PR, bukan push main | Merge PR ke main dan lihat workflow baru |
| Docker engine not running | Docker Desktop belum siap | Jalankan Docker Desktop, aktifkan WSL2/Linux containers |
| Docker MySQL `unhealthy` | Password/volume lama/perubahan env | Cek `docker compose logs database`; volume menyimpan password lama; JANGAN hapus -v tanpa backup |
| Docker backend error migrasi | Kredensial MySQL/APP_KEY salah | Cek root `.env`, `docker compose logs backend`, `docker compose config` (jangan screenshot jika memuat rahasia) |
| Docker 502 Bad Gateway | backend gagal start | `docker compose ps` + `docker compose logs backend` dan cek MySQL healthy |
| Browser Docker menampilkan Vue tetapi API gagal | Nginx proxy / backend mati | Cek `frontend/docker/nginx.conf`, akses backend container, lihat log |
| Overleaf gambar belum tampil | Path salah atau belum diunggah | Upload ke `assets/NAMA_PERSIS.png`, cek huruf besar-kecil, Recompile |
| Overleaf PDF tidak bisa compile | compiler/file LaTeX error | Pilih pdfLaTeX, file utama `main.tex`, buka Logs dan periksa pesan pertama |

**Sumber rujukan resmi:** [Laravel 12 Sanctum](https://laravel.com/docs/12.x/sanctum), [Laravel 12 Tests](https://laravel.com/docs/12.x/http-tests), [Vue](https://vuejs.org/guide/quick-start.html), [GitHub: Publishing Docker images](https://docs.github.com/en/actions/tutorials/publish-packages/publish-docker-images), [Docker Compose dependencies](https://docs.docker.com/compose/how-tos/startup-order/), [Overleaf Docs](https://www.overleaf.com/learn).

---

## Lampiran source code lengkap

**File berikut juga tersedia sebagai berkas asli di ZIP pendamping.** Jika ZIP berhasil diekstrak di atas, **tidak perlu menyalin ulang** lampiran ini. Lampiran memungkinkan README digunakan sendiri jika suatu hari file pendamping terpisah. Jangan membuat ulang file bawaan framework yang tidak tercantum di bawah ini: file lain tetap berasal dari `composer create-project` dan `npm create vue`.

### `.env.example`

```dotenv
# Salin ke .env di AKAR proyek; JANGAN di-commit.
APP_KEY=
DB_PASSWORD=ubah_ke_password_database_yang_kuat
MYSQL_ROOT_PASSWORD=ubah_ke_password_root_yang_kuat
```

### `.github/workflows/ci-cd.yml`

```yaml
name: CI dan Publikasi GHCR

on:
  pull_request:
    branches: [main]
  push:
    branches: [main]

permissions:
  contents: read

jobs:
  backend-tests:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v4
      - name: Siapkan PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, dom, xml, pdo_sqlite, sqlite3
          coverage: none
      - name: Install Composer
        working-directory: backend
        run: composer install --no-interaction --prefer-dist --no-progress
      - name: Siapkan environment testing
        working-directory: backend
        run: |
          cp .env.testing.example .env.testing
          php artisan key:generate --env=testing
      - name: Feature test Laravel
        working-directory: backend
        run: php artisan test

  frontend-build:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v4
      - name: Siapkan Node
        uses: actions/setup-node@v4
        with:
          node-version: '22'
          cache: npm
          cache-dependency-path: frontend/package-lock.json
      - name: Install npm
        working-directory: frontend
        run: npm ci
      - name: Build Vue
        working-directory: frontend
        run: npm run build

  deploy:
    name: deploy (publish images to GHCR)
    runs-on: ubuntu-latest
    needs: [backend-tests, frontend-build]
    if: github.event_name == 'push' && github.ref == 'refs/heads/main'
    permissions:
      contents: read
      packages: write
    steps:
      - name: Checkout
        uses: actions/checkout@v4
      - name: Buildx
        uses: docker/setup-buildx-action@v3
      - name: Nama image huruf kecil
        id: names
        shell: bash
        run: |
          echo "backend=ghcr.io/${GITHUB_REPOSITORY,,}-backend" >> "$GITHUB_OUTPUT"
          echo "frontend=ghcr.io/${GITHUB_REPOSITORY,,}-frontend" >> "$GITHUB_OUTPUT"
      - name: Login GitHub Container Registry
        uses: docker/login-action@v3
        with:
          registry: ghcr.io
          username: ${{ github.actor }}
          password: ${{ secrets.GHCR_TOKEN || secrets.GITHUB_TOKEN }}
      - name: Push backend image
        uses: docker/build-push-action@v6
        with:
          context: ./backend
          file: ./backend/Dockerfile
          push: true
          tags: |
            ${{ steps.names.outputs.backend }}:latest
            ${{ steps.names.outputs.backend }}:sha-${{ github.sha }}
      - name: Push frontend image
        uses: docker/build-push-action@v6
        with:
          context: ./frontend
          file: ./frontend/Dockerfile
          push: true
          tags: |
            ${{ steps.names.outputs.frontend }}:latest
            ${{ steps.names.outputs.frontend }}:sha-${{ github.sha }}
```

### `.gitignore`

```gitignore
/.env
/.env.*
!/.env.example
/backend/.env
/backend/.env.*
!/backend/.env.example
!/backend/.env.testing.example
/backend/vendor/
/backend/database/*.sqlite
/frontend/node_modules/
/frontend/dist/
.DS_Store
```

### `backend/.dockerignore`

```gitignore
.git
.github
.env
.env.*
vendor/
node_modules/
database/*.sqlite
storage/logs/*
tests/
*.log
README.md
```

### `backend/.env.testing.example`

```dotenv
APP_NAME=UTS-Todo-Testing
APP_ENV=testing
APP_DEBUG=true
APP_KEY=
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
SESSION_DRIVER=array
CACHE_STORE=array
QUEUE_CONNECTION=sync
MAIL_MAILER=array
```

### `backend/Dockerfile`

```dockerfile
# Tahap 1: instal dependensi Composer (tanpa menaruh secret ke image).
FROM php:8.3-cli-bookworm AS build
RUN apt-get update && apt-get install -y --no-install-recommends git unzip libzip-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress
COPY . .
RUN composer dump-autoload --no-dev --optimize

# Tahap 2: Apache+PHP menjalankan Laravel.
FROM php:8.3-apache-bookworm AS runtime
RUN apt-get update && apt-get install -y --no-install-recommends libzip-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
WORKDIR /var/www/html
COPY --from=build /app /var/www/html
COPY docker/entrypoint.sh /usr/local/bin/start-app
RUN sed -i 's/\r$//' /usr/local/bin/start-app \
    && chmod +x /usr/local/bin/start-app \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
EXPOSE 80
CMD ["start-app"]
```

### `backend/Dockerfile.frankenphp`

```dockerfile
# Bonus: Laravel berjalan pada FrankenPHP, dengan Composer multi-stage.
FROM dunglas/frankenphp:1-php8.3 AS build
RUN install-php-extensions pdo_mysql pdo_sqlite zip
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress
COPY . .
RUN composer dump-autoload --no-dev --optimize

FROM dunglas/frankenphp:1-php8.3 AS runtime
RUN install-php-extensions pdo_mysql pdo_sqlite zip
ENV SERVER_NAME=:80
WORKDIR /app
COPY --from=build /app /app
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
EXPOSE 80
```

### `backend/app/Http/Controllers/Api/AuthController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'user' => $user,
            'token' => $user->createToken('vue-frontend')->plainTextToken,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau kata sandi salah.'],
            ]);
        }

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $user->createToken('vue-frontend')->plainTextToken,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }
}
```

### `backend/app/Http/Controllers/Api/TaskController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->tasks()->latest()->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:pending,done'],
            'due_date' => ['nullable', 'date'],
        ]);

        $task = $request->user()->tasks()->create($data);
        return response()->json(['message' => 'Tugas dibuat', 'data' => $task], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $task = $request->user()->tasks()->findOrFail($id);
        return response()->json(['data' => $task]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $task = $request->user()->tasks()->findOrFail($id);
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'in:pending,done'],
            'due_date' => ['sometimes', 'nullable', 'date'],
        ]);
        $task->update($data);
        return response()->json(['message' => 'Tugas diperbarui', 'data' => $task->refresh()]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $task = $request->user()->tasks()->findOrFail($id);
        $task->delete();
        return response()->json(['message' => 'Tugas dihapus']);
    }
}
```

### `backend/app/Models/Task.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = ['title', 'description', 'status', 'due_date'];

    protected function casts(): array
    {
        return ['due_date' => 'date:Y-m-d'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

### `backend/app/Models/User.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
```

### `backend/database/migrations/2026_10_14_000000_create_tasks_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
```

### `backend/docker/entrypoint.sh`

```sh
#!/bin/sh
set -eu
cd /var/www/html
php artisan migrate --force
exec apache2-foreground
```

### `backend/routes/api.php`

```php
<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('/tasks', TaskController::class);
});
```

### `backend/tests/Feature/TaskApiTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_and_login_return_tokens(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Mahasiswa UTS',
            'email' => 'mahasiswa@example.test',
            'password' => 'password123',
        ])->assertCreated()->assertJsonStructure(['user', 'token']);

        $this->postJson('/api/login', [
            'email' => 'mahasiswa@example.test',
            'password' => 'password123',
        ])->assertOk()->assertJsonStructure(['user', 'token']);
    }

    public function test_guest_cannot_access_tasks(): void
    {
        $this->getJson('/api/tasks')->assertUnauthorized();
    }

    public function test_authenticated_user_can_crud_tasks(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $response = $this->postJson('/api/tasks', [
            'title' => 'Menyelesaikan UTS',
            'description' => 'Uji CRUD Laravel',
        ], $headers)->assertCreated();
        $id = $response->json('data.id');

        $this->getJson('/api/tasks', $headers)->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/tasks/'.$id, $headers)->assertOk();
        $this->putJson('/api/tasks/'.$id, [
            'title' => 'UTS sudah selesai',
            'status' => 'done',
        ], $headers)->assertOk()->assertJsonPath('data.status', 'done');
        $this->deleteJson('/api/tasks/'.$id, [], $headers)->assertOk();
        $this->getJson('/api/tasks/'.$id, $headers)->assertNotFound();
    }

    public function test_user_cannot_view_another_users_task(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = $owner->tasks()->create(['title' => 'Rahasia']);
        $token = $other->createToken('test')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->getJson('/api/tasks/'.$task->id, $headers)->assertNotFound();
        $this->deleteJson('/api/tasks/'.$task->id, [], $headers)->assertNotFound();
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_invalid_task_cannot_be_created(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $this->postJson('/api/tasks', [
            'title' => '',
        ], ['Authorization' => 'Bearer '.$token])->assertUnprocessable();
    }
}
```

### `compose.frankenphp.yaml`

```yaml
services:
  backend:
    build:
      context: ./backend
      dockerfile: Dockerfile.frankenphp
```

### `compose.yaml`

```yaml
services:
  database:
    image: mysql:8.4
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: todo_app
      MYSQL_USER: todo_user
      MYSQL_PASSWORD: ${DB_PASSWORD:?isi DB_PASSWORD di .env akar proyek}
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD:?isi MYSQL_ROOT_PASSWORD di .env akar proyek}
    volumes:
      - mysql_data:/var/lib/mysql
    healthcheck:
      test: ["CMD-SHELL", "mysqladmin ping -h 127.0.0.1 -u root -p\"$$MYSQL_ROOT_PASSWORD\" --silent"]
      interval: 10s
      timeout: 5s
      retries: 12
      start_period: 30s

  backend:
    build:
      context: ./backend
      dockerfile: Dockerfile
    restart: unless-stopped
    environment:
      APP_NAME: UTS Todo
      APP_ENV: local
      APP_DEBUG: "true" # demonstrasi lokal; wajib false untuk publik/produksi
      APP_KEY: ${APP_KEY:?isi APP_KEY di .env akar proyek}
      APP_URL: http://localhost:8080
      LOG_CHANNEL: stderr
      DB_CONNECTION: mysql
      DB_HOST: database
      DB_PORT: 3306
      DB_DATABASE: todo_app
      DB_USERNAME: todo_user
      DB_PASSWORD: ${DB_PASSWORD}
      SESSION_DRIVER: file
      CACHE_STORE: file
      QUEUE_CONNECTION: sync
    depends_on:
      database:
        condition: service_healthy
    ports:
      - "8000:80"

  frontend:
    build:
      context: ./frontend
      dockerfile: Dockerfile
    restart: unless-stopped
    depends_on:
      - backend
    ports:
      - "8080:80"

volumes:
  mysql_data:
```

### `frontend/.dockerignore`

```gitignore
.git
.github
.env
.env.*
node_modules/
dist/
*.log
README.md
```

### `frontend/Dockerfile`

```dockerfile
# Tahap 1: build Vue.js menjadi file statis.
FROM node:22-alpine AS build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# Tahap 2: sajikan hasil build via Nginx.
FROM nginx:stable-alpine AS runtime
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/dist /usr/share/nginx/html
EXPOSE 80
```

### `frontend/docker/nginx.conf`

```nginx
server {
    listen 80;
    server_name _;
    root /usr/share/nginx/html;
    index index.html;

    location /api/ {
        proxy_pass http://backend:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location / {
        try_files $uri $uri/ /index.html;
    }
}
```

### `frontend/src/App.vue`

```vue
<script setup>
import { onMounted, ref } from 'vue'
import api from './lib/api'

const token = ref(localStorage.getItem('uts_token') || '')
const user = ref(null)
const mode = ref('login')
const name = ref('')
const email = ref('')
const password = ref('')
const title = ref('')
const description = ref('')
const dueDate = ref('')
const tasks = ref([])
const error = ref('')
const success = ref('')
const loading = ref(false)

function messageFrom(errorObject) {
  const data = errorObject?.response?.data
  return data?.message || Object.values(data?.errors || {})[0]?.[0] || 'Terjadi kesalahan. Coba kembali.'
}

async function authenticate() {
  error.value = ''; success.value = ''; loading.value = true
  try {
    const body = { email: email.value, password: password.value }
    if (mode.value === 'register') body.name = name.value
    const response = await api.post(mode.value === 'register' ? '/register' : '/login', body)
    token.value = response.data.token
    localStorage.setItem('uts_token', token.value)
    user.value = response.data.user
    password.value = ''
    await loadTasks()
  } catch (e) { error.value = messageFrom(e) }
  finally { loading.value = false }
}

async function loadTasks() {
  if (!token.value) return
  try {
    const [profile, list] = await Promise.all([api.get('/user'), api.get('/tasks')])
    user.value = profile.data.user
    tasks.value = list.data.data
  } catch (e) {
    if (e.response?.status === 401) clearSession()
    else error.value = messageFrom(e)
  }
}

async function addTask() {
  error.value = ''; success.value = ''
  try {
    await api.post('/tasks', {
      title: title.value,
      description: description.value || null,
      due_date: dueDate.value || null,
    })
    title.value = ''; description.value = ''; dueDate.value = ''
    success.value = 'Tugas berhasil ditambah.'
    await loadTasks()
  } catch (e) { error.value = messageFrom(e) }
}

async function toggleTask(task) {
  error.value = ''
  try {
    await api.patch(`/tasks/${task.id}`, { status: task.status === 'done' ? 'pending' : 'done' })
    await loadTasks()
  } catch (e) { error.value = messageFrom(e) }
}

async function editTask(task) {
  const newTitle = window.prompt('Ubah judul tugas:', task.title)
  if (newTitle === null) return
  if (!newTitle.trim()) { error.value = 'Judul tidak boleh kosong.'; return }
  try {
    await api.patch(`/tasks/${task.id}`, { title: newTitle.trim() })
    await loadTasks()
  } catch (e) { error.value = messageFrom(e) }
}

async function deleteTask(task) {
  if (!window.confirm(`Hapus tugas "${task.title}"?`)) return
  try {
    await api.delete(`/tasks/${task.id}`)
    await loadTasks()
  } catch (e) { error.value = messageFrom(e) }
}

function clearSession() {
  localStorage.removeItem('uts_token')
  token.value = ''; user.value = null; tasks.value = []
}

async function logout() {
  try { await api.post('/logout') } catch (_) { /* tetap bersihkan sesi lokal */ }
  clearSession()
  success.value = 'Logout berhasil.'
}

onMounted(loadTasks)
</script>

<template>
  <main class="wrap">
    <header><h1>UTS — Sistem Manajemen Tugas</h1><p>Laravel REST API + Vue.js + Axios</p></header>
    <div v-if="error" class="alert error">{{ error }}</div>
    <div v-if="success" class="alert success">{{ success }}</div>

    <section v-if="!token" class="card">
      <h2>{{ mode === 'login' ? 'Masuk' : 'Registrasi' }}</h2>
      <form @submit.prevent="authenticate">
        <label v-if="mode === 'register'">Nama <input v-model="name" required placeholder="Nama lengkap" /></label>
        <label>Email <input v-model="email" type="email" required placeholder="contoh@email.com" /></label>
        <label>Password <input v-model="password" type="password" minlength="8" required /></label>
        <button :disabled="loading">{{ loading ? 'Memproses...' : mode === 'login' ? 'Login' : 'Daftar' }}</button>
      </form>
      <p><button class="link" @click="mode = mode === 'login' ? 'register' : 'login'; error = ''">
        {{ mode === 'login' ? 'Belum punya akun? Daftar' : 'Sudah punya akun? Login' }}
      </button></p>
    </section>

    <template v-else>
      <section class="card flex"><span>Halo, <strong>{{ user?.name || 'Pengguna' }}</strong></span><button class="small" @click="logout">Logout</button></section>
      <section class="card">
        <h2>Tambah tugas</h2>
        <form @submit.prevent="addTask">
          <label>Judul <input v-model="title" required maxlength="255" placeholder="Mis. selesaikan laporan UTS" /></label>
          <label>Deskripsi <textarea v-model="description" rows="3" /></label>
          <label>Tenggat <input v-model="dueDate" type="date" /></label>
          <button>Tambah tugas</button>
        </form>
      </section>
      <section class="card">
        <h2>Daftar tugas ({{ tasks.length }})</h2>
        <p v-if="tasks.length === 0">Belum ada tugas. Tambahkan tugas pertama kamu.</p>
        <article v-for="task in tasks" :key="task.id" class="task">
          <div><strong :class="{ crossed: task.status === 'done' }">{{ task.title }}</strong>
            <p>{{ task.description || 'Tanpa deskripsi' }}</p>
            <small>Status: {{ task.status }} <span v-if="task.due_date"> | Tenggat: {{ task.due_date.slice(0, 10) }}</span></small>
          </div>
          <div class="actions"><button class="small" @click="toggleTask(task)">{{ task.status === 'done' ? 'Batal selesai' : 'Selesai' }}</button><button class="small" @click="editTask(task)">Edit</button><button class="small danger" @click="deleteTask(task)">Hapus</button></div>
        </article>
      </section>
    </template>
  </main>
</template>

<style>
* {box-sizing:border-box} body{margin:0;background:#f2f5f9;color:#18283b;font-family:system-ui,Arial,sans-serif}
.wrap{max-width:830px;margin:32px auto;padding:0 18px} header{margin-bottom:20px} h1{margin:0 0 8px;font-size:28px}
header p{color:#64748b;margin:0} h2{font-size:20px;margin:0 0 18px}.card{background:white;border:1px solid #dde5ed;border-radius:12px;padding:22px;margin:16px 0;box-shadow:0 2px 10px #1620330a}
label{display:block;margin-bottom:12px;font-weight:600}input,textarea{display:block;width:100%;border:1px solid #bcc8d6;border-radius:7px;padding:11px;font:inherit;margin-top:6px}
button{border:0;background:#1767d2;color:white;border-radius:8px;padding:10px 16px;cursor:pointer;font-weight:600}button:disabled{opacity:.6;cursor:wait}
button.small{padding:8px 10px;font-size:12px}button.danger{background:#bd3535}button.link{color:#1767d2;background:none;padding:0}
.flex{display:flex;justify-content:space-between;align-items:center}.task{display:flex;justify-content:space-between;gap:16px;border-top:1px solid #e5eaf0;padding:16px 0}
.task p{margin:6px 0;color:#4e6073}.task small{color:#64748b}.actions{display:flex;align-items:center;gap:5px;flex-wrap:wrap}.crossed{text-decoration:line-through;color:#6c7b8c}
.alert{padding:12px 15px;border-radius:8px;margin:10px 0}.error{background:#fee4e2;color:#9c2622}.success{background:#dcfce7;color:#166534}
@media(max-width:630px){.task{display:block}.actions{margin-top:12px}}
</style>
```

### `frontend/src/lib/api.js`

```javascript
import axios from 'axios'

const api = axios.create({ baseURL: '/api', headers: { Accept: 'application/json' } })

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('uts_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

export default api
```

### `frontend/src/main.js`

```javascript
import { createApp } from 'vue'
import App from './App.vue'

createApp(App).mount('#app')
```

### `frontend/vite.config.js`

```javascript
import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  resolve: { alias: { '@': fileURLToPath(new URL('./src', import.meta.url)) } },
  server: {
    host: '0.0.0.0',
    proxy: {
      '/api': { target: 'http://127.0.0.1:8000', changeOrigin: true },
    },
  },
})
```

---

**Akhir panduan.** Laksanakan satu tahap, cek syarat LULUS, simpan screenshot, lalu lanjut. Semua URL bertanda `USERNAME`, NIM, dan contoh password harus diganti sesuai keadaan.
