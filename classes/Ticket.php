// classes/Ticket.php
<?php
class Ticket {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO tickets (title, description, created_by, priority, status, category_id)
            VALUES (:title, :description, :created_by, :priority, 'neu', :category_id)
        ");
        return $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':created_by' => $data['created_by'],
            ':priority' => $data['priority'],
            ':category_id' => $data['category_id']
        ]);
    }

    public function getAll($userId, $role) {
        if ($role === 'admin') {
            $stmt = $this->db->query("SELECT * FROM tickets");
        } else {
            $stmt = $this->db->prepare("SELECT * FROM tickets WHERE created_by = ?");
            $stmt->execute([$userId]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>