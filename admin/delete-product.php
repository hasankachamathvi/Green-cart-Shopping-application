<?php
include(__DIR__ . '/admin-auth.php');
ensureAdminSetup($conn);
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
	$stmt = $conn->prepare('DELETE FROM products WHERE product_id = ?');
	$stmt->bind_param('i', $id);
	$stmt->execute();
}

header('Location: edit-product.php');
exit;
