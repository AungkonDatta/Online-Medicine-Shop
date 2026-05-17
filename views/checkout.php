<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout &mdash; MediShop</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="app-body">

<?php require 'views/_navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">&#128666; Checkout</h1>
            <p class="page-sub">Complete your order in 3 simple steps</p>
        </div>
        <a href="index.php?page=cart" class="btn btn-ghost">&larr; Back to Cart</a>
    </div>

    <!-- Step indicator -->
    <div class="checkout-steps" style="margin-bottom:28px;">
        <div class="step <?= $step === 'address' ? 'active' : ($step !== 'address' ? 'done' : '') ?>">
            <span class="step-num"><?= ($step !== 'address') ? '&#10003;' : '1' ?></span>
            <span>Shipping Address</span>
        </div>
        <div class="step-connector <?= $step !== 'address' ? 'done' : '' ?>"></div>
        <div class="step <?= $step === 'invoice' ? 'active' : ($step === 'payment' ? 'done' : '') ?>">
            <span class="step-num"><?= $step === 'payment' ? '&#10003;' : '2' ?></span>
            <span>Invoice Review</span>
        </div>
        <div class="step-connector <?= $step === 'payment' ? 'done' : '' ?>"></div>
        <div class="step <?= $step === 'payment' ? 'active' : '' ?>">
            <span class="step-num">3</span>
            <span>Payment</span>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= h($error) ?></div>
    <?php endif; ?>

    <?php if ($step === 'address'): ?>
    <!-- ======= STEP 1: Address ======= -->
    <div class="card form-card" style="max-width:560px;">
        <h3 class="card-title">&#128205; Shipping Address</h3>
        <form method="POST" action="index.php?page=checkout&step=address" class="form" novalidate>
            <div class="field">
                <label for="shipping_address">Delivery Address</label>
                <textarea id="shipping_address" name="shipping_address" rows="4"
                          placeholder="Enter your full delivery address"
                          required><?= h($user['address'] ?? '') ?></textarea>
            </div>
            <div class="form-actions">
                <a href="index.php?page=cart" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary" id="addrBtn">Continue to Invoice &rarr;</button>
            </div>
        </form>
    </div>

    <!-- Order summary sidebar -->
    <div class="summary-card" style="max-width:400px;margin-top:16px;">
        <div class="summary-title">Order Items (<?= count($items) ?>)</div>
        <?php foreach ($items as $item): ?>
            <div class="summary-row">
                <span><?= h($item['name']) ?> &times; <?= $item['quantity'] ?></span>
                <span>&#2547; <?= number_format($item['price'] * $item['quantity'], 2) ?></span>
            </div>
        <?php endforeach; ?>
        <div class="summary-total">
            <span>Total</span>
            <span>&#2547; <?= number_format($total, 2) ?></span>
        </div>
    </div>

    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        var addr = document.getElementById('shipping_address').value.trim();
        if (!addr) { e.preventDefault(); alert('Please enter a shipping address.'); }
    });
    </script>

    <?php elseif ($step === 'invoice'): ?>
    <!-- ======= STEP 2: Invoice ======= -->
    <div class="invoice-box" style="max-width:700px;">
        <div class="invoice-header">
            <h3>&#129534; Order Invoice</h3>
            <p>Shipping to: <?= h($_SESSION['checkout_address']) ?></p>
        </div>
        <div style="padding:24px;">
            <table class="data-table" style="margin-bottom:20px;">
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
                    <?php foreach ($items as $i => $item): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= h($item['name']) ?></td>
                        <td><?= h($item['vendor_name']) ?></td>
                        <td class="text-right">&#2547; <?= number_format($item['price'], 2) ?></td>
                        <td class="text-right"><?= $item['quantity'] ?></td>
                        <td class="text-right fw-bold">&#2547; <?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="5" class="text-right fw-bold" style="padding-top:14px;">Grand Total</td>
                        <td class="text-right" style="font-size:16px;font-weight:700;color:var(--primary);padding-top:14px;">
                            &#2547; <?= number_format($total, 2) ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div style="display:flex;gap:12px;justify-content:flex-end;flex-wrap:wrap;">
                <a href="index.php?page=cart" class="btn btn-ghost">Cancel</a>
                <a href="index.php?page=checkout&step=payment" class="btn btn-primary">
                    Confirm &amp; Select Payment &rarr;
                </a>
            </div>
        </div>
    </div>

    <?php elseif ($step === 'payment'): ?>
    <!-- ======= STEP 3: Payment ======= -->
    <div class="card form-card" style="max-width:600px;">
        <h3 class="card-title">&#128179; Select Payment Method</h3>
        <form method="POST" action="index.php?page=checkout&step=payment" class="form" id="paymentForm" novalidate>
            <div class="payment-methods">
                <?php
                $paymentMethods = [
                    ['value' => 'Credit Card',     'icon' => '&#128179;'],
                    ['value' => 'bKash',            'icon' => '&#128242;'],
                    ['value' => 'Nagad',            'icon' => '&#128242;'],
                    ['value' => 'Bank Transfer',    'icon' => '&#127981;'],
                    ['value' => 'Cash on Delivery', 'icon' => '&#128176;'],
                ];
                foreach ($paymentMethods as $pm):
                ?>
                <label class="payment-opt" id="opt_<?= h(str_replace(' ','_',$pm['value'])) ?>">
                    <input type="radio" name="payment_method"
                           value="<?= h($pm['value']) ?>"
                           onchange="markSelected(this)">
                    <?= $pm['icon'] ?> <?= h($pm['value']) ?>
                </label>
                <?php endforeach; ?>
            </div>
            <div style="background:var(--bg);border:1px solid var(--border);border-radius:var(--radius-sm);padding:14px;margin-top:12px;">
                <strong style="font-size:14px;">Order Total: </strong>
                <span style="color:var(--primary);font-weight:700;font-size:18px;">&#2547; <?= number_format($total, 2) ?></span>
                <br><span style="font-size:13px;color:var(--text-muted);">Shipping: <?= h($_SESSION['checkout_address']) ?></span>
            </div>
            <div class="form-actions">
                <a href="index.php?page=checkout&step=invoice" class="btn btn-ghost">Back</a>
                <button type="submit" class="btn btn-primary" id="placeOrderBtn">
                    &#9989; Place Order
                </button>
            </div>
        </form>
    </div>

    <script>
    function markSelected(radio) {
        document.querySelectorAll('.payment-opt').forEach(function(l) { l.classList.remove('selected'); });
        radio.closest('.payment-opt').classList.add('selected');
    }
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        var selected = document.querySelector('input[name="payment_method"]:checked');
        if (!selected) {
            e.preventDefault();
            alert('Please select a payment method.');
        }
    });
    </script>
    <?php endif; ?>

</main>

<footer class="footer">&copy; <?= date('Y') ?> MediShop</footer>
</body>
</html>
