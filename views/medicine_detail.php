<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($med['name']) ?> &mdash; MediShop</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="app-body">

<?php require 'views/_navbar.php'; ?>

<main class="main-content">
    <p style="margin-bottom:16px;">
        <a href="index.php?page=home">&larr; Back to Medicines</a>
    </p>

    <div class="card form-card">
        <div class="med-detail-layout">
            <!-- Image -->
            <div class="med-detail-img">
                <?php if (!empty($med['image_path']) && file_exists($med['image_path'])): ?>
                    <img src="<?= h($med['image_path']) ?>" alt="<?= h($med['name']) ?>">
                <?php else: ?>
                    &#128138;
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div>
                <div class="med-detail-name"><?= h($med['name']) ?></div>
                <div class="med-detail-vendor">&#127981; <?= h($med['vendor_name']) ?></div>
                <div class="detail-meta">
                    <span class="detail-tag">&#128196; <?= h($med['category_name']) ?></span>
                    <span class="detail-tag type-<?= h($med['category_type']) ?> med-card-type">
                        <?= ucfirst(h($med['category_type'])) ?>
                    </span>
                    <?php if ($med['availability'] > 0): ?>
                        <span class="detail-tag" style="color:var(--primary);">
                            &#9989; <?= h($med['availability']) ?> in stock
                        </span>
                    <?php else: ?>
                        <span class="detail-tag out-of-stock">&#10060; Out of stock</span>
                    <?php endif; ?>
                </div>
                <div class="med-detail-price">&#2547; <?= number_format($med['price'], 2) ?></div>

                <?php if (!empty($med['description'])): ?>
                    <p class="med-desc"><?= h($med['description']) ?></p>
                <?php endif; ?>

                <?php if ($med['availability'] > 0): ?>
                    <?php if (isCustomer()): ?>
                        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                            <label style="font-weight:600;font-size:14px;">Quantity:</label>
                            <input type="number" id="detailQty" class="qty-input"
                                   min="1" max="<?= $med['availability'] ?>" value="1"
                                   style="width:72px;">
                            <button class="btn btn-primary"
                                    onclick="addToCartDetail(<?= $med['id'] ?>, <?= $med['availability'] ?>)">
                                &#128722; Add to Cart
                            </button>
                        </div>
                    <?php else: ?>
                        <a href="index.php?page=login" class="btn btn-primary">
                            &#128722; Login to Add to Cart
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<footer class="footer">&copy; <?= date('Y') ?> MediShop &mdash; Group 8 Online Medicine Shop</footer>

<div id="toast" style="position:fixed;bottom:24px;right:24px;background:var(--primary);color:#fff;
     padding:12px 20px;border-radius:var(--radius-sm);font-size:14px;font-weight:600;
     box-shadow:0 4px 20px rgba(0,0,0,.2);display:none;z-index:999;">
</div>

<script>
function showToast(msg, isError) {
    var t = document.getElementById('toast');
    t.textContent = msg;
    t.style.background = isError ? 'var(--error)' : 'var(--primary)';
    t.style.display = 'block';
    setTimeout(function() { t.style.display = 'none'; }, 2500);
}

function addToCartDetail(medicineId, maxStock) {
    var qty = parseInt(document.getElementById('detailQty').value);
    if (isNaN(qty) || qty < 1) { showToast('Enter a valid quantity', true); return; }
    if (qty > maxStock)         { showToast('Only ' + maxStock + ' in stock', true); return; }
    fetch('index.php?page=ajax&type=add_to_cart', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ medicine_id: medicineId, quantity: qty })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.error) { showToast(d.error, true); return; }
        showToast('Added to cart!', false);
        var badge = document.querySelector('.cart-count');
        if (badge) badge.textContent = d.cart_count;
    })
    .catch(function() { showToast('Error', true); });
}
</script>

</body>
</html>
