<?php

function getParam($name, $default = null)
{
	if (php_sapi_name() === 'cli') { // Parse CLI args for --key=value style
		// php ./system/UnitTests/api/BookmarkTest/BookmarkTest.php
		// --server=https://cis40.dev.technikum-wien.at --user=if23b236 --pw=FHCompleteDemo42!

		global $argv;
		foreach ($argv as $arg) {
			if (strpos($arg, '--' . $name . '=') === 0) {
				return substr($arg, strlen($name) + 3);
			}
		}
		return $default;
	} else {// Browser: use $_GET
		// https://c3p0.ma0646.technikum-wien.at/fhcompletecis4/system/UnitTests/api/BookmarkTest
		// /BookmarkTest.php?server=https://c3p0.ma0646.technikum-wien.at&user=if23b236&pw=FHCompleteDemo42!
		return isset($_GET[$name]) ? $_GET[$name] : $default;
	}
}

define('IS_CLI', php_sapi_name() === 'cli');
define('LINE_BREAK', IS_CLI ? PHP_EOL : '<br>');
define('PROJECT_ROOT', realpath(__DIR__ . '/../../../../'));

echo "Test Suite Bookmark start".LINE_BREAK;
if (!IS_CLI) echo "<pre>";
require_once(PROJECT_ROOT . '/config/cis.config.inc.php');
require_once(PROJECT_ROOT . '/vendor/nategood/httpful/bootstrap.php');
require_once(PROJECT_ROOT . '/system/UnitTests/AssertionHelpers.php');
echo "Requirements loaded".LINE_BREAK;

$server = getParam('server', APP_ROOT);
echo $server.LINE_BREAK;
$TEST_USER = getParam('user', 'defaultuser'); //if23b236
echo $TEST_USER.LINE_BREAK;
$TEST_PW = getParam('pw', 'defaultpass'); //FHCompleteDemo42!
echo $TEST_PW.LINE_BREAK;

// "Unit Test" Script to Test API Controller frontend/v1/Bookmark.php by calling methods with curated inputs and checking
// for the expected output
$URL = $server.'/cis.php/api/frontend/v1/Bookmark/';

testGetBookmarks($URL, 'getBookmarks', $TEST_USER, $TEST_PW);
$id = testInsertBookmark($URL, 'insert', $TEST_USER, $TEST_PW);
$id = testUpdateBookmark($URL, 'update', $TEST_USER, $TEST_PW, $id);
testDeleteBookmark($URL, 'delete', $TEST_USER, $TEST_PW, $id);
if (!IS_CLI) echo "<pre>";

function testGetBookmarks($url, $method, $user, $pw)
{
	echo LINE_BREAK.LINE_BREAK."Test '".$method."' start ".LINE_BREAK;

	try {
		$resultPost = \Httpful\Request::get($url.$method)
			->expectsJson()
			->authenticateWith($user, $pw)
			->send();
	} catch(\Httpful\Exception\ConnectionErrorException $cee) // Httpful exception
	{
		echo $cee;
	}
	catch (Exception $e) // any other exception
	{
		echo $e;
	}
	
	$assertions = [];
	
	$assertions[] = assertIsArray($resultPost->body->data);
	$assertions[] = assertIsString($resultPost->body->meta->status);
	$assertions[] = assertEqual($resultPost->body->meta->status, "success", "Response Status Success");

	if(allTrue($assertions)) {
		echo "Test '".$method."' finished SUCCESS".LINE_BREAK;
	} else {
		echo "Test '".$method."' finished FAIL".LINE_BREAK;
		printResponse($resultPost);
	}
}

function testInsertBookmark($url, $method, $user, $pw)
{
	echo LINE_BREAK.LINE_BREAK."Test '".$method."' start ".LINE_BREAK;
	echo LINE_BREAK;
	try {
		$bodyTitle = 'orf';
		$bodyUrl = 'https://orf.at';
		
		$resultPost = \Httpful\Request::post($url.$method)
			->expectsJson()
			->authenticateWith($user, $pw)
			->sendsJson()
			->body('{"title": "'.$bodyTitle.'", "url": "'.$bodyUrl.'"}')
			->send();
	} catch(\Httpful\Exception\ConnectionErrorException $cee) // Httpful exception
	{
		echo $cee;
	}
	catch (Exception $e) // any other exception
	{
		echo $e;
	}

	$assertions = [];
	$assertions[] = assertIsInt($resultPost->body->data);
	$assertions[] = assertIsString($resultPost->body->meta->status);
	$assertions[] = assertEqual("success", $resultPost->body->meta->status, "Response Status Success");
	if(allTrue($assertions)) {
		echo "Test '".$method."' finished SUCCESS".LINE_BREAK;
	} else {
		echo "Test '".$method."' finished FAIL".LINE_BREAK;
		printResponse($resultPost);
	}

	return $resultPost->body->data;
}

function testDeleteBookmark($url, $method, $user, $pw, $id)
{
	echo LINE_BREAK.LINE_BREAK."Test '".$method."' start \n".LINE_BREAK;
	
	try {
		$resultPost = \Httpful\Request::post($url.$method.'/'.$id)
			->expectsJson()
			->authenticateWith($user, $pw)
			->sendsJson()
			->send();
	} catch(\Httpful\Exception\ConnectionErrorException $cee) // Httpful exception
	{
		echo $cee;
	}
	catch (Exception $e) // any other exception
	{
		echo $e;
	}

	$assertions = [];
	$assertions[] = assertIsString($resultPost->body->data);
	$assertions[] = assertIsString($resultPost->body->meta->status);
	$assertions[] = assertEqual("success", $resultPost->body->meta->status, "Response Status Success");
	if(allTrue($assertions)) {
		echo "Test '".$method."' finished SUCCESS".LINE_BREAK;
	} else {
		echo "Test '".$method."' finished FAIL".LINE_BREAK;
		printResponse($resultPost);
	}
}

function testUpdateBookmark($url, $method, $user, $pw, $id)
{
	echo LINE_BREAK.LINE_BREAK."Test '".$method."' start ".LINE_BREAK;

	try {
		$bodyTitle = 'orf title updated';
		$bodyUrl = 'https://orf.at';
		
		$resultPost = \Httpful\Request::post($url.$method.'/'.$id)
			->expectsJson()
			->authenticateWith($user, $pw)
			->body('{"title": "'.$bodyTitle.'", "url": "'.$bodyUrl.'"}')
			->sendsJson()
			->send();
	} catch(\Httpful\Exception\ConnectionErrorException $cee) // Httpful exception
	{
		echo $cee;
	}
	catch (Exception $e) // any other exception
	{
		echo $e;
	}

	$assertions = [];
	$assertions[] = assertIsString($resultPost->body->data);
	$assertions[] = assertIsString($resultPost->body->meta->status);
	$assertions[] = assertEqual("success", $resultPost->body->meta->status, "Response Status Success");
	if(allTrue($assertions)) {
		echo "Test '".$method."' finished SUCCESS".LINE_BREAK;
	} else {
		echo "Test '".$method."' finished FAIL".LINE_BREAK;
		printResponse($resultPost);
	}
	
	return $resultPost->body->data;
}

function printResponse($resultPost)
{
	echo LINE_BREAK;
	echo "Response Body:";
	echo preFormat(var_export($resultPost->body, true));
	echo LINE_BREAK;
	echo "Raw Response:";
	echo preFormat(var_export($resultPost->raw_body, true));
	echo LINE_BREAK;
	echo "Status Code:";
	echo preFormat(var_export($resultPost->code, true));
	echo LINE_BREAK;
	echo "Headers:";
	echo preFormat(var_export($resultPost->headers, true));
	echo LINE_BREAK;
}

function allTrue($arr)
{
	return count(array_filter($arr, function ($v) {
		return $v === true;
	})) === count($arr);
}




