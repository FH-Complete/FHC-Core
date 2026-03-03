<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

// 'entschuldigt' & 'noch nicht eingetragen' -> wirken sich nicht auf Antritte aus
$config['NOTEN_OHNE_ANTRITT'] = [9, 17]; // tbl_note pk