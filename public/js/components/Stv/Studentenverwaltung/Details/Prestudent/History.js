import {CoreFilterCmpt} from "../../../../filter/Filter.js";

import ApiStvPrestudent from '../../../../../api/factory/stv/prestudent.js';

export default{
	components: {
		CoreFilterCmpt
	},
	props: {
		personId: Number,
		prestudentId: Number
	},
	data() {
		return {
			layout: 'fitDataFill',
			layoutColumnsOnNewData:	false,
			height:	'auto',
			selectable:	false
		}
	},
	computed: {
		tabulatorOptions() {
			const options = {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(ApiStvPrestudent.getHistoryPrestudents(this.personId)),
				ajaxResponse: (url, params, response) => response.data,
				//autoColumns: true,
				persistenceID: 'stv-details-prestudent-history',
				columns:[
					{title:"StSem", field:"studiensemester_kurzbz"},
					{title:"Prio", field:"priorisierung"},
					{title:"Stg", field:"kurzbzlang"},
					{title:"Orgform", field:"orgform_kurzbz"},
					{title:"Studienplan", field:"bezeichnung"},
					{title:"UID", field:"student_uid"},
					{title:"Status", field:"status"},
					{title:"Prestudent ID", field:"prestudent_id", visible:false}
				],
				rowFormatter: row => {
					const rowData = row.getData();
					const element = row.getElement();
					if (["Abgewiesener","Abbrecher","Absolvent"].includes(rowData.status_kurzbz)) {
						element.classList.add('disabled');
					}
					if (rowData.prestudent_id == this.prestudentId) {
						element.classList.add('fw-bold');
					}
				},
			};
			return options;
		},
		tabulatorEvents() {
			const events = [
				{
					event: 'tableBuilt',
					handler: async () => {
						await this.$p.loadCategory(['lehre']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('orgform_kurzbz').component.updateDefinition({
							title: this.$p.t('lehre', 'organisationsform')
						});

						cm.getColumnByField('bezeichnung').component.updateDefinition({
							title: this.$p.t('lehre', 'studienplan')
						});

						cm.getColumnByField('prestudent_id').component.updateDefinition({
							title: this.$p.t('ui', 'prestudent_id')
						});
					}
				}
			];
			return events;
		},
	},
	watch: {
		personId() {
			this.$refs.table.reloadTable();
		},
	},
	template: `
	<div class="stv-details-prestudent-history h-100 pt-3">
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			>
		</core-filter-cmpt>
	</div>`
}