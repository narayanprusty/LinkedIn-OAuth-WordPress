<?php

session_start();

function linkedin_oauth_redirect()
{
	global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;
	require_once("../wp-load.php");
	require_once('LinkedIn/LinkedIn.php');
	//construct URL and redirect
	$app_key = get_option("linkedin_app_key");
	$app_secret = get_option("linkedin_app_secret");
	$callback_url = get_site_url() . "/wp-admin/admin-ajax.php?action=linkedin_oauth_callback";

	$li = new LinkedIn(array('api_key' => $app_key, 'api_secret' => $app_secret, 'callback_url' => $callback_url));

	$url = $li->getLoginUrl(array(LinkedIn::SCOPE_FULL_PROFILE, LinkedIn::SCOPE_EMAIL_ADDRESS, LinkedIn::SCOPE_NETWORK, LinkedIn::SCOPE_CONTACT_INFO, LinkedIn::SCOPE_READ_WRTIE_UPDATES, LinkedIn::SCOPE_READ_WRITE_GROUPS, LinkedIn::SCOPE_WRITE_MESSAGES));

	header("Location: " . $url); 

	die();
}

add_action("wp_ajax_linkedin_oauth_redirect", "linkedin_oauth_redirect");
add_action("wp_ajax_nopriv_linkedin_oauth_redirect", "linkedin_oauth_redirect");

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function linkedin_oauth_callback()
{
	global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;
	require_once("../wp-load.php");
	require_once('LinkedIn/LinkedIn.php');

	$app_key = get_option("linkedin_app_key");
	$app_secret = get_option("linkedin_app_secret");
	$callback_url = get_site_url() . "/wp-admin/admin-ajax.php?action=linkedin_oauth_callback";

	$li = new LinkedIn(array('api_key' => $app_key, 'api_secret' => $app_secret, 'callback_url' => $callback_url));

	$token = $li->getAccessToken($_REQUEST['code']);

	$token_expires = $li->getAccessTokenExpiration();

	$info = $li->get('/people/~:(email-address)');

	$email = $info["emailAddress"];

	if(email_exists($email)) 
	{
      	$user_id = email_exists($email);
      	wp_set_auth_cookie($user_id);
      	update_user_meta($user_id, "linkedin_access_token", $token);
		header('Location: ' . get_site_url());
   	}
   	else
   	{
   		wp_create_user($email, generateRandomString(), $email);
		$user_id = email_exists($email);
		wp_set_auth_cookie($user_id);
		update_user_meta($user_id, "linkedin_access_token", $token);
		header('Location: ' . get_site_url());
   	}
	
    die();
}

add_action("wp_ajax_linkedin_oauth_callback", "linkedin_oauth_callback");
add_action("wp_ajax_nopriv_linkedin_oauth_callback", "linkedin_oauth_callback");