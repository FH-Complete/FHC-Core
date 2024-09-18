import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import FormInput from "../../../../Form/Input.js";
import BsModal from "../../../../Bootstrap/Modal.js";

export default{
	components: {
		CoreFilterCmpt,
		FormInput,
		BsModal
	},
	inject: {
		defaultSemester: {
			from: 'defaultSemester',
		},
		showHintKommPrfg: {
			from: 'configShowHintKommPrfg',
			default: false
		},
		showZgvErfuellt: {
			from: 'configShowZgvErfuellt',
			default: false
		},
/*		$reloadList: {
			from: '$reloadList',
			required: true
		}*/
	},
	props: {
		uid: Number
	},
	data(){
		return {
			tabulatorOptions: {
				ajaxURL: 'api/frontend/v1/stv/pruefung/getPruefungen/' + this.uid,
				ajaxRequestFunc: this.$fhcApi.get,
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Datum", field: "format_datum"},
					{title: "Lehrveranstaltung", field: "lehrveranstaltung_bezeichnung"},
					{title: "Note", field: "note_bezeichnung"},
					{title: "StudSem", field: "studiensemester_kurzbz"}, //just testing
					{title: "Anmerkung", field: "anmerkung"},
					{title: "Typ", field: "pruefungstyp_kurzbz"},
					{title: "PruefungId", field: "pruefung_id", visible: false},
					{title: "LehreinheitId", field: "lehreinheit_id", visible: false},
					{title: "Student_uid", field: "student_uid", visible: false},
					{title: "LV_id", field: "lehrveranstaltung_id", visible: false}, //just for testing
					{title: "Mitarbeiter_uid", field: "mitarbeiter_uid", visible: false},
					{title: "Punkte", field: "punkte", visible: false},
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 150,
						maxWidth: 150,
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-plus"></i>';
							button.title = 'neue Prüfung aus dieser LV erstellen';
							button.addEventListener(
								'click',
								(event) =>
									this.actionNewFromOldPruefung(cell.getData().pruefung_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.title = 'Prüfung bearbeiten';
							button.addEventListener(
								'click',
								(event) =>
									this.actionEditPruefung(cell.getData().pruefung_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = 'Prüfung löschen';
							button.addEventListener(
								'click',
								() =>
									this.actionDeletePruefung(cell.getData().pruefung_id)
							);
							container.append(button);

							return container;
						},
						frozen: true
					}],
				layout: 'fitDataFill',
				layoutColumnsOnNewData: false,
				height: 'auto',
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async () => {
						await this.$p.loadCategory(['fristenmanagement', 'global', 'person', 'ui',]);
						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('bezeichnung').component.updateDefinition({
							title: this.$p.t('global', 'typ')
						});

						cm.getColumnByField('anmerkung').component.updateDefinition({
							title: this.$p.t('global', 'anmerkung')
						});
					}
				}
			],
			pruefungData: {},
			listTypesExam: [],
			listLvsAndLes: [],
			listLvsAndMas: [],
			listLvs: [], //TODO(Manu) nachträglich sortieren
			listLes: [],
			listMas: [], //TODO(Manu) Filter statt SELECT DISTINCT
			listMarks: [],
			filter: false,
			statusNew: true,
			isStartDropDown: false,
			currentSemester: false,
	//		componentKey: 0,
			isFilterSet: false,
		}
	},
	computed:{
/*		lehrveranstaltungen(){
			return this.listLvsAndLes.filter((value, index, self) => {
				return self.indexOf(value) === index;
			});
		},*/
		lv_teile(){
			return this.listLvsAndLes.filter(lv => lv.lehrveranstaltung_id == this.pruefungData.lehrveranstaltung_id);
		},
		lv_teile_ma(){
			return this.listLvsAndMas.filter(lv => lv.lehrveranstaltung_id == this.pruefungData.lehrveranstaltung_id);
		},
		semester_intern: {
			get() {
				if (this.currentSemester)
					return this.currentSemester;
				else
					return false;
			},
			set(value) {
				this.currentSemester = value;
			}
		},
	},
	watch: {
/*		defaultSemester(newVal, oldVal) {
			if (newVal !== oldVal) {
				console.log("variable did change");
				//this.reload(); // Methode aufrufen, um die Komponente neu zu laden
				this.componentKey += 1;
			}
		},
		modelValue() {
			this.$refs.table.reloadTable();
		}*/
	},
	methods:{
		loadPruefung(pruefung_id) {
			return this.$fhcApi.get('api/frontend/v1/stv/pruefung/loadPruefung/' + pruefung_id)
				.then(result => {
					this.pruefungData = result.data;
					return result;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		actionNewPruefung(lv_id){
			this.statusNew = true;
			this.isStartDropDown = true;
			this.resetModal();

			this.pruefungData.note = 9;
			this.pruefungData.datum = new Date();
			this.pruefungData.pruefungstyp_kurzbz = null;
			if(lv_id){
				this.pruefungData.lehrveranstaltung_id = lv_id;
			}
			this.$refs.pruefungModal.show();
		},
		actionNewFromOldPruefung(pruefung_id) {
			this.statusNew = true;
			this.isStartDropDown = false;
			this.loadPruefung(pruefung_id).then(() => {
				this.pruefungData.note = 9;
				this.pruefungData.datum = new Date();
				this.pruefungData.pruefungstyp_kurzbz = null;
				this.pruefungData.anmerkung = null;
				this.prepareDropdowns();

				this.$refs.pruefungModal.show();
			});
		},
		actionEditPruefung(pruefung_id) {
			this.statusNew = false;
			this.isStartDropDown = false;
			this.loadPruefung(pruefung_id).then(() => {

				this.prepareDropdowns();

				this.$refs.pruefungModal.show();
			});
		},
		actionDeletePruefung(pruefung_id) {
			this.loadPruefung(pruefung_id).then(() => {
				if(this.pruefungData.pruefung_id)

						this.$fhcAlert
							.confirmDelete()
							.then(result => result
								? pruefung_id
								: Promise.reject({handled: true}))
							.then(this.deletePruefung)
							.catch(this.$fhcAlert.handleSystemError);

			});
		},
		addPruefung(){
			this.$fhcApi.post('api/frontend/v1/stv/Pruefung/insertPruefung/',
				this.pruefungData
			).then(response => {
				this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
				this.hideModal('pruefungModal');
				this.resetModal();
			}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		updatePruefung(pruefung_id){
			console.log("update Prüfung" + pruefung_id);
			this.$fhcApi.post('api/frontend/v1/stv/pruefung/updatePruefung/' + pruefung_id,
				this.pruefungData
			).then(response => {
				this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
				this.hideModal('pruefungModal');
				this.resetModal();
			}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		deletePruefung(pruefung_id) {
			this.$fhcApi.post('api/frontend/v1/stv/pruefung/deletePruefung/' + pruefung_id)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(()=> {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		hideModal(modalRef) {
			this.$refs[modalRef].hide();
		},
		resetModal() {
			this.pruefungData = {};

/*			this.pruefungData.strasse = null;
			this.pruefungData.zustellpruefunge = true;
			this.pruefungData.heimatpruefunge = true;
			this.pruefungData.rechnungspruefunge = false;
			this.pruefungData.co_name = null;
			this.pruefungData.firma_id = null;
			this.pruefungData.name = null;
			this.pruefungData.anmerkung = null;
			this.pruefungData.typ = 'h';
			this.pruefungData.nation = 'A';
			this.pruefungData.plz = null;*/

			this.statusNew = true;
		},
		reload() {
			this.$refs.table.reloadTable();
		},
/*		setFilter(semester) {
			if (semester == 'open')
				window.localStorage.setItem(LOCAL_STORAGE_ID_FILTER, this.filter ? 1 : 0);
			else if( semester == 'default_semester')
				this.$fhcApi.factory
					.stv.filter.setSemester(this.defaultSemester)
					.catch(this.$fhcAlert.handleSystemError);

			this.$nextTick(this.$refs.table.reloadTable);
		},*/
		getLvsByStudent(student_uid){
			return this.$fhcApi.get('api/frontend/v1/stv/pruefung/getLvsByStudent/' + student_uid)
				.then(result => {
					this.listLvs = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
/*		//version post request
getLvsByStudent(student_uid, studiensemester_kurzbz){
			const data = {
				student_uid: student_uid,
				studiensemester_kurzbz: studiensemester_kurzbz
			};
			return this.$fhcApi.post('api/frontend/v1/stv/pruefung/getLvsByStudent/', data)
				.then(result => {
					this.listLvs = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},*/
		getMaFromLv(lv_id){
			return this.$fhcApi.get('api/frontend/v1/stv/pruefung/getMitarbeiterLv/' + lv_id)
				.then(result => {
					this.listMas = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		getLehreinheiten(lv_id, studiensemester_kurzbz) {
			const data = {
				lv_id: lv_id,
				studiensemester_kurzbz: studiensemester_kurzbz
			};

			return this.$fhcApi.post('api/frontend/v1/stv/pruefung/getAllLehreinheiten/', data)
				.then(response => {
					this.listLes = response.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		handleTypeChange(){
			if( this.showHintKommPrfg
				&& (this.pruefungData.pruefungstyp_kurzbz === 'kommPruef'
				|| this.pruefungData.pruefungstyp_kurzbz === 'zusKommPruef')){

				//TODO(Manu) phrase
				this.pruefungData.anmerkung = 'Bitte bei Neuanlage einer kommissionellen Prüfung das Datum der Noteneintragung ' +
					'(i. d. R. heute) eintragen, um den korrekten Fristenablauf der Wiederholung zu ermöglichen.';
			}

		},
		prepareDropdowns(){

			// Get Lvs from Student
/*			this.getLvsByStudent(this.pruefungData.student_uid).then(() => {

			}).catch(error => {
				console.error('Error loading Lvs:', error);
			});*/


			// Get Ma from Lv
			this.getMaFromLv(this.pruefungData.lehrveranstaltung_id).then(() => {
			}).catch(error => {
				console.error('Error loading Ma data:', error);
			});

			// Get Lehreinheiten
			this.getLehreinheiten(this.pruefungData.lehrveranstaltung_id, this.pruefungData.studiensemester_kurzbz).then(() => {

			}).catch(error => {
				console.error('Error loading Lehreinheiten multiple:', error);
			});



			this.$refs.pruefungModal.show();
		},
		onSwitchChange() {
			if (this.isFilterSet) {
				console.log('filter gesetzt: ' + this.defaultSemester + ' uid ' + this.uid);
				this.$refs.table.setFilter("studiensemester_kurzbz", "=", this.defaultSemester);
				//TODO(Manu) TypeError: this.$refs.table.setFilter is not a function

			} else {
				console.log('Alle anzeigen');
			}
		},
	},
	created(){
		this.$fhcApi.get('api/frontend/v1/stv/pruefung/getLvsByStudent/' + this.uid)
			.then(result => {
				this.listLvs = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$fhcApi.get('api/frontend/v1/stv/pruefung/getLvsandLesByStudent/' + this.uid)
			.then(result => {
				this.listLvsAndLes = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$fhcApi.get('api/frontend/v1/stv/pruefung/getLvsAndMas/' + this.uid)
			.then(result => {
				this.listLvsAndMas = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$fhcApi.get('api/frontend/v1/stv/pruefung/getTypenPruefungen')
			.then(result => {
				this.listTypesExam = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$fhcApi.get('api/frontend/v1/stv/pruefung/getNoten')
			.then(result => {
				this.listMarks = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="stv-details-pruefung-pruefung-list 100 pt-3">
	
	{{showHintKommPrfg}}
	{{showZgvErfuellt}}
	
	{{lv_teile}}
	
	<hr>
	{{listLes}}
	aktuelles Sem: {{defaultSemester}} <br>
	current Sem: {{currentSemester}}
	<hr>
	
	  <div>
	  
		<form-input
			container-class="form-switch"
			type="checkbox"
			label="Nur aktuelles Studiensemester anzeigen"
			v-model="isFilterSet"
			@change="onSwitchChange"
			>
		</form-input>
	
		<div class="justify-content-end pb-3">
				<form-input
					container-class="form-switch"
					type="checkbox"
					label="Aktuelles Studiensemester Anzeigen"
					v-model="defaultSemester"
					 @change="setSemester('defaultSemester')"
					>
				</form-input>
<!--				<form-input
					container-class="form-switch"
					type="checkbox"
					label="Aktuelles Studiensemester Anzeigen"
					v-model="semester_intern"
					@update:model-value="setFilter('current_sem')"
					>
				</form-input>
			<div class="col-lg-3">
				<form-input
					container-class="form-switch"
					type="checkbox"
					:label="$p.t('stv/konto_filter_current_stg')"
					v-model="studiengang_kz_intern"
					:disabled="!stg_kz"
					@update:model-value="setFilter('current_stg')"
					>
				</form-input>
			</div>-->
		</div>
	
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			table-only
			:side-menu="false"
			reload
			new-btn-show
			new-btn-label="Pruefung"
			@click:new="actionNewPruefung"
			>
		</core-filter-cmpt>
			
		<!--Modal: pruefungModal-->
		<bs-modal ref="pruefungModal">
			<template #title>
				<p v-if="statusNew" class="fw-bold mt-3">{{$p.t('ui', 'add_pruefung')}}</p>
				<p v-else class="fw-bold mt-3">{{$p.t('ui', 'edit_pruefung')}}</p>
			</template>
	
			<form ref="form-pruefung" @submit.prevent class="row pt-3">
				<legend>Details</legend>
				
				<!--DropDown Lehrveranstaltung-->
				<form-input
					container-class="mb-3"
					type="select"
					name="lehrveranstaltung"
					:label="$p.t('lehre/lehrveranstaltung')"
					v-model="pruefungData.lehrveranstaltung_id"
					@change="actionNewPruefung(pruefungData.lehrveranstaltung_id)"
					>
					<option
						v-for="lv in listLvs"
						:key="lv.lehrveranstaltung_id"
						:value="lv.lehrveranstaltung_id"
						>
						{{lv.bezeichnung}} Semester {{lv.semester}} {{lv.lehrform_kurzbz}}
					</option>
				</form-input>
			
				<!--DropDown Lv-Teil-->
				<form-input
					container-class="mb-3"
					type="select"
					name="lehreinheit"
					:label="$p.t('lehre/lehreinheit')"
					v-model="pruefungData.lehreinheit_id"
					>
					<option v-if="!listLes.length" disabled> -- Bitte Lv_Teil wählen --</option>
					<option
						v-for="le in isStartDropDown ? lv_teile : listLes"
						:key="le.lehreinheit_id"
						:value="le.lehreinheit_id"
						>
						{{le.kurzbz}}-{{le.lehrform_kurzbz}} {{le.bezeichnung}} {{le.gruppe}} ({{le.mitarbeiter_uid}})
					</option>
				</form-input>
			
				<!--DropDown MitarbeiterIn
				//TODO(Manu) phrase
				-->
				<form-input
					container-class="mb-3"
					type="select"
					name="mitarbeiter"
					:label="$p.t('fristenmanagement/mitarbeiterin')"
					v-model="pruefungData.mitarbeiter_uid"
					>
					
					<option :value="null"> -- keine Auswahl -- </option>
					<option
						v-for="ma in isStartDropDown ? lv_teile_ma : listMas"
						:key="ma.mitarbeiter_uid"
						:value="ma.mitarbeiter_uid"
						>
						{{ma.vorname}} {{ma.nachname}}
					</option>				
				</form-input>
			
				<!--DropDown Typ Prüfungstermin
				//TODO (Manu) phrase
				-->
				<form-input
					container-class="mb-3"
					type="select"
					name="typ"
					:label="$p.t('global/typ')"
					v-model="pruefungData.pruefungstyp_kurzbz"
					@change="handleTypeChange()"
					>			
					<option :value="null">
						-- keine Auswahl --
					</option>
					<option
						v-for="typ in listTypesExam"
						:key="typ.pruefungstyp_kurzbz"
						:value="typ.pruefungstyp_kurzbz"
						>
						{{typ.beschreibung}}
					</option>
				</form-input>
				
				<!--DropDown Note-->	
				<form-input
					container-class="mb-3"
					type="select"
					name="typ"
					:label="$p.t('lehre/note')"
					v-model="pruefungData.note"
					>
					<option
						v-for="note in listMarks"
						:key="note.note"
						:value="note.note"
						:disabled="!note.aktiv"
						>
						{{note.bezeichnung}}
					</option>
				</form-input>
				
				<!--DropDown Datum-->
				<form-input
					container-class="mb-3"
					type="DatePicker"
					v-model="pruefungData.datum"
					name="datum"
					:label="$p.t('global/datum')"
					auto-apply
					:enable-time-picker="false"
					format="dd.MM.yyyy"
					preview-format="dd.MM.yyyy"
					:teleport="true"
					>
				</form-input>
				
				<form-input
					container-class="mb-3"
					type="textarea"
					name="name"
					:label="$p.t('global/anmerkung')"
					v-model="pruefungData.anmerkung"
					rows="4"
				>
				</form-input>
			</form>
			
			<template #footer>
			statusNew: {{statusNew}}<br>
			isStartDropDown: {{isStartDropDown}}
				<button type="button" class="btn btn-primary" @click="statusNew ? addPruefung() : updatePruefung(pruefungData.pruefung_id)">{{$p.t('ui', 'speichern')}}</button>
			</template>
		</bs-modal>
				
									
		</div>
	</div>`
};

