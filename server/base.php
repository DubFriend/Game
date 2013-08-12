<?php
abstract class Controller {
    const OK = "200";
    const UNAUTHORIZED = "401";
    const NOT_FOUND = "404";
    const ERROR = "500";

    function respond($method) {
        switch ($method) {
            case 'GET':
                $response = $this->get();
                break;
            case 'PUT':
                $response = $this->put();
                break;
            case 'POST':
                $response = $this->post();
                break;
            case 'DELETE':
                $response = $this->delete();
                break;
            case 'HEAD':
                $response = $this->head();
                break;
            case 'OPTIONS':
                $response = $this->options();
                break;
            default:
                $response = array(
                    "status" => self::ERROR,
                    "body" => "bad request type : $method"
                );
                break;
        }
        if(!tryArray($response, 'status')) {
            $response['status'] = self::OK;
        }
        return $response;
    }

    protected function get() { return $this->defaultResponse("get"); }
    protected function put() { return $this->defaultResponse("put"); }
    protected function post() { return $this->defaultResponse("post"); }
    protected function delete() { return $this->defaultResponse("delete"); }
    protected function head() { return $this->defaultResponse("head"); }
    protected function options() { return $this->defaultResponse("options"); }

    private function defaultResponse($method) {
        return array(
            "status" => self::NOT_FOUND,
            "body" => "$method requests are not implemented on this url"
        );
    }
}

abstract class View {

    private $templator;

    function __construct($templator) {
        $this->templator = $templator;
    }

    abstract protected function body_template();

    function render(array $data = array()) {
        return $this->templator->render($this->page_template(), $data);
    }

    private function page_template() {
        return '' .
        '<!DOCTYPE html>' .
        '<html lang="en">' .
            $this->head_template() .
            '<body>' .
                $this->body_template() .
                $this->js_template() .
            '</body>' .
        '</html>';
    }

    private function head_template() {
        return '' .
        '<head>' .
            '<meta charset="utf-8">' .
            '<title>{{title}}</title>' .
            '{{#css}}' .
                '<link rel="stylesheet" href="{{.}}">' .
            '{{/css}}' .
            '<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">' .
            '<link rel="icon" href="favicon.ico" type="image/x-icon">' .
        '</head>';
    }

    private function js_template() {
        return '' .
        '{{#js}}' .
            '<script src="{{.}}"></script>' .
        '{{/js}}';
    }
}
?>
