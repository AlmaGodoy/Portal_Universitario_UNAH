document.addEventListener('DOMContentLoaded', function () {
    const root = document.documentElement;
    const body = document.body;

    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const btnDown = document.getElementById('sidebarSizeDown');
    const btnReset = document.getElementById('sidebarSizeReset');
    const btnUp = document.getElementById('sidebarSizeUp');

    const STORAGE_WIDTH_KEY = 'student_sidebar_width';
    const STORAGE_COLLAPSE_KEY = 'student_sidebar_collapsed';

    const MIN_WIDTH = 240;
    const MAX_WIDTH = 380;
    const DEFAULT_WIDTH = 290;
    const STEP = 20;

    function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }

    function applyWidth(width) {
        const safeWidth = clamp(width, MIN_WIDTH, MAX_WIDTH);
        root.style.setProperty('--student-sidebar-width', safeWidth + 'px');
    }

    function getSavedWidth() {
        const saved = parseInt(localStorage.getItem(STORAGE_WIDTH_KEY), 10);
        return Number.isFinite(saved) ? clamp(saved, MIN_WIDTH, MAX_WIDTH) : DEFAULT_WIDTH;
    }

    function saveWidth(width) {
        localStorage.setItem(STORAGE_WIDTH_KEY, String(clamp(width, MIN_WIDTH, MAX_WIDTH)));
    }

    function getSavedCollapsedState() {
        return localStorage.getItem(STORAGE_COLLAPSE_KEY) === '1';
    }

    function saveCollapsedState(isCollapsed) {
        localStorage.setItem(STORAGE_COLLAPSE_KEY, isCollapsed ? '1' : '0');
    }

    function applyCollapsedState() {
        body.classList.toggle('sidebar-collapse', getSavedCollapsedState());
    }

    applyWidth(getSavedWidth());
    applyCollapsedState();

    if (btnDown) {
        btnDown.addEventListener('click', function () {
            const currentWidth = getSavedWidth();
            const newWidth = clamp(currentWidth - STEP, MIN_WIDTH, MAX_WIDTH);
            applyWidth(newWidth);
            saveWidth(newWidth);
        });
    }

    if (btnReset) {
        btnReset.addEventListener('click', function () {
            applyWidth(DEFAULT_WIDTH);
            saveWidth(DEFAULT_WIDTH);
        });
    }

    if (btnUp) {
        btnUp.addEventListener('click', function () {
            const currentWidth = getSavedWidth();
            const newWidth = clamp(currentWidth + STEP, MIN_WIDTH, MAX_WIDTH);
            applyWidth(newWidth);
            saveWidth(newWidth);
        });
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            setTimeout(() => {
                const collapsed = body.classList.contains('sidebar-collapse');
                saveCollapsedState(collapsed);
            }, 150);
        });
    }

    window.addEventListener('resize', function () {
        if (window.innerWidth < 992) return;
        applyWidth(getSavedWidth());
    });
});