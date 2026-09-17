<header class="topbar">

    <div class="topbar-left">

        <!-- TOMBOL DRAWER -->
        <button
            type="button"
            class="menu-toggle"
            id="menuToggle"
            aria-label="Buka menu"
        >
            <i class="bi bi-list"></i>
        </button>


        <div class="page-heading">

            <h5>
                <?= htmlspecialchars($pageTitle ?? 'Dashboard'); ?>
            </h5>

            <span>
                Panel Admin / Pembina
            </span>

        </div>

    </div>


    <!-- USER -->
    <div class="user-area">

        <div class="user-avatar">
            <i class="bi bi-person-fill"></i>
        </div>

        <div class="user-info">

            <strong>
                <?= htmlspecialchars(
                    $_SESSION['nama']
                    ?? $_SESSION['nama_lengkap']
                    ?? $_SESSION['username']
                    ?? 'Admin'
                ); ?>
            </strong>

            <small>
                <?= htmlspecialchars(
                    ucfirst($_SESSION['role'] ?? 'admin')
                ); ?>
            </small>

        </div>

    </div>

</header>