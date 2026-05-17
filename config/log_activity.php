<?php
/**
 * log_activity.php
 * Shared helper — call this after every CREATE, UPDATE, or DELETE.
 *
 * @param PDO    $pdo         Database connection
 * @param int    $user_id     ID of the logged-in user
 * @param string $username    Display name of the logged-in user (first + last)
 * @param string $action      'CREATE' | 'UPDATE' | 'DELETE'
 * @param string $entity_type 'client' | 'subscription'
 * @param int    $entity_id   ID of the record that was changed
 * @param string $description Human-readable summary of what happened
 */
function log_activity($pdo, $user_id, $username, $action, $entity_type, $entity_id, $description) {
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, username, action, entity_type, entity_id, description)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $username, $action, $entity_type, $entity_id, $description]);
}
?>
