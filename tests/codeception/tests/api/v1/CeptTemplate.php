<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call TO_BE_REPLACED_NAME");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");

$I->sendGET("TO_BE_REPLACED_NAME", array(TO_BE_REPLACED_PARAMETERS));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();