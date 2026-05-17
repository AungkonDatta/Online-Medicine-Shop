<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register &mdash; Online Medicine Shop</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">

<div class="auth-shell">
    <div class="auth-side">
        <div class="logo-big">&#128138;</div>
        <h1>Join Our Medicine Shop</h1>
        <p>Create a free account and start shopping for medicines with ease.</p>
        <ul class="feature-list">
            <li>&#10003; Fast registration</li>
            <li>&#10003; Secure checkout</li>
            <li>&#10003; Track all your orders</li>
            <li>&#10003; Multiple payment options</li>
        </ul>
    </div>

    <div class="auth-form-wrap">
        <div class="auth-card">
            <h2>Create Account</h2>
            <p class="muted">Register as a customer</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= h($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=register" class="form" novalidate>
                <div class="field">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name"
                           value="<?= h($old['name']) ?>"
                           placeholder="e.g. Rahim Uddin" required>
                </div>
                <div class="field">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?= h($old['email']) ?>"
                           placeholder="you@example.com" required>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone"
                               value="<?= h($old['phone']) ?>"
                               placeholder="+880 1XXXXXXXXX">
                    </div>
                    <div class="field">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address"
                               value="<?= h($old['address']) ?>"
                               placeholder="Your city / area">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password"
                               placeholder="Min 6 chars" required>
                    </div>
                    <div class="field">
                        <label for="confirm">Confirm</label>
                        <input type="password" id="confirm" name="confirm"
                               placeholder="Repeat" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>

            <p class="auth-foot">Already have an account?
                <a href="index.php?page=login">Sign in</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>
