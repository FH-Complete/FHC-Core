<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

// use vuejs dev version
$config['use_vuejs_dev_version'] = false;
// use bundled javascript
$config['use_bundled_javascript'] = false;
// systemerror_mailto use in FHC-Alert Plugin - if empty Link will not be rendered
$config['systemerror_mailto'] = '';
// use fhcomplete_build_version as path element after public (requires apache mod_rewrite)
// see <fhc_base_dir>/public/.htaccess_sample for details
$config['use_fhcomplete_build_version_in_path'] = false;
