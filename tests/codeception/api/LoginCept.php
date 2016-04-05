<?php

$I = new ApiTester($scenario);
$I->wantTo('test the Login API');
$I->haveHttpHeader('FHC-API-KEY', 'testapikey@fhcomplete.org');
$I->sendGET('/userauth/login/username/codeception%40whisperocity.com/password/secret/device_id/abcdef123');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'success' => true,
    'message' => 'User successfully logged in']);
