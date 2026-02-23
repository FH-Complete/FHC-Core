import {CoreFilterCmpt} from "../../../../../filter/Filter.js";

import BsModal from "../../../../../Bootstrap/Modal.js";
import CoreForm from '../../../../../Form/Form.js';
import FormInput from '../../../../../Form/Input.js';

import ApiStvMobility from '../../../../../../api/factory/stv/mobility.js';

export default {
	name: "MobilitySupport",
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
		listSupports: {
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
						return this.$api.call(ApiStvMobility.getSupports(this.bisio_id));
					} else {
						// use local data
						return new Promise((resolve) => {
							const localData = this.localData;
							resolve(localData);
						});
					}
				},
				ajaxResponse: (url, params, response) => response.data || this.localData,

				columns: [
					{title: "Aufenthaltsförderung_code", field: "aufenthaltfoerderung_code", visible: false},
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
									this.actionDeleteSupport(cell.getData().aufenthaltfoerderung_code)
							);
							container.append(button);

							return container;
						},
						frozen: true
					},
				],
				layout: 'fitDataStretchFrozen',
				layoutColumnsOnNewData: false,
				height: 200,
				persistenceID: 'core-mobility-support-2025112401'
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
						/*						cm.getColumnByField('actions').component.updateDefinition({
													title: this.$p.t('global', 'aktionen')
												});*/
					}
				}
			],
			clickedRows: [],
			formData: {
				aufenthaltfoerderung_code: "",
			},
			localData: [],
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
	methods: {
		actionNewSupport() {
			this.resetModal();
			this.$refs.mobilitySupport.show();
		},
		actionDeleteSupport(aufenthaltfoerderung_code) {
			if (this.bisio_id)
			{
				this.$emit('deleteMobilitySupport', {
					bisio_id: this.bisio_id,
					aufenthaltfoerderung_code: aufenthaltfoerderung_code
				});
			}
			else
			{
				const data = this.$refs.table.tabulator.getData();
				const rowExists = data.some(item => item.aufenthaltfoerderung_code === aufenthaltfoerderung_code);

				if (rowExists) {
					this.$refs.table.tabulator.deleteRow(aufenthaltfoerderung_code);
				}
			}
		},
		handleSubmitAction() {
			if( this.formData.aufenthaltfoerderung_code === "" ) {
				return; //TODO form validation
			}
			if (this.bisio_id) {
				this.$emit('setMobilitySupport', {
					aufenthaltfoerderung_code: this.formData.aufenthaltfoerderung_code,
					bisio_id: this.bisio_id
				});
			} else {
				const support = this.listSupports.find(item => item.aufenthaltfoerderung_code === this.formData.aufenthaltfoerderung_code);
				const newEntry = {
					id: Number (this.formData.aufenthaltfoerderung_code), //id necessary due to tabulator deleteRow-Action
					aufenthaltfoerderung_code: this.formData.aufenthaltfoerderung_code,
					bezeichnung: support.bezeichnung
				};

				const data = this.$refs.table.tabulator.getData();
				const rowExists = data.some(item => item.aufenthaltfoerderung_code === Number (this.formData.aufenthaltfoerderung_code));

				if(rowExists){
					this.$fhcAlert.alertError(this.$p.t('ui', 'error_entryExisting'));
				}
				else
				{
					this.localData.push(newEntry);

					// reload tabulator mit tabulator method
					if (this.$refs.table?.tabulator) {
						this.$refs.table.tabulator.replaceData(this.localData);
					}

					this.$emit('setMobilitySupportToNewMobility', {
						aufenthaltfoerderung_code: this.formData.aufenthaltfoerderung_code,
					});
				}

			}
			this.resetFormData();
		},
		resetFormData: function() {
			this.formData = {
				aufenthaltfoerderung_code: ""
			};
		},
		reload() {
			this.$refs.table.reloadTable();
			this.$emit('reload');
		},
		resetModal(){
			this.formData = {};
			this.formData.aufenthaltfoerderung_code = null;
		},
		resetLocalData() {
			this.$refs.table.tabulator.clearData();
		}
	},
	template: `
		<div class="core-mobility-support h-50 d-flex flex-column w-100 mt-2">
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
								:label="$p.t('mobility/aufenthalt')"
								v-model="formData.aufenthaltfoerderung_code"
								name="aufenthaltfoerderung_code"
								>
								<option value=""> {{$p.t('ui', 'bitteWaehlen')}}</option>
								<option
									v-for="entry in listSupports"
									:key="entry.aufenthaltfoerderung_code"
									:value="entry.aufenthaltfoerderung_code"
									>
									{{entry.bezeichnung}}
								</option>
							</form-input>
						</div>
					</core-form>
					<button type="button" class="btn btn-primary" @click="handleSubmitAction">{{$p.t('ui', 'hinzufuegen')}}</button>
				</template>
			</core-filter-cmpt>
		
		</div>
	</div>`
}