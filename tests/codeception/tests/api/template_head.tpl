<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call _CALL_");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");