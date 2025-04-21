<?php
class BaseController {
    protected $db;
    
    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    protected function model($model) {
        require_once __DIR__ . '/../models/' . $model . '.php';
        return new $model();
    }
    
    protected function view($view, $data = []) {
        extract($data);
        require_once __DIR__ . '/../views/' . $view . '.php';
    }
    
    protected function render($view, $data = []) {
        extract($data);
        
        ob_start();
        require_once __DIR__ . '/../views/' . $view . '.php';
        $content = ob_get_clean();
        
        require_once __DIR__ . '/../views/layouts/default.php';
    }
    
    protected function renderPartial($view, $data = []) {
        extract($data);
        ob_start();
        require_once __DIR__ . '/../views/' . $view . '.php';
        return ob_get_clean();
    }
    
    protected function redirect($url) {
        header("Location: /shoppingcart/" . ltrim($url, '/'));
        exit();
    }
    
    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    protected function partial($view, $data = []) {
        $content = $this->renderPartial('partials/' . $view, $data);
        echo $content;
    }
}
?> 