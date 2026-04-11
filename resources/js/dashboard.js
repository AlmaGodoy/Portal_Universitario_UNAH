document.addEventListener('DOMContentLoaded', function () {

    // ── SIDEBAR (tu código original) ──────────────────────────
    function applyCollapse(collapsed) {
        if (collapsed) {
            document.body.classList.add('sidebar-collapse');
        } else {
            document.body.classList.remove('sidebar-collapse');
        }
    }

    function ajustarSidebarScroll() {
        const aside     = document.querySelector('.main-sidebar');
        const brand     = document.querySelector('.main-sidebar .brand-link');
        const userCard  = document.querySelector('.sidebar-user-card');
        const scrollArea = document.getElementById('dashboardSidebarScroll');

        if (!aside || !brand || !scrollArea) return;

        const viewportHeight  = window.innerHeight;
        const brandHeight     = brand.offsetHeight;
        const userCardHeight  = userCard ? userCard.offsetHeight : 0;
        const disponible      = viewportHeight - brandHeight - userCardHeight;

        scrollArea.style.height    = `${Math.max(disponible, 120)}px`;
        scrollArea.style.maxHeight = `${Math.max(disponible, 120)}px`;
    }

    (function initSidebarToggle() {
        if (window.innerWidth >= 992) {
            applyCollapse(localStorage.getItem('pgSidebarCollapsed') === '1');
        }

        const btn = document.getElementById('sidebarToggleBtn');
        if (!btn) return;

        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            const collapsed = !document.body.classList.contains('sidebar-collapse');
            applyCollapse(collapsed);
            localStorage.setItem('pgSidebarCollapsed', collapsed ? '1' : '0');

            setTimeout(() => {
                ajustarSidebarScroll();
                window.dispatchEvent(new Event('resize'));
            }, 320);
        }, true);
    })();

    ajustarSidebarScroll();
    window.addEventListener('resize', ajustarSidebarScroll);
    window.addEventListener('load',   ajustarSidebarScroll);

    const pushMenu = document.querySelector('[data-widget="pushmenu"]');
    if (pushMenu) {
        pushMenu.addEventListener('click', function () {
            setTimeout(() => {
                ajustarSidebarScroll();
                window.dispatchEvent(new Event('resize'));
            }, 350);
        });
    }

    const bodyObserver = new MutationObserver(() => {
        setTimeout(ajustarSidebarScroll, 50);
    });

    bodyObserver.observe(document.body, {
        attributes: true,
        attributeFilter: ['class']
    });

    // ── TOPBAR DROPDOWNS ──────────────────────────────────────
    const dropdowns = [
        { btn: 'btnNotif', drop: 'dropNotif' },
        { btn: 'btnMsg',   drop: 'dropMsg'   },
        { btn: 'btnUser',  drop: 'dropUser'  },
    ];

    function closeAll(except) {
        dropdowns.forEach(({ btn, drop }) => {
            const d = document.getElementById(drop);
            const b = document.getElementById(btn);
            if (d && d !== except) {
                d.classList.remove('open');
                if (b) b.classList.remove('open');
            }
        });
        // también quita la clase open del chip usuario si no es el que se abre
        const chip = document.querySelector('.student-user-chip');
        if (chip && except !== document.getElementById('dropUser')) {
            chip.classList.remove('open');
        }
    }

    dropdowns.forEach(({ btn, drop }) => {
        const btnEl  = document.getElementById(btn);
        const dropEl = document.getElementById(drop);
        if (!btnEl || !dropEl) return;

        btnEl.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = dropEl.classList.contains('open');
            closeAll(null);

            if (!isOpen) {
                dropEl.classList.add('open');
                btnEl.classList.add('open');
                // si es el chip de usuario, también añade clase al chip
                if (btn === 'btnUser') {
                    const chip = document.querySelector('.student-user-chip');
                    if (chip) chip.classList.add('open');
                }
            }
        });
    });

    // Cerrar al hacer click fuera
    document.addEventListener('click', function () {
        closeAll(null);
        const chip = document.querySelector('.student-user-chip');
        if (chip) chip.classList.remove('open');
    });

    // Evitar que click dentro del dropdown lo cierre
    document.querySelectorAll('.topbar-dropdown').forEach(drop => {
        drop.addEventListener('click', e => e.stopPropagation());
    });

});