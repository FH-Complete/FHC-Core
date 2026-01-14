import {CoreFilterCmpt} from "../../filter/Filter.js";
import FormForm from '../../Form/Form.js';
import FormInput from '../../Form/Input.js';

import ApiAuthinfo from '../../../api/factory/authinfo.js';
import ApiTimelocks from "../../../api/factory/cis/zeitsperren.js";

export default {
	name: 'ZeitsperrenComponent',
	components: {
		CoreFilterCmpt,
		FormForm,
		FormInput
	},
	data(){
		return {
			uid: null,
			listTypenZeitsperren: [],
			tabulatorOptions: null,
			tabulatorEvents: [],
			zeitsperreData: {}
		};
	},
	methods: {
		actionNewZeitsperre(){
			console.log("actionNewZeitsperre ");
		},
		actionEditZeitsperre(zeitsperre_id){
			console.log("actionEditZeitsperre " + zeitsperre_id);
		},
		actionDeleteZeitsperre(zeitsperre_id){
			console.log("actionDeleteZeitsperre " + zeitsperre_id);
		},
	},
	created() {
		this.$api.call(ApiAuthinfo.getAuthUID()).then(res => {
			this.uid = res.data.uid;
			//TODO(Manu) check if uid via props is better
			this.tabulatorOptions = {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () =>
					this.$api.call(ApiTimelocks.getTimelocksUser(this.uid)),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title:"beschreibung", field:"beschreibung"},
					{title:"Grund", field:"zeitsperretyp_kurzbz"},
					{title:"Von", field:"vondatum",
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
							});
						}
					},
					{title:"Bis", field:"bisdatum",
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
							});
						}
					},
					{title:"vonstunde", field:"vonstunde", visible: false},
					{title:"bisstunde", field:"bisstunde", visible: false},
					{title:"Vertretung", field:"vertretung_uid"},
					{title:"Erreichbarkeit", field:"erreichbarkeit_kurzbz"},
					{title:"zeitsperre_id", field:"zeitsperre_id", visible: false},
					{title:"mitarbeiter_uid", field:"mitarbeiter_uid", visible: false},
					{title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.title = this.$p.t('person', 'bankvb_edit');
							button.addEventListener('click', (event) =>
								this.actionEditZeitsperre(cell.getData().zeitsperre_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('person', 'bankvb_delete');
							button.addEventListener('click', () =>
								this.actionDeleteZeitsperre(cell.getData().zeitsperre_id)
							);
							container.append(button);

							return container;
						},
						frozen: true
					},
				]
			};
		});

		this.$api
			.call(ApiTimelocks.getTypenZeitsperren())
			.then(result => {
				this.listTypenZeitsperren = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

	},

/*	created(){
		this.$api
			.call(ApiAuthinfo.getAuthUID())
			.then(res => {
				this.uid = res.data.uid;
			});


	},*/
	/*

							:label="$p.t('global/name')"

		:new-btn-label="this.$p.t('profil', 'zeitsperren')"
					{title:"bezeichnung", field:"bezeichnung"},
					{title:"updateamum", field:"updateamum"},
					{title:"updatevon", field:"updatevon"},
					{title:"insertamum", field:"insertamum"},
					{title:"insertvon", field:"insertvon"},
					{title:"freigabevon", field:"freigabevon"},
					{title:"freigabeamum", field:"freigabeamum"},
  */

	template: /* html */`
	<div class="zeitsperre">
		<h4>Meine Zeitsperren ({{uid}}) </h4>
		
		{{zeitsperreData}}
		
			<form-form class="row g-3 mt-3" ref="dataZeitsperre">
			
			
				<div class="row mb-3 col-6">										   
					<form-input 
						type="text"
						name="beschreibung"
						:label="$p.t('ui/bezeichnung')"
						v-model="zeitsperreData.beschreibung"
					>
					</form-input>
				</div>
				
				<div class="row mb-3 col-6">	
					<form-input 
						type="select"
						name="zeitsperretyp_kurzbz"
						:label="$p.t('person/grund')"
						v-model="zeitsperreData.zeitsperretyp_kurzbz"
					>
						<option
							v-for="typ in listTypenZeitsperren"
							:key="typ.zeitsperretyp_kurzbz"
							:value="typ.zeitsperretyp_kurzbz"
							>
							 {{typ.beschreibung}}
						</option>
					</form-input>
				</div>
				
				<div class="row mb-3 col-3">							   
					<form-input 
						type="text"
						name="vondatum"
						:label="$p.t('ui/from')"
						v-model="zeitsperreData.vondatum"
						required
					>
					</form-input>
				</div>				
				
				<div class="row mb-3 col-3">							   
					<form-input 
						type="text"
						name="bisdatum"
						:label="$p.t('global/bis')"
						v-model="zeitsperreData.bisdatum"
						required
					>
					</form-input>
				</div>				
				
				<div class="row mb-3 col-3">							   
					<form-input 
						type="text"
						name="vonstunde"
						label="vonstunde"
						v-model="zeitsperreData.vonstunde"
						required
					>
					</form-input>
				</div>				
				
				<div class="row mb-3 col-3">							   
					<form-input 
						type="text"
						name="bisstunde"
						label="bisstunde"
						v-model="zeitsperreData.bisstunde"
						required
					>
					</form-input>
				</div>
				
				<div class="row mb-3 col-6">
					<form-input 
						type="text"
						name="vertretung_uid"
						:label="$p.t('person/vertretung')"
						v-model="zeitsperreData.vertretung_uid"
						>
					</form-input>
				</div>
				<div class="row mb-3 col-6">
					<form-input 
						type="text"
						name="erreichbarkeit"
						:label="$p.t('person/erreichbarkeit')"
						v-model="zeitsperreData.erreichbarkeit_kurzbz"
						>
					</form-input>
				</div>
				
				<div class="row mb-3">
				  <div class="col-2 ms-auto">
					<button
					  type="button"
					  class="btn btn-primary"
					  @click="addNewZeitsperre()">
					  Zeitsperre hinzufügen
					</button>
				  </div>
				</div>
			</form-form>
		

	
			<core-filter-cmpt
			 v-if="tabulatorOptions"
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			:reload-btn-infotext="this.$p.t('table', 'reload')"
			new-btn-show
			new-btn-label="Zeitsperre"
			@click:new="actionNewZeitsperre"
		>
		</core-filter-cmpt>
	</div>
	`
};