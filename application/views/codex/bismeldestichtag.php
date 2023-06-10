<?php
	$includesArray = array(
		'title' => 'Bismeldestichtage',
		'axios027' => true,
		'bootstrap5' => true,
		'fontawesome6' => true,
		'vue3' => true,
		'filtercomponent' => true,
		'navigationcomponent' => true,
		'tabulator5' => true,
		'customCSSs' => array(
			'public/css/components/verticalsplit.css'
		),
		'customJSModules' => array('public/js/apps/Bismeldestichtag/Bismeldestichtag.js')
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>
	<!-- Load Studiensemester -->
	<div id="main">

		<!-- Navigation component -->
		<core-navigation-cmpt v-bind:add-side-menu-entries="appSideMenuEntries"></core-navigation-cmpt>

		<!-- fetch component -->
		<core-fetch-cmpt
			v-bind:api-function="fetchCmptApiFunction"
			v-bind:api-function-parameters="fetchCmptApiFunctionParams"
			v-bind:refresh="fetchCmptRefresh"
			@data-fetched="fetchCmptDataFetched">
		</core-fetch-cmpt>

		<div id="content">
			<div>
				<verticalsplit>
					<template #top>
						<!-- input fields -->
						<div class="input-group">
							<input type="date" class="form-control" name="meldestichtag" v-model="meldestichtag">
							<select class="form-control" name="studiensemester_kurzbz" v-model="currSem">
								<option v-for="sem in semList" :value="sem.studiensemester_kurzbz">
									{{ sem.studiensemester_kurzbz }}
								</option>
							</select>
							<div class="input-group-btn">
								<button type="button" class="btn btn-secondary" @click="handlerAddBismeldestichtag">
									<?php echo $this->p->t('bismeldestichtag', 'stichtagHinzufuegen') ?>
								</button>
							</div>
						</div>
					</template>
					<template #bottom>
						<!-- Filter component -->
						<core-filter-cmpt
							title="<?php echo $this->p->t('bismeldestichtag', 'stichtageVerwalten') ?>"
							filter-type="Bismeldestichtag"
							:tabulator-options="bismeldestichtagTabulatorOptions"
							:tabulator-events="bismeldestichtagTabulatorEventHandlers"
							@nw-new-entry="newSideMenuEntryHandler">
						</core-filter-cmpt>
					</template>
				</verticalsplit>
			</div>
		</div>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>
