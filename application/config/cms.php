<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

// Preferred organisational unit for a new content.
// If the user is entitled for this unit, it wins.
// If not, the first entitled unit wins.
$config['default_oe_kurzbz'] = 'etw';

// Lifetime of an edit lock in hours, counted from tbl_contentlog.start. Must be > 0.
$config['lock_ttl_hours'] = 24;

// Switches the click statistics off completely. With false the admin tab stays hidden and
// the endpoint refuses every request, whatever the rights of the caller are.
$config['clickstats_enabled'] = true;

// Default period of the click statistics in months, when the caller sends none.
$config['clickstats_months'] = 12;

// Length of the ranking in the click statistics tab.
$config['clickstats_limit'] = 100;
