<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class Main {
    var $c = array();

    function __get($key) {
        if (!array_key_exists($key, $this->c)) {
            $this->c[$key] = new $key();
            return $this->c[$key];
        } else {
            return $this->c[$key];
        }
    }

}

class Controller extends Main {

    function __construct() {
        $_auth = & load_class('Auth');
    }

}

function &get_instance() {
    $a = new Main;
    return $a;
}

?>