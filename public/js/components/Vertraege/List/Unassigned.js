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
				ajaxRequestFunc: this.$fhcApi.factory.vertraege.person.getAllContractsNotAssigned,
				ajaxParams: () => {
					return {
						person_id: this.person_id
					};
				},
				//TODO(Manu) auch beträge von NULL anzeigen als 0.00
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Typ", field: "type"},
/*					{
						title: "Betrag",
						field: "betrag",
						formatter: function(cell) {
							// Hole den Wert der Zelle
							let value = cell.getValue();

							// Falls der Wert null oder undefined ist, setze ihn auf "0,00"
							if (value == null) {
								return "0,00";
							}

							// Andernfalls formatiere ihn auf zwei Dezimalstellen
							return parseFloat(value).toFixed(2).replace(".", ",");
						}
					},*/
					{title: "Betrag",
						field: "betrag1",
						formatter: function(cell) {
							let value = cell.getValue();

							if (value == null) {
								return "0.00";
							}

							return parseFloat(value).toFixed(2);
						}},
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
			clickedRows: [],
			sumBetragLehrauftraege: 0
		}
	},
	//TODO(Manu) auch auf fhcapi umbauen?
	watch: {
		person_id() {
			console.log("data changed");
			this.$refs.table.tabulator.setData('api/frontend/v1/vertraege/vertraege/getAllContractsNotAssigned/' + this.person_id);
		},
		clickedRows() {
			console.log("clicked rows changed");
			this.$refs.table.tabulator.setData('api/frontend/v1/vertraege/vertraege/getAllContractsNotAssigned/' + this.person_id);
		},
	},
	methods: {
		toggleRowClick(rowData){
			//id already existing?
			//const index = this.clickedRows.findIndex(row => row.id === id);


			//id alleine reicht nicht: Betreuerart ebenfalls, am besten gleich ganze Row!
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
				this.sumBetragLehrauftraege -= Number(rowData.betrag1);
				this.handleSumUp();
			} else {
				this.clickedRows.push(rowData);
				//hier soll der variable sumBetragLehrauftraege der Wert von this.clickedRows.betrag1 hinzugefügt werden
				this.sumBetragLehrauftraege += Number(rowData.betrag1);
				console.log(rowData.betrag1);
				this.handleSumUp();
			}

		},
		emitSaveEvent() {
			// Emit ein Event und übergebe clickedRows an die Parent-Komponente
			this.$emit('saveClickedRows', this.clickedRows);
		},
		reloadUnassigned() {
			this.$refs.table.reloadTable();
			this.$emit('reloadUnassigned');
			this.clickedRows = [];
		//	console.log("clickedRows", this.clickedRows);
		},
		handleSumUp() {
			//this.localValue += 1; // Increment the local value
			this.$emit("sum-updated", this.sumBetragLehrauftraege); // Emit the updated value to the parent
		},
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
	
<!--	{{clickedRows}} <hr> sum:  {{sumBetragLehrauftraege}}-->
	<!--TODO(Manu) nicht anzeigen wenn keine vertraege vorhanden, "KEINE DATEN vorhanden"-->
	
	
		<p>Die folgenden Lehraufträge sind noch keinem Vertrag zugeordnet. Markieren Sie die Lehraufträge um diese dem Vertrag zuzuordnen:</p>

		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			table-only
			:side-menu="false"
			>
		</core-filter-cmpt>
		
		
		<p v-if="clickedRows.length > 0" >Folgende Lehraufträge werden hinzugefügt:</p>
		  
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