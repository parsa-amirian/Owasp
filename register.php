

<?php

require_once 'mysql.php'; 

if (isset($_POST['submit']))
{
    // Check if invitation code is valid and unused
    $invitation_code = $_POST['invitation_code'];
    $username = $_POST['username'];
    $name = $_POST['name'];
    $stmt = $conn->prepare("SELECT * FROM invitation_codes WHERE invitation_code = ? AND used = 0");
    $stmt->bind_param("s", $invitation_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Invalid invitation code.<br>";
        exit;
    }

    $row = $result->fetch_assoc();
    if ($row['used']) {
        echo "Invitation code has already been used.<br>";
        exit;
    }
    /////
    //check if the email is correct
    $email = $_POST['email'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email address.<br>";
        exit;
    }
//
//check if the email is not already registered
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo "Email already registered.<br>";
    exit;
}
///
//check if the username is not already registered
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo "Username already registered.<br>";
    exit;
}
///
//hash the password
$password = $_POST['password'];
//$password = password_hash($password, PASSWORD_DEFAULT);
///
//insert the user into the database
$stmt = $conn->prepare("INSERT INTO users (name, username, email, password, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
$stmt->bind_param("ssss", $name, $username, $email, $password);
$stmt->execute();
//$result = $stmt->get_result();

if($stmt->affected_rows > 0)
{
    print ("User registered successfully");
    // Set the used invitation code to 1 after successful registration

    echo "<br>You will be redirected in 3 seconds...";
    echo "<script>setTimeout(function(){ window.location.href = 'login.php'; }, 3000);</script>";

    
    $update_stmt = $conn->prepare("UPDATE invitation_codes SET used = 1 WHERE invitation_code = ?");
    $update_stmt->bind_param("s", $_POST['invitation_code']);
    $update_stmt->execute();
    exit();

}
else
    print("Failed to regester user.");
    exit();

//// 

    echo "Name: " . $_POST['name']. "<br>";
    echo "Username: " . $_POST['username']. "<br>";
    echo "Email: " . $_POST['email']. "<br>";
    echo "Password: " . $_POST['password']. "<br>";
    echo "Invitation Code: " . $_POST['invitation_code']. "<br>";
    echo "register successful";
}
else
{

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register | World.com</title>
    <link rel="stylesheet" href="statics/style.css" />
</head>
<body>
    <main class="card">
        <h1>Register</h1>
        <p>Please fill in the form below to get started.</p>
        <form method="post" action="register.php" accept-charset="utf-8" novalidate>
            <div class="field">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" placeholder="Username" required aria-required="true" autocomplete="username" maxlength="100" />
            </div>
            <div class="field">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" placeholder="Full name" required aria-required="true" autocomplete="name" maxlength="100" />
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" placeholder="you@example.com" required aria-required="true" autocomplete="email" maxlength="100" />
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Password" required aria-required="true" autocomplete="new-password" maxlength="100" />
            </div>
            <div class="field">
                <label for="invitation_code">Invitation Code</label>
                <input id="invitation_code" name="invitation_code" type="text" placeholder="INV123456" required aria-required="true" autocomplete="one-time-code" maxlength="100" />
            </div>
            <div class="actions">
                <button type="submit" name="submit">Register</button>
            </div>
        </form>
        <p class="note">Already registered? <a href="login.php">Login instead</a>.</p>
    </main>
</body>
</html>
<?php
}