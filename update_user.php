<?php
session_start();
require_once 'mysql.php';

if (!isset($_SESSION['userid'])) {
    header('Location: msg.php?msg=You are not logged in&type=error&goto=login.php');
    exit();
}

$userid = $_SESSION['userid'];
$name = trim($_POST['name'] ?? '');
$password = $_POST['password'] ?? '';
$bio = trim($_POST['bio'] ?? '');
$errors = [];
$uploadFileName = null;
$maxUploadSize = 2 * 1024 * 1024; // 2 MB
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

if ($name === '') {
    $errors[] = 'Name is required.';
}

if (strlen($bio) > 500) {
    $errors[] = 'Bio must be 500 characters or fewer.';
}

$currentUserStmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
$currentUserStmt->bind_param("i", $userid);
$currentUserStmt->execute();
$currentResult = $currentUserStmt->get_result();
$currentUser = $currentResult ? $currentResult->fetch_assoc() : null;
$currentUserStmt->close();

if (!$currentUser) {
    header('Location: msg.php?msg=Unable to load your profile&type=error&goto=panel.php');
    exit();
}

if (!empty($_FILES['profile_picture']['name'])) {
    $file = $_FILES['profile_picture'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'There was a problem uploading your profile picture.';
    } elseif ($file['size'] > $maxUploadSize) {
        $errors[] = 'Profile picture must be smaller than 2 MB.';
    } else {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $detectedType = $finfo->file($file['tmp_name']);

        if (!in_array($detectedType, $allowedMimeTypes, true)) {
            $errors[] = 'Only PNG, JPG, GIF, or WebP images are allowed.';
        } else {
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($extension === '') {
                switch ($detectedType) {
                    case 'image/jpeg':
                        $extension = 'jpg';
                        break;
                    case 'image/png':
                        $extension = 'png';
                        break;
                    case 'image/gif':
                        $extension = 'gif';
                        break;
                    case 'image/webp':
                        $extension = 'webp';
                        break;
                    default:
                        $extension = 'img';
                }
            }

            $uploadDir = __DIR__ . '/uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $uploadFileName = sprintf('user_%d_%s.%s', $userid, uniqid('', true), $extension);
            $destination = $uploadDir . '/' . $uploadFileName;

            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                $errors[] = 'Unable to save the uploaded profile picture.';
                $uploadFileName = null;
            }
        }
    }
}

if (!empty($errors)) {
    if ($uploadFileName) {
        @unlink(__DIR__ . '/uploads/' . $uploadFileName);
    }
    $message = urlencode(implode(' ', $errors));
    header("Location: msg.php?msg={$message}&type=error&goto=panel.php");
    exit();
}

if ($password === '') {
    $password = null;
}

if ($password !== null && strlen($password) < 2) {
    $message = urlencode('Password must be at least 2 characters.');
    if ($uploadFileName) {
        @unlink(__DIR__ . '/uploads/' . $uploadFileName);
    }
    header("Location: msg.php?msg={$message}&type=error&goto=panel.php");
    exit();
}

if ($password !== null && $uploadFileName !== null) {
    $stmt = $conn->prepare("UPDATE users SET name = ?, password = ?, profile_picture = ?, bio = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $password, $uploadFileName, $bio, $userid);
} elseif ($password !== null) {
    $stmt = $conn->prepare("UPDATE users SET name = ?, password = ?, bio = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $password, $bio, $userid);
} elseif ($uploadFileName !== null) {
    $stmt = $conn->prepare("UPDATE users SET name = ?, profile_picture = ?, bio = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $uploadFileName, $bio, $userid);
} else {
    $stmt = $conn->prepare("UPDATE users SET name = ?, bio = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $bio, $userid);
}

if ($stmt->execute()) {
    if ($uploadFileName !== null) {
        $previousFile = $currentUser['profile_picture'] ?? '';
        if (!empty($previousFile) && $previousFile !== 'default.png') {
            $previousPath = __DIR__ . '/uploads/' . basename($previousFile);
            if (is_file($previousPath)) {
                @unlink($previousPath);
            }
        }
    }

    $stmt->close();
    $message = urlencode('Profile updated successfully.');
    header("Location: msg.php?msg={$message}&type=success&goto=panel.php");
    exit();
}

if ($uploadFileName !== null) {
    @unlink(__DIR__ . '/uploads/' . $uploadFileName);
}

$stmt->close();
$message = urlencode('Failed to update your profile. Please try again.');
header("Location: msg.php?msg={$message}&type=error&goto=panel.php");
exit();

