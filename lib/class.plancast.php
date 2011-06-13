<?php
require_once (dirname (__FILE__)."/config.plancast.php");
require_once (dirname (__FILE__)."/class.curl.php");

class Plancast {
    private $curl;
    private $username;
    private $password;
    private $base_url;
    private $result;

    public $error=NULL;

    function __construct () {
        $this->curl = new Curl ();
        $this->username = USER;
        $this->password = PASS;
        $this->base_url = BASEURL;

        if ($this->curl->error) {
            $this->error = $this->curl->error;
            return;
        }

        if (($this->curl->auth_basic ($this->username, $this->password)) === FALSE) {
            $this->error = $this->curl->error;
            return FALSE;
        }
    }

    function call ($endpoint, $parameters=NULL, $method="GET") {
        if (($this->curl->set_url ($this->base_url.$endpoint.".json")) === FALSE) {
            $this->error = $this->curl->error;
            return FALSE;
        }

        if (($this->result = $this->curl->read ($parameters, $method)) === FALSE) {
            $this->error = $this->curl->error;
            return FALSE;
        }

        return $this->result; // Raw Result
    }

    function get_result () {
        return json_decode ($this->result); // Result as array
    }
}
?>
