<?php
require_once (dirname (__FILE__)."/config.twitter.php");
require_once (dirname (__FILE__)."/tmhOAuth.php");

session_start ();

class TW_STATES {
    const UNAUTH = 0;
    const AUTH = 1;
    const TOKEN = 2;
}

class Twitter {
    private $tmhOAuth;
    private $here;
    private $result;
    private $state = TW_STATES::UNAUTH;
    private $config = array (
        'oob'           =>  FALSE,
        'signin'        =>  TRUE,
        'force'         =>  FALSE,
        'force_write'   =>  FALSE,
        'force_read'    =>  FALSE
    );

    public $error = NULL;

    function __construct () {
        $this->tmhOAuth = new tmhOAuth (array ( 'consumer_key'    => CLIENT_ID, 'consumer_secret' => CLIENT_SECRET,));
        $this->here = $this->tmhOAuth->php_self();
        return;
    }

    function get_state () {
        return $this->state;
    }

    function set_up_stored ($auth_token) {
        $this->tmhOAuth->config['user_token']  = $auth_token['oauth_token'];
        $this->tmhOAuth->config['user_secret'] = $auth_token['oauth_token_secret'];

        $code = $this->tmhOAuth->request ('GET', $this->tmhOAuth->url ('1/account/verify_credentials'));
        $this->result = json_decode ($this->tmhOAuth->response['response']);
        if ($code != 200) {
            $this->error = $this->result->error;
            return FALSE;
        }

        $this->state = TW_STATES::TOKEN;
        return TRUE;
    }

    function kickstart ($config=NULL) {
        if ($config != NULL) {
            $this->config = array_merge ($this->config, $config);
        }

        $callback = ($this->config['oob']) ? 'oob' : $this->here;

        $code = $this->tmhOAuth->request('POST', $this->tmhOAuth->url('oauth/request_token', ''), array( 'oauth_callback' => $callback));
        $this->result = json_decode ($this->tmhOAuth->response['response']);

        if ($code == 200) {
            $_SESSION['oauth'] = $this->tmhOAuth->extract_params($this->tmhOAuth->response['response']);
            $method = ($this->config['signin']) ? 'authenticate' : 'authorize';
            $force  = ($this->config['force']) ? '&force_login=1' : '';
            $forcewrite  = ($this->config['force_write']) ? '&oauth_access_type=write' : '';
            $forceread  = ($this->config['force_read']) ? '&oauth_access_type=read' : '';
            header("Location: " . $this->tmhOAuth->url("oauth/{$method}", '') .  "?oauth_token={$_SESSION['oauth']['oauth_token']}{$force}{$forcewrite}{$forceread}");

        } else {
            $this->error = $this->result->error;
            return FALSE;
        }

        return TRUE;
    }

    function request_access_token ($oauth_verifier) {
        $this->tmhOAuth->config['user_token']  = $_SESSION['oauth']['oauth_token'];
        $this->tmhOAuth->config['user_secret'] = $_SESSION['oauth']['oauth_token_secret'];

        $code = $this->tmhOAuth->request('POST', $this->tmhOAuth->url('oauth/access_token', ''), array( 'oauth_verifier' => $oauth_verifier));
        $this->result = json_decode ($this->tmhOAuth->response['response']);

        if ($code != 200) {
            $this->error = $this->result->error;
            return FALSE;
        }

        /* Do something about this to get rid of sessions in library */
        $_SESSION['access_token'] = $this->tmhOAuth->extract_params($this->tmhOAuth->response['response']);
        unset($_SESSION['oauth']);
        /*******/
        header("Location: {$this->here}");
    }

    function call ($endpoint, $parameters=NULL, $method="GET") {
        $code = $this->tmhOAuth->request($method, $this->tmhOAuth->url('1/'.$endpoint), $parameters);
        $this->result = json_decode ($this->tmhOAuth->response['response']);

        if ($code != 200) {
            $this->error = $this->result->error;
            return FALSE;
        }

        return $this->tmhOAuth->response['response'];
    }

    function get_result () {
        return $this->result;
    }

    function get_auth_token () {
        if (isset ($this->tmhOAuth->config['user_token']) && isset ($this->tmhOAuth->config['user_secret'])) {
            return array ('user_token'=>$this->tmhOAuth->config['user_token'], 'user_secret'=>$this->tmhOAuth->config['user_secret']);
        } else {
            return FALSE;
        }
    }

    function destroy () {
        session_destroy ();
        header ("Location: ".$this->here);
    }
}
?>
