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
            <p class="message" style="color: <?php echo $color; ?>;"> <?php echo $msg; ?></p>
            
            <script>
                setTimeout(function () {
                    window.location.href = '<?php echo $goto; ?>';
                    }, 3000);
            </script>
            
        </div> 
</body>
</html>