import {CoreFilterCmpt} from "../../../../../filter/Filter.js";

import BsModal from "../../../../../Bootstrap/Modal.js";
import CoreForm from '../../../../../Form/Form.js';
import FormInput from '../../../../../Form/Input.js';

export default {
	components: {
		CoreFilterCmpt,
		BsModal,
		CoreForm,
		FormInput
	},
	props: {
		bisio_id: {
			type: [Number],
			required: true
		},
		listPurposes: {
			type: Array,
			required: true
		},
		formDataParent: {
			type: Object,
			required: true
		}
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: this.$fhcApi.factory.stv.mobility.getPurposes,
				ajaxParams: () => {
					return {
						id: this.bisio_id
					};
				},
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Zweck_code", field: "zweck_code", visible: false},
					{title: "Kurzbz", field: "kurzbz", visible: false},
					{title: "Bezeichnung", field: "bezeichnung"},
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 150,
						formatter: (cell, formatterParams, onRendered) => {

							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('global', 'loeschen');
								button.addEventListener(
									'click',
									() =>
										this.actionDeletePurpose(cell.getData().zweck_code)
								);
							container.append(button);

							return container;
						},
						frozen: true
					},
				],
				layout: 'fitColumns',
				layoutColumnsOnNewData: false,
				height: '300',
				selectableRangeMode: 'click',
				selectable: true,
				persistenceID: 'core-mobility-purpose'
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async() => {

						await this.$p.loadCategory(['ui', 'global', 'vertrag']);
/*
						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('bezeichnung').component.updateDefinition({
							title: this.$p.t('global', 'status')
						});
						cm.getColumnByField('format_datum').component.updateDefinition({
							title: this.$p.t('global', 'datum')
						});
						cm.getColumnByField('mitarbeiter_uid').component.updateDefinition({
							title: this.$p.t('person', 'uid')
						});
						cm.getColumnByField('vertrag_id').component.updateDefinition({
							title: this.$p.t('ui', 'vertrag_id')
						});
						cm.getColumnByField('vertragsstatus_kurzbz').component.updateDefinition({
							title: this.$p.t('vertrag', 'vertragStatus')
						});
						cm.getColumnByField('actions').component.updateDefinition({
							title: this.$p.t('global', 'aktionen')
						});
						cm.getColumnByField('updatevon').component.updateDefinition({
							title: this.$p.t('global', 'updatevon')
						});
						cm.getColumnByField('format_updateamum').component.updateDefinition({
							title: this.$p.t('global', 'updateamum')
						});
						cm.getColumnByField('insertvon').component.updateDefinition({
							title: this.$p.t('global', 'insertvon')
						});
						cm.getColumnByField('format_insertamum').component.updateDefinition({
							title: this.$p.t('global', 'insertamum')
						}); */
					}
				}
			],
			clickedRows: [],
			formData: {},
			localData: []
		}
	},
	watch: {
/*	bisio_id(newVal) {
		if (!newVal) {
			console.log("activate local Data");
			// Lokale Daten direkt in die Tabelle laden
			this.$refs.table.instance.setData(this.localData);
		} else {
			console.log("data with api" + newVal);
			let params = {
				bisio_id: newVal,
			};
			// Daten aus der API abrufen und in die Tabelle laden
			this.$fhcApi.factory.stv.mobility.getPurposes(params)
				.then(result => {
					this.$refs.table.instance.setData(result.data);
				})
				.catch(this.$fhcAlert.handleSystemError);
		}
	},*/


/*		bisio_id(newVal) {
			if (!newVal) {
				console.log("activate local Data");
				this.tabulatorOptions.ajaxRequestFunc = null;
				this.tabulatorOptions.data = this.localData;
			} else {
				console.log("data with api" + newVal);
				let params = {
					bisio_id : newVal,
				};
				//this.tabulatorOptions.ajaxRequestFunc = this.$fhcApi.factory.stv.mobility.getPurposes, newVal;
				this.$fhcApi.factory.stv.mobility.getPurposes(params)
					.then(result => {
						this.tabulatorOptions = result.data;
					})
					.catch(this.$fhcAlert.handleSystemError);
			}
		},*/
		bisio_id() {
			if (this.$refs.table) {
				this.$refs.table.reloadTable();
			}
		},
			/*		bisio_id() {
						//this.reloadTable();
						//this.$refs.table.tabulator.setData('api/frontend/v1/vertraege/vertraege/getStatiOfContract/' + this.vertrag_id);
					},*/
			formDataParent: {
				handler(newVal, oldVal)
				{
					this.formData = this.formDataParent;
				}
			,
				deep: true
			}
	},
	methods: {
		actionNewPurpose() {
			this.resetModal();
			this.$refs.mobilityPurpose.show();
		},
		actionDeletePurpose(zweck_code) {
			this.$emit('deleteMobilityPurpose', {
				bisio_id: this.bisio_id,
				zweck_code: zweck_code
			});
		},
		handleSubmitAction() {
			if (this.bisio_id) {

				this.$emit('setMobilityPurpose', {
					zweck_code: this.formData.zweck_code,
					bisio_id: this.bisio_id
				});
			} else {
				//not working
			//	this.$refs.table.addRow({id: 123, zweck_code: this.formData.zweck_code});
				//this.formData.zweck_code
				this.localData.push(this.formData.zweck_code); //not working: this.$refs.table.addRow is not a function
				console.log("action without Bisio_id " + this.formData.zweck_code);
				this.$emit('setMobilityPurposeToNewMobility', {
					zweck_code: this.formData.zweck_code,
				});
			}
		this.closeModal();
		},

		closeModal(){
			this.$refs.mobilityPurpose.hide();
			this.$emit('close-modal');
		},
		openModal(){
			this.$refs.mobilityPurpose.show();
			this.$emit('open-modal');
		},
		reload() {
			this.$refs.table.reloadTable();
			this.$emit('reload');
		},
		resetModal(){
			this.formData = {};
			this.formData.vertragsstatus_kurzbz = null;
			this.formData.datum = new Date();
			this.statusNew = true;
		}
	},
	template: `
		<div class="core-mobility-puprpose h-50 d-flex flex-column w-100 mt-2">
		<br>

<!--		<h5>{{$p.t('mobility', 'zweck')}}</h5>-->
		
		 bisio: {{bisio_id}}
	
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			new-btn-show
			:new-btn-label="this.$p.t('mobility', 'zweck')"
			@click:new="actionNewPurpose"
			>
		</core-filter-cmpt>
	
		<div >
			<bs-modal ref="mobilityPurpose">
				<template #title>
					<p class="fw-bold mt-3">neuer Zweck</p>
	
				</template>
	
				<core-form ref="mobilityData">
					<div class="row mb-3">
						<form-input
							type="select"
							:label="$p.t('mobility/zweck')"
							v-model="formData.zweck_code"
							name="zweck"
							>
							<option default>{{$p.t('ui', 'bitteWaehlen')}}</option>
							<option
								v-for="entry in listPurposes"
								:key="entry.zweck_code"
								:value="entry.zweck_code"
								>
								{{entry.bezeichnung}}
							</option>
						</form-input>
					</div>
				</core-form>
	
				<template #footer>
					<button type="button" class="btn btn-primary" @click="handleSubmitAction">{{$p.t('ui', 'speichern')}}</button>
				</template>
	
			</bs-modal>
		</div>

	</div>`
	}