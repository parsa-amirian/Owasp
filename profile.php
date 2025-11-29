 <?php

 session_start();
 require_once 'mysql.php';
 
 $userid = $_GET['user_id'];
 
 
 $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
 $stmt->bind_param("i", $userid);
 $stmt->execute();
 $user = $stmt->get_result()->fetch_assoc();
 $profile_picture = $user['profile_picture'] ?? 'default.png';
 #$profile_picture_url = $user[]

 ?>

 <html>
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile</title>
    <Link rel="stylesheet" href="/statics/style.css">
</head>
<body>
    <div class="container">
        <h1> Profile </h1>
        <?php
            // Sanitize the profile picture filename to avoid directory traversal
            $sanitizedProfile = basename($profile_picture);
            $candidateRelativePath = 'uploads/' . $sanitizedProfile;
            $candidateAbsolutePath = __DIR__ . '/' . $candidateRelativePath;

            // Check if user's custom profile picture exists, else use default
            if (!empty($profile_picture) && $profile_picture !== 'default.png' && is_file($candidateAbsolutePath)) {
                $profileImagePath = $candidateRelativePath;
            } else {
                $profileImagePath = 'statics/default-avatar.svg';
            }
        ?>
        <div class="profile-picture">
            <img src="<?php echo htmlspecialchars($profileImagePath); ?>" alt="Profile Picture" style="max-width:140px;max-height:140px;border-radius:50%;">
        </div>
</div>
</htlm>