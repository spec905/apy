<?php
session_start();
require_once 'cnfg.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in']);
    exit();
}

$action = $_POST['action'] ?? 'save';

// LIKE
if ($action === 'toggle_like') {
    $itemId = $_POST['item_id'] ?? null;
    if (!$itemId) {
        echo json_encode(['success' => false, 'message' => 'Missing item id']);
        exit();
    }

    $userEmail = $_SESSION['email'];

    $check = $conn->prepare("SELECT id FROM likes WHERE item_id = ? AND user_email = ?");
    $check->bind_param("is", $itemId, $userEmail);
    $check->execute();
    $existing = $check->get_result()->fetch_assoc();
    $check->close();

    if ($existing) {
        // déjà like = retire le like
        $del = $conn->prepare("DELETE FROM likes WHERE id = ?");
        $del->bind_param("i", $existing['id']);
        $del->execute();
        $del->close();
        $liked = false;
    } else {
        // on ajoute le like
        $ins = $conn->prepare("INSERT INTO likes (item_id, user_email) VALUES (?, ?)");
        $ins->bind_param("is", $itemId, $userEmail);
        $ins->execute();
        $ins->close();
        $liked = true;
    }

    $countResult = $conn->query("SELECT COUNT(*) AS total FROM likes WHERE item_id = " . intval($itemId));
    $count = $countResult->fetch_assoc()['total'];

    echo json_encode(['success' => true, 'liked' => $liked, 'like_count' => $count]);
    $conn->close();
    exit();
}

// ADD COMMENT 
if ($action === 'add_comment') {
    $itemId = $_POST['item_id'] ?? null;
    $text = trim($_POST['comment_text'] ?? '');

    if (!$itemId || $text === '') {
        echo json_encode(['success' => false, 'message' => 'Missing item id or comment text']);
        exit();
    }

    $userEmail = $_SESSION['email'];

    $stmt = $conn->prepare("INSERT INTO comments (item_id, user_email, comment_text) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $itemId, $userEmail, $text);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'comment' => [
                'name' => $_SESSION['name'],
                'comment_text' => $text,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving comment']);
    }

    $stmt->close();
    $conn->close();
    exit();
}

// ADD TO CART 
if ($action === 'add_to_cart') {
    $itemId = $_POST['item_id'] ?? null;
    if (!$itemId) {
        echo json_encode(['success' => false, 'message' => 'Missing item id']);
        exit();
    }

    $userEmail = $_SESSION['email'];

    $stmt = $conn->prepare("INSERT IGNORE INTO cart_items (user_email, item_id) VALUES (?, ?)");
    $stmt->bind_param("si", $userEmail, $itemId);
    $stmt->execute();

    $countResult = $conn->query("SELECT COUNT(*) AS total FROM cart_items WHERE user_email = '" . $conn->real_escape_string($userEmail) . "'");
    $count = $countResult->fetch_assoc()['total'];

    echo json_encode(['success' => true, 'cart_count' => $count]);
    $stmt->close();
    $conn->close();
    exit();
}

//  REMOVE FROM CART 
if ($action === 'remove_from_cart') {
    $itemId = $_POST['item_id'] ?? null;
    if (!$itemId) {
        echo json_encode(['success' => false, 'message' => 'Missing item id']);
        exit();
    }

    $userEmail = $_SESSION['email'];

    $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_email = ? AND item_id = ?");
    $stmt->bind_param("si", $userEmail, $itemId);
    $stmt->execute();

    echo json_encode(['success' => true]);
    $stmt->close();
    $conn->close();
    exit();
}

// DELETe item
if ($action === 'delete') {
    $id = $_POST['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing item id']);
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM items WHERE id = ? AND posted_by = ?");
    $stmt->bind_param("is", $id, $_SESSION['email']);
    $stmt->execute();

    echo json_encode([
        'success' => $stmt->affected_rows > 0,
        'message' => $stmt->affected_rows > 0 ? 'Item deleted' : 'Item not found or not yours'
    ]);

    $stmt->close();
    $conn->close();
    exit();
}

// save 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $price = $_POST['price'] ?? 0;
    $category = $_POST['category'] ?? '';
    $location = $_POST['location'] ?? '';
    $description = $_POST['description'] ?? '';
    $posted_by = $_SESSION['email'];

    // upload de l'image
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('item_', true) . '.' . $ext;
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
            $image = $destination;
        } else {
            echo json_encode(['success' => false, 'message' => 'Error uploading image']);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Image is required']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO items (title, price, category, location, image, description, posted_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdsssss", $title, $price, $category, $location, $image, $description, $posted_by);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Item posted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving item']);
    }
    $stmt->close();
    $conn->close();
}
?>