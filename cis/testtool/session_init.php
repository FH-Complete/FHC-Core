<?php
if (session_status() === PHP_SESSION_NONE) {
	session_set_cookie_params(
		0,
		'/; samesite=None',
		'',
		true,
		true
	);
	session_start();
}