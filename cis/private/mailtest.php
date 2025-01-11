<?php

require_once('../../config/global.config.inc.php');
//require_once('../../config/system.config.inc.php');
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/sancho.inc.php');

$maildata = array();
echo "HI";

$res = sendSanchoMail(
			'ParbeitsbeurteilungEndupload',
			$maildata,
			'test@test.com',
			"Masterarbeitsbetreuung",
			null,
			//'sancho_header_min_bw.jpg',
			'sancho_footer_min_bw.jpg'
			//~ false,
			//~ false
		);

		var_dump($res);

