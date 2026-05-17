<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard &mdash; MediShop</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="app-body">

<?php require 'views/_admin_navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">&#128202; Dashboard</h1>
            <p class="page-sub">Welcome back, <?= h($_SESSION['user']['name']) ?>!</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">&#128138;</div>
            <div>
                <div class="stat-value"><?= $totalMedicines ?></div>
                <div class="stat-label">Total Medicines</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue">&#128100;</div>
            <div>
                <div class="stat-value"><?= $totalCustomers ?></div>
                <div class="stat-label">Registered Customers</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">&#128666;</div>
            <div>
                <div class="stat-value"><?= $totalOrders ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red">&#9203;</div>
            <div>
                <div class="stat-value"><?= $pendingOrders ?></div>
                <div class="stat-label">Pending Orders</div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card">
        <div class="card-toolbar" style="justify-content:space-between;">
            <span style="font-weight:700;font-size:16px;">Recent Orders</span>
            <a href="index.php?page=admin_orders" class="btn-sm btn-edit">View All</a>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                        <tr><td colspan="7" class="empty">No orders yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $o): ?>
                        <tr>
                            <td>#<?= $o['id'] ?></td>
                            <td><?= h($o['customer_name']) ?></td>
                            <td><strong>&#2547; <?= number_format($o['total_amount'], 2) ?></strong></td>
                            <td><?= h($o['payment_method']) ?></td>
                            <td><?= date('d M Y', strtotime($o['order_date'])) ?></td>
                            <td><span class="status-<?= h($o['status']) ?>"><?= ucfirst(h($o['status'])) ?></span></td>
                            <td class="text-right">
                                <a class="btn-sm btn-edit" href="index.php?page=order_detail&id=<?= $o['id'] ?>">View</a>
                                <?php if ($o['status'] === 'pending'): ?>
                                <a class="btn-sm btn-accept"
                                   href="index.php?page=admin_orders&action=accept&id=<?= $o['id'] ?>"
                                   onclick="return confirm('Accept order #<?= $o['id'] ?>?')">Accept</a>
                                <a class="btn-sm btn-reject"
                                   href="index.php?page=admin_orders&action=reject&id=<?= $o['id'] ?>"
                                   onclick="return confirm('Reject order #<?= $o['id'] ?>?')">Reject</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick links -->
    <div style="display:flex;gap:12px;flex-wrap:wrap;">
        <a href="index.php?page=admin_medicines&action=add"  class="btn btn-primary">&#43; Add Medicine</a>
        <a href="index.php?page=admin_categories&action=add" class="btn btn-ghost">&#43; Add Category</a>
        <a href="index.php?page=admin_orders"                class="btn btn-ghost">&#128666; Manage Orders</a>
    </div>
</main>

<footer class="footer">&copy; <?= date('Y') ?> MediShop Admin Panel</footer>
</body>
</html>
