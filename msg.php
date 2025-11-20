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

<html>
    <head>
        <meta charset = "utf-8">
        <meta name="viewpoint" content="width=device-width, initial-scale=1> 
    </head>
</html>