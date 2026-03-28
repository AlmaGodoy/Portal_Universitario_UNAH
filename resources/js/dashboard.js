document.addEventListener('DOMContentLoaded', function () {
    function applyCollapse(collapsed) {
        if (collapsed) {
            document.body.classList.add('sidebar-collapse');
        } else {
            document.body.classList.remove('sidebar-collapse');
        }
    }

    function ajustarSidebarScroll() {
        const aside = document.querySelector('.main-sidebar');
        const brand = document.querySelector('.main-sidebar .brand-link');
        const userCard = document.querySelector('.sidebar-user-card');
        const scrollArea = document.getElementById('dashboardSidebarScroll');

        if (!aside || !brand || !scrollArea) return;

        const viewportHeight = window.innerHeight;
        const brandHeight = brand.offsetHeight;
        const userCardHeight = userCard ? userCard.offsetHeight : 0;
        const disponible = viewportHeight - brandHeight - userCardHeight;

        scrollArea.style.height = `${Math.max(disponible, 120)}px`;
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
    window.addEventListener('load', ajustarSidebarScroll);

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
});
