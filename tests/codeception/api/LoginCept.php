<?php

$I = new ApiTester($scenario);
$I->wantTo('test the Login API');
$I->haveHttpHeader('FHC-API-KEY', 'testapikey@fhcomplete.org');
$I->sendGET('AuthAPI/login?username=pam&password=1q2w3&FHC-API-KEY=testapikey@fhcomplete.org');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'success' => true,
    'message' => 'User successfully logged in']);


