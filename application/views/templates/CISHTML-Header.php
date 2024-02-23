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

if (!isset($menu)) {
	$ci =& get_instance(); // get CI instance
	$ci->load->model('content/Content_model', 'ContentModel');
	$result = $ci->ContentModel->getMenu(defined('CIS4_MENU_ENTRY') ? CIS4_MENU_ENTRY : null, get_uid());
	$menu = getData($result) ?? (object)['childs' => []];
}
?>

<script type="text/javascript">
	if (window.self !== window.top)
		document.body.classList.add("in-frame");
</script>
<header id="cis-header" class="navbar-dark">
	<button id="nav-main-btn" class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#nav-main" aria-controls="nav-main" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>
	<a id="nav-logo" href="<?= site_url(''); ?>">
		<img src="<?= base_url('/public/images/logo-300x160.png'); ?>" alt="Logo">
	</a>
	<nav id="nav-main" class="offcanvas offcanvas-start bg-dark" tabindex="-1" aria-labelledby="nav-main-btn" data-bs-backdrop="false">
		<div id="nav-main-toggle" class="position-static d-none d-lg-block bg-dark">
			<button type="button" class="btn bg-dark text-light rounded-0 p-1 d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#nav-main-menu" aria-expanded="true" aria-controls="nav-main-menu">
				<i class="fa fa-arrow-circle-left"></i>
			</button>
		</div>
		<div class="offcanvas-body p-0">
			<fhc-searchbar id="nav-search" class="fhc-searchbar w-100" :searchoptions="searchbaroptions" :searchfunction="searchfunction"></fhc-searchbar>
			<button id="nav-user-btn" class="btn btn-link rounded-0" type="button" data-bs-toggle="collapse" data-bs-target="#nav-user-menu" aria-expanded="false" aria-controls="nav-user-menu">
				<img src="<?= site_url('Cis/Pub/bild/person/' . getAuthPersonId()); ?>" class="avatar rounded-circle"/>
			</button>
			<ul id="nav-user-menu" class="collapse list-unstyled" aria-labelledby="nav-user-btn">
				<li><a class="btn btn-level-2 rounded-0 d-block" href="<?= site_url('Cis/Profil'); ?>" id="menu-profil">Profil</a></li>
				<li><a class="btn btn-level-2 rounded-0 d-block" href="#">Ampeln</a></li>
				<li><hr class="dropdown-divider"></li>
				<li><a class="btn btn-level-2 rounded-0 d-block" href="<?= site_url('Cis/Auth/logout'); ?>">Logout</a></li>
			</ul>
			<div id="nav-main-menu" class="collapse collapse-horizontal show">
				<div>
				<?php foreach($menu->childs as $entry)
					$this->load->view('templates/CISHTML-Menu/Entry', ['entry' => $entry, 'menu_id' => 'menu']);
				?>
				</div>
			</div>
		</div>
	</nav>
</header>

<main id="cis-main" class="flex-grow-1 overflow-scroll">
