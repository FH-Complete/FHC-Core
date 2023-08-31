import {CoreFilterCmpt} from "../filter/Filter.js";
import {CoreRESTClient} from '../../RESTClient.js';

export default {
	components: {
		CoreFilterCmpt
	},
	props: {
		selected: Array
	},
	emits: [
		'update:selected'
	],
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: CoreRESTClient._generateRouterURI("components/Studentenverwaltung/getStudents"),
				
				//autoColumns: true,
				columns:[
					{title:"UID", field:"uid"},
					{title:"TitelPre", field:"titelpre"},
					{title:"Nachname", field:"nachname"},
					{title:"Vorname", field:"vorname"},
					{title:"Wahlname", field:"wahlname", visible:false},
					// TODO(chris): IMPLEMENT!
				],

				height: 'auto',
				selectable: true
			},
			tabulatorEvents: [
				{
					event: 'rowSelectionChanged',
					handler: this.rowSelectionChanged
				}
			]
		}
	},
	methods: {
		actionNewPrestudent() {
			console.log('actionNewPrestudent');
		},
		rowSelectionChanged(data) {
			this.$emit('update:selected', data);
		},
		updateUrl(url) {
			this.$refs.table.tabulator.setData(CoreRESTClient._generateRouterURI(url));
			console.log(CoreRESTClient._generateRouterURI(url));
		}
	},
	mounted() {
	},
	template: `
	<div class="stv-list h-100 pt-3">
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			new-btn-show
			new-btn-label="Prestudent"
			@click:new="actionNewPrestudent"
		>
		</core-filter-cmpt>
	</div>`
};