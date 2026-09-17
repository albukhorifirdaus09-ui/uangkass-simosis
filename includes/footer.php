<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebars = [
        { sidebar: document.getElementById('sidebar'), toggle: document.getElementById('menuToggle'), overlay: document.getElementById('sidebarOverlay') },
        { sidebar: document.getElementById('classSidebar'), toggle: document.getElementById('classMenuToggle'), overlay: document.getElementById('classSidebarOverlay') },
        { sidebar: document.getElementById('osisSidebar'), toggle: document.getElementById('osisMenuToggle'), overlay: document.getElementById('osisSidebarOverlay') }
    ];

    sidebars.forEach(({ sidebar, toggle, overlay }) => {
        if (!sidebar) return;

        if (toggle) {
            toggle.addEventListener('click', function () {
                sidebar.classList.toggle('show');
                if (overlay) overlay.classList.toggle('show');
            });
        }

        if (overlay) {
            overlay.addEventListener('click', function () {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }

        sidebar.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 991) {
                    sidebar.classList.remove('show');
                    if (overlay) overlay.classList.remove('show');
                }
            });
        });
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 991) {
            sidebars.forEach(({ sidebar, overlay }) => {
                if (sidebar) sidebar.classList.remove('show');
                if (overlay) overlay.classList.remove('show');
            });
        }
    });
});
</script>

</body>
</html>