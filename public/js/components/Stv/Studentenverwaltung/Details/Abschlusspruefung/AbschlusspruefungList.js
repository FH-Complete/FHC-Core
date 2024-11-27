import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';
import PvAutoComplete from "../../../../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";


export default {
	components: {
		CoreFilterCmpt,
		BsModal,
		FormForm,
		FormInput,
		PvAutoComplete
	},
	props: {
		student: Object
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: this.$fhcApi.factory.stv.abschlusspruefung.getAbschlusspruefung,
				ajaxParams: () => {
					return {
						id: this.student.uid
					};
				},
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "vorsitz", field: "vorsitz_nachname"},
					{title: "abschlussbeurteilung", field: "beurteilung_bezeichnung"},
					{title: "prueferIn1", field: "p1_nachname", visible: false},
					{title: "prueferIn2", field: "p2_nachname", visible: false},
					{title: "prueferIn3", field: "p3_nachname", visible: false},
					{title: "datum", field: "format_datum"},
					{title: "uhrzeit", field: "uhrzeit"},
					{title: "freigabe", field: "format_freigabedatum"},
					{title: "pruefungsantritt", field: "pruefungsantritt_kurzbz"},
					{title: "sponsion", field: "format_sponsion"},
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
							button.title = this.$p.t('ui', 'delete');
							button.addEventListener('click', () =>
								this.actionDeleteAbschlusspruefung(cell.getData().abschlusspruefung_id)
							);
							container.append(button);

							//TODO(Manu) umbau auf dropdown?
							//Prüfungsprotokoll, -zeugnis, -urkunde jeweils Deutsch und English
							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-print"></i>';
							button.title = "Drucken";
							button.addEventListener('click', () =>
								this.actionPrintAbschlusspruefung(cell.getData().abschlusspruefung_id)
							);
							container.append(button);

							return container;
						},
						frozen: true
					},
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData: false,
				height: 'auto',
				selectable: true,
				index: 'abschlusspruefung_id',
				persistenceID: 'stv-details-finalexam'
			},
			tabulatorEvents: [
				{
					/*					event: 'tableBuilt',
										handler: async() => {
											await this.$p.loadCategory(['global','person']);

											let cm = this.$refs.table.tabulator.columnManager;

											cm.getColumnByField('name').component.updateDefinition({
												title: this.$p.t('global', 'name')
											});

											cm.getColumnByField('typ').component.updateDefinition({
												title: this.$p.t('global', 'typ')
											});
											cm.getColumnByField('anschrift').component.updateDefinition({
												title: this.$p.t('person', 'anschrift')
											});
											cm.getColumnByField('kontonr').component.updateDefinition({
												title: this.$p.t('person', 'kontonr')
											});
											cm.getColumnByField('blz').component.updateDefinition({
												title: this.$p.t('person', 'blz')
											});
											cm.getColumnByField('verrechnung').component.updateDefinition({
												title: this.$p.t('person', 'verrechnung')
											});
											cm.getColumnByField('person_id').component.updateDefinition({
												title: this.$p.t('person', 'person_id')
											});
											cm.getColumnByField('abschlusspruefung_id').component.updateDefinition({
												title: this.$p.t('ui', 'abschlusspruefung_id')
											});*/
					/*						cm.getColumnByField('actions').component.updateDefinition({
												title: this.$p.t('global', 'aktionen')
											});*/
				}
			],
			lastSelected: null,
			formData: {
				typStg: null,
				pruefungstyp_kurzbz: null,
				akadgrad_id: null
			},
			statusNew: true,
			arrTypen: [],
			arrAntritte: [],
			arrBeurteilungen: [],
			arrAkadGrad: [],
			arrNoten: [],
			filteredMitarbeiter: [],
			filteredPruefer: [],
			abortController: {
				mitarbeiter: null,
				pruefer: null
			}
		}
	},
	methods: {
		actionNewAbschlusspruefung() {
			this.resetForm();
			this.statusNew = false;
		},
		actionEditAbschlusspruefung(abschlusspruefung_id) {
			this.statusNew = false;
			console.log("action edit: ", abschlusspruefung_id);
			this.loadAbschlusspruefung(abschlusspruefung_id).then(() => {
				if (this.formData.abschlusspruefung_id){
					console.log("activate form?");
				}
			});
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
		addNewAbschlusspruefung(formData) {
			this.formData.plz = this.formData.address.plz;
			return this.$refs.formData.factory.stv.abschlusspruefung.addNewAbschlusspruefung(this.uid, this.formData)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.resetForm();
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					//window.scrollTo(0, 0);
					this.reload();
				});
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		loadAbschlusspruefung(abschlusspruefung_id) {
			this.statusNew = false;
			return this.$fhcApi.factory.stv.abschlusspruefung.loadAbschlusspruefung(abschlusspruefung_id)
				.then(result => {
					this.formData = result.data;
					return result;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updateAbschlusspruefung(abschlusspruefung_id) {
			this.formData.plz = this.formData.address.plz;
			return this.$refs.formData.factory.stv.abschlusspruefung.updateAbschlusspruefung(abschlusspruefung_id,
				this.formData
			).then(response => {
				this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
				this.hideModal('adressModal');
				this.resetForm();
			}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					//window.scrollTo(0, 0);
					this.reload();
				});
		},
		deleteAbschlusspruefung(abschlusspruefung_id) {
			return this.$fhcApi.factory.stv.abschlusspruefung.deleteAbschlusspruefung(abschlusspruefung_id)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(()=> {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		resetForm() {
			this.formData = {};
			this.statusNew = true;
		},
		search(event) {
			if (this.abortController.mitarbeiter) {
				this.abortController.mitarbeiter.abort();
			}
			this.abortController.mitarbeiter = new AbortController();

			return this.$refs.formData.factory.stv.abschlusspruefung.getMitarbeiter(event.query)
				.then(result => {
					this.filteredMitarbeiter = result.data.retval;
				});
		},
		searchNotAkad(event) {
			if (this.abortController.pruefer) {
				this.abortController.pruefer.abort();
			}
			this.abortController.pruefer = new AbortController();

			return this.$refs.formData.factory.stv.abschlusspruefung.getPruefer(event.query)
				.then(result => {
					this.filteredPruefer = result.data.retval;
				});
		},
		setDefaultFormData(){

			//TODO(Manu) check why phrasen not working
			//check lg: if no prüfungsnotizen
			if (this.formData.typStg === 'b')
			{
				this.formData.pruefungstyp_kurzbz = 'Bachelor';
				this.formData.protokoll = this.$p.t('abschlusspruefung', 'pruefungsnotizenBachelor');
				this.formData.protokoll = this.$p.t('ui', 'pruefungsnotizenMaster');
			}
			if (this.formData.typStg === 'd'){
				this.formData.pruefungstyp_kurzbz = 'Diplom';
				this.formData.protokoll = this.$p.t('abschlusspruefung', 'pruefungsnotizenBachelor');
			}
			if (this.formData.typStg === 'lg')
			{
				this.formData.pruefungstyp_kurzbz =  'lgabschluss';
			}
		//	else
		//		console.log("no Typ??");

			if (!this.formData.akadgrad_id && this.arrAkadGrad.length > 0) {
				console.log("in here");
				this.formData.akadgrad_id = this.arrAkadGrad[0].akadgrad_id;
			}
		}
	},
	created() {
		this.$fhcApi.factory.stv.abschlusspruefung.getTypenAbschlusspruefung()
			.then(result => {
				this.arrTypen = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi.factory.stv.abschlusspruefung.getTypenAntritte()
			.then(result => {
				this.arrAntritte = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi.factory.stv.abschlusspruefung.getBeurteilungen()
			.then(result => {
				this.arrBeurteilungen = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi.factory.stv.abschlusspruefung.getNoten()
			.then(result => {
				this.arrNoten = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi.factory.stv.abschlusspruefung.getAkadGrade(this.student.studiengang_kz)
			.then(result => {
				this.arrAkadGrad = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi.factory.stv.abschlusspruefung.getTypStudiengang(this.student.studiengang_kz)
			.then(result => {
				this.formData.typStg = result.data;
				this.setDefaultFormData();
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="stv-details-abschlusspruefung h-100 pb-3">
		<h1>{{this.$p.t('stv','tab_finalexam')}} </h1>
<!--			{{typStg}}
			<hr>
			{{formData}}
			{{arrAkadGrad}}-->
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			new-btn-show
			:new-btn-label="this.$p.t('stv', 'tab_finalexam')"
			@click:new="actionNewAbschlusspruefung"
			>
		</core-filter-cmpt>
		
		<form-form ref="formData">
			<legend>{{this.$p.t('global','details')}}</legend>
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
					:label="$p.t('lehre', 'note')"
					type="select"
					v-model="formData.note"
					name="note"
					>
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
					label="Prüfungsantritt"
					type="select"
					v-model="formData.pruefungsantritt_kurzbz"
					name="pruefungsantritt_kurzbz"
					>
					<option :value="null">-- {{$p.t('fehlermonitoring', 'keineAuswahl')}} --</option>
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
					container-class="col-6 stv-details-abschlusspruefung-vorsitz"
					label="Vorsitz"
					type="autocomplete"
					optionLabel="mitarbeiter" 
					v-model="formData.vorsitz"
					name="vorsitz"
					:suggestions="filteredMitarbeiter"
					@complete="search" 
					:min-length="3"
					>
				</form-input>
				<form-input
					container-class="col-6 stv-details-abschlusspruefung-pruefer1"
					label="Prüferin1"
					type="autocomplete"
					optionLabel="mitarbeiter" 
					v-model="formData.pruefer1"
					name="pruefer1"
					:suggestions="filteredPruefer"
					@complete="searchNotAkad" 
					:min-length="3"
					>
				</form-input>
			</div>
			
			<div class="row mb-3">
			<!--TODO(Manu) bezeichnung_english, filter auf aktive? -->
				<form-input
					container-class="col-6 stv-details-abschlusspruefung-abschlussbeurteilung_kurzbz"
					label="Abschlussbeurteilung"
					type="select"
					v-model="formData.abschlussbeurteilung_kurzbz"
					name="abschlussbeurteilung_kurzbz"
					>
					<option 
						v-for="beurteilung in arrBeurteilungen" 
						:key="beurteilung.abschlussbeurteilung_kurzbz" 
						:value="beurteilung.abschlussbeurteilung_kurzbz" 
						>
						{{beurteilung.bezeichnung}}
					</option>
				</form-input>
				<form-input
					container-class="col-6 stv-details-abschlusspruefung-pruefer2"
					label="Prüferin2"
					type="autocomplete"
					optionLabel="mitarbeiter" 
					v-model="formData.pruefer2"
					name="pruefer2"
					:suggestions="filteredPruefer"
					@complete="searchNotAkad" 
					:min-length="3"
					>
				</form-input>
			</div>
			
			<div class="row mb-3">
				<form-input
					container-class="col-6 stv-details-abschlusspruefung-akadgrad"
					label="Akademischer Grad"
					type="select"
					v-model="formData.akadgrad_id"
					name="akadgrad"
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
					container-class="col-6 stv-details-abschlusspruefung-pruefer3"
					label="Prüferin3"
					type="autocomplete"
					optionLabel="mitarbeiter" 
					v-model="formData.pruefer3"
					name="pruefer3"
					:suggestions="filteredPruefer"
					@complete="searchNotAkad" 
					:min-length="3"
					>
				</form-input>
			</div>
			
			<div class="row mb-3">
				<form-input
					container-class="col-6 stv-details-abschlusspruefung-datum"
					label="Datum"
					type="DatePicker"
					v-model="formData.datum"
					auto-apply 
					:enable-time-picker="false"
					format="dd.MM.yyyy"
					name="datum"
					:teleport="true"
					>
				</form-input>
				<form-input
					container-class="col-6 stv-details-abschlusspruefung-anmerkung"
					label="Anmerkung"
					type="textarea"
					v-model="formData.anmerkung"
					name="anmerkung"
					>
				</form-input>
			</div>
			
			<div class="row mb-3">
				<form-input
					container-class="col-6 stv-details-abschlusspruefung-sponsion"
					label="Sponsion"
					type="DatePicker"
					v-model="formData.sponsion"
					auto-apply 
					:enable-time-picker="false"
					format="dd.MM.yyyy"
					name="sponsion"
					:teleport="true"
					>
				</form-input>
				<form-input
					container-class="col-6 stv-details-abschlusspruefung-protokoll"
					label="Protokoll"
					type="textarea"
					v-model="formData.protokoll"
					name="protokoll"
					:rows= 10
					>
				</form-input>
			</div>
						
			<div>
				<button v-if="statusNew" class="btn btn-primary" @click="addNewAbschlusspruefung()"> {{$p.t('ui', 'speichern')}}</button>
				<button v-else class="btn btn-primary" @click="updateAbschlusspruefung(formData.abschlusspruefung_id)"> {{$p.t('ui', 'speichern')}}</button>
			</div>
	
	</form-form>
	
	</div>



`}