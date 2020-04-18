<?php

include_once('helpers.php');

function renderView($name, $data = [])
{
	if ($name == 'loggedin'){
		echo '<h3> LOGGED IN </h3>';
		echo '<p> <a href="?action=repos">view repos</a></p>';
		echo '<p><a href="?action=logout">log out</a></p>';
	} else if ($name == 'loggedout'){
		echo '<h3> NOT LOGGED IN </h3>';
		echo '<p><a href="?action=login">log in</a></p>';
	} else if ($name == 'repos'){
		echo '<h3> REPOS </h3>';
		echo '<ul>';
		foreach($data['repos'] as $repo){
			echo '<li><a href=' . $repo['html_url'] . '>' . $repo['name'] . '</a></li>';
		}
		echo '</ul>';
	}
}

$authorizeUrl = 'https://github.com/login/oauth/authorize';
$tokenUrl = 'https://github.com/login/oauth/access_token';
$apiUrlBase = 'https://api.github.com';
$baseUrl = 'http://' . $_SERVER['SERVER_NAME'] . ':' .$_SERVER['SERVER_PORT'];
$githubClientId=env('client_id');
$githubClientSecret=env('client_secret');

session_start();

//todo: functions etc to make upcoming mess more readable

if (isset($_GET['code'])){
	//Return from github after authentication
	if (!$_GET['state'] == $_SESSION['state']){
		die('incorrect state');
	}
	//Exchange auth code for access token
	$url = $tokenUrl .  '?' . http_build_query([
		'grant_type' => 'authorization_code',
		'client_id' => $githubClientId,
		'client_secret' => $githubClientSecret,
		// 'redirect_uri' => $baseURL,
		'code' => $_GET['code']
	]);
	$response = apiRequest($url);
	$_SESSION['access_token'] = $response['access_token'];
	gotoUrl($baseUrl);
}

if (!isset($_GET['action'])){
	if (!empty($_SESSION['access_token'])){
		renderView('loggedin');
	} else {
		renderview('loggedout');
	}
	die();
} 

//Login click, redirect to github repo
if (isset($_GET['action']) ){
	if ($_GET['action'] == 'login'){
		unset($_SESSION['access_token']);

		$_SESSION['state'] = bin2hex(random_bytes(16));

		$params = [
			'client_id' => $githubClientId,
			//'redirect_uri' => $baseURL,
			//'scope' => 'user:public_repo',
			'scope' => 'user',
			'state' => $_SESSION['state']
		];
		$url = $authorizeUrl. '?' .http_build_query($params);
		gotoUrl($url);
	} else if ($_GET['action'] == 'logout'){
		unset($_SESSION['access_token']);
		gotoUrl($baseUrl);
	} else if ($_GET['action'] == 'repos'){
		$response = apiRequest($apiUrlBase.'/user/repos?'.http_build_query([
			'sort' => 'created', 'direction' => 'desc'
		]));
		
		$data['repos'] = $response;
		renderView('repos', $data);
	}
}