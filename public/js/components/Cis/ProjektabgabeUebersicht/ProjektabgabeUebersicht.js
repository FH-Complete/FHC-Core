import {CoreFilterCmpt} from "../../../components/filter/Filter.js";
import ApiPaabgabe from '../../../api/factory/paabgabeUebersicht.js'
import Loader from "../../Loader.js";

export const ProjektabgabeUebersicht =  {
	name: "ProjektabgabeUebersicht",
	props: {
		viewData: Object // NOTE: this is inherited from router-view
	},
	components: {
		CoreFilterCmpt,
		Loader
	},
	data() {
		return {
			phrasenPromise: null,
			phrasenResolved: false,
			tabulatorUuid: Vue.ref(0),
			tableBuiltResolve: null,
			tableBuiltPromise: null,
			studiengaenge: null,
			abgabetypen: null,
			termine: null,
			abgaben: null,
			defaultStudiengang: {
				studiengang_kz: null,
				kuerzel: '-'
			},
			defaultTyp: {
				paabgabetyp_kurzbz: null,
				bezeichnung: '-'
			},
			defaultTermin: {
				termin: null,
				termin_anzeige: Vue.computed(() => this.$p.t('ui/alle'))
			},
			selectedStudiengang: null,
			selectedAbgabetyp: null,
			selectedTermin: null,
			personSearchString: null,
			paabgabeTableOptions: {
				height: Vue.ref(400),
				index: 'paabgabe_id',
				layout: 'fitColumns',
				//~ placeholder: this.$p.t('global/noDataAvailable'),
				columns: [
					{
						title: Vue.computed(() => this.$p.t('global/aktionen')), field: 'actions',
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let downloadButton = document.createElement('button');
							downloadButton.className = 'btn btn-outline-secondary';
							downloadButton.innerHTML = '<i class="fa fa-download"></i>';
							downloadButton.title = this.$p.t('ui', 'downloadDok');
							downloadButton.addEventListener('click', evt => {
								evt.stopPropagation();
								this.actionDownload(cell.getData().paabgabe_id);
							});
							container.append(downloadButton);

							if (this.viewData.showEdit)
							{
								let editButton = document.createElement('button');
								editButton.className = 'btn btn-outline-secondary';
								editButton.innerHTML = '<i class="fa fa-edit"></i>';
								//editButton.addEventListener('click', () =>
									//this.$refs.edit.open(cell.getData())
								//);
								container.append(editButton);
							}

							return container;
						}
					},
					{title: Vue.computed(() => this.$p.t('abgabetool/paabgabeid')), field: 'paabgabe_id', visible: false},
					{title: Vue.computed(() => this.$p.t('abgabetool/projektarbeitid')), field: 'projektarbeit_id', visible: false},
					{
						title: Vue.computed(() => this.$p.t('abgabetool/termin')),
						field: "termin",
						widthGrow: 1,
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
					{title: Vue.computed(() => this.$p.t('abgabetool/c4abgabetyp')), field: 'paabgabetyp_bezeichnung'},
					{title: Vue.computed(() => this.$p.t('person/uid')), field: 'uid'},
					{title: Vue.computed(() => this.$p.t('person/vorname')), field: 'vorname'},
					{title: Vue.computed(() => this.$p.t('person/nachname')), field: 'nachname'},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4projekttyp')), field: 'projekttyp_kurzbz'},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4titel')), field: 'titel'},
					{title: Vue.computed(() => this.$p.t('abgabetool/personStatus')), field: 'personStatus'},
					{
						title: "in Visual Library",
						field: 'in_visual_library',
						widthGrow: 1,
						formatter: (cell) => {
							return cell.getValue() ? this.$p.t('ui/ja') : this.$p.t('ui/nein');
						}
					}
				],
				persistence: false,
			},
			paabgabeTableEventHandlers: [{
					event: "tableBuilt",
					handler: async () => {
						this.tableBuiltResolve()
					}
				}
			]};
	},
	methods: {
		tableResolve(resolve) {
			this.tableBuiltResolve = resolve
		},
		setupData(){
			//~ const d = data.map(paabgabe => {
				//~ return {
					//~ ort_kurzbz: paabgabe.ort_kurzbz,
					//~ bezeichnung: paabgabe.bezeichnung.replace('&amp;', '&'),
					//~ nummer: paabgabe.planbezeichnung,
					//~ personen: paabgabe.max_person
				//~ }
			//~ })

			this.$refs.paabgabeTable.tabulator.setData(this.abgaben);
		},
		setNoDataPlaceholder() {
			this.$refs.paabgabeTable.tabulatorOptions.placeholder = this.$p.t('global/noDataAvailable');
		},
		loadStudiengaenge() {
			this.$api.call(ApiPaabgabe.getStudiengaenge())
				.then(res => {
					this.studiengaenge = res?.data ?? []
				})
		},
		loadPaabgabeTypes() {
			this.$api.call(ApiPaabgabe.getPaAbgabetypen())
				.then(res => {
					this.abgabetypen = res?.data ?? []
				})
		},
		loadTermine() {
			this.$api.call(ApiPaabgabe.getTermine(this.selectedStudiengang, this.selectedAbgabetyp))
				.then(res => {
					this.selectedTermin = null;
					this.termine = res?.data ?? []
			})
		},
		loadPaAbgaben() {
			this.$refs.loader.show();

			this.$api.call(
				ApiPaabgabe.getPaAbgaben(this.selectedStudiengang, this.selectedAbgabetyp, this.selectedTermin, this.personSearchString)
			)
			.then(res => {
				this.$refs.loader.hide();
				this.abgaben = res?.data ?? [];
				this.setupData(res?.data ?? []);
			});
		},
		handleUuidDefined(uuid) {
			this.tabulatorUuid = uuid
		},
		//~ setRoute(val) {
			//~ // TODO: router push
		//~ },
		async setupMounted() {
			this.loadStudiengaenge();
			this.loadPaabgabeTypes();
			this.loadTermine();

			this.tableBuiltPromise = new Promise(this.tableResolve);
			await this.tableBuiltPromise;

			this.setNoDataPlaceholder();
			//this.loadPaAbgaben();


			//~ const tableID = this.tabulatorUuid ? ('-' + this.tabulatorUuid) : ''
			//~ const tableDataSet = document.getElementById('filterTableDataset' + tableID);
			//~ if(!tableDataSet) return
			//~ const rect = tableDataSet.getBoundingClientRect();

			//~ const h = window.visualViewport.height - rect.top - 100
			//~ if(this.$refs.raumsucheTable) {
				//~ this.$refs.raumsucheTable.$refs.table.style.setProperty('height', h+'px')
			//~ }

		},
		actionDownload(paabgabe_id) {
			//~ window.open(
				//~ FHC_JS_DATA_STORAGE_OBJECT.app_root
				//~ + FHC_JS_DATA_STORAGE_OBJECT.ci_router
				//~ +'/api/frontend/v1/education/paabgabeuebersicht/downloadProjektarbeit?paabgabe_id=' + encodeURIComponent(paabgabe_id),
				//~ '_blank'
			//~ );
		},
		actionDownloadZip(ev) {
			console.log(ev);
			const url = new URL(FHC_JS_DATA_STORAGE_OBJECT.app_root
				+ FHC_JS_DATA_STORAGE_OBJECT.ci_router
				+'/api/frontend/v1/education/PaabgabeUebersicht/downloadZip');
			if (this.selectedStudiengang) url.searchParams.append('studiengang_kz', this.selectedStudiengang);
			if (this.selectedAbgabetyp) url.searchParams.append('abgabetyp_kurzbz', this.selectedAbgabetyp);
			if (this.selectedTermin) url.searchParams.append('abgabedatum', this.selectedTermin);
			if (this.personSearchString) url.searchParams.append('personSearchString', this.personSearchString);
			window.open(url.toString(), '_blank');
		}
	},
	computed: {
		isDarkMode() {
			return this.$theme.theme_name.value == 'dark';
		},
		personSearchEnabled() {
			return this.selectedStudiengang == null && this.selectedTermin == null && this.selectedAbgabetyp == null;
		},
		abgabeSearchEnabled() {
			return this.personSearchString == '' || this.personSearchString == null;
		},
		zipDownloadUrl() {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/api/frontend/v1/education/PaabgabeUebersicht/downloadZip';
		}
	},
	created() {
		this.phrasenPromise = this.$p.loadCategory(['abgabetool', 'global', 'person', 'ui']);
		this.phrasenPromise.then(()=> {this.phrasenResolved = true});
	},
	mounted() {
		this.setupMounted();
	},
	template: `
	<h1 class="h3">{{$p.t('abgabetool/projektabgabeUebersicht')}}</h1>
	<hr>
	<div class="row">

		<div class="col-12 col-lg-2">
			<h6>{{ $p.t('abgabetool/studiengang') }}:</h6>
			<select
				ref="studiengang"
				id="studiengangSelect"
				v-model="selectedStudiengang"
				class="form-select"
				:aria-label="$p.t('abgabetool/studiengang_auswaehlen')"
				:disabled="!abgabeSearchEnabled"
				@change="loadTermine();"
			>
				<option :key="defaultStudiengang.studiengang_kz" selected :value="defaultStudiengang.studiengang_kz">{{defaultStudiengang.kuerzel}}</option>
				<option v-for="stg in studiengaenge" :key="stg.studiengang_kz" :value="stg.studiengang_kz">{{stg.kuerzel}}</option>
			</select>
		</div>

		<div class="col-12 col-lg-2">
			<h6>{{ $p.t('abgabetool/c4abgabetyp') }}:</h6>
			<select
				ref="abgabetyp"
				id="abgabetypSelect"
				v-model="selectedAbgabetyp"
				class="form-select"
				:aria-label="$p.t('abgabetool/abgabetyp_auswaehlen')"
				:disabled="!abgabeSearchEnabled"
				@change="loadTermine();"
			>
				<option :key="defaultTyp.paabgabetyp_kurzbz" selected :value="defaultTyp.paabgabetyp_kurzbz">{{defaultTyp.bezeichnung}}</option>
				<option v-for="typ in abgabetypen" :key="typ.paabgabetyp_kurzbz" :value="typ.paabgabetyp_kurzbz">{{typ.bezeichnung}}</option>
			</select>
		</div>

		<div class="col-12 col-lg-2">
			<h6>{{ $p.t('abgabetool/termin') }}:</h6>
			<select
				ref="termin"
				id="terminSelect"
				v-model="selectedTermin"
				class="form-select"
				:aria-label="$p.t('abgabetool/termin_auswaehlen')"
				:disabled="!abgabeSearchEnabled"
			>
				<option :key="defaultTermin.termin" selected :value="defaultTermin.termin">{{defaultTermin.termin_anzeige}}</option>
				<option v-for="termin in termine" :key="termin.termin" :value="termin.termin">{{termin.termin_anzeige}}</option>
			</select>
		</div>

		<div class="col-12 col-lg-2">
			<h6>{{ $p.t('abgabetool/personsuche') }}:</h6>
			<input
				type="text"
				name="person-search"
				class="form-control"
				:placeholder="'name, uid, person ID, prestudent ID'"
				:disabled="!personSearchEnabled"
				v-on:keyup.enter="loadPaAbgaben"
				v-model="personSearchString"
			/>
		</div>

		<div class="col-12 col-lg-2 align-content-end">
			<button class="btn btn-primary border-0" @click="loadPaAbgaben">{{ $p.t('abgabetool/anzeigen') }}</button>
		</div>
		<div class="col-12 col-lg-2 align-content-end">
			<button class="btn btn-secondary border-0" @click="actionDownloadZip">{{ $p.t('abgabetool/zipDownload') }}</button>
		</div>

	</div>

	<core-filter-cmpt
		v-if="phrasenResolved"
		@uuidDefined="handleUuidDefined"
		ref="paabgabeTable"
		:tabulator-options="paabgabeTableOptions"
		:tabulator-events="paabgabeTableEventHandlers"
		tableOnly
		:sideMenu="false"
	 />

	 <loader ref="loader" :timeout="0"></loader>
	`
};

export default ProjektabgabeUebersicht;
