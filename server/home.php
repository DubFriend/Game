<?php
class Home_Controller extends Controller {
    private $view;

    function __construct($view) {
        $this->view = $view;
    }

    function get() {
        return array('body' => $this->view->render($this->data()));
    }

    private function data() {
        $path = 'js/';
        $libPath = 'js/lib/';
        return array(
            'title' => 'Real Slow Strategy',
            'css' => array('css/bootstrap.css', 'css/style.css'),
            'js' => array(
                $libPath . 'jquery-2.0.3.js',
                $libPath . 'underscore.js',
                $libPath . 'bootstrap.js',
                $libPath . 'mustache.js',
                $libPath . 'echo.js',
                $libPath . 'jsmessage.js',
                $libPath . 'draw.js',
                $path . 'execute.js'
            )
        );
    }
}

class Home_View extends View {
    function body_template() {
        return '' .
        '<div id="game-window"></div>' .
        '<div id="game-menu"></div>';
    }
}
?>
