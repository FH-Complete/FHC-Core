<?php
	ini_set(sendmail_from, "pam@technikum-wien.at");
	if (!mail($sendto,"Test","Dies ist ein Test","From: pam@technikum-wien.at\r\n"."Reply-To: webmaster@technikum-wien.at\r\n"))
		die ("Mail konnte nicht verschickt werden!");
	else
		die ("Mail wurde an $sendto verschickt! [PHP]");
?>
