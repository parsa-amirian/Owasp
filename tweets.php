<?php

session_start();
require_once 'mysql.php';

header('Content-Type: application/json');

/**
 * Build a profile picture URL for API responses.
 */
function build_profile_picture_url(?string $profilePicture): string {
    if (empty($profilePicture)) {
        return 'statics/default-avatar.svg';
    }

    $sanitized = basename($profilePicture);
    return 'uploads/' . rawurlencode($sanitized);
}

/**
 * Return a JSON error response and exit.
 */
function json_error(int $statusCode, string $message): void {
    http_response_code($statusCode);
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

function handle_post(): void {
    if (empty($_SESSION['userid'])) {
        json_error(401, 'You must be logged in to post a tweet.');
    }

    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        json_error(400, 'Invalid JSON payload.');
    }

    $userId = (int) $_SESSION['userid'];
    $rawTweet = trim($payload['tweet_content'] ?? '');
    if ($rawTweet === '') {
        json_error(400, 'Tweet content cannot be empty.');
    }

    // Limit to 280 characters
    $limitedTweet = function_exists('mb_substr')
        ? mb_substr($rawTweet, 0, 280, 'UTF-8')
        : substr($rawTweet, 0, 280);

    $storedContent = urlencode($limitedTweet);

    $stmt = $GLOBALS['conn']->prepare("INSERT INTO tweets (user_id, content) VALUES (?, ?)");
    if (!$stmt) {
        json_error(500, 'Unable to prepare tweet insert.');
    }

    $stmt->bind_param("is", $userId, $storedContent);
    $executeOk = $stmt->execute();
    $tweetId = $stmt->insert_id;
    $stmt->close();

    if (!$executeOk) {
        json_error(500, 'Failed to save tweet.');
    }

    // Fetch user info for response
    $userStmt = $GLOBALS['conn']->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $userRow = $userResult ? $userResult->fetch_assoc() : null;
    $userStmt->close();

    $profilePicUrl = build_profile_picture_url($userRow['profile_picture'] ?? null);
    $username = $userRow['username'] ?? 'Unknown';
    $createdAt = date('Y-m-d H:i:s');

    echo json_encode([
        'success' => true,
        'tweet' => [
            'id' => $tweetId,
            'user_id' => $userId,
            'username' => $username,
            'content' => $limitedTweet,
            'created_at' => $createdAt,
            'profile_picture_url' => $profilePicUrl,
        ],
    ]);
    exit();
}

function handle_get(): void {
    $all_tweets_sql = "select t.user_id, t.content,
     t.created_at, u.username, u.profile_picture 
     from tweets t left join users u on t.user_id = u.id order by t.created_at desc";
    $all_tweets_result = $GLOBALS['conn']->query($all_tweets_sql);
    $all_tweets = [];
    if ($all_tweets_result) {
        while ($tweet = $all_tweets_result->fetch_assoc()) {
            $tweet['content'] = urldecode($tweet['content'] ?? '');
            if (!empty($tweet['created_at'])) {
                $dt = new DateTime($tweet['created_at']);
                $tweet['created_at'] = $dt->format('Y-m-d H:i:s');
            } else {
                $tweet['created_at'] = '';
            }
            $tweet['profile_picture_url'] = build_profile_picture_url($tweet['profile_picture'] ?? null);
            $all_tweets[] = $tweet;
        }
        $all_tweets_result->free();
    }

    echo json_encode($all_tweets);
    exit();
}

// Explicit routing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_post();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    handle_get();
}

json_error(405, 'Method not allowed.');
?>
