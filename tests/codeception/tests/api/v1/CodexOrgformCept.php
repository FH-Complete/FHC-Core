<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/codex/Orgform/: Orgform All OrgformLV");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");
$I->sendGET("v1/codex/Orgform/Orgform", array("orgform_kurzbz" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
$I->sendGET("v1/codex/Orgform/All", array());
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
$I->sendGET("v1/codex/Orgform/OrgformLV", array());
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
