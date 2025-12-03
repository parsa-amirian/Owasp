<?php
//show all users
require_once 'mysql.php';

$sql = "SELECT id, username, profile_picture, created_at, updated_at FROM users";
$result = $conn->query($sql);




echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Users</title>
    <link rel="stylesheet" href="statics/style.css" />
    <style>
        body {
            background: #f4f6fb;
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .users-wrapper {
            background: #fff;
            max-width: 700px;
            margin: 40px auto 0 auto;
            padding: 32px 24px 24px 24px;
            border-radius: 18px;
            box-shadow: 0 8px 28px #0002;
        }
        h1 {
            text-align: center;
            color: #2d3e50;
            margin: 0 0 28px 0;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fafdff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 14px #c7d1dd33;
        }
        thead tr {
            background: #e8edf5;
        }
        th, td {
            padding: 14px 12px;
            text-align: left;
        }
        th {
            font-weight: 600;
            color: #42516b;
            letter-spacing: 0.04em;
        }
        tbody tr {
            border-top: 1px solid #edf0f5;
        }
        tbody tr:nth-child(even) {
            background: #f5f7fa;
        }
        td img {
            box-shadow: 0 2px 8px #20408015;
            background: #e0e4e8;
            border-radius: 50%;
            vertical-align: middle;
            margin-right: 0.5em;
            width: 48px;
            height: 48px;
            object-fit: cover;
        }
        td {
            color: #273647;
            vertical-align: middle;
            font-size: 1.05em;
        }
        .user-bio {
            color: #5b6e84;
            font-size: 0.99em;
            white-space: pre-line;
        }
            .center-column {
                text-align: center;
            }
    </style>
</head>
<body>
    <div class="users-wrapper">
        <h1>All Users</h1>
        <table>
            <thead>
                <tr>
                    <th class="center-column">ID</th>
                    <th class="center-column">Profile</th>
                    <th class="center-column">Username</th>
                    <th class="center-column">Profile Picture</th>
                    <th class="center-column">Created At</th>
                    <th class="center-column">Updated At</th>
                </tr>
            </thead>
            <tbody>';

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $profilePic = 'statics/default-avatar.svg';
        if (!empty($row['profile_picture'])) {
            $sanitizedFile = basename($row['profile_picture']);
            $candidateRelativePath = 'uploads/' . $sanitizedFile;
            $candidateAbsolutePath = __DIR__ . '/' . $candidateRelativePath;
            if (is_file($candidateAbsolutePath)) {
                $profilePic = $candidateRelativePath;
            }
        }
        echo '<tr>
            <td class="center-column">' . htmlspecialchars($row["id"]) . '</td>
            <td class="center-column">
                <a href="panel.php?user_id=' . urlencode($row['id']) . '">View</a>
            </td>
            <td class="center-column">' . htmlspecialchars($row["username"]) . '</td>
            <td class="center-column"><img src="' . htmlspecialchars($profilePic) . '" alt="Profile Picture"></td>
            <td class="center-column">' . htmlspecialchars($row["created_at"]) . '</td>
            <td class="center-column">' . htmlspecialchars($row["updated_at"]) . '</td>
        </tr>';
    }
} else {
    echo '<tr><td colspan="4" style="text-align:center;color:#789;">No users found.</td></tr>';
}

echo '        </tbody>
        </table>
    </div>
</body>
</html>';

