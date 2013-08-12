<?php
require ROOT . "function.php";
require ROOT . "lib/mustache.php/src/Mustache/Autoloader.php";
require ROOT . "lib/sequel.php";
require ROOT . "lib/authentication/authenticate.php";
require ROOT . "lib/authentication/registration.php";
require ROOT . "factory.php";
require ROOT . "router.php";
require ROOT . "base.php";
require ROOT . "home.php";

Mustache_Autoloader::register();

$get = sanitizeArray($_GET);
$post = sanitizeArray($_POST);
$server = sanitizeArray($_SERVER);

$_GET = $_POST = $_SERVER = null;

$Factory = new Factory(array(
    /*'database' => new Sequel(new PDO(
        'mysql:host=' . DATABASE_HOST . ';dbname=' . DATABASE_NAME,
        DATABASE_USER,
        DATABASE_PASS
    )),*/
    'get' => $get,
    'post' => $post,
    'server' => $server
));

$Router = new Router(array(
    'factory' => $Factory,
    'path' => tryArray($server, 'PATH_INFO', '')
));

$response = $Router->route()->respond($server['REQUEST_METHOD']);

http_response_code($response['status']);
echo $response['body'];
?>
