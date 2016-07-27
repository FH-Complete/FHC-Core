<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/crm/Prestudentstatus/Prestudentstatus");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");

$I->sendGET("v1/crm/Prestudentstatus/Prestudentstatus", array("ausbildungssemester" => "0", "studiensemester_kurzbz" => "0", "status_kurzbz" => "0", "prestudent_id" => "0"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);

$I->sendGET("v1/crm/Prestudentstatus/LastStatus", array("prestudent_id" => 3));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);