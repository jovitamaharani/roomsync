
// DATA FORM LAMA JIKA VALIDASI GAGAL
const oldState = window.adminUsersOldState;


// MENGISI FORM EDIT
function fillForm(form, data) {
    Object.keys(data).forEach(function (key) {
        const el = form.elements[key];

        if (el) {
            el.value = data[key] ?? '';
        }
    });
}


// MENGATUR IKON PASSWORD
function setPasswordIcon(button, isVisible) {
    const icon = button.querySelector('.password-eye-icon');

    if (!icon) return;

    if (isVisible) {
        // MATA TERBUKA (BOOTSTRAP ICONS)
        icon.innerHTML = '<i class="bi bi-eye"></i>';
    } else {
        // MATA TERPEJAM (SVG)
        icon.innerHTML = `
            <svg viewBox="0 0 32 32" aria-hidden="true">
                <path d="M3 17 Q8 26 16 26 Q24 26 29 17"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"/>
                <path d="M7 22 L5 25 M12 25 L11 29 M20 25 L21 29 M25 22 L27 25"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"/>
            </svg>
        `;
    }
}

document.addEventListener('DOMContentLoaded', function () {

    // DATATABLES
    function initTable() {
        if (typeof DataTable === 'undefined') {
            setTimeout(initTable, 50);
            return;
        }

        if (
            document.getElementById('usersTable') &&
            !DataTable.isDataTable('#usersTable')
        ) {
            new DataTable('#usersTable', {
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: -1 }
                ],
                layout: {
                    topStart: {
                        buttons: [
                            {
                                extend: "copy",
                                text: '<i class="bi bi-clipboard me-1"></i>Copy',
                                exportOptions: { columns: ':not(:last-child)' }
                            },
                            {
                                extend: "excel",
                                text: '<i class="bi bi-file-earmark-excel me-1"></i>Excel',
                                exportOptions: { columns: ':not(:last-child)' }
                            },
                            {
                                extend: "pdf",
                                text: '<i class="bi bi-file-earmark-pdf me-1"></i>PDF',
                                exportOptions: { columns: ':not(:last-child)' }
                            },
                            {
                                extend: "print",
                                text: '<i class="bi bi-printer me-1"></i>Print',
                                exportOptions: { columns: ':not(:last-child)' }
                            }
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
        }
    }

    initTable();


    // MENGAMBIL ELEMEN MODAL
    const modalTambahEl = document.getElementById('modalTambahUser');
    const modalEditEl = document.getElementById('modalEditUser');
    const modalHapusEl = document.getElementById('modalHapusUser');


    // TAMPILKAN / SEMBUNYIKAN PASSWORD
    document.addEventListener('click', function (event) {
        const button = event.target.closest('.toggle-password');

        if (!button) return;

        const wrapper = button.closest('.password-field');
        const input = wrapper
            ? wrapper.querySelector('input')
            : null;

        if (!input) return;

        const willShow = input.type === 'password';

        input.type = willShow ? 'text' : 'password';

        setPasswordIcon(button, willShow);

        button.setAttribute('aria-pressed', String(willShow));

        button.setAttribute(
            'aria-label',
            willShow
                ? 'Sembunyikan password'
                : 'Tampilkan password'
        );
    });


    // RESET VISIBILITAS PASSWORD
    function resetPasswordVisibility(form) {
        if (!form) return;

        form.querySelectorAll('.password-field').forEach(function (wrapper) {
            const input = wrapper.querySelector('input');
            const button = wrapper.querySelector('.toggle-password');

            if (input) {
                input.type = 'password';
            }

            if (button) {
                setPasswordIcon(button, false);

                button.setAttribute('aria-pressed', 'false');
                button.setAttribute(
                    'aria-label',
                    'Tampilkan password'
                );
            }
        });
    }


    // MODAL TAMBAH
    modalTambahEl.addEventListener('hidden.bs.modal', function () {
        const form = modalTambahEl.querySelector('form');

        form.reset();
        resetPasswordVisibility(form);
    });


    // MODAL EDIT
    modalEditEl.addEventListener('hidden.bs.modal', function () {
        resetPasswordVisibility(
            modalEditEl.querySelector('form')
        );
    });


    // ISI DATA EDIT
    modalEditEl.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        if (button && button.dataset.user) {
            const form = modalEditEl.querySelector('form');

            fillForm(
                form,
                JSON.parse(button.dataset.user)
            );

            form.elements.password.value = '';
            form.elements.password_confirm.value = '';

            resetPasswordVisibility(form);
        }
    });


    // MODAL HAPUS
    modalHapusEl.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        if (button) {
            document.getElementById('hapusUserId').value =
                button.dataset.id || '';

            document.getElementById('hapusUserName').textContent =
                button.dataset.label
                    ? '"' + button.dataset.label + '"'
                    : 'ini';
        }
    });


    // BUKA KEMBALI MODAL JIKA VALIDASI GAGAL
    function reopenModal() {
        if (typeof bootstrap === 'undefined') {
            setTimeout(reopenModal, 50);
            return;
        }

        const el = oldState.modal === 'edit'
            ? modalEditEl
            : modalTambahEl;

        const form = el.querySelector('form');

        fillForm(form, oldState.data);
        resetPasswordVisibility(form);

        bootstrap.Modal.getOrCreateInstance(el).show();
    }

    if (oldState) {
        reopenModal();
    }

});
