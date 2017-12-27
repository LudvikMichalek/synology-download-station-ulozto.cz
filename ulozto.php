<?php

class SynoFileHostingUlozto {

	const HOST_BASE_URL = 'https://uloz.to/';

    private $Url;
    private $Username;
    private $Password;
    private $HostInfo;
    private $Token;

	
    public function __construct($Url, $Username, $Password, $HostInfo) {
        $this->Url = $Url;
        $this->Username = $Username;
        $this->Password = $Password;
        $this->HostInfo = $HostInfo;
    }

	
    //This function returns download url
    public function GetDownloadInfo() {
        // check user account
		if ($this->Verify() === LOGIN_FAIL) {
			return array(DOWNLOAD_ERROR => LOGIN_FAIL);
		}
		return array(DOWNLOAD_URL => $this->getFileUrl());
	}
	

	public function Verify($ClearCookie = NULL) {
        if ($this->getToken() === true) {
            return USER_IS_PREMIUM;
        } else {
            return LOGIN_FAIL;
        }
    }


    private function getFileUrl() {
		sleep(2);
		
		$PostData = array(
			'username' => $this->Username,
			'password' => $this->Password,
			'remember' => 'false',
			'_token_'  => $this->Token
		);

		$PostData = http_build_query($PostData);
	
		// do login
		$loginSession = curl_init();
		curl_setopt($loginSession, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($loginSession, CURLOPT_POST, TRUE);
		curl_setopt($loginSession, CURLOPT_POSTFIELDS, $PostData);
		curl_setopt($loginSession, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.10) Gecko/2009042523 Ubuntu/9.04 (jaunty) Firefox/3.0.10');
		curl_setopt($loginSession, CURLOPT_COOKIEJAR, '/tmp/ulozto.cookies.l');
		curl_setopt($loginSession, CURLOPT_COOKIEFILE, '/tmp/ulozto.cookies.t');
		curl_setopt($loginSession, CURLOPT_HEADER, TRUE);
		curl_setopt($loginSession, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($loginSession, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($loginSession, CURLOPT_TIMEOUT, 15);
		curl_setopt($loginSession, CURLOPT_REFERER, self::HOST_BASE_URL . "login?do=loginForm-submit");
		curl_setopt($loginSession, CURLOPT_URL, self::HOST_BASE_URL . "login?do=loginForm-submit");
		curl_exec($loginSession);
		curl_close($loginSession);
		
		// get file URL redirect
		$fileSession = curl_init();
		curl_setopt($fileSession, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($fileSession, CURLOPT_POST, TRUE);
		curl_setopt($fileSession, CURLOPT_POSTFIELDS, $PostData);
		curl_setopt($fileSession, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.10) Gecko/2009042523 Ubuntu/9.04 (jaunty) Firefox/3.0.10');
		curl_setopt($fileSession, CURLOPT_COOKIEFILE, '/tmp/ulozto.cookies.l');
		curl_setopt($fileSession, CURLOPT_HEADER, TRUE);
		curl_setopt($fileSession, CURLOPT_TIMEOUT, 15);
		curl_setopt($fileSession, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($fileSession, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($fileSession, CURLOPT_REFERER, self::HOST_BASE_URL . "login");
		curl_setopt($fileSession, CURLOPT_URL, $this->Url . "?do=directDownload");
		$LoginInfo = curl_exec($fileSession);
		$info = curl_getinfo($fileSession);
		$redirect_url = $info['url'];
		curl_close($fileSession);
		
		return $redirect_url;
    }
	
    private function getToken() {
        $tokenSession = curl_init();
		curl_setopt($tokenSession, CURLOPT_HEADER, false);
		curl_setopt($tokenSession, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.10) Gecko/2009042523 Ubuntu/9.04 (jaunty) Firefox/3.0.10');
		curl_setopt($tokenSession, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($tokenSession, CURLOPT_TIMEOUT, 15);
		curl_setopt($tokenSession, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($tokenSession, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($tokenSession, CURLOPT_COOKIEJAR, '/tmp/ulozto.cookies.t');
		curl_setopt($tokenSession, CURLOPT_URL, self::HOST_BASE_URL . 'login');
		$response = curl_exec($tokenSession);
		curl_close($tokenSession);

		// parse token
		preg_match('/frm-loginForm-_token_" value="(.*?)"/', $response, $tokenValues);
		
		if ($response === NULL) {
			return false;
		}
		
		$this->Token = $tokenValues[1];
		
		if (strlen($this->Token) !== 38) {
			return false;
		}

		return true;
	}

}
