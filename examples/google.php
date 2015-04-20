<?php
session_start();
REQUIRE_ONCE '../OAuthClient.class.php';

$CLIENT_ID = '995059916144-2odfvfoh0h18fhfsid1lh25d1vpunm5n.apps.googleusercontent.com';
$CLIENT_SECRET = 'A3G-GgF_2rsWXUuvmU1hPLOv';
$AUTH_URL = 'https://accounts.google.com/o/oauth2/auth';
$TOKEN_URL = 'https://accounts.google.com/o/oauth2/token';

$google = new OAuthClient();
$google->setClientId($CLIENT_ID)->setClientSecret($CLIENT_SECRET)->setScope('https://www.googleapis.com/auth/plus.me')->setAuthUrl($AUTH_URL)->setTokenUrl($TOKEN_URL);

if (isset($_GET['code']) == true) {
	if ($google->authenticate($_GET['code']) == true) {
		$redirectUrl = $google->getRedirectUrl();
		header('location:'.$redirectUrl);
	}
	exit;
} elseif ($google->getAccessToken() == null) {
	$authUrl = $google->getAuthenticationUrl();
	header('location:'.$authUrl);
	exit;
}
?>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1000">
<title>SimpleOAuth2Client Examples - GitHub</title>
</head>
<body>
	<pre>
<?php
	print_r($_SESSION);
	$data = $google->get('https://www.googleapis.com/plus/v1/people/me');
	print_r($data);
?>
	</pre>
</body>
</html>