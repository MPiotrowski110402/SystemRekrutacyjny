<?php
require_once 'db.php';
$db = new Database();
$errors = [];
class Auth {
    private $db;


    public function __construct(Database $db) {
        $this->db = $db->getConnection();
    }

    public function validateFormData($data){
        $errors = [];
        if(empty($data['first_name'])){
            $errors[] = 'First name is required';
        } elseif (!preg_match('/^[a-zA-ZąćęłńóśżźĄĆĘŁŃÓŚŻŹ]+$/u', $data['first_name'])) {
            $errors[] = 'First name should only contain letters';
        }
        if(empty($data['last_name'])){
            $errors[] = 'Last name is required';
        } elseif (!preg_match('/^[a-zA-ZąćęłńóśżźĄĆĘŁŃÓŚŻŹ]+$/u', $data['last_name'])) {
            $errors[] = 'Last name should only contain letters';
        }
        if(empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
            $errors[] = 'Invalid email address';
        }
        if(empty($data['phone']) ||!preg_match('/^[0-9]{9}$/', $data['phone'])){
            $errors[] = 'Invalid phone number';
        }
        //opcjonalne dane
        if(isset($data['linkedin']) && $data['linkedin'] !== '' && !filter_var($data['linkedin'], FILTER_VALIDATE_URL)){
            $errors[] = 'Invalid Linkedin URL';
        }
        if(isset($data['github']) && $data['github'] !== '' && !filter_var($data['github'], FILTER_VALIDATE_URL)){
            $errors[] = 'Invalid GitHub URL';
        }

        if(empty($data['experience'])){
            $errors[] = 'Experience is required';
        }
        if(empty($data['cv']['name'])){
            $errors[] = 'CV file is required';
        }
        $email = $this->db->prepare("SELECT email FROM users WHERE email = ?");
        $email->execute([$data['email']]);
        if($email->rowCount() > 0){
            $errors[] = 'Email already exists';
        }
        if(isset($data['portfolio']) && !empty($data['portfolio']['name'])){
            $this->validateFile($data['portfolio'], $errors);
        }
        $this->validateFile($data['cv'], $errors);
        return $errors;

    }
    private function validateFile($file, &$errors){
        if($file['error'] !== UPLOAD_ERR_OK){
            $errors[] = 'Error uploading CV file';
            return;
        }
        $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if(!in_array(mime_content_type($file['tmp_name']),$allowedMimeTypes)){
            $errors[] = 'Invalid CV file format';
        }
        if($file['size'] > 5* 1024 * 1024){
            $errors[] = 'CV file size exceeds 5MB Limit';
        }
    }
    public function saveFormData($data) {

        $cvPath = $this->saveFile($data['cv']);
        $portfolioPath = $this->saveFile($data['portfolio']);
        
    

        $stmt = $this->db->prepare("INSERT INTO users (first_name, last_name, email, phone, experience, linkedin, github, portfolio, cv) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['experience'],
            $data['linkedin'] ?? null,
            $data['github'] ?? null,
            $portfolioPath,
            $cvPath
        ]);
    }
    private function saveFile($file) {
        $targetDir = 'assets/';
        
        $uniqueFileName = uniqid() . '-' . basename($file['name']);
        
        $targetFilePath = $targetDir . $uniqueFileName;

        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            return $targetFilePath; 
        } else {
            return null; 
        }
    }
}
$auth = new Auth($db);
if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST'){
    $formData = [
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'experience' => $_POST['experience'],
        'cv' => $_FILES['cv'],
    ];
    if(isset($_POST['linkedin'])){
        $formData['linkedin'] = $_POST['linkedin'];
    }
    if(isset($_POST['github'])){
        $formData['github'] = $_POST['github'];
    }
    if(isset($_POST['portfolio'])){
        $formData['portfolio'] = $_FILES['portfolio'];
    }
    $errors = $auth->validateFormData($formData);
    if(empty($errors)){
        $auth->saveFormData($formData);
        header('Location: index.php?page=success');
        exit();
    }

}
echo $twig->render('form.html.twig', [
    'errors' => $errors 
]);
    
?>
