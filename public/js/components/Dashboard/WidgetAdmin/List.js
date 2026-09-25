import { CoreFilterCmpt } from "../../filter/Filter.js";
import BsConfirm from "../../Bootstrap/Confirm.js";

import ApiDashboardWidget from "../../../api/factory/dashboard/widget.js";

export default {
	name: 'WidgetsAdminList',
	components: {
		CoreFilterCmpt,
	},
	inject: {
		imageSrc: "imageSrc",
	},
	props: {
		unsavedProgress: Boolean,
	},
	emit: [
		"new",
		"select",
	],
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(ApiDashboardWidget.listAllOriginal()),
				ajaxResponse: (url, params, response) => response?.data,
				columns: [
					{
						field: 'setup.icon',
						title: 'Icon',
						titlePhrase: 'dashboard/widget_icon',
						formatter: (cell, formatterParams) => {
							const img = document.createElement('img');
							img.src = this.imageSrc(cell.getData().setup?.icon);

							if (formatterParams?.height)
								img.style.height = formatterParams.height;

							return img;
						},
						formatterParams: {
							height: '1.5em',
						},
						hozAlign: 'center'
					},
					{
						field: 'setup.name',
						title: 'Name',
						titlePhrase: 'global/name',
					},
					{
						field: 'widget_kurzbz',
						title: 'Kurzbz',
						titlePhrase: 'dashboard/widget_kurzbz',
					},
					{
						field: 'berechtigung_kurzbz',
						title: 'Berechtigung',
						titlePhrase: 'global/permission',
					},
				],
				locale: true,
				selectableRows: 1,
				index: 'widget_id',
				persistenceID: 'widgets-admin-list',
				height: '100%',
				selectableRowsCheck: this.selectableCheck,
			},
			tabulatorEvents: [
				{
					event: 'rowSelected',
					handler: this.selectWidget,
				},
				{
					event: 'dataLoading',
					handler: () => {
						// save current
						const selected = this.$refs.table.tabulator.getSelectedRows();
						if (selected.length)
							this.lastSelected = selected[0].getData().widget_id;
					},
				},
				{
					event: 'dataProcessing',
					handler: () => {
						this.adding = true;
					},
				},
				{
					event: 'dataProcessed',
					handler: () => {
						// reselect current
						this.adding = false;
						if (this.lastSelected !== null) {
							this.$refs.table.tabulator.selectRow(this.lastSelected);
							this.lastSelected = null;
						}
					},
				},
			],
			lastSelected: null,
			adding: false,
		};
	},
	methods: {
		selectRow(row) {
			// deselect
			row.getTable().getSelectedRows().forEach(r => r.deselect());
			// select
			row.select();
		},
		updateOrAddDataAndSelect(data) {
			this.$refs.table.tabulator
				.updateOrAddData([ data ])
				.then(res => this.selectRow(res[0]))
				.catch(this.$fhcAlert.handleSystemError);

		},
		selectableCheck(row) {
			if (this.adding)
				return true;
			if (!this.unsavedProgress)
				return true;

			BsConfirm
				.popup(this.$p.t('dashboard/confirm_unsaved_progress'))
				.then(() => this.selectRow(row))
				.catch(() => {});

			return false;
		},
		newWidget() {
			if (this.unsavedProgress) {
				BsConfirm
					.popup(this.$p.t('dashboard/confirm_unsaved_progress'))
					.then(() => this.$emit('new'))
					.catch(() => {});
			} else {
				this.$emit('new');
			}
		},
		selectWidget(data) {
			if (this.lastSelected === null)
				this.$emit('select', data.getData());

			if (!data.isSelected()) {
				/** NOTE(chris): There is a bug in Tabulator where the selected
				 * object gets duplicated and doesnt't match in comparisons.
				 * This results in two visible selected entries, but only one of
				 * them is really selected. This seems to happen if a newly
				 * added row is selected the second time.
				 */
				this.selectRow(data);
			}
		},
	},
	template: /* html */`
	<div class="widgets-admin-list h-100 d-flex flex-column">
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			:side-menu="false"
			table-only
			reload
			new-btn-show
			:new-btn-label="$p.t('dashboard/widget')"
			@click:new="newWidget"
		>
		</core-filter-cmpt>
	</div>
	`
}
