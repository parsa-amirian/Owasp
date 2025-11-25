<?php
session_start();

require_once 'mysql.php';
require_once 'function.php';


$msg = $_GET['msg'];
$type = $_GET['type'];
$goto = $_GET['goto'] ?? 'index.php';


$normalizedType = strtolower($type);
$isSuccess = in_array($normalizedType, ['success', 'succsess'], true);
$messageClass = $isSuccess ? 'message message-success' : 'message message-error';


?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset = "utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Message | World.com</title>
        <link rel="stylesheet" href="statics/style.css" />

    </head>
    <body>
        <div class="container">
            <h1>Message</h1>
            <p class="<?php echo $messageClass; ?>"> <?php echo htmlspecialchars($msg); ?></p>
            <p class="muted"> Redirecting to <?php echo htmlspecialchars($goto); ?> In 3 Seconds...</p>
            <script>
                const params = new URLSearchParams(window.location.search);
                const goto = params.get("goto")
                setTimeout(function () {
                    window.location.href = goto || 'index.php ' ;
                    }, 3000);
            </script>
            
        </div> 
</body>
</html>