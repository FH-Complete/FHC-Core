import {CoreFilterCmpt} from '../filter/Filter.js';
import {CoreNavigationCmpt} from '../navigation/Navigation.js';
import FormInput from "../Form/Input.js";

import CoreBaseLayout from '../../components/layout/BaseLayout.js';
import ApiTempusSync from '../../api/factory/tempus/sync.js';
import TempusSyncModal from './Modal.js';

export default {
	props: {
		config: Object,
		defaultSemester: String
	},
	components: {
		CoreFilterCmpt,
		CoreBaseLayout,
		CoreNavigationCmpt,
		FormInput,
		TempusSyncModal
	},
	data() {
		return {
			studiensemester_kurzbz: this.defaultSemester ?? null
		}
	},
	watch: {
		studiensemester_kurzbz()
		{
			this.reloadTable();
		}
	},
	computed: {
		tabulatorOptions() {
			return {
				index: "kalender_syncstatus_id",
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(ApiTempusSync.getSyncs(this.studiensemester_kurzbz)),
				ajaxResponse: (url, params, response) => {
					return response.data || [];
				},
				layout: 'fitDataStretch',
				placeholder: "Keine Daten verfügbar",
				persistenceID: "2026_07_21_tempus_sync_v1",
				locale: true,
				columns: [
					{
						formatter: 'rowSelection',
						titleFormatter: 'rowSelection',
						headerSort: false,
						width: 40
					},
					{titlePhrase: 'lehre/organisationseinheit', field: 'oebezeichnung'},
					{titlePhrase: 'lehre/studiensemester', field: 'studiensemester_kurzbz'},
					{titlePhrase: 'lehre/bis_title', field: 'datum_bis',
						formatter: (cell) => {
							const value = cell.getValue();
							if (!value) return ''
							const date = luxon.DateTime.fromSQL(value)
							return date.isValid ? date.toFormat('dd.MM.yyyy') : value
						},
						minWidth: 100
					},
					{titlePhrase: 'lehre/studienplan', field: 'studienplanbezeichnung', minWidth: 200},
					{titlePhrase: 'lehre/ausbildungssemester', field: 'ausbildungssemester'},
					{titlePhrase: 'global/status', field: 'sync_status_kurzbz'},
					{titlePhrase: 'lehre/mail_benachrichtigung', field: 'mail', formatter: 'tickCross', hozAlign: 'center', minWidth: 80},
					{titlePhrase:'global/insertamum', field: 'insertamum',
						formatter: (cell) => {
							const value = cell.getValue();
							if (!value) return ''
							const date = luxon.DateTime.fromSQL(value)
							return date.isValid ? date.toFormat('dd.MM.yyyy HH:mm') : value
						},
						visible: false
					},
					{titlePhrase: 'global/insertvon', field: 'insertvon', visible: false},
					{titlePhrase: 'global/updateamum', field: 'updateamum',
						formatter: (cell) => {
							const value = cell.getValue();
							if (!value) return ''
							const date = luxon.DateTime.fromSQL(value)
							return date.isValid ? date.toFormat('dd.MM.yyyy HH:mm') : value
						},
						visible: false
					},
					{titlePhrase: 'global/updatevon', field: 'updatevon', visible: false},
					{
						titlePhrase: 'global/aktionen',
						field: 'actions',
						headerSort: false,
						hozAlign: 'center',
						width: 120,
						formatter: (cell) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";
							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.addEventListener('click', () =>
								this.$refs.syncModal.openEdit(cell.getRow().getData())
							);

							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary';
							button.innerHTML = '<i class="fa fa-trash"></i>';
							button.addEventListener('click', evt => {
								evt.stopPropagation();
								this.$fhcAlert
									.confirmDelete()
									.then(result => result ? cell.getData().kalender_syncstatus_id : Promise.reject({handled:true}))
									.then(kalender_syncstatus_id => this.$api.call(ApiTempusSync.delete(kalender_syncstatus_id)))
									.then(() => {
										this.reloadTable();
									})
									.catch(this.$fhcAlert.handleSystemError);
							});
							container.append(button);

							return container;
						}
					}
				],
			}
		}
	},

	methods: {
		openNewModal()
		{
			this.$refs.syncModal.openNew();
		},
		reloadTable()
		{
			this.$refs.syncTable.reloadTable();
		},
		openStartModal()
		{
			this.$refs.syncModal.openStart();
		}
	},
	template: `
		<core-navigation-cmpt></core-navigation-cmpt>
		<core-base-layout>
			<template #main>

				<core-filter-cmpt
					ref="syncTable"
					:tabulator-options="tabulatorOptions"
					:table-only=true
					:side-menu="false"
					:reload="true"
					:useSelectionSpan="false"
					new-btn-label="Hinzufügen"
					new-btn-show
					@click:new="openNewModal">

					<template #actions>
						<form-input
							type="select"
							v-model="studiensemester_kurzbz"
							name="studiensemester_kurzbz"
						>
							<option :value="null" disabled>-- {{ $p.t('lehre', 'studiensemester') }} --</option>
							<option
								v-for="studiensemester in config.studiensemestern"
								:key="studiensemester.studiensemester_kurzbz"
								:value="studiensemester.studiensemester_kurzbz"
							>
								{{ studiensemester.studiensemester_kurzbz }}
							</option>
						</form-input>
						<button
							type="button"
							class="btn btn-outline-secondary"
							@click="openStartModal"
						>
							{{ $p.t('global', 'jetztStarten') }}
						</button>
					</template>

				</core-filter-cmpt>

				<tempus-sync-modal
					ref="syncModal"
					:config="config"
					:stsem_kurzbz="studiensemester_kurzbz"
					@saved="reloadTable"
					>
				</tempus-sync-modal>
			</template>
		</core-base-layout>

	`
};