<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/system/PCRM/Call");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");

$I->sendGET("v1/system/PCRM/Call", array(
	"resource" => "codex/Bundesland_model", "function" => "load", "bundesland_code" => "1")
);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);

$I->sendGET("v1/system/PCRM/Call", array(
	"resource" => "PermissionLib", "function" => "hasPermission",
	"sn" => "bis.tbl_archiv", "pt" => "s")
);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);