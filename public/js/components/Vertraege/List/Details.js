import {CoreFilterCmpt} from "../../filter/Filter.js";

import BsModal from "../../Bootstrap/Modal.js";
import CoreForm from '../../Form/Form.js';
import FormInput from '../../Form/Input.js';

export default {
	components: {
		CoreFilterCmpt,
		BsModal,
		CoreForm,
		FormInput
	},
	props: {
		person_id: {
			type: [Number],
			required: true
		},
		vertrag_id: {
			type: [Number],
			required: true
		},
	},
	data() {
		return {
			//tableData: [],
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: this.$fhcApi.factory.vertraege.person.getAllContractsAssigned,
				ajaxParams: () => {
					return {
						person_id: this.person_id,
						vertrag_id: this.vertrag_id
					};
				},
				ajaxResponse: (url, params, response) => response.data,
				//Version with saving Data in tableData Object
/*				ajaxResponse: (url, params, response) => {
					this.tableData = response.data;
					return response.data;
				},*/
				columns: [
					{title: "Typ", field: "type"},
					{title: "Betrag", field: "betrag"},
					{title: "Bezeichnung", field: "bezeichnung"},
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
			clickedRows: [],
/*			table: null,
			tableData: []*/
		}
	},
	watch: {
		person_id: 'updateTableData',
		vertrag_id: 'updateTableData'
/*		person_id() {
			this.$refs.table.tabulator.setData('api/frontend/v1/vertraege/vertraege/getAllContractsAssigned/' + this.person_id + '/' + this.vertrag_id);
		},
		vertrag_id() {
			this.$refs.table.tabulator.setData('api/frontend/v1/vertraege/vertraege/getAllContractsAssigned/' + this.person_id + '/' + this.vertrag_id);
		},*/
	},
	methods: {
		updateTableData() {
			this.$refs.table.tabulator.setData(`api/frontend/v1/vertraege/vertraege/getAllContractsAssigned/ ' ${this.person_id}/${this.vertrag_id}`);
		}
	},
	template: `
	<!--TODO(Manu) check css, design -->
	<div class="core-vertraege h-50 d-flex flex-column w-100">
		person_id: {{person_id}}
		<br>
		vertrag_id: {{vertrag_id}}
		
		  <div>
			<!-- Tabulator-Container -->
			<div ref="table"></div>
		
			<!-- Ausgabe der Daten -->
			<div v-for="row in tableData" :key="row.id">
			  <p>{{ row.name }} - {{ row.value }}</p>
			</div>
		  </div>
		
		<h4>Vertragdetails</h4>
	
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			table-only
			:side-menu="false"	
			>
		</core-filter-cmpt>		
	</div>`
}