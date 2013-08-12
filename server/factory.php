<?php
class Factory {
    private $get, $post, $sql;

    function __construct(array $fig = array()) {
        $this->get = tryArray($fig, 'get');
        $this->post = tryArray($fig, 'post');
        $this->sql = tryArray($fig, 'database');
    }

    function index() {
        return new Home_Controller(new Home_View(new Mustache_Engine()));
    }
}
?>
