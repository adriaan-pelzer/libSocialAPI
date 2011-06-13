<?php
class Curl {
    private $curl_handle;
    private $url;
    private $data;
    private $info;

    public $error=NULL;

    function __construct ($url=NULL) {
        $this->curl_handle = curl_init ($url);
        if (!($this->setopt (CURLOPT_RETURNTRANSFER, TRUE))) {
            $this->error = "Cannot set RETURNTRANSFER option";
            return;
        } else if (!($this->setopt (CURLOPT_FOLLOWLOCATION, TRUE))) {
            $this->error = "Cannot set FOLLOWLOCATION option";
            return;
        } else if (!($this->setopt (CURLOPT_MAXREDIRS, 10))) {
            $this->error = "Cannot set MAXREDIRS option";
            return;
        } else if (!($this->setopt(CURLOPT_SSLVERSION,3))) {
            $this->error = "Cannot set SSLVERSION option";
            return FALSE;
        } else if (!($this->setopt(CURLOPT_SSL_VERIFYPEER, FALSE))) {
            $this->error = "Cannot set SSL_VERIFYPEER option";
            return FALSE;
        } else if (!($this->setopt(CURLOPT_SSL_VERIFYHOST, 2))) {
            $this->error = "Cannot set SSL_VERIFYHOST option";
            return FALSE;
        }
        return;
    }

    function get_data () {
        return $this->data;
    }

    function get_info () {
        return $this->info;
    }

    function setopt ($opt, $val) {
        if (!(curl_setopt ($this->curl_handle, $opt, $val))) {
            $this->error = "Cannot set ".$opt." option";
            return FALSE;
        } else {
            return TRUE;
        }
    }

    function set_url ($url) {
        $this->url = $url;
        return ($this->setopt (CURLOPT_URL, $url));
    }

    function convert_params ($params) {
        if (is_array ($params)) {
            foreach ($params as $key=>$val) {
                $params_str .= $key."=".$val."&";
            }
            rtrim ($params_str, "&");
        } else {
            $params_str = $params;
        }

        return $params_str;
    }

    function auth_basic ($username, $password) {
        if (!($this->setopt(CURLOPT_HTTPAUTH, CURLAUTH_BASIC ))) {
            $this->error = "Cannot set HHTPAUTH option";
            return FALSE;
        } else if (!($this->setopt(CURLOPT_USERPWD, $username.":".$password))) {
            $this->error = "Cannot set USERPWD option";
            return FALSE;
        }

        return TRUE;
    }

    function read ($params=NULL, $method="GET") {
        if ($method == "GET") {
            if (!($this->setopt (CURLOPT_HTTPGET, TRUE))) {
                $this->error = "Cannot set HTTP method to GET";
                return FALSE;
            }

            if (($params != NULL) && (!($this->set_url ($this->url."?".$this->convert_params ($params))))) {
                $this->error = "Cannot set url with GET parameters added";
                return FALSE;
            }
        } else if ($method == "POST") {
            if (!($this->setopt (CURLOPT_POST, TRUE))) {
                $this->error = "Cannot set HTTP method to POST";
                return FALSE;
            }

            if (($params != NULL) && (!($this->setopt (CURLOPT_POSTFIELDS, $this->convert_params ($params))))) {
                $this->error = "Cannot set POSTFIELDS";
                return FALSE;
            }
        } else {
            $this->error = "Method ".$method." not implemented";
            return FALSE;
        }

        $this->data = curl_exec ($this->curl_handle);
        $this->info = curl_getinfo ($this->curl_handle);

        /*echo "<p>data</p>\n";
        echo "<pre>\n";
        echo $this->data."\n";
        echo "</pre>\n";*/

        return $this->data;
    }

    function close () {
        curl_close ($this->curl_handle);
    }

    function __destruct () {
        $this->close ();
    }
}
?>
