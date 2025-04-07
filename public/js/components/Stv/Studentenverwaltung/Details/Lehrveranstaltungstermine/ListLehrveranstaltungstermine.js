import {CoreFilterCmpt} from "../../../../filter/Filter.js";

export default {
	name: "TblCourseList",
	components: {
		CoreFilterCmpt
	},
	props: {
		student: Object
	},
	data(){
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: this.$fhcApi.factory.stv.courselist.getCourselist,
				ajaxParams: () => {
					return {
						id: this.student.uid
					};
				},
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Prestudent_id", field: "prestudent_id"},
					]
			},
			tabulatorEvents: [],
		}

	},
	methods: {},
	template: `
		<div class="stv-details-courselist h-100 pb-3">
			<h4>Termine</h4>
			{{student}}
		
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