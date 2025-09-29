<?php
require_once 'db.php';

function respond($arr, $code = 200)
{
    http_response_code($code);
    echo json_encode($arr);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if (!$action) {
    respond(['success' => false, 'message' => 'Missing action parameter'], 400);
}

$conn = openCon();
if (!$conn) {
    respond(['success' => false, 'message' => 'Database connection error'], 500);
}

switch ($action) {
    case 'list':
        $result = $conn->query("SELECT * FROM tasks ORDER BY id DESC");
        if ($result) {
            $tasks = [];
            while ($row = $result->fetch_assoc()) {
                $tasks[] = $row;
            }
            respond(['success' => true, 'tasks' => $tasks]);
        } else {
            respond(['success' => false, 'message' => 'Failed to fetch tasks'], 500);
        }
        break;

    case 'add':
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Allow: POST');
            respond(['success' => false, 'message' => 'Method Not Allowed. Use POST.'], 405);
        }

        $raw = file_get_contents('php://input');
        $payload = [];
        $contentType = $_SERVER['CONTENT_TYPE'] ?? ($_SERVER['HTTP_CONTENT_TYPE'] ?? '');

        if ($raw && stripos($contentType, 'application/json') !== false) {
            $payload = json_decode($raw, true) ?: [];
        } else {
            $payload = $_POST ?: [];
        }

        $title = trim($payload['title'] ?? '');

        if ($title === '') {
            respond(['success' => false, 'message' => 'Title is required'], 422);
        }
        if (mb_strlen($title) > 255) {
            respond(['success' => false, 'message' => 'Title too long (max 255 chars)'], 422);
        }

        $checkStmt = mysqli_prepare($conn, 'SELECT id FROM tasks WHERE title = ?');
        if (!$checkStmt) {
            respond(['success' => false, 'message' => 'Failed to prepare duplicate check'], 500);
        }
        mysqli_stmt_bind_param($checkStmt, 's', $title);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);

        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            mysqli_stmt_close($checkStmt);
            respond(['success' => false, 'message' => 'Task with this title already exists'], 409);
        }
        mysqli_stmt_close($checkStmt);

        $stmt = mysqli_prepare($conn, 'INSERT INTO tasks (title) VALUES (?)');
        if (!$stmt) {
            respond(['success' => false, 'message' => 'Failed to prepare statement'], 500);
        }

        mysqli_stmt_bind_param($stmt, 's', $title);

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            respond(['success' => false, 'message' => 'Failed to add task'], 500);
        }

        $id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        respond([
            'success' => true,
            'message' => 'Task added',
            'id'      => (int)$id,
            'title'   => $title
        ], 201);
        break;


    case 'delete':
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Allow: POST');
            respond(['success' => false, 'message' => 'Method Not Allowed. Use POST.'], 405);
        }
        $id = null;
        if (isset($_GET['id'])) {
            $id = (int) $_GET['id'];
        } elseif (isset($_POST['id'])) {
            $id = (int) $_POST['id'];
        } else {
            $raw = file_get_contents('php://input');
            $ct  = $_SERVER['CONTENT_TYPE'] ?? ($_SERVER['HTTP_CONTENT_TYPE'] ?? '');
            if ($raw && stripos($ct, 'application/json') !== false) {
                $body = json_decode($raw, true) ?: [];
                if (isset($body['id'])) {
                    $id = (int) $body['id'];
                }
            }
        }

        if (!$id || $id <= 0) {
            respond(['success' => false, 'message' => 'Valid id is required'], 422);
        }

        $stmt = mysqli_prepare($conn, 'DELETE FROM tasks WHERE id = ?');
        if (!$stmt) {
            respond(['success' => false, 'message' => 'Failed to prepare statement'], 500);
        }

        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            respond(['success' => false, 'message' => 'Failed to delete task'], 500);
        }

        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        if ($affected === 0) {
            respond(['success' => false, 'message' => 'Task not found'], 404);
        }

        respond(['success' => true, 'message' => 'Task deleted']);

        break;

    default:
        respond(['success' => false, 'message' => 'Unknown action'], 400);
}

?>
