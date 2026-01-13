import { CoreFilterCmpt } from "../../../../filter/Filter.js";

import ApiStvGroups from '../../../../../api/factory/stv/group.js';

export default {
	name: 'TabGroupsList',
	components: {
		CoreFilterCmpt
	},
	inject: [
		"currentSemester"
	],
	props: {
		students: Object
	},
	emits: [
		"new"
	],
	data() {
		return {
			phrasenLoaded: false,
			optionsReady: true
		};
	},
	computed: {
		tabulatorOptions() {
			let ajaxRequestFunc, ajaxResponse, initialFilter;
			if (Array.isArray(this.students)) {
				ajaxRequestFunc = () => {
					return this.$api.call(
						this.students.map(student => ApiStvGroups.getGruppen(student.uid))
					);
				};
				ajaxResponse = (url, params, response) => {
					return response.reduce((data, result) => [
						...data,
						...result.value.data
					], []);
				};
				initialFilter = [
					this.students.map(student => {
						return { field: "uid", type: "=", value: student.uid };
					}),
					[
						{ field: "studiensemester_kurzbz", type: "=", value: null },
						{ field: "studiensemester_kurzbz", type: "=", value: this.currentSemester }
					]
				];
			} else {
				ajaxRequestFunc = () => {
					return this.$api.call(
						ApiStvGroups.getGruppen(this.students.uid)
					);
				};
				ajaxResponse = (url, params, response) => {
					return response.data;
				};
				initialFilter = [
					{ field: "uid", type: "=", value: this.students.uid },
					[
						{ field: "studiensemester_kurzbz", type: "=", value: null },
						{ field: "studiensemester_kurzbz", type: "=", value: this.currentSemester }
					]
				];
			}
			return {
				ajaxURL: 'dummy',
				ajaxRequestFunc,
				ajaxResponse,
				initialFilter,
				columns: [
					{ title: this.$p.t('gruppenmanagement/gruppe'), field: "gruppe_kurzbz" },
					{ title: this.$p.t('ui/bezeichnung'), field: "bezeichnung" },
					{ title: this.$p.t('lehre/studiensemester'), field: "studiensemester_kurzbz" },
					{
						title: this.$p.t('gruppenmanagement/automatisch_generiert'),
						field: "generiert",
						formatter: "tickCross",
						hozAlign: "center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-success"></i>',
							crossElement: '<i class="fa fa-xmark text-danger"></i>'
						}
					},
					{ title: this.$p.t('ui/student_uid'), field: "uid" },
					{
						title: this.$p.t('global/actions'),
						field: 'actions',
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
								this.actionDeleteGroup(data)
							);
							if (data.generiert)
								button.disabled = true;
							container.append(button);

							return container;
						},
						frozen: true
					}
				],
				height: 'auto',
				index: 'group_id',
				persistenceID: 'stv-details-group-list'
			};
		}
	},
	watch: {
		tabulatorOptions() {
			// Refresh Tabulator if options have changed
			this.optionsReady = false;
			this.$nextTick(() => this.optionsReady = true);
		}
	},
	methods: {
		actionDeleteGroup(data) {
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? data
					: Promise.reject({handled: true}))
				.then(this.deleteGroup)
				.catch(this.$fhcAlert.handleSystemError);
		},
		deleteGroup(params) {
			return this.$api
				.call(ApiStvGroups.deleteGroup(params))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
					this.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		reload() {
			this.$refs.table.reloadTable();
		}
	},
	mounted() {
		this.$p
			.loadCategory(['global', 'lehre', 'ui', 'gruppenmanagement'])
			.then(() => {
				this.phrasenLoaded = true;
			});
	},
	template: /* html */`
	<div class="stv-details-groups-list">
		<core-filter-cmpt
			v-if="phrasenLoaded && optionsReady"
			ref="table"
			:tabulator-options="tabulatorOptions"
			table-only
			:side-menu="false"
			reload
			:reload-btn-infotext="$p.t('table/reload')"
			new-btn-show
			:new-btn-label="$p.t('lehre/gruppe')"
			@click:new="$emit('new')"
		>
		</core-filter-cmpt>
	</div>`
};