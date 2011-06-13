<?php
define (CLIENT_ID, "43016acdc897444da0131c33c7807e5d");
define (CLIENT_SECRET, "5df080d637264872a7d9f83a6d29e33c");
define (REDIR_URL, "http://vtp.raak.it/plancast/test_instagram.php");
define (AUTH_URL, "https://api.instagram.com/oauth/authorize/?client_id=".CLIENT_ID."&redirect_uri=".REDIR_URL."&response_type=code");
define (TOKEN_URL, "https://api.instagram.com/oauth/access_token");
define (BASE_URL, "https://api.instagram.com/v1/");
?>
