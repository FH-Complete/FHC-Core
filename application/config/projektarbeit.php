<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * before this date projektarbeiten failed do upload in time will be ignored
 * @var string string formated as Date
 */
$config['projektarbeitjob_start'] = '2023-06-01';

/**
 * projektarbeiten bachelor will not be copied anymore after this amount of already existing
 * @var Integer count of projektarbeiten
 */
$config['projektarbeitjob_finishCopy_bachelor'] = 6;

/**
 *  projektarbeiten master will not be copied anymore after this amount of already existing
 * @var Integer count of projektarbeiten
 */
$config['projektarbeitjob_finishCopy_diplom'] = 3;