<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

// List of permissions that are allowed to perform loginAs
$config['permission_loginas_allowed'] = array('admin');

// List of permissions that cannot be gained with loginAs
$config['permission_loginas_blacklist'] = array('admin');

// List of users whose identity cannot be obtained with loginAs
$config['permission_loginas_users_blacklist'] = array('_DummyLektor', '_DummyStudent');
