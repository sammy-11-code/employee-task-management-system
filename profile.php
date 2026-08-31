<?php
declare(strict_types=1);

require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/layout.php';
require_login();

$user = current_user();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf((string) ($_POST['csrf_token'] ?? ''));
    $newPassword = (string) ($_POST['password'] ?? '');
    if ($newPassword !== '') {
        if (strlen($newPassword) < 8 || !preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/\d/', $newPassword)) {
            $message = 'Password must be 8+ chars, include upper/lowercase and a number.';
        } else {
            $statement = $pdo->prepare('UPDATE users SET password_hash = :password WHERE id = :id');
            $statement->execute(['password' => password_hash($newPassword, PASSWORD_DEFAULT), 'id' => $user['id']]);
            $message = 'Password updated successfully.';
        }
    }
}

page_header('Profile');
?>
<div class="panel form-panel">
    <div class="panel-heading">
        <div>
            <p class="eyebrow mb-1">Account</p>
            <h2 class="h4 mb-0">My profile</h2>
        </div>
    </div>
    <?php if ($message !== ''): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Name</label>
            <input class="form-control" value="<?= e($user['first_name'] . ' ' . $user['last_name']) ?>" disabled>
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input class="form-control" value="<?= e($user['email']) ?>" disabled>
        </div>
        <div class="col-12">
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <label class="form-label">New password</label>
                <input class="form-control" type="password" name="password" autocomplete="new-password">
                <button class="btn btn-primary mt-3" type="submit">Update password</button>
            </form>
        </div>
    </div>
</div>
<?php page_footer(); ?>
