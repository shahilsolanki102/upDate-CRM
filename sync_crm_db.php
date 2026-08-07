<?php
$conn = new mysqli('localhost', 'root', '', 'updt_crm');
if ($conn->connect_error) die("DB Error: " . $conn->connect_error);

echo "Syncing updt_crm database tables...\n";

// 1. activity_log
$conn->query("CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 2. announcements
$conn->query("CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 3. events
$conn->query("CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    start DATETIME NOT NULL,
    end DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 4. knowledge
$conn->query("CREATE TABLE IF NOT EXISTS knowledge (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 5. profiles
$conn->query("CREATE TABLE IF NOT EXISTS profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    designation VARCHAR(100),
    profile_pic VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 6. Fix tasks columns for upDate project compatibility
function addCol($conn, $t, $c, $type) {
    $r = $conn->query("SHOW COLUMNS FROM `$t` LIKE '$c'");
    if ($r && $r->num_rows == 0) {
        $conn->query("ALTER TABLE `$t` ADD `$c` $type");
    }
}

addCol($conn, 'tasks', 'user_id', 'INT DEFAULT 0');
addCol($conn, 'tasks', 'created_by', 'INT DEFAULT 0');
addCol($conn, 'tasks', 'submission_file', 'VARCHAR(255)');
addCol($conn, 'tasks', 'submitted_at', 'DATETIME');
addCol($conn, 'tasks', 'remarks', 'TEXT');

addCol($conn, 'users', 'department', 'VARCHAR(80)');
addCol($conn, 'users', 'designation', 'VARCHAR(80)');

addCol($conn, 'notes', 'note', 'TEXT');

// Sync user_id in tasks if assigned_to is populated
$conn->query("UPDATE tasks SET user_id = assigned_to WHERE user_id = 0 AND assigned_to > 0");

// Seed initial users if empty
$pass = password_hash('user123', PASSWORD_DEFAULT);
$conn->query("INSERT IGNORE INTO users (id, name, email, password, role) VALUES 
(1, 'Admin User', 'admin@updtcrm.com', '$pass', 'admin'),
(2, 'Solanki Sahil', 'sahilsolanki2073@gmail.com', '$pass', 'user'),
(3, 'Rahul Sharma', 'rahul@updtcrm.com', '$pass', 'user')");

$conn->query("INSERT IGNORE INTO admins (id, name, email, password, role) VALUES 
(1, 'Admin User', 'admin@updtcrm.com', 'admin123', 'admin')");

echo "updt_crm database is 100% complete and synchronized!\n";
?>
