import {CoreFilterCmpt} from "../../../../../filter/Filter.js";
import ModalEdit from "../Modal/Edit.js";
import ModalUpload from "../Modal/Upload.js";

import ApiStvDocuments from "../../../../../../api/factory/stv/documents.js";

export default {
	name: "UnacceptedDocuments",
	components: {
		CoreFilterCmpt,
		ModalEdit,
		ModalUpload
	},
	props: {
		prestudent_id: Number,
		studiengang_kz: Number
	},
	data(){
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(
					ApiStvDocuments.getDocumentsUnaccepted({
						id: this.prestudent_id,
						studiengang_kz: this.studiengang_kz})
				),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Dokument", field: "bezeichnung"},
					{title: "Kurzbz", field: "dokument_kurzbz", visible: false},
					{
						title: "UploadDatum", field: "hochgeladenamum",
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour12: false
							});
						}
						},
					{title: "nachgereicht", field: "nachgereicht", visible: false,
						formatter:"tickCross",
						hozAlign:"center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-secondary"></i>',
							crossElement: '<i class="fa fa-xmark text-secondary"></i>'
						}},
					{title: "vorhanden",
						field: "vorhanden",
						formatter: function(cell, formatterParams, onRendered) {
							let value = cell.getValue();
							let rowData = cell.getRow().getData();  // Zugriff auf gesamte Zeile
							let dueDate = rowData["nachgereicht_am"];
							const date = new Date(dueDate);
							let dateFormatted = date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour12: false
							});

							let tickCrossIcon = value
								? '<i class="fa fa-check text-success"></i>'
								: '<i class="fa fa-xmark text-danger"></i>';

							// if nachgereicht_am show datum
							let pill = dueDate
								? `<span>${dateFormatted}</span>`
								: '';

							return `${tickCrossIcon} ${pill}`;
						},
						hozAlign: "center"
						},
					{title: "Infotext", field: "infotext"},
					{title: "akte_id", field: "akte_id"},
					{title: "titel_intern", field: "titel_intern"},
					{title: "Anmerkung_intern", field: "anmerkung_intern", visible: false},
					{title: "Online", field: "onlinebewerbung",
						formatter:"tickCross",
						hozAlign:"center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-secondary"></i>',
							crossElement: '<i class="fa fa-xmark text-secondary"></i>'
						}},
					{title: "Pflicht", field: "pflicht",
						formatter:"tickCross",
						hozAlign:"center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-secondary"></i>',
							crossElement: '<i class="fa fa-xmark text-secondary"></i>'
						}},
					{title: "Nachreichung am", field: "nachgereicht_am",
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour12: false
							});
						}},
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 50,
						maxWidth: 100,
						formatter: (cell, formatterParams, onRendered) => {

							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							if(cell.getData().akte_id){
								let button = document.createElement('a');
								button.className = 'btn btn-outline-secondary btn-action';
								button.innerHTML = '<i class="fa fa-download"></i>';
								button.title = this.$p.t('ui', 'downloadDok');
								button.href = this.actionDownloadFile(cell.getData().akte_id);
								button.role = 'button';
								button.target = '_blank';
								container.append(button);

								button = document.createElement('button');
								button.className = 'btn btn-outline-secondary btn-action';
								button.innerHTML = '<i class="fa fa-edit"></i>';
								button.title = this.$p.t('ui', 'editDokument');
								button.addEventListener('click', (event) =>
									this.actionEditFile(cell.getData().akte_id)
								);
								container.append(button);

								button = document.createElement('button');
								button.className = 'btn btn-outline-secondary btn-action';
								button.innerHTML = '<i class="fa fa-xmark"></i>';
								button.title = this.$p.t('ui', 'deleteDokument');
								button.addEventListener('click', () =>
									this.actionDeleteFile(cell.getData().akte_id)
								);
								container.append(button);
							}
							else
							{
								let button = document.createElement('button');
								button.className = 'btn btn-outline-secondary btn-action';
								button.innerHTML = '<i class="fa fa-upload"></i>';
								button.title = this.$p.t('ui', 'uploadDokument');
								button.addEventListener('click', () =>
									this.actionUploadFile(cell.getData().dokument_kurzbz)
								);
								container.append(button);
							}

							return container;
						},
						frozen: true
					},
				],
				layout: 'fitDataStretchFrozen',
				layoutColumnsOnNewData: false,
				height: 300,
				selectable: true,
				selectableRangeMode: 'click',
				persistenceID: 'core-details-documents-unaccepted',
				listDocuments: [],
				prestudentDocumentData: [],
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async () => {
						await this.$p.loadCategory(['global', 'dokumente', 'ui', 'mobility', 'ampeln']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('bezeichnung').component.updateDefinition({
							title: this.$p.t('global', 'dokument')
						});
						cm.getColumnByField('dokument_kurzbz').component.updateDefinition({
							title: this.$p.t('mobility', 'kurzbz')
						});
						cm.getColumnByField('hochgeladenamum').component.updateDefinition({
							title: this.$p.t('global', 'uploaddatum')
						});
						cm.getColumnByField('nachgereicht').component.updateDefinition({
							title: this.$p.t('dokumente', 'nachgereicht')
						});
						cm.getColumnByField('vorhanden').component.updateDefinition({
							title: this.$p.t('dokumente', 'vorhanden')
						});
						cm.getColumnByField('titel_intern').component.updateDefinition({
							title: this.$p.t('global', 'titel')
						});
						cm.getColumnByField('anmerkung_intern').component.updateDefinition({
							title: this.$p.t('global', 'anmerkung')
						});
						cm.getColumnByField('akte_id').component.updateDefinition({
							title: this.$p.t('global', 'akte_id')
						});
						cm.getColumnByField('pflicht').component.updateDefinition({
							title: this.$p.t('ampeln', 'mandatory')
						});
						cm.getColumnByField('nachgereicht_am').component.updateDefinition({
							title: this.$p.t('global', 'dokument')
						});
					}
				},
				{
					event: "rowDblClick",
					handler: (e, row) => {
						if (row.getData().vorhanden) {
							window.open(
								this.actionDownloadFile(row.getData().akte_id)
							);
						}
					}
				}
			]
		}
	},
	methods: {
		actionDownloadFile(akte_id){
			return FHC_JS_DATA_STORAGE_OBJECT.app_root
				+ FHC_JS_DATA_STORAGE_OBJECT.ci_router 
				+ '/api/frontend/v1/stv/dokumente/download?akte_id=' 
				+ encodeURIComponent(akte_id);
		},
		actionUploadFile(dokument_kurzbz){
			this.$refs.modalUpload.open(this.prestudent_id, dokument_kurzbz);
		},
		actionDeleteFile(akte_id){
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? akte_id
					: Promise.reject({handled: true}))
				.then(this.deleteFile)
				.catch(this.$fhcAlert.handleSystemError);
		},
		actionEditFile(akte_id){
			this.$refs.modalEdit.open(akte_id);
		},
		acceptDocuments(selected){

			//Check if more than one document per dokument_kurzbz should be accepted
			const counts = {};
			for (const item of selected)
			{
				const value = item.dokument_kurzbz;
				if (!counts[value]) {
					counts[value] = 1;
				}
				else {
					counts[value]++;
				}

				if (counts[value] > 1) {
					{
						this.$fhcAlert.alertError(this.$p.t('dokumente', 'error_duplicateDokument_kurzbz'));
						return;
					}
				}
			}

			Promise
				.allSettled(
					selected.map(e =>
						this.$api
							.call(ApiStvDocuments.createZuordnung({
								prestudent_id: this.prestudent_id,
								dokument_kurzbz: e.dokument_kurzbz
							}))
							.then(() => ({
							success: true,
							dokument_bz: e.bezeichnung
						}))
							.catch(() => ({
								success: false,
								dokument_bz: e.bezeichnung
							}))
					)
				)
				.then(results => {
						const failed = results.filter(res => !res.value.success);
						const suceeded = results.filter(res => res.value.success);
						if (failed.length > 0) {
							failed.forEach(res => {
								this.$fhcAlert.alertError(this.$p.t('dokumente', 'errorAccepted',
									{'dokument_kurzbz': res.value.dokument_bz}
								));
							});
							let countSuceeded = suceeded.length;
							if(countSuceeded > 0)
								this.$fhcAlert.alertSuccess(this.$p.t('dokumente', 'successCountAccepted',
									{'count': countSuceeded}));

						} else {
							this.$fhcAlert.alertSuccess(this.$p.t('dokumente', 'successAccepted'));
						}
						this.reloadAll();
				});
		},
		deleteFile(akte_id){
			return this.$fhcApi.factory.stv.documents.deleteFile(akte_id)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(()=> {
					this.reload();
				});
		},
		reload(){
			this.$refs.table.reloadTable();
		},
		reloadAll() {
			this.reload();
			this.$emit('reloadAccepted');
		}
	},
	template: `
	 <div class="stv-details-documents h-100 pb-3">
		<h5>{{$p.t('dokumente', 'unaccepted')}}</h5>

		 <modal-edit
			ref="modalEdit"
			@reload="reloadAll"
			>
		</modal-edit>

		 <modal-upload
			ref="modalUpload"
			@reload="reloadAll"
			>
		</modal-upload>

		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			new-btn-show
			:new-btn-label="this.$p.t('global', 'dokument')"
			@click:new="actionUploadFile"
		>
			<template #actions="{selected}">
				<button
					class="btn btn-primary"
					@click="acceptDocuments(selected)"
					:disabled="!selected.length"
					>
						{{$p.t('dokumente', 'dokumentAccept')}}
				</button>
			</template>
		</core-filter-cmpt>
	 </div>
	 `
}