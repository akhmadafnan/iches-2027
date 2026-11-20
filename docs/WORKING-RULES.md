# ATURAN MAIN PENGEMBANGAN — ICHES 2027

**Versi:** 1.0 | **Tanggal:** 20 November 2026  
**Tujuan:** cepat selesai, sesuai harapan pemilik proyek, tetap bisa dirawat dan tidak salah sasaran.  
**Acuan:** [PRD](PRD.md), [TASKS](TASKS.md), [OOP Standards](OOP-LARAVEL-LIVEWIRE-STANDARDS.md).

## 1. Prinsip utama

1. **User journey first.** Utamakan fitur benar-benar bisa dipakai dari awal sampai akhir, bukan membuat banyak menu kosong.
2. **Keep it simple.** Implementasi monolith Laravel Livewire yang idiomatis; pilih solusi sederhana dan stabil.
3. **One source of truth.** Blueprint → PRD → TASKS → source/test. Jika bertentangan, keputusan eksplisit terbaru pemilik proyek mengatasi draft terdahulu; perbarui dokumen terkait.
4. **No invented policy.** Jangan mengarang kategori tarif, bank, speaker, start hour, kuota, kriteria sertifikat, jumlah reviewer atau fasilitas baru.
5. **No unauthorized public design.** Website publik dan copy frontend masih perlu diskusi terpisah. Backend dan dashboard privat boleh dikerjakan setelah GO CODING.
6. **OOP Laravel.** Business logic di actions/services, authorization di policies, state terjaga, Livewire hanya UI orchestration.
7. **Targeted verification.** Uji file/perubahan relevan; jangan menghabiskan waktu menjalankan seluruh suite berulang kali jika tidak dibutuhkan.
8. **Safe by default.** Akses data, transfer payment, private files, token QR, transaksi double-scan wajib benar.
9. **Git simple.** Commit kecil yang berarti; tanpa PR atau persetujuan berlapis yang tidak diperlukan pada kerja solo, tetapi tidak boleh force-push/merusak history.
10. **Honest status.** DONE hanya setelah terbukti. Kegagalan atau belum dites dijelaskan singkat, jangan ditutupi.

## 2. Siapa mengerjakan apa?

**Pemilik proyek:** menetapkan arah produk, mengoreksi UI/fitur dan memberikan instruksi mulai/melanjutkan pekerjaan; memutuskan kebijakan yang benar-benar diperlukan.

**ChatGPT:** analis produk, penjaga PRD/TASKS, membuat work order pendek untuk AI coding, memeriksa hasil dan membantu menemukan solusi tercepat.

**AI Coding Agent (Codex/agent lain):** mengimplementasikan scope yang diberikan, menjalankan tes yang relevan bila aman dan tersedia, melaporkan hasil serta blocker. Agent tidak mengambil alih keputusan produk.

Satu task bisa dikerjakan oleh satu agent; hindari dua agent mengedit file yang sama tanpa koordinasi.

## 3. Gerbang kerja praktis

- **Dokumentasi sekarang:** boleh membuat/memperbarui README, PRD, task, aturan dan standar sesuai permintaan pengguna. Tidak membuat Laravel sebelum ada arahan.
- **GO CODING:** pemilik menyebut mulai/kerjakan task. Setelah GO, agent boleh mengedit source hanya untuk task yang disebut.
- **Database:** migration pada database development/test yang diotorisasi; **tidak boleh** migrate:fresh, reset, truncate, drop atau merusak data real tanpa permintaan eksplisit.
- **Git:** perubahan docs dapat langsung di-commit bila diminta seperti sesi ini. Untuk source, commit/push mengikuti ruang kerja yang disetujui pengguna; jangan merge/rebase/force push atau ganti default branch secara diam-diam.
- **Production:** deployment, real email massal, mutasi rekening, pengiriman pesan kepada pengguna nyata, dan perubahan data real adalah tindakan terpisah yang memerlukan instruksi jelas.
- **Blocker:** jika langkah penting tidak dapat diuji/diakses, hentikan langkah yang berisiko dan laporkan; lanjutkan bagian lain yang aman.

Tidak perlu seremonial GO terpisah untuk hal remeh dalam satu task; tindakan destruktif/eksternal tetap tidak boleh diasumsikan.

## 4. Pola task yang wajib diikuti (singkat)

1. Ambil **task ID** dari TASKS.
2. Baca hanya paragraf PRD yang mendukung task tersebut dan source yang relevan.
3. Nyatakan hasil akhir yang akan dibuktikan.
4. Kerjakan implementasi paling kecil yang memenuhi acceptance.
5. Tambahkan validation, policy, migration constraint dan error handling yang diperlukan.
6. Jalankan formatter/test terarah + browser smoke jika menyentuh UI.
7. Perbaiki langsung temuan yang masih dalam scope.
8. Laporkan hasil/commit/test dan centang status DONE hanya bila sesuai bukti.

**Template perintah untuk agent:**

> Kerjakan TASKS [ID]. Ikuti docs/PRD.md, docs/WORKING-RULES.md, docs/OOP-LARAVEL-LIVEWIRE-STANDARDS.md dan AGENTS.md. Baca hanya source yang terkait. Jangan ubah frontend publik yang masih HOLD, jangan perluas scope, gunakan Laravel OOP standar. Implementasikan, jalankan tes terarah dan berikan bukti singkat.

## 5. Aturan kualitas yang tidak bisa ditawar

- Server memeriksa otorisasi untuk setiap aksi dan download privat.
- Auth/role tidak dapat ditingkatkan hanya dengan memanipulasi form/input.
- File PDF/foto/bukti bayar divalidasi dan tidak dibagikan publik sembarangan.
- Claim reviewer dan redemption QR menangani request bersamaan.
- Pembayaran Verified harus sesuai pemeriksaan mutasi dan hanya oleh petugas berizin.
- Decision internal tidak boleh bocor ke author.
- Token QR bukan email/NIK/ID sequential mentah dan dapat direvoke.
- Perubahan penting menyimpan actor dan timestamp.
- Uji kegagalan/error state, tidak hanya happy path.

## 6. Aturan UI

- **Dashboard privat**: boleh dibuat minimal dan responsif sesuai PRD agar alur bekerja, tanpa mencari template baru untuk setiap modul.
- **Scanner petugas**: mobile-first, tombol pilihan layanan jelas, hasil scan terlihat, konfirmasi fisik satu langkah.
- **Website publik**: konten, style, struktur dan copy belum final. Rancang bersama pemilik proyek sebelum mengimplementasi halaman publik. Jangan menganggap daftar halaman lama sebagai desain yang sudah disetujui.
- Gunakan logo ICHES, UNISYA, Forum Pimpinan PTKIS asli untuk ID Card setelah aset resmi tersedia. Jangan mendesain ulang logo.

## 7. Aturan Git sederhana

- Repo tunggal: https://github.com/akhmadafnan/iches-2027
- main menyimpan baseline yang dapat dipakai dan dibaca; gunakan commit deskriptif per paket kerja.
- Branch tambahan boleh dipakai untuk perubahan berisiko/besar, tetapi tidak wajib membuat PR untuk setiap perubahan dokumentasi atau satu fitur solo.
- Jangan menyalin seluruh histori/struktur dari repo ICHES lama.
- Jangan commit .env, database dump peserta, API key, password, user token, bukti transaksi nyata atau file privat.
- Jika konflik, fetch dan periksa diff dahulu; jangan force-push untuk mengatasi konflik.

## 8. Format laporan task (maksimal ringkas)

**Task:** TASK-ID  
**Status:** DONE / BLOCKED / PARTIAL  
**Hasil:** alur yang kini berfungsi  
**Files/Commit:** path dan SHA  
**Tests:** perintah + PASS/FAIL/NOT RUN  
**Blocker:** hanya yang menghalangi  
**Next:** task relevan berikutnya.

Tidak perlu esai audit 20 halaman untuk setiap perubahan kecil. Review tetap serius pada bagian yang menyentuh uang, privacy, reviewer decision, dan QR redemption.

## 9. Aturan perubahan

Perubahan produk kecil yang tidak mengubah bisnis boleh langsung masuk task aktif. Perubahan yang mengubah harga, hak program, status akademik, cetak QR, keterlihatan dokumen atau registrasi perlu tercatat pada PRD dan diketahui pemilik sebelum dikerjakan.

Jika fitur belum dibahas, jangan menjadikannya dependency palsu. Buat implementasi sederhana yang tidak mengarang kebijakan, atau tunda modul itu saja.

## 10. Selesai berarti bisa dibuktikan

- Source code dan tes ada.
- Alur user ditunjukkan pada data demo atau lingkungan yang diizinkan.
- State, hak akses dan error diperiksa.
- Tidak ada perubahan di luar task.
- Status di TASKS diperbarui dengan commit/test.
- Pekerjaan yang belum bisa diuji **bukan** dinyatakan PASS.

**Kesimpulan:** cepat, fokus pada fungsi, OOP standar, Git sederhana, dan tidak ada spekulasi konten publik.
