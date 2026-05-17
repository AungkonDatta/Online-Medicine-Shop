<?php $u = $_SESSION['user']; ?>
<header class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php?page=admin">
            <span class="brand-icon">&#128138;</span>
            <span>MediShop Admin</span>
        </a>
        <nav class="nav-links">
            <a href="index.php?page=admin"            <?= (($_GET['page'] ?? '') === 'admin')            ? 'class="active"' : '' ?>>Dashboard</a>
            <a href="index.php?page=admin_medicines"  <?= (($_GET['page'] ?? '') === 'admin_medicines')  ? 'class="active"' : '' ?>>Medicines</a>
            <a href="index.php?page=admin_categories" <?= (($_GET['page'] ?? '') === 'admin_categories') ? 'class="active"' : '' ?>>Categories</a>
            <a href="index.php?page=admin_customers"  <?= (($_GET['page'] ?? '') === 'admin_customers')  ? 'class="active"' : '' ?>>Customers</a>
            <a href="index.php?page=admin_orders"     <?= (($_GET['page'] ?? '') === 'admin_orders')     ? 'class="active"' : '' ?>>Orders</a>
        </nav>
        <div class="nav-user">
            <span class="user-pill">
                <span class="user-avatar"><?= strtoupper(substr($u['name'], 0, 1)) ?></span>
                <span class="user-meta">
                    <span class="user-name"><?= h($u['name']) ?></span>
                    <span class="user-role">Admin</span>
                </span>
            </span>
            <a href="index.php?page=logout" class="btn-logout">Logout</a>
        </div>
    </div>
</header>
