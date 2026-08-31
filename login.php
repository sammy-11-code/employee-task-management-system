<?php
declare(strict_types=1);

require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect_to('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf((string) ($_POST['csrf_token'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $statement = $pdo->prepare('SELECT * FROM users WHERE email = :email AND status = \'active\' LIMIT 1');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        $update = $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $update->execute(['id' => $user['id']]);
        header('Location: dashboard.php');
        exit;
    }
    $error = 'The email or password is incorrect.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in | ETMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(app_url('assets/css/app.css')) ?>">
</head>
<body class="auth-page">
<main class="auth-shell">
    <section class="auth-brand">
        <div class="auth-pill">Employee Task Management System</div>
        <h1>Keep every task visible, assigned, and on time.</h1>
        <p>ETMS brings administrators, managers, and employees into one secure workspace for planning, progress updates, and accountability.</p>
        <ul class="auth-highlights">
            <li>Secure role-based access</li>
            <li>Live task visibility across teams</li>
            <li>Clear ownership and deadlines</li>
        </ul>
    </section>
    <section class="auth-card">
        <div class="auth-card__top">
            <div class="brand-mark">E</div>
            <div>
                <p class="eyebrow text-primary">Welcome back</p>
                <h2>Sign in to ETMS</h2>
                <p class="text-secondary mb-0">Use your work account to continue.</p>
            </div>
        </div>
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" novalidate class="mt-4">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="mb-3">
                <label class="form-label" for="email">Work email</label>
                <input class="form-control form-control-lg" type="email" id="email" name="email" required autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input class="form-control form-control-lg" type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <label class="form-check-label small text-secondary" for="rememberMe">
                    <input class="form-check-input me-2" type="checkbox" id="rememberMe">
                    Remember me
                </label>
                <a href="<?= e(app_url('forgot_password.php')) ?>" class="small">Forgot password?</a>
            </div>
            <button class="btn btn-primary btn-lg w-100" type="submit">Sign in</button>
        </form>
        <p class="small text-secondary mt-4 mb-0">Need access? Contact your system administrator.</p>
    </section>
</main>
</body>
</html>