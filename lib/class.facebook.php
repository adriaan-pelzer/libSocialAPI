<?php
require_once (dirname (__FILE__)."/config.facebook.php");
require_once (dirname (__FILE__)."/class.curl.php");

/**
 * Returns the current URL. This is instead of PHP_SELF which is unsafe
 *
 * @param bool $dropqs whether to drop the querystring or not. Default true
 * @return string the current URL
 */
/*function php_self($dropqs=true) {
    $url = sprintf('%s://%s%s',
        empty($_SERVER['HTTPS']) ? 'http' : 'https',
        $_SERVER['SERVER_NAME'],
        $_SERVER['REQUEST_URI']
    );

    $parts = parse_url($url);

    $port = $_SERVER['SERVER_PORT'];
    $scheme = $parts['scheme'];
    $host = $parts['host'];
    $path = @$parts['path'];
    $qs   = @$parts['query'];

    $port or $port = ($scheme == 'https') ? '443' : '80';

    if (($scheme == 'https' && $port != '443')
        || ($scheme == 'http' && $port != '80')) {
            $host = "$host:$port";
        }
    $url = "$scheme://$host$path";
    if ( ! $dropqs)
        return "{$url}?{$qs}";
    else
        return $url;
}*/

class FB_STATES {
    const UNAUTH = 0;
    const AUTH = 1;
    const TOKEN = 2;
}

class Facebook {
    private $curl;
    //private $here = php_self();
    private $scope = array (
        'user_about_me', 'friends_about_me',
        'user_activities', 'friends_activities',
        'user_birthday', 'friends_birthday',
        'user_checkins', 'friends_checkins',
        'user_education_history', 'friends_education_history',
        'user_events', 'friends_events',
        'user_groups', 'friends_groups',
        'user_hometown', 'friends_hometown',
        'user_interests', 'friends_interests',
        'user_likes', 'friends_likes',
        'user_location', 'friends_location',
        'user_notes', 'friends_notes',
        'user_online_presence', 'friends_online_presence',
        'user_photo_video_tags', 'friends_photo_video_tags',
        'user_photos', 'friends_photos',
        'user_relationships', 'friends_relationships',
        'user_relationship_details', 'friends_relationship_details',
        'user_religion_politics', 'friends_religion_politics',
        'user_status', 'friends_status',
        'user_videos', 'friends_videos',
        'user_website', 'friends_website',
        'user_work_history', 'friends_work_history',
        'email',
        'read_friendlists',
        'read_insights',
        'read_mailbox',
        'read_requests',
        'read_stream',
        'xmpp_login',
        'ads_management',
        'publish_stream',
        'create_event',
        'create_note',
        'rsvp_event',
        'sms',
        'offline_access',
        'publish_checkins',
        'manage_friendlists',
        'manage_pages',
        'export_stream',
        'photo_upload',
        'share_item',
        'sms',
        'status_update',
        'video_upload',
        'xmpp_login'
    );
    private $auth_token = array ();
    private $state = FB_STATES::UNAUTH;

    public $error = NULL;

    function __construct ($scope=NULL, $auth_token=NULL, $expires_at=NULL) {
        $this->curl = new Curl ();

        if ($scope != NULL) {
            $this->scope = $scope;
        }

        if ($this->curl->error) {
            $this->error = $this->curl->error;
            return;
        }

        if (isset ($_REQUEST['error'])) {
            $this->error = $_REQUEST['error_description'];
            return;
        }

        if (isset ($_REQUEST['code'])) {
            if (!($this->curl->set_url (TOKEN_URL.$_REQUEST['code']))) {
                $this->error = $this->curl->error;
                return;
            }

            if (!($token_payload = $this->curl->read ())) {
                $this->error = $this->curl->error;
                return;
            }

            $info = $this->curl->get_info();

            if ($info['http_code'] == 400) {
                $this->result = json_decode ($this->get_data ());
                $this->error = $this->result->error->message;
                return;
            }

            $parms_mashed = explode ("&", $token_payload);

            foreach ($parms_mashed as $parm_mashed) {
                list ($key, $value) = explode ("=", $parm_mashed, 2);
                $this->auth_token[$key] = $value;
            }

            if (isset ($this->auth_token['expires'])) {
                $this->auth_token['expires_at'] = time() + $this->auth_token['expires'];
            }

            $this->state = FB_STATES::TOKEN;

            return;
        }

        if ($auth_token != NULL) {
            if (isset ($expires_at) && (time() > $expires_at)) {
                /* Auth */
                header ("Location: ".AUTH_URL.$this->build_scope_str());
                die ();
            }
            $this->auth_token['access_token'] = $auth_token;
            $this->auth_token['expires_at'] = $expires_at;
            /* Verify token */

            if (!($this->call ("me"))) {
                return;
            }
        }

        /* Auth */
        header ("Location: ".AUTH_URL.$this->build_scope_str());
        //echo "Location: ".AUTH_URL.$this->build_scope_str();
        die ();
    }

    function build_scope_str () {
        $scope_str = "";

        foreach ($this->scope as $scope) {
            if ($scope_str == "") {
                $scope_str = $scope;
            } else {
                $scope_str .= ",".$scope;
            }
        }

        return $scope_str;
    }

    function get_auth_token () {
        return $this->auth_token;
    }

    function call ($endpoint, $parameters=NULL, $method="GET") {
        $parameters['access_token'] = $this->auth_token['access_token'];

        if (!($this->curl->set_url (BASE_URL.$endpoint))) {
            $this->error = $this->curl->error;
            return FALSE;
        }

        if (!($payload = $this->curl->read ($parameters, $method))) {
            $this->error = $this->curl->error;
            return FALSE;
        }

        $this->result = json_decode ($payload);

        return $payload; //Raw
    }

    function get_result () {
        return $this->result;
    }
}
?>
