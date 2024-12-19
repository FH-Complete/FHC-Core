import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';

export default {
	components: {
		CoreFilterCmpt,
		BsModal,
		FormForm,
		FormInput
	},
	inject: {
		$reloadList: {
			from: '$reloadList',
			required: true
		}
	},
	props: {
		student: Object
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: this.$fhcApi.factory.stv.mobility.getMobilitaeten,
				ajaxParams: () => {
					return {
						id: this.student.student_uid
					};
				},
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Kurzbz", field: "kurzbz"},
					{title: "Nation", field: "nation_code"},
					{title: "Von", field: "von"},
					{title: "Bis", field: "bis"},
					{title: "bisio_id", field: "bisio_id"},
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.title = this.$p.t('ui', 'bearbeiten');
							button.addEventListener('click', (event) =>
								this.actionEditmobility(cell.getData().mobility_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('ui', 'loeschen');
							button.addEventListener('click', () =>
								this.actionDeletemobility(cell.getData().mobility_id)
							);
							container.append(button);

							return container;
						},
						frozen: true
					},
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData: false,
				height: 'auto',
				minHeight: '200',
				selectable: true,
				index: 'mobility_id',
				persistenceID: 'stv-details-table_mobiliy'
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async() => {
						await this.$p.loadCategory(['global', 'person', 'stv', 'mobility', 'ui']);


						let cm = this.$refs.table.tabulator.columnManager;

						// cm.getColumnByField('vorsitz_nachname').component.updateDefinition({
						// 	title: this.$p.t('mobility', 'vorsitz_header')
						// });
						// cm.getColumnByField('beurteilung_bezeichnung').component.updateDefinition({
						// 	title: this.$p.t('mobility', 'abschlussbeurteilung')
						// });
						// cm.getColumnByField('p1_nachname').component.updateDefinition({
						// 	title: this.$p.t('mobility', 'pruefer1')
						// });
						// cm.getColumnByField('p2_nachname').component.updateDefinition({
						// 	title: this.$p.t('mobility', 'pruefer2')
						// });
						// cm.getColumnByField('p3_nachname').component.updateDefinition({
						// 	title: this.$p.t('mobility', 'pruefer3')
						// });
						// cm.getColumnByField('format_datum').component.updateDefinition({
						// 	title: this.$p.t('global', 'datum')
						// });
						// cm.getColumnByField('uhrzeit').component.updateDefinition({
						// 	title: this.$p.t('global', 'uhrzeit')
						// });
						// cm.getColumnByField('format_freigabedatum').component.updateDefinition({
						// 	title: this.$p.t('mobility', 'freigabe')
						// });
						// cm.getColumnByField('antritt_bezeichnung').component.updateDefinition({
						// 	title: this.$p.t('mobility', 'pruefungsantritt')
						// });
						// cm.getColumnByField('format_sponsion').component.updateDefinition({
						// 	title: this.$p.t('mobility', 'sponsion')
						// });
						// cm.getColumnByField('anmerkung').component.updateDefinition({
						// 	title: this.$p.t('global', 'anmerkung')
						// });
						// cm.getColumnByField('pruefungstyp_kurzbz').component.updateDefinition({
						// 	title: this.$p.t('global', 'typ')
						// });
						// cm.getColumnByField('mobility_id').component.updateDefinition({
						// 	title: this.$p.t('ui', 'mobility_id')
						// });
						/*
						cm.getColumnByField('actions').component.updateDefinition({
						title: this.$p.t('global', 'aktionen')
												});
						*/
					}
				}
			],
			formData: {
			},
			statusNew: true,
		}
	},
	watch: {
		student(){
			if (this.$refs.table) {
				this.$refs.table.reloadTable();
			}
		}
	},
	methods: {
		getStudiengangsTyp(){
			this.stgTyp = '';
			this.$fhcApi.factory.stv.mobility.getTypStudiengang(this.stg_kz)
					.then(result => this.stgTyp = result.data)
					.catch(this.$fhcAlert.handleSystemError);
		},
		actionNewmobility() {
			this.resetForm();
			this.statusNew = true;
			this.setDefaultFormData();
		},
		actionEditmobility(mobility_id) {
			this.resetForm();
			this.statusNew = false;
			this.loadmobility(mobility_id);
		},
		actionDeletemobility(mobility_id) {
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? mobility_id
					: Promise.reject({handled: true}))
				.then(this.deletemobility)
				.catch(this.$fhcAlert.handleSystemError);
		},
		addNewmobility() {
			const dataToSend = {
				uid: this.student.uid,
				formData: this.formData
			};

			return this.$refs.formFinalExam.factory.stv.mobility.addNewmobility(dataToSend)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.resetForm();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		loadmobility(mobility_id) {
			return this.$fhcApi.factory.stv.mobility.loadmobility(mobility_id)
				.then(result => {
					this.formData = result.data;
					//TODO(Manu) check if cisRoot is okay
					this.formData.link = this.cisRoot + 'index.ci.php/lehre/Pruefungsprotokoll/showProtokoll?mobility_id=' + this.formData.mobility_id + '&fhc_controller_id=67481e5ed5490';
					return result;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updatemobility(mobility_id) {
			const dataToSend = {
				id: mobility_id,
				formData: this.formData
			};
			return this.$refs.formFinalExam.factory.stv.mobility.updatemobility(dataToSend)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.resetForm();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		deletemobility(mobility_id) {
			return this.$fhcApi.factory.stv.mobility.deletemobility(mobility_id)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		resetForm() {
			this.formData = null;
		},
	},
	created() {
		// this.$fhcApi.factory.stv.mobility.getTypenmobility()
		// 	.then(result => {
		// 		this.arrTypen = result.data;
		// 	})
		// 	.catch(this.$fhcAlert.handleSystemError);
		// this.$fhcApi.factory.stv.mobility.getTypenAntritte()
		// 	.then(result => {
		// 		this.arrAntritte = result.data;
		// 	})
		// 	.catch(this.$fhcAlert.handleSystemError);
		// this.$fhcApi.factory.stv.mobility.getBeurteilungen()
		// 	.then(result => {
		// 		this.arrBeurteilungen = result.data;
		// 	})
		// 	.catch(this.$fhcAlert.handleSystemError);
		// this.$fhcApi.factory.stv.mobility.getNoten()
		// 	.then(result => {
		// 		this.arrNoten = result.data;
		// 	})
		// 	.catch(this.$fhcAlert.handleSystemError);
		// this.$fhcApi.factory.stv.mobility.getAkadGrade(this.student.studiengang_kz)
		// 	.then(result => {
		// 		this.arrAkadGrad = result.data;
		// 	})
		// 	.catch(this.$fhcAlert.handleSystemError);
		// if (!this.student.length) {
		// 	this.$fhcApi.factory.stv.mobility.getTypStudiengang(this.student.studiengang_kz)
		// 		.then(result => {
		// 			this.stgTyp = result.data;
		// 			this.setDefaultFormData();
		// 		})
		// 		.catch(this.$fhcAlert.handleSystemError);
		// } else
		// 	this.getStudiengangsTyp();
	},
	template: `
	<div class="stv-details-mobility h-100 pb-3">
		<h4>In/out</h4>
	

		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			new-btn-show
			new-btn-label="Mobilität"
			@click:new="actionNewmobility"
			>
		</core-filter-cmpt>

		<form-form v-if="!this.student.length" ref="formMobility" @submit.prevent>
		
			<!-- <legend>{{this.$p.t('global','details')}}</legend>
			<p v-if="statusNew">[{{$p.t('ui', 'neu')}}]</p>
			<div class="row mb-3">
				<form-input
					container-class="col-6 stv-details-mobility-typ"
					:label="$p.t('global', 'typ')"
					type="select"
					v-model="formData.pruefungstyp_kurzbz"
					name="pruefungstyp_kurzbz"
					>
					<option
						v-for="typ in arrTypen"
						:key="typ.pruefungstyp_kurzbz"
						:value="typ.pruefungstyp_kurzbz"
						>
						{{typ.beschreibung}}
					</option>
				</form-input>
				<form-input
					container-class="col-6 stv-details-mobility-note"
					:label="$p.t('lehre', 'note')"
					type="select"
					v-model="formData.note"
					name="note"
					>
					<option :value="null"> -- {{$p.t('fehlermonitoring', 'keineAuswahl')}} -- </option>
					<option
						v-for="note in arrNoten"
						:key="note.note"
						:value="note.note"
						>
						{{note.bezeichnung}}
					</option>
				</form-input>
			</div>

			<div class="row mb-3">
				<form-input
					container-class="col-6 stv-details-mobility-pruefungsantritt"
					:label="$p.t('mobility', 'pruefungsantritt')"
					type="select"
					v-model="formData.pruefungsantritt_kurzbz"
					name="pruefungsantritt_kurzbz"
					>
					<option :value="null"> -- {{$p.t('fehlermonitoring', 'keineAuswahl')}} -- </option>
					<option
						v-for="antritt in arrAntritte"
						:key="antritt.pruefungsantritt_kurzbz"
						:value="antritt.pruefungsantritt_kurzbz"
						>
						{{antritt.bezeichnung}}
					</option>
				</form-input>
			</div>

			<div class="row mb-3">
				<template v-if="statusNew">
					<form-input
						container-class="col-6 stv-details-mobility-vorsitz"
						:label="$p.t('mobility', 'vorsitz_header')"
						type="autocomplete"
						optionLabel="mitarbeiter"
						v-model="formData.vorsitz"
						name="vorsitz"
						:suggestions="filteredMitarbeiter"
						@complete="search"
						:min-length="3"
						>
					</form-input>
				</template>
				<template v-else >
					<form-input
						v-if= "formData.pv"
						container-class="col-6 stv-details-mobility-vorsitz"
						type="text"
						name="name"
						:label="$p.t('mobility', 'vorsitz_header')"
						v-model="formData.pv"
						>
					</form-input>
					<form-input
						v-else
						container-class="col-6 stv-details-mobility-vorsitz"
						:label="$p.t('mobility', 'vorsitz_header')"
						type="autocomplete"
						optionLabel="mitarbeiter"
						v-model="formData.vorsitz"
						name="vorsitz"
						:suggestions="filteredMitarbeiter"
						@complete="search"
						:min-length="3"
						>
					</form-input>
				</template>

				<template v-if="statusNew">
					<form-input
						container-class="col-6 stv-details-mobility-pruefer1"
						:label="$p.t('mobility', 'pruefer1')"
						type="autocomplete"
						optionLabel="mitarbeiter"
						v-model="formData.pruefer1"
						name="pruefer1"
						:suggestions="filteredPruefer"
						@complete="searchNotAkad"
						:min-length="3"
						>
					</form-input>
				</template>
				<template v-else >
					<form-input
						v-if= "formData.p1"
						container-class="col-6 stv-details-mobility-pruefer1"
						type="text"
						name="name"
						:label="$p.t('mobility', 'pruefer1')"
						v-model="formData.p1"
						>
					</form-input>
					<form-input
						v-else
						container-class="col-6 stv-details-mobility-pruefer1"
						:label="$p.t('mobility', 'pruefer1')"
						type="autocomplete"
						optionLabel="mitarbeiter"
						v-model="formData.pruefer1"
						name="pruefer1"
						:suggestions="filteredPruefer"
						@complete="searchNotAkad"
						:min-length="3"
						>
					</form-input>
				</template>
			</div>

			<div class="row mb-3">

				<form-input
					container-class="col-6 stv-details-mobility-abschlussbeurteilung_kurzbz"
					:label="$p.t('mobility', 'abschlussbeurteilung')"
					type="select"
					v-model="formData.abschlussbeurteilung_kurzbz"
					name="abschlussbeurteilung_kurzbz"
					>
					<option :value="null"> -- {{$p.t('fehlermonitoring', 'keineAuswahl')}} -- </option>
					<option
						v-for="beurteilung in arrBeurteilungen"
						:key="beurteilung.abschlussbeurteilung_kurzbz"
						:value="beurteilung.abschlussbeurteilung_kurzbz"
						>
						{{beurteilung.bezeichnung}}
					</option>
				</form-input>
				<template v-if="statusNew">
					<form-input
						container-class="col-6 stv-details-mobility-pruefer2"
						:label="$p.t('mobility', 'pruefer2')"
						type="autocomplete"
						optionLabel="mitarbeiter"
						v-model="formData.pruefer2"
						name="pruefer2"
						:suggestions="filteredPruefer"
						@complete="searchNotAkad"
						:min-length="3"
						>
					</form-input>
				</template>
				<template v-else >
					<form-input
						v-if= "formData.p2"
						container-class="col-6 stv-details-mobility-pruefer2"
						type="text"
						name="name"
						:label="$p.t('mobility', 'pruefer2')"
						v-model="formData.p2"
						>
					</form-input>
					<form-input
						v-else
						container-class="col-6 stv-details-mobility-pruefer2"
						:label="$p.t('mobility', 'pruefer2')"
						type="autocomplete"
						optionLabel="mitarbeiter"
						v-model="formData.pruefer2"
						name="pruefer2"
						:suggestions="filteredPruefer"
						@complete="searchNotAkad"
						:min-length="3"
						>
					</form-input>
				</template>
			</div>

			<div class="row mb-3">
				<form-input
					container-class="col-6 stv-details-mobility-akadgrad"
					:label="$p.t('mobility', 'akadGrad')"
					type="select"
					v-model="formData.akadgrad_id"
					name="akadgrad"
					>
					<option
						v-for="grad in arrAkadGrad"
						:key="grad.akadgrad_id"
						:value="grad.akadgrad_id"
						>
						{{grad.titel}}
					</option>
				</form-input>
				<template v-if="statusNew">
					<form-input
						container-class="col-6 stv-details-mobility-pruefer3"
						:label="$p.t('mobility', 'pruefer3')"
						type="autocomplete"
						optionLabel="mitarbeiter"
						v-model="formData.pruefer3"
						name="pruefer3"
						:suggestions="filteredPruefer"
						@complete="searchNotAkad"
						:min-length="3"
						>
					</form-input>
				</template>
				<template v-else >
					<form-input
						v-if= "formData.p3"
						container-class="col-6 stv-details-mobility-pruefer3"
						type="text"
						name="name"
						:label="$p.t('mobility', 'pruefer3')"
						v-model="formData.p3"
						>
					</form-input>
					<form-input
						v-else
						container-class="col-6 stv-details-mobility-pruefer3"
						:label="$p.t('mobility', 'pruefer3')"
						type="autocomplete"
						optionLabel="mitarbeiter"
						v-model="formData.pruefer3"
						name="pruefer3"
						:suggestions="filteredPruefer"
						@complete="searchNotAkad"
						:min-length="3"
						>
					</form-input>
				</template>
			</div>

			<div class="row mb-3">
				<form-input
					container-class="col-6 stv-details-mobility-datum"
					:label="$p.t('global', 'datum')"
					type="DatePicker"
					v-model="formData.datum"
					auto-apply
					:enable-time-picker="false"
					format="dd.MM.yyyy"
					name="datum"
					:teleport="true"
					>
				</form-input>
				<form-input
					container-class="col-6 stv-details-mobility-anmerkung"
					:label="$p.t('global', 'anmerkung')"
					type="textarea"
					v-model="formData.anmerkung"
					name="anmerkung"
					>
				</form-input>
			</div>

			<div class="row mb-3">
				<form-input
					container-class="col-6 stv-details-mobility-sponsion"
					:label="$p.t('mobility', 'sponsion')"
					type="DatePicker"
					v-model="formData.sponsion"
					auto-apply
					:enable-time-picker="false"
					format="dd.MM.yyyy"
					name="sponsion"
					:teleport="true"
					>
				</form-input>
				<form-input
					container-class="col-6 stv-details-mobility-protokoll"
					:label="$p.t('mobility', 'protokoll')"
					type="textarea"
					v-model="formData.protokoll"
					name="protokoll"
					:rows= 10
					>
				</form-input>
			</div>

			<div class="row mb-3 col-6">
				<div class="col">
					<p >{{$p.t('mobility', 'zurBeurteilung')}}</p>
				</div>
				<div class="col">
					<p>
					   <a :href="formData.link" target="_blank" rel="noopener noreferrer">
						  {{$p.t('mobility', 'pruefungsprotokoll')}}
						</a>
					</p>
				</div>
			</div>

			<div class="text-end mb-3">
				<button v-if="statusNew" class="btn btn-primary" @click="addNewmobility()"> {{$p.t('ui', 'speichern')}}</button>
				<button v-else class="btn btn-primary" @click="updatemobility(formData.mobility_id)"> {{$p.t('ui', 'speichern')}}</button>
			</div> -->

		</form-form>

				
	</div>
`
}

