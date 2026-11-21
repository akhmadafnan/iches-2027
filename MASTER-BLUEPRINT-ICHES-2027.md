# MASTER BLUEPRINT
## ICHES 2027 — Integrated Conference & Event Management System

**Versi:** 1.0 — Draft untuk Evaluasi Pemilik Proyek  
**Tanggal:** 20 November 2026  
**Target teknologi:** Laravel + Livewire Starter Kit + MySQL  
**Status:** Rancangan produk, belum implementasi  
**Acuan:** TOR ICHES 2027, keputusan bisnis repository lama, dan penyederhanaan kebutuhan terbaru

---

# BAB I. VISI DAN ARAH PENGEMBANGAN

## 1.1. Identitas aplikasi

**Nama aplikasi:** ICHES 2027 Conference Management System  
**Nama kegiatan:** The 6th International Conference on Humanity, Education, and Society (ICHES 2027)  
**Penyelenggara:** Universitas Islam Syarifuddin (UNISYA), Lumajang  
**Pelaksanaan utama:** 5–6 Januari 2027

Aplikasi merupakan sistem informasi terintegrasi yang mendukung penyelenggaraan konferensi internasional dan seluruh rangkaian kegiatan pendampingnya, mulai dari pendaftaran peserta hingga pengelolaan hasil kegiatan.

Sistem dikembangkan dalam satu aplikasi web dengan website publik, personal dashboard, reviewer workspace, dashboard panitia, dan backoffice administrator.

## 1.2. Tujuan utama

Aplikasi dirancang untuk mencapai enam tujuan:

1. Memudahkan peserta mendaftar dan mengikuti berbagai program ICHES melalui satu akun.
2. Menyediakan sistem pengiriman abstrak dan Full Paper beserta tracking akademik yang mudah dipahami.
3. Mempermudah reviewer dan penyelenggara menjalankan proses penilaian.
4. Mengintegrasikan pengelolaan keikutsertaan, pembayaran, jadwal, dan dokumen peserta.
5. Memanfaatkan satu QR individu untuk ID Card, kehadiran, konsumsi, conference kit, dan layanan kegiatan.
6. Menyediakan informasi operasional dan laporan yang dapat dipantau administrator.

## 1.3. Filosofi desain

**Simple for Users, Flexible for Administrators.**

Prinsip penerapannya:

- Peserta hanya melihat aktivitas yang berhubungan dengan dirinya.
- Reviewer langsung melihat paper yang bisa dinilai dan tugas yang sedang dikerjakan.
- Petugas hanya melihat layanan scanner yang ditugaskan.
- Administrator mengelola program dan operasional tanpa perlu mengubah source code untuk perubahan data rutin.
- Tampilan profesional tidak boleh mengorbankan kejelasan alur kerja.
- Fitur yang tidak dibutuhkan untuk pelaksanaan ICHES 2027 tidak dibangun.

**Keputusan arsitektur:** membuat aplikasi baru, bukan melanjutkan kompleksitas repository lama. Aturan bisnis yang masih relevan tetap digunakan.

---

# BAB II. STRUKTUR PENGGUNA DAN AKSES

## 2.1. Satu akun untuk semua aktivitas

Setiap orang hanya mempunyai satu akun ICHES.

Akun tersebut dapat digunakan untuk mengirim paper, mendaftar kegiatan, mengikuti paket internasional, mengakses jadwal, melihat ID Card, mengunduh dokumen, dan memantau kehadiran.

Pengguna tidak perlu mendaftar ulang ketika menambah program.

## 2.2. Model registrasi

Form registrasi awal hanya meminta:

- Nama lengkap.
- Email.
- Password.
- Institusi/afiliasi.
- Negara.
- Tujuan awal keikutsertaan.

Pada bagian tujuan awal, pengguna dapat memilih:

1. **Submit a Paper** — ingin mengikuti konferensi sebagai pemakalah.
2. **Join as Participant** — ingin menghadiri konferensi tanpa paper.
3. **Explore Programs** — ingin melihat program terlebih dahulu.

Pilihan tersebut hanya menentukan tampilan awal dashboard. Pengguna tetap boleh mengikuti program lain setelah akun aktif.

Foto profil dapat dilengkapi melalui dashboard setelah registrasi.

## 2.3. Empat kelompok akses utama

| Akses | Fungsi |
|---|---|
| User | Presenter, peserta konferensi, peserta program, dan pengguna umum |
| Reviewer | Menilai paper melalui Reviewer Workspace |
| Committee | Melakukan tugas operasional yang diberikan Admin |
| Admin | Mengelola aplikasi, data, program, dan konfigurasi |

Satu orang boleh memiliki akses tambahan tanpa membuat akun baru.

Tugas khusus seperti **PIC Co-Host, pengambil keputusan akademik, petugas keuangan, dan moderator** diberikan sebagai penugasan atau izin terbatas dalam sistem. Tidak perlu membuat dashboard dan role permanen yang terpisah untuk setiap jabatan tersebut.

Keputusan akademik, verifikasi pembayaran, dan penerbitan dokumen tetap dibatasi kepada pengguna yang mendapat kewenangan.

---

# BAB III. STRUKTUR LIMA PROGRAM ICHES

ICHES 2027 terdiri atas lima program yang dikelola secara terpisah tetapi terhubung.

## 3.1. International Conference

**Fokus:** konferensi, paper, presentasi, dan kegiatan akademik.

Fitur utama:

- Pendaftaran Presenter atau Participant.
- Pemilihan mode Online atau Onsite sesuai kategori yang tersedia.
- Pemilihan conference track.
- Abstract Submission.
- Upload Abstract PDF.
- Tracking submission.
- Reviewer Self-Claim.
- Review dan rekomendasi akademik.
- Keputusan resmi seleksi abstrak hanya Accept atau Reject (keputusan akademik terpisah dari rekomendasi reviewer).
- Letter of Acceptance.
- Upload Full Paper.
- Pengelolaan parallel session.
- Jadwal presentasi, ruangan, dan Zoom.
- Konfirmasi paper telah dipresentasikan.
- Sertifikat dan dokumen terkait.
- Rekap akademik.

Delapan conference track disediakan sebagai data awal sesuai TOR dan dapat dikelola Admin.

## 3.2. Pembinaan Riset dan PKM

**Fokus:** keikutsertaan dan penyelenggaraan kegiatan pembinaan.

Fitur utama:

- Informasi kegiatan dan agenda.
- Data narasumber.
- Daftar peserta yang memperoleh akses.
- Jadwal dan lokasi kegiatan.
- QR absensi kegiatan.
- Riwayat keikutsertaan.
- Dokumen atau materi kegiatan jika tersedia.
- Rekap peserta dan kehadiran.

Tidak memerlukan sistem submission akademik tersendiri.

## 3.3. International MoU

**Fokus:** penyelenggaraan agenda kerja sama internasional.

Fitur utama:

- Master institusi mitra.
- Data PIC dan delegasi institusi.
- Informasi agenda MoU.
- Data dokumen atau tautan dokumen MoU.
- Daftar peserta kegiatan.
- Status pelaksanaan penandatanganan.
- QR absensi.
- Rekap kerja sama dan dokumentasi.

Peserta yang mengikuti kegiatan MoU melalui Paket Internasional tidak otomatis menjadi delegasi resmi atau penandatangan MoU.

## 3.4. International Community Service

**Fokus:** kegiatan pengabdian internasional di lapangan.

Fitur utama:

- Informasi program dan lokasi.
- Daftar peserta.
- Pembagian kelompok pengabdian.
- Penugasan pendamping/PIC.
- Jadwal dan agenda.
- QR check-in kegiatan.
- Dokumentasi kegiatan.
- Rekap keikutsertaan dan hasil pelaksanaan.

Pembagian kelompok dibuat sederhana melalui pengaturan Admin, tanpa sistem penjadwalan lapangan yang kompleks.

## 3.5. Sustainable Destination Study

**Fokus:** kunjungan dan studi destinasi berkelanjutan.

Fitur utama:

- Informasi perjalanan.
- Daftar peserta dan manifest.
- Jadwal kegiatan.
- Informasi rombongan.
- QR check-in pada titik yang ditetapkan.
- Pengelolaan kebutuhan layanan peserta.
- Dokumentasi dan rekap kegiatan.

Detail kendaraan dan pembagian rombongan dapat ditambahkan melalui data Admin jika memang digunakan dalam operasional.

## 3.6. Hubungan antarprogram

Kelima program memiliki:

**Komponen bersama:**
- Akun dan profil pengguna.
- Data keikutsertaan.
- Dashboard pribadi.
- QR individu.
- ID Card.
- Jadwal.
- Notifikasi.
- Dokumen.
- Kehadiran.
- Laporan.

**Komponen khusus:** hanya fitur yang diperlukan oleh masing-masing program.

Dengan model tersebut, tidak ada lima database pengguna, lima halaman registrasi akun, atau lima sistem QR yang berbeda.

---

# BAB IV. MODEL KEIKUTSERTAAN DAN PAKET PROGRAM

## 4.1. International Conference

Pengguna dapat mengikuti Conference sebagai:

- Presenter.
- Non-Presenter Participant.

Mode keikutsertaan dapat berupa online atau onsite sesuai kategori pendaftaran yang tersedia.

Presenter mengikuti alur paper. Non-Presenter Participant tidak perlu melewati proses submission atau review.

## 4.2. Paket Program Internasional

Disediakan satu produk:

**International Program Package — Rp1.000.000 per orang.**

Satu pembelian memberikan akses ke empat kegiatan:

1. International MoU.
2. Pembinaan Riset dan PKM.
3. International Community Service.
4. Sustainable Destination Study.

Alur pengguna:

**Dashboard → Select International Package → Invoice → Transfer → Upload Payment Proof → Finance Verification → Program Access Active.**

Keempat akses aktif setelah pembayaran berstatus Verified.

Peserta tetap memiliki catatan kehadiran yang terpisah pada masing-masing kegiatan.

## 4.3. Co-Host Institution

Co-Host merupakan jalur khusus institusi dalam International Conference.

Aturannya:

- Biaya Rp3.000.000 per institusi.
- Satu invoice institusi.
- Maksimal lima perwakilan.
- Lima jatah paper Conference.
- Satu perwakilan mengirim satu paper melalui akun sendiri.
- Fitur keikutsertaan sponsor aktif setelah pembayaran institusi Verified.

Setiap paper menjalani proses review normal dan tidak memperoleh keputusan Accept otomatis.

Anggota Co-Host dapat membeli Paket Internasional melalui dashboard pribadinya apabila ingin mengikuti empat program pendamping.

## 4.4. Prinsip penting

**Program, paket pembayaran, dan kehadiran adalah tiga hal berbeda.**

Seseorang dapat:

- Terdaftar pada program.
- Sudah membayar paket.
- Memiliki hak mengikuti kegiatan.
- Belum hadir.
- Sudah mengambil conference kit.
- Belum mengambil konsumsi.

Seluruh kondisi tersebut harus dapat dibedakan oleh sistem.

---

# BAB V. ALUR AKADEMIK INTERNATIONAL CONFERENCE

## 5.1. Tahap 1 — Submit Abstract

Presenter membuka menu **My Submissions → Submit Abstract**.

Form inti berisi:

- Judul paper.
- Conference track.
- Identitas penulis dan co-author.
- Abstract text.
- Keywords.
- Upload Abstract PDF.

Pengguna dapat menyimpan draft sebelum mengirim.

Setelah memilih Submit dan data dinyatakan valid, paper berstatus **Submitted** serta mendapatkan nomor submission.

Tidak diperlukan tahap Administrative Screening setelah submission yang valid.

## 5.2. Tahap 2 — Reviewer Self-Claim

Paper Submitted langsung tersedia di Reviewer Abstract Index.

Reviewer dapat:

- Melihat daftar paper yang tersedia.
- Mencari berdasarkan judul atau track.
- Membuka abstrak dan PDF.
- Mengklaim paper yang dapat direview.
- Menyimpan draft review.
- Mengirim rekomendasi dan komentar.

Reviewer tidak boleh mengklaim paper miliknya sendiri.

Tampilan reviewer difokuskan pada dua daftar utama:

**Available Papers** dan **My Reviews**.

Tidak perlu membangun sistem komunikasi reviewer-author yang rumit.

## 5.3. Tahap 3 — Keputusan akademik

Petugas akademik yang berwenang membuka hasil review dan menetapkan keputusan.

Status yang digunakan:

- Accepted.
- Rejected.

Untuk seleksi abstrak **tidak ada status Revisions Required**. Reviewer hanya mengisi General Comments dan rekomendasi Accept/Reject. Saran perbaikan dari komentar digunakan pada penyusunan Full Paper jika keputusan Accepted resmi dipublikasikan.

Keputusan internal belum otomatis terlihat oleh author.

Setelah pihak berwenang memilih **Publish Decision**, hasil muncul di dashboard author dan dapat diinformasikan melalui email.

Keputusan akhir tidak dibuat otomatis oleh rekomendasi reviewer.

## 5.4. Tahap 4 — Arahan Perbaikan Full Paper

Seleksi abstrak **tidak menggunakan status Revisions Required maupun pengiriman ulang abstrak**. Reviewer hanya menulis General Comments dan memilih Accept atau Reject. Setelah keputusan Accepted resmi dipublikasikan, komentar yang disetujui untuk dibagikan dapat menjadi panduan author saat menyusun Full Paper.

Paper ID dan histori submission/review tetap dipertahankan. Tidak ada pembukaan kembali Draft abstrak atau tahapan revisi abstrak karena komentar reviewer. Rekomendasi reviewer tetap internal sampai keputusan dipublikasikan.

## 5.5. Tahap 5 — LoA dan Full Paper

Jika keputusan **Accepted telah resmi diumumkan**, dashboard menyediakan:

- Status Accepted.
- Tautan Letter of Acceptance.
- Upload Full Paper.
- Informasi langkah berikutnya.

Admin dapat memasukkan tautan LoA PDF yang telah dibuat di Google Drive. Setiap author hanya melihat tautan yang terkait dengan paper miliknya.

**Full Paper tidak perlu menunggu pembayaran Verified.**

## 5.6. Tahap 6 — Parallel Session

Admin menempatkan paper Accepted ke sesi presentasi.

Author melihat:

- Nama sesi.
- Tanggal dan jam.
- Track.
- Moderator atau Session Chair.
- Ruangan bagi presenter offline.
- Tombol Join Zoom bagi presenter online.
- Informasi presentasi.

## 5.7. Tahap 7 — Setelah presentasi

Moderator atau petugas yang ditunjuk mengonfirmasi status paper:

**Presented** atau **Not Presented**.

Informasi tersebut digunakan untuk rekap akademik dan proses dokumen pascaacara.

Jika diperlukan evaluasi Full Paper lanjutan untuk publikasi, pengelola dapat menanganinya dalam fase setelah layanan inti stabil.

---

# BAB VI. PERSONAL DASHBOARD PESERTA

## 6.1. Konsep tampilan

Dashboard menggunakan antarmuka yang personal, responsif dan sederhana.

Menu utama:

1. **Overview**
2. **My Submissions**
3. **My Programs**
4. **My Schedule**
5. **My ID Card & QR**
6. **My Payments**
7. **My Documents**
8. **My Profile**

Menu tertentu dapat disembunyikan apabila belum relevan bagi pengguna.

## 6.2. Dashboard Overview

Halaman utama menampilkan:

- Sambutan dan identitas peserta.
- Status profil dan foto.
- Submission aktif, jika ada.
- Program yang diikuti.
- Jadwal terdekat.
- Informasi pembayaran penting.
- Tombol cepat menuju QR dan ID Card.
- Notifikasi tindakan yang harus diselesaikan.

## 6.3. My Submissions

Daftar seluruh paper milik pengguna.

Setiap paper mempunyai:

- Kode paper.
- Judul.
- Track.
- Status akademik.
- Riwayat progres.
- Dokumen.
- Tombol tindakan sesuai status.

## 6.4. My Programs

Pengguna dapat:

- Melihat program ICHES.
- Mengikuti Conference.
- Membeli Paket Internasional.
- Melihat keikutsertaan yang sudah aktif.
- Membuka jadwal dan informasi masing-masing program.

## 6.5. My Documents

Satu tempat untuk:

- LoA.
- Pedoman Full Paper.
- Panduan presentasi.
- Dokumen kegiatan.
- Sertifikat ketika telah diterbitkan.
- Dokumen lain yang dibagikan panitia.

---

# BAB VII. FOTO PROFIL DAN ID CARD

## 7.1. Upload dan crop foto

Foto dilengkapi setelah registrasi melalui **My Profile**.

Alur:

**Upload Photo → Crop Modal 1:1 → Adjust Position/Zoom → Save Photo → Update ID Card.**

Modal crop mengikuti referensi gambar yang telah diberikan:

- Area crop persegi.
- Drag untuk menggeser posisi foto.
- Zoom slider.
- Reset.
- Cancel.
- Use Photo.

Hasil crop menjadi sumber foto utama untuk profil dan kartu identitas.

## 7.2. Dua template ID Card

Mengikuti contoh desain yang telah diberikan:

### Participant ID Card

- Warna utama hijau.
- Header logo Forum Pimpinan PTKIS Tapal Kuda, UNISYA, dan ICHES.
- Tulisan vertikal **PARTICIPANT**.
- Foto peserta persegi.
- Nama lengkap.
- Institusi.
- QR Code.
- Kode identitas.

### Committee ID Card

- Warna utama biru navy.
- Header logo resmi yang sama.
- Tulisan vertikal **COMMITTEE**.
- Foto panitia persegi.
- Nama lengkap.
- Jabatan panitia.
- Institusi.
- QR Code.
- Kode identitas.

Desain final mempertahankan struktur referensi, menggunakan aset logo asli dan keluaran resolusi tinggi.

## 7.3. Fitur dashboard

Peserta dapat:

- View ID Card.
- Download ID Card PNG.
- Download QR PNG.

Admin dapat:

- Melihat daftar ID Card.
- Memfilter peserta online dan onsite.
- Menentukan daftar siap cetak.
- Mengunduh kartu secara massal.
- Melihat riwayat pencetakan.

## 7.4. Penghematan pencetakan

**ID Card fisik hanya disiapkan untuk peserta onsite yang telah dikonfirmasi dan panitia yang memerlukannya.**

Peserta online tetap mempunyai QR dan kartu digital, tetapi tidak otomatis masuk daftar cetak.

Jika seseorang mengikuti beberapa program onsite, cukup satu kartu fisik.

---

# BAB VIII. QR CODE DAN QR SERVICES MANAGEMENT

## 8.1. Satu QR pribadi

Setiap pengguna memiliki satu identitas QR untuk event ICHES 2027.

Nomor identitas dapat menggunakan format yang mudah dibaca seperti:

**ICH27-0001**

QR menggunakan token khusus yang diverifikasi sistem, bukan sekadar nomor identitas terbuka.

QR yang sama digunakan untuk seluruh layanan.

## 8.2. QR Service Builder

Admin mempunyai menu **QR Services Management** untuk membuat layanan validasi tanpa perubahan kode.

Jenis layanan dasar:

- Check-in / Attendance.
- Item Collection.
- Meal Collection.
- Access Verification.

Contoh layanan yang dapat dibuat:

- Conference Check-in.
- Conference Kit Collection.
- Lunch Day 1.
- Dinner Day 1.
- Pembinaan Attendance.
- International PKM Attendance.
- Destination Check-in.
- Layanan tambahan yang dibutuhkan panitia.

## 8.3. Konfigurasi layanan

Saat membuat layanan, Admin cukup menentukan:

- Nama layanan.
- Program atau kegiatan terkait.
- Jenis layanan.
- Peserta yang berhak.
- Waktu layanan.
- Jumlah pemakaian.
- Petugas yang ditugaskan.
- Konfirmasi otomatis atau manual.
- Status aktif atau nonaktif.

Tidak diperlukan pemrograman ulang untuk membuat layanan baru yang termasuk jenis layanan tersebut.

## 8.4. Dashboard Scanner Panitia

Petugas login dan membuka **QR Scanner**.

Sistem menampilkan daftar layanan aktif sesuai penugasannya.

Alur petugas:

1. Pilih layanan, misalnya Conference Kit.
2. Buka kamera scanner.
3. Pindai QR peserta.
4. Sistem memeriksa hak peserta.
5. Hasil menampilkan foto dan identitas peserta.
6. Petugas menyerahkan barang atau menyelesaikan pelayanan.
7. Petugas mengonfirmasi penyerahan.
8. Sistem menyimpan riwayat layanan.

Setelah satu pelayanan selesai, scanner tetap berada pada mode yang sama untuk melayani peserta berikutnya.

## 8.5. Hasil validasi

Hasil yang ditampilkan:

**Valid** — peserta memenuhi syarat dan layanan masih tersedia.

**Already Used** — layanan telah digunakan.

**Not Eligible** — peserta tidak memiliki hak terhadap layanan tersebut.

**Invalid QR** — QR tidak dikenal atau tidak aktif.

**Service Closed** — layanan belum atau tidak lagi berlaku.

## 8.6. Pencegahan penggunaan ganda

Satu layanan yang dibatasi satu kali tidak boleh diberikan dua kali hanya karena QR dipindai pada perangkat berbeda.

Sistem harus memeriksa transaksi secara terpusat dan mencatat petugas serta waktu pelayanan.

**Menggunakan QR untuk makan tidak menghabiskan hak absensi atau conference kit.** Setiap jenis layanan memiliki riwayat sendiri.

## 8.7. Mode pengembangan

Versi pertama memakai scanner melalui browser HP panitia.

Tidak dibangun aplikasi Android native secara terpisah pada tahap awal.

Jika internet bermasalah di lokasi, panitia menggunakan prosedur manual sementara yang kemudian direkonsiliasi. Sistem tidak boleh menganggap scan yang belum diterima server sebagai transaksi yang sudah terkonfirmasi.

---

# BAB IX. PARALLEL SESSION DAN ZOOM MANAGEMENT

## 9.1. Tujuan

Memudahkan Admin mengelola penempatan paper, moderator, ruangan, dan presentasi online.

## 9.2. Pengelolaan sesi

Admin dapat membuat sesi dengan:

- Nama sesi.
- Track.
- Tanggal dan waktu.
- Mode Online atau Offline.
- Ruangan.
- Moderator/Session Chair.
- Daftar paper/presenter.
- Link Zoom apabila online.

Satu sesi dapat memuat beberapa paper.

Penempatan paper dapat dilakukan melalui pemilihan dari daftar paper Accepted.

## 9.3. Dashboard presenter

Presenter dapat melihat:

- Jadwal presentasi.
- Nama dan lokasi sesi.
- Urutan presentasi jika tersedia.
- Moderator.
- Link Zoom jika online.
- Status presentasinya.

## 9.4. Dashboard moderator

Moderator hanya melihat sesi yang ditugaskan dan dapat mengonfirmasi status presentasi paper.

Tidak diperlukan mesin otomatis pengoptimal jadwal atau integrasi Zoom API pada versi pertama.

Link Zoom dikelola secara manual oleh Admin.

**Klik Join Zoom tidak dianggap otomatis sebagai bukti kehadiran.**

---

# BAB X. PEMBAYARAN DAN VERIFIKASI

## 10.1. Pendekatan

Sistem pembayaran memakai **transfer bank manual**.

Tidak menggunakan payment gateway pada versi pertama.

## 10.2. Alur pembayaran

**Invoice → Transfer Bank → Upload Bukti → Verifikasi → Status Updated.**

Pengguna dapat membuka detail invoice dan melihat status pembayaran dari dashboard.

## 10.3. Ruang lingkup Finance

Satu mesin pembayaran digunakan untuk:

- Presenter reguler.
- Non-Presenter Participant.
- Paket Internasional pribadi.
- Co-Host institusi.

Invoice peserta dan invoice institusi tetap dibedakan.

## 10.4. Pemeriksaan pembayaran

Petugas berwenang memeriksa bukti dan mencocokkannya dengan mutasi rekening resmi sebelum memberi status Verified.

Status minimum:

- Unpaid.
- Pending Verification.
- Verified.
- Correction Required.

Verifikasi pembayaran tidak boleh secara otomatis membuat paper Accepted, mengonfirmasi absensi, atau menerbitkan sertifikat.

---

# BAB XI. DOKUMEN, SERTIFIKAT DAN NOTIFIKASI

## 11.1. Document Center

Dokumen dapat dikelola melalui tautan PDF atau file yang disediakan Admin.

Prioritas awal:

- Letter of Acceptance.
- Full Paper Guidelines.
- Presentation Guidelines.
- Program Information.
- Certificate Link.

Untuk mempercepat pembangunan, pembuatan LoA atau sertifikat secara otomatis bukan syarat awal. Admin dapat menautkan PDF yang sudah disiapkan.

## 11.2. Sertifikat

Untuk International Conference, sistem mencatat paper yang telah dikonfirmasi Presented.

Sesuai keputusan akademik sebelumnya, sertifikat terkait paper dapat disediakan secara individual untuk para penulis setelah panitia menerbitkannya.

Dashboard submitting author menampilkan tautan dokumen yang sudah tersedia.

Sertifikat program lain dapat diunggah atau ditautkan Admin sesuai kebutuhan, tanpa membangun mekanisme kelayakan yang rumit.

## 11.3. Email Notification

Email dikirim untuk peristiwa penting:

- Aktivasi/verifikasi akun.
- Konfirmasi Abstract Submitted.
- Pengumuman keputusan akademik.
- Ketersediaan LoA.
- Konfirmasi upload Full Paper.
- Verifikasi pembayaran.
- Pengumuman atau perubahan jadwal.
- Dokumen/sertifikat tersedia.

Informasi lain cukup ditampilkan pada dashboard.

Admin dapat mengaktifkan atau menonaktifkan jenis notifikasi yang tersedia.

---

# BAB XII. WEBSITE PUBLIK PROFESIONAL

## 12.1. Konsep visual

Website publik harus menampilkan identitas konferensi internasional secara profesional, akademik, modern, elegan, responsif, dan konsisten.

Desain publik dibuat khusus untuk ICHES, tidak harus mengikuti tampilan dashboard Admin.

## 12.2. Struktur halaman publik

| Halaman | Isi utama |
|---|---|
| Home | Hero, tema, tanggal, countdown, program, CTA |
| About | Profil konferensi dan penyelenggara |
| Call for Papers | Informasi submission dan delapan track |
| Programs | Lima rangkaian kegiatan |
| Important Dates | Jadwal akademik dan kegiatan |
| Registration & Fees | Informasi keikutsertaan dan biaya |
| Committees & Speakers | Panitia dan narasumber yang telah ditetapkan |
| Schedule | Agenda dan sesi yang sudah dipublikasikan |
| Contact | Kontak resmi penyelenggara |

Login dan Register menjadi bagian dari navigasi utama.

## 12.3. Admin Website Management

Admin dapat memperbarui:

- Konten halaman.
- Judul dan deskripsi.
- Banner/hero.
- Gambar.
- Informasi narasumber.
- Jadwal.
- Important Dates.
- Kontak.
- Pengumuman.
- Publikasi atau penyembunyian konten.

Untuk versi pertama, CMS dibuat berbasis form sederhana.

**Tidak perlu membangun visual drag-and-drop page builder.**

Countdown mengambil tanggal/jam acara yang tersimpan pada pengaturan Admin, bukan angka yang diketik permanen di frontend.

---

# BAB XIII. BACKOFFICE ADMIN

## 13.1. Struktur navigasi

**Dashboard**
- Overview.
- Statistik peserta dan paper.
- Ringkasan program.
- Ringkasan pembayaran.
- Ringkasan kehadiran.

**Conference**
- Conference Settings.
- Tracks.
- Submissions.
- Review Management.
- Academic Decisions.
- Full Papers.
- Parallel Sessions.

**Programs**
- International MoU.
- Research & PKM Upgrading.
- International Community Service.
- Destination Study.
- International Package.

**Participants & Institutions**
- All Participants.
- Co-Host Institutions.
- Program Enrollments.
- Committee & Assignments.

**Event Operations**
- ID Cards.
- QR Services.
- QR Scanner Assignments.
- Attendance.
- Kit & Meal Distribution.

**Finance**
- Invoices.
- Payment Verification.
- Payment Reports.

**Documents**
- LoA & Documents.
- Certificates.

**Website**
- Pages & Content.
- Media.
- Announcements.
- Event Settings.

**Reports**
- Participants.
- Submissions.
- Academic Decisions.
- Programs.
- Attendance.
- Services.
- Payments.

**Settings**
- Users & Access.
- General Settings.
- Email Settings.

Menu dapat digabung secara visual agar sidebar tetap ringkas.

## 13.2. Prinsip Admin

Admin harus dapat mengubah data kegiatan yang bersifat rutin tanpa meminta developer.

Namun, perubahan yang sensitif—terutama hasil akademik, keuangan, dan bukti layanan—tetap tercatat dan hanya boleh dilakukan pengguna berwenang.

## 13.3. Laporan

Tahap awal cukup menyediakan tabel yang bisa dicari, difilter, dan diekspor ke CSV/Excel.

Tidak perlu membangun dashboard business intelligence yang kompleks.

---

# BAB XIV. ARSITEKTUR TEKNOLOGI

## 14.1. Teknologi utama

| Komponen | Pilihan |
|---|---|
| Backend | Laravel |
| Starter Kit | Laravel Livewire |
| UI | Livewire, Blade, Tailwind CSS |
| UI Components | Flux UI atau komponen kompatibel |
| Database | MySQL |
| Authentication | Laravel Starter Kit |
| Foto Crop | Cropper.js |
| QR | Generator QR server-side |
| Scanner | Web camera scanner |
| File Storage | Laravel Storage |
| Notifikasi | Laravel Mail/Notifications |
| Version Control | Git dan repository baru |

## 14.2. Pendekatan pengembangan

Sistem dibuat sebagai **satu aplikasi Laravel monolith**.

Tidak perlu:

- Frontend React/Vue terpisah.
- Microservices.
- Aplikasi Android khusus.
- Payment gateway.
- Integrasi Zoom API.
- Workflow engine generik.
- Permission builder kompleks.
- Visual CMS drag-and-drop.
- Mesin penjadwalan otomatis.
- Integrasi langsung ke OJS pada fase awal.

## 14.3. Prinsip data

Struktur inti cukup memisahkan:

- User dan profil.
- Program serta keikutsertaan.
- Paper dan review.
- Keputusan akademik.
- Jadwal/sesi.
- Paket dan pembayaran.
- QR dan layanan.
- Dokumen.
- Kehadiran.

Struktur tersebut dibuat secara proporsional; tidak menyalin seluruh skema database repository lama.

---

# BAB XV. PRIORITAS PENGEMBANGAN

## 15.1. Fase 1 — Foundation

**Target:** aplikasi dasar berjalan.

Hasil yang dibutuhkan:

- Laravel Livewire terpasang.
- Login dan registrasi.
- Dashboard pengguna.
- Dashboard Admin.
- Struktur program.
- Website publik awal.

## 15.2. Fase 2 — Academic Submission

**Target:** seluruh alur inti pengiriman abstrak berfungsi.

Hasil yang dibutuhkan:

- Submit Abstract.
- Upload PDF.
- My Submissions.
- Reviewer Workspace.
- Review Submission.
- Academic Decision.
- Tracking.

Ini merupakan prioritas bisnis tertinggi karena Abstract Submission dijadwalkan mulai 1 Desember 2026.

## 15.3. Fase 3 — Program & Finance

**Target:** peserta dapat mengikuti program yang benar.

Hasil yang dibutuhkan:

- Program Enrollments.
- Paket Internasional.
- Co-Host.
- Invoice manual.
- Upload bukti pembayaran.
- Verifikasi pembayaran.

## 15.4. Fase 4 — ID Card & QR

**Target:** layanan peserta dapat dikelola melalui identitas tunggal.

Hasil yang dibutuhkan:

- Upload dan crop foto.
- ID Card Participant/Committee.
- Download PNG.
- QR pribadi.
- QR Services Management.
- Scanner Panitia.
- Rekap layanan.

## 15.5. Fase 5 — Event Operations

**Target:** kegiatan 5–6 Januari dapat dikelola.

Hasil yang dibutuhkan:

- Parallel Session.
- Zoom Link Management.
- Moderator Workspace.
- QR attendance tiap program.
- Kelompok pengabdian.
- Manifest kegiatan.
- Document Center.
- Laporan panitia.

## 15.6. Fase 6 — Penutupan Kegiatan

**Target:** dokumentasi dan penyelesaian layanan setelah acara.

Hasil yang dibutuhkan:

- Konfirmasi paper Presented.
- Dokumen/sertifikat.
- Rekap peserta dan kehadiran.
- Rekap pembayaran.
- Rekap pengambilan layanan.
- Export laporan.
- Pengarsipan dokumen hasil kegiatan.

---

# BAB XVI. TARGET WAKTU

| Periode | Target |
|---|---|
| 20–22 November 2026 | Fondasi dan demonstrasi alur utama untuk pimpinan |
| 23–30 November 2026 | Penyelesaian serta pengujian sistem submission dan reviewer |
| 1–20 Desember 2026 | Operasional Abstract Submission dan review bergulir |
| 22 Desember 2026 | Target pengumuman keputusan akademik |
| 23–28 Desember 2026 | Full Paper dan tahapan pembayaran sesuai ketentuan |
| Sebelum 5 Januari 2027 | Finalisasi QR, ID Card, sesi, Zoom, dan operasional program |
| 5–6 Januari 2027 | Pelaksanaan acara |
| Setelah kegiatan | Sertifikat, laporan dan tindak lanjut hasil akademik |

Jadwal pengembangan merupakan target kerja, bukan jaminan otomatis. Peluncuran fungsi publik harus didahului pengujian alur, akses pengguna, upload file, pembayaran, dan integritas data.

Untuk laporan pimpinan pada 22 November, demonstrasi sebaiknya menunjukkan satu perjalanan pengguna yang bekerja secara nyata, disertai gambaran sistem lengkap yang akan diselesaikan bertahap.

---

# BAB XVII. KRITERIA KEBERHASILAN APLIKASI

Aplikasi dinilai berhasil apabila skenario inti berikut dapat dijalankan:

| No. | Skenario | Hasil yang diharapkan |
|---|---|---|
| 1 | Pengguna membuat akun | Satu akun aktif dan dapat login |
| 2 | Pengguna memilih program lain | Tidak perlu membuat akun baru |
| 3 | Presenter mengirim abstrak | Paper dan PDF tercatat serta mendapat kode |
| 4 | Reviewer mengklaim paper | Tugas review tercatat pada akunnya |
| 5 | Reviewer mengirim rekomendasi | Hasil tersedia untuk pihak akademik |
| 6 | Keputusan diterbitkan | Author melihat hasil dan riwayat progres |
| 7 | Paper Accepted | LoA dan Full Paper tersedia |
| 8 | Peserta membeli Paket Internasional | Satu invoice untuk empat program |
| 9 | Pembayaran diverifikasi | Hak program yang bersangkutan aktif |
| 10 | Anggota Co-Host mengirim paper | Jatah institusi tercatat tanpa tagihan ganda |
| 11 | User mengunggah dan crop foto | Foto tampil sesuai pada ID Card |
| 12 | User mengunduh ID Card/QR | PNG berhasil diunduh |
| 13 | Admin membuat QR Service baru | Layanan muncul di scanner petugas yang ditugaskan |
| 14 | QR digunakan mengambil kit | Sistem mencatat penyerahan satu kali |
| 15 | QR yang sama digunakan untuk absensi | Kehadiran tercatat tanpa mengubah riwayat kit |
| 16 | Admin membuat parallel session | Presenter melihat jadwal yang ditugaskan |
| 17 | Presenter online membuka jadwal | Link Zoom yang benar tersedia |
| 18 | Admin mengekspor laporan | Data sesuai filter dapat diunduh |

Kriteria tersebut menekankan alur kerja nyata, bukan jumlah halaman atau banyaknya fitur yang terlihat selesai.

---

# BAB XVIII. PEMBATASAN PROYEK AGAR CEPAT SELESAI

Untuk menjaga kecepatan pengembangan, hal berikut **tidak menjadi pekerjaan wajib pada tahap pertama**:

- Perincian kuota peserta lapangan.
- Pengaturan komposisi fasilitas fisik yang kompleks.
- Mesin penentuan sertifikat otomatis untuk seluruh jenis program.
- Pembuat rubrik penilaian reviewer yang kompleks.
- Seluruh variasi tarif di luar kategori yang sudah ditetapkan.
- Sistem inventory/gudang lengkap.
- Aplikasi Android native.
- Scanner offline dengan sinkronisasi kompleks.
- Payment gateway.
- Zoom API integration.
- OJS integration.
- Sistem penerbitan jurnal otomatis.
- Sistem anti-benturan jadwal otomatis.
- Multi-event platform untuk konferensi lain.
- Website builder drag-and-drop.
- Fitur akademik dan administratif yang tidak digunakan dalam ICHES 2027.

Jika diperlukan, data atau layanan sederhana dapat ditangani melalui pengaturan Admin dan operasi manual yang tetap tercatat.

**Penyederhanaan tidak berlaku pada perlindungan data pengguna, hak akses, keamanan upload, kebenaran transaksi pembayaran, dan pencegahan scan ganda.**

---

# BAB XIX. MODEL PENGEMBANGAN FULL AI

## 19.1. Pembagian kerja

**Pemilik proyek:** menetapkan tujuan, memeriksa hasil, dan menyetujui perubahan penting.

**ChatGPT:** menjaga blueprint sebagai sumber acuan, menyiapkan instruksi pengembangan yang ringkas, serta mengaudit kesesuaian hasil terhadap alur bisnis.

**AI Coding Agent:** mengimplementasikan fitur, membuat migration, membangun antarmuka, menjalankan pengujian yang diizinkan, dan memperbaiki kegagalan sesuai tugas.

## 19.2. Metode kerja

Pekerjaan dibagi menjadi paket fungsional yang pendek.

Setiap paket harus mempunyai:

- Tujuan.
- Fitur yang dibuat.
- Batas pekerjaan.
- Hasil yang harus dapat digunakan.
- Pengujian minimum.

AI tidak perlu membaca seluruh dokumen dari repository lama pada setiap pekerjaan. Master Blueprint ini menjadi rujukan produk utama, sementara dokumen lama hanya dibuka jika ada kebutuhan bisnis yang spesifik.

## 19.3. Repository

Direkomendasikan membuat satu repository Git baru untuk aplikasi Laravel Livewire.

Repository lama dipertahankan dan tidak diubah.

Pembuatan repository dan implementasi baru dilakukan setelah rancangan disetujui serta pekerjaan pengembangan diotorisasi.

---

# BAB XX. KESIMPULAN BLUEPRINT

ICHES 2027 Conference Management System dikembangkan sebagai **satu portal terintegrasi untuk peserta, pemakalah, reviewer, panitia dan administrator**.

Aplikasi menggabungkan lima program ICHES dalam satu identitas pengguna, dengan International Conference sebagai pusat proses akademik.

Fitur pembeda utamanya adalah:

1. **One Account, Multiple Programs** — pengguna dapat mengikuti berbagai kegiatan melalui satu akun.
2. **Trackable Academic Submission** — pemakalah mengetahui posisi dan perkembangan paper.
3. **Simple Reviewer Workspace** — reviewer bekerja melalui daftar paper dan form penilaian yang jelas.
4. **Integrated Participant Identity** — foto, ID Card dan QR pribadi terhubung.
5. **Configurable QR Services** — Admin membuat layanan scan tanpa modifikasi kode untuk setiap kegiatan.
6. **Hybrid Event Management** — sesi offline dan Zoom dikelola melalui dashboard.
7. **Centralized Administration** — seluruh data kegiatan, peserta dan laporan tersedia pada satu sistem.

**Arah final yang diusulkan:**

Membangun aplikasi baru berbasis Laravel Livewire dengan tampilan profesional, alur yang mudah dipahami, fitur yang dibatasi pada kebutuhan penyelenggaraan ICHES 2027, dan pengembangan bertahap berdasarkan prioritas operasional.

**Status dokumen: MENUNGGU EVALUASI PEMILIK PROYEK.**

Belum menjadi instruksi untuk membuat repository, menjalankan migration, atau memulai coding.

---

### Dokumen acuan utama

- [Analisis TOR dan Rebaseline ICHES 2027](https://github.com/akhmadafnan/iches-unisya/blob/main/docs/10-ANALISIS-TOR-DAN-REBASELINE-2027.md)
- [Keputusan Paket Program Internasional](https://github.com/akhmadafnan/iches-unisya/blob/main/docs/15-PAKET-INTERNASIONAL-DASHBOARD-PESERTA.md)
- [Co-Host Lima Perwakilan dan Lima Paper](https://github.com/akhmadafnan/iches-unisya/blob/main/docs/16-COHOST-LIMA-PAPER-PERWAKILAN.md)
- [Admin-Centered CRUD](https://github.com/akhmadafnan/iches-unisya/blob/main/docs/17-ADMIN-CRUD-DAN-PENGATURAN-ACARA.md)
- [Alur Review, Keputusan dan Parallel Session](https://github.com/akhmadafnan/iches-unisya/blob/main/docs/18B-03-CRUD-REVIEW-DECISION-FULLPAPER-SESSION-DAN-UAT.md)
- [Finance dan Verifikasi Pembayaran](https://github.com/akhmadafnan/iches-unisya/blob/main/docs/18B-06-CRUD-FINANCE-INVOICE-PEMBAYARAN-VERIFIKASI.md)
