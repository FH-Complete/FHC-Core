<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');


// CMS Content Id for CIS4 Menu Root
$config['cis_menu_root_content_id'] = 11087;
// send Mails for ProfilUpdate
$config['cis_send_profil_update_mails'] = true;
// Vilesci CI BaseUrl
$config['cis_vilesci_base_url'] = defined('VILESCI_ROOT') ? VILESCI_ROOT : APP_ROOT;
$config['cis_vilesci_index_page'] = 'index.ci.php';
// Cis CI BaseUrl
$config['cis_base_url'] = defined('CIS_ROOT') ? CIS_ROOT : APP_ROOT;
$config['cis_index_page'] = 'cis.php';
