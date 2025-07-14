<?php

echo "Test Suite Bookmark start \r\n";
echo "<br>";

require_once(dirname(__FILE__).'/../../../../config/cis.config.inc.php');
require_once(dirname(__FILE__).'/../../../../vendor/nategood/httpful/bootstrap.php');
require_once(dirname(__FILE__).'/../../AssertionHelpers.php');

echo "Requirements loaded \r\n";
echo "<br>";

// TODO: parameterize for different user setups
$TEST_USER = 'if23b236';
$TEST_PW = 'FHCompleteDemo42!';

// "Unit Test" Script to Test API Controller frontend/v1/Bookmark.php by calling methods with curated inputs and checking
// for the expected output
$ROOT = APP_ROOT; // calls itself -> TODO: switch for other machines
//$ROOT = 'https://ci.dev.technikum-wien.at/';
//$ROOT = 'https://cis40.dev.technikum-wien.at/';
$URL = $ROOT.'cis.php/api/frontend/v1/Bookmark/'; 

testGetBookmarks($URL, 'getBookmarks', $TEST_USER, $TEST_PW);
$id = testInsertBookmark($URL, 'insert',  $TEST_USER, $TEST_PW);
$id = testUpdateBookmark($URL, 'update',  $TEST_USER, $TEST_PW, $id);
testDeleteBookmark($URL, 'delete',  $TEST_USER, $TEST_PW, $id);

function testGetBookmarks($url, $method, $user, $pw) {
	echo "<br><br>Test '".$method."' start \r\n";
	echo "<br>";
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
		echo "Test '".$method."' finished SUCCESS<br>";
	} else {
		echo "Test '".$method."' finished FAIL<br>";
		printResponse($resultPost);
	}
}

function testInsertBookmark($url, $method, $user, $pw) {
	echo "<br><br>Test '".$method."' start \r\n";
	echo "<br>";
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
	$assertions[] = assertEqual("success",$resultPost->body->meta->status, "Response Status Success");
	if(allTrue($assertions)) {
		echo "Test '".$method."' finished SUCCESS<br>";
	} else {
		echo "Test '".$method."' finished FAIL<br>";
		printResponse($resultPost);
	}

	return $resultPost->body->data;
}

function testDeleteBookmark($url, $method, $user, $pw, $id) {
	echo "<br><br>Test '".$method."' start \r\n";
	echo "<br>";
	
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
	$assertions[] = assertEqual("success",$resultPost->body->meta->status, "Response Status Success");
	if(allTrue($assertions)) {
		echo "Test '".$method."' finished SUCCESS<br>";
	} else {
		echo "Test '".$method."' finished FAIL<br>";
		printResponse($resultPost);
	}
}

function testUpdateBookmark($url, $method, $user, $pw, $id) {
	echo "<br><br>Test '".$method."' start \r\n";
	echo "<br>";

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
	$assertions[] = assertEqual("success",$resultPost->body->meta->status, "Response Status Success");
	if(allTrue($assertions)) {
		echo "Test '".$method."' finished SUCCESS<br>";
	} else {
		echo "Test '".$method."' finished FAIL<br>";
		printResponse($resultPost);
	}
	
	return $resultPost->body->data;
}

function printResponse($resultPost) {
	echo "<br>";
	echo "Response Body:\n";
	print_r($resultPost->body);
	echo "<br>";
	echo "Raw Response:\n";
	print_r($resultPost->raw_body);
	echo "<br>";
	echo "Status Code:\n";
	print_r($resultPost->code);
	echo "<br>";
	echo "Headers:\n";
	print_r($resultPost->headers);
	echo "<br>";
}

function allTrue($arr) {
	return count(array_filter($arr, function($v) {
			return $v === true;
		})) === count($arr);
}




