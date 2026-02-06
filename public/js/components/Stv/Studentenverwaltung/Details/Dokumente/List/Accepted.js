import {CoreFilterCmpt} from "../../../../../filter/Filter.js";
import ModalEdit from "../Modal/Edit.js";
import ModalUpload from "../Modal/Upload.js";

import ApiStvDocuments from "../../../../../../api/factory/stv/documents.js";

export default {
	components: {
		name: "AcceptedDocuments",
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
			listDocuments: [],
			layoutColumnsOnNewData: false,
			height: 300,
		}
	},
	computed: {
		tabulatorOptions() {
			const options = {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(
					ApiStvDocuments.getDocumentsAccepted({
						id: this.prestudent_id,
						studiengang_kz: this.studiengang_kz})
				),
				ajaxResponse: (url, params, response) =>  {
					return response.data;
				},
				layout: 'fitDataStretchFrozen',
				index: 'akte_id',
				selectableRows: true,
				selectableRowsRangeMode: 'click',
				persistenceID: 'stv-details-accepted-2026020401',
				columns: [
					{title: "akte_id", field: "akte_id", visible: false},
					{title: "Dokument", field: "bezeichnung"},
					{title: "Akzeptiertdatum", field: "docdatum",
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
						}},
					{title: "Akzeptiertvon", field: "insertvonma"},
					{title: "Kurzbz", field: "dokument_kurzbz", visible: false},
					{title: "Prestudent ID", field: "prestudent_id", visible: false},
					{title: "nachgereicht", field: "nachgereicht", visible: false,
						formatter:"tickCross",
						hozAlign:"center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-secondary"></i>',
							crossElement: '<i class="fa fa-xmark text-secondary"></i>'
						}},
					{title: "Infotext", field: "infotext"},
					{title: "dms_id", field: "dms_id", visible: false},
					{title: "titel", field: "titel_intern", visible: false},
					{title: "vorhanden", field: "vorhanden",
						formatter:"tickCross",
						hozAlign:"center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-success"></i>',
							crossElement: '<i class="fa fa-xmark text-danger"></i>'
						}},
					{title: "Anmerkung_intern", field: "anmerkung_intern", visible: false},
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

							if(cell.getData().vorhanden){
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
									this.actionEditDocument(cell.getData().akte_id)
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
			};
			return options;
		},
		tabulatorEvents() {
			const events = [
				{
					event: 'tableBuilt',
					handler: async () => {
						await this.$p.loadCategory(['global', 'dokumente', 'ui', 'mobility', 'ampeln']);

						const setHeader = (field, text) => {
							const col = this.$refs.table.tabulator.getColumn(field);
							if (!col) return;

							const el = col.getElement();
							if (!el || !el.querySelector) return;

							const titleEl = el.querySelector('.tabulator-col-title');
							if (titleEl) {
								titleEl.textContent = text;
							}
						};
						setHeader('bezeichnung', this.$p.t('global', 'dokument'));
						setHeader('docdatum', this.$p.t('dokumente', 'datumAkzeptiert'));
						setHeader('dokument_kurzbz', this.$p.t('mobility', 'kurzbz'));
						setHeader('insertvonma', this.$p.t('dokumente', 'akzeptiertVon'));
						setHeader('hochgeladenamum', this.$p.t('global', 'uploaddatum'));
						setHeader('nachgereicht', this.$p.t('dokumente', 'nachgereicht'));
						setHeader('vorhanden', this.$p.t('dokumente', 'vorhanden'));
						setHeader('dms_id', this.$p.t('global', 'dms_id'));
						setHeader('titel_intern', this.$p.t('global', 'titel'));
						setHeader('anmerkung_intern', this.$p.t('global', 'anmerkung'));
						setHeader('akte_id', this.$p.t('global', 'akte_id'));
						setHeader('nachgereicht_am', this.$p.t('dokumente', 'nachreichungAm'));
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
			];
			return events;
		},
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
		actionEditDocument(akte_id){
			this.$refs.modalEdit.open(akte_id);
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
		deleteFile(akte_id){
			return this.$api
				.call(ApiStvDocuments.deleteFile(akte_id))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(()=> {
					this.reload();
				});
		},
		deleteZuordnung(selected) {

			//Check if more than one document per dokument_kurzbz should be unaccepted
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

			Promise.allSettled(
				selected.map(e =>
					this.$api
						.call(ApiStvDocuments.deleteZuordnung({
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
			).then(results => {
				const failed = results.filter(res => !res.value.success);
				const suceeded = results.filter(res => res.value.success);
				if (failed.length > 0) {
					failed.forEach(res => {
						this.$fhcAlert.alertError(this.$p.t('dokumente', 'errorUnaccepted',
							{'dokument_kurzbz': res.value.dokument_bz}
						));
					});
					let countSuceeded = suceeded.length;
					if(countSuceeded > 0)
						this.$fhcAlert.alertSuccess(this.$p.t('dokumente', 'successCountUnaccepted',
							{'count': countSuceeded}));

				} else {
					this.$fhcAlert.alertSuccess(this.$p.t('dokumente', 'successUnaccepted'));
				}
				this.reloadAll();
			});
		},

		reload(){
			this.$refs.table.reloadTable();
		},
		reloadAll(){
			this.reload();
			this.$emit('reloadUnaccepted');
		}
	},
	template: `
	 <div class="stv-details-aceepted h-100 pb-3">
		<h5>{{$p.t('dokumente', 'accepted')}}</h5>

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
			:reload-btn-infotext="this.$p.t('table', 'reload')"
	 	>
			<template #actions="{selected}">
				<button
					class="btn btn-primary"
					@click="deleteZuordnung(selected)"
					:disabled="!selected.length"
					>
						{{$p.t('dokumente', 'dokumentUnaccept')}}
				</button>
			</template>
		</core-filter-cmpt>
	 </div>
	 `
}