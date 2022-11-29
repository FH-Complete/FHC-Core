<?php
$includesArray = array(
	'title' => $title ?? 'FH-Complete',
	'vue3' => true,
	'bootstrap5' => true,
	'fontawesome6' => true,
	'axios027' => true,
	'customJSModules' => array_merge([
		'public/js/apps/Cis.js'
	], $customJSModules ?? []),
	'customCSSs' => array_merge([
		'public/css/Cis4/Cis.css'
	], $customCSSs ?? [])
);

$this->load->view('templates/FHC-Header', $includesArray);


$ci =& get_instance(); // get CI instance
$ci->load->model('content/Content_model', 'ContentModel');
$result = $ci->ContentModel->getMenu(6739, null);
$menu = getData($result) ?? (object)['childs' => []];

?>

<div id="cis4">
	<header>
		<div id="cis-navigation-top" class="navbar navbar-expand-lg fixed-top navbar-dark bg-primary p-0">
			<button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#cis-navigation-top-offcanvas">
				<span class="navbar-toggler-icon"></span>
			</button>
			<a class="navbar-brand col-auto col-lg-2 px-3 py-0 m-0" href="<?= site_url('cis'); ?>">
				<img src="<?= base_url('/public/images/logo-300x160.png'); ?>" width="90">
			</a>
			<div id="cis-navigation-top-offcanvas" class="offcanvas offcanvas-start align-items-stretch mt-lg-0 w-100 px-3 px-lg-0 d-flex flex-column flex-lg-row bg-dark border-0 pt-3 pt-lg-0" tabindex="-1" data-bs-backdrop="false">
				<fhc-searchbar class="fhc-searchbar w-100" :searchoptions="{types:[],actions:{}}" :searchfunction="search"></fhc-searchbar>
				<ul class="navbar-nav flex-grow-1">
					<!-- TODO(chris): foreach menu -->
					<li class="nav-item d-lg-none">
						<a class="nav-link" href="#">Mein CIS</a>
					</li>
					<li class="nav-item d-lg-none">
						<a class="nav-link" href="#">FHTW Campus</a>
					</li>
					<li class="nav-item d-lg-none">
						<a class="nav-link" href="#">FHTW Services</a>
					</li>
					<li class="nav-item d-lg-none">
						<a class="nav-link" href="<?= site_url('cis/cms/content/10012'); ?>">COVID 19</a>
					</li>
					<li class="nav-item">
						<div class="dropdown pe-lg-3">
							<a class="nav-link" href="#" id="dropdown01" data-bs-toggle="dropdown" data-bs-reference="parent" aria-expanded="false">
								<img src="<?= base_url('/cis/public/bild.php?src=person&person_id=' . getAuthPersonId()); ?>" class="avatar rounded-circle" width="45" height="45"/>
							</a>
							<ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end m-0" aria-labelledby="dropdown01">
								<!-- TODO(chris): foreach menu -->
								<li><a class="dropdown-item" href="#" id="menu-profil">Profil</a></li>
								<li><a class="dropdown-item" href="#">Ampeln</a></li>
								<li><hr class="dropdown-divider"></li>
								<li><a class="dropdown-item" href="#">Logout</a></li>
							</ul>
						</div>
					</li>
				</ul>
			</div>
		</div>
	</header>

	<div class="container-fluid px-0 flex-grow-1 row mx-0">
		<nav id="cis-navigation-left" class="d-none d-lg-block position-sticky col-lg-2 px-0 bg-secondary">
			<div class="btn btn-dark text-muted rounded-0 w-100 text-start"><small>MAIN MENU</small></div>
			<?php foreach($menu->childs as $entry)
				$this->load->view('templates/CIS-Menu/Entry', ['entry' => $entry, 'menu_id' => 'menu']);
			?>
		</nav>
		<!--nav class="list-group list-group-flush d-none d-lg-block position-sticky col-lg-2 px-0">
				< !-- Separator with title -- >
				<div class="list-group-item sidebar-separator-title text-muted d-flex align-items-center menu-collapsed">
					<small>MAIN MENU</small>
				</div>
				< !-- /end Separator -- >
				<a href="#submenu1" data-bs-toggle="collapse" aria-expanded="true" class="bg-primary text-white list-group-item list-group-item-action flex-column align-items-start">
					<div class="d-flex w-100 justify-content-start align-items-center">
						<i class="fa fa-user fa-fw me-3"></i>
						<span class="menu-collapsed">Mein CIS</span>
					</div>
				</a>
				<div id='submenu1' class="collapse show sidebar-submenu">
					<a href="#" class="list-group-item list-group-item-action list-group-item-dark active" id="menu-mein-bereich">
						<span class="menu-collapsed">Mein Bereich</span>
					</a>
					<a href="#" class="list-group-item list-group-item-action list-group-item-dark">
						<span class="menu-collapsed">Studium</span>
					</a>
					<a href="#" class="list-group-item list-group-item-action list-group-item-dark" id="menu-lvplan">
						<span class="menu-collapsed">LV Plan</span>
					</a>
					<a href="#" class="list-group-item list-group-item-action list-group-item-dark">
						<span class="menu-collapsed">Campus Life</span>
					</a>
				</div>
				<a href="#submenu2" data-bs-toggle="collapse" aria-expanded="false" class="bg-primary text-white list-group-item list-group-item-action flex-column align-items-start">
					<div class="d-flex w-100 justify-content-start align-items-center">
						<i class="fa fa-user fa-fw me-3"></i>
						<span class="menu-collapsed">FHTW Hochschule</span>
					</div>
				</a>
				<div id='submenu2' class="collapse sidebar-submenu">
					<a href="#" class="list-group-item list-group-item-action list-group-item-dark" id="menu-organisation">
						<span class="menu-collapsed">Organisation</span>
					</a>
					<a href="#" class="list-group-item list-group-item-action list-group-item-dark">
						<span class="menu-collapsed">Studiengänge</span>
					</a>
					<a href="#" class="list-group-item list-group-item-action list-group-item-dark">
						<span class="menu-collapsed">Forschung & Entwicklung</span>
					</a>
					<a href="#" class="list-group-item list-group-item-action list-group-item-dark">
						<span class="menu-collapsed">Technikum Wien Acadamy</span>
					</a>
				</div>
				<a href="#submenu3" data-bs-toggle="collapse" aria-expanded="false" class="bg-primary text-white list-group-item flex-column align-items-start">
					<div class="d-flex w-100 justify-content-start align-items-center">
						<i class="fa fa-user fa-fw me-3"></i>
						<span class="menu-collapsed">FHTW Services</span>
					</div>
				</a>
				<div id='submenu3' class="collapse sidebar-submenu">
					<a href="#" class="list-group-item list-group-item-action list-group-item-dark">
						<span class="menu-collapsed">Teaching & Learning Center</span>
					</a>
					<a href="#" class="list-group-item list-group-item-action list-group-item-dark">
						<span class="menu-collapsed">International Office</span>
					</a>
					<a href="#" class="list-group-item list-group-item-action list-group-item-dark">
						<span class="menu-collapsed">Bibliothek</span>
					</a>
					<a href="#" class="list-group-item list-group-item-action list-group-item-dark">
						<span class="menu-collapsed">IT-Services</span>
					</a>
				</div>
				<a href="#submenu4" data-bs-toggle="collapse" aria-expanded="false" class="bg-primary text-white list-group-item flex-column align-items-start">
					<div class="d-flex w-100 justify-content-start align-items-center">
						<i class="fa fa-user fa-fw me-3"></i>
						<span class="menu-collapsed">Links & Downloads</span>
					</div>
				</a>
				<div class="list-group-item sidebar-separator-title text-muted d-flex align-items-center menu-collapsed">
					<small>AKTUELL</small>
				</div>
				<a href="<?= site_url('cis/cms/content/10012'); ?>" class="bg-primary text-white list-group-item">
					<div class="d-flex w-100 justify-content-start align-items-center">
						<i class="fa fa-medkit fa-fw me-3"></i>
						<span class="menu-collapsed">COVID 19</span>
					</div>
				</a>
				<div class="list-group-item sidebar-separator menu-collapsed"></div>
			</ul>
		</nav-->
		<main class="ms-sm-auto col-lg-10 px-md-4 p-4 overflow-hidden">