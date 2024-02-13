import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import {CoreRESTClient} from "../../../../../RESTClient";

export default{
	components: {
		CoreFilterCmpt
	},
	props: {
		prestudent_id: String
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: CoreRESTClient._generateRouterURI('components/stv/Prestudent/getHistoryPrestudent/' + this.prestudent_id),
				//autoColumns: true,
				columns:[
					{title:"Kurzbz", field:"status_kurzbz"},
					{title:"StSem", field:"studiensemester_kurzbz"},
					{title:"Sem", field:"ausbildungssemester"},
					{title:"Lehrverband", field:"lehrverband"},
					{title:"Datum", field:"format_datum"},
					{title:"Studienplan", field:"bezeichnung"},
					{title:"BestätigtAm", field:"format_bestaetigtam"},
					{title:"AbgeschicktAm", field:"format_bewerbung_abgeschicktamum"},
					{title:"Statusgrund", field:"statusgrund_kurzbz"},
					{title:"Organisationsform", field:"ps.orgform_kurzbz"},
					{title:"PrestudentInId", field:"prestudent_id"},
					{title:"StudienplanId", field:"studienplan_id"},
					{title:"Anmerkung", field:"anmerkung"},
					{title:"BestätigtVon", field:"bestaetigtvon"},
					{title:"InsertAmUm", field:"insertamum"},
					{title:"InsertVon", field:"insertvon"},
					{title:"UpdateAmUm", field:"updateamum"},
					{title:"UpdateVon", field:"updatevon"},
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData:	false,
				height:	'auto',
				selectable:	false,
				rowClickMenu:[
					{
						label:"Bearbeiten",
						action:function(e, row){
							console.log("bearbeiten: " + row.getData().status_kurzbz + ' '  + row.getData().prestudent_id);
						}
					},
					{
						separator:true,
					},
					{
						disabled:true,
						label:"Status bestätigen",
						action:function(e, column){
							column.move("col");
						}
					},
					{
						separator:true,
					},
					{
						disabled:true,
						label:"Neuen Status hinzufügen",
						action:function(e, column){
							column.move("col");
						}
					},
					{
						separator:true,
					},
					{
						disabled:true,
						label:"Entfernen",
						action:function(e, column){
							column.move("col");
						}
					},
					{
						separator:true,
					},
					{
						disabled:true,
						label:"Status vorrücken",
						action:function(e, column){
							column.move("col");
						}
					},
					{
						separator:true,
					},
					{
						label:"<i class='fas fa-check-square'></i> Select Row",
						action:function(e, row){
							row.select();
						}
					},
				],
/*				rowContextMenu:[
					{
						label:"Bearbeiten",
						action:function(e, column){
							console.log("bearbeiten" + column);
							column.hide();
						}
					},
					{
						separator:true,
					},
					{
						disabled:true,
						label:"Status bestätigen",
						action:function(e, column){
							column.move("col");
						}
					},
					{
						separator:true,
					},
					{
						disabled:true,
						label:"Neuen Status hinzufügen",
						action:function(e, column){
							column.move("col");
						}
					},
					{
						separator:true,
					},
					{
						disabled:true,
						label:"Entfernen",
						action:function(e, column){
							column.move("col");
						}
					},
					{
						separator:true,
					},
					{
						disabled:true,
						label:"Status vorrücken",
						action:function(e, column){
							column.move("col");
						}
					},
				]*/
			},
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
				@click:new="actionNewAdress"
			>
		</core-filter-cmpt>
		</div>`
}