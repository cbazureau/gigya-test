<?php
    // ------------------------
    // CONFIGURATION
    // ------------------------
    $ROOT_PATH = "https://fidm.eu1.gigya.com/oidc/op/v1.0/3_A0ls8LHd13QJi7KaQJrj_m94Lk6tlV6ZWuxdAFua3Rizk_81LrpRbYMMuumTKLMd";
    $CLIENT_ID = "CZWEEY3dYhTc69nOYxoxvs9O";
    $CLIENT_SECRET = "<CLIENT_SECRET>";
    $REDIRECT_URL = "https://www.lebeaujeu.com/gigya-oidc/partner.php";
    $SCOPE = "openid+email+personId";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[OIDC] Partner Page</title>
    </head>
<body>
    <h1>[OIDC] Partner Page</h1>

<?php

/**
 * getToken
 */
function getToken() {
	$url = $GLOBALS["ROOT_PATH"]."/token";
	$authHeader="Basic ";
	$hashedAuthString=base64_encode($GLOBALS["CLIENT_ID"] . ":" . $GLOBALS["CLIENT_SECRET"]);
	$authHeader .= $hashedAuthString;
	$fields = array(
		'code' => $_GET['code'],
		'grant_type' => 'authorization_code',
		'redirect_uri' => $GLOBALS["REDIRECT_URL"]
	);
	$postvars='';
	$sep='';
	foreach($fields as $key=>$value)
	{
		$postvars.= $sep.urlencode($key).'='.urlencode($value);
		$sep='&';
	}
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: ' . $authHeader));
	curl_setopt($ch,CURLOPT_POST,count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS,$postvars);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	$result = curl_exec($ch);
	curl_close($ch);
	$json = json_decode($result, true);
    if(empty($json['access_token'])) {
        print_r($json);
        return "";
    }
    return $json['access_token'];
}

/**
 * getUserInfo
 */
function getUserInfo($accessToken) {
	$url = $GLOBALS["ROOT_PATH"]."/userinfo";
	$ch = curl_init();
    $bearAccessToken="Bearer " . $accessToken;
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: ' . $bearAccessToken));
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	$resultUI = curl_exec($ch);
	curl_close($ch);
	$userInfojson = json_decode($resultUI, true);
    print_r($userInfojson);
}

if($_GET['code']) {
    echo '<pre>';
    $access_token = getToken();
    if(empty($access_token)) {
        echo "Can't retreive an access_token\n"; 
    }
    else {
        getUserInfo($access_token);
    } 
    echo '</pre>';

} else {
    ?>
    <script>
        function loginPage() {
            const url = new URL("<?php echo $ROOT_PATH; ?>/authorize");
            url.searchParams.set('ui_locales', 'ro-RO')
            url.searchParams.set('client_id', '<?php echo $CLIENT_ID; ?>')
            url.searchParams.set('redirect_uri', '<?php echo $REDIRECT_URL; ?>')
            url.searchParams.set('response_type', 'code')
            url.searchParams.set('response_mode', 'login')
            url.searchParams.set('nonce', Math.random())
            // url.searchParams.set('scope', '<?php echo $SCOPE; ?>') // can't do that due to '+' char encoding
            window.location.href = url.toString()+'&scope=<?php echo $SCOPE; ?>';
        }
    </script>
    <div>
        <ul>
            <li><a href="#" onclick="javascript:loginPage()">Login with IdConnect</a></li>
        </ul>
    </div>

<?php } ?>

</body>
</html>