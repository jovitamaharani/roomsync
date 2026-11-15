
document.addEventListener('DOMContentLoaded', function () {

    const buttons = document.querySelectorAll('.toggle-reset-password');

    // SVG mata tertutup
    const closedEye = `
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

    // SVG mata terbuka
    const openEye = `
    <svg viewBox="0 0 32 32" aria-hidden="true">
        <path d="M2 16 Q8 6 16 6 Q24 6 30 16 Q24 26 16 26 Q8 26 2 16Z"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linejoin="round"/>
        <circle cx="16" cy="16" r="5.5"
            fill="none"
            stroke="currentColor"
            stroke-width="2"/>
    </svg>
`;


    buttons.forEach(function (button) {

        const input = document.getElementById(button.dataset.target);
        const icon = button.querySelector('.password-eye-icon');

        if (!input || !icon) return;

        // Kondisi awal: mata tertutup
        icon.innerHTML = closedEye;

        button.addEventListener('click', function () {

            const isVisible = input.type === 'password';

            input.type = isVisible ? 'text' : 'password';

            icon.innerHTML = isVisible ? openEye : closedEye;

            button.setAttribute('aria-pressed', String(isVisible));

            button.setAttribute(
                'aria-label',
                isVisible ? 'Sembunyikan password' : 'Tampilkan password'
            );

        });

    });

});
