import {CoreFilterCmpt} from "../../../filter/Filter.js";
import FormInput from "../../../Form/Input.js";
import AkteEdit from "./Archiv/Edit.js";

export default {
	components: {
		CoreFilterCmpt,
		FormInput,
		AkteEdit
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
								.then(this.$fhcApi.factory.stv.archiv.delete)
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
			this.$refs.table.tabulator.updateOrAddData(data);
		},
		actionArchive() {
			let archiveDataArr = Array.isArray(this.modelValue) ? this.modelValue : [this.modelValue];

			for (let archiveData of archiveDataArr)
			{
				this.loading = true;
				let archiveFunction =
					this.selectedVorlage.signierbar
					? this.$fhcApi.factory.stv.archiv.archiveSigned
					: this.$fhcApi.factory.stv.archiv.archive;

				archiveFunction({
					xml: this.getXmlByXsl(this.selectedVorlage.vorlage_kurzbz),
					xsl: this.selectedVorlage.vorlage_kurzbz,
					ss: this.defaultSemester,
					uid: archiveData.uid,
					prestudent_id: archiveData.prestudent_id
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
		this.$fhcApi
			.factory.stv.archiv.getArchivVorlagen()
			.then(result => {this.vorlagenArchiv = result.data; this.selectedVorlage = result.data.filter(o => o.vorlage_kurzbz == 'Zeugnis')[0];})
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
			</template>
		</core-filter-cmpt>
		<akte-edit ref="edit" :config="config" @saved="updateData"></akte-edit>
	</div>`
};
