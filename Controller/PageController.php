<?php

class PageController {
    
    public function about() {
        include __DIR__ . '/../views/Front_back/AboutView.php';
    }

    public function team() {
        include __DIR__ . '/../views/Front_back/TeamView.php';
    }

    public function services() {
        include __DIR__ . '/../views/Front_back/ServicesView.php';
    }
}
?>
