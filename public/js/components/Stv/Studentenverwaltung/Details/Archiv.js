import {CoreFilterCmpt} from "../../../filter/Filter.js";
import FormInput from "../../../Form/Input.js";
import AkteEdit from "./Archiv/Edit.js";

import ApiStvArchiv from '../../../../api/factory/stv/archiv.js';
import ApiStvDocuments from '../../../../api/factory/stv/documents.js';
import DocumentDropdown from "../Details/Archiv/DocumentDropdown.js";


export default {
	name: 'Archiv',
	components: {
		CoreFilterCmpt,
		FormInput,
		AkteEdit,
		DocumentDropdown
	},
	inject: {
		currentSemester: {
			from: 'currentSemester'
		},
		/* isBerechtigtDocAndOdt: {
			from: 'hasPermissionOutputformat',
			default: false
		},*/
		cisRoot: {
			from: 'cisRoot'
		},
	},
	props: {
		modelValue: Object,
		config: {
			type: Object,
			default: {}
		}
	},
	data() {
		return {
			loading: false,
			selectedVorlage: {},
			vorlagenArchiv: [],
			vorlageXmlXslMappings: {
				'zeugnis.rdf.php': [
						'Zeugnis',
						'ZeugnisEng'
				],
				'abschlusspruefung.rdf.php': [
						'PrProtokollBakk',
						'PrProtBakkEng',
						'PrProtBA',
						'PrProtBAEng',
						'PrProtokollDipl',
						'PrProtDiplEng',
						'PrProtMA',
						'PrProtMAEng',
						'Bescheid',
						'BescheidEng',
						'Bakkurkunde',
						'BakkurkundeEng',
						'Diplomurkunde',
						'DiplomurkundeEng',
				],
				'diplomasupplement.xml.php': [
						'DiplSupplement',
						'SZeugnis'
				],
				'studienblatt.xml.php': [
						'Studienblatt',
						'StudienblattEng'
				],
				'ausbildungsvertrag.xml.php': [
						'Ausbildungsver',
						'AusbVerEng'
				],
				'abschlussdokument_lehrgaenge.xml.php': [
						'AbschlussdokumentLehrgaenge'
				]
			},
			documentDropdownObject: {}
		};
	},
	computed: {
		tabulatorOptions() {
			const options = {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(
					ApiStvArchiv.getArchiv(
						this.modelValue.person_id ? [this.modelValue.person_id] : null || this.modelValue.map(e => e.person_id)
					)
				),
				ajaxResponse: (url, params, response) => response.data,
				layout:"fitDataTable",
				index: 'akte_id',
				persistenceID: 'stv-details-archiv',
				columns: [
					{title: "Akte Id", field: "akte_id", visible: false},
					{title: this.$p.t('stv', 'archiv_title'), field: "titel"},
					{title: this.$p.t('stv', 'archiv_description'), field: "bezeichnung"},
					{title: this.$p.t('stv', 'archiv_creation_date'), field: "erstelltam"},
					{
						title: this.$p.t('stv', 'archiv_signiert'),
						field: "signiert",
						formatter:"tickCross",
						hozAlign:"center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-success"></i>',
							crossElement: '<i class="fa fa-xmark text-danger"></i>'
						}
					},
					{
						title: "Selfservice",
						field: "stud_selfservice",
						formatter:"tickCross",
						hozAlign:"center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-success"></i>',
							crossElement: '<i class="fa fa-xmark text-danger"></i>'
						},
					},
					{title: this.$p.t('stv', 'archiv_accepted_on_at'), field: "akzeptiertamum"},
					{
						title: this.$p.t('stv', 'archiv_gedruckt'),
						field: "gedruckt",
						visible: false,
						formatter:"tickCross",
						hozAlign:"center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-success"></i>',
							crossElement: '<i class="fa fa-xmark text-danger"></i>'
						}
					},
					{
						title: 'Aktionen', field: 'actions',
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let downloadButton = document.createElement('button');
							downloadButton.className = 'btn btn-outline-secondary';
							downloadButton.innerHTML = '<i class="fa fa-download"></i>';
							downloadButton.title = this.$p.t('ui', 'downloadDok');
							downloadButton.addEventListener('click', evt => {
								evt.stopPropagation();
								this.actionDownload(cell.getData().akte_id);
							});
							container.append(downloadButton);

							if (this.config.showEdit)
							{
								let editButton = document.createElement('button');
								editButton.className = 'btn btn-outline-secondary';
								editButton.innerHTML = '<i class="fa fa-edit"></i>';
								editButton.addEventListener('click', () =>
									this.$refs.edit.open(cell.getData())
								);
								container.append(editButton);
							}

							let deleteButton = document.createElement('button');
							deleteButton.className = 'btn btn-outline-secondary';
							deleteButton.innerHTML = '<i class="fa fa-trash"></i>';
							deleteButton.addEventListener('click', evt => {
								evt.stopPropagation();
								this.$fhcAlert
									.confirmDelete()
									.then(result => result ? {akte_id: cell.getData().akte_id, studiengang_kz: this.modelValue.studiengang_kz} : Promise.reject({handled:true}))
									.then((params) => {
										return this.$api.call(ApiStvArchiv.delete(params.akte_id, params.studiengang_kz))
									})
									.then(() => {
										//cell.getRow().delete();
										this.reload();
									})
									.catch(this.$fhcAlert.handleSystemError);
							});
							container.append(deleteButton);

							return container;
						},
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						maxWidth: 150,
						frozen: true
					}
				]
			};
			return options;
		},
		tabulatorEvents() {
			const events = [
				{
					event: "rowDblClick",
					handler: (e, row) => {
						this.actionDownload(row.getData().akte_id);
					}
				}
			];

			return events;
		},
		studentUids() {
			if (this.modelValue.uid)
			{
				return [this.modelValue.uid];
			}
			return this.modelValue.map(e => e.uid);
		},
		studentKzs(){
			if (this.modelValue.uid)
			{
				return [this.modelValue.studiengang_kz];
			}
			return this.modelValue.map(e => e.studiengang_kz);
		},
		stg_kz(){
			return this.studentKzs[0];
		},
		showAllFormats() {
			if( this.isBerechtigtDocAndOdt === false
				|| !Array.isArray(this.isBerechtigtDocAndOdt) )
			{
				return false;
			}
			let retval = this.isBerechtigtDocAndOdt.includes(this.stgInfo.oe_kurzbz);
			return retval;
		},
		showDropDownMulti(){
			if (this.modelValue.length) {
				return true;
			}
			return false;
		}
	},
	watch: {
		modelValue() {
			this.$refs.table.reloadTable();
		}
	},
	methods: {
		reload() {
			this.$refs.table.reloadTable();
		},
		updateData(data) {
			if (!data)
				return this.reload();
			this.$refs.table.tabulator.updateOrAddData(data);
		},
		actionArchive() {
			let archiveDataArr = Array.isArray(this.modelValue) ? this.modelValue : [this.modelValue];

			for (let archiveData of archiveDataArr)
			{
				this.loading = true;

				// sign document depending on signierbar property
				let archiveFunction =
					this.selectedVorlage.signierbar
					? ApiStvArchiv.archiveSigned
					: ApiStvArchiv.archive;

				this.$api.call(
					archiveFunction({
						xml: this.getXmlByXsl(this.selectedVorlage.vorlage_kurzbz),
						xsl: this.selectedVorlage.vorlage_kurzbz,
						ss: this.currentSemester,
						uid: archiveData.uid,
						prestudent_id: archiveData.prestudent_id
					})
				)
				.then(result => result.data)
				.then(() => {
					this.reload();
					this.loading = false;
				})
				.then(() => this.$p.t('ui/gespeichert'))
				.then(this.$fhcAlert.alertSuccess)
				.catch(error => {
					this.$fhcAlert.handleSystemError(error);
					this.loading = false;
				});
			}
		},
		actionDownload(akte_id) {
			window.open(
				FHC_JS_DATA_STORAGE_OBJECT.app_root
				+ FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/api/frontend/v1/stv/akte/download?akte_id=' + encodeURIComponent(akte_id),
				'_blank'
			);
		},
		getXmlByXsl(xsl) {
			for (let xml in this.vorlageXmlXslMappings) {
				let xslArr = this.vorlageXmlXslMappings[xml];

				if (xslArr.includes(xsl)) return xml;
			}
			return null;
		}
	},
	created() {
		this.$api
			.call(ApiStvArchiv.getArchivVorlagen())
			.then(result => {
				this.vorlagenArchiv = result.data;
				this.selectedVorlage = result.data.filter(o => o.vorlage_kurzbz == 'Zeugnis')[0];
			})
			.catch(this.$fhcAlert.handleSystemError);

		if (this.modelValue.length) {
			const params = {
				studiensemester_kurzbz: this.currentSemester,
				studiengang_kz: this.stg_kz
			};
			this.$api
				.call(ApiStvDocuments.getDocumentDropdownMulti(this.studentUids, params))
				.then(result => {
					this.documentDropdownObject = result;
				})
				.catch(this.$fhcAlert.handleSystemError);
		} else {
			const params = {
				prestudent_id: this.modelValue.prestudent_id,
				studiensemester_kurzbz: this.currentSemester,
				studiengang_kz: this.modelValue.studiengang_kz
			};
			this.$api
				.call(ApiStvDocuments.getDocumentDropdown(params))
				.then(result => {
					this.documentDropdownObject = result;
				})
				.catch(this.$fhcAlert.handleSystemError);
		}

	},
	template: `
	<div class="stv-details-archiv h-100 d-flex flex-column">

		<core-filter-cmpt
			ref="table"
			table-only
			:side-menu="false"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			reload
			:reload-btn-infotext="this.$p.t('table', 'reload')"
			>
			<template #actions>

				<div class="input-group w-auto">
					<select class="form-select" v-model="selectedVorlage">
						<option v-for="vorlage in vorlagenArchiv" :key="vorlage.vorlage_kurzbz" :value="vorlage">
							{{ vorlage.bezeichnung }}
						</option>
					</select>
					<button
						class="btn btn-primary"
						@click="actionArchive()"
						:disabled="loading"
						>
						<i v-if="loading" class="fa fa-spinner fa-spin"></i>
						{{ $p.t('stv/archiv_dokument_archivieren') }}
					</button>
				</div>

				<document-dropdown
					v-if="documentDropdownObject.data"
					:documents="documentDropdownObject.data"
					:showAllFormats='true'
					:studentUids="studentUids"
					:showDropDownMulti="showDropDownMulti"
					:cisRoot="cisRoot"
					:stgKz="stg_kz"
				></document-dropdown>

			</template>
		</core-filter-cmpt>
		<akte-edit ref="edit" :config="config" @saved="updateData"></akte-edit>
	</div>`
};
