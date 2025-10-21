<?php
/**
 * Copyright (C) 2025 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

$includesArray = array(
	'title' => 'Login',
	'axios027' => true,
	'bootstrap5' => true,
	'fontawesome6' => true,
	'vue3' => true,
	'primevue3' => true,
	'phrases' => array('uid', 'global'),
	'navigationcomponent' => true,
	'customJSModules' => array('public/js/LoginAs.js'),
);

$this->load->view('templates/FHC-Header', $includesArray);
?>

	<div id="main"></div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

