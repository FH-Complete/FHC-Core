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
		'customCSSs' => array('vendor/vuejs/vuedatepicker_css/main.css'),
		'customJSs' => array('vendor/vuejs/vuedatepicker_js/vue-datepicker.iife.js'),
		'customJSModules' => array('public/js/apps/Bismeldestichtag/Bismeldestichtag.js')
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>
	<div id="main">

		<!-- Navigation component -->
		<core-navigation-cmpt></core-navigation-cmpt>

		<!-- Fetch component -->
		<core-fetch-cmpt
			v-bind:api-function="fetchCmptApiFunction"
			v-bind:api-function-parameters="fetchCmptApiFunctionParams"
			v-bind:refresh="fetchCmptRefresh"
			@data-fetched="fetchCmptDataFetched">
		</core-fetch-cmpt>

		<div id="content">
			<!-- input fields -->
			<div class="row">
				<div class="col-5">
					<datepicker v-model="meldestichtag"
						v-bind:enable-time-picker="false"
						v-bind:placeholder="'Meldestichtag'"
						v-bind:text-input="true"
						v-bind:auto-apply="true"
						locale="de"
						format="dd.MM.yyyy"
						model-type="yyyy-MM-dd">
					</datepicker>
				</div>
				<div class="col-4">
					<select class="form-select" name="studiensemester_kurzbz" v-model="currSem">
						<option v-for="sem in semList" :value="sem.studiensemester_kurzbz">
							{{ sem.studiensemester_kurzbz }}
						</option>
					</select>
				</div>
				<div class="col-3">
					<button type="button" class="btn btn-primary" @click="handlerAddBismeldestichtag">
						<?php echo $this->p->t('bismeldestichtag', 'stichtagHinzufuegen') ?>
					</button>
				</div>
			</div>
			<br />
			<!-- Filter component -->
			<div class="row">
				<div class="col">
					<core-filter-cmpt
						title="<?php echo $this->p->t('bismeldestichtag', 'stichtageVerwalten') ?>"
						ref="bismeldestichtageTable"
						:side-menu="false"
						:tabulator-options="bismeldestichtagTabulatorOptions"
						:tabulator-events="bismeldestichtagTabulatorEventHandlers"
						:table-only="true">
					</core-filter-cmpt>
				</div>
			</div>
		</div>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>
