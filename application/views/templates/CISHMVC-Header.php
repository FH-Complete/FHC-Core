<?php
if (!isset($menu)) {
	$menu = [];
	if (property_exists($this->router, 'menu') && $this->router->menu && property_exists($this->router->menu, 'children')) {
		$menu = $this->router->menu->children;
	}
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
		<?php if ($menu) { ?>
			<div id="nav-main-toggle" class="position-static d-none d-lg-block bg-dark">
				<button type="button" class="btn bg-dark text-light rounded-0 p-1 d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#nav-main-menu" aria-expanded="true" aria-controls="nav-main-menu">
					<i class="fa fa-arrow-circle-left"></i>
				</button>
			</div>
		<?php } ?>
		<div class="offcanvas-body p-0">
			<fhc-searchbar id="nav-search" class="fhc-searchbar w-100" :searchoptions="searchbaroptions" :searchfunction="searchfunction"></fhc-searchbar>
			<button id="nav-user-btn" class="btn btn-link rounded-0" type="button" data-bs-toggle="collapse" data-bs-target="#nav-user-menu" aria-expanded="false" aria-controls="nav-user-menu">
				<img src="<?= site_url('Cis/Pub/bild/person/' . getAuthPersonId()); ?>" class="avatar rounded-circle"/>
			</button>
			<ul id="nav-user-menu" class="collapse list-unstyled" aria-labelledby="nav-user-btn">
				<li><a class="btn btn-level-2 rounded-0 d-block" href="#" id="menu-profil">Profil</a></li>
				<li><a class="btn btn-level-2 rounded-0 d-block" href="#">Ampeln</a></li>
				<li><hr class="dropdown-divider"></li>
				<li><a class="btn btn-level-2 rounded-0 d-block" href="<?= site_url('Cis/Auth/logout'); ?>">Logout</a></li>
			</ul>
			<?php if ($menu) { ?>
				<div id="nav-main-menu" class="collapse collapse-horizontal show">
					<div>
						<?php
						foreach ($menu as $entry) {
							$this->load->view('templates/CISHMVC-Menu/Entry', ['entry' => $entry, 'menu_id' => 'menu']);
						}
						?>
					</div>
				</div>
			<?php } ?>
		</div>
	</nav>
</header>

<main id="cis-main" class="flex-grow-1 overflow-scroll p-4">
