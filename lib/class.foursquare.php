<?php
require_once (dirname (__FILE__)."/config.foursquare.php");
require_once (dirname (__FILE__)."/class.curl.php");

class FS_STATES {
    const UNAUTH = 0;
    const AUTH = 1;
    const TOKEN = 2;
}

class Foursquare {
    private $curl;
    private $base_url;
    private $id;
    private $secret;
    private $redirect;
    private $auth_url;
    private $token_url;
    private $auth_token;
    private $auth_code;
    private $userless;
    private $result;
    private $state = FS_STATES::UNAUTH;

    public $error=NULL;

    function __construct ($auth_token=NULL) {
        $this->base_url = BASE_URL;
        $this->id = CLIENT_ID;
        $this->secret = CLIENT_SECRET;
        $this->redirect = REDIR_URL;
        $this->auth_url = AUTH_URL;
        $this->token_url = TOKEN_URL;
        $this->curl = new Curl ();

        if ($this->curl->error) {
            $this->error = $this->curl->error;
            return;
        }

        if ($auth_token != NULL) {
            $this->auth_token = $auth_token;
            /* TODO: Test token for freshness */
            return;
        } else {
            if (isset ($_GET['code'])) {
                $this->auth_code = $_GET['code'];
                $this->state = FS_STATES::AUTH;

                if (!($this->result = $this->query_json ($this->token_url."&code=".$this->auth_code))) {
                    return;
                } else {
                    $this->state = FS_STATES::TOKEN;
                    $this->auth_token = $this->result->access_token;
                }
            } else {
                header ("Location: ".$this->get_auth_url ());
                die ();
            }
        }
    }

    function get_auth_url () {
        return $this->auth_url;
    }

    function get_auth_token () {
        return $this->auth_token;
    }

    function get_state () {
        return $this->state;
    }

    function query_json ($url, $parameters=NULL, $method="GET") {
        if (($this->curl->set_url ($url)) === FALSE) {
            $this->error = $this->curl->error;
            return FALSE;
        }

        if (($this->result = json_decode ($this->curl->read ())) === FALSE) {
            $this->error = $this->curl->error;
            return FALSE;
        }

        if ($this->result->meta->code != 200) {
            $this->error = $this->result->meta->errorDetail;
            return FALSE;
        }

        return $this->result; // Raw Result
    }

    function call ($endpoint, $parameters=NULL, $method="GET") {
        if (preg_match ("/\?/", $endpoint)) {
            $seperator = "&";
        } else {
            $seperator = "?";
        }

        return $this->query_json ($this->base_url.$endpoint.$seperator."oauth_token=".$this->auth_token, $parameter, $method);
    }

    function get_result () {
        return $this->result; // Result as array
    }
}
?>
