<?php
define (CLIENT_ID, "Z0WPKRYOCPCO3O3DQSXZPCX4OWMWMN2H22VH5AX2GMTVM3YB");
define (CLIENT_SECRET, "1E0JNNJCOOPKRUD5LC3KGBIN4KFQXT05KAAQLWB4B4VC0TY3");
define (REDIR_URL, "http://vtp.raak.it/plancast/test_foursquare.php");
define (AUTH_URL, "https://foursquare.com/oauth2/authenticate?client_id=".CLIENT_ID."&response_type=code&redirect_uri=".REDIR_URL);
define (TOKEN_URL, "https://foursquare.com/oauth2/access_token?client_id=".CLIENT_ID."&client_secret=".CLIENT_SECRET."&grant_type=authorization_code&redirect_uri=".REDIR_URL);
define (BASE_URL, "https://api.foursquare.com/v2/");
?>
