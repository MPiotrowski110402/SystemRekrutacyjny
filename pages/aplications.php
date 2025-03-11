<?php
require_once 'db.php';
$db = new Database();

class Applications {
    private $db;
    public function __construct(Database $db) {
        $this->db = $db->getConnection();
    }

    public function getAplications() {
        $stmt = $this->db->prepare("SELECT * FROM users ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
$applications = new Applications($db);



$aplication = $applications->getAplications();
echo $twig->render('aplications.html.twig',[
    'applications' => $aplication
    ]);

?>