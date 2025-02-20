import {CoreFilterCmpt} from "../../../filter/Filter.js";
import FormInput from "../../../Form/Input.js";
//~ import KontoNew from "./Konto/New.js";
//~ import KontoEdit from "./Konto/Edit.js";

export default {
	components: {
		CoreFilterCmpt,
		FormInput
		//~ KontoNew,
		//~ KontoEdit
	},
	inject: {
		defaultSemester: {
			from: 'defaultSemester'
		}
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
			vorlage_kurzbz: '',
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
			}
			//studiengang_kz: false
		};
	},
	computed: {
		//~ personIds() {
			//~ if (this.modelValue.person_id)
				//~ return [this.modelValue.person_id];
			//~ return this.modelValue.map(e => e.person_id);
		//~ },
		tabulatorColumns() {
			const columns = [
				{title: "Akte Id", field: "akte_id", visible: false},
				{title: "Titel", field: "titel"},
				{title: "Bezeichnung", field: "bezeichnung"},
				{title: "Erstelldatum", field: "erstelltam"},
				{title: "Signiert", field: "erstelltam"},
				{title: "Selfservice", field: "signiert"},
				{title: "AkzeptiertAmUm", field: "akzeptiertamum"},
				{title: "Gedruckt", field: "gedruckt", visible: false},
				{
					title: 'Aktionen', field: 'actions',
					formatter: (cell, formatterParams, onRendered) => {
						let container = document.createElement('div');
						container.className = "d-flex gap-2";

						//~ let button = document.createElement('button');
						//~ button.className = 'btn btn-outline-secondary';
						//~ button.innerHTML = '<i class="fa fa-edit"></i>';
						//~ button.addEventListener('click', () =>
							//~ this.$refs.edit.open(cell.getData())
						//~ );
						//~ container.append(button);

						let button = document.createElement('button');
						button.className = 'btn btn-outline-secondary';
						button.innerHTML = '<i class="fa fa-trash"></i>';
						button.addEventListener('click', evt => {
							evt.stopPropagation();
							this.$fhcAlert
								.confirmDelete()
								.then(result => result ? {akte_id: cell.getData().akte_id, studiengang_kz: this.modelValue.studiengang_kz} : Promise.reject({handled:true}))
								.then(this.$fhcApi.factory.stv.archiv.delete)
								.then(() => {
									//cell.getRow().delete();
									this.reload();
								})
								.catch(this.$fhcAlert.handleSystemError);
						});
						container.append(button);

						return container;
					},
					minWidth: 150, // Ensures Action-buttons will be always fully displayed
					maxWidth: 150,
					frozen: true
				}
			];
			return Object.values(columns);
		},
		tabulatorOptions() {
			return this.$fhcApi.factory.stv.archiv.tabulatorConfig({
				layout:"fitDataTable",
				columns: this.tabulatorColumns,
				//selectable: true,
				//selectableRangeMode: 'click',
				index: 'akte_id',
				persistenceID: 'stv-details-archiv'
			}, this);
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
			// TODO(chris): check children (!delete?, multiple children)
			//this.$refs.table.tabulator.updateOrAddData(data.map(row => row.buchungsnr_verweis ? {buchungsnr:row.buchungsnr_verweis, _children:row} : row));
			this.$refs.table.tabulator.updateOrAddData(data);
		},
		actionArchive() {
			this.loading = true;
			this.$fhcApi
				.factory.stv.archiv.archive({
					xml: this.getXmlByXsl(this.vorlage_kurzbz),
					xsl: this.vorlage_kurzbz,
					ss: this.defaultSemester,
					uid: this.modelValue.uid,
					prestudent_id: this.modelValue.prestudent_id
				})
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
		},
		actionDownload(akte_id) {
			window.open(
				FHC_JS_DATA_STORAGE_OBJECT.app_root
				+ FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/api/frontend/v1/stv/archiv/download?akte_id=' + encodeURIComponent(akte_id),
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
		this.$fhcApi
			.factory.stv.archiv.getArchivVorlagen()
			.then(result => {this.vorlagenArchiv = result.data; this.vorlage_kurzbz = result.data.length > 0 ? result.data[0].vorlage_kurzbz : '';})
			.catch(this.$fhcAlert.handleSystemError);

	},
	template: `
	<div class="stv-details-konto h-100 d-flex flex-column">
		<core-filter-cmpt
			ref="table"
			table-only
			:side-menu="false"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			reload
			>
			<template #actions>
				<div class="input-group w-auto">
					<select class="form-select" v-model="vorlage_kurzbz">
						<option v-for="vorlage in vorlagenArchiv" :key="vorlage.vorlage_kurzbz" :value="vorlage.vorlage_kurzbz">
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
			</template>
		</core-filter-cmpt>
	</div>`
};
		//~ <konto-new ref="new" :config="config" @saved="updateData" :person-ids="personIds" :stg-kz="stg_kz"></konto-new>
		//~ <konto-edit ref="edit" :config="config" @saved="updateData"></konto-edit>
