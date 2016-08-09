<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/crm/prestudent Prestudent and PrestudentByPersonID");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");

$I->sendGET("v1/crm/prestudent/Prestudent", array("prestudent_id" => 1));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

$I->sendGET("v1/crm/prestudent/PrestudentByPersonID", array("person_id" => 3));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();