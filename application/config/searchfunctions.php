<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');


$config['equal-int'] = [
	'priority' => 4,
	'rank' => "0",
	'compare' => "{field} = {word}",
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
	'rank' => "ts_rank_cd({field}, to_tsquery('simple', {word}))",
	'compare' => "to_tsquery('simple', {word}) @@ {field}"
];

