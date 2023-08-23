import {CoreFilterCmpt} from "../filter/Filter.js";

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
				columns:[ //Define Table Columns
					{title:"Name", field:"name", width:150},
					{title:"Age", field:"age", hozAlign:"left", formatter:"progress"},
					{title:"Favourite Color", field:"col"},
					{title:"Date Of Birth", field:"dob", sorter:"date", hozAlign:"center"},
				],
				data: [
					{id:1, name:"Oli Bob", age:"12", col:"red", dob:""},
					{id:2, name:"Mary May", age:"1", col:"blue", dob:"14/05/1982"},
					{id:3, name:"Christine Lobowski", age:"42", col:"green", dob:"22/05/1982"},
					{id:4, name:"Brendon Philips", age:"125", col:"orange", dob:"01/08/1980"},
					{id:5, name:"Margret Marmajuke", age:"16", col:"yellow", dob:"31/01/1999"},
					{id:6, name:"Oli Bob", age:"12", col:"red", dob:""},
					{id:7, name:"Mary May", age:"1", col:"blue", dob:"14/05/1982"},
					{id:8, name:"Christine Lobowski", age:"42", col:"green", dob:"22/05/1982"},
					{id:9, name:"Brendon Philips", age:"125", col:"orange", dob:"01/08/1980"}
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
		}
	},
	mounted() {
	},
	template: `
	<div class="stv-list h-100 pt-3">
		<core-filter-cmpt
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