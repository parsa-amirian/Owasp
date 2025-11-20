<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);



if (!isset($_SESSION['login'])) {
    header('Location: msg.php?msg=You are not logged in&type=error&goto=login.php');
    exit();
}

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Panel | World.com</title>
    <link rel="stylesheet" href="statics/style.css" />
</head>
<body>
    <main class="card panel-card">
        <h2>Welcome to the panel</h2>
        <p>You are logged in as <strong><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></strong>.</p>
        <p class="note">Need to do something else? <a href="index.php">Return to home</a>.</p>
    </main>
</body>
</html>