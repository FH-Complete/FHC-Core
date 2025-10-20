/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
import VueDatePicker from '../vueDatepicker.js.php'
import CoreSearchbar from "../searchbar/searchbar.js";
import VerticalSplit from "../verticalsplit/verticalsplit.js";
import FhcCalendar from "../Calendar/Tempus.js";
import FhcCoursepicker from "../Tempus/Coursepicker.js";
import ApiKalender from '../../api/factory/kalender.js';
import ApiSearchbar from "../../api/factory/searchbar.js";
import ApiRenderers from '../../api/factory/renderers.js';

export default {
	name: "Tempus",
	components: {
		CoreSearchbar,
		VerticalSplit,
		FhcCalendar,
		FhcCoursepicker
	},
	props: {
		defaultSemester: String,
		config: Object,
		permissions: Object,
		tempusRoot: String,
		cisRoot: String,
		activeAddons: String, // semicolon separated list of active addons
		viewData: Object,
	},
	provide() {
		return {
			cisRoot: this.cisRoot,
			defaultSemester: this.defaultSemester,
			$reloadList: () => {
				this.$refs.stvList.reload();
			},
			renderers: Vue.computed(() => this.renderers),
		}
	},
	data() {
		return {
			selected: [],
			searchbaroptions: {
				origin: 'tempus',
				cssclass: "position-relative",
				calcheightonly: true,
				types: [
					//"student",
					"raum",
					//"mitarbeiter"
				],
				actions: {
					raum: {
						defaultaction: {
							type: "function",
							action: this.setOrt
						},
						childactions: [
						]
					}
				}
			},
			lv_id: null,
			events: null,
			minimized: false,
			calendarDate: luxon.DateTime.local(), //new CalendarDate(new Date()),
			currentlySelectedEvent: null,
			//currentDay: new Date(),
			studiensemesterKurzbz: this.defaultSemester,
			lists: {
				nations: [],
				sprachen: [],
				geschlechter: []
			},
			renderers: null,
			ort_kurzbz: 'EDV_A5.08',
		}
	},
	methods: {
		setOrt: function(data)
		{
			// Wenn bei der Suche ein Ort ausgewaehlt wird, dann wir der Ort gesetzt und ein Reload getriggert durch den watcher
			this.ort_kurzbz = data.ort_kurzbz;
		},
		handleChangeDate() {
		},
		handleChangeMode() {
		},
		searchfunction(params) {
			return this.$api.call(ApiSearchbar.search(params));
		},
		getPromiseFunc(start, end) {
			return [
				this.$api.call(ApiKalender.getRoomplan(this.ort_kurzbz, '2025-10-01','2025-10-30')),//start.toISODate(), end.toISODate())),
			];
		},
		parkingdrop: function(evt)
		{
			evt.preventDefault();
			var data = JSON.parse(evt.dataTransfer.getData("text"));
			alert('parked Data:'+data.id);
			console.log(data);
		},
		dropHandler: function(event, start, end)
		{
			let day = start.date.toFormat('yyyy-MM-dd');
			let time = start.date.toFormat('hh:mm');

			let dropdata = JSON.parse(event.dataTransfer.getData('text'))

			if(dropdata.type=='kalender')
			{
				let kalender_id = dropdata.id;

				Promise.allSettled([
					this.$api.call(ApiKalender.updateKalenderEvent(kalender_id, this.ort_kurzbz, day+' '+time, null))
				]).then((result) => {
					let promise_events = [];
					result.forEach((promise_result) => {
						if (promise_result.status === 'fulfilled' && promise_result.value.meta.status === "success")
						{
							// TODO - reload
						}
					})
				});
			}
			else if(dropdata.type=='lehreinheit')
			{
				// TODO Calculate end time
				let lehreinheit_id = dropdata.id;
				let start_time =  day+' '+time;
				let end_time = start.date.plus({ minutes: 45 }).toFormat('yyyy-MM-dd hh:mm');
				alert("mode:"+dropdata.mode);

				Promise.allSettled([
					this.$api.call(ApiKalender.addKalenderEvent(lehreinheit_id, this.ort_kurzbz, start_time, end_time))
				]).then((result) => {
					let promise_events = [];
					result.forEach((promise_result) => {
						if (promise_result.status === 'fulfilled' && promise_result.value.meta.status === "success") {

							// TODO - reload
						}
					})
				});
			}
			else
			{
				alert("Unbekannte Daten gedroppt");
			}
		},
		onRightClick: function(evt) {
			this.$refs.EventContextMenu.show(evt);
		}
	},
	watch: {
	  ort_kurzbz: function (newValue, oldValue) {
		  // Raumansicht laden wenn der Ort geaendert wird
	  }
	},
	computed: {
		currentDay() {
			return luxon.DateTime.now().setZone(this.config.timezone).toISODate();
		},
		currentMode() {
			return 'week';
		},
	},
	async created()
	{
		await this.$api
			.call(ApiRenderers.loadRenderers())
			.then(res => res.data)
			.then(data => {
				for (let rendertype of Object.keys(data)) {
					let modalTitle = null;
					let modalContent = null;
					let calendarEvent = null;
					if (data[rendertype].modalTitle)
						modalTitle = Vue.markRaw(Vue.defineAsyncComponent(() => import(data[rendertype].modalTitle)));
					if (data[rendertype].modalContent)
						modalContent = Vue.markRaw(Vue.defineAsyncComponent(() => import(data[rendertype].modalContent)));
					if (data[rendertype].calendarEvent)
						calendarEvent = Vue.markRaw(Vue.defineAsyncComponent(() => import(data[rendertype].calendarEvent)));

					if (data[rendertype].calendarEventStyles){
						var head = document.head;
						if(!head.querySelector(`link[href="${data[rendertype].calendarEventStyles}"]`)){
							var link = document.createElement("link");
							link.type = "text/css";
							link.rel = "stylesheet";
							link.href = data[rendertype].calendarEventStyles;
							head.appendChild(link);
						}
					}

					if(this.renderers === null) {
						this.renderers = {};
					}
					if (!this.renderers[rendertype]) {
						this.renderers[rendertype] = {}
					}
					this.renderers[rendertype].modalTitle = modalTitle;
					this.renderers[rendertype].modalContent = modalContent;
					this.renderers[rendertype].calendarEvent = calendarEvent;
				}
			});
	},
	mounted() {


	},
	template: `
	<div class="tempus">
		<header class="navbar navbar-expand-lg navbar-dark bg-dark flex-md-nowrap p-0 shadow">
			<a class="navbar-brand col-md-4 col-lg-3 col-xl-2 me-0 px-3">Tempus</a>
			<div class="collapse navbar-collapse" id="navbarSupportedContent">
			<ul class="navbar-nav me-auto mb-2 mb-lg-0">
				<li class="nav-item">
					<a class="nav-link" href="#">Config</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="#">Issues</a>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
						Reports
					</a>
					<ul class="dropdown-menu">
						<li><a class="dropdown-item" href="#">AZG Verletzungen</a></li>
						<li><a class="dropdown-item" href="#">Raumauslastung</a></li>
						<li><hr class="dropdown-divider"></li>
						<li><a class="dropdown-item" href="#">LektorInnenliste</a></li>
					</ul>
				</li>
			</ul>
			</div>
			<core-searchbar :searchoptions="searchbaroptions" :searchfunction=searchfunction class="searchbar w-100"></core-searchbar>
		</header>
		<div class="container-fluid overflow-hidden heightfull">
			<div class="row h-100">
				<nav id="sidebarMenu" class="bg-light offcanvas offcanvas-start col-md p-md-0 h-100">
					<div class="offcanvas-header justify-content-end px-1 d-md-none">
						<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" :aria-label="$p.t('ui/schliessen')"></button>
					</div>
					<div style="float: left">
						<fhc-coursepicker></fhc-coursepicker>
						<div id="parkinglot" ondragover="event.preventDefault();" @drop="parkingdrop">
							<br />
							<i class="fa-solid fa-square-parking"></i><br />
							<span>Drag here to park</span>
						</div>
						<br />
						Raum <input type="text" v-model="ort_kurzbz">
					</div>
				</nav>

				<main class="col-md-8 ms-sm-auto col-lg-9 col-xl-10">
				<fhc-calendar
					ref="calendar"
					:timezone="config.timezone"
					:get-promise-func="getPromiseFunc"
					:date="currentDay"
					:mode="currentMode"
					@drop="dropHandler"
					@update:date="handleChangeDate"
					@update:mode="handleChangeMode"
					class="responsive-calendar"
				/>
				</main>
			</div>
		</div>
	</div>`
};
