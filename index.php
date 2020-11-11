<?php

// Sandbox Versions

$authorize_url = "https://sandbox-api.digikey.com/v1/oauth2/authorize";
$token_url = "https://sandbox-api.digikey.com/v1/oauth2/token";

$test_api_url = "https://sandbox-api.digikey.com/Search/v3/Products/Keyword";
$test_api_url2 = "https://sandbox-api.digikey.com/Search/v3/Products/";

//	client (application) credentials 
$client_id = "";
$client_secret = "";

// Production Versions
/*
$authorize_url = "https://api.digikey.com/v1/oauth2/authorize";
$token_url = "https://api.digikey.com/v1/oauth2/token";

$test_api_url = "https://api.digikey.com/Search/v3/Products/Keyword";
$test_api_url2 = "https://api.digikey.com/Search/v3/Products/";

//	client (application) credentials 
$client_id = "";
$client_secret = ""; 
*/


//	callback URL specified when the application was defined--has to match what the application says
$callback_uri = "https://localhost/DIGIKEY/index.php";



//	step A - simulate a request from a browser on the authorize_url
//		will return an authorization code after the user is prompted for credentials
function getAuthorizationCode() 
{
    global $authorize_url, $client_id, $callback_uri;

    $authorization_redirect_url = $authorize_url . "?response_type=code&client_id=" . $client_id . "&redirect_uri=" . $callback_uri . "&scope=openid";
    header("Location: " . $authorization_redirect_url);
}


function getAccessToken($authorization_code) 
{
    global $token_url, $client_id, $client_secret, $callback_uri;

    $header = array("Content-Type: application/x-www-form-urlencoded");
    $content = "grant_type=authorization_code&code=$authorization_code&redirect_uri=$callback_uri&client_id=$client_id&client_secret=$client_secret";

    $curl = curl_init();
    curl_setopt_array($curl, array(
    	CURLOPT_URL => $token_url,
	CURLOPT_HTTPHEADER => $header,
	CURLOPT_SSL_VERIFYPEER => false,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_POST => true,
	CURLOPT_POSTFIELDS => $content
	));
    $response = curl_exec($curl);
    curl_close($curl);

    if ($response === false) {
        echo "Failed";
        echo curl_error($curl);
        return ''; }
                
    $myResp = json_decode($response);
    if (isset($myRest->error)) {
        echo "Error:<br />";
        echo $response; 
        return '';  }

    $access_tokens[0] = $myResp->access_token;
    $access_tokens[1] = $myResp->refresh_token;
    
    return $access_tokens;
}


function refreshAccessToken($refresh_token) 
{
    global $token_url, $client_id, $client_secret;

    $header = array("Content-Type: application/x-www-form-urlencoded");
    $content = "grant_type=refresh_token&refresh_token=$refresh_token&client_id=$client_id&client_secret=$client_secret";

    $curl = curl_init();
    curl_setopt_array($curl, array(
	CURLOPT_URL => $token_url,
	CURLOPT_HTTPHEADER => $header,
	CURLOPT_SSL_VERIFYPEER => false,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_POST => true,
	CURLOPT_POSTFIELDS => $content
	));
    $response = curl_exec($curl);
    curl_close($curl);

    if ($response === false) {
        echo "Failed";
        echo curl_error($curl);
        return ''; }
                
    $myResp = json_decode($response);
    if (isset($myRest->error)) {
        echo "Error:<br />";
        echo $response; 
        return '';  }

    $access_tokens[0] = $myResp->access_token;
    $accesss_tokens[1] = $myResp->refresh_token;
    return $access_tokens;
}


function getResource($access_token) 
{
    global $client_id, $test_api_url;

    $header = array("Authorization: Bearer {$access_token}", "X-DIGIKEY-Client-Id: $client_id", "Content-Type: application/json");
    $myArray = array('Keywords' => 'CC0402KRX5R8BB224', 'RecordCount'=>10);
    $content = json_encode($myArray);
        
    $curl = curl_init();
    curl_setopt_array($curl, array(
	CURLOPT_URL => $test_api_url,
	CURLOPT_HTTPHEADER => $header,
	CURLOPT_SSL_VERIFYPEER => false,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_POST => true,
	CURLOPT_POSTFIELDS => $content
	));
    $response = curl_exec($curl);
    curl_close($curl);

    $myResp = json_decode($response, true);
    if (isset($myResp['ErrorMessage'])) echo $myResp['ErrorMessage'];
    else echo '<br>Request Keyword OK';
}


function getResource2($access_token) 
{
    global $client_id, $test_api_url2;

    $header = array("Authorization: Bearer {$access_token}", "X-DIGIKEY-Client-Id: $client_id", "Content-Type: application/json");
    $myURL = $test_api_url2."CC0402KRX5R8BB224";
        
    $curl = curl_init();
    curl_setopt_array($curl, array(
	CURLOPT_URL => $myURL,
	CURLOPT_HTTPHEADER => $header,
	CURLOPT_SSL_VERIFYPEER => false,
	CURLOPT_RETURNTRANSFER => true
	));
    $response = curl_exec($curl);
    curl_close($curl);

    $myResp = json_decode($response, true);
    if (isset($myResp['ErrorMessage'])) echo $myResp['ErrorMessage'];
    else echo '<br>Request Product OK';
}

if ($_GET["code"]) 
{
    $access_tokens = getAccessToken($_GET["code"]);
    if (!empty($access_tokens)) {
        echo 'Access Token Obtained:'.$access_tokens[0];
        if (!empty($retval)) echo '<br>'.$retval; }
    getResource($access_tokens[0]);
    getResource2($access_tokens[0]);
    $access_tokens = refreshAccessToken($access_tokens[1]); 
} 
else getAuthorizationCode();


