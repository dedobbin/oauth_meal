<?php 

function apiRequest($url, $post=False) 
{
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

	if ($post){
		curl_setopt($ch, CURLOPT_POST, http_build_query($post));
	}

	$headers = [
		'Accept: application/vnd.github.v3+json, application/json',
		'User-Agent: https://example-app.com/'
	];

	if (isset($_SESSION['access_token'])){
		$headers[] = 'Authorization: Bearer ' . $_SESSION['access_token'];
	}

	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$response = curl_exec($ch);
	if (curl_errno($ch)){
		error_log("Failed request to: " . $url . "<br/>Reason: " . curl_error($ch));
	}
	else if (!$response){
		error_log("No response from " . $url);
	}
	curl_close($ch);
	return json_decode($response, true);
}

function gotoUrl($url)
{
	header('Location:' . $url);
	die();
}

function env($key, $strict=true)
{
	$lines = file('.env');
	if ($lines === false){
		die('Failed to read .env file');
	}
	foreach($lines as $line){
		if (strpos($line, $key) !== false){
			$pair = explode('=', $line);
			$value = $pair[1];
		}
	}
	if (!isset($value)){
		error_log('env: "' . $key . '" not found.');
		$value= "";
	}
	$value = trim($value);
	return $value;
}

function dump_die($data)
{
	var_dump($data);
	die();
}