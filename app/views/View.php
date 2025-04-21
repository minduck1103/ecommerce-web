<?php
class View {
    private $layout = 'default';
    private $data = [];
    
    // Phương thức set layout
    public function setLayout($layout) {
        $this->layout = $layout;
    }
    
    // Phương thức set data
    public function setData($key, $value) {
        $this->data[$key] = $value;
    }
    
    // Phương thức render view
    public function render($view, $data = []) {
        // Merge data
        $this->data = array_merge($this->data, $data);
        
        // Extract data to variables
        extract($this->data);
        
        // Start output buffering
        ob_start();
        
        // Include view file
        $viewFile = 'app/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            throw new Exception("View file {$view} not found");
        }
        
        // Get view content
        $content = ob_get_clean();
        
        // Include layout if exists
        $layoutFile = 'app/views/layouts/' . $this->layout . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }
    
    // Phương thức render partial view
    public function partial($view, $data = []) {
        // Extract data to variables
        extract($data);
        
        // Include partial view file
        $viewFile = 'app/views/partials/' . $view . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            throw new Exception("Partial view file {$view} not found");
        }
    }
    
    // Phương thức render JSON response
    public function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?> 