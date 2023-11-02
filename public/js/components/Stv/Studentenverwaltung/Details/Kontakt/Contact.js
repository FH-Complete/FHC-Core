import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import {CoreRESTClient} from "../../../../../RESTClient";

var editIcon = function(cell, formatterParams){ //plain text value
	return "<i class='fa fa-edit'></i>";
};
var deleteIcon = function(cell, formatterParams){ //plain text value
	return "<i class='fa fa-remove'></i>";
};

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
				ajaxURL: CoreRESTClient._generateRouterURI('components/stv/Student/getKontakte/' + this.uid),
				columns:[
					{title:"Typ", field:"kontakttyp"}, //TODO(manu) mix ok?
					{title:"Kontakt", field:"kontakt"},
					{title:"Zustellung", field:"zustellung",
						formatter: (cell, formatterParams, onRendered) => {
							let output = cell.getValue() ? "ja" : "nein";
							return output;}
					},
					{title:"Anmerkung", field:"anmerkung"},
					//{title:"Firma", field:"adress_id"},
					{title:"Firma_id", field:"firma_id", visible:false},
					{title:"Person_id", field:"person_id", visible:false},
					{title:"Kontakt_id", field:"kontakt_id", visible:false},
					{title:"letzte Änderung", field:"updateamum", visible:false},
					{formatter:editIcon, width:40, align:"center", cellClick:function(e, cell){alert("Edit data for kontakt_id: " + cell.getRow().getIndex())}},
					{formatter:deleteIcon, width:40, align:"center", cellClick:function(e, cell){alert("Delete data for kontakt_id " + cell.getRow().getIndex())}},
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData:	false,
				height:	'auto',
				selectable:	true,
				index: 'kontakt_id',
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

