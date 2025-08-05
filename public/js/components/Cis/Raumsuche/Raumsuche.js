import {CoreFilterCmpt} from "../../../components/filter/Filter.js";
import VueDatePicker from '../../vueDatepicker.js.php';

export const Raumsuche =  {
	name: "Raumsuche",
	props: {
		
	},
	components: {
		VueDatePicker,
		CoreFilterCmpt,
		InputNumber: primevue.inputnumber,
	},
	data() {
		return {
			tabulatorUuid: Vue.ref(0),
			tableBuiltResolve: null,
			tableBuiltPromise: null,
			roomtypes: null,
			defaultType: {
				raumtyp_kurzbz: '',
				beschreibung: Vue.computed(() => this.$p.t('global/alle'))
			},
			anzahl: 1,
			selectedType: null,
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
					{title: Vue.computed(() => this.$p.t('global/nummer')), field: 'nummer', widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('global/personen')), field: 'personen', widthGrow: 1},
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
			},
			{
				event: "cellClick",
				handler: async (e, cell) => {

					if((cell.column.field === 'linkInfo' || cell.column.field === 'linkRes') && cell.value){
						window.open(cell.value, '_blank');
						e.stopPropagation();
					}
					
				}
			}
			]};
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
					personen: room.max_person,
					linkInfo: room.content_id ? this.roomInfoLink(room) : null,
					linkRes: this.roomPlanLink(room)
					
				}
					
			})
			
			this.$refs.raumsucheTable.tabulator.setData(d);
		},
		loadRoomTypes() {
			this.$fhcApi.factory.ort.getRoomTypes().then(res => {
				res?.data?.forEach(type => {
					type.beschreibung = type.beschreibung.replace('&amp;', '&')
				})
				this.selectedType = this.defaultType
				this.roomtypes = res?.data ?? []
			})
		},
		loadRooms() {
			this.$fhcApi.factory.ort.getRooms(this.datum.toISOString(), this.getTimeString(this.von), this.getTimeString(this.bis), this.selectedType?.raumtyp_kurzbz ?? '', this.anzahl)
				.then(res => {
					if(res?.data?.retval) this.setupData(res.data.retval)
			})
		},
		handleUuidDefined(uuid) {
			this.tabulatorUuid = uuid
		},
		search(){
			this.loadRooms()
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
	computed: {
		isDarkMode(){
			return this.$theme.theme_name.value == 'dark';
		}
	},
	created() {
		
	},
	mounted() {
		this.setupMounted()
	},
	template: `
	<h1 class="h3">{{$p.t('rauminfo/roomSearch')}}</h1>
	<hr>
	<div class="row">
		<div class="col-12 col-lg-2">
			<VueDatePicker
				:dark="isDarkMode"
				v-model="datum"
				:clearable="false"
				date-picker
				:enable-time-picker="false"
				:format="dateFormat"
				:text-input="datepickerTextInputOptions"
				:min-date="new Date()"
				auto-apply>
			</VueDatePicker>
		</div>
		<div class="col-12 col-lg-1">
			<VueDatePicker
				:dark="isDarkMode"
				v-model="von"
				:clearable="false"
				time-picker
				:format="timeFormat"
				:text-input="timepickerTextInputOptions"
				:is-24="true"
				auto-apply
				>
			</VueDatePicker>
		</div>
		<div class="col-12 col-lg-1">
			<VueDatePicker
				:dark="isDarkMode"
				v-model="bis"
				:clearable="false"
				time-picker
				:format="timeFormat"
				:text-input="timepickerTextInputOptions"
				:is-24="true"
				auto-apply>
			</VueDatePicker>
		</div>
		
		<div class="col-lg-auto">
			<select ref="raumtyp" id="raumtypSelect" v-model="selectedType" class="form-select" 
			:aria-label="$p.t('global/studiensemester_auswaehlen')" @change="setRoute($event.target.value)">
				<option :key="defaultType" selected :value="defaultType">{{defaultType.beschreibung}}</option>
				<option v-for="typ in roomtypes" :key="typ" :value="typ">{{typ.beschreibung}}</option>
			</select>
		</div>
		

		<div class="col-4 col-lg-2">
			<InputNumber v-model="anzahl" :prefix="$p.t('rauminfo/anzahlPersonen') + ': '" inputId="anzahlInput" :min="1" :max="100" />
		</div>
		<div class="col-8 col-lg-2 d-flex justify-content-center align-items-center">
			<button class="btn btn-primary border-0" @click="search">{{ $p.t('rauminfo/roomSearch') }} <i class="fa fa-magnifying-glass"></i></button>
		</div>
	</div>
	

     <core-filter-cmpt 
		@uuidDefined="handleUuidDefined"
		:title="''"  
		ref="raumsucheTable" 
		:tabulator-options="raumsucheTableOptions"  
		:tabulator-events="raumsucheTableEventHandlers"
		tableOnly 
		:sideMenu="false"
	 />
    `,
};

export default Raumsuche;
