<?php

    /*
     * Copyright 2010-2012 Evernote Corporation.
     *
     * This sample web application demonstrates the step-by-step process of using OAuth to
     * authenticate to the Evernote web service. More information can be found in the
     * Evernote API Overview at http://dev.evernote.com/documentation/cloud/.
     *
     * This application uses the PHP OAuth Extension to implement an OAuth client.
     * To use the application, you must install the PHP OAuth Extension as described
     * in the extension's documentation: http://www.php.net/manual/en/book.oauth.php
     *
     * Note that the formalization of OAuth as RFC 5849 introduced some terminology changes.
     * The comments in this sample code use the new (RFC) terminology, but most of the code
     * itself still uses the old terms, which are also used by the PHP OAuth Extension.
     *
     * Old term                    New Term
     * --------------------------------------------------
     * Consumer                    client
     * Service Provider            server
     * User                        resource owner
     * Consumer Key and Secret     client credentials
     * Request Token and Secret    temporary credentials
     * Access Token and Secret     token credentials
     */

    require_once __DIR__."/vendor/autoload.php";
    require_once __DIR__.'/config.php';
    require_once 'functions.php';

    use EDAM\Types\Data, EDAM\Types\Note, EDAM\Types\Notebook, EDAM\Types\Resource, EDAM\Types\ResourceAttributes,
      EDAM\NoteStore;
    use EDAM\Error\EDAMUserException, EDAM\Error\EDAMErrorCode;
    use Evernote\Client;
   
    //$everInterfaceBasePath = dirname(__DIR__);
    //require_once $everInterfaceBasePath.'/evercase/vendor/epals/epalsapi/src/ePals/UserAttribute.php';

    // Use a session to keep track of temporary credentials, etc
    session_start();

    // Status variables
    $lastError = null;
    $currentStatus = null;
    if($_GET['username'])
    {
    $_SESSION['test_user'] = $_GET['username'];
    }
    // Request dispatching. If a function fails, $lastError will be updated.
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        if ($action == 'requestToken') {
            getTemporaryCredentials();
        } elseif ($action == 'callback') {
            handleCallback();
        } elseif ($action == 'accessToken') {
            getTokenCredentials();
        } elseif ($action == 'listNotebooks') {
            listNotebooks();
        } elseif ($action == 'reset') {
            resetSession();
        } elseif ($action == 'test') {
            evernotepage($_GET['username']);
        } elseif ($action == 'deleteRevoke'){
            delete_revoke($_GET['username']);
        }
    }
?>
<script type="text/javascript" src="jquery-1.11.0.min.js"></script>
<html>
    <head>
        <title>Evernote PHP OAuth Demo</title>
    </head>
    <body>
        <h1>Evernote PHP OAuth Demo</h1>

        <p>
            This application demonstrates the use of OAuth to authenticate to the Evernote web service.
            OAuth support is implemented using the <a href="http://www.php.net/manual/en/book.oauth.php">PHP OAuth Extension</a>.
        </p>

        <p>
            On this page, we demonstrate each step of the OAuth authentication process.
            You would not typically expose a user to this level of detail.
            <a href="sampleApp.php?action=reset">Click here</a> to use an application that is more similar to what you would use in the real world.
        </p>

        <hr/>

        <h2>Authentication Steps</h2>
        
        <ul>
            <li>
                UserAttribute of User:
                <input type="text" id="username" value="<?php echo $_SESSION['test_user']?>">
                @epals.com
                <input type="button" onclick="oauth()" value="authorize">
            </li>
            <li>
                
            </li>
        </ul>

        <ul>

            <!-- Step 1: get temporary credentials -->
            <li><b>Step 1</b>:
<?php if (!isset($_SESSION['requestToken'])) { ?>
                <a href="testOauth.php?action=requestToken">Click here</a> to
<?php } ?>
                obtain temporary credentials
            </li>

            <!-- Step 2: authorize the temporary credentials -->
            <li><b>Step 2</b>:
<?php if (isset($_SESSION['requestToken']) && !isset($_SESSION['oauthVerifier'])) { ?>
                <a href="<?php echo htmlspecialchars(getAuthorizationUrl()); ?>">Click here</a> to
<?php } ?>
                authorize the temporary credentials
            </li>

            <!-- Step 3: exchange the authorized temporary credentials for token credentials -->
            <li><b>Step 3</b>:
<?php if (isset($_SESSION['requestToken']) && isset($_SESSION['oauthVerifier']) && !isset($_SESSION['accessToken'])) { ?>
                <a href="testOauth.php?action=accessToken">Click here</a> to
<?php } ?>
                exchange the authorized temporary credentials for token credentials
            </li>

            <!-- Step 4: demonstrate using the token credentials to access the user's account -->
            <li><b>Step 4</b>:
<?php if (isset($_SESSION['accessToken']) && !isset($_SESSION['notebooks'])) { ?>
                <a href="testOauth.php?action=listNotebooks">Click here</a> to
<?php } ?>
                list all notebooks in the authorizing user's Evernote account
            </li>

        </ul>

        <p>
            <a href="testOauth.php?action=reset">Click here</a> to start over
        </p>
        
        <p>
            <a href="#" onclick="deletet()">Click here</a> to delete token in UserAttribule and re_authorize 
        </p>

        <hr/>

        <h2>Current status</h2>
        <p>
            <b>Evernote server:</b> <?php echo htmlspecialchars(SANDBOX ? 'sandbox' : 'production'); ?>
            <br/>
            <b>Last action:</b>
<?php
    if (!empty($lastError)) {
        echo '<span style="color:red">' . htmlspecialchars($lastError) . '</span>';
    } else {
        echo '<span style="color:green">' . htmlspecialchars($currentStatus) . '</span>';
    }
?>
        </p>

<?php if (isset($_SESSION['notebooks'])) { ?>
        <b>Notebooks:</b>
        <ul>
<?php foreach ($_SESSION['notebooks'] as $notebook) { ?>
            <li>
    <?php echo htmlspecialchars($notebook); ?>
            </li>
    <?php } ?>
        </ul>
<?php } ?>

        <b>Temporary credentials:</b>
        <ul>
            <li><b>Identifier:</b><br/>
<?php if (isset($_SESSION['requestToken'])) { echo htmlspecialchars($_SESSION['requestToken']); } ?>
            </li>
            <li><b>Secret:</b><br/>
<?php if (isset($_SESSION['requestTokenSecret'])) { echo htmlspecialchars($_SESSION['requestTokenSecret']); } ?>
            </li>
        </ul>

        <p>
            <b>OAuth verifier:</b><br/>
<?php if (isset($_SESSION['oauthVerifier'])) { echo htmlspecialchars($_SESSION['oauthVerifier']); } ?>
        </p>

        <b>Token credentials:</b>
        <ul>
            <li><b>Identifier:</b><br/>
<?php if (isset($_SESSION['accessToken'])) { echo htmlspecialchars($_SESSION['accessToken']); } ?>
            </li>
            <li><b>Secret:</b><br/>
<?php if (isset($_SESSION['accessTokenSecret'])) { echo htmlspecialchars($_SESSION['accessTokenSecret']); } ?>
            </li>
            <li><b>User ID:</b><br/>
<?php if (isset($_SESSION['userId'])) { echo htmlspecialchars($_SESSION['userId']); } ?>
            </li>
            <li><b>Expires:</b><br/>
<?php if (isset($_SESSION['tokenExpires'])) { echo htmlspecialchars(date(DATE_RFC1123, $_SESSION['tokenExpires'])); } ?>
            </li>
        </ul>

    </body>
</html>

<script>
    function oauth()
    {
        var username = $("#username").val();
        window.location.href="?action=test&username="+username; 
    }
    
    function deletet()
    {
        var username = $("#username").val();
        window.location.href="?action=deleteRevoke&username="+username; 
    }
</script>