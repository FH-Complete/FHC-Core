import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';

import ApiStvAdmissionDates from '../../../../../api/factory/stv/admissionDates.js';

export default {
	name: 'ListAdmissionDates',
	components: {
		CoreFilterCmpt,
		BsModal,
		FormForm,
		FormInput
	},
	inject: {
		allowUebernahmePunkte: {
			from: 'configAllowUebernahmePunkte',
			default: true
		},
		//if true use punkte, false: use percentage
		useReihungstestPunkte: {
			from: 'configUseReihungstestPunkte',
			default: true
		},
		$reloadList: {
			from: '$reloadList',
			required: true
		},
		cisRoot: {
			from: 'cisRoot'
		},
	},
	props: {
		student: Object
	},
	data() {
		return {
			formData: {},
			statusNew: true,
			listPlacementTests: [],
			listStudyPlans: [],
			filterOnlyFutureTestsSet: false,
			filteredPlacementTests: [],
			//data after tabulator data
			layout: 'fitDataStretchFrozen',
			layoutColumnsOnNewData: false,
			height: 'auto',
			minHeight: 200,
			index: 'aufnahmetermin_id',
			persistenceID: 'stv-details-table_admission-dates-2025112401'
		}
	},
	methods: {
		actionNewPlacementTest() {
			this.resetForm();
			this.statusNew = true;
			this.formData.anmeldedatum = new Date();
			this.$refs.placementTestModal.show();
		},
		actionEditPlacementTest(rt_person_id) {
			this.resetForm();
			this.statusNew = false;
			this.loadPlacementTest(rt_person_id);
			this.$refs.placementTestModal.show();
		},
		actionDeletePlacementTest(rt_person_id) {
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? rt_person_id
					: Promise.reject({handled: true}))
				.then(this.deletePlacementTest)
				.catch(this.$fhcAlert.handleSystemError);
		},
		addNewPlacementTest() {
			const dataToSend = {
				person_id: this.student.person_id,
				formData: this.formData
			};
			return this.$refs.formPlacementTest
				.call(ApiStvAdmissionDates.addNewPlacementTest(dataToSend))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal("placementTestModal");
					this.resetForm();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		hideModal(modalRef){
			this.$refs[modalRef].hide();
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		loadPlacementTest(rt_person_id) {
			return this.$api
				.call(ApiStvAdmissionDates.loadPlacementTest(rt_person_id))
				.then(result => {
					this.formData = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updatePlacementTest(rt_person_id) {
			const dataToSend = {
				formData: this.formData,
				rt_person_id: rt_person_id,
			};
			this.$refs.formPlacementTest
				.call(ApiStvAdmissionDates.updatePlacementTest(dataToSend))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal("placementTestModal");
					this.resetForm();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		deletePlacementTest(rt_person_id) {
			return this.$api
				.call(ApiStvAdmissionDates.deletePlacementTest(rt_person_id))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		getResultReihungstest(reihungstest_id){
			const paramsRt = {
				reihungstest_id: reihungstest_id,
				person_id: this.student.person_id,
				punkte: this.useReihungstestPunkte,
				studiengang_kz: this.student.studiengang_kz
			};

			return this.$api
				.call(ApiStvAdmissionDates.getResultReihungstest(paramsRt))
				.then(response => {
					this.formData.punkte = response.data;
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		loadFutureReihungstests(){
			const arrayReihungstestIds = this.listPlacementTests.map(item => item.reihungstest_id);
			const paramsRt = {
				studiengang_kz: this.student.studiengang_kz,
				arrayReihungstestIds : arrayReihungstestIds
			};

			return this.$api
				.call(ApiStvAdmissionDates.loadFutureReihungstests(paramsRt))
				.then(response => {
					this.filteredPlacementTests = response.data;
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'filterOnlyFutureActive'));
					this.$refs.filterButton.className = 'btn btn-secondary w-100';
					this.$refs.filterButton.title = this.$p.t('ui', 'alleAnzeigen');
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		toggleFilter(){
			this.filterOnlyFutureTestsSet = !this.filterOnlyFutureTestsSet;

			if(this.filterOnlyFutureTestsSet) {
				this.loadFutureReihungstests();
			}
			else
			{
				this.filteredPlacementTests = this.listPlacementTests;
				this.$refs.filterButton.className = 'btn btn-outline-secondary w-100';
				this.$refs.filterButton.title = this.$p.t('admission', 'loadZukuenftigeRT');}
		},
		openAdministrationPlacementTest(reihungstest_id){
			let link = FHC_JS_DATA_STORAGE_OBJECT.app_root + 'vilesci/stammdaten/reihungstestverwaltung.php';
			if(reihungstest_id){
				link += '?reihungstest_id=' + reihungstest_id;
			}
			window.open(link, '_blank');
		},
		resetForm() {
			this.formData = {};
		},
	},
	computed: {
		tabulatorOptions() {
			const options = {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(
					ApiStvAdmissionDates.getAufnahmetermine(this.student.person_id)
				),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "rt_id", field: "rt_id", visible: false},
					{title: "rt_person_id", field: "rt_person_id", visible: false},
					{title: "person_id", field: "person_id", visible: false},
					{title: "datum", field: "datum",
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
							});
						}
					},
					{title: "stufe", field: "stufe"},
					{title: "studiensemester", field: "studiensemester"},
					{title: "anmerkung", field: "anmerkung", visible: false},
					{title: "anmeldedatum", field: "anmeldedatum", visible: false,
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
							});
						}
					},
					{title: "punkte", field: "punkte"},
					{
						title: "teilgenommen", field: "teilgenommen",
						formatter: "tickCross",
						hozAlign: "center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-success"></i>',
							crossElement: '<i class="fa fa-xmark text-danger"></i>'
						}
					},
					{title: "ort", field: "ort", visible: false},
					{title: "studienplan", field: "studienplan", visible: false},
					{title: "studienplan_id", field: "studienplan_id", visible: false},
					{title: "stg", field: "studiengangkurzbzlang"},
					{title: "Stg", field: "stg_kuerzel"},
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.title = this.$p.t('ui', 'bearbeiten');
							button.addEventListener('click', (event) =>
								this.actionEditPlacementTest(cell.getData().rt_person_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('ui', 'loeschen');
							button.addEventListener('click', () =>
								this.actionDeletePlacementTest(cell.getData().rt_person_id)
							);
							container.append(button);

							return container;
						},
						frozen: true
					}
				],
			};
			return options;
		},
		tabulatorEvents() {
			const events = [
				{
					event: 'tableBuilt',
					handler: async () => {
						await this.$p.loadCategory(['admission', 'global', 'person', 'ui', 'projektarbeitsbeurteilung']);
						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('rt_id').component.updateDefinition({
							title: this.$p.t('ui', 'reihungstest_id')
						});
						cm.getColumnByField('rt_person_id').component.updateDefinition({
							title: this.$p.t('ui', 'reihungstest_person_id')
						});
						cm.getColumnByField('person_id').component.updateDefinition({
							title: this.$p.t('person', 'person_id')
						});
						cm.getColumnByField('datum').component.updateDefinition({
							title: this.$p.t('global', 'datum')
						});
						cm.getColumnByField('stufe').component.updateDefinition({
							title: this.$p.t('admission', 'stufe')
						});
						cm.getColumnByField('studiensemester').component.updateDefinition({
							title: this.$p.t('lehre', 'studiensemester')
						});
						cm.getColumnByField('anmerkung').component.updateDefinition({
							title: this.$p.t('global', 'anmerkung')
						});
						cm.getColumnByField('anmeldedatum').component.updateDefinition({
							title: this.$p.t('admission', 'anmeldedatum')
						});
						cm.getColumnByField('punkte').component.updateDefinition({
							title: this.$p.t('exam', 'punkte')
						});
						cm.getColumnByField('teilgenommen').component.updateDefinition({
							title: this.$p.t('admission', 'teilgenommen')
						});
						cm.getColumnByField('ort').component.updateDefinition({
							title: this.$p.t('person', 'ort')
						});
						cm.getColumnByField('studienplan').component.updateDefinition({
							title: this.$p.t('lehre', 'studienplan')
						});
						cm.getColumnByField('studienplan_id').component.updateDefinition({
							title: this.$p.t('ui', 'studienplan_id')
						});
						cm.getColumnByField('studiengangkurzbzlang').component.updateDefinition({
							title: this.$p.t('projektarbeitsbeurteilung', 'studiengang')
						});
						cm.getColumnByField('stg_kuerzel').component.updateDefinition({
							title: this.$p.t('admission', 'stg_kurz')
						});
					}
				}
			];

			return events;
		}
	},
	created() {
		this.$api
			.call(ApiStvAdmissionDates.getListPlacementTests(this.student.prestudent_id))
			.then(result => {
				this.listPlacementTests = this.filteredPlacementTests = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStvAdmissionDates.getListStudyPlans(this.student.person_id))
			.then(result => {
				this.listStudyPlans = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="stv-details-admission-table h-100 pb-3">
		<h4>{{$p.t('admission', 'allgemein')}}</h4>
		
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			:reload-btn-infotext="this.$p.t('table', 'reload')"
			new-btn-show
			:new-btn-label="this.$p.t('lehre', 'reihungstest')"
			@click:new="actionNewPlacementTest"
			>
		</core-filter-cmpt>
		
		<!--Modal: placementTestModal-->
		<bs-modal ref="placementTestModal" dialog-class="modal-dialog-scrollable modal-lg">		
			<template #title>
				<p v-if="statusNew" class="fw-bold mt-3">{{$p.t('admission', 'admission_new')}}</p>
				<p v-else class="fw-bold mt-3">{{$p.t('admission', 'admission_edit')}}</p>
			</template>


			<form-form ref="formPlacementTest" @submit.prevent>
			
				<div class="row mb-3 g-2">
					<div class="col-10">
						<form-input
							container-class="stv-details-admission-placementtest"
							:label="$p.t('lehre', 'reihungstest')"
							type="select"
							v-model="formData.rt_id"
							name="reihungstest_id"
							>
							<option value=""> -- {{ $p.t('fehlermonitoring', 'keineAuswahl') }} --</option>
							<option
								v-for="test in filteredPlacementTests"
								:key="test.reihungstest_id"
								:value="test.reihungstest_id"
								>
								{{test.studiensemester_kurzbz}} St.{{test.stufe}} {{test.stg}} {{test.datum}} {{test.uhrzeit}}
								{{test.anmerkung}} <span v-if="test.max_teilnehmer">({{test.angemeldete_teilnehmer}}/{{test.max_teilnehmer}})</span>
								<span v-if="test.datum">({{test.wochentag}})</span>
							</option>
						</form-input>			
					</div>
					
					<div class="col-1">
						<label class="form-label" style="color:transparent;">filter</label>
						<button 
							class="btn btn-outline-secondary w-100"
							ref="filterButton"
							@click="toggleFilter" 
							:title="$p.t('admission', 'loadZukuenftigeRT')"
							>
								<i class="fa fa-filter"></i>
							</button>
					</div>						
					<div class="col-1">
						<label class="form-label" style="color:transparent;">edit</label>
						<button 
							class="btn btn-outline-secondary w-100" 
							@click="openAdministrationPlacementTest(formData.rt_id)" 
							:title="$p.t('admission', 'headerRTVerwaltung')"
							>
							<i class="fa fa-edit"></i>
						</button>
					</div>	
								
				</div>
				
				<div class="row mb-3">
					<div class="col-7">
						<form-input
							container-class="stv-details-admission-anmeldedatum"
							:label="$p.t('admission', 'anmeldundRtAm')"
							type="DatePicker"
							v-model="formData.anmeldedatum"
							auto-apply
							:enable-time-picker="false"
							text-input
							format="dd.MM.yyyy"
							name="anmeldedatum"
							:teleport="true"
							>
						</form-input>
					</div>
				</div>		
				
				<div class="row mb-3">
					<div class="mt-2">
						<form-input
							container-class="stv-details-admission-teilgenommen"
							type="checkbox"
							name="teilgenommen"
							:label="$p.t('admission/rtAngetreten')"
							v-model="formData.teilgenommen"
						>
						</form-input>
					</div>
				</div>
				
				<div class="row mb-3">
					<div class="col-7">
						<form-input
							container-class="stv-details-admission-studyplan"
							:label="$p.t('lehre', 'studienplan')"
							type="select"
							v-model="formData.studienplan_id"
							name="studienplan_id"
							>
							<option value=""> -- {{ $p.t('fehlermonitoring', 'keineAuswahl') }} --</option>
							<option
								v-for="stplan in listStudyPlans"
								:key="stplan.studienplan_id"
								:value="stplan.studienplan_id"
								>
								{{stplan.bezeichnung}}
							</option>
						</form-input>
					</div>
				</div>				
				
				<div class="row mb-3">
					<div class="col-3">
						<form-input
							container-class="stv-details-admission-points"
							:label="$p.t('exam', 'punkte')"
							type="text"
							v-model="formData.punkte"
							name="punkte"
							>
						</form-input>
						
					</div>
				
					<div v-if="allowUebernahmePunkte" class="col-4">
						<label class="form-label" style="color:transparent;">getPunkte</label>
						<button class="btn btn-outline-secondary w-100" @click="getResultReihungstest(formData.rt_id)">{{ $p.t('admission', 'getRTErgebnis') }}</button>
					</div>

				</div>				
		</form-form>
		
		<template #footer>
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{$p.t('ui', 'abbrechen')}}</button>
			<button v-if="statusNew" class="btn btn-primary" @click="addNewPlacementTest()"> {{$p.t('ui', 'speichern')}}</button>
			<button v-else class="btn btn-primary" @click="updatePlacementTest(formData.rt_person_id)"> {{$p.t('ui', 'speichern')}}</button>
		</template>
	    </bs-modal>

	</div>
	`
}
