<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/system/Phrase/Phrase");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");

$I->sendGET("v1/system/Phrase/Phrase", array("phrase_id" => "0", "app" => "0", "sprache" => "0", "phrase" => "0", "orgeinheit_kurzbz" => "0", "orgform_kurzbz" => "0"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);