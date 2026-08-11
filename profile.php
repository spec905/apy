<?php
session_start();
require_once 'cnfg.php';
$loggedInUser = null;
if (isset($_SESSION['name']) && isset($_SESSION['email'])) {
    $loggedInUser = [
        'name' => $_SESSION['name'],
        'email' => $_SESSION['email'],
        'country' => $_SESSION['country'] ?? ''
        
    ];
}

//items in profile
$items = [];
$userEmail = $loggedInUser['email'] ?? '';
//recupere likes
$stmt = $conn->prepare("
    SELECT items.*,
        (SELECT COUNT(*) FROM likes WHERE likes.item_id = items.id) AS like_count,
        (SELECT COUNT(*) FROM likes WHERE likes.item_id = items.id AND likes.user_email = ?) AS liked_by_user
    FROM items ORDER BY created_at DESC
");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}
$stmt->close();
//recupere comments
$commentsByItem=[];
$commentsResult=$conn->query("
SELECT comments.item_id,comments.comment_text, users.name
FROM comments Join users ON comments.user_email=users.email
ORDER BY comments.created_at ASC");
while($row = $commentsResult->fetch_assoc()){
    $commentsByItem[$row['item_id']][] = $row;
}
foreach ($items as &$item) {
    $item['comments'] = $commentsByItem[$item['id']] ?? [];
}
unset($item);
unset($item);
$conn->close();
?>

<!--html start-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo">StitchLocal</div>
        <nav>
            <a href="index.php" class="back-to-page-btn">back to page</a>
        </nav>
    </header>

    <main>
        <section class="profile_header">
            <div class="profile_pic" id="profile_pic"></div>

            <form id="upload-pic-form" action="upload_picture.php" method="post" enctype="multipart/form-data">
                <input type="file" name="profile_picture" id="profile_picture_input" accept="image/*" required style="display:none;">
                <button type="button" id="upload-btn" class="upload-btn">profile picture</button>
                <button type="submit" id="submit-upload-btn"class="upload-btn" style="display: none;">Upload Photo</button>      
            <section class="profile-info-box">
                <div class="profile_info">
                <h2 id="user_name"> Name</h2>
                <p id="user_email"> email@user.com</p>
                <p id="user_location">Country</p>
            </div>
            </section>
        </section>
       
        <section class="list_container">
            <h2>Items posted</h2>
            <div id="profile-items-grid" class="grid"></div>
        </section>
    </main>
    <script>
        const phpUser = <?= json_encode($loggedInUser) ?>;
        const phpItems = <?= json_encode($items) ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>