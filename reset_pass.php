<?php
require_once 'mysql.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$show_form = false; 
$show_error = '';
$show_success = '';
$username = '';

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    // Look up user by token and check if not expired
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token IS NOT NULL");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $show_error = "Invalid or expired reset token.";
    } else {
        $user = $result->fetch_assoc();
        if (isset($user['reset_token_expires']) && strtotime($user['reset_token_expires']) < time()) {
            $show_error = "Reset token has expired.";
        } else {
            $username = $user['username'];
            $show_form = true;
        }
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'])) {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_repeat = $_POST['password_repeat'] ?? '';

    if (empty($token) || empty($password) || empty($password_repeat)) {
        $show_error = "All fields are required.";
    } else if ($password !== $password_repeat) {
        $show_error = "Passwords do not match.";
    } else if (strlen($password) < 2) {
        $show_error = "Password must be at least 2 characters.";
    } else {
        // Find the user with the provided token
        $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token IS NOT NULL");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $show_error = "Invalid or expired reset token.";
        } else {
            $user = $result->fetch_assoc();
            if (isset($user['reset_token_expires']) && strtotime($user['reset_token_expires']) < time()) {
                $show_error = "Reset token has expired.";
            } else {
                // Hash password
                //$password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Update user's password and remove the reset token
                $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE username = ?");
                $stmt->bind_param("ss", $password, $user['username']);
                if($stmt->execute()) {
                    $show_success = "Your password has been reset successfully! <a href='login.php'>Login now</a>.";
                } else {
                    $show_error = "Failed to reset password. Please try again.";
                }
            }
        }
    }
} else {
    $show_error = "No reset token specified.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reset Password | World.com</title>
    <link rel="stylesheet" href="statics/style.css" />
</head>
<body>
    <main class="card">
        <h1>Reset Password</h1>
        <?php if ($show_error): ?>
            <p style="color: red;"><?= htmlspecialchars($show_error) ?></p>
            <p><a href="forget_pass.php">Try password reset again</a> or <a href="login.php">back to login</a>.</p>
        <?php elseif ($show_success): ?>
            <p style="color: green;"><?= $show_success ?></p>
        <?php elseif ($show_form): ?>
            <form method="post" action="reset_pass.php" accept-charset="utf-8" novalidate>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>" />
                <div class="field">
                    <label for="password">New Password</label>
                    <input id="password" name="password" type="password" placeholder="New Password" required autocomplete="new-password" minlength="8" maxlength="100" />
                </div>
                <div class="field">
                    <label for="password_repeat">Repeat New Password</label>
                    <input id="password_repeat" name="password_repeat" type="password" placeholder="Repeat New Password" required autocomplete="new-password" minlength="8" maxlength="100" />
                </div>
                <div class="actions">
                    <button type="submit">Set New Password</button>
                </div>
            </form>
            <p class="note"><a href="login.php">Back to login</a></p>
        <?php endif; ?>
    </main>
</body>
</html>
