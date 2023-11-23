import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import {CoreRESTClient} from "../../../../../RESTClient";
/*import {BsModal} from "../../../../Bootstrap/Modal.js";*/

var editIcon = function(cell, formatterParams){ //plain text value
	return "<i class='fa fa-edit'></i>";
};
var deleteIcon = function(cell, formatterParams) { //plain text value
	return "<i class='fa fa-remove'></i>";
};

export default{
	components: {
		CoreFilterCmpt,
/*		BsModal*/
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
				ajaxURL: CoreRESTClient._generateRouterURI('components/stv/Kontakt/getBankverbindung/' + this.uid),
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
	/*				{title: "Actions",
						columns:[*/
							{formatter:editIcon, width:40, align:"center", cellClick: (e, cell) => {
									this.actionEditBankverbindung(cell.getData().bankverbindung_id);
									console.log(cell.getRow().getIndex(), cell.getData(), this);
								}, width:50, headerSort:false},
							{formatter:deleteIcon, width:40, align:"center", cellClick: (e, cell) => {
									this.actionDeleteBankverbindung(cell.getData().bankverbindung_id);
									console.log(cell.getRow().getIndex(), cell.getData(), this);
								}, width:50, headerSort:false },
/*						],
					},*/
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData:	false,
				height:	'auto',
				selectable:	true,
				index: 'bankverbindung_id',
			},
			tabulatorEvents: [],
			lastSelected: null,
			bankverbindungData: {
				verrechnung: true,
				typ: 'p'
			},
			initData: {
				verrechnung: true,
				typ: 'p'
			}
		}
	},
	methods:{
		actionNewBankverbindung(){
			bootstrap.Modal.getOrCreateInstance(this.$refs.newBankverbindungModal).show();
		},
		actionEditBankverbindung(bankverbindung_id){
			this.loadBankverbindung(bankverbindung_id).then(() => {
				if(this.bankverbindungData.bankverbindung_id)
					bootstrap.Modal.getOrCreateInstance(this.$refs.editBankverbindungModal).show();
			});
		},
		actionDeleteBankverbindung(bankverbindung_id){
			this.loadBankverbindung(bankverbindung_id).then(() => {
				if(this.bankverbindungData.bankverbindung_id)  //Todo(Manu) not optimal
					bootstrap.Modal.getOrCreateInstance(this.$refs.deleteBankverbindungModal).show();
			});
		},
		addNewBankverbindung(bankverbindungData) {
			CoreRESTClient.post('components/stv/Kontakt/addNewBankverbindung/' + this.uid,
				this.bankverbindungData
			).then(response => {
				if (!response.data.error) {
					this.$fhcAlert.alertSuccess('Speichern erfolgreich');
					this.hideModal('newBankverbindungModal');
					this.resetModal();
				} else {
					//console.log(response.data.retval);
					const errorData = response.data.retval;
					Object.entries(errorData).forEach(entry => {
						const [key, value] = entry;
						console.log(key, value);
						this.$fhcAlert.alertError('Das Feld ' + key + ' ist erforderlich');
					});
					this.statusCode = 0;
					this.statusMsg = response.data;
					//console.log('Speichern nicht erfolgreich: ' + this.statusMsg);
				}
			}).catch(error => {
				console.log(error);
				this.statusCode = 0;
				this.statusMsg = 'Error in Catch';
				this.$fhcAlert.alertError('Fehler bei Speicherroutine aufgetreten');
			}).finally(() => {
				window.scrollTo(0, 0);
				this.reload();
			});
		},
		loadBankverbindung(bankverbindung_id){
			return CoreRESTClient.get('components/stv/Kontakt/loadBankverbindung/' + bankverbindung_id
			).then(
				result => {
					console.log(this.bankverbindungData, result);
					if(!result.data.retval || result.data.retval.length < 1)
					{
						this.bankverbindungData = {};
						this.$fhcAlert.alertError('Keine Bankverbindung mit Id ' + bankverbindung_id + ' gefunden');
					}
					else
					{
						this.bankverbindungData = result.data.retval;
					}
					return result;
				}
			);
		},
		updateBankverbindung(bankverbindung_id){
			CoreRESTClient.post('components/stv/Kontakt/updateBankverbindung/' + bankverbindung_id,
				this.bankverbindungData
			).then(response => {
				console.log(response);
				if (!response.data.error) {
					this.$fhcAlert.alertSuccess('Speichern erfolgreich');
					this.hideModal('editBankverbindungModal');
					this.resetModal();
				} else {
					const errorData = response.data.retval;
					console.log(errorData);
					Object.entries(errorData).forEach(entry => {
						const [key, value] = entry;
						this.$fhcAlert.alertError('Das Feld ' + key + ' ist erforderlich');
					});
				}
			}).catch(error => {
				this.statusMsg = 'Error in Catch';
				console.log('Speichern nicht erfolgreich ' + this.statusMsg);
				this.$fhcAlert.alertError('Fehler bei Speicherroutine aufgetreten');
			}).finally(() => {
				window.scrollTo(0, 0);
				this.reload();
			});
		},
		deleteBankverbindung(bankverbindung_id){
			CoreRESTClient.post('components/stv/Kontakt/deleteBankverbindung/' + bankverbindung_id)
				.then(response => {
					console.log(response);
					if (!response.data.error || response.data === []) {
						this.$fhcAlert.alertSuccess('Löschen erfolgreich');
					} else {
						this.$fhcAlert.alertError('Keine Adresse mit Id ' + bankverbindung_id + ' gefunden');
					}
				}).catch(error => {
				this.$fhcAlert.alertError('Fehler bei Löschroutine aufgetreten');
			}).finally(()=> {
				window.scrollTo(0, 0);
				this.hideModal('deleteBankverbindungModal');
				this.resetModal();
				this.reload();
			});
		},
		hideModal(modalRef){
			bootstrap.Modal.getOrCreateInstance(this.$refs[modalRef]).hide();
		},
		reload(){
			this.$refs.table.reloadTable();
		},
		resetModal(){
			this.bankverbindungData = {};
			this.bankverbindungData = this.initData;
		},
	},
	template: `	
		<div class="stv-list h-100 pt-3">
		
<!--		Todo(manu) BsModal-->
<!--		<BsModal title="Bankverbindung anlegen" ref="newBankverbindungModal">
            <template #body>
                <form class="row g-3" v-if="currentAddress != null" ref="bankverbindungData" >
					<div class="row mb-3">
							<label for="name" class="form-label col-sm-4">Name</label>
							<div class="col-sm-3">
								<input type="text" :readonly="readonly" class="form-control-sm" id="name" v-model="bankverbindungData['name']">
							</div>
						</div>
						<div class="row mb-3">											   
							<label for="anschrift" class="form-label col-sm-4">Anschrift</label>
							<div class="col-sm-3">
								<input type="text" :readonly="readonly" class="form-control-sm" id="anschrift" v-model="bankverbindungData['anschrift']">
							</div>
						</div>	

						<div class="row mb-3">											   
							<label for="iban" class="form-label col-sm-4">IBAN</label>
							<div class="col-sm-3">
								<input type="text" :readonly="readonly" required class="form-control-sm" id="iban" v-model="bankverbindungData['iban']">
							</div>
						</div>	
						<div class="row mb-3">											   
							<label for="bic" class="form-label col-sm-4">BIC</label>
							<div class="col-sm-3">
								<input type="text" :readonly="readonly" class="form-control-sm" id="bic" v-model="bankverbindungData['bic']">
							</div>
						</div>	
						<div class="row mb-3">											   
							<label for="kontonr" class="form-label col-sm-4">Kontonummer</label>
							<div class="col-sm-3">
								<input type="text" :readonly="readonly" class="form-control-sm" id="kontonr" v-model="bankverbindungData['kontonr']">
							</div>
						</div>	
						<div class="row mb-3">											   
							<label for="blz" class="form-label col-sm-4">BLZ</label>
							<div class="col-sm-3">
								<input type="text" :readonly="readonly" class="form-control-sm" id="blz" v-model="bankverbindungData['blz']">
							</div>
						</div>		
						<div class="row mb-3">
							<label for="typ" class="form-label col-sm-4">Typ</label>
							<div class="col-sm-5">
								<select  id="typ" class="form-control" required v-model="bankverbindungData['typ']">
									<option  value="p">Privatkonto</option>
									<option  value="f">Firmenkonto</option>
								</select>
							</div>
						</div>	
						<div class="row mb-3">
							<label for="verrechnung" class="form-label col-sm-4">Verrechnung</label>
							<div class="col-sm-3">
								<div class="form-check">	
									<input id="verrechnung" type="checkbox" class="form-check-input" value="1" v-model="bankverbindungData['verrechnung']">
								</div>
							</div>
						</div>	
				</form>
			</template>
            <template #footer>
            		<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
					<button type="button" class="btn btn-primary" @click="addNewBankverbindung()">OK</button>
            </template>
					
		</BsModal>-->
		

		<!--Modal: new Bankverbindung-->
			<div ref="newBankverbindungModal" class="modal fade" id="newBankverbindungModal" tabindex="-1" aria-labelledby="newBankverbindungModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
				<div class="modal-content">
				  <div class="modal-header">
					<h5 class="modal-title" id="newBankverbindungModalLabel">Bankverbindung anlegen</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				  </div>
				  <div class="modal-body">
					<form ref="bankverbindungData">				
						<div class="row mb-3">
							<label for="name" class="form-label col-sm-4">Name</label>
							<div class="col-sm-3">
								<input type="text" :readonly="readonly" class="form-control-sm" id="name" v-model="bankverbindungData['name']">
							</div>
						</div>
						<div class="row mb-3">											   
							<label for="anschrift" class="form-label col-sm-4">Anschrift</label>
							<div class="col-sm-3">
								<input type="text" :readonly="readonly" class="form-control-sm" id="anschrift" v-model="bankverbindungData['anschrift']">
							</div>
						</div>	

						<div class="row mb-3">											   
							<label for="iban" class="form-label col-sm-4">IBAN</label>
							<div class="col-sm-3">
								<input type="text" :readonly="readonly" required class="form-control-sm" id="iban" v-model="bankverbindungData['iban']">
							</div>
						</div>	
						<div class="row mb-3">											   
							<label for="bic" class="form-label col-sm-4">BIC</label>
							<div class="col-sm-3">
								<input type="text" :readonly="readonly" class="form-control-sm" id="bic" v-model="bankverbindungData['bic']">
							</div>
						</div>	
						<div class="row mb-3">											   
							<label for="kontonr" class="form-label col-sm-4">Kontonummer</label>
							<div class="col-sm-3">
								<input type="text" :readonly="readonly" class="form-control-sm" id="kontonr" v-model="bankverbindungData['kontonr']">
							</div>
						</div>	
						<div class="row mb-3">											   
							<label for="blz" class="form-label col-sm-4">BLZ</label>
							<div class="col-sm-3">
								<input type="text" :readonly="readonly" class="form-control-sm" id="blz" v-model="bankverbindungData['blz']">
							</div>
						</div>		
						<div class="row mb-3">
							<label for="typ" class="form-label col-sm-4">Typ</label>
							<div class="col-sm-5">
								<select  id="typ" class="form-control" required v-model="bankverbindungData['typ']">
									<option  value="p">Privatkonto</option>
									<option  value="f">Firmenkonto</option>
								</select>
							</div>
						</div>	
						<div class="row mb-3">
							<label for="verrechnung" class="form-label col-sm-4">Verrechnung</label>
							<div class="col-sm-3">
								<div class="form-check">	
									<input id="verrechnung" type="checkbox" class="form-check-input" value="1" v-model="bankverbindungData['verrechnung']">
								</div>
							</div>
						</div>	
											   
					</form>  
								
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
					<button type="button" class="btn btn-primary" @click="addNewBankverbindung()">OK</button>
				  </div>
				</div>
			  </div>
			</div>
				
			<!--Modal: Edit Bankverbindung-->
			<div ref="editBankverbindungModal" class="modal fade" id="editBankverbindungModal" tabindex="-1" aria-labelledby="editBankverbindungModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
				<div class="modal-content">
				  <div class="modal-header">
					<h5 class="modal-title" id="editBankverbindungModalLabel">Bankverbindung bearbeiten</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="resetModal"></button>
				  </div>
				  <div class="modal-body">
						<form ref="bankverbindungData">
						
							<div class="row mb-3">
								<label for="name" class="form-label col-sm-4">Name</label>
								<div class="col-sm-3">
									<input type="text" :readonly="readonly" class="form-control-sm" id="name" v-model="bankverbindungData['name']">
								</div>
							</div>
													
							<div class="row mb-3">											   
							<label for="anschrift" class="form-label col-sm-4">Anschrift</label>
							<div class="col-sm-3">
								<input type="text" :readonly="readonly" class="form-control-sm" id="anschrift" v-model="bankverbindungData['anschrift']">
							</div>
						</div>	

						<div class="row mb-3">											   
							<label for="iban" class="form-label col-sm-4">IBAN</label>
							<div class="col-sm-3">
								<input type="text" :readonly="readonly" required class="form-control-sm" id="iban" v-model="bankverbindungData['iban']">
							</div>
						</div>	
						<div class="row mb-3">											   
							<label for="bic" class="form-label col-sm-4">BIC</label>
							<div class="col-sm-3">
								<input type="text" :readonly="readonly" class="form-control-sm" id="bic" v-model="bankverbindungData['bic']">
							</div>
						</div>	
						<div class="row mb-3">											   
							<label for="kontonr" class="form-label col-sm-4">Kontonummer</label>
							<div class="col-sm-3">
								<input type="text" :readonly="readonly" class="form-control-sm" id="kontonr" v-model="bankverbindungData['kontonr']">
							</div>
						</div>	
						<div class="row mb-3">											   
							<label for="blz" class="form-label col-sm-4">BLZ</label>
							<div class="col-sm-3">
								<input type="text" :readonly="readonly" class="form-control-sm" id="blz" v-model="bankverbindungData['blz']">
							</div>
						</div>		
						<div class="row mb-3">
							<label for="typ" class="form-label col-sm-4">Typ</label>
							<div class="col-sm-5">
								<select  id="typ" class="form-control" required v-model="bankverbindungData['typ']">
									<option  value="p">Privatkonto</option>
									<option  value="f">Firmenkonto</option>
								</select>
							</div>
						</div>	
						<div class="row mb-3">
							<label for="verrechnung" class="form-label col-sm-4">Verrechnung</label>
							<div class="col-sm-3">
								<div class="form-check">	
									<input id="verrechnung" type="checkbox" class="form-check-input" value="1" v-model="bankverbindungData['verrechnung']">
								</div>
							</div>
						</div>	
																				   
					</form>  
								
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="resetModal">Abbrechen</button>
					<button ref="Close" type="button" class="btn btn-primary" @click="updateBankverbindung(bankverbindungData.bankverbindung_id)">OK</button>
	
				  </div>
				</div>
			  </div>
			</div>
				
			<!-- Modal: Delete Bankverbindung-->
			<div ref="deleteBankverbindungModal" class="modal fade" id="deleteBankverbindungModal" tabindex="-1" aria-labelledby="deleteBankverbindungModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
				<div class="modal-content">
				  <div class="modal-header">
					<h5 class="modal-title" id="deleteBankverbindungModalLabel">Kontakt löschen</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				  </div>
				  <div class="modal-body">	  
					<p>Bankverbindung wirklich löschen?</p>											
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
					<button type="button" class="btn btn-primary" @click="deleteBankverbindung(bankverbindungData.bankverbindung_id)">OK</button>
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
				@click:new="actionNewBankverbindung"
			>
		</core-filter-cmpt>
		</div>`
};

