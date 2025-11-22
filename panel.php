<?php
//var_dump($_SESSION);
session_start();
session_regenerate_id(true);
error_reporting(E_ALL);
ini_set('display_errors', 1);


if (empty($_SESSION['username'])) {
    header('Location: msg.php?msg=You are not logged in&type=error&goto=login.php');
    exit();
} else {
?>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Panel</title>
    <link rel="stylesheet" href="statics/style.css" />
</head>
<body>
    <div class="container">
        <h1>Panel</h1>
        <p class="muted">you are logged in</p>
        <p class="welcome">welcome to the panel, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
    </div>   
</body>    
<?php
}
?>
