<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../../config/config.php';

$action = $_POST['action'] ?? '';

switch ($action) {

    // ── CREATE ────────────────────────────────────────────────────────────────
    case 'create':
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Category name is required.']);
            exit();
        }

        try {
            // Check duplicate
            $check = $conn->prepare("SELECT id FROM categories WHERE name = ?");
            $check->execute([$name]);
            if ($check->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Category name already exists.']);
                exit();
            }

            $stmt = $conn->prepare("INSERT INTO categories (name, description, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$name, $description]);
            echo json_encode(['success' => true, 'message' => 'Category created successfully.']);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error.']);
        }
        break;

    // ── READ ONE (for edit form) ───────────────────────────────────────────────
    case 'get':
        $id = intval($_POST['id'] ?? 0);
        try {
            $stmt = $conn->prepare("SELECT id, name, description FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $cat = $stmt->fetch(PDO::FETCH_OBJ);
            if ($cat) {
                echo json_encode(['success' => true, 'data' => $cat]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Category not found.']);
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error.']);
        }
        break;

    // ── UPDATE ────────────────────────────────────────────────────────────────
    case 'update':
        $id          = intval($_POST['id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name) || $id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid data.']);
            exit();
        }

        try {
            // Check duplicate (exclude self)
            $check = $conn->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
            $check->execute([$name, $id]);
            if ($check->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Category name already exists.']);
                exit();
            }

            $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $description, $id]);
            echo json_encode(['success' => true, 'message' => 'Category updated successfully.']);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error.']);
        }
        break;

    // ── DELETE ────────────────────────────────────────────────────────────────
    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
            exit();
        }

        try {
            // Don't delete if products still assigned
            $check = $conn->prepare("SELECT COUNT(*) as cnt FROM products WHERE category_id = ?");
            $check->execute([$id]);
            $cnt = $check->fetch(PDO::FETCH_OBJ)->cnt;
            if ($cnt > 0) {
                echo json_encode(['success' => false, 'message' => "Cannot delete: {$cnt} product(s) still assigned to this category."]);
                exit();
            }

            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Category deleted successfully.']);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
