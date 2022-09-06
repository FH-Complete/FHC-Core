<?php
$this->load->view('templates/FHC-Header',
	array(
		'title' => 'FH-Complete',
		'bootstrap5' => true,
		'fontawesome6' => true,
		'axios027' => true,
		'restclient' => true,
		'vue3' => true,
		'customJSModules' => ['public/js/apps/Test.js'],
		'navigationcomponent' => true
	)
);
?>

	<style type="text/css">
		.fixed-h {
			padding-bottom: 100%;
			position: relative;
			height: 0;
		}
		.fixed-h > * {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
		}
		.fixed-h-1 > * {
			height: 100%;
		}
		.fixed-h-2 > * {
			height: calc(200% + var(--bs-gutter-y));
		}

		.core-dashboard .row {
			--bs-gutter-y: 1.5rem;
		}

		.draganddropcontainer {
			grid-template-columns:repeat(4,1fr);
		}
		@media(max-width: 700px) {
			.draganddropcontainer {
				grid-template-columns:repeat(2,1fr);
			}
		}
	</style>

	<div id="main">

		<core-navigation-cmpt :add-side-menu-entries="appSideMenuEntries"></core-navigation-cmpt>

		<div id="content">
			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
				<h1 class="h2">Dashboard</h1>
			</div>

			<core-dashboard dashboard="CIS"></core-dashboard>

		</div>
	</div>

<?php $this->load->view('templates/FHC-Footer'); ?>
