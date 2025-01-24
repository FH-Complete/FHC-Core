import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';
import MobilityPurpose from './List/Purpose.js';
//import LocalPurpose from './List/PurposesLocal.js';

export default {
	components: {
		CoreFilterCmpt,
		BsModal,
		FormForm,
		FormInput,
		MobilityPurpose,
//		LocalPurpose
	},
	inject: {
		$reloadList: {
			from: '$reloadList',
			required: true
		},
		lists: {
			from: 'lists'
		}
	},
	props: {
		student: Object
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: this.$fhcApi.factory.stv.mobility.getMobilitaeten,
				ajaxParams: () => {
					return {
						id: this.student.uid
					};
				},
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Kurzbz", field: "kurzbz"},
					{title: "Nation", field: "nation_code"},
					{title: "Von", field: "format_von"},
					{title: "Bis", field: "format_bis"},
					{title: "bisio_id", field: "bisio_id"},
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
								this.actionEditMobility(cell.getData().bisio_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('ui', 'loeschen');
							button.addEventListener('click', () =>
								this.actionDeleteMobility(cell.getData().bisio_id)
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
				index: 'bisio_id',
				persistenceID: 'stv-details-table_mobiliy'
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async() => {
						await this.$p.loadCategory(['global', 'person', 'stv', 'mobility', 'ui']);


						let cm = this.$refs.table.tabulator.columnManager;

						// cm.getColumnByField('vorsitz_nachname').component.updateDefinition({
						// 	title: this.$p.t('mobility', 'vorsitz_header')
						// });
						// cm.getColumnByField('beurteilung_bezeichnung').component.updateDefinition({
						// 	title: this.$p.t('mobility', 'abschlussbeurteilung')
						// });
						// cm.getColumnByField('p1_nachname').component.updateDefinition({
						// 	title: this.$p.t('mobility', 'pruefer1')
						// });
						// cm.getColumnByField('p2_nachname').component.updateDefinition({
						// 	title: this.$p.t('mobility', 'pruefer2')
						// });
						// cm.getColumnByField('p3_nachname').component.updateDefinition({
						// 	title: this.$p.t('mobility', 'pruefer3')
						// });
						// cm.getColumnByField('format_datum').component.updateDefinition({
						// 	title: this.$p.t('global', 'datum')
						// });
						// cm.getColumnByField('uhrzeit').component.updateDefinition({
						// 	title: this.$p.t('global', 'uhrzeit')
						// });
						// cm.getColumnByField('format_freigabedatum').component.updateDefinition({
						// 	title: this.$p.t('mobility', 'freigabe')
						// });
						// cm.getColumnByField('antritt_bezeichnung').component.updateDefinition({
						// 	title: this.$p.t('mobility', 'pruefungsantritt')
						// });
						// cm.getColumnByField('format_sponsion').component.updateDefinition({
						// 	title: this.$p.t('mobility', 'sponsion')
						// });
						// cm.getColumnByField('anmerkung').component.updateDefinition({
						// 	title: this.$p.t('global', 'anmerkung')
						// });
						// cm.getColumnByField('pruefungstyp_kurzbz').component.updateDefinition({
						// 	title: this.$p.t('global', 'typ')
						// });
						// cm.getColumnByField('bisio_id').component.updateDefinition({
						// 	title: this.$p.t('ui', 'bisio_id')
						// });
						/*
						cm.getColumnByField('actions').component.updateDefinition({
						title: this.$p.t('global', 'aktionen')
												});
						*/
					}
				}
			],
			formData: {
				von: new Date(),
				bis: new Date(),
				mobilitaetsprogramm_code: 7,
				gastnation: 'A',
				herkunftsland: 'A',
				bisio_id: null,
				localPurposes: []
			},
			statusNew: true,
			programsMobility: [],
			listLvs: [],
			listPurposes: [],
			listSupports: [],
		}
	},
	watch: {
		student(){
			if (this.$refs.table) {
				this.$refs.table.reloadTable();
			}
		}
	},
	methods: {
		actionNewMobility() {
			this.resetForm();
			this.statusNew = true;
			//this.setDefaultFormData();
		},
		actionEditMobility(bisio_id) {
			this.resetForm();
		//	this.formData.bisio_id = bisio_id;
			this.statusNew = false;
			this.loadMobility(bisio_id);
		},
		actionDeleteMobility(bisio_id) {
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? bisio_id
					: Promise.reject({handled: true}))
				.then(this.deleteMobility(bisio_id))
				.catch(this.$fhcAlert.handleSystemError);
		},
		addNewMobility() {
			//TODO(Manu) um localPurposes erweitern
/*			console.log(this.formData.localPurposes[0]);
			if(this.formData.localPurposes.length){
				this.$fhcAlert.alertSuccess('nach speichern purposes mit neuer bisio_id zusammenführen');
				return;
			}*/
			const dataToSend = {
				uid: this.student.uid,
				formData: this.formData
			};
			return this.$fhcApi.factory.stv.mobility.addNewMobility(dataToSend)
			//return this.$refs.formMobility.factory.stv.mobility.addNewMobility(dataToSend)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.resetForm();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		loadMobility(bisio_id) {
			return this.$fhcApi.factory.stv.mobility.loadMobility(bisio_id)
				.then(result => {

					this.formData = result.data;
					console.log("after");
					//return result;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updateMobility(bisio_id) {
			const dataToSend = {
				formData: this.formData,
				uid: this.student.uid,
			};
			return this.$fhcApi.factory.stv.mobility.updateMobility(dataToSend)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.resetForm();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		deleteMobility(bisio_id) {
			//TODO(Manu) prompt wird nicht abgewartet!
			return this.$fhcApi.factory.stv.mobility.deleteMobility(bisio_id)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		resetForm() {
			this.formData = {};
			this.formData.von = new Date();
			this.formData.bis = new Date();
			this.formData.mobilitaetsprogramm_code = 7;
			this.formData.gastnation = 'A';
			this.formData.herkunftsland = 'A';
			this.formData.bisio_id = null;
			this.formData.localPurposes = [];
		},
		// --- methods purposes ---
		addMobilityPurpose({zweck_code, bisio_id}){
			let params = {
				bisio_id : bisio_id,
				zweck_code: zweck_code
			};
			return this.$fhcApi.factory.stv.mobility.addMobilityPurpose(params)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));

					this.$refs.purposes.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		deleteMobilityPurpose({zweck_code, bisio_id}){
			let params = {
				bisio_id : bisio_id,
				zweck_code: zweck_code
			};
			return this.$fhcApi.factory.stv.mobility.deleteMobilityPurpose(params)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));

					this.$refs.purposes.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		addPurposeToMobility({zweck_code}){

			console.log("localPurposes befüllen: " , zweck_code);
			this.formData.localPurposes.push(zweck_code);
/*			this.formData.firstPurpose = zweck_code;
			this.$refs.purposes.closeModal();*/
		},
/*		addPurposeToMobility(purpose) {
			this.localPurposes.push(purpose);
		},*/
		//lokale Variante
/*		async saveAllChanges() {
			try {
				const savedData = await this.$fhcApi.factory.stv.mobility.savePurposes({
					id: this.bisio_id,
					purposes: this.localData.filter(item => !item.zweck_code.startsWith('temp_')) // Nur echte IDs senden
				});

				// Synchronisiere temporäre IDs mit echten Daten
				savedData.forEach((item) => {
					const localItem = this.localData.find(local => local.zweck_code === item.tempId);
					if (localItem) {
						localItem.zweck_code = item.zweck_code; // Aktualisiere die echte ID
					}
				});

				this.updateTabulatorData();
			} catch (error) {
				console.error('Fehler beim Speichern der Änderungen:', error);
			}
		},*/
	},
	created() {
		this.$fhcApi.factory.stv.mobility.getProgramsMobility()
			.then(result => {
				this.programsMobility = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi.factory.stv.mobility.getLVList(this.student.studiengang_kz)
			.then(result => {
				this.listLvs = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi.factory.stv.mobility.getListPurposes()
			.then(result => {
				this.listPurposes = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi.factory.stv.mobility.getListSupports()
			.then(result => {
				this.listSupports = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="stv-details-mobility h-100 pb-3">
		<h4>In/out</h4>
		
<!--		{{listSupports}}-->
		<hr>
		{{formData}}
<!--		
		<hr>
		
		{{listPurposes}}-->
		
<!--		{{programsMobility}}-->
<!--		{{listLvs}}-->
	

		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			new-btn-show
			new-btn-label="Mobilität"
			@click:new="actionNewMobility"
			>
		</core-filter-cmpt>

		<form-form v-if="!this.student.length" ref="formMobility" @submit.prevent>

		<div class="row mb-3">
			<legend class="col-6">BIS</legend>
			<legend class="col-6">Outgoing</legend>
		</div>
			
			<div class="row mb-3">
				<form-input
					container-class="col-6 stv-details-mobility-von"
					:label="$p.t('ui', 'von')"
					type="DatePicker"
					v-model="formData.von"
					auto-apply
					:enable-time-picker="false"
					format="dd.MM.yyyy"
					name="von"
					:teleport="true"
					>
				</form-input>
				
				<form-input
					container-class="col-6 stv-details-mobility-typ"
					:label="$p.t('lehre', 'lehrveranstaltung')"
					type="select"
					v-model="formData.lehrveranstaltung"
					name="lehrveranstaltung"
					>
					<option
						v-for="lv in listLvs"
						:key="lv.lehrveranstaltung_id"
						:value="lv.lehrveranstaltung_id"
						>
						{{lv.bezeichnung}} - Semester {{lv.semester}}
					</option>
				</form-input>
			</div>
			
			<div class="row mb-3">
				<form-input
					container-class="col-6 stv-details-mobility-bis"
					:label="$p.t('global', 'bis')"
					type="DatePicker"
					v-model="formData.bis"
					auto-apply
					:enable-time-picker="false"
					format="dd.MM.yyyy"
					name="bis"
					:teleport="true"
					>
				</form-input>
				<form-input
					container-class="col-6 stv-details-mobility-typ"
					:label="$p.t('lehre', 'lehreinheit')"
					type="select"
					v-model="formData.lehreinheit"
					name="lehreinheit"
					>
<!--					<option
						v-for="typ in arrTypen"
						:key="typ.pruefungstyp_kurzbz"
						:value="typ.pruefungstyp_kurzbz"
						>
						{{typ.beschreibung}}
					</option>-->
				</form-input>
			</div>
			
			<div class="row mb-3">
				<form-input
					container-class="col-6 stv-details-mobility-mobilitaetsprogramm"
					:label="$p.t('mobility', 'mobilitaetsprogramm')"
					type="select"
					v-model="formData.mobilitaetsprogramm_code"
					name="mobilitaetsprogramm_code"
					>
					<option
						v-for="mob in programsMobility"
						:key="mob.mobilitaetsprogramm_code"
						:value="mob.mobilitaetsprogramm_code"
						>
						{{mob.kurzbz}} - {{mob.beschreibung}}
					</option>
				</form-input>
				<form-input
					container-class="col-6 stv-details-mobility-ort"
					:label="$p.t('person', 'ort')"
					type="text"
					v-model="formData.ort"
					name="ort"
					>

				</form-input>
			</div>
			
			<div class="row mb-3">
				<form-input
					container-class="col-6 stv-details-mobility-gastnation"
					:label="$p.t('mobility', 'gastnation')"
					type="select"
					v-model="formData.nation_code"
					name="gastnation"
					>
					<option 
					v-for="nation in lists.nations" 
					:key="nation.nation_code" 
					:value="nation.nation_code" 
					:disabled="nation.sperre"
					>
					{{nation.kurztext}}
					</option>
				</form-input>
				<form-input
					container-class="col-6 stv-details-mobility-universitaet"
					:label="$p.t('mobility', 'universitaet')"
					type="text"
					v-model="formData.universitaet"
					name="universitaet"
					>

				</form-input>
			</div>
			
			<div class="row mb-3">
				<form-input
					container-class="col-6 stv-details-mobility-herkunftsland"
					:label="$p.t('mobility', 'herkunftsland')"
					type="select"
					v-model="formData.herkunftsland_code"
					name="herkunftsland"
					>
					<option 
					v-for="nation in lists.nations" 
					:key="nation.nation_code" 
					:value="nation.nation_code" 
					:disabled="nation.sperre"
					>
					{{nation.kurztext}}
					</option>
				</form-input>
				<form-input
					container-class="col-3 stv-details-mobility-ects_erworben"
					:label="$p.t('mobility', 'ects_erworben')"
					type="text"
					v-model="formData.ects_erworben"
					name="ects_erworben"
					>
				</form-input>				
				<form-input
					container-class="col-3 stv-details-mobility-ects_angerechnet"
					:label="$p.t('mobility', 'ects_angerechnet')"
					type="text"
					v-model="formData.ects_angerechnet"
					name="ects_angerechnet"
					>
				</form-input>
			</div>
			
			<div class="row mb-3">
				<div class="col-6 stv-details-mobility-zweck">
					<MobilityPurpose 
						:bisio_id="formData.bisio_id" 
						:listPurposes="listPurposes"
						@deleteMobilityPurpose="deleteMobilityPurpose"
						@setMobilityPurpose="addMobilityPurpose"
						@setMobilityPurposeToNewMobility="addPurposeToMobility"
						ref="purposes"
						></MobilityPurpose>
				</div>
				
<!--				<div class="col-6 stv-details-mobility-zweck">
					<LocalPurpose 
						:listPurposes="listPurposes"
						ref="purposesLocal"
						></LocalPurpose>
				</div>-->
			
				
<!--				<form-input
					container-class="col-6 stv-details-mobility-aufenthalt"
					:label="$p.t('mobility', 'aufenthalt')"
					type="textarea"
					v-model="formData.aufenthalt"
					name="aufenthalt"
					>
				</form-input>-->
			</div>

			<div class="text-end mb-3">
				<button v-if="statusNew" class="btn btn-primary" @click="addNewMobility()"> {{$p.t('ui', 'speichern')}}</button>
				<button v-else class="btn btn-primary" @click="updateMobility(formData.bisio_id)"> {{$p.t('ui', 'speichern')}}</button>
			</div>

		</form-form>

				
	</div>
`
}

