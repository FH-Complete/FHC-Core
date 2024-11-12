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
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 50,
						formatter: (cell, formatterParams, onRendered) => {

							const container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = 'Lehrauftrag löschen';

							let type = cell.getData().type;
							console.log(type);
							if (type == 'Lehrauftrag')
							{
								button.addEventListener(
									'click',
									() =>
										this.actionDeleteLehrauftrag(cell.getData().vertrag_id, cell.getData().lehreinheit_id, cell.getData().mitarbeiter_uid)
								);
							}

							if (type == 'Betreuung')
							{
								button.addEventListener(
									'click',
									() =>
										this.actionDeleteBetreuung(cell.getData().vertrag_id, cell.getData().projektarbeit_id,  cell.getData().betreuerart_kurzbz)
								);
							}

							container.append(button);

							return container;
						},
						frozen: true
					},
				],
				layout: 'fitColumns',
				layoutColumnsOnNewData: false,
				height: '200',
				selectableRangeMode: 'click',
				selectable: true,
			},
			clickedRows: [],
		}
	},
	watch: {

		person_id() {
			this.$refs.table.tabulator.setData('api/frontend/v1/vertraege/vertraege/getAllContractsAssigned/' + this.person_id + '/' + this.vertrag_id);
		},
		vertrag_id() {
			this.$refs.table.tabulator.setData('api/frontend/v1/vertraege/vertraege/getAllContractsAssigned/' + this.person_id + '/' + this.vertrag_id);
		},
	},
	methods: {
		actionDeleteLehrauftrag(vertrag_id, lehreinheit_id, mitarbeiter_uid) {
			this.$emit('deleteLehrauftrag', {
				lehreinheit_id: lehreinheit_id,
				vertrag_id: vertrag_id,
				mitarbeiter_uid: mitarbeiter_uid
			});
		},
		actionDeleteBetreuung(vertrag_id, projektarbeit_id, betreuerart_kurzbz) {
			this.$emit('deleteBetreuung', {
				person_id: this.person_id,
				vertrag_id: vertrag_id,
				projektarbeit_id: projektarbeit_id,
				betreuerart_kurzbz: betreuerart_kurzbz
			});
		},
		reload() {
			this.$refs.table.reloadTable();
			this.$emit('reload');
		},
	},
	template: `
	<!--TODO(Manu) nicht anzeigen, wenn keine vorhanden ? check css, design -->
	
	<div class="core-vertraege-details h-50 d-flex flex-column w-100">
	<br>		
		<h4>Vertragdetails</h4>
	
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			table-only
			:side-menu="false"
			reload
			>
		</core-filter-cmpt>		
	</div>`
}