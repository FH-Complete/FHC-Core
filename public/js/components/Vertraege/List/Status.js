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
		vertrag_id: {
			type: [Number],
			required: true
		},
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: this.$fhcApi.factory.vertraege.person.getStatiOfContract,
				ajaxParams: () => {
					return {
						vertrag_id: this.vertrag_id
					};
				},
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Status", field: "status", width: 125},
					{title: "Datum", field: "datum", width: 150},
					{title: "vertrag_id", field: "vertrag_id", visible: false},
					{title: "User", field: "bezeichnung", width: 150},
					{title: "Vertragsstatus_kurzbz", field: "studiensemester_kurzbz"},
					{title: "insertvon", field: "insertvon", visible: false},
					{title: "insertamum", field: "insertamum", visible: false},
					{title: "updatevon", field: "updatevon", visible: false},
					{title: "updateamum", field: "updateamum", visible: true},
					{title: "betreuerart_kurzbz", field: "betreuerart_kurzbz", visible: false},
					{title: "Vertragsstunden", field: "vertragsstunden", visible: false},
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
	watch: {
		vertrag_id() {
			this.$refs.table.tabulator.setData('api/frontend/v1/vertraege/vertraege/getStatiOfContract/' + this.vertrag_id);
		}
	},
	methods: {	},
	template: `
	<!--TODO(Manu) check filter (akzeptiert, neu, erteilt?), design -->
	<div class="core-vertraege h-50 d-flex flex-column w-100">
<!--		person_id: {{person_id}}-->
		<br>
		vertrag_id: {{vertrag_id}} !
		<h4>Vertragstatus</h4>
	
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			table-only
			:side-menu="false"	
			>
		</core-filter-cmpt>		
	</div>`
}