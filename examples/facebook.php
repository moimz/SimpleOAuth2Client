<?php
session_start();
REQUIRE_ONCE '../OAuthClient.class.php';

$CLIENT_ID = '262048643983887';
$CLIENT_SECRET = 'ff6440811c9834222fd8cbc60efd1ccd';
$AUTH_URL = 'https://graph.facebook.com/oauth/authorize';
$TOKEN_URL = 'https://graph.facebook.com/oauth/access_token';

$facebook = new OAuthClient();
$facebook->setClientId($CLIENT_ID)->setClientSecret($CLIENT_SECRET)->setAuthUrl($AUTH_URL)->setTokenUrl($TOKEN_URL);

if (isset($_GET['code']) == true) {
	if ($facebook->authenticate($_GET['code']) == true) {
		$redirectUrl = $facebook->getRedirectUrl();
		header('location:'.$redirectUrl);
	}
	exit;
} elseif ($facebook->getAccessToken() == null) {
	$authUrl = $facebook->getAuthenticationUrl();
	header('location:'.$authUrl);
	exit;
}
?>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1000">
<title>SimpleOAuth2Client Examples - Facebook</title>
</head>
<body>
	<pre>
<?php
	print_r($_SESSION);
	$data = $facebook->get('https://graph.facebook.com/me');
	print_r($data);
?>
	</pre>
</body>
</html>