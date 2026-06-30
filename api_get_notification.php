<?php
// api_get_notifications.php
// JSON endpoint for the Flutter app — returns this user's rows from the
// `notifications` table, same data getNotifications()/getUnreadCount()
// in config.php provide to the web header dropdown.
//
// Also supports POST ?action=mark_read to flip is_read=1 for all of
// this user's notifications, mirroring markAllRead() in config.php.

require_once 'config.php';
header('Content-Type: application/json');

$userId = isset($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : 0;
if (!$userId) {
    echo json_encode(['error' => 'Missing user_id']);
    exit;
}

// Mark-all-read action (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_read') {
    markAllRead($conn, $userId);
    echo json_encode(['success' => true]);
    exit;
}

// Default action: fetch notifications (GET)
$notifs = getNotifications($conn, $userId, 20);
$unread = getUnreadCount($conn, $userId);

$out = array_map(function ($n) {
    return [
        'id'        => (int) $n['id'],
        'title'     => $n['title'],
        'message'   => $n['message'],
        'isRead'    => (bool) $n['is_read'],
        'date'      => $n['created_at'],
    ];
}, $notifs);

echo json_encode([
    'notifications' => $out,
    'unreadCount'   => $unread,
]);