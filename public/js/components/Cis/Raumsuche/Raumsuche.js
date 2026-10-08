import {CoreFilterCmpt} from "../../../components/filter/Filter.js";
import VueDatePicker from '../../vueDatepicker.js.php';
import ApiOrt from '../../../api/factory/ort.js'
import InViewHelp from '../../InViewHelp.js';

// advanced filters: tbl_ort column, filter type and label, the same list as Ort::ADVANCED_FILTERS
const ADVANCED_FILTERS = [
	{ column: 'lehre', type: 'flag', label: 'rauminfo/nurLehrraeume', checked: true },
	{ column: 'content_id', type: 'exists', label: 'rauminfo/nurMitInfoseite' },
	{ column: 'ort_kurzbz', type: 'text', label: 'rauminfo/raum_kurzbz' },
	{ column: 'bezeichnung', type: 'text', label: 'global/bezeichnung' },
	{ column: 'planbezeichnung', type: 'text', label: 'rauminfo/raumnummer' },
	{ column: 'stockwerk', type: 'range', label: 'rauminfo/stockwerk' },
	{ column: 'gebteil', type: 'select', label: 'rauminfo/gebaeudeteil' },
	{ column: 'm2', type: 'range', label: 'rauminfo/flaeche' }
];

// the GET parameters of the advanced filters with their default values
const ADVANCED_DEFAULTS = {};
for (const { column, type, checked } of ADVANCED_FILTERS) {
	if (type === 'range') {
		ADVANCED_DEFAULTS[column + '_min'] = '';
		ADVANCED_DEFAULTS[column + '_max'] = '';
	} else {
		ADVANCED_DEFAULTS[column] = type === 'flag' || type === 'exists' ? !!checked : '';
	}
}

export const Raumsuche =  {
	name: "Raumsuche",
	components: {
		VueDatePicker,
		CoreFilterCmpt,
		InViewHelp,
		InputNumber: primevue.inputnumber,
	},
	inject: ["isMobile"],
	data() {
		return {
			phrasenPromise: null,
			phrasenResolved: false,
			tabulatorUuid: Vue.ref(0),
			tableBuiltResolve: null,
			tableBuiltPromise: null,
			roomtypes: null,
			anzahl: 1,
			selectedType: null,
			standorte: null,
			standort_id: null,
			roomsRequest: 0,
			loading: false,
			advancedFilterFields: ADVANCED_FILTERS,
			advancedFilters: { ...ADVANCED_DEFAULTS },
			advancedFilterOptions: {},
			showAdvancedFilters: false,
			datum: new Date(),
			von: Vue.ref({
				hours: new Date().getHours(),
				minutes: new Date().getMinutes()
			}),
			bis: Vue.ref({
				hours: new Date().getHours() + 1,
				minutes: new Date().getMinutes()
			}),
			datepickerTextInputOptions: {
				enterSubmit: true,
				tabSubmit: true,
				selectOnFocus: true,
				format: 'dd.MM.yyyy',
				escClose: true
			},
			timepickerTextInputOptions: {
				enterSubmit: true,
				tabSubmit: true,
				selectOnFocus: true,
				format: 'HH:mm',
				escClose: true
			},
			raumsucheTableOptions: {
				height: Vue.ref(400),
				index: 'ort_kurzbz',
				layout: 'fitColumns',
				placeholder: this.$p.t('global/noDataAvailable'),
				columns: [
					{title: Vue.computed(() => this.$p.t('rauminfo/raum_kurzbz')), field: 'ort_kurzbz', widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('global/bezeichnung')), field: 'bezeichnung', widthGrow: 2},
					{title: Vue.computed(() => this.$p.t('rauminfo/raumnummer')), field: 'nummer', widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('rauminfo/standort')), field: 'standort', widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('rauminfo/gebaeudeteil')), field: 'gebteil', widthGrow: 1, visible: false},
					{title: Vue.computed(() => this.$p.t('rauminfo/stockwerk')), field: 'stockwerk', sorter: 'number', widthGrow: 1, visible: false},
					{title: Vue.computed(() => this.$p.t('rauminfo/personcap')), field: 'personen', widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('rauminfo/flaeche')), field: 'm2', sorter: 'number', widthGrow: 1, visible: false},
					{title: Vue.computed(() => this.$p.t('rauminfo/lehrraum')), field: 'lehre', formatter: 'tickCross', hozAlign: 'center', widthGrow: 1, visible: false},
					{title: Vue.computed(() => this.$p.t('rauminfo/rauminfo')),
						field: 'linkInfo', formatter: this.linkFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('rauminfo/roomReservations')), 
						field: 'linkRes', formatter: this.linkFormatter, widthGrow: 1}
				],
				persistence: false,
			},
			raumsucheTableEventHandlers: [{
				event: "tableBuilt",
				handler: async () => {
					this.tableBuiltResolve()
				}
			}
			]};
	},
	computed: {
		isDarkMode(){
			return this.$theme.theme_name.value == 'dark';
		},
		// shown on the toggle, the panel can be closed while its filters are active
		activeAdvancedFilters() {
			return Object.keys(ADVANCED_DEFAULTS).filter(key => this.advancedFilters[key] !== ADVANCED_DEFAULTS[key]).length
		}
	},
	watch: {
		datum: 'loadRooms',
		von: 'loadRooms',
		bis: 'loadRooms',
		selectedType: 'loadRooms',
		standort_id: 'loadRooms',
		anzahl: 'loadRooms',
		advancedFilters: { handler: 'loadRooms', deep: true }
	},
	methods: {
		tableResolve(resolve) {
			this.tableBuiltResolve = resolve
		},
		linkFormatter(cell) {
			const val = cell.getValue();
			const field = cell.getField();
			const arialabel = (field === 'linkInfo') 
							? this.$p.t('rauminfo/rauminfo') 
							: this.$p.t('rauminfo/roomReservations');
			if(val) {
				return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">' +
				'<a href="'+val+'" aria-label="' + arialabel + '">' +
				'<i class="fa fa-arrow-up-right-from-square me-1 fhc-primary-color" ></i>' +
				'</a></div>'
			} else {
				return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">' +
					'-</div>'
			}
		},
		roomPlanLink(room) {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router
			+ '/CisVue/Cms/getRoomInformation/' + room.ort_kurzbz
		},
		roomInfoLink(room) {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router
				+ '/CisVue/Cms/content/' + room.content_id
		},
		getTimeString(time) {
			const hours = String(time.hours).padStart(2, '0');
			const minutes = String(time.minutes).padStart(2, '0');
			return `${hours}:${minutes}`
		},
		setupData(data){
			const d = data.map(room => {
				return {
					ort_kurzbz: room.ort_kurzbz,
					bezeichnung: room.bezeichnung.replace('&amp;', '&'),
					nummer: room.planbezeichnung,
					standort: room.standort,
					gebteil: room.gebteil,
					stockwerk: room.stockwerk,
					personen: room.max_person,
					m2: room.m2,
					lehre: room.lehre,
					linkInfo: room.content_id ? this.roomInfoLink(room) : null,
					linkRes: this.roomPlanLink(room)
					
				}
					
			})
			
			this.$refs.raumsucheTable.tabulator.setData(d);
		},
		loadRoomTypes() {
			this.$api.call(ApiOrt.getRoomTypes())
				.then(res => {
				res?.data?.forEach(type => {
					type.beschreibung = type.beschreibung.replace('&amp;', '&')
				})
				this.roomtypes = res?.data ?? []
			})
		},
		loadStandorte() {
			this.$api.call(ApiOrt.getStandorte())
				.then(res => {
					this.standorte = res?.data ?? []
				})
		},
		loadAdvancedFilterOptions() {
			this.$api.call(ApiOrt.getAdvancedFilterOptions())
				.then(res => {
					this.advancedFilterOptions = res?.data ?? {}
				})
		},
		loadRooms() {
			// only the latest request may fill the table, older answers can arrive later
			const request = ++this.roomsRequest
			this.loading = true
			// empty and unchecked filters stay out of the request
			const advancedFilters = Object.fromEntries(Object.entries(this.advancedFilters).filter(([, value]) => value !== '' && value !== false))
			this.$api.call(ApiOrt.getRooms(this.datum.toISOString(), this.getTimeString(this.von), this.getTimeString(this.bis), this.selectedType?.raumtyp_kurzbz ?? '', this.anzahl, this.standort_id, advancedFilters))
				.then(res => {
					if(request === this.roomsRequest && res?.data?.retval) this.setupData(res.data.retval)
				})
				.finally(() => {
					if(request === this.roomsRequest) this.loading = false
				})
		},
		handleUuidDefined(uuid) {
			this.tabulatorUuid = uuid
		},
		resetAdvancedFilters() {
			this.advancedFilters = { ...ADVANCED_DEFAULTS }
		},
		setRoute(val) {
			// TODO: router push
		},
		dateFormat(date) {
			const day = String(date.getDate()).padStart(2, '0');
			const month = String(date.getMonth() + 1).padStart(2, '0');
			const year = date.getFullYear();
			return `${day}.${month}.${year}`
		},
		timeFormat(date) {
			const hours = String(date.getHours()).padStart(2, '0');
			const minutes = String(date.getMinutes()).padStart(2, '0');
			return `${hours}:${minutes}`;
		},
		async setupMounted() {
			
			this.tableBuiltPromise = new Promise(this.tableResolve)
			await this.tableBuiltPromise
			
			this.loadRoomTypes()
			this.loadStandorte()
			this.loadAdvancedFilterOptions()
			this.loadRooms()

			const tableID = this.tabulatorUuid ? ('-' + this.tabulatorUuid) : ''
			const tableDataSet = document.getElementById('filterTableDataset' + tableID);
			if(!tableDataSet) return
			const rect = tableDataSet.getBoundingClientRect();

			const h = window.visualViewport.height - rect.top - 100
			if(this.$refs.raumsucheTable) {
				this.$refs.raumsucheTable.$refs.table.style.setProperty('height', h+'px')
			}
			
		}
	},
	created() {
		this.phrasenPromise = this.$p.loadCategory(['rauminfo', 'global', 'ui', 'filter'])
		this.phrasenPromise.then(()=> {this.phrasenResolved = true})
	},
	mounted() {
		this.setupMounted()
	},
	template: `
	<div class="d-flex align-items-center gap-2 mb-2">
		<h1 class="h3 mb-0">{{$p.t('rauminfo/roomSearch')}}</h1>
		<in-view-help button-class="fs-5 text-body-secondary" :text="$p.t('rauminfo/tooltipRaumsuchev2')"></in-view-help>
	</div>
	<hr>
	<div class="row">
		<div :class="{'pb-1': isMobile}" class="col-12 col-lg-2">
			<VueDatePicker
				@contextmenu="(e) => {if (isMobile) {e.preventDefault();}}"
				v-model="datum"
				:dark="isDarkMode"
				:clearable="false"
				:enable-time-picker="false"
				:format="dateFormat"
				:text-input="datepickerTextInputOptions"
				:min-date="new Date()"
				date-picker
				auto-apply
				>
			</VueDatePicker>
		</div>
		<div :class="{'pb-1': isMobile}" class="col-12 col-lg-1">
			<VueDatePicker
				@contextmenu="(e) => {if (isMobile) {e.preventDefault();}}"
				v-model="von"
				:dark="isDarkMode"
				:clearable="false"
				:format="timeFormat"
				:text-input="timepickerTextInputOptions"
				:is-24="true"
				time-picker
				auto-apply
				>
			</VueDatePicker>
		</div>
		<div :class="{'pb-1': isMobile}" class="col-12 col-lg-1">
			<VueDatePicker
				@contextmenu="(e) => {if (isMobile) {e.preventDefault();}}"
				v-model="bis"
				:dark="isDarkMode"
				:clearable="false"
				:format="timeFormat"
				:text-input="timepickerTextInputOptions"
				:is-24="true"
				time-picker
				auto-apply>
			</VueDatePicker>
		</div>
		
		<div :class="{'pb-1': isMobile}" class="col-12 col-lg-2">
			<select ref="raumtyp" id="raumtypSelect" v-model="selectedType" class="form-select" 
			:aria-label="$p.t('rauminfo/raumtyp')" @change="setRoute($event.target.value)">
				<option :value="null">{{ $p.t('global/alle') }}</option>
				<option v-for="typ in roomtypes" :key="typ" :value="typ">{{typ.beschreibung}}</option>
			</select>
		</div>

		<div :class="{'pb-1': isMobile}" class="col-12 col-lg-2">
			<select id="standortSelect" v-model="standort_id" class="form-select" :aria-label="$p.t('rauminfo/standort')">
				<option :value="null">{{ $p.t('rauminfo/alleStandorte') }}</option>
				<option value="none">{{ $p.t('rauminfo/ohneStandort') }}</option>
				<option v-for="standort in standorte" :key="standort.standort_id" :value="standort.standort_id">{{ standort.bezeichnung }}</option>
			</select>
		</div>
		

		<div :class="{'pb-2': isMobile}" class="col-12 col-lg">
			<InputNumber v-model="anzahl" 
			:prefix="$p.t('rauminfo/minCapacity') + ': '" 
			inputId="anzahlInput" :min="1" :max="1000" 
			:style="{'width': '100%'}"
			/>
		</div>
	</div>
	

	<div class="d-flex justify-content-end my-1">
		<button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-body-secondary"
			:aria-expanded="showAdvancedFilters" aria-controls="raumsucheAdvancedFilters"
			@click="showAdvancedFilters = !showAdvancedFilters">
			<i class="fa-solid fa-sliders me-1"></i>{{ $p.t('rauminfo/erweiterteFilter') }}
			<span v-if="activeAdvancedFilters" class="badge rounded-pill bg-primary ms-1">{{ activeAdvancedFilters }}</span>
			<i class="fa-solid ms-1" :class="showAdvancedFilters ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
		</button>
	</div>
	<div v-show="showAdvancedFilters" id="raumsucheAdvancedFilters" class="row g-2 mb-3">
		<div v-for="filter in advancedFilterFields" :key="filter.column" class="col-12 col-md-6 col-lg-3"
			:class="{'align-self-end': filter.type === 'flag' || filter.type === 'exists'}">
			<div v-if="filter.type === 'flag' || filter.type === 'exists'" class="form-check">
				<input :id="'raumsucheFilter-' + filter.column" v-model="advancedFilters[filter.column]" type="checkbox" class="form-check-input">
				<label :for="'raumsucheFilter-' + filter.column" class="form-check-label">{{ $p.t(filter.label) }}</label>
			</div>
			<template v-else>
				<label :for="'raumsucheFilter-' + filter.column" class="form-label small mb-1">{{ $p.t(filter.label) }}</label>
				<input v-if="filter.type === 'text'" :id="'raumsucheFilter-' + filter.column"
					v-model.lazy.trim="advancedFilters[filter.column]" type="text" class="form-control form-control-sm">
				<select v-else-if="filter.type === 'select'" :id="'raumsucheFilter-' + filter.column"
					v-model="advancedFilters[filter.column]" class="form-select form-select-sm">
					<option value="">{{ $p.t('ui/alle') }}</option>
					<option v-for="option in advancedFilterOptions[filter.column]" :key="option" :value="option">{{ option }}</option>
				</select>
				<div v-else class="input-group input-group-sm">
					<input :id="'raumsucheFilter-' + filter.column" v-model.lazy="advancedFilters[filter.column + '_min']"
						type="number" step="any" class="form-control"
						:placeholder="$p.t('ui/von')" :aria-label="$p.t(filter.label) + ' ' + $p.t('ui/von')">
					<input v-model.lazy="advancedFilters[filter.column + '_max']"
						type="number" step="any" class="form-control"
						:placeholder="$p.t('global/bis')" :aria-label="$p.t(filter.label) + ' ' + $p.t('global/bis')">
				</div>
			</template>
		</div>
		<div class="col-12 text-end">
			<button type="button" class="btn btn-link btn-sm p-0" :disabled="!activeAdvancedFilters" @click="resetAdvancedFilters">
				{{ $p.t('filter/filterDelete') }}
			</button>
		</div>
	</div>

	<div class="position-relative">
		<div :class="{'opacity-50': loading}">
			<core-filter-cmpt
				v-if="phrasenResolved"
				@uuidDefined="handleUuidDefined"
				:title="''"
				ref="raumsucheTable"
				:tabulator-options="raumsucheTableOptions"
				:tabulator-events="raumsucheTableEventHandlers"
				tableOnly
				:sideMenu="false"
			/>
		</div>
		<div v-if="loading" class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center">
			<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
		</div>
	</div>
    `,
};

export default Raumsuche;
