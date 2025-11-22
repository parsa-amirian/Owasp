<?php
require_once 'mysql.php'; 
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

function get_ip_address(){
    return $_SERVER['REMOTE_ADDR']; 
}

if(isset($_POST['submit'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Default login status
    $login_status = 0;

    if ($result->num_rows == 0) {
        $msg = "User not found";
        
        // Log
        $ip_address = get_ip_address();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $conn->query("INSERT INTO login_logs (ip_address, user_agent, referer, login_status, username, created_at)
        VALUES ('{$ip_address}','{$conn->real_escape_string($user_agent)}',
        '{$conn->real_escape_string($referer)}', '0', '{$conn->real_escape_string($username)}', NOW())");

    } else {

        $user = $result->fetch_assoc();

        if ($user['password'] !== $password) {
            $msg = "Password incorrect";
            
            // Log
            $ip_address = get_ip_address();
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $referer = $_SERVER['HTTP_REFERER'] ?? '';
            $conn->query("INSERT INTO login_logs (ip_address, user_agent, referer, login_status, username, created_at)
            VALUES ('{$ip_address}','{$conn->real_escape_string($user_agent)}',
            '{$conn->real_escape_string($referer)}', '0', '{$conn->real_escape_string($username)}', NOW())");

        } else {

            // Success
            $_SESSION['username'] = $user['username'];
            $_SESSION['user-id'] = $user['id'];
            $_SESSION['login'] = true;
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['name'];
            $login_status = 1;

            // Log
            $ip_address = get_ip_address();
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $referer = $_SERVER['HTTP_REFERER'] ?? '';
            $conn->query("INSERT INTO login_logs (ip_address, user_agent, referer, login_status, username, created_at)
            VALUES ('{$ip_address}','{$conn->real_escape_string($user_agent)}',
            '{$conn->real_escape_string($referer)}', '1', '{$conn->real_escape_string($username)}', NOW())");

            //echo "Login successful. Redirecting to panel...<br>";
            //echo "<script>setTimeout(function(){ window.location.href = 'panel.php'; }, 1000);</script>";
            //exit();
            header('Location: msg.php?msg=Login successful&type=succsess&goto=panel.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login | World.com</title>
    <link rel="stylesheet" href="statics/style.css" />
</head>
<body>
    <main class="card">
        <h1>Login</h1>
        <p>Please enter your credentials to continue.</p>
        <form method="post" action="login.php" accept-charset="utf-8" novalidate>
            <div class="field">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" placeholder="Username" required aria-required="true" autocomplete="username" maxlength="100" />
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Password" required aria-required="true" autocomplete="current-password" maxlength="100" />
            </div>
            <div class="actions">
                <button type="submit" name="submit">Login</button>
            </div>
        </form>
        <?php if(isset($msg) && !empty($msg)): ?>
        <p class="message" style="color:red;"><?php echo htmlspecialchars($msg); ?></p>
        <?php endif; ?>
        <p class="note">Don't have an account? <a href="register.php">Register here</a>.</p>
    </main>
</body>
</html>
