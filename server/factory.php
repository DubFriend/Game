<?php
class Factory {
    private $get, $post, $sql;

    function __construct(array $fig = array()) {
        $this->get = tryArray($fig, 'get');
        $this->post = tryArray($fig, 'post');
        $this->sql = tryArray($fig, 'database');
    }

    function index() {
        return new Default_Controller();
    }
}
?>
