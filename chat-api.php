<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function apiOut(array $data, int $status = 200): void { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); exit; }
function postInt(string $key): int { return isset($_POST[$key]) && is_numeric($_POST[$key]) ? (int)$_POST[$key] : 0; }
function canAccessVideo(mysqli $conn, int $userId, int $videoId): bool {
    $stmt = $conn->prepare('SELECT v.id FROM videos v LEFT JOIN video_access va ON va.video_id=v.id AND va.user_id=? WHERE v.id=? AND (v.is_public=1 OR v.user_id=? OR va.user_id=?) LIMIT 1');
    if (!$stmt) return false;
    $stmt->bind_param('iiii', $userId, $videoId, $userId, $userId); $stmt->execute(); $ok = (bool)$stmt->get_result()->fetch_row(); $stmt->close(); return $ok;
}
function normalizeRoom(string $room): string { return $room === 'direct' ? 'direct' : 'video'; }
$userId = (int)($_SESSION['user_id'] ?? 0);
if (!$userId) apiOut(['ok'=>false,'error'=>'Please log in to use chat.'], 401);
$action = $_GET['action'] ?? $_POST['action'] ?? 'fetch';
$room = normalizeRoom((string)($_GET['room'] ?? $_POST['room'] ?? 'video'));
$videoId = (int)($_GET['video_id'] ?? $_POST['video_id'] ?? 0);
$otherId = (int)($_GET['user_id'] ?? $_POST['user_id'] ?? 0);
if ($room === 'video') {
    if ($videoId < 1 || !canAccessVideo($conn, $userId, $videoId)) apiOut(['ok'=>false,'error'=>'You do not have access to this video chat.'], 403);
} else {
    if ($otherId < 1 || $otherId === $userId) apiOut(['ok'=>false,'error'=>'Choose a valid conversation.'], 400);
    $stmt=$conn->prepare('SELECT id FROM users WHERE id=?'); $stmt->bind_param('i',$otherId); $stmt->execute(); $exists=(bool)$stmt->get_result()->fetch_row(); $stmt->close();
    if (!$exists) apiOut(['ok'=>false,'error'=>'User not found.'],404);
}

if ($action === 'fetch') {
    $after = max(0, (int)($_GET['after'] ?? 0));
    if ($room === 'video') {
        $stmt=$conn->prepare('SELECT m.id,m.message,m.created_at,m.sender_id,u.username FROM chat_messages m JOIN users u ON u.id=m.sender_id WHERE m.room_type=\'video\' AND m.video_id=? AND m.id>? ORDER BY m.id ASC LIMIT 100');
        $stmt->bind_param('ii',$videoId,$after);
    } else {
        $stmt=$conn->prepare('SELECT m.id,m.message,m.created_at,m.sender_id,u.username FROM chat_messages m JOIN users u ON u.id=m.sender_id WHERE m.room_type=\'direct\' AND m.id>? AND ((m.sender_id=? AND m.recipient_id=?) OR (m.sender_id=? AND m.recipient_id=?)) ORDER BY m.id ASC LIMIT 100');
        $stmt->bind_param('iiiii',$after,$userId,$otherId,$otherId,$userId);
    }
    $stmt->execute(); $result=$stmt->get_result(); $messages=[]; while($row=$result->fetch_assoc()) { $row['id']=(int)$row['id']; $row['sender_id']=(int)$row['sender_id']; $messages[]=$row; } $stmt->close();
    if ($room === 'video') {
        $stmt=$conn->prepare('SELECT u.username FROM chat_typing t JOIN users u ON u.id=t.user_id WHERE t.room_type=\'video\' AND t.video_id=? AND t.user_id<>? AND t.is_typing=1 AND t.updated_at >= (NOW() - INTERVAL 6 SECOND)');
        $stmt->bind_param('ii',$videoId,$userId);
    } else {
        $stmt=$conn->prepare('SELECT u.username FROM chat_typing t JOIN users u ON u.id=t.user_id WHERE t.room_type=\'direct\' AND t.user_id=? AND t.recipient_id=? AND t.is_typing=1 AND t.updated_at >= (NOW() - INTERVAL 6 SECOND)');
        $stmt->bind_param('ii',$otherId,$userId);
    }
    $stmt->execute(); $typingRows=$stmt->get_result(); $typing=[]; while($r=$typingRows->fetch_assoc()) $typing[]=$r['username']; $stmt->close();
    $payload=['ok'=>true,'messages'=>$messages,'typing'=>$typing];
    if ($room === 'video') {
        $stmt=$conn->prepare('SELECT position_seconds,is_paused,updated_by,UNIX_TIMESTAMP(updated_at) AS updated_epoch FROM video_playback_state WHERE video_id=?');
        $stmt->bind_param('i',$videoId); $stmt->execute(); $state=$stmt->get_result()->fetch_assoc(); $stmt->close();
        if ($state) { $state['position_seconds']=(float)$state['position_seconds']; $state['is_paused']=(int)$state['is_paused']; $state['updated_by']=(int)$state['updated_by']; $state['updated_epoch']=(int)$state['updated_epoch']; }
        $payload['playback']=$state ?: null;
    }
    apiOut($payload);
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) apiOut(['ok'=>false,'error'=>'Security token expired. Refresh this page and try again.'], 419);
if ($action === 'send') {
    $message=trim((string)($_POST['message'] ?? ''));
    if ($message === '' || strlen($message) > 8000) apiOut(['ok'=>false,'error'=>'Message must contain 1–2000 characters.'],400);
    if ($room === 'video') $stmt=$conn->prepare('INSERT INTO chat_messages (room_type,video_id,sender_id,message) VALUES (\'video\',?,?,?)');
    else $stmt=$conn->prepare('INSERT INTO chat_messages (room_type,sender_id,recipient_id,message) VALUES (\'direct\',?,?,?)');
    if ($room === 'video') $stmt->bind_param('iis',$videoId,$userId,$message);
    else $stmt->bind_param('iis',$userId,$otherId,$message);
    $ok=$stmt->execute(); $newId=$stmt->insert_id; $stmt->close();
    apiOut(['ok'=>$ok,'id'=>$newId,'error'=>$ok?null:'Could not send message.'], $ok?200:500);
}
if ($action === 'typing') {
    $typing = !empty($_POST['typing']) ? 1 : 0;
    $vId = $room === 'video' ? $videoId : 0; $rId = $room === 'direct' ? $otherId : 0;
    $stmt=$conn->prepare('INSERT INTO chat_typing (user_id,room_type,video_id,recipient_id,is_typing) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE is_typing=VALUES(is_typing),updated_at=CURRENT_TIMESTAMP');
    $stmt->bind_param('isiii',$userId,$room,$vId,$rId,$typing); $ok=$stmt->execute(); $stmt->close(); apiOut(['ok'=>$ok]);
}
if ($action === 'playback' && $room === 'video') {
    $position=max(0.0,min(86400.0,(float)($_POST['position'] ?? 0))); $paused=!empty($_POST['paused'])?1:0;
    $stmt=$conn->prepare('INSERT INTO video_playback_state (video_id,updated_by,position_seconds,is_paused) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE updated_by=VALUES(updated_by),position_seconds=VALUES(position_seconds),is_paused=VALUES(is_paused),updated_at=CURRENT_TIMESTAMP');
    $stmt->bind_param('iidi',$videoId,$userId,$position,$paused); $ok=$stmt->execute(); $stmt->close(); apiOut(['ok'=>$ok]);
}
apiOut(['ok'=>false,'error'=>'Unknown action.'],400);
?>