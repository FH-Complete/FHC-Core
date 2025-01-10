import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';

export default {
	components: {
		CoreFilterCmpt,
		BsModal,
		FormForm,
		FormInput
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
								this.actionEditMobility(cell.getData().mobility_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('ui', 'loeschen');
							button.addEventListener('click', () =>
								this.actionDeleteMobility(cell.getData().mobility_id)
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
				index: 'mobility_id',
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
						// cm.getColumnByField('mobility_id').component.updateDefinition({
						// 	title: this.$p.t('ui', 'mobility_id')
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
				mobilitaetsprogramm: 7,
				gastnation: 'A',
				herkunftsland: 'A',
			},
			statusNew: true,
			programsMobility: [],
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
		getStudiengangsTyp(){
			this.stgTyp = '';
			this.$fhcApi.factory.stv.mobility.getTypStudiengang(this.stg_kz)
					.then(result => this.stgTyp = result.data)
					.catch(this.$fhcAlert.handleSystemError);
		},
		actionNewMobility() {
			this.resetForm();
			this.statusNew = true;
			//this.setDefaultFormData();
		},
		actionEditMobility(mobility_id) {
			this.resetForm();
			this.statusNew = false;
			this.loadMobility(mobility_id);
		},
		actionDeleteMobility(mobility_id) {
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? mobility_id
					: Promise.reject({handled: true}))
				.then(this.deletemobility)
				.catch(this.$fhcAlert.handleSystemError);
		},
		addNewMobility() {
			const dataToSend = {
				uid: this.student.uid,
				formData: this.formData
			};

			return this.$refs.formFinalExam.factory.stv.mobility.addNewmobility(dataToSend)
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
		loadMobility(mobility_id) {
			return this.$fhcApi.factory.stv.mobility.loadmobility(mobility_id)
				.then(result => {
					this.formData = result.data;
					//TODO(Manu) check if cisRoot is okay
					this.formData.link = this.cisRoot + 'index.ci.php/lehre/Pruefungsprotokoll/showProtokoll?mobility_id=' + this.formData.mobility_id + '&fhc_controller_id=67481e5ed5490';
					return result;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updateMobility(mobility_id) {
			const dataToSend = {
				id: mobility_id,
				formData: this.formData
			};
			return this.$refs.formFinalExam.factory.stv.mobility.updatemobility(dataToSend)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.resetForm();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		deleteMobility(mobility_id) {
			return this.$fhcApi.factory.stv.mobility.deletemobility(mobility_id)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		resetForm() {
			this.formData = null;
		},
	},
	created() {
		this.$fhcApi.factory.stv.mobility.getProgramsMobility()
			.then(result => {
				this.programsMobility = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="stv-details-mobility h-100 pb-3">
		<h4>In/out</h4>
		
		{{formData}}
	

		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			new-btn-show
			new-btn-label="Mobilität"
			@click:new="actionNewmobility"
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
					v-model="formData.mobilitaetsprogramm"
					name="mobilitaetsprogramm"
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
					type="select"
					v-model="formData.ort"
					name="ort"
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
					container-class="col-6 stv-details-mobility-gastnation"
					:label="$p.t('mobility', 'gastnation')"
					type="select"
					v-model="formData.gastnation"
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
					type="select"
					v-model="formData.ort"
					name="ort"
					>

				</form-input>
			</div>
			
			<div class="row mb-3">
				<form-input
					container-class="col-6 stv-details-mobility-herkunftsland"
					:label="$p.t('mobility', 'herkunftsland')"
					type="select"
					v-model="formData.herkunftsland"
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
					type="input"
					v-model="formData.ects_erworben"
					name="ects_erworben"
					>
				</form-input>				
				<form-input
					container-class="col-3 stv-details-mobility-ects_angerechnet"
					:label="$p.t('mobility', 'ects_angerechnet')"
					type="input"
					v-model="formData.ects_angerechnet"
					name="ects_angerechnet"
					>
				</form-input>
			</div>
			
			<div class="row mb-3">
				<form-input
					container-class="col-6 stv-details-mobility-zweck"
					:label="$p.t('mobility', 'zweck')"
					type="textarea"
					v-model="formData.zweck"
					name="zweck"
					>
				</form-input>
				<form-input
					container-class="col-6 stv-details-mobility-aufenthalt"
					:label="$p.t('mobility', 'aufenthalt')"
					type="textarea"
					v-model="formData.aufenthalt"
					name="aufenthalt"
					>
				</form-input>
			</div>

			<div class="text-end mb-3">
				<button v-if="statusNew" class="btn btn-primary" @click="addNewMobility()"> {{$p.t('ui', 'speichern')}}</button>
				<button v-else class="btn btn-primary" @click="updateMobility(formData.mobility_id)"> {{$p.t('ui', 'speichern')}}</button>
			</div>

		</form-form>

				
	</div>
`
}

