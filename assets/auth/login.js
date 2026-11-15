document.addEventListener('DOMContentLoaded', function () {

    const button = document.querySelector('.toggle-login-password');
    const input = document.getElementById('password');

    if (!button || !input) return;

    const icon = button.querySelector('.password-eye-icon');

    if (!icon) return;

    // GANTI IKON SESUAI KONDISI PASSWORD
    function updatePasswordIcon(isVisible) {
        if (isVisible) {
            icon.innerHTML = '<i class="bi bi-eye"></i>';
        } else {
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

    // KLIK TOMBOL MATA
    button.addEventListener('click', function () {
        const isVisible = input.type === 'password';

        input.type = isVisible ? 'text' : 'password';

        updatePasswordIcon(isVisible);

        button.setAttribute('aria-pressed', String(isVisible));
        button.setAttribute(
            'aria-label',
            isVisible ? 'Sembunyikan password' : 'Tampilkan password'
        );
    });

});
