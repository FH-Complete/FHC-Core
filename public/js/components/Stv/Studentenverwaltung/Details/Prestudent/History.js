import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import {CoreRESTClient} from "../../../../../RESTClient";

export default{
	components: {
		CoreFilterCmpt
	},
	props: {
		person_id: String
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: CoreRESTClient._generateRouterURI('components/stv/Prestudent/getHistoryPrestudents/' + this.person_id),
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
			},
		}
	},
	template: `
		<div class="stv-list h-100 pt-3">
			<core-filter-cmpt
				ref="table"
				:tabulator-options="tabulatorOptions"
				table-only
				:side-menu="false"
			>
		</core-filter-cmpt>
		</div>`
}