import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';
import PvAutoComplete from "../../../../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";
import AbschlusspruefungDropdown from "./AbschlusspruefungDropdown.js";

import ApiStudiengang from '../../../../../api/factory/studiengang.js';
import ApiStvAbschlusspruefung from '../../../../../api/factory/stv/abschlusspruefung.js';

export default {
	components: {
		CoreFilterCmpt,
		BsModal,
		FormForm,
		FormInput,
		PvAutoComplete,
		AbschlusspruefungDropdown
	},
	inject: {
		cisRoot: {
			from: 'cisRoot'
		},
		config: {
			from: 'config',
			required: true
		},
		$reloadList: {
			from: '$reloadList',
			required: true
		},
		isBerechtigtDocAndOdt: {
			from: 'hasPermissionOutputformat',
			default: false
		}
	},
	props: {
		student: Object
	},
	data() {
		return {
			tabulatorData: [],
			lastSelected: null,
			formData: {
				typStg: null,
				pruefungstyp_kurzbz: null,
				akadgrad_id: null,
				vorsitz: null,
				pruefungsantritt_kurzbz: null,
				abschlussbeurteilung_kurzbz: null,
				datum: null,
				sponsion: null,
				pruefer1: null,
				pruefer2: null,
				pruefer3: null,
				anmerkung: null,
				protokoll: null,
				note: null,
				link: null
			},
			statusNew: true,
			arrTypen: [],
			arrAntritte: [],
			arrBeurteilungen: [],
			arrAkadGrad: [],
			arrNoten: [],
			selectedVorsitz: null,
			filteredMitarbeiter: [],
			filteredPersons: [],
			selectedPruefer1: null,
			selectedPruefer2: null,
			selectedPruefer3: null,
			stgInfo: { typ: '', oe_kurzbz: '' },
			abortController: {
				mitarbeiter: null,
				persons: null
			},
			layout: 'fitDataStretchFrozen',
			layoutColumnsOnNewData: false,
			height: 'auto',
			minHeight: '200',
			index: 'abschlusspruefung_id',
			persistenceID: 'stv-details-finalexam-2025112401'
		}
	},
	computed: {
		tabulatorOptions() {
			const options = {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(ApiStvAbschlusspruefung.getAbschlusspruefung(this.student.uid)),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "vorsitz", field: "vorsitz_nachname"},
					{title: "abschlussbeurteilung", field: "beurteilung_bezeichnung"},
					{title: "prueferIn1", field: "p1_nachname", visible: false},
					{title: "prueferIn2", field: "p2_nachname", visible: false},
					{title: "prueferIn3", field: "p3_nachname", visible: false},
					{
						title: "datum",
						field: "datum",
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour12: false
							});
						}
					},
					{title: "uhrzeit", field: "uhrzeit"},
					{
						title: "freigabe",
						field: "freigabedatum",
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour12: false
							});
						}
					},
					{title: "pruefungsantritt", field: "antritt_bezeichnung"},
					{
						title: "sponsion",
						field: "sponsion",
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour12: false
							});
						}
					},
					{title: "anmerkung", field: "anmerkung"},
					{title: "abschlusspruefung_id", field: "abschlusspruefung_id", visible: false},
					{title: "typ", field: "pruefungstyp_kurzbz", visible: false},

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
								this.actionEditAbschlusspruefung(cell.getData().abschlusspruefung_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('ui', 'loeschen');
							button.addEventListener('click', () =>
								this.actionDeleteAbschlusspruefung(cell.getData().abschlusspruefung_id)
							);
							container.append(button);

							container.append(cell.getData().actionDiv);

							return container;
						},
						frozen: true
					},
				],
			};
			return options;
		},
		tabulatorEvents() {
			const events = [
				{
					event: 'dataLoaded',
					handler: data => this.tabulatorData = data.map(item => {
						item.actionDiv = document.createElement('div');
						return item;
					}),
				},
				{
					event: 'tableBuilt',
					handler: async() => {
						await this.$p.loadCategory(['global', 'person', 'stv', 'abschlusspruefung', 'ui']);


						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('vorsitz_nachname').component.updateDefinition({
							title: this.$p.t('abschlusspruefung', 'vorsitz_header')
						});
						cm.getColumnByField('beurteilung_bezeichnung').component.updateDefinition({
							title: this.$p.t('abschlusspruefung', 'abschlussbeurteilung')
						});
						cm.getColumnByField('p1_nachname').component.updateDefinition({
							title: this.$p.t('abschlusspruefung', 'pruefer1')
						});
						cm.getColumnByField('p2_nachname').component.updateDefinition({
							title: this.$p.t('abschlusspruefung', 'pruefer2')
						});
						cm.getColumnByField('p3_nachname').component.updateDefinition({
							title: this.$p.t('abschlusspruefung', 'pruefer3')
						});
						cm.getColumnByField('datum').component.updateDefinition({
							title: this.$p.t('global', 'datum')
						});
						cm.getColumnByField('uhrzeit').component.updateDefinition({
							title: this.$p.t('global', 'uhrzeit')
						});
						cm.getColumnByField('freigabedatum').component.updateDefinition({
							title: this.$p.t('abschlusspruefung', 'freigabe')
						});
						cm.getColumnByField('antritt_bezeichnung').component.updateDefinition({
							title: this.$p.t('abschlusspruefung', 'pruefungsantritt')
						});
						cm.getColumnByField('sponsion').component.updateDefinition({
							title: this.$p.t('abschlusspruefung', 'sponsion')
						});
						cm.getColumnByField('anmerkung').component.updateDefinition({
							title: this.$p.t('global', 'anmerkung')
						});
						cm.getColumnByField('pruefungstyp_kurzbz').component.updateDefinition({
							title: this.$p.t('global', 'typ')
						});
						cm.getColumnByField('abschlusspruefung_id').component.updateDefinition({
							title: this.$p.t('abschlusspruefung', 'abschlusspruefung_id')
						});
					}
				}
			];
			return events;
		},
		studentUids() {
			if (this.student.uid)
			{
				return [this.student.uid];
			}
			return this.student.map(e => e.uid);
		},
		studentKzs(){
			if (this.student.uid)
			{
				return [this.student.studiengang_kz];
			}
			return this.student.map(e => e.studiengang_kz);
		},
		stg_kz(){
			return this.studentKzs[0];
		},
		showAllFormats() {
			if( this.isBerechtigtDocAndOdt === false
				|| !Array.isArray(this.isBerechtigtDocAndOdt) )
			{
				return false;
			}
			let retval = this.isBerechtigtDocAndOdt.includes(this.stgInfo.oe_kurzbz);
			return retval;
		}
	},
	watch: {
		student(){
			if (this.$refs.table) {
				this.$refs.table.reloadTable();
			}
			this.getStudiengangByKz();
		},
		selectedVorsitz(newVal) {
			this.formData.vorsitz = newVal?.mitarbeiter_uid || null;
		},
		selectedPruefer1(newVal) {
			this.formData.pruefer1 = newVal?.person_id || null;
		},
		selectedPruefer2(newVal) {
			this.formData.pruefer2 = newVal?.person_id || null;
		},
		selectedPruefer3(newVal) {
			this.formData.pruefer3 = newVal?.person_id || null;
		}
	},
	methods: {
		getStudiengangByKz(){
			this.stgInfo = { typ: '', oe_kurzbz: '' };
			this.$api
				.call(ApiStudiengang.getStudiengangByKz(this.stg_kz))
				.then(result => this.stgInfo = result.data)
				.catch(this.$fhcAlert.handleSystemError);
		},
		actionNewAbschlusspruefung() {
			this.resetForm();
			this.statusNew = true;
			this.$refs.finalexamModal.show();
			this.setDefaultFormData();
		},
		actionEditAbschlusspruefung(abschlusspruefung_id) {
			this.resetForm();
			this.statusNew = false;
			this.loadAbschlusspruefung(abschlusspruefung_id).then((result) => {
				//set selectedData to enable viewing label in primevue autocomplete fields
				const data = result.data;
				this.selectedVorsitz = {
					label: this.getPersonLabel(data.pv_titelpre, data.pv_nachname, data.pv_vorname, data.pv_titelpost, data.pv_uid),
					person_id: data.pv_person_id,
					mitarbeiter_uid: data.pv_uid
				};
				if (data.p1_person_id) {
					this.selectedPruefer1 = {
						label: this.getPersonLabel(data.p1_titelpre, data.p1_nachname, data.p1_vorname, data.p1_titelpost),
						person_id: data.p1_person_id
					};
				}
				if (data.p2_person_id) {
					this.selectedPruefer2 = {
						label: this.getPersonLabel(data.p2_titelpre, data.p2_nachname, data.p2_vorname, data.p2_titelpost),
						person_id: data.p2_person_id
					}
				};
				if (data.p3_person_id) {
					this.selectedPruefer3= {
						label: this.getPersonLabel(data.p3_titelpre, data.p3_nachname, data.p3_vorname, data.p3_titelpost),
						person_id: data.p3_person_id
					};
				}
			});
			this.$refs.finalexamModal.show();
		},
		getPersonLabel(titelpre, nachname, vorname, titelpost, uid) {
			return nachname + ' ' + vorname + (titelpre ? ' ' + titelpre : '') + (titelpost ? ' ' + titelpost : '') + (uid ? ' (' + uid + ')' : '');

		},
		actionDeleteAbschlusspruefung(abschlusspruefung_id) {
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? abschlusspruefung_id
					: Promise.reject({handled: true}))
				.then(this.deleteAbschlusspruefung)
				.catch(this.$fhcAlert.handleSystemError);
		},
		addNewAbschlusspruefung() {
			const dataToSend = {
				uid: this.student.uid,
				formData: this.formData
			};

			return this.$refs.formFinalExam
				.call(ApiStvAbschlusspruefung.addNewAbschlusspruefung(dataToSend))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal('finalexamModal');
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
		loadAbschlusspruefung(abschlusspruefung_id) {
			return this.$api
				.call(ApiStvAbschlusspruefung.loadAbschlusspruefung(abschlusspruefung_id))
				.then(result => {
					this.formData = result.data;
					this.formData.link = FHC_JS_DATA_STORAGE_OBJECT.app_root + 'index.ci.php/lehre/Pruefungsprotokoll/showProtokoll?abschlusspruefung_id=' + this.formData.abschlusspruefung_id;
					return result;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updateAbschlusspruefung(abschlusspruefung_id) {
			const dataToSend = {
				id: abschlusspruefung_id,
				formData: this.formData
			};
			return this.$refs.formFinalExam
				.call(ApiStvAbschlusspruefung.updateAbschlusspruefung(dataToSend))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal('finalexamModal');
					this.resetForm();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		deleteAbschlusspruefung(abschlusspruefung_id) {
			return this.$api
				.call(ApiStvAbschlusspruefung.deleteAbschlusspruefung(abschlusspruefung_id))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		resetForm() {
			this.formData.pruefungstyp_kurzbz = null;
			this.formData.akadgrad_id = null;
			this.formData.vorsitz = null;
			this.formData.pruefungsantritt_kurzbz = null;
			this.formData.abschlussbeurteilung_kurzbz = null;
			this.formData.datum = null;
			this.formData.sponsion = null;
			this.formData.pruefer1 = null;
			this.formData.pruefer2 = null;
			this.formData.pruefer3 = null;
			this.formData.anmerkung = null;
			this.formData.protokoll = null;
			this.formData.note = null;
			this.selectedVorsitz = null;
			this.selectedPruefer1 = null;
			this.selectedPruefer2 = null;
			this.selectedPruefer3 = null;

		},
		setDefaultFormData() {

			this.resetForm();

			if (this.stgInfo.typ === 'b') {
				this.formData.pruefungstyp_kurzbz = 'Bachelor';
			}
			if (this.stgInfo.typ === 'd' || this.stgInfo === 'm') {
				this.formData.pruefungstyp_kurzbz = 'Diplom';
			}
			if (this.stgInfo.typ === 'lg') {
				this.formData.pruefungstyp_kurzbz = 'lgabschluss';
			}

			if (!this.formData.akadgrad_id && this.arrAkadGrad.length > 0) {
				this.formData.akadgrad_id = this.arrAkadGrad[0].akadgrad_id;
			}
		},
		printDocument(link) {
			window.open(link, '_blank');
		},
		searchMitarbeiter(event) {
			if (this.abortController.mitarbeiter) {
				this.abortController.mitarbeiter.abort();
			}

			this.abortController.mitarbeiter = new AbortController();

			return this.$api
				.call(ApiStvAbschlusspruefung.getMitarbeiter(event.query))
				.then(result => {
					this.filteredMitarbeiter = [];
					for (let mitarbeiter of result.data.retval) {
						this.filteredMitarbeiter.push(
							{
								label: this.getPersonLabel(
									mitarbeiter.titelpre,
									mitarbeiter.nachname,
									mitarbeiter.vorname,
									mitarbeiter.titelpost,
									mitarbeiter.mitarbeiter_uid
								),
								person_id: mitarbeiter.person_id,
								mitarbeiter_uid: mitarbeiter.mitarbeiter_uid
							}
						);
					}
				});
		},
		searchPerson(event) {
			if (this.abortController.persons) {
				this.abortController.persons.abort();
			}

			this.abortController.persons = new AbortController();

			return this.$api
				.call(ApiStvAbschlusspruefung.getPruefer(event.query))
				.then(result => {
					this.filteredPersons = [];
					for (let person of result.data.retval) {
						this.filteredPersons.push(
							{
								label: this.getPersonLabel(
									person.titelpre,
									person.nachname,
									person.vorname,
									person.titelpost,
									person.person_uid
								),
								person_id: person.person_id
							}
						);
					}
				});
		},
	},
	created() {
		this.$api
			.call(ApiStvAbschlusspruefung.getTypenAbschlusspruefung())
			.then(result => {
				this.arrTypen = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStvAbschlusspruefung.getTypenAntritte())
			.then(result => {
				this.arrAntritte = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStvAbschlusspruefung.getBeurteilungen())
			.then(result => {
				this.arrBeurteilungen = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStvAbschlusspruefung.getNoten())
			.then(result => {
				this.arrNoten = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStvAbschlusspruefung.getAkadGrade(this.student.studiengang_kz))
			.then(result => {
				this.arrAkadGrad = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		if (!this.student.length) {
			this.$api
				.call(ApiStudiengang.getStudiengangByKz(this.student.studiengang_kz))
				.then(result => {
					this.stgInfo = result.data;
					this.setDefaultFormData();
				})
				.catch(this.$fhcAlert.handleSystemError);
		} else
			this.getStudiengangByKz();
	},
	template: `
	<div class="stv-details-abschlusspruefung h-100 pb-3">
		<h4>{{this.$p.t('stv','tab_finalexam')}}</h4>

		<div v-if="this.student.length">
			<abschlusspruefung-dropdown
				:showAllFormats="showAllFormats"
				:studentUids="studentUids"
				:showDropDownMulti="true"
				:stgTyp="stgInfo.typ"
				:stgKz="stg_kz"
				:cisRoot="cisRoot"
				@linkGenerated="printDocument"
			></abschlusspruefung-dropdown>
		</div>

		<core-filter-cmpt
			v-if="!this.student.length"
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			:reload-btn-infotext="this.$p.t('table', 'reload')"
			new-btn-show
			:new-btn-label="this.$p.t('stv', 'tab_finalexam')"
			@click:new="actionNewAbschlusspruefung"
			>
		</core-filter-cmpt>

		<!--Modal: finalexamModal-->
		<bs-modal ref="finalexamModal" dialog-class="modal-xl modal-dialog-scrollable">
			<template #title>
				<p v-if="statusNew" class="fw-bold mt-3">{{$p.t('abschlusspruefung', 'abschluessPruefungAnlegen')}}</p>
				<p v-else class="fw-bold mt-3">{{$p.t('abschlusspruefung', 'abschluessPruefungBearbeiten')}}</p>
			</template>

			<form-form v-if="!this.student.length" ref="formFinalExam" @submit.prevent>

				<legend>{{this.$p.t('global','details')}}</legend>
				<p v-if="statusNew">[{{$p.t('ui', 'neu')}}]</p>
				<div class="row mb-3">
					<form-input
						container-class="col-6 stv-details-abschlusspruefung-typ"
						:label="$p.t('global', 'typ')"
						type="select"
						v-model="formData.pruefungstyp_kurzbz"
						name="pruefungstyp_kurzbz"
						>
						<option
							v-for="typ in arrTypen"
							:key="typ.pruefungstyp_kurzbz"
							:value="typ.pruefungstyp_kurzbz"
							>
							{{typ.beschreibung}}
						</option>
					</form-input>
					<form-input
						container-class="col-6 stv-details-abschlusspruefung-note"
						:label="$p.t('abschlusspruefung', 'notekommpruefung')"
						type="select"
						v-model="formData.note"
						name="note"
						>
						<option :value="null"> -- {{$p.t('fehlermonitoring', 'keineAuswahl')}} -- </option>
						<option
							v-for="note in arrNoten"
							:key="note.note"
							:value="note.note"
							>
							{{note.bezeichnung}}
						</option>
					</form-input>
				</div>

				<div class="row mb-3">
					<form-input
						container-class="col-6 stv-details-abschlusspruefung-pruefungsantritt"
						:label="$p.t('abschlusspruefung', 'pruefungsantritt')"
						type="select"
						v-model="formData.pruefungsantritt_kurzbz"
						name="pruefungsantritt_kurzbz"
						>
						<option :value="null"> -- {{$p.t('fehlermonitoring', 'keineAuswahl')}} -- </option>
						<option
							v-for="antritt in arrAntritte"
							:key="antritt.pruefungsantritt_kurzbz"
							:value="antritt.pruefungsantritt_kurzbz"
							>
							{{antritt.bezeichnung}}
						</option>
					</form-input>
				</div>

				<div class="row mb-3">
					<form-input
						type="autocomplete"
						container-class="col-6 stv-details-abschlusspruefung-vorsitz"
						:label="$p.t('abschlusspruefung', 'vorsitz')"
						name="vorsitz"
						v-model="selectedVorsitz"
						optionLabel="label"
						optionValue="mitarbeiter_uid"
						dropdown
						forceSelection
						:suggestions="filteredMitarbeiter"
						@complete="searchMitarbeiter"
						:min-length="3"
					>
					</form-input>
					<form-input
						type="autocomplete"
						container-class="col-6 stv-details-abschlusspruefung-pruefer1"
						:label="$p.t('abschlusspruefung', 'pruefer1')"
						name="pruefer1"
						v-model="selectedPruefer1"
						optionLabel="label"
						optionValue="person_id"
						dropdown
						forceSelection
						:suggestions="filteredPersons"
						@complete="searchPerson"
						:min-length="3"
					>
					</form-input>
				</div>
				
				<div class="row mb-3">
					<form-input
						container-class="col-6 stv-details-abschlusspruefung-abschlussbeurteilung_kurzbz"
						:label="$p.t('abschlusspruefung', 'abschlussbeurteilung')"
						type="select"
						v-model="formData.abschlussbeurteilung_kurzbz"
						name="abschlussbeurteilung_kurzbz"
						>
						<option :value="null"> -- {{$p.t('fehlermonitoring', 'keineAuswahl')}} -- </option>
						<option
							v-for="beurteilung in arrBeurteilungen"
							:key="beurteilung.abschlussbeurteilung_kurzbz"
							:value="beurteilung.abschlussbeurteilung_kurzbz"
							>
							{{beurteilung.bezeichnung}}
						</option>
					</form-input>
					<form-input
						type="autocomplete"
						container-class="col-6 stv-details-abschlusspruefung-pruefer2"
						:label="$p.t('abschlusspruefung', 'pruefer2')"
						name="pruefer2"
						v-model="selectedPruefer2"
						optionLabel="label"
						optionValue="person_id"
						dropdown
						forceSelection
						:suggestions="filteredPersons" 
						@complete="searchPerson"
						:min-length="3"
					>
					</form-input>
				</div>

				<div class="row mb-3">
					<form-input
						container-class="col-6 stv-details-abschlusspruefung-akadgrad"
						:label="$p.t('abschlusspruefung', 'akadGrad')"
						type="select"
						v-model="formData.akadgrad_id"
						name="akadgrad_id"
						>
						<option
							v-for="grad in arrAkadGrad"
							:key="grad.akadgrad_id"
							:value="grad.akadgrad_id"
							>
							{{grad.titel}}
						</option>
					</form-input>
					<form-input
						type="autocomplete"
						container-class="col-6 stv-details-abschlusspruefung-pruefer3"
						:label="$p.t('abschlusspruefung', 'pruefer3')"
						name="pruefer3"
						v-model="selectedPruefer3"
						optionLabel="label"
						optionValue="person_id"
						dropdown
						forceSelection
						:suggestions="filteredPersons" 
						@complete="searchPerson"
						:min-length="3"
					>
					</form-input>
				</div>

				<div class="row mb-3">
					<form-input
						container-class="col-6 stv-details-abschlusspruefung-datum"
						:label="$p.t('global', 'datum')"
						type="DatePicker"
						v-model="formData.datum"
						model-type="yyyy-MM-dd"
						auto-apply
						:enable-time-picker="false"
						text-input
						format="dd.MM.yyyy"
						name="datum"
						:teleport="true"
						>
					</form-input>
					<form-input
						container-class="col-6 stv-details-abschlusspruefung-anmerkung"
						:label="$p.t('global', 'anmerkung')"
						type="textarea"
						v-model="formData.anmerkung"
						name="anmerkung"
						>
					</form-input>
				</div>

				<div class="row mb-3">
					<form-input
						container-class="col-6 stv-details-abschlusspruefung-sponsion"
						:label="$p.t('abschlusspruefung', 'sponsion')"
						type="DatePicker"
						v-model="formData.sponsion"
						model-type="yyyy-MM-dd"
						auto-apply
						:enable-time-picker="false"
						text-input
						format="dd.MM.yyyy"
						name="sponsion"
						:teleport="true"
						>
					</form-input>
					<form-input
						container-class="col-6 stv-details-abschlusspruefung-protokoll"
						:label="$p.t('abschlusspruefung', 'protokoll')"
						type="textarea"
						v-model="formData.protokoll"
						name="protokoll"
						:rows= 10
						readonly
						>
					</form-input>
				</div>

				<div class="row mb-3 col-6">
					<div class="col">
						<p >{{$p.t('abschlusspruefung', 'zurBeurteilung')}}</p>
					</div>
					<div class="col">
						<p>
							<a :href="formData.link" target="_blank" rel="noopener noreferrer">
								{{$p.t('abschlusspruefung', 'pruefungsprotokoll')}}
							</a>
						</p>
					</div>
				</div>

			</form-form>

			<template #footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{$p.t('ui', 'abbrechen')}}</button>
					<button v-if="statusNew" class="btn btn-primary" @click="addNewAbschlusspruefung()"> {{$p.t('ui', 'speichern')}}</button>
					<button v-else class="btn btn-primary" @click="updateAbschlusspruefung(formData.abschlusspruefung_id)"> {{$p.t('ui', 'speichern')}}</button>
			</template>

		</bs-modal>

		<Teleport v-for="data in tabulatorData" :key="data.abschlusspruefung_id" :to="data.actionDiv">
			<abschlusspruefung-dropdown
				:showAllFormats="showAllFormats"
				:showDropDownMulti="false"
				:abschlusspruefung_id="data.abschlusspruefung_id"
				:studentUids="data.student_uid"
				:stgPrfgTyp="data.pruefungstyp_kurzbz"
				:stgKz="stg_kz"
				:cisRoot="cisRoot"
				@linkGenerated="printDocument"
			></abschlusspruefung-dropdown>
		</Teleport>		
	</div>
`
}
