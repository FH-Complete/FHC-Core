import {CoreFilterCmpt} from "../../../../filter/Filter.js";

import ApiStvGroups from '../../../../../api/factory/stv/group.js';

export default {
	name: 'TblGroups',
	components: {
		CoreFilterCmpt,
	},
	inject: {
		currentSemester: {
			from: 'currentSemester',
		},
	},
	props: {
		student: Object
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(
					ApiStvGroups.getGruppen(this.student.uid)
				),
				ajaxResponse: (url, params, response) => response.data,
				initialFilter: {
					logic: "and",
					filters: [
						{ field: "uid", operator: "eq", value: this.student.uid },
						{
							logic: "or",
							filters: [
								{ field: "studiensemester_kurzbz", operator: "eq", value: null },
								{ field: "studiensemester_kurzbz", operator: "eq", value: this.currentSemester }
							]
						}
					]
				},
				columns: [
					{title: "Gruppe", field: "gruppe_kurzbz"},
					{title: "Bezeichnung", field: "bezeichnung"},
					{title: "Semester", field: "studiensemester_kurzbz"},
					{
						title: "automatisch generiert",
						field: "generiert",
						formatter: "tickCross",
						hozAlign: "center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-success"></i>',
							crossElement: '<i class="fa fa-xmark text-danger"></i>'
						}
					},
					{title: "UID", field: "uid"},
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {
							const container = document.createElement('div');
							container.className = "d-flex gap-2";

							const data = cell.getData();

							const button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('ui', 'loeschen');
							button.addEventListener('click', () =>
								this.actionDeleteGroup(data.gruppe_kurzbz)
							);
							if (data.generiert)
								button.disabled = true;
							container.append(button);

							return container;
						},
						frozen: true
					},
				],
				layout: 'fitDataFill',
				height: 'auto',
				selectable: true,
				index: 'group_id',
				persistenceID: 'stv-details-gruppe'
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async () => {

						await this.$p.loadCategory(['global', 'person', 'stv', 'ui', 'gruppenmanagement']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('gruppe_kurzbz').component.updateDefinition({
							title: this.$p.t('gruppenmanagement', 'gruppe')
						});

						cm.getColumnByField('bezeichnung').component.updateDefinition({
							title: this.$p.t('ui', 'bezeichnung')
						});

						cm.getColumnByField('generiert').component.updateDefinition({
							title: this.$p.t('gruppenmanagement', 'automatisch_generiert')
						});

						cm.getColumnByField('uid').component.updateDefinition({
							title: this.$p.t('ui', 'student_uid')
						});

						//Interference with Filter if not commented out
						/*
						cm.getColumnByField('studiensemester_kurzbz').component.updateDefinition({
							title: this.$p.t('lehre', 'studiensemester')
						});*/

					}
				}
			],
		}
	},
	methods: {
		actionDeleteGroup(gruppe_kurzbz) {
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? gruppe_kurzbz
					: Promise.reject({handled: true}))
				.then(this.deleteGroup)
				.catch(this.$fhcAlert.handleSystemError);

		},
		deleteGroup(gruppe_kurzbz) {
			const group_id = {
				id: this.student.uid,
				gruppe_kurzbz: gruppe_kurzbz
			};

			return this.$api
				.call(ApiStvGroups.deleteGroup(group_id))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		reload() {
			this.$refs.table.reloadTable();
		},
	},
	watch: {
		currentSemester(newVal) {
			if (newVal) {
				this.$refs.table.tabulator.clearFilter(); // Clear old filters

				this.$refs.table.tabulator.setFilter((data) => {
					return (
						data.uid === this.student.uid &&
						(
							data.studiensemester_kurzbz === newVal ||
							data.studiensemester_kurzbz === null
						)
					);
				});
			}
		},
		student() {
			this.$refs.table.reloadTable();
		}
	},
	template: `
				<div class="stv-details-gruppen h-100 pb-3">
					<h5>{{$p.t('stv', 'tab_groups')}}</h5>

					<core-filter-cmpt
						ref="table"
						:tabulator-options="tabulatorOptions"
						:tabulator-events="tabulatorEvents"
						table-only
						:side-menu="false"
						reload
					>
					</core-filter-cmpt>
				</div>
			`
}