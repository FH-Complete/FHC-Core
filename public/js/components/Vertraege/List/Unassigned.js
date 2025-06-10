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
					{title: "Typ", field: "type", width: 100},
					{
						title: "Betrag",
						field: "betrag1",
						formatter: function(cell) {
							let value = cell.getValue();
							if (value == null) {
								return "0.00";
							}
							return parseFloat(value).toFixed(2);
						}},
					{title: "Bezeichnung", field: "bezeichnung", width: 150},
					{title: "Studiensemester", field: "studiensemester_kurzbz",  width: 160},
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
				persistenceID: 'core-contracts-unassigned'
			},
			tabulatorEvents: [
				{
					event: 'rowClick',
					handler: (e, row) => {
						const data = row.getData();
						this.toggleRowClick(data);
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
							let cm = this.$refs.table.tabulator.columnManager;

							cm.getColumnByField('type').component.updateDefinition({
								title: this.$p.t('global', 'typ')
							});
							cm.getColumnByField('bezeichnung').component.updateDefinition({
								title: this.$p.t('ui', 'bezeichnung')
							});
							cm.getColumnByField('lehreinheit_id').component.updateDefinition({
								title: this.$p.t('ui', 'lehreinheit_id')
							});
							cm.getColumnByField('betrag1').component.updateDefinition({
								title: this.$p.t('ui', 'betrag')
							});
							cm.getColumnByField('studiensemester_kurzbz').component.updateDefinition({
								title: this.$p.t('lehre', 'studiensemester')
							});
							cm.getColumnByField('mitarbeiter_uid').component.updateDefinition({
								title: this.$p.t('ui', 'mitarbeiter_uid')
							});
							cm.getColumnByField('projektarbeit_id').component.updateDefinition({
								title: this.$p.t('ui', 'projektarbeit_id')
							});
							cm.getColumnByField('betreuerart_kurzbz').component.updateDefinition({
								title: this.$p.t('projektarbeitsbeurteilung', 'betreuerart')
							});
							cm.getColumnByField('vertragsstunden').component.updateDefinition({
								title: this.$p.t('vertrag', 'vertragsstunden')
							});
							cm.getColumnByField('vertrag_id').component.updateDefinition({
								title: this.$p.t('ui', 'vertrag_id')
							});
							cm.getColumnByField('vertragsstunden_studiensemester_kurzbz').component.updateDefinition({
								title: this.$p.t('vertrag', 'vertragsstunden_studiensemester')
							});
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
		//TODO(Manu) check if still working
		person_id() {
			this.$refs.table.reloadTable();
			//this.$refs.table.tabulator.setData('api/frontend/v1/vertraege/vertraege/getAllContractsNotAssigned/' + this.person_id);
		},
		clickedRows() {
			this.$refs.table.reloadTable();
			//this.$refs.table.tabulator.setData('api/frontend/v1/vertraege/vertraege/getAllContractsNotAssigned/' + this.person_id);
		},
	},
	methods: {
		toggleRowClick(rowData){
			// check row
			const exists = this.clickedRows.some(row => JSON.stringify(row) === JSON.stringify(rowData));

			if (exists) {
				this.clickedRows = this.clickedRows.filter(row => JSON.stringify(row) !== JSON.stringify(rowData));
				this.sumBetragLehrauftraege -= Number(rowData.betrag1);
				this.handleSumUp();
			} else {
				this.clickedRows.push(rowData);
				this.sumBetragLehrauftraege += Number(rowData.betrag1);
				//console.log(rowData.betrag1);
				this.handleSumUp();
			}

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
		
		<p v-if="clickedRows.length > 0" >{{$p.t('vertrag', 'text_addLehrauftrag')}}</p>
		  
		<div v-for="item in clickedRows" :key="item.lehreinheit_id" class="row">
			<div class="col-md-6">
			  <input
				class="form-control"
				type="text"
				:value="item.type + ' | ' + item.studiensemester_kurzbz + ' | ' + item.bezeichnung + ' ( lehreinheit_id: ' + item.lehreinheit_id + ')'"
				aria-label="readonly input example"
				readonly
			  >
			</div>
		</div>
	</div>`
}