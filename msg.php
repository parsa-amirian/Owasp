<?php
session_start();

require_once 'mysql.php';
require_once 'function.php';


$msg = $_GET['msg'];
$type = $_GET['type'];
$goto = $_GET['goto'] ?? 'index.php';


if($type == 'success'){
    $color = 'green';
}
else{
    $color = 'red';

}


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
            <p class="message" style="color: <?php echo htmlspecialchars($color, ENT_QUOTES, 'UTF-8'); ?>;">
                <?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?>
            </p>
            <?php if (!empty($goto)) : ?>
                <p><a href="<?php echo htmlspecialchars($goto, ENT_QUOTES, 'UTF-8'); ?>">Continue</a></p>
                <script>
                    setTimeout(function () {
                        window.location.href = '<?php echo htmlspecialchars($goto, ENT_QUOTES, 'UTF-8'); ?>';
                    }, 3000);
                </script>
            <?php endif; ?>
        </div> 
</body>
</html>