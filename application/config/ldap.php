<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

defined('LDAP_SERVER') OR require_once './config/system.config.inc.php'; // LDAP configs

$ldap_active_group = 'development';

$ldap['development'] = array(); // development LDAP configs

if (defined('LDAP_SERVER')) // 1st LDAP server
{
	$ldap['development'][] = array(
		'server'	=> LDAP_SERVER,
		'port'		=> LDAP_PORT,
		'starttls'	=> LDAP_STARTTLS,
		'basedn'	=> LDAP_BASE_DN,
		'username'	=> LDAP_BIND_USER,
		'password'	=> LDAP_BIND_PASSWORD,
		'usf'		=> LDAP_USER_SEARCH_FILTER,
		'timeout'	=> 1
	);
}

if (defined('LDAP2_SERVER')) // 2nd LDAP server
{
	$ldap['development'][] = array(
		'server'	=> LDAP2_SERVER,
		'port'		=> LDAP2_PORT,
		'starttls'	=> LDAP2_STARTTLS,
		'basedn'	=> LDAP2_BASE_DN,
		'username'	=> LDAP2_BIND_USER,
		'password'	=> LDAP2_BIND_PASSWORD,
		'usf'		=> LDAP2_USER_SEARCH_FILTER,
		'timeout'	=> 1
	);
}

$ldap['production'] = array(); // Live LDAP configs

if (defined('LDAP_SERVER')) // 1st LDAP server
{
	$ldap['production'][] = array(
		'server'	=> LDAP_SERVER,
		'port'		=> LDAP_PORT,
		'starttls'	=> LDAP_STARTTLS,
		'basedn'	=> LDAP_BASE_DN,
		'username'	=> LDAP_BIND_USER,
		'password'	=> LDAP_BIND_PASSWORD,
		'usf'		=> LDAP_USER_SEARCH_FILTER,
		'timeout'	=> 1
	);
}

if (defined('LDAP2_SERVER')) // 2nd LDAP server
{
	$ldap['production'][] = array(
		'server'	=> LDAP2_SERVER,
		'port'		=> LDAP2_PORT,
		'starttls'	=> LDAP2_STARTTLS,
		'basedn'	=> LDAP2_BASE_DN,
		'username'	=> LDAP2_BIND_USER,
		'password'	=> LDAP2_BIND_PASSWORD,
		'usf'		=> LDAP2_USER_SEARCH_FILTER,
		'timeout'	=> 1
	);
}

