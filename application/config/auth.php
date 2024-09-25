<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

// Array or a string of authentication methods sorted by priority (highest to lowest)
// NOTE: AUTH_HBALDAP works also as login page (old ugly HTTP basic authentication)
//		should be placed at the end of the array
$config['authentication_foreign_methods'] = array(AUTH_BT, AUTH_HBALDAP);

// Login method
$config['authentication_login'] = AUTH_LDAP;

// Array of login pages
$config['authentication_login_pages'] = array(
	AUTH_DB => '/system/Login/emailCode',
	AUTH_LDAP => '/system/Login/usernamePassword',
	AUTH_SSO => '/system/Login/sso'
);

// Logout page
$config['authentication_logout_page'] = '/system/Logout';
