<?php
require_once 'mysql.php';
require_once 'function.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);


if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];

    // Look up the token in the database (assuming 'users' table has reset_token, and reset_token_expires fields)
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token IS NOT NULL");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Invalid or expired reset token.<br>";
        exit();
    } else {
        $user = $result->fetch_assoc();
        // Check if token is expired (assuming reset_token_expires holds expiry date)
        if (isset($user['reset_token_expires'])) {
            if (strtotime($user['reset_token_expires']) < time()) {
                echo "Reset token has expired.<br>";
                exit();
            }
        }
        // Token is valid; you can now allow the user to reset their password
        echo "Reset token is valid. You may now reset your password.<br>";
        // Additional logic for password reset can be included here
    }
}


if (isset($_POST['submit'])) {
    $username = trim($_POST['username'] ?? '');
    
    if ($username === '') {
        echo "Username is required.<br>";
        exit();
    }
    
    // Check if username exists in database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "Username not found in our system.<br>";
        echo "<a href='forget_pass.php'>Try again</a> | <a href='login.php'>Back to login</a>";
        exit();
    }
    
    // Email exists - generate reset token
    $reset_token = random_token();
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
    
    // Store reset token in database (assuming you have a password_resets table)
    
    $update_reset_token_sql = "UPDATE users SET reset_token = ? WHERE username = ?";
    $stmt = $conn->prepare($update_reset_token_sql);
    $stmt->bind_param("ss", $reset_token, $username) ;
    $stmt->execute();

    if($stmt->affected_rows > 0)
        {
            print('reset token send to email<br>');
            $reset_token_url = "http://world.com/reset_pass.php?token=". $reset_token;
            print($reset_token_url);
            print("<br>");
            print('as we do not have a real domain we think the link have been sent to the user :/<br>');
            print($reset_token);
            echo "<script>setTimeout(function(){ window.location.href = '" . htmlspecialchars($reset_token_url, ENT_QUOTES) . "'; }, 3000);</script>";
            exit();
        
        }
    else 
        {
            print('faild to send reset token');
            exit();
        }

    // For now, we'll just show a success message
    // In production, you would:
    // 1. Create a password_resets table with columns: email, token, expires_at, created_at
    // 2. Insert the token into the database
    // 3. Send an email with the reset link
    
    echo "Password reset instructions have been sent to the email associated with your username.<br>";
    echo "Please check your inbox and follow the instructions to reset your password.<br><br>";
    echo "<a href='login.php'>Back to login</a>";
    exit();
}
else {
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Forgot Password | World.com</title>
    <link rel="stylesheet" href="statics/style.css" />
</head>
<body>
    <main class="card">
        <h1>Forgot Password</h1>
        <p>Enter your username to reset your password.</p>
        <form method="post" action="forget_pass.php" accept-charset="utf-8" novalidate>
            <div class="field">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" placeholder="yourusername" required aria-required="true" autocomplete="username" maxlength="100" />
            </div>
            <div class="actions">
                <button type="submit" name="submit">Reset Password</button>
            </div>
        </form>
        <p class="note">Remembered now? <a href="login.php">Back to login</a>.</p>
    </main>
</body>
</html>
<?php
}
?>