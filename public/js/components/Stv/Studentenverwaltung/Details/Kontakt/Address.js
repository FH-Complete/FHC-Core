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
			formData: {}
		}
	},
	methods:{
		actionNewAdress(){

			console.log("Neue Adresse anlegen");
			bootstrap.Modal.getOrCreateInstance(this.$refs.newAdressModal).show();
		},
		deleteAdressData(){
			formData: {}
		},
		hideModal(){
			this.modalRefVis = false;
		},
		addNewAddress(formData) {
			CoreRESTClient.post('components/stv/Student/addNewAddress/' + this.uid,
					this.formData
				).then(response => {
					console.log(response);
				if (!response.data.error) {
					this.statusCode = response.data.retval[0];
					this.statusMsg = response.data.retval[0].typ;
					console.log('Speichern erfolgreich: ' + this.statusMsg);
					this.$fhcAlert.alertSuccess('Speichern erfolgreich');
				} else {
					this.statusCode = 0;
					this.statusMsg = 'Error';
					console.log('Speichern nicht erfolgreich: ' + this.statusMsg);
/*					this.$fhcAlert.alertDanger('Speichern nicht erfolgreich');*/
				}
			}).catch(error => {
				console.log(error);
				this.statusCode = 0;
				this.statusMsg = 'Error2';
				console.log('Speichern nicht erfolgreich ' + this.statusMsg);
			}).finally(() => {
				window.scrollTo(0, 0);
			});
			this.deleteAdressData();
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
	template: `	
			
		<div class="stv-list h-100 pt-3">
		
		     <div class="toast-container position-absolute top-0 end-0 pt-4 pe-2">
                <Toast ref="toastRef">
                    <template #body><h4>Adresse gespeichert.</h4></template>
                </Toast>
            </div>
			
<!--		<Modal>    -->
		
		<div ref="newAdressModal" class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Details Adresse</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			  </div>
			  <div class="modal-body">
					<form  ref="formData">
											
						<div>
						<label for="typ" class="required form-label">Typ</label>
							<select id="typ" v-model="formData['typ']">
							  <option value="h">Hauptwohnsitz</option>
							  <option value="n">Nebenwohnsitz</option>
							  <option value="r">Rechnungsadresse</option>
							  <option value="f">Firma</option>
							</select>									
						</div>
																	   
						<div class="col-sm-3">
							<label for="strasse" class="form-label">Strasse</label>
							<input type="text" :readonly="readonly" class="form-control-sm" id="strasse" v-model="formData['strasse']" maxlength="256">
						</div>
						<div class="col-sm-3">
							<label for="nation" class="form-label">Nation</label>
							<input type="nation" v-model="formData['nation']">
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
						
						<div class="col-sm-1">                                             
							<label for="heimatadresse" class="form-label">Heimatadr.</label>
							<div>
								<input class="form-check-input" type="checkbox" id="heimatadresse" v-model="formData['heimatadresse']">
							</div>
						</div>
						<div class="col-sm-1">
							<label for="zustelladresse" class="form-label">Zustelladr.</label>
							<div>
								<input class="form-check-input" type="checkbox" id="zustelladresse" v-model="formData['zustelladresse']">
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

