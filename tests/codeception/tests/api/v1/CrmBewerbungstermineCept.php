<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/crm/Bewerbungstermine/: Bewerbungstermine ByStudiengangStudiensemester ByStudienplan Current");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");
$I->sendGET("v1/crm/Bewerbungstermine/Bewerbungstermine", array("bewerbungstermine_id" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
$I->sendGET("v1/crm/Bewerbungstermine/ByStudiengangStudiensemester", array("studiengang_kz" => "1", "studiensemester_kurzbz" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
$I->sendGET("v1/crm/Bewerbungstermine/ByStudienplan", array("studienplan_id" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
$I->sendGET("v1/crm/Bewerbungstermine/Current", array());
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
