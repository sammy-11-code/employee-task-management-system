<?php
declare(strict_types=1);

require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect_to('dashboard.php');
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf((string) ($_POST['csrf_token'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $statement = $pdo->prepare('SELECT id FROM users WHERE email = :email AND status = \'active\' LIMIT 1');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();
    if ($user) {
        $token = bin2hex(random_bytes(20));
        $hash = hash('sha256', $token);
        $insert = $pdo->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 1 HOUR))');
        $insert->execute(['user_id' => $user['id'], 'token_hash' => $hash]);
        $message = 'A password reset link has been prepared. Please contact the system administrator to complete it.';
    } else {
        $message = 'No active account was found for that email.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot password | ETMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(app_url('assets/css/app.css')) ?>">
</head>
<body class="auth-page">
<main class="auth-shell">
    <section class="auth-brand">
        <span class="brand-mark">E</span>
        <p class="eyebrow">Employee Task Management System</p>
        <h1>Reset access</h1>
        <p class="text-white-50">Enter your work email and we will guide the next step.</p>
    </section>
    <section class="auth-card">
        <h2 class="h4">Forgot your password?</h2>
        <?php if ($message !== ''): ?><div class="alert alert-info"><?= e($message) ?></div><?php endif; ?>
        <form method="post" class="mt-3">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label class="form-label">Work email</label>
            <input class="form-control" type="email" name="email" required>
            <button class="btn btn-primary w-100 mt-3" type="submit">Send reset request</button>
        </form>
        <p class="small text-secondary mt-3 mb-0"><a href="<?= e(app_url('login.php')) ?>">Back to sign in</a></p>
    </section>
</main>
</body>
</html>
