<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * TODO: 2025-07-21 ma0080 bare integer comparision results in db error
 *       if searchstring is numeric but out of range of int, think about 
 *       more sophisticated solution using column data type - quick fix 
 *       convert field and search string to text
 */
$config['equal-int'] = [
	'priority' => 4,
	'rank' => "0",
	'compare' => "{field}::text = {word}::text",
	'force_integer' => true
];

$config['equals'] = [
	'priority' => 3,
	'rank' => "0",
	'compare' => "LOWER({field}) = {word}"
];

$config['similar'] = [
	'priority' => 2,
	'rank' => "(COALESCE({field}, '') <->> {word})",
	'compare' => "COALESCE({field}, '') %> {word}",
	'compare_boolean' => "COALESCE({field}, '') ILIKE {like:word}"
];

$config['vector'] = [
	'priority' => 1,
	'rank' => "ts_rank({field}, to_tsquery('simple', {word}))",
	'compare' => "to_tsquery('simple', {word}) @@ {field}"
];

