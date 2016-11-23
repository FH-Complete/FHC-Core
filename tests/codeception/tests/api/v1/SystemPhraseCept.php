<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/system/Phrase/: Phrase Phrases");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");
$I->sendGET("v1/system/Phrase/Phrase", array("phrase_id" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
$I->sendGET("v1/system/Phrase/Phrases", array("app" => "1", "sprache" => "1", "phrase" => "1", "orgeinheit_kurzbz" => "1", "orgform_kurzbz" => "1", "blockTags" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
