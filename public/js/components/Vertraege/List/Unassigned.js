import {CoreFilterCmpt} from "../../filter/Filter.js";

import BsModal from "../../Bootstrap/Modal.js";
import CoreForm from '../../Form/Form.js';
import FormInput from '../../Form/Input.js';
import {BismeldestichtagHelper} from "../../../apps/Bismeldestichtag/BismeldestichtagHelper";

export default {
	components: {
		CoreFilterCmpt,
		BsModal,
		CoreForm,
		FormInput
	},
	props: {
		endpoint: {
			type: Object,
			required: true
		},
		person_id: {
			type: [Number],
			required: true
		},
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: this.$fhcApi.factory.vertraege.person.getAllContractsNotAssigned,
				ajaxParams: () => {
					return {
						person_id: this.person_id
					};
				},
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Typ", field: "type", width: 125},
					{title: "Betrag", field: "betrag", width: 150},
					{title: "Bezeichnung", field: "bezeichnung", width: 150},
					{title: "Studiensemester", field: "studiensemester_kurzbz"},
					{title: "PruefungId", field: "betrag", visible: false},
					{title: "mitarbeiter_uid", field: "mitarbeiter_uid", visible: false},
					{title: "projektarbeit_id", field: "projektarbeit_id", visible: false},
					{title: "lehreinheit_id", field: "lehreinheit_id", visible: true},
					{title: "betreuerart_kurzbz", field: "betreuerart_kurzbz", visible: false},
					{title: "Vertragsstunden", field: "vertragsstunden", visible: false},
					{title: "vertrag_id", field: "vertrag_id", visible: false}, //just for testing
					{
						title: "VertragsstundenStudiensemester",
						field: "vertragsstunden_studiensemester_kurzbz",
						visible: false
					},
				],
				layout: 'fitColumns',
				layoutColumnsOnNewData: false,
				height: '200',
				selectableRangeMode: 'click',
				selectable: true,
			},
			clickedRows: []
		}
	},
	methods: {
		toggleRowClick(rowData){
			//id already existing?
			//const index = this.clickedRows.findIndex(row => row.id === id);


			//id alleine reicht nicht: Betreuerart ebenfalls
/*			if (this.clickedRows.indexOf(id) == -1) {
				console.log("Die Zahl " + id + " ist NICHT Array enthalten.");
				this.clickedRows.push(id);

			} else {
				console.log("Die Zahl " + id + " ist im Array enthalten.");
				const index = this.clickedRows.indexOf(id);
				this.clickedRows.splice(index, 1);
			}*/

			// check row
			const exists = this.clickedRows.some(row => JSON.stringify(row) === JSON.stringify(rowData));

			if (exists) {
				this.clickedRows = this.clickedRows.filter(row => JSON.stringify(row) !== JSON.stringify(rowData));
			} else {
				this.clickedRows.push(rowData);
			}

		}
	},
	mounted() {
		this.$nextTick(() => {
			this.$refs.table.tabulator.on("rowClick", (e, row) => {
				const data = row.getData();
				this.toggleRowClick(data);
			});
		});
	},
	template: `
	<!--TODO(Manu) check css, design .. mybe list.js-->
	<div class="core-vertraege h-50 d-flex flex-column w-100">
	
	{{clickedRows}}
	
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			table-only
			:side-menu="false"	
			>
		</core-filter-cmpt>		
	</div>`
}