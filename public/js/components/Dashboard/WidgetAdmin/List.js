import { CoreFilterCmpt } from "../../filter/Filter.js";
import BsConfirm from "../../Bootstrap/Confirm.js";

import ApiDashboardWidget from "../../../api/factory/dashboard/widget.js";

export default {
	name: 'WidgetsAdminList',
	components: {
		CoreFilterCmpt,
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
						mutator: value => {
							if (!value)
								return FHC_JS_DATA_STORAGE_OBJECT.app_root + 'skin/images/fh_technikum_wien_illustration_klein.png';
							if (value[0] == '/')
								return FHC_JS_DATA_STORAGE_OBJECT.app_root + value.substr(1);
							return value;
						},
						formatter: 'image',
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
			],
			adding: false,
		};
	},
	methods: {
		selectRow(row) {
			const id = row.getData().widget_id;
			const selected = row.getTable().getSelectedRows();
			// deselect
			selected.forEach(r => {
				if (r.getData().widget_id != id) {
					r.deselect();
				}
			});
			// select
			row.select();
		},
		updateOrAddDataAndSelect(data) {
			this.adding = true;
			this.$refs.table.tabulator
				.updateOrAddData([ data ])
				.then(res => {
					this.selectRow(res[0]);
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.adding = false;
				});

		},
		selectableCheck(row) {
			if (this.adding)
				return true;
			if (row.isSelected())
				return true;
			if (!this.unsavedProgress)
				return true;

			BsConfirm
				.popup(this.$p.t('dashboard/confirm_unsaved_progress'))
				.then(() => {
					this.selectRow(row);
				})
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
			this.$emit('select', data.getData());
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
