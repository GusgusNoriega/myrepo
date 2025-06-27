document.addEventListener('DOMContentLoaded', () => {
    const sidebar  = document.getElementById('sidebar');
    const toggle   = document.getElementById('sidebarToggle');
    const accBtns  = document.querySelectorAll('.accordion');

    /* Sidebar colapsada al cargar en pantallas pequeñas */
    if (window.innerWidth < 768) sidebar.classList.add('collapsed');

    /* Mostrar / ocultar sidebar */
    toggle.addEventListener('click', () => sidebar.classList.toggle('collapsed'));

    /* --- Acordeones con apertura automática según URL --- */
    accBtns.forEach(btn => {
        const submenu   = btn.nextElementSibling;       // UL
        const menuItem  = btn.parentElement;            // LI

        // Estado inicial
        submenu.style.height = 0;

        // Comprueba si dentro del submenu hay un enlace activo
        if (submenu.querySelector('a.active')) {
            submenu.style.height = submenu.scrollHeight + 'px';
            menuItem.classList.add('open');
        }

        // Click manual
        btn.addEventListener('click', () => {
            const isOpen = menuItem.classList.contains('open');

            /* Cierra otros menús si quieres comportamiento exclusivo */
            accBtns.forEach(otherBtn => {
                const otherItem = otherBtn.parentElement;
                if (otherItem !== menuItem) {
                    otherItem.classList.remove('open');
                    otherBtn.nextElementSibling.style.height = 0;
                }
            });

            // Toggle actual
            menuItem.classList.toggle('open');
            submenu.style.height = isOpen ? 0 : submenu.scrollHeight + 'px';
        });

    });

});

// obtiene el token desde la meta
const API_TOKEN = document
    .querySelector('meta[name="api-token"]')
    ?.getAttribute('content') || sessionStorage.getItem('api_token');

// Guarda en sessionStorage para que los «fetch» internos, sin recarga, lo sigan usando
if (API_TOKEN) sessionStorage.setItem('api_token', API_TOKEN);

export async function apiFetch (url, opts = {}) {
    const token = sessionStorage.getItem('api_token');
    return fetch(url, {
        ...opts,
        headers: {
            'Accept'       : 'application/json',
            'Content-Type' : 'application/json',
            'Authorization': `Bearer ${token}`,
            ...opts.headers,
        }
    });
}




