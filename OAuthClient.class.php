<?php
class OAuthClient {
	private $_clientId;
	private $_clientSecret;
	private $_scope = '';
	private $_redirectUrl;
	private $_userAgent = null;
	
	private $_authUrl;
	private $_tokenUrl;
	
	private $_isMakeAccessHeader = false;
	protected $_headers = array();
	protected $_params = array();
	
	private $_accessToken;
	
	function __construct() {
		$temp = explode('?',$_SERVER['REQUEST_URI']);
		$this->_redirectUrl = 'http://'.$_SERVER['HTTP_HOST'].array_shift($temp);
		
		if ($this->getSession('OAuth') == null) {
			$_SESSION['OAuth'] = array();
		}
		
		$this->_accessToken = $this->getSession('OAuth');
	}
	
	function getSession($name) {
		return isset($_SESSION[$name]) == true ? $_SESSION[$name] : null;
	}
	
	function setClientId($clientId) {
		if (empty($this->_accessToken[$clientId]) == true) {
			$this->_accessToken[$clientId] = null;
			$_SESSION['OAuth'] = $this->_accessToken;
		}
		$this->_clientId = $clientId;
		return $this;
	}
	
	function setClientSecret($clientSecret) {
		$this->_clientSecret = $clientSecret;
		return $this;
	}
	
	function setScope($scope) {
		$this->_scope = $scope;
		return $this;
	}
	
	function setUserAgent($userAgent) {
		$this->_userAgent = $userAgent;
		return $this;
	}
	
	function setAuthUrl($authUrl) {
		$this->_authUrl = $authUrl;
		return $this;
	}
	
	function setTokenUrl($tokenUrl) {
		$this->_tokenUrl = $tokenUrl;
		return $this;
	}
	
	function getRedirectUrl() {
		return $this->_redirectUrl;
	}
	
	function setRedirectUrl($redirectUrl) {
		$this->_redirectUrl = $redirectUrl;
		return $this;
	}
	
	function getAuthenticationUrl($extra_parameters=array()) {
		$parameters = array_merge(array(
			'response_type'=>'code',
			'client_id' =>$this->_clientId,
			'redirect_uri'=>$this->_redirectUrl,
			'scope'=>$this->_scope
		),$extra_parameters);

		return $this->_authUrl.'?'.http_build_query($parameters,null,'&');
	}
	
	function getAccessToken() {
		if ($this->_accessToken[$this->_clientId] != null) return $this->_accessToken[$this->_clientId];
	}
	
	function setAccessToken($token,$type='Url') {
		$this->_accessToken[$this->_clientId] = new stdClass();
		$this->_accessToken[$this->_clientId]->access_token = $token;
		$this->_accessToken[$this->_clientId]->token_type = $type;
		
		$_SESSION['OAuth'] = $this->_accessToken;
		
		return $this;
	}
	
	function authenticate($code) {
		if (strlen($code) == 0) die('Error');
		
		$params = array();
		$params['code'] = $code;
		$params['grant_type'] = 'authorization_code';
		$params['client_id'] = $this->_clientId;
		$params['client_secret'] = $this->_clientSecret;
		$params['redirect_uri'] = $this->_redirectUrl;
		
		$token = $this->executeRequest($this->_tokenUrl,$params);
		
		if ($token !== false) {
			$this->setAccessToken($token->access_token,isset($token->token_type) == true ? strtoupper($token->token_type) : 'URL');
			return true;
		} else {
			return false;
		}
	}
	
	function makeAccessHeader() {
		if ($this->_isMakeAccessHeader === true) return;
		
		switch ($this->_accessToken[$this->_clientId]->token_type) {
			case 'URL' :
				$this->_params['access_token'] = $this->_accessToken[$this->_clientId]->access_token;
			case 'BEARER' :
				$this->_headers['Authorization'] = 'Bearer '.$this->_accessToken[$this->_clientId]->access_token;
				break;
		}
		
		$this->_isMakeAccessHeader = true;
	}
	
	function get($url,$params=array(),$headers=array()) {
		if ($this->_accessToken[$this->_clientId] == null) return null;
		
		$this->makeAccessHeader();
		$headers = array_merge($this->_headers,$headers);
		$params = array_merge($this->_params,$params);
		
		return $this->executeRequest($url,$params,'get',$headers);
	}
	
	function post($url,$params=array(),$headers=array()) {
		$this->makeAccessHeader();
		$headers = array_merge($this->_headers,$headers);
		$params = array_merge($this->_params,$params);
		
		return $this->executeRequest($url,$params,'post',$headers);
	}
	
	function executeRequest($url,$params=array(),$method='post',$headers=array()) {
		$ch = curl_init();
		
		if (empty($headers) == false) {
			$httpHeaders = array();
			foreach($headers as $key=>$value) {
				$httpHeaders[] = $key.': '.$value;
			}
			
			curl_setopt($ch,CURLOPT_HTTPHEADER,$httpHeaders);
		}
		
		if ($this->_userAgent != null) {
			curl_setopt($ch,CURLOPT_USERAGENT,$this->_userAgent);
		}
		
		if ($method == 'post') {
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_POST,true);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$params);
		} else {
			if (empty($params) == true) {
				curl_setopt($ch,CURLOPT_URL,$url);
			} else {
				$url.= '?'.http_build_query($params,null,'&');
				curl_setopt($ch,CURLOPT_URL,$url);
			}
		}
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		
		$result = curl_exec($ch);
		$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		$content_type = explode(';',curl_getinfo($ch,CURLINFO_CONTENT_TYPE));
		$content_type = array_shift($content_type);
		
		if ($http_code == 200) {
			curl_close($ch);
			
			if ($content_type == 'application/json') {
				return json_decode($result);
			} else {
				parse_str($result,$result);
				return (object)$result;
			}
		} else {
			print_r(curl_error($ch));
			curl_close($ch);
			return false;
		}
	}
}
?>
