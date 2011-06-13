<?php
require_once (dirname (__FILE__)."/config.instagram.php");
require_once (dirname (__FILE__)."/class.curl.php");

class IG_STATES {
    const UNAUTH = 0;
    const AUTH = 1;
    const TOKEN = 2;
}

class Instagram {
    private $curl;
    private $auth_token;
    private $state = IG_STATES::UNAUTH;
    private $defparms = array (
        'client_id'=>CLIENT_ID,
        'client_secret'=>CLIENT_SECRET,
        'grant_type'=>'authorization_code',
        'redirect_uri'=>REDIR_URL
    );
    private $scope = array ();
    private $debug = FALSE;

    public $error = NULL;

    function debug ($heading, $object) {
        if ($this->debug) {
            echo "<pre>\n";
            echo "<p>".$heading."</p>\n";
            print_r ($object);
            echo "</pre>\n";
        }
    }

    function __construct ($auth_token=NULL, $scope=NULL) {
        $this->curl = new Curl ();

        if ($this->curl->error) {
            $this->error = $this->curl->error;
            return;
        }

        if ($auth_token != NULL) {
            $this->auth_token = $auth_token;
            /* Verify token */

            if (!($this->call ("users/self/"))) {
                return;
            }

            if ($this->result->meta->code != 200) {
                $this->error = $this->result->
            return;
        }

        if ($scope != NULL) {
            $this->scope = array_merge ($this->scope, $scope);
        }

        if (isset ($_REQUEST['error'])) {
            $this->error = $_REQUEST['error_description'];
            return;
        }

        if (isset ($_REQUEST['code'])) {
            $parameters = array_merge ($this->defparms, array ('code'=>$_REQUEST['code']));

            if (!($this->curl->set_url (TOKEN_URL))) {
                $this->error = $this->curl->error;
                return;
            }

            if (!($token_payload = $this->curl->read ($parameters, 'POST'))) {
                $this->error = $this->curl->error;
                return;
            }

            $this->result = json_decode ($token_payload);

            $this->debug ("Result", $this->result);

            if (isset ($this->result->code) && ($this->result->code != 200)) {
                $this->error = $this->result->error_message;
                return;
            }

            $this->auth_token = $this->result->access_token;
            $this->state = IG_STATES::TOKEN;

            $this->debug ("After oauth_token store", $this->auth_token);
            return;
        }

        /* Auth */
        header ("Location: ".AUTH_URL.$this->build_scope_str());
        //echo "Location: ".AUTH_URL.$this->build_scope_str();
        die ();
    }

    function build_scope_str () {
        $scope_str = "&scope=";

        foreach ($this->scope as $scope) {
            if ($scope_str == "&scope=") {
                $scope_str = $scope;
            } else {
                $scope_str .= "+".$scope;
            }
        }

        if ($scope_str == "&scope=") {
            return "";
        } else {
            return $scope_str;
        }
    }

    function get_auth_token () {
        return $this->auth_token;
    }

    function call ($endpoint, $parameters=NULL, $method="GET") {
        $parameters['access_token'] = $this->auth_token;

        $this->debug ("Parameters", $parameters);

        if (!($this->curl->set_url (BASE_URL.$endpoint))) {
            $this->error = $this->curl->error;
            return FALSE;
        }

        $this->debug ("CURL before", $this->curl);

        if (!($payload = $this->curl->read ($parameters, $method))) {
            $this->error = $this->curl->error;
            return FALSE;
        }

        $this->debug ("CURL after", $this->curl);

        $this->result = json_decode ($payload);

        $this->debug ("Result", $this->result);

        return $payload; //Raw
    }

    function get_result () {
        return $this->result;
    }
}
?>
