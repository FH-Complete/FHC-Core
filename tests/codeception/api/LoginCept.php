<?php

$I = new ApiTester($scenario);
$I->wantTo('test the Login API');
$I->haveHttpHeader('FHC-API-KEY', 'testapikey@fhcomplete.org');
//$I->haveHttpHeader('username', 'testapikey@fhcomplete.org');
//$I->haveHttpHeader('password', 'testapikey@fhcomplete.org');
$I->sendPOST('AuthAPI/login?username=admin&password=1q2w3');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'success' => true,
    'message' => 'User successfully logged in']);


