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
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(ApiStvPrestudent.getHistoryPrestudents(this.personId)),
				ajaxResponse: (url, params, response) => response.data,
				//autoColumns: true,
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
				layout: 'fitDataFill',
				layoutColumnsOnNewData:	false,
				height:	'auto',
				selectable:	false,
				persistenceID: 'stv-details-prestudent-history'
			},
			tabulatorEvents: [
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
			]
		}
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