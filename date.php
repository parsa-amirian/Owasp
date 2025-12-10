//this is a test for giving the date to the user

<?php

$date = date('Y-m-d H:i:s');
echo $date;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Date Test</title>
</head>
<body>
    <h1>Date Test</h1>
    <p><?php echo $date; ?></p>
</body>
</html>