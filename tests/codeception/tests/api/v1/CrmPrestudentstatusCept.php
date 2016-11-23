<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/crm/Prestudentstatus/: Prestudentstatus LastStatus");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");
$I->sendGET("v1/crm/Prestudentstatus/Prestudentstatus", array("ausbildungssemester" => "1", "studiensemester_kurzbz" => "1", "status_kurzbz" => "1", "prestudent_id" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
$I->sendGET("v1/crm/Prestudentstatus/LastStatus", array("prestudent_id" => "1", "studiensemester_kurzbz" => "1", "status_kurzbz" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
