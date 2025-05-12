<?php
// messages.php

session_start();

// Include database connection
require_once '../../config/database.php';

// Include Message model
require_once '../../models/Message.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /app-loove/public/auth/login.php");
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Fetch messages for the logged-in user
$messageModel = new Message();
$messages = $messageModel->getMessagesByUserId($user_id);

// Fetch chat partner information if applicable
$partner_id = isset($_GET['partner_id']) ? intval($_GET['partner_id']) : null;
$partner_info = null;
if ($partner_id) {
    $partner_info = $messageModel->getUserById($partner_id);
}

// Include header
include '../layouts/header.php';
?>

<div class="chat-container">
    <h2>Messages</h2>
    <div class="chat-partner-info">
        <?php if ($partner_info): ?>
            <h3>Chatting with: <?php echo htmlspecialchars($partner_info['name']); ?></h3>
        <?php endif; ?>
    </div>
    <div class="messages">
        <?php foreach ($messages as $message): ?>
            <div class="message">
                <strong><?php echo htmlspecialchars($message['sender_name']); ?>:</strong>
                <p><?php echo htmlspecialchars($message['content']); ?></p>
                <span class="timestamp"><?php echo htmlspecialchars($message['timestamp']); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <form action="../../controllers/MessageController.php" method="POST">
        <input type="hidden" name="recipient_id" value="<?php echo $partner_id; ?>">
        <textarea name="message" placeholder="Type your message here..." required></textarea>
        <button type="submit">Send</button>
    </form>
</div>

<?php
// Include footer
include '../layouts/footer.php';
?>