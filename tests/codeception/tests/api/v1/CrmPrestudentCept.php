<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/crm/Prestudent/: Prestudent PrestudentByPersonID Specialization");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");
$I->sendGET("v1/crm/Prestudent/Prestudent", array("prestudent_id" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
$I->sendGET("v1/crm/Prestudent/PrestudentByPersonID", array("person_id" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
$I->sendGET("v1/crm/Prestudent/Specialization", array("prestudent_id" => "1", "titel" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
