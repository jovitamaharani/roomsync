
// (() => {
//     "use strict";
//     window.sikaraAdminLoaded = true;

//     // ========================================
//     // 1. IDENTITAS ADMIN
//     // Autentikasi diperiksa oleh admin.php
//     // ========================================

//     const adminName = document.getElementById("adminName");
//     const todayDate = document.getElementById("todayDate");

//     if (adminName) {
//         adminName.textContent = "Administrator JTI";
//     }

//     if (todayDate) {
//         todayDate.textContent = new Intl.DateTimeFormat("id-ID", {
//             weekday: "long",
//             day: "numeric",
//             month: "long",
//             year: "numeric"
//         }).format(new Date());
//     }

//     // ========================================
//     // 2. DATA AWAL
//     // Data masih berupa simulasi
//     // ========================================

//     const rooms = [
//         ["RT 01", "Ruang Teori 01", "Gedung TI", 1, 35, "Ruang Teori", "KOSONG"],
//         ["RT 02", "Ruang Teori 02", "Gedung TI", 2, 35, "Ruang Teori", "TERPAKAI"],
//         ["RT 03", "Ruang Teori 03", "Gedung TI", 2, 35, "Ruang Teori", "TERPAKAI"],
//         ["Lab RPL 1", "Lab Rekayasa Perangkat Lunak 1", "Gedung TI", 3, 30, "Laboratorium", "KOSONG"],
//         ["Lab RPL 2", "Lab Rekayasa Perangkat Lunak 2", "Gedung TI", 3, 30, "Laboratorium", "TERPAKAI"],
//         ["Lab Jaringan", "Lab Jaringan Komputer & IoT", "Gedung TI", 3, 28, "Laboratorium", "KOSONG"],
//         ["RT 04", "Ruang Teori 04", "Gedung TI", 1, 40, "Ruang Teori", "KOSONG"],
//         ["RT 05", "Ruang Teori 05", "Gedung TI", 2, 40, "Ruang Teori", "KOSONG"]
//     ];

//     const schedules = [
//         ["Senin", "08:00–11:30", "Desain dan Pemrograman Web", "Dimas Wahyu Wibowo", "2F", "RT 02"],
//         ["Senin", "09:00–12:00", "Manajemen Proyek", "Titis Octary Satrio", "2F", "RT 03"],
//         ["Senin", "08:00–11:30", "Basis Data Lanjut", "Mustika Mentari", "2F", "Lab RPL 2"],
//         ["Senin", "13:00–15:30", "Sistem Operasi", "Farid Angga", "2E", "RT 02"],
//         ["Senin", "13:00–16:30", "Pengembangan Aplikasi Mobile", "Rosa A.S.", "3F", "Lab RPL 1"],
//         ["Selasa", "07:00–09:30", "Matematika Komputasi", "Subari", "1E", "RT 01"],
//         ["Selasa", "13:00–16:00", "Jaringan Komputer Lanjut", "Sofyan", "2D", "Lab Jaringan"]
//     ];

//     const lecturers = [
//         ["198701012010011001", "Dimas Wahyu Wibowo, S.T., M.T.", "dimas.wibowo@polinema.ac.id", "Teknologi Informasi"],
//         ["198605122009121002", "Titis Octary Satrio, S.ST., M.MT.", "titis.octary@polinema.ac.id", "Teknologi Informasi"],
//         ["199003182015041003", "Mustika Mentari, S.Kom., M.Kom.", "mustika.mentari@polinema.ac.id", "Teknologi Informasi"],
//         ["198811202012121004", "Farid Angga, S.Kom., M.T.", "farid.angga@polinema.ac.id", "Teknologi Informasi"],
//         ["199104092016042005", "Rosa A.S., M.T.", "rosa.as@polinema.ac.id", "Teknologi Informasi"]
//     ];

//     const classes = [
//         ["TI-1A", "Teknologi Informasi", "2026", "1", "-"],
//         ["TI-1B", "Teknologi Informasi", "2026", "1", "-"],
//         ["TI-2E", "Teknologi Informasi", "2025", "3", "-"],
//         ["TI-2F", "Teknologi Informasi", "2025", "3", "Dafa Naufal Rabbani"],
//         ["TI-3F", "Teknologi Informasi", "2024", "5", "-"]
//     ];

//     const courses = [
//         ["TIF201", "Desain dan Pemrograman Web", 3, 4, "Teknologi Informasi"],
//         ["TIF202", "Manajemen Proyek", 2, 4, "Teknologi Informasi"],
//         ["TIF203", "Basis Data Lanjut", 3, 4, "Teknologi Informasi"],
//         ["TIF204", "Sistem Operasi", 3, 3, "Teknologi Informasi"],
//         ["TIF305", "Pengembangan Aplikasi Mobile", 3, 5, "Teknologi Informasi"],
//         ["TIF206", "Jaringan Komputer Lanjut", 3, 4, "Teknologi Informasi"]
//     ];

//     const academicYears = [
//         ["2026/2027", "Ganjil", "2026-08-01", "2027-01-31", "Aktif"],
//         ["2025/2026", "Genap", "2026-02-01", "2026-07-31", "Selesai"]
//     ];

//     let audit = [];

//     try {
//         audit = JSON.parse(
//             localStorage.getItem("sikara_audit_logs") || "[]"
//         );

//         if (!Array.isArray(audit)) {
//             audit = [];
//         }
//     } catch (error) {
//         console.error("Gagal membaca data aktivitas:", error);
//     }

//     // ========================================
//     // 3. KONFIGURASI TABEL
//     // ========================================

//     const pageDefs = {
//         lecturers: {
//             title: "Daftar Dosen",
//             id: "lecturersTable",
//             headers: ["NIDN", "Nama Dosen", "Email", "Program Studi"],
//             rows: lecturers
//         },

//         classes: {
//             title: "Daftar Kelas",
//             id: "classesTable",
//             headers: ["Kode Kelas", "Program Studi", "Angkatan", "Semester", "Wali Kelas"],
//             rows: classes
//         },

//         schedules: {
//             title: "Jadwal Mata Kuliah",
//             id: "schedulesTable",
//             headers: ["Hari", "Jam", "Mata Kuliah", "Dosen", "Kelas", "Ruang"],
//             rows: schedules
//         },

//         bookings: {
//             title: "Riwayat Peminjaman",
//             id: "bookingsTable",
//             headers: ["Waktu", "Ruang", "Peminjam", "Detail Peminjaman", "Status"],
//             rows: audit
//                 .filter(item => item.type === "BOOKING")
//                 .map(item => [
//                     item.timestamp,
//                     item.room,
//                     item.actor,
//                     item.details,
//                     item.finalStatus
//                 ])
//         },

//         activity: {
//             title: "Jurnal Activity Control",
//             id: "activityTable",
//             headers: ["Waktu", "Ruang", "Jenis Aktivitas", "Pelapor", "Detail", "Status Akhir"],
//             rows: audit
//                 .filter(item => item.type === "REPORT" || item.type === "BUBAR")
//                 .map(item => [
//                     item.timestamp,
//                     item.room,
//                     item.typeLabel,
//                     item.actor,
//                     item.details,
//                     item.finalStatus
//                 ])
//         },

//         years: {
//             title: "Academic Year",
//             id: "yearsTable",
//             headers: ["Tahun Akademik", "Semester", "Mulai", "Selesai", "Status"],
//             rows: academicYears
//         },

//         rooms: {
//             title: "Daftar Ruang Kelas",
//             id: "roomsTable",
//             headers: ["Kode", "Nama Ruang", "Gedung", "Lantai", "Kapasitas", "Tipe", "Status"],
//             rows: rooms
//         },

//         courses: {
//             title: "Daftar Mata Kuliah",
//             id: "coursesTable",
//             headers: ["Kode", "Nama Mata Kuliah", "SKS", "Semester", "Program Studi"],
//             rows: courses
//         }
//     };

//     const titles = {
//         overview: "Ringkasan",
//         ...Object.fromEntries(
//             Object.entries(pageDefs).map(([key, page]) => [
//                 key,
//                 page.title
//             ])
//         )
//     };

//     const tableInstances = {};

//     // ========================================
//     // 4. FUNGSI BANTU
//     // ========================================

//     function escapeHtml(value) {
//         return String(value ?? "").replace(/[&<>"']/g, char => ({
//             "&": "&amp;",
//             "<": "&lt;",
//             ">": "&gt;",
//             '"': "&quot;",
//             "'": "&#39;"
//         })[char]);
//     }

//     function showToast(message, isError = false) {
//         const area = document.getElementById("toastArea");

//         if (!area) {
//             console[isError ? "error" : "log"](message);
//             return;
//         }

//         const node = document.createElement("div");

//         node.className =
//             `toast align-items-center text-bg-${isError ? "danger" : "success"} border-0`;

//         node.setAttribute("role", "status");

//         const body = document.createElement("div");
//         body.className = "d-flex";

//         const text = document.createElement("div");
//         text.className = "toast-body";
//         text.textContent = message;

//         const closeButton = document.createElement("button");
//         closeButton.type = "button";
//         closeButton.className = "btn-close btn-close-white me-2 m-auto";
//         closeButton.setAttribute("data-bs-dismiss", "toast");
//         closeButton.setAttribute("aria-label", "Tutup");

//         body.append(text, closeButton);
//         node.appendChild(body);
//         area.appendChild(node);

//         if (typeof bootstrap !== "undefined" && bootstrap.Toast) {
//             const toast = new bootstrap.Toast(node, { delay: 3500 });
//             toast.show();

//             node.addEventListener("hidden.bs.toast", () => node.remove());
//         } else {
//             node.classList.add("show");
//             setTimeout(() => node.remove(), 3500);
//         }
//     }

//     // ========================================
//     // 5. RENDER TABEL
//     // ========================================

//     function drawTable(key, rows = pageDefs[key]?.rows || []) {
//         const definition = pageDefs[key];

//         if (!definition) return;

//         const table = document.getElementById(definition.id);

//         if (!table) return;

//         if (tableInstances[key]) {
//             tableInstances[key].destroy();
//             delete tableInstances[key];
//         }

//         const tbody = table.querySelector("tbody");

//         if (!tbody) return;

//         tbody.innerHTML = rows.map(row => {
//             const cells = row.map((cell, index) => {
//                 const value = escapeHtml(cell);

//                 if (
//                     (key === "rooms" && index === 6) ||
//                     (key === "years" && index === 4)
//                 ) {
//                     const status = String(cell ?? "").toLowerCase();

//                     let badgeClass = "badge-green";

//                     if (status === "terpakai") {
//                         badgeClass = "badge-red";
//                     } else if (status === "aktif") {
//                         badgeClass = "badge-blue";
//                     }

//                     return `<td><span class="badge-status ${badgeClass}">${value}</span></td>`;
//                 }

//                 return `<td>${value}</td>`;
//             });

//             return `<tr>${cells.join("")}</tr>`;
//         }).join("");

//         if (typeof DataTable === "function") {
//             tableInstances[key] = new DataTable(table, {
//                 pageLength: 10,
//                 order: [],
//                 language: {
//                     search: "Cari:",
//                     lengthMenu: "Tampilkan _MENU_",
//                     info: "Menampilkan _START_–_END_ dari _TOTAL_ data",
//                     infoEmpty: "Belum ada data",
//                     zeroRecords: "Data tidak ditemukan",
//                     emptyTable: "Belum ada data",
//                     paginate: {
//                         first: "Awal",
//                         last: "Akhir",
//                         next: "Berikutnya",
//                         previous: "Sebelumnya"
//                     }
//                 }
//             });
//         }
//     }

//     // ========================================
//     // 6. HALAMAN RINGKASAN
//     // ========================================

//     function renderOverview() {
//         const summaryCards = document.getElementById("summaryCards");

//         if (summaryCards) {
//             const counts = [
//                 ["Ruang kelas", rooms.length, "bi-door-open"],
//                 ["Dosen", lecturers.length, "bi-person-vcard"],
//                 ["Kelas", classes.length, "bi-people"],
//                 ["Jadwal", schedules.length, "bi-calendar3"]
//             ];

//             summaryCards.innerHTML = counts.map(([label, number, icon]) => `
//                 <div class="col-6 col-xl-3">
//                     <div class="summary-card">
//                         <span class="summary-icon">
//                             <i class="bi ${icon}"></i>
//                         </span>
//                         <span>
//                             <span class="summary-label">${label}</span>
//                             <span class="summary-number d-block">${number}</span>
//                         </span>
//                     </div>
//                 </div>
//             `).join("");
//         }

//         const table = document.querySelector("#todayTable tbody");

//         if (!table) return;

//         table.innerHTML = schedules.slice(0, 5).map(schedule => `
//             <tr>
//                 <td>${escapeHtml(schedule[1])}</td>
//                 <td>${escapeHtml(schedule[2])}</td>
//                 <td>${escapeHtml(schedule[4])}</td>
//                 <td>${escapeHtml(schedule[5])}</td>
//                 <td><span class="badge-status badge-blue">Terjadwal</span></td>
//             </tr>
//         `).join("");

//         if (!tableInstances.overview && typeof DataTable === "function") {
//             tableInstances.overview = new DataTable("#todayTable", {
//                 pageLength: 10,
//                 searching: true,
//                 lengthChange: false,
//                 info: true,
//                 paging: true,
//                 ordering: true,
//                 language: {
//                     search: "Cari jadwal:",
//                     info: "Menampilkan _START_–_END_ dari _TOTAL_ jadwal",
//                     infoEmpty: "Belum ada jadwal",
//                     zeroRecords: "Jadwal tidak ditemukan",
//                     emptyTable: "Belum ada jadwal",
//                     paginate: {
//                         next: "Berikutnya",
//                         previous: "Sebelumnya"
//                     }
//                 }
//             });
//         }
//     }

//     // ========================================
//     // 7. NAVIGASI ANTARHALAMAN
//     // ========================================

//     function showPage(key) {
//         if (!titles[key]) return;

//         document.querySelectorAll(".admin-page").forEach(page => {
//             page.classList.toggle("active", page.id === `page-${key}`);
//         });

//         document.querySelectorAll(".side-link").forEach(button => {
//             button.classList.toggle("active", button.dataset.page === key);
//         });

//         const pageTitle = document.getElementById("pageTitle");

//         if (pageTitle) {
//             pageTitle.textContent = titles[key];
//         }

//         const sidebar = document.getElementById("adminSidebar");
//         const scrim = document.getElementById("sidebarScrim");

//         sidebar?.classList.remove("open");
//         scrim?.classList.remove("show");

//         if (key === "overview") {
//             renderOverview();
//         } else {
//             drawTable(key);
//         }
//     }

//     // Render overview jika komponen summary tersedia di halaman
//     if (document.getElementById("summaryCards") || document.getElementById("todayTable")) {
//         renderOverview();
//     }

//     // Render tabel modul yang ada di halaman aktif
//     Object.keys(pageDefs).forEach(key => {
//         if (document.getElementById(pageDefs[key].id)) {
//             drawTable(key);
//         }
//     });

//     document.querySelectorAll(".side-link[data-page]").forEach(button => {
//         button.addEventListener("click", () => {
//             showPage(button.dataset.page);
//         });
//     });

//     document.querySelectorAll("[data-open-page]").forEach(button => {
//         button.addEventListener("click", () => {
//             showPage(button.dataset.openPage);
//         });
//     });

//     // ========================================
//     // 8. SIDEBAR RESPONSIF
//     // ========================================

//     const sidebar = document.getElementById("adminSidebar");
//     const sidebarScrim = document.getElementById("sidebarScrim");
//     const sidebarToggle = document.getElementById("sidebarToggle");

//     sidebarToggle?.addEventListener("click", () => {
//         sidebar?.classList.toggle("open");
//         sidebarScrim?.classList.toggle("show");
//     });

//     sidebarScrim?.addEventListener("click", () => {
//         sidebar?.classList.remove("open");
//         sidebarScrim.classList.remove("show");
//     });

//     // ========================================
//     // 9. LOGOUT
//     // Mengakhiri sesi melalui PHP
//     // ========================================

//     const logoutBtnEl = document.getElementById("logoutBtn");
//     if (logoutBtnEl) {
//         logoutBtnEl.addEventListener("click", (e) => {
//             const href = logoutBtnEl.getAttribute("href");
//             if (!href || href === "#") {
//                 e.preventDefault();
//                 window.location.href = "../logout.php";
//             }
//         });
//     }

//     // ========================================
//     // 10. IMPOR EXCEL
//     // ========================================

//     const importMap = {
//         lecturers: "importLecturers",
//         classes: "importClasses",
//         schedules: "importSchedules"
//     };

//     document.querySelectorAll("[data-import]").forEach(button => {
//         button.addEventListener("click", () => {
//             const inputId = importMap[button.dataset.import];
//             const input = inputId ? document.getElementById(inputId) : null;

//             if (input) input.click();
//         });
//     });

//     Object.entries(importMap).forEach(([key, inputId]) => {
//         const input = document.getElementById(inputId);

//         if (!input) return;

//         input.addEventListener("change", async event => {
//             const file = event.target.files?.[0];

//             if (!file) return;

//             try {
//                 if (typeof XLSX === "undefined") {
//                     throw new Error("Library Excel belum berhasil dimuat.");
//                 }

//                 const data = await file.arrayBuffer();
//                 const workbook = XLSX.read(data, { type: "array" });

//                 const firstSheet = workbook.Sheets[workbook.SheetNames[0]];

//                 const matrix = XLSX.utils.sheet_to_json(firstSheet, {
//                     header: 1,
//                     defval: ""
//                 });

//                 // Baris pertama dianggap sebagai header.
//                 const imported = matrix.slice(1).filter(row =>
//                     row.some(value => String(value).trim() !== "")
//                 );

//                 if (!imported.length) {
//                     throw new Error("Tidak ada data setelah baris header.");
//                 }

//                 pageDefs[key].rows = imported;

//                 drawTable(key);

//                 showToast(
//                     `${imported.length} baris berhasil diimpor ke ${pageDefs[key].title}.`
//                 );
//             } catch (error) {
//                 showToast(`Gagal membaca file: ${error.message}`, true);
//             } finally {
//                 event.target.value = "";
//             }
//         });
//     });

//     // ========================================
//     // 11. TAMBAH DATA
//     // ========================================

//     let editingKey = null;

//     document.querySelectorAll("[data-add]").forEach(button => {
//         button.addEventListener("click", () => {
//             editingKey = button.dataset.add;

//             const definition = pageDefs[editingKey];

//             if (!definition) return;

//             const modalTitle = document.getElementById("dataModalTitle");
//             const modalBody = document.getElementById("dataModalBody");
//             const modalElement = document.getElementById("dataModal");

//             if (!modalTitle || !modalBody || !modalElement) {
//                 showToast("Komponen modal tidak ditemukan.", true);
//                 return;
//             }

//             modalTitle.textContent = `Tambah · ${definition.title}`;

//             modalBody.innerHTML = definition.headers.map((header, index) => `
//                 <div class="mb-3">
//                     <label class="form-label" for="field${index}">
//                         ${escapeHtml(header)}
//                     </label>
//                     <input
//                         class="form-control"
//                         id="field${index}"
//                         name="field${index}"
//                         required>
//                 </div>
//             `).join("");

//             if (typeof bootstrap !== "undefined" && bootstrap.Modal) {
//                 bootstrap.Modal.getOrCreateInstance(modalElement).show();
//             } else {
//                 showToast("Bootstrap belum berhasil dimuat.", true);
//             }
//         });
//     });

//     const dataForm = document.getElementById("dataForm");

//     dataForm?.addEventListener("submit", event => {
//         event.preventDefault();

//         if (!editingKey || !pageDefs[editingKey]) return;

//         const row = [...new FormData(event.currentTarget).values()];

//         pageDefs[editingKey].rows.push(row);

//         drawTable(editingKey);

//         const modalElement = document.getElementById("dataModal");
//         const modalInstance = modalElement &&
//             bootstrap.Modal.getInstance(modalElement);

//         modalInstance?.hide();

//         event.currentTarget.reset();

//         showToast("Data berhasil ditambahkan.");

//         editingKey = null;
//     });

// })();
