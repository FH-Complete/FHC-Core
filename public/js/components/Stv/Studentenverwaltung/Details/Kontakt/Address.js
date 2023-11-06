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
				ajaxURL: CoreRESTClient._generateRouterURI('components/stv/Student/getAdressen/' + this.uid),
				//autoColumns: true,
				columns:[
					{title:"Typ", field:"bezeichnung"}, //TODO(manu) mix ok?
					{title:"Strasse", field:"strasse"},
					{title:"Plz", field:"plz"},
					{title:"Ort", field:"ort"},
					{title:"Gemeinde", field:"gemeinde"},
					{title:"Nation", field:"nation"},
					{title:"Heimatadresse", field:"heimatadresse",
						formatter: (cell, formatterParams, onRendered) => {
							let output = cell.getValue() ? "ja" : "nein";
							return output;}
					},
					{title:"Abweich.Empf", field:"co_name"},
					{title:"Firma", field:"name"}, //TODO(manu) check in DB
					{title:"Firma_id", field:"firma_id", visible:false},
					{title:"Adresse_id", field:"adresse_id", visible:false},
					{title:"Person_id", field:"person_id", visible:false},
					{title:"Name", field:"name", visible:false},
					{title:"letzte Änderung", field:"updateamum", visible:false},
					{title:"Rechnungsadresse", field:"rechnungsadresse", visible:false,
						formatter: (cell, formatterParams, onRendered) => {
							let output = cell.getValue() ? "ja" : "nein";
							return output;}
					},
					{title:"Anmerkung", field:"anmerkung", visible:false},
					{formatter:editIcon, width:40, align:"center", cellClick:function(e, cell){alert("Edit data for adresse_id: " + cell.getRow().getIndex())}},
					{formatter:deleteIcon, width:40, align:"center", cellClick:function(e, cell){alert("Delete data for adresse_id " + cell.getRow().getIndex())}},
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData:	false,
				height:	'auto',
				selectable:	true,
				index: 'adresse_id',
			},
			tabulatorEvents: [

			],
			lastSelected: null,
			modalRefVis: false,
			addressData: [],
			formData: {
				zustelladresse: false,
				heimatadresse: false
			},
			nations: [],
			adressentypen: []
		}
	},
	methods:{
		actionNewAdress(){

			console.log("Neue Adresse anlegen");
			bootstrap.Modal.getOrCreateInstance(this.$refs.newAdressModal).show();
		},
		deleteAdressData(){
			return this.formData = null;
		},
		hideModal(){
			bootstrap.Modal.getOrCreateInstance(this.$refs.newAdressModal).hide();
		},
		addNewAddress(formData) {
			CoreRESTClient.post('components/stv/Student/addNewAddress/' + this.uid,
					this.formData
				).then(response => {
					console.log(response);
				if (!response.data.error) {
					this.statusCode = 0;
					this.statusMsg = 'success';
					console.log('Speichern erfolgreich: ' + this.statusMsg);
					this.$fhcAlert.alertSuccess('Speichern erfolgreich');
				} else {
					this.statusCode = 0;
					this.statusMsg = 'Error';
					console.log('Speichern nicht erfolgreich: ' + this.statusMsg);
					this.$fhcAlert.alertError('Speichern nicht erfolgreich');
				}
			}).catch(error => {
				console.log(error);
				this.statusCode = 0;
				this.statusMsg = 'Error in Catch';
				console.log('Speichern nicht erfolgreich ' + this.statusMsg);
				this.$fhcAlert.alertError('Fehler bei Speicherroutine aufgetreten');
			}).finally(() => {
				window.scrollTo(0, 0);
				hideModal();
			});
		},


		/*		showModalRef(){

					modalRef = true;
				}*/
		/*		updateUrl(url, first) {
					this.lastSelected = first ? undefined : this.selected;
					if (url)
						url = CoreRESTClient._generateRouterURI(url);
					if (!this.$refs.table.tableBuilt)
						this.$refs.table.tabulator.on("tableBuilt", () => {
							this.$refs.table.tabulator.setData(url);
						});
					else
						this.$refs.table.tabulator.setData(url);
				}*/
	},
	created(){
		CoreRESTClient
			.get('components/stv/Student/getNations')
			.then(result => {
				this.nations = result.data;
			})
			.catch(err => {
				console.error(err.response.data || err.message);
			});
		CoreRESTClient
			.get('components/stv/Student/getAdressentypen')
			.then(result => {
				this.adressentypen = result.data;
			})
			.catch(err => {
				console.error(err.response.data || err.message);
			});
	},
	template: `	
			
		<div class="stv-list h-100 pt-3">
					
<!--		<Modal>   
 TODO(MANU) use BSModal, Validierungen
 -->
		
		<div ref="newAdressModal" class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Details Adresse</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			  </div>
			  <div class="modal-body">
					<form  ref="formData">{{formData}}
												
						<div class="col-sm-3">
							<label for="adressentyp" class="form-label required">Typ</label>
							<select id="adressentyp" class="form-control" v-model="formData.typ">
								<option value="">-- keine Auswahl --</option>
								<option v-for="typ in adressentypen" :key="typ.adressentyp_kurzbz" :value="typ.adressentyp_kurzbz" >{{typ.bezeichnung}}</option>
							</select>
						</div>
																	   
						<div class="col-sm-3">
							<label for="strasse" class="form-label">Strasse</label>
							<input type="text" :readonly="readonly" class="form-control-sm" id="strasse" v-model="formData['strasse']" maxlength="256">
						</div>
						
						<div class="col-sm-3">
							<label for="nation" class="form-label">Nation</label>
							<select id="nation" class="form-control" v-model="formData.nation">
								<option value="">-- keine Auswahl --</option>
								<option v-for="nation in nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.kurztext}}</option>
							</select>
						</div>
						
						 <div class="col-sm-3">
							<label for="plz" class="required form-label" >PLZ</label>
							<input type="text" required v-model="formData['plz']" >
						</div>
						
						<div class="col-sm-3">
							<label for="gemeinde" class="form-label">Gemeinde</label>
							<input class="form-control-sm" id="gemeinde"  v-model="formData['gemeinde']"maxlength="256">
						</div>
						
						<div class="col-sm-3">
							<label for="ort" class="required form-label">Ortschaft</label>  
							<input type="text" required v-model="formData['ort']"> 
						</div>
										
						<div class="col-sm-3 align-self-center">
						<label for="heimatadresse" class="form-label">Heimatadresse</label>
							<div class="form-check">
								<input id="heimatadresse" type="checkbox" class="form-check-input" value="1" v-model="formData['heimatadresse']">
							</div>
						</div>
						
						<div class="col-sm-3 align-self-center">
						<label for="zustelladresse" class="form-label">Zustelladresse</label>
							<div class="form-check">	
								<input id="zustelladresse" type="checkbox" class="form-check-input" value="1" v-model="formData['zustelladresse']">
							</div>
						</div>
						

						<div class="col-sm-6">
							<label for="co_name" class="form-label">Abweich.Empfänger. (c/o)</label>
							<input type="text" v-model="formData['co_name']">
						</div>
									   
				</form>  
				
				
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
				<button type="button" class="btn btn-primary" @click="addNewAddress()">OK</button>
			  </div>
			</div>
		  </div>
		</div>
				
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
				<button v-if="reload" class="btn btn-outline-warning" aria-label="Reload" @click="editTable">
					<span class="fa-solid fa-rotate-right" aria-hidden="true"></span>
				</button>
		</core-filter-cmpt>
		</div>`
};

