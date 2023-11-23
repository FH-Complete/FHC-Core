import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import {CoreRESTClient} from "../../../../../RESTClient";
import PvAutoComplete from "../../../../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";
/*import PvToast from "../../../../../../index.ci.php/public/js/components/primevue/toast/toast.esm.min.js";*/


var editIcon = function(cell, formatterParams){ //plain text value
	return "<i class='fa fa-edit'></i>";
};
var deleteIcon = function(cell, formatterParams){ //plain text value
	return "<i class='fa fa-remove'></i>";
};


export default{
	components: {
		CoreFilterCmpt,
		PvAutoComplete
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
				ajaxURL: CoreRESTClient._generateRouterURI('components/stv/Kontakt/getAdressen/' + this.uid),
				//autoColumns: true,
				columns:[
					{title:"Typ", field:"bezeichnung"},
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
					{title:"Name", field:"name"},
					{title:"Firma", field:"firmenname"}, //TODO(manu) check in DB
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
/*					{title: "Actions",
						columns:[*/
							{formatter:editIcon, width:40, align:"center", cellClick: (e, cell) => {
									this.actionEditAdress(cell.getData().adresse_id);
									console.log(cell.getRow().getIndex(), cell.getData(), this);
								}, width:50, headerSort:false},
							{formatter:deleteIcon, width:40, align:"center", cellClick: (e, cell) => {
									this.actionDeleteAdress(cell.getData().adresse_id);
									console.log(cell.getRow().getIndex(), cell.getData(), this);
								}, width:50, headerSort:false },
/*					],
					},*/
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData:	false,
				height:	'auto',
				selectable:	true,
				index: 'adresse_id',
			},
			tabulatorEvents: [

			],
			addressData: {},
			formData: {
				zustelladresse: true,
				heimatadresse: true,
				rechnungsadresse: false,
				typ: 'h',
				nation: 'A'
			},
			initData: {
				zustelladresse: true,
				heimatadresse: true,
				rechnungsadresse: false,
				typ: 'h',
				nation: 'A'
			},
			nations: [],
			adressentypen: [],
			firmen: [],
			ortschaften: [],
			gemeinden: [],
			filteredFirmen: []
		}
	},
	computed:{

	},
	methods:{
		actionNewAdress(){
			bootstrap.Modal.getOrCreateInstance(this.$refs.newAdressModal).show();
		},
		actionEditAdress(adress_id){
			this.loadAdress(adress_id).then(() => {
				if(this.addressData.adresse_id)
					bootstrap.Modal.getOrCreateInstance(this.$refs.editAdressModal).show();
			});
		},
		actionDeleteAdress(adress_id){
			this.loadAdress(adress_id).then(() => {
				if(this.addressData.adresse_id)
					if(this.addressData.heimatadresse)
						this.$fhcAlert.alertError("Heimatadressen dürfen nicht gelöscht werden, da diese für die BIS-Meldung relevant sind. Um die Adresse dennoch zu löschen, entfernen sie das Häkchen bei Heimatadresse!");
					else
						bootstrap.Modal.getOrCreateInstance(this.$refs.deleteAdressModal).show();
			});
		},
		addNewAddress(formData) {
			CoreRESTClient.post('components/stv/Kontakt/addNewAddress/' + this.uid,
				this.formData
			).then(response => {
				if (!response.data.error) {
					this.$fhcAlert.alertSuccess('Speichern erfolgreich');
					this.hideModal('newAdressModal');
					this.resetModal();
				} else {
					const errorData = response.data.retval;
					Object.entries(errorData).forEach(entry => {
						const [key, value] = entry;
						console.log(key, value);
						this.$fhcAlert.alertError(value);
					});
				}
			}).catch(error => {
				console.log(error);
				this.statusCode = 0;
				this.statusMsg = 'Error in Catch';
				console.log('Speichern nicht erfolgreich ' + this.statusMsg);
				this.$fhcAlert.alertError('Fehler bei Speicherroutine aufgetreten');
			}).finally(() => {
				window.scrollTo(0, 0);
				this.reload();
			});

			//this.formData = [];
		},
		reload(){
			this.$refs.table.reloadTable();
		},
		loadAdress(adress_id){
			return CoreRESTClient.get('components/stv/Kontakt/loadAddress/' + adress_id
			).then(
				result => {
					console.log(this.addressData, result);
					if(result.data.retval)
						this.addressData = result.data.retval;
					else
					{
						this.addressData = {};
						this.$fhcAlert.alertError('Keine Adresse mit Id ' + adress_id + ' gefunden');
					}
					return result;
				}
			);
		},
		updateAddress(adress_id){
			CoreRESTClient.post('components/stv/Kontakt/updateAddress/' + adress_id,
				this.addressData
			).then(response => {
				if (!response.data.error) {
					this.$fhcAlert.alertSuccess('Speichern erfolgreich');
					this.hideModal('editAdressModal');
					this.resetModal();
				} else {
					const errorData = response.data.retval;
					Object.entries(errorData).forEach(entry => {
						const [key, value] = entry;
						console.log(key, value);
						this.$fhcAlert.alertError(value);
					});
				}
			}).catch(error => {
				this.statusMsg = 'Error in Catch';
				console.log('Speichern nicht erfolgreich ' + this.statusMsg);
				this.$fhcAlert.alertError('Fehler bei Speicherroutine aufgetreten');
			}).finally(() => {
				window.scrollTo(0, 0);
				//hideModal();
				this.reload();
			});
		},
		deleteAddress(adress_id){
			CoreRESTClient.post('components/stv/Kontakt/deleteAddress/' + adress_id)
				.then(response => {
					console.log(response);
					if (!response.data.error) {
						this.statusCode = 0;
						this.statusMsg = 'success';
						console.log('Löschen erfolgreich: ' + this.statusMsg);
						this.$fhcAlert.alertSuccess('Löschen erfolgreich');
					} else {
						this.statusCode = 0;
						this.statusMsg = 'Error';
						console.log('Löschen nicht erfolgreich: ' + this.statusMsg);
						this.$fhcAlert.alertError('Keine Adresse mit Id ' + adress_id + ' gefunden');
					}
				}).catch(error => {
				console.log(error);
				this.statusCode = 0;
				this.statusMsg = 'Error in Catch';
				console.log('Löschen nicht erfolgreich ' + this.statusMsg);
				this.$fhcAlert.alertError('Fehler bei Löschroutine aufgetreten');
			}).finally(()=> {
				window.scrollTo(0, 0);
				this.hideModal('deleteAdressModal');
				this.reload();
			});
		},
		getGemeinden(searchString){
			return CoreRESTClient.get('components/stv/Kontakt/getGemeinden/' + searchString
			).then(
				result => {
					if(result.data.retval)
						this.gemeinden = result.data.retval;
					else
					{
						this.gemeinden = {};
						this.$fhcAlert.alertError('Keine Gemeinde mit PLZ ' + plz + ' gefunden');
					}
					return result;
				}
			);
		},
		getOrtschaften(searchString){
			return CoreRESTClient.get('components/stv/Kontakt/getOrtschaften/' + searchString
			).then(
				result => {
					if(result.data.retval)
						this.ortschaften = result.data.retval;
					else
					{
						this.ortschaften = {};
						//this.$fhcAlert.alertError('Keine Ortschaft mit PLZ ' + plz + ' gefunden');
					}
					return result;
				}
			);
		},
		search(event) {
			//console.log(event.query);
			return CoreRESTClient
				.get('components/stv/Kontakt/getFirmen/' + event.query)
				.then(result => {
					this.filteredFirmen = CoreRESTClient.getData(result.data);
					//return firma.name.toLowerCase().startsWith(event.query.toLowerCase());
				});
		},
		reload(){
			this.$refs.table.reloadTable();
		},
		hideModal(modalRef){
			bootstrap.Modal.getOrCreateInstance(this.$refs[modalRef]).hide();
		},
		resetModal(){
			this.formData = {};
			this.formData = this.initData;
			this.addressData = {};
		},
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
			.get('components/stv/Kontakt/getAdressentypen')
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
		 TODO(MANU) use BSModal, Validierungen, utf-8? 
		 -->
				
		<div ref="newAdressModal" class="modal fade" id="newAddressModal" tabindex="-1" aria-labelledby="newAddressModalLabel" aria-hidden="true">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
				<h5 class="modal-title" id="newAddressModalLabel">Neue Adresse anlegen</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			  </div>
			  <div class="modal-body">
				<form  ref="formData">					
					<div class="row mb-3">
						<label for="adressentyp" class="form-label col-sm-4">Typ</label>
						<div class="col-sm-5">
							<select id="adressentyp" class="form-control" v-model="formData.typ">
								<option v-for="typ in adressentypen" :key="typ.adressentyp_kurzbz" :value="typ.adressentyp_kurzbz" >{{typ.bezeichnung}}</option>
							</select>
						</div>
					</div>
					<div class="row mb-3">											   
						<label for="strasse" class="form-label col-sm-4">Strasse</label>
						<div class="col-sm-3">
							<input type="text" :readonly="readonly" class="form-control-sm" id="strasse" v-model="formData['strasse']">
						</div>
					</div>	
						
					<div class="row mb-3">
						<label for="nation" class="form-label col-sm-4">Nation</label>
						<div class="col-sm-5">
							<select id="nation" class="form-control" v-model="formData.nation">
								<option v-for="nation in nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.kurztext}}</option>
							</select>
						</div>
					</div>	
						
					<div class="row mb-3">								
						<label for="plz" class="required form-label col-sm-4" >PLZ</label>
						 <div class="col-sm-3">
							<input type="text" class="form-control-sm" required v-model="formData['plz']" >
						</div>
					</div>	
					
					<div class="row mb-3">
						<label for="gemeinde" class="form-label col-sm-4">Gemeinde</label>
						<div v-if="formData.plz && formData.nation === 'A'" class="col-sm-5">
							<select id="gemeinde" class="form-control" v-model="formData['gemeinde']" @click="getGemeinden(formData.plz)">
								<option value="">-- keine Auswahl --</option>
								<option v-for="gemeinde in gemeinden" :value="gemeinde.name" >{{gemeinde.name}}</option>
							</select>	
						</div>
						<div v-else class="col-sm-3">
							<div class="col-sm-3">
								<input id="ort" type="text" class="form-control-sm" v-model="formData['gemeinde']">
							</div>
						</div>
					</div>
						
					<div class="row mb-3">	
						<label for="ort" class="form-label col-sm-4">Ortschaft</label>  
						<div v-if="formData.plz && formData.nation === 'A' && formData.gemeinde" class="col-sm-5">
							<select id="ort" class="form-control" v-model="formData['ort']" @click="getOrtschaften(formData.plz + '/' + formData.gemeinde)">
								<option value="">-- keine Auswahl --</option>
								<option v-for="ort in ortschaften" :value="ort.ortschaftsname" >{{ort.ortschaftsname}}</option>
							</select>	
						</div>
						<div v-else-if="formData.plz && formData.nation === 'A'" class="col-sm-5">
							<select id="ort" class="form-control" v-model="formData['ort']" @click="getOrtschaften(formData.plz)">
								<option value="">-- keine Auswahl --</option>
								<option v-for="ort in ortschaften" :value="ort.ortschaftsname" >{{ort.ortschaftsname}}</option>
							</select>	
						</div>
						<div v-else class="col-sm-3">
							<div class="col-sm-3">
								<input id="ort" type="text" class="form-control-sm" v-model="formData['ort']">
							</div>
						</div>
					</div>	
						
					<div class="row mb-3">				
						<label for="heimatadresse" class="form-label col-sm-4">Heimatadresse</label>
						<div class="col-sm-3">
							<div class="form-check">
								<input id="heimatadresse" type="checkbox" class="form-check-input" value="1" v-model="formData['heimatadresse']">
							</div>
						</div>
					</div>	
						
					<div class="row mb-3">
						<label for="zustelladresse" class="form-label col-sm-4">Zustelladresse</label>
						<div class="col-sm-3">
							<div class="form-check">	
								<input id="zustelladresse" type="checkbox" class="form-check-input" value="1" v-model="formData['zustelladresse']">
							</div>
						</div>
					</div>	
					
					<div class="row mb-3">
							<label for="co_name" class="form-label col-sm-4">Abweich.Empfänger. (c/o)</label>
						<div class="col-sm-3">
							<input type="text" id="co_name" class="form-control-sm" v-model="formData['co_name']">
						</div>	
					</div>
					
					<div class="row mb-3">
						<label for="rechnungsadresse" class="form-label col-sm-4">Rechnungsadresse</label>
						<div class="col-sm-3">
							<div class="form-check">	
								<input id="rechnungsadresse" type="checkbox" class="form-check-input" v-model="formData['rechnungsadresse']">
							</div>
						</div>
					</div>			
					
					<div class="row mb-3">
						<label for="firma_name" class="form-label col-sm-4">Firma</label>
							<div class="col-sm-3">
									<PvAutoComplete v-model="formData['firma']" optionLabel="name" :suggestions="filteredFirmen" @complete="search" minLength="3"/>
							</div>	
					</div>	
					
					<div class="row mb-3">											   
						<label for="name" class="form-label col-sm-4">Name</label>
						<div class="col-sm-3">
							<input type="text" :readonly="readonly" class="form-control-sm" id="name" v-model="formData['name']">
						</div>
					</div>		
					
					<div class="row mb-3">											   
						<label for="anmerkung" class="form-label col-sm-4">Anmerkung</label>
						<div class="col-sm-3">
							<input type="text" :readonly="readonly" class="form-control-sm" id="anmerkung" v-model="formData['anmerkung']">
						</div>
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
		
		<div ref="editAdressModal" class="modal fade" id="editAdressModal" tabindex="-1" aria-labelledby="editAdressModalLabel" aria-hidden="true">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
				<h5 class="modal-title" id="editAdressModalLabel">Adresse bearbeiten</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			  </div>
			  <div class="modal-body">
				<form ref="addressData"> 
												
					<div class="row mb-3">
						<label for="adressentyp" class="form-label col-sm-4">Typ</label>
						<div class="col-sm-5">
							<select id="adressentyp" class="form-control" v-model="addressData.typ">
								<option v-for="typ in adressentypen" :key="typ.adressentyp_kurzbz" :value="typ.adressentyp_kurzbz" >{{typ.bezeichnung}}</option>
							</select>
						</div>
					</div>
					
					<div class="row mb-3">											   
						<label for="strasse" class="form-label col-sm-4">Strasse</label>
						<div class="col-sm-3">
							<input type="text" :readonly="readonly" class="form-control-sm" id="strasse" v-model="addressData.strasse">
						</div>
					</div>	
						
					<div class="row mb-3">
						<label for="nation" class="form-label col-sm-4">Nation</label>
						<div class="col-sm-5">
							<select id="nation" class="form-control" v-model="addressData.nation">
								<option v-for="nation in nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.kurztext}}</option>
							</select>
						</div>
					</div>	
					
					<div class="row mb-3">								
						<label for="plz" class="required form-label col-sm-4" >PLZ</label>
						 <div class="col-sm-3">
							<input type="text" class="form-control-sm" required v-model="addressData.plz" >
						</div>
					</div>	
						
					<div class="row mb-3">
						<label for="gemeinde" class="form-label col-sm-4">Gemeinde</label>
						<div v-if="addressData.gemeinde" class="col-sm-3" >
							<input id="ort" type="text" class="form-control-sm" v-model="addressData['gemeinde']">
						</div>
						<div v-else-if="addressData.plz && addressData.nation === 'A'" class="col-sm-5">
							<select id="gemeinde" class="form-control" v-model="addressData['gemeinde']" @click="getGemeinden(addressData.plz)">
								<option value="">-- keine Auswahl --</option>
								<option v-for="gemeinde in gemeinden" :value="gemeinde.name" >{{gemeinde.name}}</option>
							</select>	
						</div>
						<div v-else class="col-sm-3">
							<div class="col-sm-3">
								<input id="ort" type="text" class="form-control-sm" v-model="addressData['gemeinde']">
							</div>
						</div>
					</div>

					<div class="row mb-3">	
						<label for="ort" class="form-label col-sm-4">Ortschaft</label>  
						<div v-if="addressData.ort" class="col-sm-3">
							<div class="col-sm-3">
								<input id="ort" type="text" class="form-control-sm" v-model="addressData['ort']">
							</div>
						</div>
						<div v-else-if="addressData.plz && addressData.nation === 'A' && addressData.gemeinde" class="col-sm-5">
							<select id="ort" class="form-control" v-model="addressData['ort']" @click="getOrtschaften(addressData.plz + '/' + addressData.gemeinde)">
								<option value="">-- keine Auswahl --</option>
								<option v-for="ort in ortschaften" :value="ort.ortschaftsname" >{{ort.ortschaftsname}}</option>
							</select>	
						</div>
						<div v-else-if="addressData.plz && addressData.nation === 'A'" class="col-sm-5">
							<select id="ort" class="form-control" v-model="addressData['ort']" @click="getOrtschaften(addressData.plz)">
								<option value="">-- keine Auswahl --</option>
								<option v-for="ort in ortschaften" :value="ort.ortschaftsname" >{{ort.ortschaftsname}}</option>
							</select>	
						</div>
						<div v-else class="col-sm-3">
							<div class="col-sm-3">
								<input id="ort" type="text" class="form-control-sm" v-model="addressData['ort']">
							</div>
						</div>
					</div>
					
					<div class="row mb-3">				
						<label for="heimatadresse" class="form-label col-sm-4">Heimatadresse</label>
						<div class="col-sm-3">
							<div class="form-check">
								<input id="heimatadresse" type="checkbox" class="form-check-input" value="1" v-model="addressData['heimatadresse']">
							</div>
						</div>
					</div>	
						
					<div class="row mb-3">
						<label for="zustelladresse" class="form-label col-sm-4">Zustelladresse</label>
						<div class="col-sm-3">
							<div class="form-check">	
								<input id="zustelladresse" type="checkbox" class="form-check-input" value="1" v-model="addressData['zustelladresse']">
							</div>
						</div>
					</div>	
					
					<div class="row mb-3">
						<label for="co_name" class="form-label col-sm-4">Abweich.Empfänger. (c/o)</label>
						<div class="col-sm-3">
							<input type="text" id="co_name" class="form-control-sm" v-model="addressData['co_name']">
						</div>	
					</div>
					
					<div class="row mb-3">
						<label for="rechnungsadresse" class="form-label col-sm-4">Rechnungsadresse</label>
						<div class="col-sm-3">
							<div class="form-check">	
								<input id="rechnungsadresse" type="checkbox" class="form-check-input" v-model="addressData['rechnungsadresse']">
							</div>
						</div>
					</div>	

					
					<div class="row mb-3">
						<label for="firma_name" class="form-label col-sm-4">Firma</label>
							<div v-if="addressData.firmenname" class="col-sm-3">
							 	<input type="text" :readonly="readonly" class="form-control-sm" id="name" v-model="addressData.firmenname">
							</div>	
							<div v-else class="col-sm-3">
							 	<PvAutoComplete v-model="addressData['firma']" optionLabel="name" :suggestions="filteredFirmen" @complete="search" minLength="3"/>
							</div>	
					</div>	
					
					<div class="row mb-3">					
						<input type="hidden" :readonly="readonly" class="form-control-sm" id="firma_id" v-model="addressData.firma_id">
					</div>

					<div class="row mb-3">											   
						<label for="name" class="form-label col-sm-4">Name</label>
						<div class="col-sm-2">
							<input type="text" :readonly="readonly" class="form-control-sm" id="name" v-model="addressData['name']">
						</div>
					</div>		
					
					<div class="row mb-3">											   
						<label for="anmerkung" class="form-label col-sm-4">Anmerkung</label>
						<div class="col-sm-3">
							<input type="text" :readonly="readonly" class="form-control-sm" id="anmerkung" v-model="addressData['anmerkung']">
						</div>
					</div>	
																										   
				</form>  
							
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
				<button ref="Close" type="button" class="btn btn-primary" @click="updateAddress(addressData.adresse_id)">OK</button>

			  </div>
			</div>
		  </div>
		</div>
		
		<div ref="deleteAdressModal" class="modal fade" id="deleteAdressModal" tabindex="-1" aria-labelledby="deleteAdressModalLabel" aria-hidden="true">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
				<h5 class="modal-title" id="deleteAdressModalLabel">Adresse löschen</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			  </div>
			  <div class="modal-body">	  
			  	<p>Adresse {{addressData.adresse_id}} wirklich löschen?</p>											
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
				<button type="button" class="btn btn-primary" @click="deleteAddress(addressData.adresse_id)">OK</button>
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
				<button v-if="reload" class="btn btn-outline-warning" aria-label="Reload"> 
					<span class="fa-solid fa-rotate-right" aria-hidden="true"></span>  				
				</button>
		</core-filter-cmpt>
		</div>`
};

