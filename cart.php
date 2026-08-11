<?php
session_start();
require_once 'cnfg.php';

if (!isset($_SESSION['email'])) {
    header("Location: acc.php");
    exit();
}

$myEmail = $_SESSION['email'];

$stmt = $conn->prepare("
    SELECT items.*
    FROM cart_items
    JOIN items ON cart_items.item_id = items.id
    WHERE cart_items.user_email = ?
    ORDER BY cart_items.id DESC
");
$stmt->bind_param("s", $myEmail);
$stmt->execute();
$result = $stmt->get_result();
$cartItems = [];
$total = 0;
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
    $total += $row['price'];
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo">StitchLocal</div>
        <nav>
            <a href="index.php" class="back-to-page-btn">back to page</a>
        </nav>
    </header>

    <main class="chat-container">
        <h2>Your Cart</h2>

        <?php if (empty($cartItems)): ?>
            <p class="no-comments">Your cart is empty.</p>
        <?php endif; ?>

        <div class="cart-list">
            <?php foreach ($cartItems as $item): ?>
                <div class="cart-item" data-id="<?= $item['id'] ?>">
                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                    <div class="cart-item-info">
                        <h3><?= htmlspecialchars($item['title']) ?></h3>
                        <p>$<?= number_format($item['price'], 2) ?></p>
                    </div>
                    <button class="remove-from-cart-btn" data-id="<?= $item['id'] ?>">Remove</button>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($cartItems)): ?>
            <div class="cart-total">
                <h3>Total: $<?= number_format($total, 2) ?></h3>
            </div>
        <?php endif; ?>
    </main>
    <script src="script.js"></script>
</body>
</html>