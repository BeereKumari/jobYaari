<?php
$pageTitle = 'Profile';
$breadcrumb = '<a href="/admin/index.php">Home</a> &bull; Profile';
require_once dirname(__DIR__, 1) . '/includes/admin-header.php';

$stmt = $pdo->prepare("SELECT username, email, created_at FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!empty($newPassword)) {
            $adminCheck = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
            $adminCheck->execute([$_SESSION['admin_id']]);
            $adminData = $adminCheck->fetch();

            if (!password_verify($currentPassword, $adminData['password'])) {
                $errors[] = 'Current password is incorrect.';
            } elseif (strlen($newPassword) < 8) {
                $errors[] = 'New password must be at least 8 characters.';
            } elseif ($newPassword !== $confirmPassword) {
                $errors[] = 'New passwords do not match.';
            } else {
                $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                $updateStmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $updateStmt->execute([$hash, $_SESSION['admin_id']]);
                $success = 'Password updated successfully.';
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>

<?php if ($success): ?>
<div class="alert alert-success"><?= escape($success) ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
<div class="alert alert-error">
    <ul style="margin:0; padding-left: 20px;">
        <?php foreach ($errors as $err): ?><li><?= escape($err) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h2>Admin Profile</h2></div>
    <div class="profile-info">
        <div class="profile-row">
            <strong>Username:</strong> <?= escape($admin['username']) ?>
        </div>
        <div class="profile-row">
            <strong>Email:</strong> <?= escape($admin['email']) ?>
        </div>
        <div class="profile-row">
            <strong>Member Since:</strong> <?= formatDate($admin['created_at'], 'd M Y') ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Change Password</h2></div>
    <form method="POST" action="/admin/profile.php" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password">
        </div>
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" minlength="8">
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" minlength="8">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Password</button>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__, 1) . '/includes/admin-footer.php'; ?>
