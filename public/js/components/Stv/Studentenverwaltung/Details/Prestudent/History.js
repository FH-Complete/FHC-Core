import {CoreFilterCmpt} from "../../../../filter/Filter.js";

export default{
	components: {
		CoreFilterCmpt
	},
	props: {
		person_id: Number
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'api/frontend/v1/stv/Prestudent/getHistoryPrestudents/' + this.person_id,
				ajaxRequestFunc: this.$fhcApi.get,
				ajaxResponse: (url, params, response) => response.data,
				//autoColumns: true,
				columns:[
					{title:"StSem", field:"studiensemester_kurzbz"},
					{title:"Prio", field:"priorisierung"},
					{title:"Stg", field:"kurzbzlang"},
					{title:"Orgform", field:"orgform_kurzbz"},
					{title:"Studienplan", field:"bezeichnung"},
					{title:"UID", field:"student_uid"},
					{title:"Status", field:"status"}
				],
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
					}
				}
			]
		}
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