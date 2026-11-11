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
    window.showToast = showToast;

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

