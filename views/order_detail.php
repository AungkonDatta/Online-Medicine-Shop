<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order #<?= $order['id'] ?> &mdash; MediShop</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="app-body">

<?php
if (isAdmin()) {
    require 'views/_admin_navbar.php';
} else {
    require 'views/_navbar.php';
}
?>

<main class="main-content" style="max-width:800px;">
    <p style="margin-bottom:16px;">
        <?php if (isAdmin()): ?>
            <a href="index.php?page=admin_orders">&larr; Back to All Orders</a>
        <?php else: ?>
            <a href="index.php?page=my_orders">&larr; Back to My Orders</a>
        <?php endif; ?>
    </p>

    <div class="page-header">
        <div>
            <h1 class="page-title">Order #<?= $order['id'] ?></h1>
            <p class="page-sub">Placed on <?= date('d M Y, h:i A', strtotime($order['order_date'])) ?></p>
        </div>
        <span class="status-<?= h($order['status']) ?>" style="font-size:14px;padding:8px 16px;">
            <?= ucfirst(h($order['status'])) ?>
        </span>
    </div>

    <!-- Meta info -->
    <div class="card form-card" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
        <div>
            <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;font-weight:600;margin-bottom:4px;">Customer</div>
            <div style="font-weight:600;"><?= h($customer['name']) ?></div>
            <div style="font-size:13px;color:var(--text-muted);"><?= h($customer['email']) ?></div>
        </div>
        <div>
            <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;font-weight:600;margin-bottom:4px;">Payment Method</div>
            <div style="font-weight:600;"><?= h($order['payment_method']) ?></div>
        </div>
        <div style="grid-column:1/-1;">
            <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;font-weight:600;margin-bottom:4px;">Shipping Address</div>
            <div style="font-weight:600;"><?= h($order['shipping_address']) ?></div>
        </div>
    </div>

    <!-- Order items table -->
    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Medicine</th>
                        <th>Vendor</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $i => $item): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= h($item['medicine_name']) ?></td>
                        <td><?= h($item['vendor_name']) ?></td>
                        <td class="text-right">&#2547; <?= number_format($item['unit_price'], 2) ?></td>
                        <td class="text-right"><?= $item['quantity'] ?></td>
                        <td class="text-right fw-bold">&#2547; <?= number_format($item['unit_price'] * $item['quantity'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="5" class="text-right fw-bold" style="font-size:15px;">Grand Total</td>
                        <td class="text-right" style="font-size:17px;font-weight:700;color:var(--primary);">
                            &#2547; <?= number_format($order['total_amount'], 2) ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (isAdmin() && $order['status'] === 'pending'): ?>
    <div style="display:flex;gap:12px;margin-top:8px;">
        <a href="index.php?page=admin_orders&action=accept&id=<?= $order['id'] ?>"
           class="btn btn-primary"
           onclick="return confirm('Accept this order?')">
            &#9989; Accept Order
        </a>
        <a href="index.php?page=admin_orders&action=reject&id=<?= $order['id'] ?>"
           class="btn btn-danger"
           onclick="return confirm('Reject this order?')">
            &#10060; Reject Order
        </a>
    </div>
    <?php endif; ?>

</main>

<footer class="footer">&copy; <?= date('Y') ?> MediShop &mdash; Group 8 Online Medicine Shop</footer>
</body>
</html>
