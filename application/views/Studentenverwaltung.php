<?php
	$includesArray = array(
		'title' => 'Studentenverwaltung',
		'axios027' => true,
		'bootstrap5' => true,
		'fontawesome6' => true,
		'vue3' => true,
		'primevue3' => true,
		'filtercomponent' => true,
		'tabulator5' => true,
		'phrases' => [],
		'customCSSs' => [
			'public/css/Studentenverwaltung.css'
		],
		'customJSModules' => [
			'public/js/apps/Studentenverwaltung.js'
		]
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>

	<div id="main">
		<header class="navbar navbar-expand-lg navbar-dark bg-dark flex-md-nowrap p-0 shadow">
			<a class="navbar-brand col-md-4 col-lg-3 col-xl-2 me-0 px-3" href="<?= site_url('Studentenverwaltung'); ?>">FHC 4.0</a>
			<button class="navbar-toggler d-md-none m-1 collapsed" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
			<fhc-searchbar :searchoptions="searchbaroptions" :searchfunction="searchfunction" class="searchbar w-100"></fhc-searchbar>
		</header>
		
		<div class="container-fluid overflow-hidden">
			<div class="row h-100">
				<nav id="sidebarMenu" class="bg-light offcanvas offcanvas-start col-md p-md-0 h-100">
					<div class="offcanvas-header justify-content-end px-1 d-md-none">
						<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
					</div>
					<stv-verband @select-verband="onSelectVerband"></stv-verband>
				</nav>
				<main class="col-md-8 ms-sm-auto col-lg-9 col-xl-10">
					<vertical-split>
						<template #top>
							<stv-list ref="stvList" v-model:selected="selected"></stv-list>
						</template>
						<template #bottom>
							<stv-details :student="lastSelected"></stv-details>
						</template>
					</vertical-split>
				</main>
			</div>
		</div>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

