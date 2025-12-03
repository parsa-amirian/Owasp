 <?php

 session_start();
 require_once 'mysql.php';
 
 $userid = $_GET['user_id'];
 
 
 $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
 $stmt->bind_param("i", $userid);
 $stmt->execute();
 $user = $stmt->get_result()->fetch_assoc();
 $profile_picture = $user['profile_picture'] ?? 'default.png';
$tweets = [];

if ($conn) {
    $tweetsStmt = $conn->prepare("SELECT content, created_at FROM tweets WHERE user_id = ? ORDER BY created_at DESC");
    if ($tweetsStmt) {
        $tweetsStmt->bind_param("i", $userid);
        if ($tweetsStmt->execute()) {
            $tweetsResult = $tweetsStmt->get_result();
            $tweets = $tweetsResult ? $tweetsResult->fetch_all(MYSQLI_ASSOC) : [];
        }
        $tweetsStmt->close();
    }
}
 #$profile_picture_url = $user[]

 //call the internal.py api to get the user data
 // Fetch the user data from the internal API and print the JSON response
 $apiUrl = "http://10.0.0.10:5000/api/user/" . urlencode($userid);
 $apiResponse = @file_get_contents($apiUrl);

 if ($apiResponse === FALSE) {
     echo "<pre style='color:red;'>Unable to reach internal API or user not found.</pre>";
 } else {
     $apiJson = json_decode($apiResponse, true);
     if ($apiJson !== null) {
        $user_info = json_encode($apiJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
     } else {
         echo "<pre style='color:red;'>Error decoding API response.</pre>";
         die();
     }
 }


 ?>

 <html>
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile</title>
   <link rel="stylesheet" href="/statics/style.css">
</head>
<body class="profile-body">
    <div class="profile-card">
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

        <div class="profile-header">
        <div class="profile-picture">
                <img src="<?php echo htmlspecialchars($profileImagePath); ?>" alt="Profile Picture">
            </div>

            <?php if (!empty($apiJson)): ?>
                <div class="profile-main">
                    <p class="profile-name"><?php echo htmlspecialchars($apiJson['name'] ?? 'N/A'); ?></p>
                    <p class="profile-username">@<?php echo htmlspecialchars($apiJson['username'] ?? 'N/A'); ?></p>
                    <p class="profile-heading">Profile Overview</p>
                </div>
            <?php else: ?>
                <div class="profile-main">
                    <p class="profile-heading">Profile</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($apiJson)): ?>
            <div class="profile-details">
                <p class="section-title">Bio</p>
                <p class="profile-bio"><?php echo nl2br(htmlspecialchars($apiJson['bio'] ?? 'N/A')); ?></p>
            </div>
        <?php else: ?>
            <div class="profile-details">
                <p class="profile-bio">Unable to display profile details.</p>
            </div>
        <?php endif; ?>

        <div class="profile-tweets">
            <h2>Tweets</h2>
            <?php if (!empty($tweets)): ?>
                <ul>
                    <?php foreach ($tweets as $tweet): ?>
                        <li>
                            <p><?php echo nl2br(htmlspecialchars($tweet['content'])); ?></p>
                            <small><?php echo htmlspecialchars($tweet['created_at']); ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No tweets found for this user.</p>
            <?php endif; ?>
        </div>
</div>
</body>
</html>