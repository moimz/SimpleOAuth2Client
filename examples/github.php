<?php
session_start();
REQUIRE_ONCE '../OAuthClient.class.php';

$CLIENT_ID = 'b3f954eccc5378afbacf';
$CLIENT_SECRET = '4507787bbac2f89382c5b29dc07017bbc776c218';
$AUTH_URL = 'https://github.com/login/oauth/authorize';
$TOKEN_URL = 'https://github.com/login/oauth/access_token';

$github = new OAuthClient();
$github->setClientId($CLIENT_ID)->setClientSecret($CLIENT_SECRET)->setAuthUrl($AUTH_URL)->setScope('user')->setUserAgent('Awesome-Octocat-App')->setTokenUrl($TOKEN_URL);

if (isset($_GET['code']) == true) {
	if ($github->authenticate($_GET['code']) == true) {
		$redirectUrl = $github->getRedirectUrl();
		header('location:'.$redirectUrl);
	}
	exit;
} elseif ($github->getAccessToken() == null) {
	$authUrl = $github->getAuthenticationUrl();
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
	$data = $github->get('https://api.github.com/user');
	print_r($data);
?>
	</pre>
</body>
</html>