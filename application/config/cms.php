<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

// Preferred organisational unit for a new content.
// If the user is entitled for this unit, it wins.
// If not, the first entitled unit wins.
$config['default_oe_kurzbz'] = 'etw';

// Lifetime of an edit lock in hours, counted from tbl_contentlog.start. Must be > 0.
$config['lock_ttl_hours'] = 24;

// Default period of the tree click count in months, when the caller sends none.
$config['clickstats_months'] = 12;
