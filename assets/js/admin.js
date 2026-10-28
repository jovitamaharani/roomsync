
(() => {
    "use strict";
    window.sikaraAdminLoaded = true;

    // ========================================
    // 1. IDENTITAS ADMIN
    // Autentikasi diperiksa oleh admin.php
    // ========================================

    const adminName = document.getElementById("adminName");
    const todayDate = document.getElementById("todayDate");

    if (adminName) {
        adminName.textContent = "Administrator JTI";
    }

    if (todayDate) {
        todayDate.textContent = new Intl.DateTimeFormat("id-ID", {
            weekday: "long",
            day: "numeric",
            month: "long",
            year: "numeric"
        }).format(new Date());
    }

    // ========================================
    // 2. DATA AWAL
    // Data masih berupa simulasi
    // ========================================

    const rooms = [
        ["RT 01", "Gedung TI", "KOSONG"],
        ["RT 02", "Gedung TI", "TERPAKAI"],
        ["RT 03", "Gedung TI", "TERPAKAI"],
        ["RT 04", "Gedung TI", "KOSONG"],
        ["RT 05", "Gedung TI", "KOSONG"],
        ["Lab RPL 1", "Gedung TI", "KOSONG"],
        ["Lab RPL 2", "Gedung TI", "TERPAKAI"],
        ["Lab Jaringan", "Gedung TI", "KOSONG"]
    ];

    const schedules = [
        ["Senin", "08:00–11:30", "Desain dan Pemrograman Web", "Dimas Wahyu Wibowo, S.T., M.T.", "TI-2F", "RT 02"],
        ["Senin", "09:00–12:00", "Manajemen Proyek", "Titis Octary Satrio, S.ST., M.MT.", "TI-2F", "RT 03"],
        ["Senin", "08:00–11:30", "Basis Data Lanjut", "Mustika Mentari, S.Kom., M.Kom.", "TI-2F", "Lab RPL 2"],
        ["Senin", "13:00–15:30", "Sistem Operasi", "Farid Angga, S.Kom., M.T.", "TI-2E", "RT 02"],
        ["Senin", "13:00–16:30", "Pengembangan Aplikasi Mobile", "Dr. Eng. Rosa Andrie Asmara, S.T., M.T.", "TI-3F", "Lab RPL 1"],
        ["Selasa", "07:00–09:30", "Desain dan Pemrograman Web", "Dimas Wahyu Wibowo, S.T., M.T.", "TI-1A", "RT 01"],
        ["Selasa", "13:00–16:00", "Jaringan Komputer Lanjut", "Farid Angga, S.Kom., M.T.", "TI-1B", "Lab Jaringan"]
    ];

    const lecturers = [
        ["DMW", "Dimas Wahyu Wibowo, S.T., M.T."],
        ["TOS", "Titis Octary Satrio, S.ST., M.MT."],
        ["MMT", "Mustika Mentari, S.Kom., M.Kom."],
        ["FRA", "Farid Angga, S.Kom., M.T."],
        ["RSA", "Dr. Eng. Rosa Andrie Asmara, S.T., M.T."]
    ];

    const classes = [
        ["TI-1A", "2026/2027 Ganjil"],
        ["TI-1B", "2026/2027 Ganjil"],
        ["TI-2E", "2026/2027 Ganjil"],
        ["TI-2F", "2026/2027 Ganjil"],
        ["TI-3F", "2025/2026 Genap"],
        ["SIB-1A", "2026/2027 Ganjil"],
        ["SIB-2A", "2026/2027 Ganjil"]
    ];

    const courses = [
        ["TIF201", "Desain dan Pemrograman Web"],
        ["TIF202", "Manajemen Proyek"],
        ["TIF203", "Basis Data Lanjut"],
        ["TIF204", "Sistem Operasi"],
        ["TIF305", "Pengembangan Aplikasi Mobile"],
        ["TIF206", "Jaringan Komputer Lanjut"]
    ];

    const academicYears = [
        ["2026/2027 Ganjil", "Aktif"],
        ["2025/2026 Genap", "Selesai"],
        ["2025/2026 Ganjil", "Selesai"]
    ];

    const users = [
        ["admin_jti", "Administrator JTI", "admin.jti@polinema.ac.id", "Admin", "Aktif"],
        ["197503151999031002", "Dr. Eng. Rosa Andrie Asmara, S.T., M.T.", "rosa.asmara@polinema.ac.id", "Headmaster", "Aktif"],
        ["2341720001", "Dafa Naufal Rabbani (TI-2A)", "dafa.naufal@student.polinema.ac.id", "Ketua Kelas", "Aktif"],
        ["2341720045", "Ahmad Syarifuddin (TI-2B)", "ahmad.syarif@student.polinema.ac.id", "Ketua Kelas", "Aktif"],
        ["198010102005011001", "M. Hasyim Ratsanjani, S.Kom., M.Kom.", "hasyim@polinema.ac.id", "Headmaster", "Aktif"],
        ["2341720090", "Siti Nurhaliza (SIB-2A)", "siti.nurhaliza@student.polinema.ac.id", "Ketua Kelas", "Nonaktif"]
    ];

    let audit = [];

    try {
        audit = JSON.parse(
            localStorage.getItem("sikara_audit_logs") || "[]"
        );

        if (!Array.isArray(audit)) {
            audit = [];
        }
    } catch (error) {
        console.error("Gagal membaca data aktivitas:", error);
    }

    // ========================================
    // 3. KONFIGURASI TABEL
    // ========================================

    const pageDefs = {
        lecturers: {
            title: "Daftar Dosen",
            id: "lecturersTable",
            headers: ["Kode Dosen", "Nama Dosen"],
            rows: lecturers
        },

        classes: {
            title: "Daftar Kelas",
            id: "classesTable",
            headers: ["Nama Kelas", "Tahun Akademik"],
            rows: classes
        },

        schedules: {
            title: "Jadwal Mata Kuliah",
            id: "schedulesTable",
            headers: ["Hari", "Jam", "Mata Kuliah", "Dosen", "Kelas", "Ruang"],
            rows: schedules
        },

        bookings: {
            title: "Riwayat Peminjaman",
            id: "bookingsTable",
            headers: ["Waktu", "Ruang", "Peminjam", "Detail Peminjaman", "Status"],
            rows: audit
                .filter(item => item.type === "BOOKING")
                .map(item => [
                    item.timestamp,
                    item.room,
                    item.actor,
                    item.details,
                    item.finalStatus
                ])
        },

        activity: {
            title: "Jurnal Activity Control",
            id: "activityTable",
            headers: ["Waktu", "Ruang", "Jenis Aktivitas", "Pelapor", "Detail", "Status Akhir"],
            rows: audit
                .filter(item => item.type === "REPORT" || item.type === "BUBAR")
                .map(item => [
                    item.timestamp,
                    item.room,
                    item.typeLabel,
                    item.actor,
                    item.details,
                    item.finalStatus
                ])
        },

        years: {
            title: "Academic Year",
            id: "yearsTable",
            headers: ["Tahun Akademik", "Status"],
            rows: academicYears
        },

        rooms: {
            title: "Daftar Ruang Kelas",
            id: "roomsTable",
            headers: ["Nama Ruang", "Gedung", "Status"],
            rows: rooms
        },

        courses: {
            title: "Daftar Mata Kuliah",
            id: "coursesTable",
            headers: ["Kode MK", "Nama Mata Kuliah"],
            rows: courses
        },

        users: {
            title: "Kelola Pengguna",
            id: "usersTable",
            headers: ["NIM / NIP / Username", "Nama Lengkap", "Email", "Peran", "Status"],
            rows: users
        }
    };

    const titles = {
        overview: "Ringkasan",
        ...Object.fromEntries(
            Object.entries(pageDefs).map(([key, page]) => [
                key,
                page.title
            ])
        )
    };

    const tableInstances = {};

    // ========================================
    // 4. FUNGSI BANTU
    // ========================================

    function escapeHtml(value) {
        return String(value ?? "").replace(/[&<>"']/g, char => ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#39;"
        })[char]);
    }

    function showToast(message, isError = false) {
        const area = document.getElementById("toastArea");

        if (!area) {
            console[isError ? "error" : "log"](message);
            return;
        }

        const node = document.createElement("div");

        node.className =
            `toast align-items-center text-bg-${isError ? "danger" : "success"} border-0`;

        node.setAttribute("role", "status");

        const body = document.createElement("div");
        body.className = "d-flex";

        const text = document.createElement("div");
        text.className = "toast-body";
        text.textContent = message;

        const closeButton = document.createElement("button");
        closeButton.type = "button";
        closeButton.className = "btn-close btn-close-white me-2 m-auto";
        closeButton.setAttribute("data-bs-dismiss", "toast");
        closeButton.setAttribute("aria-label", "Tutup");

        body.append(text, closeButton);
        node.appendChild(body);
        area.appendChild(node);

        if (typeof bootstrap !== "undefined" && bootstrap.Toast) {
            const toast = new bootstrap.Toast(node, { delay: 3500 });
            toast.show();

            node.addEventListener("hidden.bs.toast", () => node.remove());
        } else {
            node.classList.add("show");
            setTimeout(() => node.remove(), 3500);
        }
    }

    // ========================================
    // 5. RENDER TABEL
    // ========================================

    function drawTable(key, rows = pageDefs[key]?.rows || []) {
        const definition = pageDefs[key];

        if (!definition) return;

        const table = document.getElementById(definition.id);

        if (!table) return;

        if (tableInstances[key]) {
            const oldContainer = table.closest(".dt-container");
            if (oldContainer) {
                const filterGroup = oldContainer.querySelector(`[data-table-filter-group="${key}"]`);
                if (filterGroup) {
                    table.closest(".table-responsive, .content-card")?.before(filterGroup);
                }
            }
            tableInstances[key].destroy();
            delete tableInstances[key];
        }

        const tbody = table.querySelector("tbody");

        if (!tbody) return;

        const thList = Array.from(table.querySelectorAll("thead th"));
        const hasAction = thList.some(th => th.textContent.trim().toLowerCase() === "aksi");

        tbody.innerHTML = rows.map((row, rowIndex) => {
            const cells = row.map((cell, index) => {
                const value = escapeHtml(cell);

                if (
                    (key === "rooms" && index === 2) ||
                    (key === "years" && index === 1) ||
                    (key === "users" && index === 4)
                ) {
                    const status = String(cell ?? "").toLowerCase();

                    let badgeClass = "badge-green";

                    if (status === "terpakai" || status === "nonaktif" || status === "selesai") {
                        badgeClass = "badge-red";
                    } else if (status === "aktif" || status === "kosong") {
                        badgeClass = "badge-green";
                    }

                    return `<td><span class="badge-status ${badgeClass}">${value}</span></td>`;
                }

                if (key === "users" && index === 3) {
                    const role = String(cell ?? "").toLowerCase();
                    let roleBadge = "badge-blue";
                    if (role === "admin") roleBadge = "badge-red";
                    else if (role === "headmaster") roleBadge = "badge-green";
                    else if (role.includes("ketua")) roleBadge = "badge-blue";
                    return `<td><span class="badge-status ${roleBadge}">${value}</span></td>`;
                }

                return `<td>${value}</td>`;
            });

            if (hasAction) {
                let actionBtn = "";
                if (key === "lecturers") {
                    actionBtn = `
                        <button type="button" class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditDosen"
                            data-bs-kode="${escapeHtml(row[0])}"
                            data-bs-nama="${escapeHtml(row[1])}"
                            data-row-index="${rowIndex}"
                            title="Edit Data Dosen">
                            <i class="bi bi-pencil-square me-1"></i>Edit
                        </button>`;
                } else if (key === "years") {
                    actionBtn = `
                        <button type="button" class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditTahun"
                            data-bs-tahun="${escapeHtml(row[0])}"
                            data-bs-status="${escapeHtml(row[1])}"
                            data-row-index="${rowIndex}"
                            title="Edit Tahun Akademik">
                            <i class="bi bi-pencil-square me-1"></i>Edit
                        </button>`;
                } else if (key === "courses") {
                    actionBtn = `
                        <button type="button" class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditKursus"
                            data-bs-kode="${escapeHtml(row[0])}"
                            data-bs-nama="${escapeHtml(row[1])}"
                            data-row-index="${rowIndex}"
                            title="Edit Mata Kuliah">
                            <i class="bi bi-pencil-square me-1"></i>Edit
                        </button>`;
                } else if (key === "rooms") {
                    actionBtn = `
                        <button type="button" class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditRuang"
                            data-bs-nama="${escapeHtml(row[0])}"
                            data-bs-gedung="${escapeHtml(row[1])}"
                            data-bs-status="${escapeHtml(row[2])}"
                            data-row-index="${rowIndex}"
                            title="Edit Ruang Kelas">
                            <i class="bi bi-pencil-square me-1"></i>Edit
                        </button>`;
                } else if (key === "schedules") {
                    actionBtn = `
                        <button type="button" class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditJadwal"
                            data-bs-hari="${escapeHtml(row[0])}"
                            data-bs-jam="${escapeHtml(row[1])}"
                            data-bs-matkul="${escapeHtml(row[2])}"
                            data-bs-dosen="${escapeHtml(row[3])}"
                            data-bs-kelas="${escapeHtml(row[4])}"
                            data-bs-ruang="${escapeHtml(row[5])}"
                            data-row-index="${rowIndex}"
                            title="Edit Jadwal Kuliah">
                            <i class="bi bi-pencil-square me-1"></i>Edit
                        </button>`;
                } else if (key === "classes") {
                    actionBtn = `
                        <button type="button" class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditKelas"
                            data-bs-nama="${escapeHtml(row[0])}"
                            data-bs-tahun="${escapeHtml(row[1])}"
                            data-row-index="${rowIndex}"
                            title="Edit Kelas">
                            <i class="bi bi-pencil-square me-1"></i>Edit
                        </button>`;
                } else if (key === "users") {
                    actionBtn = `
                        <button type="button" class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditUser"
                            data-bs-username="${escapeHtml(row[0])}"
                            data-bs-nama="${escapeHtml(row[1])}"
                            data-bs-email="${escapeHtml(row[2])}"
                            data-bs-peran="${escapeHtml(row[3])}"
                            data-bs-status="${escapeHtml(row[4])}"
                            data-row-index="${rowIndex}"
                            title="Edit Pengguna">
                            <i class="bi bi-pencil-square me-1"></i>Edit
                        </button>`;
                }

                if (actionBtn) {
                    let itemLabel = "";
                    if (key === "lecturers") itemLabel = `${row[1]} (${row[0]})`;
                    else if (key === "years") itemLabel = row[0];
                    else if (key === "courses") itemLabel = `${row[1]} (${row[0]})`;
                    else if (key === "rooms") itemLabel = `${row[0]} (${row[1]})`;
                    else if (key === "schedules") itemLabel = `${row[2]} (${row[4]})`;
                    else if (key === "classes") itemLabel = row[0];
                    else if (key === "users") itemLabel = `${row[1]} (${row[0]})`;

                    const hapusBtn = `
                        <button type="button" class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#modalHapus"
                            data-bs-item="${escapeHtml(itemLabel)}"
                            data-bs-module="${key}"
                            data-row-index="${rowIndex}"
                            title="Hapus Data">
                            <i class="bi bi-trash me-1"></i>Hapus
                        </button>`;

                    cells.push(`
                        <td class="text-center">
                            <div class="d-inline-flex gap-1">
                                ${actionBtn}
                                ${hapusBtn}
                            </div>
                        </td>
                    `);
                }
            }

            return `<tr>${cells.join("")}</tr>`;
        }).join("");

        if (typeof DataTable === "function") {
            tableInstances[key] = new DataTable(table, {
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                order: [],
                columnDefs: hasAction ? [{ orderable: false, targets: -1 }] : [],
                layout: {
                    topStart: {
                        buttons: [
                            { extend: "copy", text: "<i class=\"bi bi-clipboard me-1\"></i>Copy" },
                            { extend: "excel", text: "<i class=\"bi bi-file-earmark-excel me-1\"></i>Excel" },
                            { extend: "pdf", text: "<i class=\"bi bi-file-earmark-pdf me-1\"></i>PDF" },
                            { extend: "print", text: "<i class=\"bi bi-printer me-1\"></i>Print" }
                        ]
                    },
                    topEnd: "search",
                    bottomStart: "info",
                    bottomEnd: "paging"
                },
                language: {
                    search: "Search:",
                    searchPlaceholder: "Search records...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    zeroRecords: "No matching records found",
                    emptyTable: "No data available in table",
                    paginate: {
                        first: "«",
                        last: "»",
                        next: "›",
                        previous: "‹"
                    }
                }
            });

            // Posisikan search input DataTables di sebelah kiri filter dropdown
            const filterGroup = document.querySelector(`[data-table-filter-group="${key}"]`);
            const dtContainer = table.closest(".dt-container");
            if (filterGroup && dtContainer) {
                const layoutEnd = dtContainer.querySelector(".dt-layout-end");
                if (layoutEnd && !layoutEnd.contains(filterGroup)) {
                    filterGroup.classList.remove("d-none");
                    const searchEl = layoutEnd.querySelector(".dt-search");
                    if (searchEl) {
                        searchEl.after(filterGroup);
                    } else {
                        layoutEnd.appendChild(filterGroup);
                    }
                }

                // Terapkan filter yang sedang terpilih jika ada
                filterGroup.querySelectorAll("select[data-col]").forEach(sel => {
                    if (sel.value.trim()) {
                        const colIdx = parseInt(sel.dataset.col, 10);
                        const escaped = sel.value.trim().replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                        tableInstances[key].column(colIdx).search("^" + escaped + "$", true, false);
                    }
                });
                tableInstances[key].draw();
            }
        }
    }

    // ========================================
    // 6. HALAMAN RINGKASAN
    // ========================================

    function renderOverview() {
        const summaryCards = document.getElementById("summaryCards");

        if (summaryCards) {
            const counts = [
                ["Ruang kelas", rooms.length, "bi-door-open"],
                ["Dosen", lecturers.length, "bi-person-vcard"],
                ["Kelas", classes.length, "bi-people"],
                ["Jadwal", schedules.length, "bi-calendar3"]
            ];

            summaryCards.innerHTML = counts.map(([label, number, icon]) => `
                <div class="col-6 col-xl-3">
                    <div class="summary-card">
                        <span class="summary-icon">
                            <i class="bi ${icon}"></i>
                        </span>
                        <span>
                            <span class="summary-label">${label}</span>
                            <span class="summary-number d-block">${number}</span>
                        </span>
                    </div>
                </div>
            `).join("");
        }

        const table = document.querySelector("#todayTable tbody");

        if (!table) return;

        table.innerHTML = schedules.slice(0, 5).map(schedule => `
            <tr>
                <td>${escapeHtml(schedule[1])}</td>
                <td>${escapeHtml(schedule[2])}</td>
                <td>${escapeHtml(schedule[4])}</td>
                <td>${escapeHtml(schedule[5])}</td>
                <td><span class="badge-status badge-blue">Terjadwal</span></td>
            </tr>
        `).join("");

        if (!tableInstances.overview && typeof DataTable === "function") {
            tableInstances.overview = new DataTable("#todayTable", {
                pageLength: 5,
                searching: true,
                ordering: true,
                info: true,
                paging: true,
                layout: {
                    topStart: null,
                    topEnd: "search",
                    bottomStart: "info",
                    bottomEnd: "paging"
                },
                language: {
                    search: "Search:",
                    searchPlaceholder: "Search schedules...",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    paginate: {
                        next: "›",
                        previous: "‹"
                    }
                }
            });
        }
    }

    // ========================================
    // 7. NAVIGASI ANTARHALAMAN
    // ========================================

    function showPage(key) {
        if (!titles[key]) return;

        document.querySelectorAll(".admin-page").forEach(page => {
            page.classList.toggle("active", page.id === `page-${key}`);
        });

        document.querySelectorAll(".side-link").forEach(button => {
            button.classList.toggle("active", button.dataset.page === key);
        });

        const pageTitle = document.getElementById("pageTitle");

        if (pageTitle) {
            pageTitle.textContent = titles[key];
        }

        const sidebar = document.getElementById("adminSidebar");
        const scrim = document.getElementById("sidebarScrim");

        sidebar?.classList.remove("open");
        scrim?.classList.remove("show");

        if (key === "overview") {
            renderOverview();
        } else {
            drawTable(key);
        }
    }

    // Render overview jika komponen summary tersedia di halaman
    if (document.getElementById("summaryCards") || document.getElementById("todayTable")) {
        renderOverview();
    }

    // Render tabel modul yang ada di halaman aktif
    Object.keys(pageDefs).forEach(key => {
        if (document.getElementById(pageDefs[key].id)) {
            drawTable(key);
        }
    });

    document.querySelectorAll(".side-link[data-page]").forEach(button => {
        button.addEventListener("click", () => {
            showPage(button.dataset.page);
        });
    });

    document.querySelectorAll("[data-open-page]").forEach(button => {
        button.addEventListener("click", () => {
            showPage(button.dataset.openPage);
        });
    });

    // ========================================
    // 8. SIDEBAR RESPONSIF
    // ========================================

    const sidebar = document.getElementById("adminSidebar");
    const sidebarScrim = document.getElementById("sidebarScrim");
    const sidebarToggle = document.getElementById("sidebarToggle");

    sidebarToggle?.addEventListener("click", () => {
        sidebar?.classList.toggle("open");
        sidebarScrim?.classList.toggle("show");
    });

    sidebarScrim?.addEventListener("click", () => {
        sidebar?.classList.remove("open");
        sidebarScrim.classList.remove("show");
    });

    // ========================================
    // 9. LOGOUT
    // Mengakhiri sesi melalui PHP
    // ========================================

    const logoutBtnEl = document.getElementById("logoutBtn");
    if (logoutBtnEl) {
        logoutBtnEl.addEventListener("click", (e) => {
            const href = logoutBtnEl.getAttribute("href");
            if (!href || href === "#") {
                e.preventDefault();
                window.location.href = "../logout.php";
            }
        });
    }

    // ========================================
    // 10. IMPOR EXCEL
    // ========================================

    const importMap = {
        lecturers: "importLecturers",
        classes: "importClasses",
        schedules: "importSchedules",
        courses: "importCourses",
        rooms: "importRooms",
        users: "importUsers",
        years: "importYears"
    };

    document.querySelectorAll("[data-import]").forEach(button => {
        button.addEventListener("click", () => {
            const inputId = importMap[button.dataset.import];
            const input = inputId ? document.getElementById(inputId) : null;

            if (input) input.click();
        });
    });

    Object.entries(importMap).forEach(([key, inputId]) => {
        const input = document.getElementById(inputId);

        if (!input) return;

        input.addEventListener("change", async event => {
            const file = event.target.files?.[0];

            if (!file) return;

            try {
                if (typeof XLSX === "undefined") {
                    throw new Error("Library Excel belum berhasil dimuat.");
                }

                const data = await file.arrayBuffer();
                const workbook = XLSX.read(data, { type: "array" });

                const firstSheet = workbook.Sheets[workbook.SheetNames[0]];

                const matrix = XLSX.utils.sheet_to_json(firstSheet, {
                    header: 1,
                    defval: ""
                });

                // Baris pertama dianggap sebagai header.
                const imported = matrix.slice(1).filter(row =>
                    row.some(value => String(value).trim() !== "")
                );

                if (!imported.length) {
                    throw new Error("Tidak ada data setelah baris header.");
                }

                pageDefs[key].rows = imported;

                drawTable(key);

                showToast(
                    `${imported.length} baris berhasil diimpor ke ${pageDefs[key].title}.`
                );
            } catch (error) {
                showToast(`Gagal membaca file: ${error.message}`, true);
            } finally {
                event.target.value = "";
            }
        });
    });



    // ========================================
    // 11. MODAL TAMBAH & EDIT (BOOTSTRAP NATIVE)
    // Menggunakan event Bootstrap show.bs.modal & submit
    // ========================================

    const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.value = val ?? "";
    };

    // --- MODAL DOSEN ---
    const modalEditDosen = document.getElementById("modalEditDosen");
    if (modalEditDosen) {
        modalEditDosen.addEventListener("show.bs.modal", event => {
            const btn = event.relatedTarget;
            if (!btn) return;
            setVal("editKodeDosen", btn.getAttribute("data-bs-kode"));
            setVal("editNamaDosen", btn.getAttribute("data-bs-nama"));
            setVal("editRowIndexDosen", btn.getAttribute("data-row-index"));
        });
    }

    const formEditDosen = document.getElementById("formEditDosen");
    if (formEditDosen) {
        formEditDosen.addEventListener("submit", event => {
            event.preventDefault();
            const idx = parseInt(document.getElementById("editRowIndexDosen")?.value, 10);
            const kode = document.getElementById("editKodeDosen")?.value || "";
            const nama = document.getElementById("editNamaDosen")?.value || "";

            if (!isNaN(idx) && pageDefs.lecturers?.rows[idx]) {
                pageDefs.lecturers.rows[idx] = [kode, nama];
                drawTable("lecturers");
                const inst = bootstrap.Modal.getInstance(modalEditDosen);
                inst?.hide();
                showToast("Data dosen berhasil diperbarui.");
            }
        });
    }

    const formTambahDosen = document.getElementById("formTambahDosen");
    if (formTambahDosen) {
        formTambahDosen.addEventListener("submit", event => {
            event.preventDefault();
            const kode = document.getElementById("kodeDosen")?.value || "";
            const nama = document.getElementById("namaDosen")?.value || "";

            if (pageDefs.lecturers) {
                pageDefs.lecturers.rows.push([kode, nama]);
                drawTable("lecturers");
                const modalEl = document.getElementById("modalTambahDosen");
                const inst = modalEl && bootstrap.Modal.getInstance(modalEl);
                inst?.hide();
                formTambahDosen.reset();
                showToast("Data dosen berhasil ditambahkan.");
            }
        });
    }

    // --- MODAL TAHUN AKADEMIK ---
    const modalEditTahun = document.getElementById("modalEditTahun");
    if (modalEditTahun) {
        modalEditTahun.addEventListener("show.bs.modal", event => {
            const btn = event.relatedTarget;
            if (!btn) return;
            setVal("editNamaTahun", btn.getAttribute("data-bs-tahun"));
            setVal("editStatusTahun", btn.getAttribute("data-bs-status"));
            setVal("editRowIndexTahun", btn.getAttribute("data-row-index"));
        });
    }

    const formEditTahun = document.getElementById("formEditTahun");
    if (formEditTahun) {
        formEditTahun.addEventListener("submit", event => {
            event.preventDefault();
            const idx = parseInt(document.getElementById("editRowIndexTahun")?.value, 10);
            const tahun = document.getElementById("editNamaTahun")?.value || "";
            const status = document.getElementById("editStatusTahun")?.value || "";

            if (!isNaN(idx) && pageDefs.years?.rows[idx]) {
                pageDefs.years.rows[idx] = [tahun, status];
                drawTable("years");
                const inst = bootstrap.Modal.getInstance(modalEditTahun);
                inst?.hide();
                showToast("Data tahun akademik berhasil diperbarui.");
            }
        });
    }

    const formTambahTahun = document.getElementById("formTambahTahun");
    if (formTambahTahun) {
        formTambahTahun.addEventListener("submit", event => {
            event.preventDefault();
            const tahun = document.getElementById("namaTahun")?.value || "";
            const status = document.getElementById("statusTahun")?.value || "";

            if (pageDefs.years) {
                pageDefs.years.rows.push([tahun, status]);
                drawTable("years");
                const modalEl = document.getElementById("modalTambahTahun");
                const inst = modalEl && bootstrap.Modal.getInstance(modalEl);
                inst?.hide();
                formTambahTahun.reset();
                showToast("Data tahun akademik berhasil ditambahkan.");
            }
        });
    }

    // --- MODAL MATA KULIAH ---
    const modalEditKursus = document.getElementById("modalEditKursus");
    if (modalEditKursus) {
        modalEditKursus.addEventListener("show.bs.modal", event => {
            const btn = event.relatedTarget;
            if (!btn) return;
            setVal("editKodeMatkul", btn.getAttribute("data-bs-kode"));
            setVal("editNamaMatkul", btn.getAttribute("data-bs-nama"));
            setVal("editRowIndexKursus", btn.getAttribute("data-row-index"));
        });
    }

    const formEditKursus = document.getElementById("formEditKursus");
    if (formEditKursus) {
        formEditKursus.addEventListener("submit", event => {
            event.preventDefault();
            const idx = parseInt(document.getElementById("editRowIndexKursus")?.value, 10);
            const kode = document.getElementById("editKodeMatkul")?.value || "";
            const nama = document.getElementById("editNamaMatkul")?.value || "";

            if (!isNaN(idx) && pageDefs.courses?.rows[idx]) {
                pageDefs.courses.rows[idx] = [kode, nama];
                drawTable("courses");
                const inst = bootstrap.Modal.getInstance(modalEditKursus);
                inst?.hide();
                showToast("Data mata kuliah berhasil diperbarui.");
            }
        });
    }

    const formTambahKursus = document.getElementById("formTambahKursus");
    if (formTambahKursus) {
        formTambahKursus.addEventListener("submit", event => {
            event.preventDefault();
            const kode = document.getElementById("kodeMatkul")?.value || "";
            const nama = document.getElementById("namaMatkul")?.value || "";

            if (pageDefs.courses) {
                pageDefs.courses.rows.push([kode, nama]);
                drawTable("courses");
                const modalEl = document.getElementById("modalTambahKursus");
                const inst = modalEl && bootstrap.Modal.getInstance(modalEl);
                inst?.hide();
                formTambahKursus.reset();
                showToast("Data mata kuliah berhasil ditambahkan.");
            }
        });
    }

    // --- MODAL RUANG ---
    const modalEditRuang = document.getElementById("modalEditRuang");
    if (modalEditRuang) {
        modalEditRuang.addEventListener("show.bs.modal", event => {
            const btn = event.relatedTarget;
            if (!btn) return;
            setVal("editNamaRuang", btn.getAttribute("data-bs-nama"));
            setVal("editGedungRuang", btn.getAttribute("data-bs-gedung"));
            setVal("editStatusRuang", btn.getAttribute("data-bs-status"));
            setVal("editRowIndexRuang", btn.getAttribute("data-row-index"));
        });
    }

    const formEditRuang = document.getElementById("formEditRuang");
    if (formEditRuang) {
        formEditRuang.addEventListener("submit", event => {
            event.preventDefault();
            const idx = parseInt(document.getElementById("editRowIndexRuang")?.value, 10);
            const nama = document.getElementById("editNamaRuang")?.value || "";
            const gedung = document.getElementById("editGedungRuang")?.value || "";
            const status = document.getElementById("editStatusRuang")?.value || "";

            if (!isNaN(idx) && pageDefs.rooms?.rows[idx]) {
                pageDefs.rooms.rows[idx] = [nama, gedung, status];
                drawTable("rooms");
                const inst = bootstrap.Modal.getInstance(modalEditRuang);
                inst?.hide();
                showToast("Data ruang berhasil diperbarui.");
            }
        });
    }

    const formTambahRuang = document.getElementById("formTambahRuang");
    if (formTambahRuang) {
        formTambahRuang.addEventListener("submit", event => {
            event.preventDefault();
            const nama = document.getElementById("namaRuang")?.value || "";
            const gedung = document.getElementById("gedungRuang")?.value || "";
            const status = document.getElementById("statusRuang")?.value || "";

            if (pageDefs.rooms) {
                pageDefs.rooms.rows.push([nama, gedung, status]);
                drawTable("rooms");
                const modalEl = document.getElementById("modalTambahRuang");
                const inst = modalEl && bootstrap.Modal.getInstance(modalEl);
                inst?.hide();
                formTambahRuang.reset();
                showToast("Data ruang berhasil ditambahkan.");
            }
        });
    }

    // --- MODAL JADWAL ---
    const modalEditJadwal = document.getElementById("modalEditJadwal");
    if (modalEditJadwal) {
        modalEditJadwal.addEventListener("show.bs.modal", event => {
            const btn = event.relatedTarget;
            if (!btn) return;
            setVal("editHariJadwal", btn.getAttribute("data-bs-hari"));
            setVal("editJamJadwal", btn.getAttribute("data-bs-jam"));
            setVal("editMatkulJadwal", btn.getAttribute("data-bs-matkul"));
            setVal("editDosenJadwal", btn.getAttribute("data-bs-dosen"));
            setVal("editKelasJadwal", btn.getAttribute("data-bs-kelas"));
            setVal("editRuangJadwal", btn.getAttribute("data-bs-ruang"));
            setVal("editRowIndexJadwal", btn.getAttribute("data-row-index"));
        });
    }

    const formEditJadwal = document.getElementById("formEditJadwal");
    if (formEditJadwal) {
        formEditJadwal.addEventListener("submit", event => {
            event.preventDefault();
            const idx = parseInt(document.getElementById("editRowIndexJadwal")?.value, 10);
            const hari = document.getElementById("editHariJadwal")?.value || "";
            const jam = document.getElementById("editJamJadwal")?.value || "";
            const matkul = document.getElementById("editMatkulJadwal")?.value || "";
            const dosen = document.getElementById("editDosenJadwal")?.value || "";
            const kelas = document.getElementById("editKelasJadwal")?.value || "";
            const ruang = document.getElementById("editRuangJadwal")?.value || "";

            if (!isNaN(idx) && pageDefs.schedules?.rows[idx]) {
                pageDefs.schedules.rows[idx] = [hari, jam, matkul, dosen, kelas, ruang];
                drawTable("schedules");
                const inst = bootstrap.Modal.getInstance(modalEditJadwal);
                inst?.hide();
                showToast("Data jadwal berhasil diperbarui.");
            }
        });
    }

    const formTambahJadwal = document.getElementById("formTambahJadwal");
    if (formTambahJadwal) {
        formTambahJadwal.addEventListener("submit", event => {
            event.preventDefault();
            const hari = document.getElementById("hariJadwal")?.value || "";
            const jam = document.getElementById("jamJadwal")?.value || "";
            const matkul = document.getElementById("matkulJadwal")?.value || "";
            const dosen = document.getElementById("dosenJadwal")?.value || "";
            const kelas = document.getElementById("kelasJadwal")?.value || "";
            const ruang = document.getElementById("ruangJadwal")?.value || "";

            if (pageDefs.schedules) {
                pageDefs.schedules.rows.push([hari, jam, matkul, dosen, kelas, ruang]);
                drawTable("schedules");
                const modalEl = document.getElementById("modalTambahJadwal");
                const inst = modalEl && bootstrap.Modal.getInstance(modalEl);
                inst?.hide();
                formTambahJadwal.reset();
                showToast("Data jadwal berhasil ditambahkan.");
            }
        });
    }

    // --- MODAL KELAS ---
    const modalEditKelas = document.getElementById("modalEditKelas");
    if (modalEditKelas) {
        modalEditKelas.addEventListener("show.bs.modal", event => {
            const btn = event.relatedTarget;
            if (!btn) return;
            setVal("editNamaKelas", btn.getAttribute("data-bs-nama"));
            setVal("editTahunAkademikKelas", btn.getAttribute("data-bs-tahun"));
            setVal("editRowIndexKelas", btn.getAttribute("data-row-index"));
        });
    }

    const formEditKelas = document.getElementById("formEditKelas");
    if (formEditKelas) {
        formEditKelas.addEventListener("submit", event => {
            event.preventDefault();
            const idx = parseInt(document.getElementById("editRowIndexKelas")?.value, 10);
            const nama = document.getElementById("editNamaKelas")?.value || "";
            const tahun = document.getElementById("editTahunAkademikKelas")?.value || "";

            if (!isNaN(idx) && pageDefs.classes?.rows[idx]) {
                pageDefs.classes.rows[idx] = [nama, tahun];
                drawTable("classes");
                const inst = bootstrap.Modal.getInstance(modalEditKelas);
                inst?.hide();
                showToast("Data kelas berhasil diperbarui.");
            }
        });
    }

    const formTambahKelas = document.getElementById("formTambahKelas");
    if (formTambahKelas) {
        formTambahKelas.addEventListener("submit", event => {
            event.preventDefault();
            const nama = document.getElementById("namaKelas")?.value || "";
            const tahun = document.getElementById("tahunAkademikKelas")?.value || "";

            if (pageDefs.classes) {
                pageDefs.classes.rows.push([nama, tahun]);
                drawTable("classes");
                const modalEl = document.getElementById("modalTambahKelas");
                const inst = modalEl && bootstrap.Modal.getInstance(modalEl);
                inst?.hide();
                formTambahKelas.reset();
                showToast("Data kelas berhasil ditambahkan.");
            }
        });
    }


    // --- MODAL HAPUS DATA ---
    let currentHapusModule = null;
    const modalHapus = document.getElementById("modalHapus");
    if (modalHapus) {
        modalHapus.addEventListener("show.bs.modal", event => {
            const btn = event.relatedTarget;
            if (!btn) return;
            const item = btn.getAttribute("data-bs-item") || "data ini";
            const rowIdx = btn.getAttribute("data-row-index");
            currentHapusModule = btn.getAttribute("data-bs-module");

            const labelEl = document.getElementById("hapusItemLabel");
            const idxEl = document.getElementById("hapusRowIndex");
            if (labelEl) labelEl.textContent = item;
            if (idxEl) idxEl.value = rowIdx;
        });
    }

    const formHapus = document.getElementById("formHapus");
    if (formHapus) {
        formHapus.addEventListener("submit", event => {
            event.preventDefault();
            const idx = parseInt(document.getElementById("hapusRowIndex")?.value, 10);

            if (currentHapusModule && !isNaN(idx) && pageDefs[currentHapusModule]?.rows[idx]) {
                pageDefs[currentHapusModule].rows.splice(idx, 1);
                drawTable(currentHapusModule);
                const inst = bootstrap.Modal.getInstance(modalHapus);
                inst?.hide();
                showToast("Data berhasil dihapus.");
            }
        });
    }


    // --- MODAL USER ---
    const modalEditUser = document.getElementById("modalEditUser");
    if (modalEditUser) {
        modalEditUser.addEventListener("show.bs.modal", event => {
            const btn = event.relatedTarget;
            if (!btn) return;
            setVal("editUsernameUser", btn.getAttribute("data-bs-username"));
            setVal("editNamaUser", btn.getAttribute("data-bs-nama"));
            setVal("editEmailUser", btn.getAttribute("data-bs-email"));
            setVal("editPeranUser", btn.getAttribute("data-bs-peran"));
            setVal("editStatusUser", btn.getAttribute("data-bs-status"));
            setVal("editRowIndexUser", btn.getAttribute("data-row-index"));
            setVal("editPasswordUser", "");
        });
    }

    const formEditUser = document.getElementById("formEditUser");
    if (formEditUser) {
        formEditUser.addEventListener("submit", event => {
            event.preventDefault();
            const idx = parseInt(document.getElementById("editRowIndexUser")?.value, 10);
            const username = document.getElementById("editUsernameUser")?.value || "";
            const nama = document.getElementById("editNamaUser")?.value || "";
            const email = document.getElementById("editEmailUser")?.value || "";
            const peran = document.getElementById("editPeranUser")?.value || "";
            const status = document.getElementById("editStatusUser")?.value || "";

            if (!isNaN(idx) && pageDefs.users?.rows[idx]) {
                pageDefs.users.rows[idx] = [username, nama, email, peran, status];
                drawTable("users");
                const inst = bootstrap.Modal.getInstance(modalEditUser);
                inst?.hide();
                showToast("Data pengguna berhasil diperbarui.");
            }
        });
    }

    const formTambahUser = document.getElementById("formTambahUser");
    if (formTambahUser) {
        formTambahUser.addEventListener("submit", event => {
            event.preventDefault();
            const username = document.getElementById("usernameUser")?.value || "";
            const nama = document.getElementById("namaUser")?.value || "";
            const email = document.getElementById("emailUser")?.value || "";
            const peran = document.getElementById("peranUser")?.value || "";
            const status = document.getElementById("statusUser")?.value || "";

            if (pageDefs.users) {
                pageDefs.users.rows.push([username, nama, email, peran, status]);
                drawTable("users");
                const modalEl = document.getElementById("modalTambahUser");
                const inst = modalEl && bootstrap.Modal.getInstance(modalEl);
                inst?.hide();
                formTambahUser.reset();
                showToast("Data pengguna berhasil ditambahkan.");
            }
        });
    }

    // ========================================
    // 12. FITUR FILTER & SEARCH CEPAT (DATATABLES API)
    // ========================================

    // 1. Pencarian instan berdasarkan input ketikan
    document.querySelectorAll("input[id^='search']").forEach(input => {
        input.addEventListener("input", () => {
            const raw = input.id.replace("search", "").toLowerCase();
            const tableKey = raw.endsWith("s") ? raw : (raw === "course" ? "courses" : (raw === "room" ? "rooms" : (raw === "user" ? "users" : (raw === "class" ? "classes" : (raw === "schedule" ? "schedules" : (raw === "lecturer" ? "lecturers" : (raw === "year" ? "years" : raw)))))));
            const dt = tableInstances[tableKey] || tableInstances[raw] || tableInstances[raw + "s"];
            if (dt) {
                dt.search(input.value).draw();
            }
        });
    });

    // 2. Filter dropdown kolom tertentu (misal: Tahun, Hari, Kelas, Ruang, Status, Peran)
    document.addEventListener("change", event => {
        const select = event.target.closest("select[data-table][data-col]");
        if (!select) return;

        const tableKey = select.dataset.table;
        const colIdx = parseInt(select.dataset.col, 10);
        const val = select.value.trim();
        const dt = tableInstances[tableKey];
        if (!dt) return;

        if (!val) {
            dt.column(colIdx).search("").draw();
        } else {
            const escaped = val.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
            dt.column(colIdx).search("^" + escaped + "$", true, false).draw();
        }
    });

    // 3. Tombol Reset Filter & Search
    document.querySelectorAll("[data-reset-filter]").forEach(button => {
        button.addEventListener("click", () => {
            const tableKey = button.dataset.resetFilter;
            const dt = tableInstances[tableKey];
            if (!dt) return;

            // Kosongkan seluruh filter select & input search untuk tabel ini
            document.querySelectorAll(`select[data-table="${tableKey}"]`).forEach(sel => sel.value = "");
            
            // Cari input search terkait
            document.querySelectorAll("input[id^='search']").forEach(inp => {
                if (inp.id.toLowerCase().includes(tableKey.slice(0, 4))) {
                    inp.value = "";
                }
            });

            dt.search("").columns().search("").draw();
            showToast("Filter pencarian direset.");
        });
    });

})();
