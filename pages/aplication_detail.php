<?php
require_once 'db.php';
$db = new Database();
class ApplicationDetail {
    private $db;
    public function __construct(Database $db) {
        $this->db = $db->getConnection();
    }
    public function getAplicationById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id =?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function deleteAplicationById($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id =?");
        $stmt->execute([$id]);
        header('Location: index.php?page=aplications');
        exit;
    }
}
$applications = new ApplicationDetail($db);
$id = $_GET['id'];
$application = $applications->getAplicationById($id);


if(isset($_GET['deleteId'])){
    return $applications->deleteAplicationById($_GET['deleteId']);
}
echo $twig->render('aplication_detail.html.twig',[
    'application' => $application
]);
?>