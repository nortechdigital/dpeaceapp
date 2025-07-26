<?php
$token = $_GET['token'] ?? '';

// Validate token
$pdo = new PDO("mysql:host=$host;dbname=$dbname", "$username", "$password");
$stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("Invalid or expired token.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    if ($password !== $confirm) {
        $error = "Passwords don't match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        // Update password and clear token
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        $stmt->execute([$hash, $user['id']]);
        
        echo "Password updated successfully. You can now login.";
        exit;
    }
}
?>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" class="container py-3">
    <div class="mb-3">
        <label class="form-label">New Password:</label>
        <input type="password" name="password" class="form-control" required minlength="8" required>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Confirm Password:</label>
        <input type="password" name="confirm_password" class="form-control" required minlength="8" required>
    </div>
    
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    <button type="submit" class="btn btn-primary">Update Password</button>
</form>