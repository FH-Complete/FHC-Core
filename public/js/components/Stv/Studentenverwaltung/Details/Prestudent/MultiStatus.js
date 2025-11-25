import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import BsConfirm from "../../../../Bootstrap/Confirm.js";
import FormInput from '../../../../Form/Input.js';
import StatusModal from '../Status/Modal.js';
import StatusDropdown from '../Status/Dropdown.js';

import ApiStvPrestudent from '../../../../../api/factory/stv/prestudent.js';

export default{
	components: {
		CoreFilterCmpt,
		BsModal,
		FormInput,
		StatusModal,
		StatusDropdown
	},
	inject: {
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
		showToolbarStudent() {
			if (Array.isArray(this.modelValue)) {
				if (!this.modelValue.length)
					return false;
				return this.modelValue.every(item => item.uid);
			}
			return !!this.modelValue.uid;
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
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(ApiStvPrestudent.getHistoryPrestudent(this.modelValue.prestudent_id)),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Kurzbz", field: "status_kurzbz", tooltip: true},
					{title: "StSem", field: "studiensemester_kurzbz"},
					{title: "Sem", field: "ausbildungssemester"},
					{title: "Lehrverband", field: "lehrverband", width: 72},
					{
						title: "Datum",
						field: "datum",
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
					{title: "Studienplan", field: "bezeichnung"},
					{
						title: "BestätigtAm",
						field: "bestaetigtam",
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
					{
						title: "AbgeschicktAm",
						field: "bewerbung_abgeschicktamum",
						visible: false,
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour: "2-digit",
								minute: "2-digit",
								second: "2-digit",
								hour12: false
							});
						}
					},
					{title: "Statusgrund", field: "statusgrund_bezeichnung"},
					{title: "Organisationsform", field: "orgform_kurzbz", visible: false},
					{title: "PrestudentInId", field: "prestudent_id", visible: false},
					{title: "StudienplanId", field: "studienplan_id", visible: false},
					{title: "Anmerkung", field: "anmerkung", visible: false},
					{title: "BestätigtVon", field: "bestaetigtvon", visible: false},
					{
						title: "InsertAmUm",
						field: "insertamum",
						visible: false,
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour: "2-digit",
								minute: "2-digit",
								second: "2-digit",
								hour12: false
							});
						}
					},
					{title: "InsertVon", field: "insertvon", visible: false},
					{
						title: "UpdateAmUm",
						field: "updateamum",
						visible: false,
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour: "2-digit",
								minute: "2-digit",
								second: "2-digit",
								hour12: false
							});
						}
					},
					{title: "UpdateVon", field: "updatevon", visible: false},
/*					{title: "Aufnahmestufe", field: "aufnahmestufe", visible: false},*/
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {

							const container = document.createElement('div');
							container.className = "d-flex gap-2";

							const data = cell.getData();

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-forward"></i>';
							button.title = this.$p.t('ui', 'btn_statusVorruecken');
							button.addEventListener('click', () =>
								this.actionAdvanceStatus(data.status_kurzbz, data.studiensemester_kurzbz, data.ausbildungssemester)
							);
							if (!['Student', 'Diplomand', 'Unterbrecher'].includes(data.status_kurzbz))
								button.disabled = true;
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-check"></i>';
							button.title = this.$p.t('ui', 'btn_confirmStatus');
							button.addEventListener('click', () =>
								this.actionConfirmStatus(data.status_kurzbz, data.studiensemester_kurzbz, data.ausbildungssemester)
							);
							if (data.bestaetigtam || !data.bewerbung_abgeschicktamum)
								button.disabled = true;
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.title = this.$p.t('ui', 'btn_editStatus');
							button.addEventListener('click', () =>
								this.actionEditStatus(data.status_kurzbz, data.studiensemester_kurzbz, data.ausbildungssemester)
							);
							if (this.dataMeldestichtag && this.dataMeldestichtag > data.datum && !this.hasPermissionToSkipStatusCheck)
								button.disabled = true;
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('ui', 'btn_deleteStatus');
							button.addEventListener('click', () =>
								this.actionDeleteStatus(data.status_kurzbz, data.studiensemester_kurzbz, data.ausbildungssemester)
							);
							if (this.dataMeldestichtag && this.dataMeldestichtag > data.datum && !this.hasPermissionToSkipStatusCheck)
								button.disabled = true;
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
						row.getElement().classList.add('text-black','text-opacity-50','fst-italic');
					}
				},
				layout: 'fitDataStretchFrozen',
				layoutColumnsOnNewData: false,
				height: 'auto',
				selectable: false,
				index: 'statusId',
				persistenceID: 'stv-multistatus-2025112401'
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async () => {
						await this.$p.loadCategory(['lehre','global','person','ui']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('lehrverband').component.updateDefinition({
									title: this.$p.t('lehre', 'lehrverband')
								});

						cm.getColumnByField('bestaetigtam').component.updateDefinition({
							title: this.$p.t('lehre', 'bestaetigt_am')
						});

						cm.getColumnByField('bewerbung_abgeschicktamum').component.updateDefinition({
							title: this.$p.t('lehre', 'bewerbung_abgeschickt_am')
						});

						cm.getColumnByField('bezeichnung').component.updateDefinition({
							title: this.$p.t('lehre', 'studienplan')
						});

						cm.getColumnByField('actions').component.updateDefinition({
							title: this.$p.t('global', 'aktionen')
						});

						cm.getColumnByField('datum').component.updateDefinition({
							title: this.$p.t('global', 'datum')
						});

						cm.getColumnByField('anmerkung').component.updateDefinition({
							title: this.$p.t('global', 'anmerkung')
						});

						cm.getColumnByField('bestaetigtvon').component.updateDefinition({
							title: this.$p.t('lehre', 'bestaetigt_von')
						});

						cm.getColumnByField('insertamum').component.updateDefinition({
							title: this.$p.t('lehre', 'insert_am')
						});

						cm.getColumnByField('insertvon').component.updateDefinition({
							title: this.$p.t('lehre', 'insert_von')
						});

						cm.getColumnByField('prestudent_id').component.updateDefinition({
							title: this.$p.t('ui', 'prestudent_id')
						});

						cm.getColumnByField('studienplan_id').component.updateDefinition({
							title: this.$p.t('ui', 'studienplan_id')
						});
					}
				}
			],
			statusData: {},
			statusId: {},
			dataMeldestichtag: null,
			statusNew: true,
			maxSem: 0
		};
	},
	watch: {
		modelValue() {
			if (this.$refs.table) {
				this.$refs.table.reloadTable();
			}
			this.getMaxSem();
		}
	},
	methods: {
		getMaxSem() {
			const studiengang_kzs = this.modelValue.studiengang_kz
				? [this.modelValue.studiengang_kz]
				: this.modelValue.map(prestudent => prestudent.studiengang_kz);
			this.maxSem = 0;
			this.$api
				.call(ApiStvPrestudent.getMaxSem(studiengang_kzs))
				.then(result => this.maxSem = result.data)
				.catch(this.$fhcAlert.handleSystemError);
		},
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
				.then(result => {
					// If confirmed, check if this is the last status
					return result
						? this.$api.call(ApiStvPrestudent.isLastStatus(statusId.prestudent_id))
						: Promise.reject({handled: true});
				})
				.then(result => {
					return result.data
						? new Promise((resolve, reject) => {
							BsConfirm.popup(this.$p.t('lehre', 'last_status_confirm_delete'))
								.then(resolve)
								.catch(() => reject({handled: true}));
						})
						: true;
				})
				.then(result => {
					return result
						? this.$api.call(ApiStvPrestudent.deleteStatus(statusId))
						: Promise.reject({handled: true});
				})
				.then(() => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
					this.reload();
					this.$reloadList();
				})
				.catch(this.$fhcAlert.handleSystemError); // Handle any errors
		},
		actionAdvanceStatus(status, stdsem, ausbildungssemester) {
			const statusId = {
				prestudent_id: this.modelValue.prestudent_id,
				status_kurzbz: status,
				studiensemester_kurzbz: stdsem,
				ausbildungssemester: ausbildungssemester
			};
			return this.$api
				.call(ApiStvPrestudent.advanceStatus(statusId))
				.then(() => this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successAdvance')))
				.then(this.reload)
				.catch(this.$fhcAlert.handleSystemError);
		},
		actionConfirmStatus(status, stdsem, ausbildungssemester) {
			const statusId = {
				prestudent_id: this.modelValue.prestudent_id,
				status_kurzbz: status,
				studiensemester_kurzbz: stdsem,
				ausbildungssemester: ausbildungssemester
			};
			BsConfirm
				.popup(this.$p.t('stv', 'status_confirm_popup'))
				.then(() => this.$api.call(ApiStvPrestudent.confirmStatus(statusId)))
				.then(() => this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successConfirm')))
				.then(this.reload)
				.catch(this.$fhcAlert.handleSystemError);
		},
		reload() {
			if (this.$refs.table)
				this.$refs.table.reloadTable();
		}
	},
	created() {
		this.getMaxSem();
		this.$api
			.call(ApiStvPrestudent.getLastBismeldestichtag())
			.then(result => {
				this.dataMeldestichtag = result.data[0]?.meldestichtag;
				if (this.$refs.table && this.$refs.table.tableBuilt)
					this.$refs.table.tabulator.redraw(true);
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="stv-multistatus h-100 pt-3">			
		<status-modal
			ref="test"
			:meldestichtag="new Date(dataMeldestichtag)"
			:max-sem="maxSem"
			@saved="reload"
			>
		</status-modal>
			
		<core-filter-cmpt
			v-if="!this.modelValue.length"
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			:reload-btn-infotext="this.$p.t('table', 'reload')"
			new-btn-show
			:new-btn-label="this.$p.t('global', 'status')"
			@click:new="actionNewStatus"
			>
			
			<template #actions="{updateData2}">
				<!-- SingleSelectButton-->
				<status-dropdown 
					ref="statusDropdown"
					:show-toolbar-student="showToolbarStudent"
					:show-toolbar-interessent="showToolbarInteressent"
					:prestudent-ids="prestudentIds"
					:max-sem="maxSem"
					@reload-table="reload"
				>		
				</status-dropdown>
			</template>	

		</core-filter-cmpt>

		<div v-else>
			<!--MultiSelectButton-->
			<status-dropdown 
				ref="statusDropdown"
				:show-toolbar-student="showToolbarStudent"
				:show-toolbar-interessent="showToolbarInteressent"
				:prestudent-ids="prestudentIds"
				:max-sem="maxSem"
				@reload-table="reload"
				>		
			</status-dropdown>
	 	</div>

	</div>`
};