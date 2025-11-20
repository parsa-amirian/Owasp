
<?php
require_once 'mysql.php'; 
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

function get_ip_address(){
    return $_SERVER['REMOTE_ADDR']; 
}

/*

CREATE TABLE login_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT ,
    referer TEXT ,
    login_status SMALLINT,
    username varchar(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


*/

if(isset($_POST['submit'])) {

$username = $_POST['username'];
$password = $_POST['password'];

// Check if user exists
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "User not found.<br>";
    $login_status = 0;
    exit();
}

$user = $result->fetch_assoc();

if ($user['password'] !== $password) {
    echo "Password incorrect.<br>";
    $login_status = 0;
    exit();
}

// User and password correct
$_SESSION['username'] = $username;
echo "Login successful. Redirecting to panel...<br>";
$login_status = 1;
echo "<script>setTimeout(function(){ window.location.href = 'panel.php'; }, 1000);</script>";


//$login_status = 0;
//$insert_login = "insert into login_logs (ip_address, user_agent, referer, login_status, username, created_at) values (?,?,?,?,?,now())";
/*
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$login_status = 1; // You can define codes as needed. Here 1 for successful login.

$log_stmt = $conn->prepare("INSERT INTO login_logs (ip_address, user_agent, referer, login_status, username) VALUES (?, ?, ?, ?, ?)");
$log_stmt->bind_param("sssds", $ip_address, $user_agent, $referer, $login_status, $username);
$log_stmt->execute();
*/

//log the login attempt

//$ip_address = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
$ip_address = get_ip_address();
$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
//$login_status_val = int($login_status);

$insert_login_sql = "INSERT INTO login_logs (ip_address, user_agent, referer,
 login_status, username, created_at) VALUES ('"
    . $ip_address . "', '"
    . $conn->real_escape_string($user_agent) . "', '"
    . $conn->real_escape_string($referer) . "', '"
    . $login_status . "', '"
    . $conn->real_escape_string($username) . "', NOW())";

$conn->query($insert_login_sql);

/*
$insert_login_log_sql = "INSERT INTO login_logs (ip_address, user_agent,
referer, login_status, username, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
$stmt = $connection->prepare ($insert_login_log_sql);
$stmt->bind_param ("ssssss", $ip_address, $user_agent, $referer, $login_status, $username, $created_at) ;
$stmt->execute ();
exit();
*/
 

}
else {
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
        <p class="note">Don't have an account? <a href="register.php">Register here</a>.</p>
    </main>
</body>
</html>
<?php
}