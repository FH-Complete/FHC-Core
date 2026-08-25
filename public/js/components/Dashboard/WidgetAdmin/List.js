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
					},
					{
						field: 'widget_kurzbz',
						title: 'Kurzbz',
					},
					{
						field: 'berechtigung_kurzbz',
						title: 'Berechtigung',
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
		};
	},
	methods: {
		selectableCheck(row) {
			if (row.isSelected())
				return false;
			if (!this.unsavedProgress)
				return true;

			BsConfirm
				.popup('selectablecheck' + row.getData().widget_id)
				.then(() => {
					const currentRows = row.getTable().getSelectedRows();
					if (currentRows.length)
						currentRows[0].deselect();
					row.select();
				})
				.catch(() => {});

			return false;
		},
		newWidget() {
			if (this.unsavedProgress) {
				BsConfirm
					.popup('newcheck')
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
			new-btn-label="Widget"
			@click:new="newWidget"
		>
		</core-filter-cmpt>
	</div>
	`
}
