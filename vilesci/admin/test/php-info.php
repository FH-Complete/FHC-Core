<?php
	//Header("WWW-Authenticate: Basic realm=\"My Realm\"");
	unset($PHP_AUTH_USER);
	Header("HTTP/1.0 401 Unauthorized");
	phpinfo();
?>
