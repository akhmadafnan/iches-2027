# ENGINEERING STANDARDS — OOP PHP, LARAVEL & LIVEWIRE

**Versi:** 1.0 | **Tanggal:** 20 November 2026  
**Wajib untuk seluruh code baru.** Fondasi Laravel 13 + Livewire 4 kini sudah terpasang pada proyek. Periksa versi paket aktual sebelum menambah fitur, bukan menggunakan asumsi lama. Product rules follow [CURRENT STATE & DECISIONS](CURRENT-STATE-AND-DECISIONS.md), not superseded earlier workflows.

**Referensi resmi:**
- [Laravel 13 Starter Kits — source 13.x](https://github.com/laravel/docs/blob/13.x/starter-kits.md) — official Livewire Starter Kit.
- [Laravel 13 Documentation — official source 13.x](https://github.com/laravel/docs/tree/13.x) — ORM, Policies, authorization, validation, transactions, queues, notifications.
- [Livewire 4 Components](https://livewire.laravel.com/docs/4.x/components) — class-based components tetap didukung.
- [Livewire 4 Validation](https://livewire.laravel.com/docs/4.x/validation).
- [PHP-FIG PSR-12](https://www.php-fig.org/psr/psr-12/); gunakan Laravel Pint sebagai formatter.

**Dua repository rujukan teknis WAJIB (disepakati pemilik proyek 20 November 2026):**
- **[laravel/docs — 13.x](https://github.com/laravel/docs/tree/13.x)**: sumber dokumentasi Laravel 13; branch resminya `13.x`, termasuk [releases.md](https://github.com/laravel/docs/blob/13.x/releases.md) dan [structure.md](https://github.com/laravel/docs/blob/13.x/structure.md).
- **[laravel/livewire-starter-kit — main](https://github.com/laravel/livewire-starter-kit)**: source of truth untuk struktur starter kit, komponen, Fortify dan dependency; [composer.json](https://github.com/laravel/livewire-starter-kit/blob/main/composer.json) pada pemeriksaan 20 Nov 2026 menetapkan PHP `^8.3`, Laravel `^13.17`, Livewire `^4.1`, Flux `^2.13.1`, Fortify `^1.37.2`.

**Panduan konflik versi:** ikuti dependency **yang benar-benar terpasang** pada project dan source dokumentasi upstream yang cocok; periksa ulang upstream sebelum coding. Jangan menerapkan API Livewire 3 atau tutorial Laravel lama tanpa verifikasi. Starter Kit nyata memiliki actions Fortify, pages Blade/Livewire, layout dan file `routes/web.php`; **extend bawaan, jangan bangun auth sendiri**. Kode bisnis ICHES diutamakan class-based/OOP sedangkan scaffolding bawaan tidak wajib direstrukturisasi hanya demi gaya. PRD tetap mengatur keputusan produk, repo upstream mengatur penggunaan framework.

**Standar visual aplikasi yang telah disetujui (20 November 2026):** gunakan [Flux UI theming resmi](https://fluxui.dev/docs/theming) dengan CSS variables di `resources/css/app.css`: light `--color-accent` dan `--color-accent-content` = `--color-emerald-600`, foreground putih; dark accent emerald-600, content emerald-400, foreground putih. Layout aplikasi privat menggunakan hierarki tipografi jelas, card/panel yang rapi, tabel Flux penuh lebar dengan nomor urut dan pagination status-kiri/actions-kanan, dan modal Flux untuk edit akses. Uji desain melalui render browser desktop/mobile serta targeted Livewire tests. Website publik tetap menunggu pembahasan pemilik proyek.

## 1. Baseline stack

- Laravel **13.x** dan official **Livewire Starter Kit** dengan versi yang kompatibel, PHP **8.3 atau lebih tinggi** sesuai kebutuhan Laravel 13, Composer, MySQL.
- Livewire **4.x jika versi yang terpasang mendukungnya**; jangan mencampurkan API Livewire v3 dan v4 tanpa memeriksa versi proyek.
- Blade, Tailwind, Flux UI dari starter kit jika tersedia dan sesuai; hindari frontend framework kedua.
- PSR-4 autoload, PSR-12/Pint, PHPUnit/Pest sesuai apa yang dipakai starter kit.
- Jangan mengedit vendor/, framework, atau scaffold auth jika tidak diperlukan. Pertahankan konvensi starter kit kecuali ada alasan konkret.

## 2. Prinsip OOP yang wajib

1. **Encapsulation:** keadaan domain dimodifikasi melalui action/service yang memvalidasi hak dan aturan; jangan mass-assign status sensitif dari Livewire.
2. **Single Responsibility:** komponen Livewire menampilkan, menerima input, memanggil use-case; bukan tempat seluruh logika transaksi.
3. **Separation of Concerns:** Eloquent model memodelkan data/relasi dan query sederhana; Action/Service menjalankan alur bisnis; Policy mengotorisasi; Notification mengirim pesan.
4. **Dependency Injection:** constructor/method injection dari Laravel container; hindari new class manually untuk dependency kompleks.
5. **Interface Segregation / DIP secara pragmatis:** interface hanya bila ada dua implementasi, integration boundary atau kebutuhan test yang benar-benar mendukungnya. **Tidak wajib** membuat interface untuk setiap class.
6. **Composition over Inheritance:** gunakan komposisi service/action, bukan inheritance chain atau super-base-controller.
7. **DRY tanpa overengineering:** aturan finance/paper/QR disentralisasi; jangan membuat generic repository/service abstractions yang tidak menyelesaikan masalah.
8. **Explicit behavior:** nama class/action menggambarkan pekerjaan, misalnya SubmitAbstract, ClaimReview, PublishDecision, VerifyPayment, RedeemQrService.
9. **Type safety:** typed properties/arguments/returns, enum untuk state stabil, nullable bila diperlukan, readonly DTO untuk data valid yang perlu dibawa lintas layer.
10. **No magic constants:** status dan business codes central, configurable Admin bila kebijakan dapat berubah.

## 3. Struktur folder target setelah Laravel dibuat

    app/
      Actions/
        Auth/
        Conference/
        Review/
        Finance/
        Programs/
        QrServices/
        Documents/
      Data/                 # DTO bila membantu, tidak wajib untuk setiap form
      Enums/                # Status yang benar-benar stabil
      Http/
        Controllers/        # Endpoint download, API/minimal routes bila perlu
        Requests/           # Form Request khusus HTTP non-Livewire
      Livewire/
        Dashboard/
        Conference/
        Reviewer/
        Committee/
        Admin/
      Models/               # Eloquent model + scopes/relationships
      Notifications/
      Policies/
      Services/             # Reusable domain integrations, seperti CardPngRenderer
      Support/              # Utilities kecil yang benar-benar reusable
    database/
      factories/
      migrations/
      seeders/
    resources/
      views/
        components/
        livewire/          # Sesuaikan dengan konvensi starter kit aktual
    tests/
      Feature/
      Unit/

Folder merupakan **arah organisasi**, bukan daftar folder kosong yang harus dibuat pada hari pertama. Livewire v4 mempunyai beberapa bentuk komponen; untuk flow domain penting pilih **class-based Livewire** agar OOP dan dependency jelas. Jika starter kit memakai single-file pada halaman auth bawaan, jangan menulis ulang semua hanya untuk konsistensi kosmetik.

## 4. Responsibilities dan boundaries

| Area | Yang boleh dilakukan | Yang tidak boleh |
|---|---|---|
| Route | Mapping URL → controller/component, auth/policy middleware | Membuat seluruh keputusan bisnis inline |
| Livewire Component | Display state, validate UI input, authorize, call action, feedback | Finance verification logic panjang, direct public file exposure |
| Action / Use Case | Satu operasi bisnis lengkap; transaction, invariants, authorized actor | Merender HTML atau mengeluarkan teks marketing |
| Service | Kemampuan dipakai berulang, e.g. QR validation, PNG generation | Mengumpulkan semua domain menjadi God Service |
| Eloquent Model | Relasi, casts, scopes, persistence-friendly helpers | Listener acak yang membuat invoice atau grant entitlement tanpa jelas |
| Policy | Who may do action on which resource | Mengandalkan menu hidden sebagai security |
| Enum / Value Object | Menamai state dan nilai yang stabil | Menaruh harga/deadline yang harus bisa diedit Admin |
| Notification | Komunikasi setelah bisnis selesai | Mengubah keputusan/payment diam-diam |
| Migration | Constraints, FK, unique, index | Menaruh rahasia atau data production asli |
| Test | Memverifikasi kontrak domain dan security | Mengklaim integrasi tanpa melakukan uji |

### Contoh batas tindakan

    [Livewire SubmitAbstract]
        -> validates input and current user
        -> authorizes create/submit
        -> calls [SubmitAbstract Action]
        -> action checks paper owner and current state
        -> action stores metadata/file references transactionally
        -> action records history and fires after-commit event
        -> Livewire shows success and next step

Pola yang sama berlaku untuk ClaimReview, PublishDecision, VerifyPayment, RedeemQrService. Code tidak harus mengikuti semua layer secara ritual: CRUD yang sangat sederhana bisa langsung lewat Livewire+Model dengan Policy, sementara alur sensitif memakai Action yang teruji.

## 5. PHP coding conventions

- Gunakan namespace PSR-4 dan nama class PascalCase; method/variabel camelCase; tabel snake_case.
- Prefer class final untuk action yang tidak dirancang untuk inheritance, ketika cocok; bukan kewajiban pada Eloquent/Livewire framework classes.
- Untuk file PHP baru non-framework-generated, pertimbangkan declare(strict_types=1) agar kontrak jelas. Hormati conventions starter kit.
- Hindari metode > beberapa puluh baris yang mencampurkan validasi/IO/bisnis; ekstrak bila memang meningkatkan keterbacaan.
- Return type, parameter type dan PHPDoc untuk relasi/generic collection penting. Jangan menambah komentar yang sekadar mengulang nama kode.
- Gunakan native enum/cast Laravel untuk state (Draft/Submitted/Accepted) yang jelas; uang sebagai integer minor units + currency, jangan float.
- Simpan waktu secara konsisten dan tampilkan timezone acara Asia/Jakarta untuk jadwal.
- Validate semua data dari Livewire/HTTP dan enforce constraints di DB juga.
- Error domain bisa berupa ValidationException atau exception bisnis yang spesifik dan dipresentasikan jelas ke user, tanpa membuka stack trace.
- Hindari raw SQL bila Eloquent/query builder cukup; untuk race-condition gunakan transaksi/lock + unique DB constraint.
- Format dengan Laravel Pint sesuai config, jangan memperdebatkan gaya lint yang bisa diselesaikan otomatis.

## 6. Laravel/Livewire implementation rules

**Auth:** Gunakan bawaan starter kit. User tidak dapat menentukan system role saat register. Password reset/verification menggunakan fitur resmi.

**Data scope:** Query selalu dibatasi owner/program/institution/assignment. Download lewat authorized signed/controller response, bukan file public yang mudah ditebak.

**State:** Domain state transitions hanya melalui actions, bukan update langsung dari form status umum. Published Decision berbeda dari internal decision. QR redeemed berbeda dari scanned. Finance Verified berbeda dari upload proof.

**Livewire:** Komponen harus merespons loading/error, authorize pada method action, hindari public property untuk model sensitif tanpa sanitization, gunakan pagination/filter dari query server. Model binding frontend bukan security boundary.

**File:** Foto crop 1:1, abstract PDF, full paper, payment proof disimpan privat sesuai kebutuhan; validasi extension dan MIME, ukuran yang sesuai kebijakan operasional (tidak perlu menetapkan angka fiktif sekarang), safe filename dan access checks.

**QR:** Nomor yang tercetak bukan secret token. Token acak disimpan/diperiksa secara aman; tidak ada email/NIK di payload. Scanner membutuhkan HTTPS dan authorized Committee session. Admin membuat layanan berbasis service type, parameter dan entitlements. Double scan harus aman dalam transaksi.

**Payment:** Harga/snapshot mata uang dalam invoice; evidence privat. Hanya petugas Finance yang diizinkan dapat membuat Verified setelah pemeriksaan mutasi. Co-Host dan paket personal punya logika rights berbeda.

**Events and mail:** Notification dikirim setelah commit; gunakan Laravel queue bila tersedia. Tidak mengirim email nyata saat local test kecuali memang dikonfigurasi aman.

**Caching:** Hindari optimisasi dini. Jangan cache hak akses/keputusan/entitlement secara tak terkendali hingga pengguna masih melihat hak lama.

## 7. Design patterns — when to use

- **Action / Command:** default untuk proses bisnis (submit, claim, decide, verify, redeem).
- **Policy:** default untuk scope dan izin.
- **DTO:** untuk data tervalidasi yang perlu dipindah antar beberapa method.
- **Strategy:** hanya jika ada beberapa implementasi nyata (misal provider output dokumen) dan tidak memperumit MVP.
- **Observer/Event:** ketika side effect jelas terpisah, bukan agar setiap CRUD menjadi event-driven.
- **Service:** untuk renderer kartu, QR generator, file/doc integration.
- **Repository pattern:** *tidak wajib*. Eloquent sudah menyediakan abstraction persistence. Tambahkan hanya jika diperlukan.
- **Factory:** untuk test data, bukan setiap object domain.

## 8. Testing minimum per layer

| Area | Test wajib |
|---|---|
| Auth + role | unauthorized actions 403, other user private data hidden |
| Submission | draft vs submitted, invalid PDF, owner, no screening |
| Reviewer | own-paper blocked, cross-reviewer access blocked, concurrent claims |
| Academic decision | internal result hidden; published Accepted unlock Full Paper |
| Finance | proof ≠ Verified; wrong actor denied; no double invoice |
| Package/Co-Host | Verified grants correct entitlements, 1 invoice, quotas according to approved fixed Co-Host rules |
| QR | eligible, not eligible, duplicate, two simultaneous scans, kit needs confirm |
| File download | owner scope and private files enforced |
| UI | Livewire validation + browser smoke; camera on real phone for scan |

Preferred commands **once app exists**: Composer test command / php artisan test with targeted filter, vendor/bin/pint --test (or pint in scoped files), and npm run build if frontend assets changed. **Never claim commands have run before installation.**

## 9. Review checklist for every coding task

1. Correct feature and task ID? No new extra modules?
2. Does code reuse starter kit and Laravel classes?
3. Is business logic out of Livewire UI?
4. Are typed PHP methods, responsibilities, naming clear?
5. Does every protected action check authorization server-side?
6. Are validation and DB integrity both enforced?
7. No file/secret/PII exposure?
8. Is race-condition case tested if relevant?
9. Targeted test and basic UI smoke PASS with evidence?
10. Does owner still control all public content/design decisions?

## 10. Documentation-to-code mapping

Blueprint explains what/why; [PRD](PRD.md) enumerates behavior; [TASKS](TASKS.md) determines order and success; [WORKING-RULES](WORKING-RULES.md) limits change scope; this document defines how PHP/Laravel OOP is written. Engineering details can evolve as the official starter kit evolves; changes must not silently alter business decisions.

**Rule of thumb:** make it OOP, idiomatic Laravel, short, tested and easy for the next AI agent to understand.
