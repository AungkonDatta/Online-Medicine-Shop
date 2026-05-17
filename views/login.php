<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login &mdash; Online Medicine Shop</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">

<div class="auth-shell">
    <div class="auth-side">
        <div class="logo-big">&#128138;</div>
        <h1>Online Medicine Shop</h1>
        <p>Your trusted platform for medicines delivered to your doorstep.</p>
        <ul class="feature-list">
            <li>&#10003; Browse 100s of medicines</li>
            <li>&#10003; Filter by vendor &amp; type</li>
            <li>&#10003; Easy cart &amp; checkout</li>
            <li>&#10003; Track your orders</li>
        </ul>
    </div>

    <div class="auth-form-wrap">
        <div class="auth-card">
            <h2>Welcome Back</h2>
            <p class="muted">Sign in to your account</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=login" class="form" novalidate>
                <div class="field">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           placeholder="you@example.com" required autofocus>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Enter password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>

            <p class="auth-foot">New customer?
                <a href="index.php?page=register">Create an account</a>
            </p>
            <p class="hint">
                <strong>Admin:</strong> admin@medicine.com / admin123
            </p>
        </div>
    </div>
</div>

</body>
</html>
