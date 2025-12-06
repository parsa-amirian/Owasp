<?php
session_start();
require_once 'mysql.php';

if(!isset($_SESSION['login'])) {
    header('Location: msg.php?msg=You are not logged in&type=error&goto=login.php');
    exit();
}

if(isset($_GET['post_id'])) {
    $id = (int) $_GET['post_id'];
    
    // Get the user_id from the tweet before deleting it, so we can redirect back to the profile
    $getStmt = $conn->prepare("SELECT user_id FROM tweets WHERE id = ?");
    $getStmt->bind_param("i", $id);
    $getStmt->execute();
    $result = $getStmt->get_result();
    $tweet = $result->fetch_assoc();
    $tweetUserId = $tweet ? $tweet['user_id'] : null;
    $getStmt->close();
    
    // Delete the tweet
    $stmt = $conn->prepare("DELETE FROM tweets WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    
    // Redirect back to the profile page
    $redirectUserId = $tweetUserId ?? (isset($_SESSION['userid']) ? $_SESSION['userid'] : '');
    header('Location: msg.php?msg=Tweet deleted successfully&type=success&goto=profile.php?user_id=' . urlencode($redirectUserId));
    exit();
} else {
    $redirectUserId = isset($_SESSION['userid']) ? $_SESSION['userid'] : '';
    header('Location: msg.php?msg=Invalid request&type=error&goto=profile.php?user_id=' . urlencode($redirectUserId));
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Tweet</title>
</head>