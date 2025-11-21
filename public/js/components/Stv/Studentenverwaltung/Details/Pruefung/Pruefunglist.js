import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import FormInput from "../../../../Form/Input.js";
import FormForm from '../../../../Form/Form.js';
import BsModal from "../../../../Bootstrap/Modal.js";

import ApiStvExam from '../../../../../api/factory/stv/exam.js';

export default{
	components: {
		CoreFilterCmpt,
		FormInput,
		FormForm,
		BsModal
	},
	inject: {
		currentSemester: {
			from: 'currentSemester',
		},
		showHintKommPrfg: {
			from: 'configShowHintKommPrfg',
			default: false
		},
		lists: {
			from: 'lists'
		},
	},
	props: {
		uid: String
	},
	data(){
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(ApiStvExam.getPruefungen(this.uid)),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Datum", field: "format_datum"},
					{title: "Lehrveranstaltung", field: "lehrveranstaltung_bezeichnung"},
					{title: "Note", field: "note_bezeichnung"},
					{title: "Anmerkung", field: "anmerkung"},
					{title: "Typ", field: "pruefungstyp_kurzbz"},
					{title: "PruefungId", field: "pruefung_id", visible: false},
					{title: "LehreinheitId", field: "lehreinheit_id", visible: false},
					{title: "Student_uid", field: "student_uid", visible: false},
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
							button.innerHTML = '<i class="fa fa-copy"></i>';
							button.title = this.$p.t('exam', 'newFromOld_pruefung');
							button.addEventListener(
								'click',
								(event) =>
									this.actionNewFromOldPruefung(cell.getData().pruefung_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.title = this.$p.t('exam', 'edit_pruefung');
							button.addEventListener(
								'click',
								(event) =>
									this.actionEditPruefung(cell.getData().pruefung_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('exam', 'delete_pruefung');
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
				layout: 'fitDataStretchFrozen',
				layoutColumnsOnNewData: false,
				height: 'auto',
				index: 'pruefung_id',
				persistenceID: 'stv-details-pruefung-list'
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async () => {
						await this.$p.loadCategory(['fristenmanagement', 'global', 'lehre', 'exam', 'ui']);
						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('format_datum').component.updateDefinition({
							title: this.$p.t('global', 'datum')
						});
						cm.getColumnByField('lehrveranstaltung_bezeichnung').component.updateDefinition({
							title: this.$p.t('lehre', 'lehrveranstaltung')
						});
						cm.getColumnByField('note_bezeichnung').component.updateDefinition({
							title: this.$p.t('lehre', 'note')
						});
						cm.getColumnByField('anmerkung').component.updateDefinition({
							title: this.$p.t('global', 'anmerkung')
						});
						cm.getColumnByField('pruefungstyp_kurzbz').component.updateDefinition({
							title: this.$p.t('global', 'typ')
						});
						cm.getColumnByField('punkte').component.updateDefinition({
							title: this.$p.t('exam', 'punkte')
						});
						cm.getColumnByField('pruefung_id').component.updateDefinition({
							title: this.$p.t('ui', 'pruefung_id')
						});
						cm.getColumnByField('lehreinheit_id').component.updateDefinition({
							title: this.$p.t('global', 'lehreinheit_id')
						});
						cm.getColumnByField('mitarbeiter_uid').component.updateDefinition({
							title: this.$p.t('ui', 'mitarbeiter_uid')
						});
						cm.getColumnByField('student_uid').component.updateDefinition({
							title: this.$p.t('ui', 'student_uid')
						});
						//Uncaught TypeError: e.element.after is not a function
						/*	cm.getColumnByField('actions').component.updateDefinition({
								title: this.$p.t('global', 'actions')
							});*/
					}
				}
			],
			pruefungData: {},
			listTypesExam: [],
			listLvsAndLes: [],
			listLvsAndMas: [],
			listLvs: [],
			listLes: [],
			listMas: [],
			listMarks: [],
			zeugnisData: [],
			checkData:[],
			filter: false,
			statusNew: true,
			isStartDropDown: false,
			isFilterSet: false,
			showHint: false,
		}
	},
	computed:{
		lv_teile(){
			return this.listLvsAndLes.filter(lv => lv.lehrveranstaltung_id == this.pruefungData.lehrveranstaltung_id);
		},
		lv_teile_ma(){
			return this.listLvsAndMas.filter(lv => lv.lehrveranstaltung_id == this.pruefungData.lehrveranstaltung_id);
		}
	},
	methods:{
		loadPruefung(pruefung_id) {
			return this.$api
				.call(ApiStvExam.loadPruefung(pruefung_id))
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

			this.pruefungData.student_uid = this.uid;
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
		addPruefung() {
			return this.$refs.examData
				.call(ApiStvExam.addPruefung(this.pruefungData))
				.then(response => {
					if (response.data)
						this.$fhcAlert.alertDefault('info', 'Info', response.data, true);
					else
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
			this.checkChangeAfterExamDate();
			return this.$refs.examData
				.call(ApiStvExam.updatePruefung(pruefung_id, this.pruefungData))
				.then(response => {
					this.checkData = response.data;
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal('pruefungModal');
					this.resetModal();
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		checkTypeExam(){
			if (this.showHintKommPrfg
				&& (this.pruefungData.pruefungstyp_kurzbz === 'kommPruef'
					|| this.pruefungData.pruefungstyp_kurzbz === 'zusKommPruef')){
				this.showHint = true;
			}
			else
				this.showHint = false;
		},
		checkChangeAfterExamDate() {
			const data = {
				student_uid: this.pruefungData.student_uid,
				studiensemester_kurzbz: this.pruefungData.studiensemester_kurzbz,
				lehrveranstaltung_id: this.pruefungData.lehrveranstaltung_id
			};
			return this.$api
				.call(ApiStvExam.checkZeugnisnoteLv(data))
				.then(result => {
					this.zeugnisData = result.data;
					let checkDate = this.zeugnisData[0].uebernahmedatum === '' ||
					this.zeugnisData[0].benotungsdatum > this.zeugnisData[0].uebernahmedatum
						? this.zeugnisData[0].benotungsdatum
						: this.zeugnisData[0].uebernahmedatum;
					if (checkDate >= this.pruefungData.datum
						&& this.pruefungData.note !== this.zeugnisData[0].note) {
						this.$fhcAlert.alertInfo(this.$p.t('exam', 'hinweis_changeAfterExamDate'));
					}
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		deletePruefung(pruefung_id) {
			return this.$api
				.call(ApiStvExam.deletePruefung(pruefung_id))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
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
			this.statusNew = true;
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		getMaFromLv(lv_id) {
			return this.$api
				.call(ApiStvExam.getMitarbeiterLv(lv_id))
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
			return this.$api
				.call(ApiStvExam.getAllLehreinheiten(data))
				.then(response => {
					this.listLes = response.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		prepareDropdowns(){
			// Get Ma from Lv
			this.getMaFromLv(this.pruefungData.lehrveranstaltung_id).then(() => {
			}).catch(this.$fhcAlert.handleSystemError);

			// Get Lehreinheiten
			this.getLehreinheiten(
				this.pruefungData.lehrveranstaltung_id,
				this.pruefungData.studiensemester_kurzbz)
				.then(() => {
				}).catch(this.$fhcAlert.handleSystemError);

			this.$refs.pruefungModal.show();
		},
		onSwitchChange() {
			if (this.isFilterSet) {
				this.$refs.table.tabulator.setFilter("studiensemester_kurzbz", "=", this.currentSemester);
			}
			else {
				this.$refs.table.tabulator.clearFilter("studiensemester_kurzbz");
			}
		}
	},
	watch: {
		//adaption to go directly through different semesters
		currentSemester(newVal, oldVal) {
			this.$refs.table.tabulator.clearFilter("studiensemester_kurzbz");

			if (newVal && this.isFilterSet) {
				this.$refs.table.tabulator.setFilter("studiensemester_kurzbz", "=", newVal);
			}
		},
	},
	created() {
		this.$api
			.call(ApiStvExam.getLvsByStudent(this.uid))
			.then(result => {
				this.listLvs = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStvExam.getLvsandLesByStudent(this.uid, this.currentSemester))
			.then(result => {
				this.listLvsAndLes = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStvExam.getLvsAndMas(this.uid))
			.then(result => {
				this.listLvsAndMas = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStvExam.getTypenPruefungen())
			.then(result => {
				this.listTypesExam = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStvExam.getNoten())
			.then(result => {
				this.listMarks = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="stv-details-pruefung-pruefung-list 100 pt-3">
	
	  <div>	  
		<div class="justify-content-end pb-3">
			<form-input
				container-class="form-switch"
				type="checkbox"
				:label="$p.t('exam/filter_currentSem')"
				v-model="isFilterSet"
				@change="onSwitchChange"
				>
			</form-input>
		</div>
	
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			:reload-btn-infotext="this.$p.t('table', 'reload')"
			new-btn-show
			:new-btn-label="this.$p.t('lehre', 'pruefung')"
			@click:new="actionNewPruefung"
			>
		</core-filter-cmpt>
			
		<!--Modal: pruefungModal-->
		<bs-modal ref="pruefungModal">
			<template #title>
				<p v-if="statusNew" class="fw-bold mt-3">{{ $p.t('exam', 'add_pruefung') }}</p>
				<p v-else class="fw-bold mt-3">{{ $p.t('exam', 'edit_pruefung') }}</p>
			</template>
	
			<form-form class="row pt-3" ref="examData" @submit.prevent>
				<legend>Details</legend>
				
				<!--DropDown Lehrveranstaltung-->
				<form-input
					container-class="mb-3"
					type="select"
					name="lehrveranstaltung_id"
					:label="$p.t('lehre/lehrveranstaltung') + ' *'"
					v-model="pruefungData.lehrveranstaltung_id"
					@change="actionNewPruefung(pruefungData.lehrveranstaltung_id)"
					>
					<option
						v-for="lv in listLvs"
						:key="lv.lehrveranstaltung_id"
						:value="lv.lehrveranstaltung_id"
						>
						{{ lv.bezeichnung }} Semester {{ lv.semester }} {{ lv.lehrform_kurzbz }}
					</option>
				</form-input>
			
				<!--DropDown Lv-Teil-->
				<form-input
					container-class="mb-3"
					type="select"
					name="lehreinheit_id"
					:label="$p.t('lehre/lehreinheit') + ' *'"
					v-model="pruefungData.lehreinheit_id"
					>
					<option v-if="!listLes.length" disabled> -- {{ $p.t('exam', 'bitteLvteilWaehlen') }} --</option>
					<option
						v-for="le in isStartDropDown ? lv_teile : listLes"
						:key="le.lehreinheit_id"
						:value="le.lehreinheit_id"
						>
						{{ le.kurzbz }}-{{ le.lehrform_kurzbz }} {{ le.bezeichnung }} {{ le.gruppe }} ({{ le.kuerzel }})
					</option>
				</form-input>
			
				<!--DropDown MitarbeiterIn	-->
				<form-input
					container-class="mb-3"
					type="select"
					name="mitarbeiter"
					:label="$p.t('fristenmanagement/mitarbeiterin')"
					v-model="pruefungData.mitarbeiter_uid"
					>
					
					<option :value="null"> -- {{ $p.t('exam', 'keineAuswahl') }} -- </option>
					<option
						v-for="ma in isStartDropDown ? lv_teile_ma : listMas"
						:key="ma.mitarbeiter_uid"
						:value="ma.mitarbeiter_uid"
						>
						{{ ma.vorname }} {{ ma.nachname }}
					</option>				
				</form-input>
			
				<!--DropDown Typ Prüfungstermin	-->
				<form-input
					container-class="mb-3"
					type="select"
					name="pruefungstyp_kurzbz"
					:label="$p.t('global/typ') + ' *'"
					v-model="pruefungData.pruefungstyp_kurzbz"
					@change="checkTypeExam"
					>			
					<option :value="null">-- {{ $p.t('exam', 'keineAuswahl') }} --</option>
					<option
						v-for="typ in listTypesExam"
						:key="typ.pruefungstyp_kurzbz"
						:value="typ.pruefungstyp_kurzbz"
						>
						{{ typ.beschreibung }}
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
						{{ note.bezeichnung }}
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
					text-input
					format="dd.MM.yyyy"
					preview-format="dd.MM.yyyy"
					:teleport="true"
					>
				</form-input>
				
				<div v-if="showHint">
					<div class="form-control d-flex align-items-start">
						<i class="fa fa-info-circle text-primary me-2"></i>
						<div>{{ $p.t('exam', 'hinweis_kommPrfg') }}</div>
					</div>
				</div>
		
				<form-input
					container-class="mb-3"
					type="textarea"
					name="name"
					:label="$p.t('global/anmerkung')"
					v-model="pruefungData.anmerkung"
					rows="4"
				>
				</form-input>
			</form-form>
			
			<template #footer>
				<button type="button" class="btn btn-primary" @click="statusNew ? addPruefung() : updatePruefung(pruefungData.pruefung_id)">{{$p.t('ui', 'speichern')}}</button>
			</template>
		</bs-modal>
				
									
		</div>
	</div>`
};

