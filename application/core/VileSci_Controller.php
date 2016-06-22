<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class VileSci_Controller extends FHC_Controller
{
	function __construct()  
	{
        parent::__construct();
		/*if (! $this->getUID() && strncmp(uri_string(), 'system/Login', 11) !== 0)
			redirect(site_url('system/Login/'.uri_string()));*/
	}

}
