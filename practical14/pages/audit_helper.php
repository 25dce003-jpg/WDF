
<?php

/**
 * Log an administrative action in the audit_logs table.
 *
 * Requires an existing PDO connection in $conn.
 */

if (!function_exists("logAdminAction")) {
    function logAdminAction(
        PDO $conn,
        string $action,
        string $details = ""
    ): bool {
        try {
            $stmt = $conn->prepare(
                "INSERT INTO audit_logs
                    (student_id, action, details, ip_address)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->execute([
                $_SESSION["student_id"] ?? null,
                $action,
                $details,
                $_SERVER["REMOTE_ADDR"] ?? null
            ]);

            return true;

        } catch (Throwable $e) {
            // Logging errors should not stop the main operation.
            error_log(
                "Audit logging failed: " . $e->getMessage()
            );

            return false;
        }
    }
}
