<?php
session_start();
require_once 'mysql.php';

$tweetMessage = '';
$tweetFetchError = '';
$allTweets = [];
$loggedInUser = null;
$loggedInAvatarPath = 'statics/default-avatar.svg';

if (!empty($_SESSION['userid'])) {
    $indexUserId = (int) $_SESSION['userid'];
    $userStmt = $conn->prepare("SELECT name, username, profile_picture FROM users WHERE id = ?");
    if ($userStmt) {
        $userStmt->bind_param("i", $indexUserId);
        if ($userStmt->execute()) {
            $userResult = $userStmt->get_result();
            $loggedInUser = $userResult ? $userResult->fetch_assoc() : null;
        }
        $userStmt->close();
    }

    if (!empty($loggedInUser['profile_picture'])) {
        $sanitizedProfile = basename($loggedInUser['profile_picture']);
        $candidateRelativePath = 'uploads/' . $sanitizedProfile;
        $candidateAbsolutePath = __DIR__ . '/' . $candidateRelativePath;
        if (is_file($candidateAbsolutePath)) {
            $loggedInAvatarPath = $candidateRelativePath;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['userid'] ?? null;
    $rawTweet = $_POST['tweet_content'] ?? '';
    $rawTweet = trim($rawTweet);

    if (!$userId) {
        $tweetMessage = 'You must be logged in to post a tweet.';
    } elseif ($rawTweet === '') {
        $tweetMessage = 'Tweet content cannot be empty.';
    } else {
        // Limit original content length to 280 characters
        if (function_exists('mb_substr')) {
            $limitedTweet = mb_substr($rawTweet, 0, 280, 'UTF-8');
        } else {
            $limitedTweet = substr($rawTweet, 0, 280);
        }
        // URL-encode the content before storing
        $storedContent = urlencode($limitedTweet);

        $stmt = $conn->prepare("INSERT INTO tweets (user_id, content) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("is", $userId, $storedContent);
            if ($stmt->execute()) {
                $tweetMessage = 'Tweet saved successfully.';
            } else {
                $tweetMessage = 'Error saving tweet.';
            }
            $stmt->close();
        } else {
            $tweetMessage = 'Unable to prepare tweet insert.';
        }
    }
}
$tweetsSql = "SELECT t.user_id, t.content, t.created_at, u.username, 
u.profile_picture FROM tweets t LEFT JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC";
$tweetsResult = $conn->query($tweetsSql);
if ($tweetsResult) {
    while ($tweetRow = $tweetsResult->fetch_assoc()) {
        $allTweets[] = $tweetRow;
    }
    $tweetsResult->free();
} else {
    $tweetFetchError = 'Unable to load tweets right now.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>World.com</title>
    <link rel="stylesheet" href="statics/style.css" />
</head>
<body>
    <main class="card">
        <?php if (!empty($loggedInUser)): ?>
        <div class="index-user-bar">
            <div class="index-user-text">
                <p class="index-user-line">You are logged in as <strong><?php echo htmlspecialchars($loggedInUser['username']); ?></strong></p>
                <p class="index-user-name"><?php echo htmlspecialchars($loggedInUser['name']); ?></p>
            </div>
            <div class="index-user-avatar">
                <img src="<?php echo htmlspecialchars($loggedInAvatarPath); ?>" alt="Your profile picture">
            </div>
        </div>
        <?php endif; ?>

        <h1>Welcome</h1>
        <p>Thanks for visiting. Choose an option to continue.</p>
        <nav class="links">
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
            <a href="panel.php">Panel</a>
            <a href="all_users.php">All Users</a>
        </nav>

        <hr class="divider" />

        <section>
            <h2>Post a Tweet</h2>
            <p class="muted">Write a short message (max 280 characters). Requires login.</p>
            <form method="post" action="index.php" accept-charset="utf-8">
                <div class="field">
                    <label for="tweet_content">Tweet content</label>
                    <textarea id="tweet_content" name="tweet_content" rows="4" maxlength="280" required></textarea>
                </div>
                <div class="actions">
                    <button type="submit">Post Tweet</button>
                </div>
            </form>
            <?php if (!empty($tweetMessage)): ?>
                <p class="message <?php echo strpos($tweetMessage, 'successfully') !== false ? 'message-success' : 'message-error'; ?>">
                    <?php echo htmlspecialchars($tweetMessage); ?>
                </p>
            <?php endif; ?>
        </section>

        <section class="tweet-feed">
            <h2>Latest Tweets</h2>
            <?php if (!empty($tweetFetchError)): ?>
                <p class="message message-error"><?php echo htmlspecialchars($tweetFetchError); ?></p>
            <?php elseif (empty($allTweets)): ?>
                <p class="muted">No tweets have been posted yet.</p>
            <?php else: ?>
                <ul class="tweet-list">
                    <?php foreach ($allTweets as $tweet): ?>
                        <li class="tweet-item">
                            <div class="tweet-avatar">
                                <?php
                                    $tweetProfile = $tweet['profile_picture'] ?? 'statics/default-avatar.svg';
                                    $sanitizedTweetProfile = basename($tweetProfile);
                                    $tweetRelativePath = 'uploads/' . $sanitizedTweetProfile;
                                    $tweetAbsolutePath = __DIR__ . '/' . $tweetRelativePath;
                                    if (empty($tweet['profile_picture']) || !is_file($tweetAbsolutePath)) {
                                        $tweetRelativePath = 'statics/default-avatar.svg';
                                    }
                                ?>
                                <img src="<?php echo htmlspecialchars($tweetRelativePath); ?>" alt="Profile Picture">
                            </div>
                            <div class="tweet-content-wrap">
                                <p class="tweet-meta">
                                    <a href="profile.php?user_id=<?php echo htmlspecialchars($tweet['user_id']); ?>">
                                        <strong><?php echo htmlspecialchars($tweet['username'] ?? 'Unknown'); ?></strong>
                                    </a>
                                    <span><?php echo htmlspecialchars($tweet['created_at'] ?? ''); ?></span>
                                </p>
                                <p class="tweet-content">
                                    <?php echo nl2br(htmlspecialchars(urldecode($tweet['content']))); ?>
                                </p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>