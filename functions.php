<?php

    /*
     * Copyright 2011-2012 Evernote Corporation.
     *
     * This file contains functions used by Evernote's PHP OAuth samples.
     */

    $everInterfaceBasePath = dirname(__DIR__);
    require_once 'vendor/epals/epalsapi/src/ePals/UserAttribute.php';
    //require_once $everInterfaceBasePath.'/evercase/vendor/autoload.php';
    // Import the classes that we're going to be using
    use EDAM\Error\EDAMSystemException,
        EDAM\Error\EDAMUserException,
        EDAM\Error\EDAMErrorCode,
        EDAM\Error\EDAMNotFoundException;
    use Evernote\Client;
    use ePals\UserAttribute;
   
    
    // Verify that you successfully installed the PHP OAuth Extension
    if (!class_exists('OAuth')) {
        die("<span style=\"color:red\">The PHP OAuth Extension is not installed</span>");
    }

    // Verify that you have configured your API key
    if (strlen(OAUTH_CONSUMER_KEY) == 0 || strlen(OAUTH_CONSUMER_SECRET) == 0) {
        $configFile = dirname(__FILE__) . '/config.php';
        die("<span style=\"color:red\">Before using this sample code you must edit the file $configFile " .
              "and fill in OAUTH_CONSUMER_KEY and OAUTH_CONSUMER_SECRET with the values that you received from Evernote. " .
              "If you do not have an API key, you can request one from " .
              "<a href=\"http://dev.evernote.com/documentation/cloud/\">http://dev.evernote.com/documentation/cloud/</a></span>");
    }

    /*
     * The first step of OAuth authentication: the client (this application)
     * obtains temporary credentials from the server (Evernote).
     *
     * After successfully completing this step, the client has obtained the
     * temporary credentials identifier, an opaque string that is only meaningful
     * to the server, and the temporary credentials secret, which is used in
     * signing the token credentials request in step 3.
     *
     * This step is defined in RFC 5849 section 2.1:
     * http://tools.ietf.org/html/rfc5849#section-2.1
     *
     * @return boolean TRUE on success, FALSE on failure
     */
    function getTemporaryCredentials()
    {
        global $lastError, $currentStatus;
        try {
            $client = new Client(array(
                'consumerKey' => OAUTH_CONSUMER_KEY,
                'consumerSecret' => OAUTH_CONSUMER_SECRET,
                'sandbox' => SANDBOX
            ));
            $requestTokenInfo = $client->getRequestToken(getCallbackUrl());
            if ($requestTokenInfo) {
                $_SESSION['requestToken'] = $requestTokenInfo['oauth_token'];
                $_SESSION['requestTokenSecret'] = $requestTokenInfo['oauth_token_secret'];
                $currentStatus = 'Obtained temporary credentials';

                return TRUE;
            } else {
                $lastError = 'Failed to obtain temporary credentials.';
            }
        } catch (OAuthException $e) {
            $lastError = 'Error obtaining temporary credentials: ' . $e->getMessage();
        }

        return FALSE;
    }

    /*
     * The completion of the second step in OAuth authentication: the resource owner
     * authorizes access to their account and the server (Evernote) redirects them
     * back to the client (this application).
     *
     * After successfully completing this step, the client has obtained the
     * verification code that is passed to the server in step 3.
     *
     * This step is defined in RFC 5849 section 2.2:
     * http://tools.ietf.org/html/rfc5849#section-2.2
     *
     * @return boolean TRUE if the user authorized access, FALSE if they declined access.
     */
    function handleCallback1()
    {
        global $lastError, $currentStatus;
        if (isset($_GET['oauth_verifier'])) {
            $_SESSION['oauthVerifier'] = $_GET['oauth_verifier'];
            $currentStatus = 'Content owner authorized the temporary credentials';

            return TRUE;
        } else {
            // If the User clicks "decline" instead of "authorize", no verification code is sent
            $lastError = 'Content owner did not authorize the temporary credentials';

            return FALSE;
        }
    }

    /*
     * The third and final step in OAuth authentication: the client (this application)
     * exchanges the authorized temporary credentials for token credentials.
     *
     * After successfully completing this step, the client has obtained the
     * token credentials that are used to authenticate to the Evernote API.
     * In this sample application, we simply store these credentials in the user's
     * session. A real application would typically persist them.
     *
     * This step is defined in RFC 5849 section 2.3:
     * http://tools.ietf.org/html/rfc5849#section-2.3
     *
     * @return boolean TRUE on success, FALSE on failure
     */
    function getTokenCredentials1()
    {
        global $lastError, $currentStatus;

        if (isset($_SESSION['accessToken'])) {
            $lastError = 'Temporary credentials may only be exchanged for token credentials once';

            return FALSE;
        }

        try {
            $client = new Client(array(
                'consumerKey' => OAUTH_CONSUMER_KEY,
                'consumerSecret' => OAUTH_CONSUMER_SECRET,
                'sandbox' => SANDBOX
            ));
            $accessTokenInfo = $client->getAccessToken($_SESSION['requestToken'], $_SESSION['requestTokenSecret'], $_SESSION['oauthVerifier']);
            if ($accessTokenInfo) {
                $_SESSION['accessToken'] = $accessTokenInfo['oauth_token'];
                $currentStatus = 'Exchanged the authorized temporary credentials for token credentials';

                return TRUE;
            } else {
                $lastError = 'Failed to obtain token credentials.';
            }
        } catch (OAuthException $e) {
            $lastError = 'Error obtaining token credentials: ' . $e->getMessage();
        }

        return FALSE;
    }

    /*
     * Demonstrate the use of token credentials obtained via OAuth by listing the notebooks
     * in the resource owner's Evernote account using the Evernote API. Returns an array
     * of String notebook names.
     *
     * Once you have obtained the token credentials identifier via OAuth, you can use it
     * as the auth token in any call to an Evernote API function.
     *
     * @return boolean TRUE on success, FALSE on failure
     */
    function listNotebooks()
    {
        global $lastError, $currentStatus;

        try {
            $accessToken = $_SESSION['accessToken'];
            $client = new Client(array(
                'token' => $accessToken,
                'sandbox' => SANDBOX
            ));
            $notebooks = $client->getNoteStore()->listNotebooks();
            $result = array();
            if (!empty($notebooks)) {
                foreach ($notebooks as $notebook) {
                    $result[] = $notebook->name;
                }
            }
            $_SESSION['notebooks'] = $result;
            $currentStatus = 'Successfully listed content owner\'s notebooks';

            return TRUE;
        } catch (EDAMSystemException $e) {
            if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
                $lastError = 'Error listing notebooks: ' . EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter;
            } else {
                $lastError = 'Error listing notebooks: ' . $e->getCode() . ": " . $e->getMessage();
            }
        } catch (EDAMUserException $e) {
            if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
                $lastError = 'Error listing notebooks: ' . EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter;
            } else {
                $lastError = 'Error listing notebooks: ' . $e->getCode() . ": " . $e->getMessage();
            }
        } catch (EDAMNotFoundException $e) {
            if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
                $lastError = 'Error listing notebooks: ' . EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter;
            } else {
                $lastError = 'Error listing notebooks: ' . $e->getCode() . ": " . $e->getMessage();
            }
        } catch (Exception $e) {
            $lastError = 'Error listing notebooks: ' . $e->getMessage();
        }

        return FALSE;
    }

    /*
     * Reset the current session.
     */
    function resetSession()
    {
        if (isset($_SESSION['requestToken'])) {
            unset($_SESSION['requestToken']);
        }
        if (isset($_SESSION['requestTokenSecret'])) {
            unset($_SESSION['requestTokenSecret']);
        }
        if (isset($_SESSION['oauthVerifier'])) {
            unset($_SESSION['oauthVerifier']);
        }
        if (isset($_SESSION['accessToken'])) {
            unset($_SESSION['accessToken']);
        }
        if (isset($_SESSION['accessTokenSecret'])) {
            unset($_SESSION['accessTokenSecret']);
        }
        if (isset($_SESSION['noteStoreUrl'])) {
            unset($_SESSION['noteStoreUrl']);
        }
        if (isset($_SESSION['webApiUrlPrefix'])) {
            unset($_SESSION['webApiUrlPrefix']);
        }
        if (isset($_SESSION['tokenExpires'])) {
            unset($_SESSION['tokenExpires']);
        }
        if (isset($_SESSION['userId'])) {
            unset($_SESSION['userId']);
        }
        if (isset($_SESSION['notebooks'])) {
            unset($_SESSION['notebooks']);
        }
    }

    /*
     * Get the URL of this application. This URL is passed to the server (Evernote)
     * while obtaining unauthorized temporary credentials (step 1). The resource owner
     * is redirected to this URL after authorizing the temporary credentials (step 2).
     */
    function getCallbackUrl()
    {
        $thisUrl = (empty($_SERVER['HTTPS'])) ? "http://" : "https://";
        $thisUrl .= $_SERVER['SERVER_NAME'];
        $thisUrl .= ($_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443) ? "" : (":".$_SERVER['SERVER_PORT']);
        $thisUrl .= $_SERVER['SCRIPT_NAME'];
        $thisUrl .= '?action=callback';

        return $thisUrl;
    }

    /*
     * Get the Evernote server URL used to authorize unauthorized temporary credentials.
     */
    function getAuthorizationUrl()
    {
        $client = new Client(array(
            'consumerKey' => OAUTH_CONSUMER_KEY,
            'consumerSecret' => OAUTH_CONSUMER_SECRET,
            'sandbox' => SANDBOX
        ));

        return $client->getAuthorizeUrl($_SESSION['requestToken']);
    }
    
    function evernotepage($user)
    {
        //$s = new UserAttribute('test@epals.com');
         try{
        $s = new UserAttribute();
        $s->set_ElasticSearch_Server("apidev.dev.epals.com:9200");
        $s->set_SIS_Server("http://dev01.neuedu.dev.ec2.epals.net:8080/sis/");
        $s->loadUserAttribute($user."@epals.com");
        }catch(Exception $e)
        {
            echo 'error';
        }
        try{
        $s->get('oauthVerifier');
        }catch(Exception $e)
        {
            $s->add('oauthVerifier','');
        }
        try{
        $s->get('accessToken');
        }catch(Exception $e)
        {
            $s->add('accessToken','');
        }
        $ato = $s->get('accessToken');
        if(!$ato)
        {
            resetSession();
            getAccesstoken($user);
        }
        try {
            //$accessToken = $_SESSION['accessToken'];
            $accessToken = $ato;
            $client = new Client(array(
                'token' => $accessToken,
                'sandbox' => SANDBOX
            ));
            $notebooks = $client->getNoteStore()->listNotebooks();
            $result = array();
            if (!empty($notebooks)) {
                foreach ($notebooks as $notebook) {
                    $result[] = $notebook->name;
                }
            }
            $_SESSION['notebooks'] = $result;
            $currentStatus = 'Successfully listed content owner\'s notebooks';

            return TRUE;
        } catch (Exception $e) {
            resetSession();
            re_authorize();
        }
        
        exit;
        
    }
    
    function re_authorize()
    {
        global $lastError, $currentStatus;
        try {
            $client = new Client(array(
                'consumerKey' => OAUTH_CONSUMER_KEY,
                'consumerSecret' => OAUTH_CONSUMER_SECRET,
                'sandbox' => SANDBOX
            ));
            $requestTokenInfo = $client->getRequestToken(getCallbackUrl());
            if ($requestTokenInfo) {
                $_SESSION['requestToken'] = $requestTokenInfo['oauth_token'];
                $_SESSION['requestTokenSecret'] = $requestTokenInfo['oauth_token_secret'];
                $currentStatus = 'Obtained temporary credentials';

                
            } else {
                $lastError = 'Failed to obtain temporary credentials.';
            }
        } catch (OAuthException $e) {
            $lastError = 'Error obtaining temporary credentials: ' . $e->getMessage();
        }
        
        $s = $client->getAuthorizeUrl($_SESSION['requestToken']);
        header("Location: ".$s);
    }

    function handleCallback()
    {
        global $lastError, $currentStatus;
        if (isset($_GET['oauth_verifier'])) {
            $_SESSION['oauthVerifier'] = $_GET['oauth_verifier'];
            
            getTokenCredentials();

        } else {
            // If the User clicks "decline" instead of "authorize", no verification code is sent
            resetSession();
            re_authorize();

            return FALSE;
        }
    }
    
    function getTokenCredentials()
    {
        global $lastError, $currentStatus;
        $user = $_SESSION['test_user'];

        if (isset($_SESSION['accessToken'])) {
            $lastError = 'Temporary credentials may only be exchanged for token credentials once';

            return FALSE;
        }

        try {
            $client = new Client(array(
                'consumerKey' => OAUTH_CONSUMER_KEY,
                'consumerSecret' => OAUTH_CONSUMER_SECRET,
                'sandbox' => SANDBOX
            ));
            $accessTokenInfo = $client->getAccessToken($_SESSION['requestToken'], $_SESSION['requestTokenSecret'], $_SESSION['oauthVerifier']);
            if ($accessTokenInfo) {
                $_SESSION['accessToken'] = $accessTokenInfo['oauth_token'];
                $s = new UserAttribute();
                $s->set_ElasticSearch_Server("apidev.dev.epals.com:9200");
                $s->set_SIS_Server("http://dev01.neuedu.dev.ec2.epals.net:8080/sis/");
                $s->loadUserAttribute($user."@epals.com");
                $s ->add('accessToken', $accessTokenInfo['oauth_token']);
                //$s ->add('oauthVerifier', $_SESSION['oauthVerifier']);
                $currentStatus = 'Exchanged the authorized temporary credentials for token credentials';

                listNotebooks();
                
            } else {
                resetSession();
                re_authorize();
            }
        } catch (OAuthException $e) {
                resetSession();
                re_authorize();
        }

        return FALSE;
    }
    
    function getAccesstoken($user)
    {
        global $lastError, $currentStatus;
        $s = new UserAttribute();
        $s->set_ElasticSearch_Server("apidev.dev.epals.com:9200");
        $s->set_SIS_Server("http://dev01.neuedu.dev.ec2.epals.net:8080/sis/");
        $s->loadUserAttribute($user."@epals.com");
        $sa = $s->get('oauthVerifier');
        if(!$sa)
        {
            resetSession();
            re_authorize();
        }
        else
        {
        try {
            $client = new Client(array(
                'consumerKey' => OAUTH_CONSUMER_KEY,
                'consumerSecret' => OAUTH_CONSUMER_SECRET,
                'sandbox' => SANDBOX
            ));
            $requestTokenInfo = $client->getRequestToken(getCallbackUrl());
            if ($requestTokenInfo) {
                $_SESSION['requestToken'] = $requestTokenInfo['oauth_token'];
                $_SESSION['requestTokenSecret'] = $requestTokenInfo['oauth_token_secret'];
                $currentStatus = 'Obtained temporary credentials';

                
            } else {
                $lastError = 'Failed to obtain temporary credentials.';
            }
        } catch (OAuthException $e) {
            $lastError = 'Error obtaining temporary credentials: ' . $e->getMessage();
        }
        
        try {
            $accessTokenInfo = $client->getAccessToken($_SESSION['requestToken'], $_SESSION['requestTokenSecret'], $sa);
            if ($accessTokenInfo) {
                $_SESSION['accessToken'] = $accessTokenInfo['oauth_token'];

                $s ->add('accessToken', $accessTokenInfo['oauth_token']);
                $currentStatus = 'Exchanged the authorized temporary credentials for token credentials';

                listNotebooks();
                
            } else {
                resetSession();
                re_authorize();
            }
        } catch (OAuthException $e) {
                resetSession();
                re_authorize();
        }
        }
        
    }
    
    function delete_revoke($user){
        
        $s = new UserAttribute();
        $s->set_ElasticSearch_Server("apidev.dev.epals.com:9200");
        $s->set_SIS_Server("http://dev01.neuedu.dev.ec2.epals.net:8080/sis/");
        $s->loadUserAttribute($user."@epals.com");
        $s -> add('accessToken','');
        resetSession();
    }
    
    function showUserAttr(){
        
        $s = new UserAttribute();
        $s->set_ElasticSearch_Server("apidev.dev.epals.com:9200");
        $s->set_SIS_Server("http://dev01.neuedu.dev.ec2.epals.net:8080/sis/");
        $s->loadUserAttribute("chytest1@epals.com");
        //$s -> add('accessToken','');
        print_r($s -> getAll());
    }
