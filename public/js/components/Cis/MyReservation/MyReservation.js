import {CoreFilterCmpt} from "../../../components/filter/Filter.js";
import VueDatePicker from '../../vueDatepicker.js.php';
import ApiReservierung from '../../../api/factory/reservierung.js'
import { formatISODate } from "../Abgabetool/dateUtils.js";

export const MyReservation =  {
	name: "MyReservation",
	components: {
		VueDatePicker,
		CoreFilterCmpt
	},
	inject: ["isMobile"],
	data() {
		return {
			phrasenPromise: null,
			phrasenResolved: false,
			tabulatorUuid: Vue.ref(0),
			tableBuiltResolve: null,
			tableBuiltPromise: null,
			myReservationTableOptions: {
				minHeight: 250,
				index: 'reservierung_id',
				layout: 'fitColumns',
				placeholder: this.$p.t('global/noDataAvailable'),
				columns: [
					{title: Vue.computed(() => this.$capitalize(this.$p.t('global/datum'))), field: 'datum', widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('global/titel'))), field: 'titel', widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('ui/stunde'))), field: 'stunde', widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('global/raum'))), field: 'ort_kurzbz', widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('reservierung/reserviert_fuer'))), field: 'reserviert_fuer', widthGrow: 2},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('global/beschreibung'))), field: 'beschreibung', widthGrow: 2},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('reservierung/reserviert_von'))), field: 'reserviert_von',  widthGrow: 2},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('global/actions'))), field: 'aktion', formatter: this.formAction, widthGrow: 1}
				],
				persistence: false,
			},
			myReservationTableEventHandlers: [{
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
		generalPresets: function() {
			return [
				{
					"id": null,
					"name": "Standard",
					"displayedColumns": [
						"datum","titel","stunde","ort_kurzbz","reserviert_fuer",
						"reserviert_fuer", "reserviert_von", "aktion"
					],
					"headerFilters": [],
					"sort": null
				}
			];
		},
	},
	methods: {
		formAction(cell) {
			const actionButtons = document.createElement('div');
			actionButtons.className = "d-flex gap-3";
			actionButtons.style.display = "flex";
			actionButtons.style.alignItems = "stretch";
			actionButtons.style.justifyContent = "start";
			actionButtons.style.height = "100%";

			const data = cell.getRow().getData();

			const createButton = (iconClass, titleKey, clickHandler) => {
				const btn = document.createElement('button');
				btn.className = 'btn btn-outline-secondary';
				btn.style.display = "flex";
				btn.style.alignItems = "center"; // center icon vertically
				btn.style.justifyContent = "center"; // center icon horizontally
				btn.style.height = "100%"; // fill parent container height
				btn.style.aspectRatio = "1 / 1"; // keep square shape (optional)
				btn.style.padding = "0"; // remove extra padding for compactness
				if(iconClass == 'fa fa-timeline') btn.style.transform = "rotate(90deg)";
				btn.innerHTML = `<i class="${iconClass}" style="color:#00649C; font-size:1.1rem;"></i>`;
				btn.title = this.$capitalize(this.$p.t(titleKey));
				btn.addEventListener('click', (e) => {
					e.stopPropagation();
					e.stopImmediatePropagation();
					clickHandler();
				});
				return btn;
			};

			// TODO: only show/enable this button if user has berechtigung 'lehre/reservierung:begrenzt'
			
			actionButtons.append(
				createButton('fa fa-trash-can', 'global/delete', () => this.deleteReservation(data)),
			);

			return actionButtons;
		},
		deleteReservation(data) {
			this.$api.call(ApiReservierung.deleteReservation(data.reservierung_id))
				.then(res => {
					if(res?.meta.status === 'success') {
						this.$refs.myReservationTable.tabulator.deleteRow(data.reservierung_id);
						this.$fhcAlert.alertSuccess(this.$p.t('reservierung/reservierungWurdeGeloescht'));
					}
				})
		},
		tableResolve(resolve) {
			this.tableBuiltResolve = resolve
		},
		setupData(data){
			const d = data.map(reservation => {
				return {
					...reservation, // spread first, then format date
					datum: formatISODate(reservation.datum),
					
				}

			})

			this.$refs.myReservationTable.tabulator.setData(d);
		},
		handleUuidDefined(uuid) {
			this.tabulatorUuid = uuid
		},
		loadMyReservation() {
			this.$api.call(ApiReservierung.getMyReservation())
				.then(res => {
					if(res?.data) this.setupData(res.data)
				})
		},
		async setupMounted() {

			this.tableBuiltPromise = new Promise(this.tableResolve)
			await this.tableBuiltPromise

			
			
			this.loadMyReservation()
			// this.loadRooms()
			//
			// const tableID = this.tabulatorUuid ? ('-' + this.tabulatorUuid) : ''
			// const tableDataSet = document.getElementById('filterTableDataset' + tableID);
			// if(!tableDataSet) return
			// const rect = tableDataSet.getBoundingClientRect();
			//
			// const h = window.visualViewport.height - rect.top - 100
			// if(this.$refs.raumsucheTable) {
			// 	this.$refs.raumsucheTable.$refs.table.style.setProperty('height', h+'px')
			// }

		}
	},
	created() {
		this.phrasenPromise = this.$p.loadCategory(['reservierung', 'global', 'ui'])
		this.phrasenPromise.then(()=> {this.phrasenResolved = true})
	},
	mounted() {
		this.setupMounted()
	},
	template: `
	<h1 class="h3">{{$p.t('reservierung/reservierungsliste')}}</h1>
	<hr>

     <core-filter-cmpt 
     	v-if="phrasenResolved"
     	:isUsingPresets="true"
		presetsId="myReservationTable"
		@uuidDefined="handleUuidDefined"
		:title="''"
		ref="myReservationTable" 
		:tabulator-options="myReservationTableOptions"  
		:tabulator-events="myReservationTableEventHandlers"
		tableOnly 
		:sideMenu="false"
	 />
    `,
};

export default MyReservation;
