<?php
// includes/session_db.php

class DatabaseSessionHandler implements SessionHandlerInterface {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        
        // Ensure the sessions table exists
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS sessions (
            id VARCHAR(128) NOT NULL PRIMARY KEY,
            data BLOB NOT NULL,
            last_access INT(11) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    public function open($savePath, $sessionName): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read($id): string {
        $stmt = $this->pdo->prepare("SELECT data FROM sessions WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (string)$row['data'] : '';
    }

    public function write($id, $data): bool {
        $stmt = $this->pdo->prepare("REPLACE INTO sessions (id, data, last_access) VALUES (:id, :data, :last_access)");
        return $stmt->execute([
            'id' => $id,
            'data' => $data,
            'last_access' => time()
        ]);
    }

    public function destroy($id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function gc($maxlifetime): int|false {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE last_access < :old");
        $old = time() - $maxlifetime;
        $stmt->execute(['old' => $old]);
        return $stmt->rowCount();
    }
}
?>
