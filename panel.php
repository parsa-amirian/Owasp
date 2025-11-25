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

$profileImagePath = 'statics/default-avatar.svg';
if ($userData && !empty($userData['profile_picture'])) {
    $sanitizedProfile = basename($userData['profile_picture']);
    $candidateRelativePath = 'uploads/' . $sanitizedProfile;
    $candidateAbsolutePath = __DIR__ . '/' . $candidateRelativePath;
    if (is_file($candidateAbsolutePath)) {
        $profileImagePath = $candidateRelativePath;
    }
}

$userBio = $userData['bio'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Panel</title>
    <link rel="stylesheet" href="statics/style.css" />
<?php
//// LOG OUT
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Destroy the session
    session_unset();
    session_destroy();

    // Redirect to msg.php with logout message
    header('Location: msg.php?msg=You have been logged out&type=success&goto=login.php');
    exit();
}
?>
</head>
<body class="panel-body">
    <div class="panel-wrapper">
        <header class="panel-header">
            <div class="panel-header-top">
                <p class="panel-badge">Account Center</p>
                <div class="panel-logout-container">
                    <form method="get" action="" class="panel-logout-form">
                        <button type="submit" name="action" value="logout" class="logout-btn" aria-label="Log out of your account">
                            <svg class="logout-btn-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24"><path fill="currentColor" d="M15.75 16.25a.75.75 0 0 1 0 1.06l-1.44 1.44a5 5 0 1 1 0-7.08l1.44 1.44a.75.75 0 0 1-1.06 1.06l-1.44-1.44a3.5 3.5 0 1 0 0 4.96l1.44-1.44a.75.75 0 0 1 1.06 0Z"/><path fill="currentColor" d="M8.22 15.53a.75.75 0 0 1 0-1.06L10.94 12 8.22 9.53a.75.75 0 1 1 1.06-1.06l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0Z"/></svg>
                            Log out
                        </button>
                    </form>
                </div>
            </div>
            <div class="panel-profile">
                <div class="panel-profile-photo">
                    <img src="<?php echo htmlspecialchars($profileImagePath); ?>" alt="Profile picture of <?php echo htmlspecialchars($userData['name']); ?>">
                </div>
                <div class="panel-profile-copy">
                    <h1>Manage your profile</h1>
                    <p class="muted">Signed in as <?php echo htmlspecialchars($userData['email']); ?></p>
                    <p class="welcome">Welcome back, <?php echo htmlspecialchars($userData['name']); ?>.</p>
                    <?php if (!empty($userBio)): ?>
                    <p class="panel-bio"><?php echo nl2br(htmlspecialchars($userBio)); ?></p>
                    <?php endif; ?>
                </div>
            </div>
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

                <form action="update_user.php" method="post" enctype="multipart/form-data">
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

                    <div class="field">
                        <label for="profile_picture">Profile picture</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/png, image/jpeg, image/gif, image/webp" />
                        <span class="field-hint">PNG, JPG, GIF, or WebP up to 2 MB.</span>
                    </div>

                    <div class="field">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio" rows="4" placeholder="Tell others a bit about you"><?php echo htmlspecialchars($userBio); ?></textarea>
                        <span class="field-hint">Limited to 500 characters.</span>
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
                    <?php if (!empty($userBio)): ?>
                    <div class="panel-bio-row">
                        <dt>Bio</dt>
                        <dd><?php echo nl2br(htmlspecialchars($userBio)); ?></dd>
                    </div>
                    <?php endif; ?>
                    
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
