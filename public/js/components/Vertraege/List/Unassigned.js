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
				ajaxRequestFunc: () => this.$api.call(
					this.endpoint.getAllContractsNotAssigned(this.person_id)
				),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Typ", field: "type", width: 100, headerFilter: "list", headerFilterParams: {valuesLookup:true, listOnEmpty:true, autocomplete:true, sort:"asc"}},
					{
						title: "Betrag",
						field: "betrag1",
						headerFilter: true,
						formatter: function(cell) {
							let value = cell.getValue();
							if (value == null) {
								return "0.00";
							}
							return parseFloat(value).toFixed(2);
						}},
					{title: "Bezeichnung", field: "bezeichnung", width: 150, headerFilter: true},
					{title: "Studiensemester", field: "studiensemester_kurzbz",  width: 160, headerFilter: "list", headerFilterParams: {valuesLookup:true, listOnEmpty:true, autocomplete:true, sort:"asc"}},
					{title: "mitarbeiter_uid", field: "mitarbeiter_uid", visible: false, headerFilter: true},
					{title: "projektarbeit_id", field: "projektarbeit_id", visible: false, headerFilter: true},
					{title: "lehreinheit_id", field: "lehreinheit_id", visible: true, headerFilter: true},
					{title: "betreuerart_kurzbz", field: "betreuerart_kurzbz", visible: false, headerFilter: true},
					{title: "Vertragsstunden", field: "vertragsstunden", visible: false, headerFilter: true},
					{title: "vertrag_id", field: "vertrag_id", visible: false, headerFilter: true}, //just for testing
					{
						title: "VertragsstundenStudiensemester",
						field: "vertragsstunden_studiensemester_kurzbz",
						visible: false,
						headerFilter: true
					},
				],
				layout: 'fitColumns',
				layoutColumnsOnNewData: false,
				height: 250,
				selectableRowsRangeMode: 'click',
				selectableRows: true,
				selectableRowsRollingSelection: false, //only allow multiselect with STRG
				index: "lehreinheit_id",
				persistenceID: 'core-contracts-unassigned-2026050501'
			},
			tabulatorEvents: [
				{
					event: 'rowClick',
					handler: (e, row) => {
						const data = row.getData();

						//this.toggleRowClick(e, data);
						this.toggleSelect(e, data);
					}
				},
				{
					event: 'dataLoaded',
					handler: (data) => {
						this.totalRows = data.length;
					}
				},
				{
					event: 'tableBuilt',
					handler: () => {
						this.$p.loadCategory(['ui', 'global', 'vertrag', 'projektarbeitsbeurteilung', 'lehre']).then(() => {

							const setHeader = (field, text) => {
								const col = this.$refs.table.tabulator.getColumn(field);
								if (!col) return;

								const el = col.getElement();
								if (!el || !el.querySelector) return;

								const titleEl = el.querySelector('.tabulator-col-title');
								if (titleEl) {
									titleEl.textContent = text;
								}
							};

							setHeader('type', this.$p.t('global', 'typ'));
							setHeader('bezeichnung', this.$p.t('ui', 'bezeichnung'));
							setHeader('lehreinheit_id', this.$p.t('lehre', 'lehreinheit_id'));
							setHeader('betrag1', this.$p.t('ui', 'betrag'));
							setHeader('studiensemester_kurzbz', this.$p.t('lehre', 'studiensemester'));
							setHeader('mitarbeiter_uid', this.$p.t('ui', 'mitarbeiter_uid'));
							setHeader('projektarbeit_id', this.$p.t('ui', 'projektarbeit_id'));
							setHeader('betreuerart_kurzbz', this.$p.t('projektarbeitsbeurteilung', 'betreuerart'));
							setHeader('vertragsstunden', this.$p.t('vertrag', 'vertragsstunden'));
							setHeader('vertrag_id', this.$p.t('ui', 'vertrag_id'));
							setHeader('vertragsstunden_studiensemester_kurzbz', this.$p.t('vertrag', 'vertragsstunden_studiensemester'));
						});
					}
				}
			],
			clickedRows: [],
			sumBetragLehrauftraege: 0,
			totalRows: 0
		}
	},
	watch: {
		//TODO(Manu) check
		person_id() {
			this.$refs.table.reloadTable();
			//this.$refs.table.tabulator.setData('api/frontend/v1/vertraege/vertraege/getAllContractsNotAssigned/' + this.person_id);
		}
	},
	methods: {
		toggleSelect(event, rowData) {

			const isCtrlPressed = event.ctrlKey;

			if (!isCtrlPressed) {

				const isSameSingleSelection =
					this.clickedRows.length === 1 &&
					this.clickedRows[0].lehreinheit_id === rowData.lehreinheit_id;

				if (isSameSingleSelection) {
					this.clickedRows = [];
					this.sumBetragLehrauftraege = 0;
				} else {
					this.clickedRows = [rowData];
					this.sumBetragLehrauftraege = Number(rowData.betrag1);
				}
			}

			// Multiselect
			else {

				const exists = this.clickedRows.some(
					row => row.lehreinheit_id === rowData.lehreinheit_id
				);

				if (exists) {
					this.clickedRows = this.clickedRows.filter(
						row => row.lehreinheit_id !== rowData.lehreinheit_id
					);
					this.sumBetragLehrauftraege -= Number(rowData.betrag1);
				} else {
					this.clickedRows.push(rowData);
					this.sumBetragLehrauftraege += Number(rowData.betrag1);
				}
			}

			this.handleSumUp();
		},
		emitSaveEvent() {
			this.$emit('saveClickedRows', this.clickedRows);
		},
		reloadUnassigned() {
			this.clickedRows = [];
			//this.$refs.table.reloadTable();
			this.$emit('reloadUnassigned');
		},
		handleSumUp() {
			this.$emit("sum-updated", this.sumBetragLehrauftraege);
		},
	},
	template: `
	<div class="core-contracts-unassigned h-50 d-flex flex-column w-100">
		<p v-if="totalRows > 0">{{$p.t('vertrag', 'text_explainLehrauftrag')}}</p>
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