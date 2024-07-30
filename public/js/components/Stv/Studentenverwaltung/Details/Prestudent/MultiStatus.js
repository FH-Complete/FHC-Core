import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import BsConfirm from "../../../../Bootstrap/Confirm.js";
import FormInput from '../../../../Form/Input.js';
import StatusModal from '../Status/Modal.js';
import StatusDropdown from '../Status/Dropdown.js';

export default{
	components: {
		CoreFilterCmpt,
		BsModal,
		FormInput,
		StatusModal,
		StatusDropdown
	},
	inject: {
		defaultSemester: {
			from: 'defaultSemester',
		},
		hasPermissionToSkipStatusCheck: {
			from: 'hasPermissionToSkipStatusCheck',
			default: false
		},
		$reloadList: {
			from: '$reloadList',
			required: true
		}
	},
	computed: {
		prestudentIds() {
			if (this.modelValue.prestudent_id)
			{
				return [this.modelValue.prestudent_id];
			}
			return this.modelValue.map(e => e.prestudent_id);
		},
		updateData() {
			const dataArray = [];
			if (this.modelValue.prestudent_id) {
				const newObj = {
					prestudent_id : this.modelValue.prestudent_id,
					studiensemester_kurzbz : this.defaultSemester,
					ausbildungssemester : this.modelValue.semester,
					orgform_kurzbz: this.modelValue.orgform_kurzbz,
					name: `${this.modelValue.vorname} ${this.modelValue.nachname}`
				};
				dataArray.push(newObj);
				return dataArray;
			}
			else
			{
				for (const item of this.modelValue) {
					const newObj = {
						prestudent_id: item.prestudent_id,
						ausbildungssemester: item.semester,
						studiensemester_kurzbz: this.defaultSemester,
						orgform_kurzbz: item.orgform_kurzbz,
						name: `${item.vorname} ${item.nachname}`
					};
					dataArray.push(newObj);
				}

				return dataArray;
			}
		},
		showToolbarStudent() {
			if (Array.isArray(this.modelValue)) {
				if (!this.modelValue.length)
					return false;
				return this.modelValue.every(item => item.uid);
			}
			return !!this.modelValue.uid;
		},
		showToolbar() {
			return this.showToolbarStudent || this.showToolbarInteressent;
		},
		showToolbarInteressent() {
			if (Array.isArray(this.modelValue)) {
				if (!this.modelValue.length)
					return false;
				return !this.modelValue.some(item => item.uid);
			}
			return !this.modelValue.uid;
		}
	},
	props: {
		modelValue: Object
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'api/frontend/v1/stv/Status/getHistoryPrestudent/' + this.modelValue.prestudent_id,
				ajaxRequestFunc: this.$fhcApi.get,
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Kurzbz", field: "status_kurzbz", tooltip: true},
					{title: "StSem", field: "studiensemester_kurzbz"},
					{title: "Sem", field: "ausbildungssemester"},
					{title: "Lehrverband", field: "lehrverband", width: 72},
					{title: "Datum", field: "format_datum"},
					{title: "Studienplan", field: "bezeichnung"},
					{title: "BestätigtAm", field: "format_bestaetigtam"},
					{title: "AbgeschicktAm", field: "format_bewerbung_abgeschicktamum", visible:false},
					{title: "Statusgrund", field: "statusgrund_bezeichnung"},
					{title: "Organisationsform", field: "orgform_kurzbz", visible: false},
					{title: "PrestudentInId", field: "prestudent_id", visible: false},
					{title: "StudienplanId", field: "studienplan_id", visible: false},
					{title: "Anmerkung", field: "anmerkung", visible: false},
					{title: "BestätigtVon", field: "bestaetigtvon", visible: false},
					{title: "InsertAmUm", field: "format_insertamum", visible: false},
					{title: "InsertVon", field: "insertvon", visible: false},
					{title: "UpdateAmUm", field: "format_updateamum", visible: false},
					{title: "UpdateVon", field: "updatevon", visible: false},
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {

							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							if (this.dataMeldestichtag && this.dataMeldestichtag > cell.getData().datum && !this.hasPermissionToSkipStatusCheck)
								button.className = 'btn btn-outline-secondary btn-action';
							else
								button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-forward"></i>';
							button.title = 'Status vorrücken';
							button.addEventListener(
								'click',
								() =>
								this.actionAdvanceStatus(cell.getData().status_kurzbz, cell.getData().studiensemester_kurzbz, cell.getData().ausbildungssemester)
							);
							container.append(button);

							button = document.createElement('button');
							if (this.dataMeldestichtag && this.dataMeldestichtag > cell.getData().datum && !this.hasPermissionToSkipStatusCheck)
								button.className = 'btn btn-outline-secondary btn-action';
							else
								button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-check"></i>';
							button.title = 'Status bestätigen';
							button.addEventListener('click', () =>
								this.actionConfirmStatus(cell.getData().status_kurzbz, cell.getData().studiensemester_kurzbz, cell.getData().ausbildungssemester)
							);
							if (cell.getData().bestaetigtam || !cell.getData().bewerbung_abgeschicktamum)
								button.disabled = true;
							container.append(button);

							button = document.createElement('button');
							if (this.dataMeldestichtag && this.dataMeldestichtag > cell.getData().datum && !this.hasPermissionToSkipStatusCheck)
								button.className = 'btn btn-outline-secondary btn-action';
							else
								button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.title = 'Status bearbeiten';
							button.addEventListener('click', (event) =>
								this.actionEditStatus(cell.getData().status_kurzbz, cell.getData().studiensemester_kurzbz, cell.getData().ausbildungssemester)
							);
							container.append(button);

							button = document.createElement('button');
							if (this.dataMeldestichtag && this.dataMeldestichtag > cell.getData().datum && !this.hasPermissionToSkipStatusCheck)
								button.className = 'btn btn-outline-secondary btn-action';
							else
								button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = 'Status löschen';
							button.addEventListener('click', () =>
								this.actionDeleteStatus(cell.getData().status_kurzbz, cell.getData().studiensemester_kurzbz, cell.getData().ausbildungssemester)
							);
							container.append(button);

							return container;
						},
						frozen: true
					},
				],
				rowFormatter: (row) => {
					const rowData = row.getData();
					if (this.dataMeldestichtag && this.dataMeldestichtag > rowData.datum)
					{
						row.getElement().classList.add('disabled');
					}
				},
				layout: 'fitDataFill',
				layoutColumnsOnNewData: false,
				height: 'auto',
				selectable: false,
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async () => {
						await this.$p.loadCategory(['lehre','global','person']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('lehrverband').component.updateDefinition({
									title: this.$p.t('lehre', 'lehrverband')
								});

						cm.getColumnByField('format_bestaetigtam').component.updateDefinition({
							title: this.$p.t('lehre', 'bestaetigt_am')
						});

						cm.getColumnByField('format_bewerbung_abgeschicktamum').component.updateDefinition({
							title: this.$p.t('lehre', 'bewerbung_abgeschickt_am')
						});

						cm.getColumnByField('bezeichnung').component.updateDefinition({
							title: this.$p.t('lehre', 'studienplan')
						});

						cm.getColumnByField('actions').component.updateDefinition({
							title: this.$p.t('global', 'aktionen')
						});

						cm.getColumnByField('format_datum').component.updateDefinition({
							title: this.$p.t('global', 'datum')
						});

						cm.getColumnByField('anmerkung').component.updateDefinition({
							title: this.$p.t('global', 'anmerkung')
						});

						cm.getColumnByField('bestaetigtvon').component.updateDefinition({
							title: this.$p.t('lehre', 'bestaetigt_von')
						});

						cm.getColumnByField('format_insertamum').component.updateDefinition({
							title: this.$p.t('lehre', 'insert_am')
						});

						cm.getColumnByField('insertvon').component.updateDefinition({
							title: this.$p.t('lehre', 'insert_von')
						});
					}
				}
			],
			statusData: {},
			statusId: {},
			dataMeldestichtag: null,
			statusNew: true
		};
	},
	watch: {
		modelValue() {
			if (this.$refs.table) {
				if (this.$refs.table.tableBuilt)
					this.$refs.table.tabulator.setData('api/frontend/v1/stv/Status/getHistoryPrestudent/' + this.modelValue.prestudent_id);
				else
					this.data.tabulatorOptions.ajaxURL = 'api/frontend/v1/stv/Status/getHistoryPrestudent/' + this.modelValue.prestudent_id;
			}
		}
	},
	methods: {
		actionNewStatus() {
			this.$refs.test.open(this.modelValue);
		},
		actionEditStatus(status, stdsem, ausbildungssemester) {
			this.$refs.test.open(this.modelValue, status, stdsem, ausbildungssemester);
		},
		actionDeleteStatus(status, stdsem, ausbildungssemester) {
			const statusId = {
				prestudent_id: this.modelValue.prestudent_id,
				status_kurzbz: status,
				studiensemester_kurzbz: stdsem,
				ausbildungssemester: ausbildungssemester
			};

			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? 'api/frontend/v1/stv/status/isLastStatus/' + statusId.prestudent_id
					: Promise.reject({handled: true})
				)
				.then(this.$fhcApi.get)
				.then(result => result.data
					? new Promise((resolve, reject) => { BsConfirm.popup(this.$p.t('lehre', 'last_status_confirm_delete')).then(resolve).catch(() => reject({handled:true})) })
					: true
				)
				.then(result => result
					? 'api/frontend/v1/stv/status/deleteStatus/' + Object.values(statusId).join('/')
					: Promise.reject({handled: true})
				)
				.then(this.$fhcApi.post)
				.then(() => this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete')))
				.then(this.reload)
				.then(this.$reloadList)
				.catch(this.$fhcAlert.handleSystemError);
		},
		actionAdvanceStatus(status, stdsem, ausbildungssemester) {
			this.statusId = {
				'prestudent_id': this.modelValue.prestudent_id,
				'status_kurzbz': status,
				'studiensemester_kurzbz': stdsem,
				'ausbildungssemester': ausbildungssemester
			};
			this.loadStatus(this.statusId).then(() => {
				if(this.statusData)
					this.advanceStatus(this.statusId);
			});
		},
		actionConfirmStatus(status, stdsem, ausbildungssemester) {
			BsConfirm
				.popup(this.$p.t('stv', 'status_confirm_popup'))
				.then(() => this.$fhcApi.post(
					'api/frontend/v1/stv/status/confirmStatus/' +
					this.modelValue.prestudent_id + '/' +
					status + '/' +
					stdsem + '/' +
					ausbildungssemester
				))
				.then(() => this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successConfirm')))
				.then(this.reload)
				.catch(this.$fhcAlert.handleSystemError);
		},
		advanceStatus(statusId) {
			return this.$fhcApi.post('api/frontend/v1/stv/status/advanceStatus/' +
				this.statusId.prestudent_id + '/' +
				this.statusId.status_kurzbz + '/' +
				this.statusId.studiensemester_kurzbz + '/' +
				this.statusId.ausbildungssemester)
				.then(
					result => {
						this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successAdvance'));
					})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		loadStatus(status_id) {
			this.statusNew = false;
			return this.$fhcApi.post('api/frontend/v1/stv/status/loadStatus/',
				status_id)
					.then(result => {
							this.statusData = result.data;
						return result;
					})
				.catch(this.$fhcAlert.handleSystemError);
		},
		reload() {
			if (this.$refs.table)
				this.$refs.table.reloadTable();
		}
	},
	created() {
		this.$fhcApi
			.get('api/frontend/v1/stv/status/getLastBismeldestichtag/')
			.then(result => {
				this.dataMeldestichtag = result.data[0].meldestichtag;
				if (this.$refs.table && this.$refs.table.tableBuilt)
					this.$refs.table.tabulator.redraw(true);
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
		<div class="stv-list h-100 pt-3">
			
			<status-modal ref="test" :meldestichtag="new Date(dataMeldestichtag)" @saved="reload"></status-modal>
				
			<core-filter-cmpt
				v-if="!this.modelValue.length"
				ref="table"
				:tabulator-options="tabulatorOptions"
				:tabulator-events="tabulatorEvents"
				table-only
				:side-menu="false"
				reload
				new-btn-show
				new-btn-label="Status"
				@click:new="actionNewStatus"
				>
				
				<template #actions="{updateData2}">
					<!-- SingleSelectButton-->
					<status-dropdown 
						ref="statusDropdown"
						:showToolbar="showToolbar"
						:showToolbarStudent="showToolbarStudent"
						:showToolbarInteressent="showToolbarInteressent"
						:prestudentIds="prestudentIds"
						:updateData="updateData"
						@reload-table="reload"
					>		
					</status-dropdown>		
				</template>	

			</core-filter-cmpt>

			<div 
				v-if="this.modelValue.length"
				ref="buttonsStatusMulti"
			>	
					<!--MultiSelectButton-->
					<status-dropdown 
						ref="statusDropdown"
						:showToolbar="showToolbar"
						:showToolbarStudent="showToolbarStudent"
						:showToolbarInteressent="showToolbarInteressent"
						:prestudentIds="prestudentIds"
						:updateData="updateData"
						@reload-table="reload"
					>		
					</status-dropdown>	
				
	 	</div>	
	</div>`
};