<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');


// CMS Content Id for CIS4 Menu Root
$config['cis_menu_root_content_id'] = 11091;
// send Mails for ProfilUpdate
$config['cis_send_profil_update_mails'] = true;
// Vilesci CI BaseUrl
$config['cis_vilesci_base_url'] = defined('VILESCI_ROOT') ? VILESCI_ROOT : APP_ROOT;
$config['cis_vilesci_index_page'] = 'index.ci.php';
// Cis CI BaseUrl
$config['cis_base_url'] = defined('CIS_ROOT') ? CIS_ROOT : APP_ROOT;
$config['cis_index_page'] = 'cis.php';

// Associative array of OEs allowed to have lehrauftraege
// Array structure: OE => Name of the phrase that contains the name of the OE
$config['cis_oes_lehrauftraege'] = array(
	'etw' => 'PDFLehrauftraegeFH',
	'lehrgang' => 'PDFLehrauftraegeLehrgaenge'
);

