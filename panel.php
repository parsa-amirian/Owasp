<?php
//var_dump($_SESSION);
session_start();
session_regenerate_id(true);
error_reporting(E_ALL);
ini_set('display_errors', 1);


if (!isset($_SESSION['username'])) {
    header('Location: msg.php?msg=You are not logged in&type=error&goto=login.php');
    exit();
} else {
require_once 'mysql.php';

$userid = $_SESSION['userid'];

$userData = null;
if ($userid !== null) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $userData = $result->fetch_assoc();
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Panel</title>
    <link rel="stylesheet" href="statics/style.css" />
</head>
<body class="panel-body">
    <div class="panel-wrapper">
        <header class="panel-header">
            <p class="panel-badge">Account Center</p>
            <h1>Manage your profile</h1>
            <p class="muted">Signed in as <?php echo htmlspecialchars($userData['email']); ?></p>
            <p class="welcome">Welcome back, <?php echo htmlspecialchars($userData['name']); ?>.</p>
        </header>

        <div class="panel-grid">
            <section class="panel-card panel-form">
                <div class="panel-card-header">
                    <div>
                        <p class="panel-label">Profile</p>
                        <h2>Edit your information</h2>
                    </div>
                    <p class="panel-helper">Update your contact details or refresh your password.</p>
                </div>

                <form action="update_user.php" method="post">
                    <div class="field">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($userData['name']); ?>" required />
                    </div>

                    <div class="field">
                        <label for="email">Email</label>
                        <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" readonly />
                        <span class="field-hint">Need to change your email? Contact support.</span>
                    </div>

                    <div class="field">
                        <label for="password">New password</label>
                        <input type="password" id="password" name="password" placeholder="Leave blank to keep current password" />
                    </div>

                    <div class="actions">
                        <button type="submit">Save changes</button>
                    </div>
                </form>
            </section>

            <section class="panel-card panel-status">
                <div class="panel-card-header">
                    <div>
                        <h2>Session overview</h2>
                    </div>
                    <span class="status-pill">Active</span>
                </div>

                <dl>
                    <div>
                        <dt>User</dt>
                        <dd><?php echo htmlspecialchars($userData['name']); ?></dd>
                    </div>
                    <div>
                        <dt>Email</dt>
                        <dd><?php echo htmlspecialchars($userData['email']); ?></dd>
                    </div>
                    
                </dl>
                <p class="muted">You are logged in and can update your profile details anytime.</p>
            </section>
        </div>
    </div>
</body>
</html>
<?php
}
?>
