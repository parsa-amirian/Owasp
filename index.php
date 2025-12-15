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
/* Load tweets from database
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
    */
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
                    <button id="tweet_submit_button" type="button" name="tweet-submit" >Post Tweet</button>
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
            <p id="tweet-status" class="muted">Loading tweets...</p>
            <ul class="tweet-list" id="tweet-list"></ul>
        </section>
    </main>
</body>
<script>

    document.addEventListener('DOMContentLoaded', function()
    {
        window.addEventListener('message', function(event) 
        {    
            console.log('Recived postMessage:', event.data);
        });

        (function() {
        const tweetStatus = document.getElementById('tweet-status');
        const tweetList = document.getElementById('tweet-list');
        let data = [];

        const escapeHtml = (value) => {
            const div = document.createElement('div');
            div.textContent = value ?? '';
            return div.innerHTML;
        };

        const renderTweets = (tweets) => {
            tweetList.innerHTML = '';
            if (!tweets || tweets.length === 0) {
                tweetStatus.textContent = 'No tweets have been posted yet.';
                tweetStatus.className = 'muted';
                return;
            }

            tweetStatus.textContent = '';
            tweetStatus.className = '';

            tweets.forEach((tweet) => {
                const li = document.createElement('li');
                li.className = 'tweet-item';

                const avatarWrap = document.createElement('div');
                avatarWrap.className = 'tweet-avatar';
                const img = document.createElement('img');
                img.src = tweet.profile_picture_url || 'statics/default-avatar.svg';
                img.alt = 'Profile Picture';
                avatarWrap.appendChild(img);

                const contentWrap = document.createElement('div');
                contentWrap.className = 'tweet-content-wrap';

                const meta = document.createElement('p');
                meta.className = 'tweet-meta';

                const userLink = document.createElement('a');
                userLink.href = 'profile.php?user_id=' + encodeURIComponent(tweet.user_id);
                userLink.innerHTML = '<strong>' + escapeHtml(tweet.username || 'Unknown') + '</strong>';

                const created = document.createElement('span');
                created.textContent = tweet.created_at || '';

                meta.appendChild(userLink);
                meta.appendChild(created);

                const content = document.createElement('p');
                content.className = 'tweet-content';
                const safeContent = escapeHtml(tweet.content || '').replace(/\n/g, '<br>');
                content.innerHTML = safeContent;

                contentWrap.appendChild(meta);
                contentWrap.appendChild(content);

                li.appendChild(avatarWrap);
                li.appendChild(contentWrap);

                tweetList.appendChild(li);
            });
        };
        document.getElementById('tweet_submit_button').addEventListener('click', function()
        {
        //alert('submitTweet');
        document.getElementById('tweet_submit_button').disable = true;
        document.getElementById('tweet_submit_button').innerHTML = "Submiting...";
        const tweetContent = document.getElementById('tweet_content').value;
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'tweets.php', true);
        xhr.setRequestHeader('content-type', "application/JSON");
        xhr.send(JSON.stringify({tweet_content: tweetContent}));
        xhr.onreadystatechange = function()
        {
            if(xhr.readyState ===4 )
            {
                //alert("Tweet Submit succesfully");
                setTimeout(function()
                {
                    document.getElementById('tweet_submit_button').disabled = false;
                    var btn = document.getElementById('tweet_submit_button');
                    if(btn && btn.parentNode)
                    {
                        btn.parentNode.removeChild(btn);
                    }
                    location.reload(1);
                }, 2000);
            }
        };
        });



        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'tweets.php', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        data = JSON.parse(xhr.responseText);
                        renderTweets(data);
                    } catch (error) {
                        tweetStatus.textContent = 'Unable to load tweets right now.';
                        tweetStatus.className = 'message message-error';
                    }

                        window.postMessage(data, '*');

                } else {
                    tweetStatus.textContent = 'Unable to load tweets right now.';
                    tweetStatus.className = 'message message-error';
                }
            }
        };
        xhr.send();
        })();
    });
</script>
</html>