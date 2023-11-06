import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import {CoreRESTClient} from "../../../../../RESTClient";

export default{
	components: {
		CoreFilterCmpt
	},
	props: {
		uid: String
	},
	emits: [
		'update:selected'
	],
	data() {
		return{
			tabulatorOptions: {
				ajaxURL: CoreRESTClient._generateRouterURI('components/stv/Student/getBankverbindung/' + this.uid),
				columns:[
					{title:"Name", field:"name"},
					{title:"Anschrift", field:"anschrift", visible:false},
					{title:"BIC", field:"bic"},
					{title:"BLZ", field:"blz", visible:false},
					{title:"IBAN", field:"iban"},
					{title:"Kontonummer", field:"kontonr", visible:false},
					{title:"Typ", field:"typ", visible:false,
						formatter: (cell, formatterParams, onRendered) => {
							let output;
							switch(cell.getValue()){
								case "p":
									output = "Privatkonto";
									break;
								case "f":
									output = "Firmenkonto";
									break;
								default:
									output = cell.getValue();
							}
							return output;}
					},
					{title:"Verrechnung", field:"verrechnung", visible:false,
						formatter: (cell, formatterParams, onRendered) => {
							let output = cell.getValue() ? "ja" : "nein";
							return output;}
					},
					{title:"Person_id", field:"person_id", visible:false},
					{title:"Bankverbindung_id", field:"bankverbindung_id", visible:false},
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData:	false,
				height:	'auto',
				selectable:	true,
				index: 'bankverbindung_id',
			},
			tabulatorEvents: [
				/*				{
									event: 'rowSelectionChanged',
									handler: this.rowSelectionChanged
								},
								{
									event: 'dataProcessed',
									handler: this.autoSelectRows
								}*/
			],
			lastSelected: null
		}
	},
	methods:{
		actionNewAdress(){
			console.log("Neuen Kontakt anlegen");
		},
		updateUrl(url, first) {
			this.lastSelected = first ? undefined : this.selected;
			if (url)
				url = CoreRESTClient._generateRouterURI(url);
			if (!this.$refs.table.tableBuilt)
				this.$refs.table.tabulator.on("tableBuilt", () => {
					this.$refs.table.tabulator.setData(url);
				});
			else
				this.$refs.table.tabulator.setData(url);
		}
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
				new-btn-label="Neu"
				@click:new="actionNewContact"
			>
		</core-filter-cmpt>
		</div>`
};

