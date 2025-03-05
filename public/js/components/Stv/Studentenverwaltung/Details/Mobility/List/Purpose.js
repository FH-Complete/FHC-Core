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
			type: [Number, null],
			required: true
		},
		listPurposes: {
			type: Array,
			required: true
		},
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: (url, config, params) => {
					if (this.bisio_id) {
						//fake params for getting api call with tabulator to run
						const config = {
							method: "get",
						};
						const params = {
							id: this.bisio_id,
						};
						return this.$fhcApi.factory.stv.mobility.getPurposes('dummy', config, params)
					}
					else
					{
						// use local data
						return new Promise((resolve) => {
							const localData = this.localData;
							resolve(localData);
						});
					}
				},
				ajaxParams: () => {
					return {
						id: this.bisio_id || "local"
					};
				},
				ajaxResponse: (url, params, response) => response.data || this.localData,


				columns: [
					{title: "Zweck_code", field: "zweck_code", visible: false},
					{title: "Kurzbz", field: "kurzbz", visible: false},
					{title: "Bezeichnung", field: "bezeichnung"},
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 50,
						maxWidth: 100,
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
				height: 200,
				persistenceID: 'core-mobility-purpose'
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async() => {

						await this.$p.loadCategory(['ui', 'global', 'mobility']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('bezeichnung').component.updateDefinition({
							title: this.$p.t('ui', 'bezeichnung')
						});
						cm.getColumnByField('kurzbz').component.updateDefinition({
							title: this.$p.t('mobility', 'kurzbz')
						});
/*						cm.getColumnByField('actions').component.updateDefinition({
							title: this.$p.t('global', 'aktionen')
						});*/
					}
				}
			],
			clickedRows: [],
			formData: {
				zweck_code: ""
			},
			localData: []
		}
	},
	watch: {
		bisio_id() {
			this.resetFormData();
			if (this.$refs.table) {
				this.$refs.table.reloadTable();
			}
		},
	},
	computed: {
		modalClasses() {
			return this.isCentered ? "modal-dialog-centered" : "";
		},
	},
	methods: {
		actionNewPurpose() {
			this.resetModal();
			this.isCentered = true;
		//	this.$refs.mobilityPurpose.show();
		},
		actionDeletePurpose(zweck_code) {
			if (this.bisio_id)
			{
				this.$emit('deleteMobilityPurpose', {
					bisio_id: this.bisio_id,
					zweck_code: zweck_code
				});
			}
			else
			{
				const data = this.$refs.table.tabulator.getData();
				const rowExists = data.some(item => item.zweck_code === zweck_code);

				if (rowExists) {
					this.$refs.table.tabulator.deleteRow(zweck_code);
				}
			}
		},
		handleSubmitAction() {
			if( this.formData.zweck_code === "" ) {
				return; //TODO form validation
			}
			if (this.bisio_id) {
				this.$emit('setMobilityPurpose', {
					zweck_code: this.formData.zweck_code,
					bisio_id: this.bisio_id
				});
			} else {
				const purpose = this.listPurposes.find(item => item.zweck_code === this.formData.zweck_code);
				const newEntry = {
					id: Number (this.formData.zweck_code), //id necessary due to tabulator deleteRow-Action
					zweck_code: Number (this.formData.zweck_code),
					kurzbz: purpose.kurzbz,
					bezeichnung: purpose.bezeichnung
				};

				const data = this.$refs.table.tabulator.getData();
				const rowExists = data.some(item => item.zweck_code === Number (this.formData.zweck_code));

				if(rowExists){
					this.$fhcAlert.alertError(this.$p.t('ui', 'error_entryExisting'));
				}
				else
				{
					this.localData.push(newEntry);

					this.$emit('setMobilityPurposeToNewMobility', {
						zweck_code: this.formData.zweck_code,
					});
				}
			}
			this.resetFormData();
		},
		resetFormData: function() {
			this.formData = {
				zweck_code: ''
			};
		},
		reload() {
			this.$refs.table.reloadTable();
			this.$emit('reload');
		},
		resetModal(){
			this.formData = {};
			this.formData.zweck_code = null;
		},
		resetLocalData() {
			this.$refs.table.tabulator.clearData();
		}
	},
	template: `
		<div class="core-mobility-purpose h-50 d-flex flex-column w-100 mt-2">
		<br>

		<div class="override_filtercmpt_actions_style">

			<core-filter-cmpt
				ref="table"
				:tabulator-options="tabulatorOptions"
				:tabulator-events="tabulatorEvents"
				table-only
				:side-menu="false"
				reload
				>
				<template #actions>
					<core-form ref="mobilityData">
						<div class="row">
							<form-input
								type="select"
								v-model="formData.zweck_code"
								name="zweck_code"
								:label="$p.t('mobility/zweck')"
								>
								<option value=""> {{$p.t('ui', 'bitteWaehlen')}}</option>
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
					<button class="btn btn-primary" @click="handleSubmitAction">{{$p.t('ui', 'hinzufuegen')}}</button>
				</template>
			</core-filter-cmpt>
	
		</div>

	</div>`
	}