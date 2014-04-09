<?php
/**
* Utility for downloading files from Google Drive
* 
* @since 4/8/14
* @author James Mensch
**/
require_once ('google-api-php-client/src/Google_Client.php');
require_once ('google-api-php-client/src/contrib/Google_DriveService.php');
chdir(dirname(__FILE__));

/**
* Google Drive download utility
* @param string $id
* @param string $secret
**/
class Drive {
    private $driveClient;
    private $driveService;
    private $tokenFile;

    public function __construct($id, $secret) {
        $this->driveClient  = new Google_Client();
        $this->setClientParams($id, $secret);
        $this->tokenFile    = 'token.txt';
        $this->checkAccessToken();
    }

    /**
    * Sets client parameters
    **/
    private function setClientParams($id, $secret) {
        $this->driveClient->setClientId($id);
        $this->driveClient->setClientSecret($secret);
        $this->driveClient->setRedirectUri(__FILE__);
        $this->driveClient->setScopes(array('https://www.googleapis.com/auth/drive'));
        $this->driveClient->setAccessType('offline');
    }
    /**
    * Starts the drive service
    **/
    private function startDriveService() {
        $this->driveService = new Google_DriveService($this->driveClient);
    }
    /**
    * Checks for previous access token
    **/
    private function checkAccessToken() {
        if (!$token = file_get_contents(urlencode($this->tokenFile))) {
            $accessToken = $this->getAccessToken();
            $this->driveClient->setAccessToken($accessToken);
        } else {
            $localToken = json_decode(unserialize($token));
            $accessToken = $localToken->refresh_token;
            $this->driveClient->refreshToken($accessToken);
        }
    }
    /**
    * Gets new refresh token if not stored locally
    * @return json accessToken
    **/
    private function getAccessToken() {
        $authUrl = $this->driveClient->createAuthUrl();
        $authCode = $_GET['auth'];

        /**
        * Exchange authCode for accessToken
        **/
        $accessToken = $this->driveClient->authenticate($authCode);
        $accessTokenObj = json_deocde($accessToken);

        if ($accessTokenObj->refresh_token) {
            file_put_contents($this->tokenFile, serialize($accessToken));
        } else {
            die("No refresh token in response.");
        }
        return $accessToken;
    }
    /**
    * Downloads file
    * @param string $url
    * @return string file
    **/
    public function getFile($url) {
        try {
            $request = new Google_HttpRequest($url, 'GET', null, null);
        } catch (Exception $e) {
            return false;
        }
        $authenticatedRequest = Google_Client::$io->authenticatedRequest($request);
        if ($authenticatedRequest->getResponseHttpCode() == 200) {
            return $authenticatedRequest->getResponseBody();
        } 
        return false;
    }
}
