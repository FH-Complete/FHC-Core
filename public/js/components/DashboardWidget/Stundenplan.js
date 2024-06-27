import Phrasen from '../../mixins/Phrasen.js';
import AbstractWidget from './Abstract.js';
import FhcCalendar from '../Calendar/Calendar.js';
import LvUebersicht from '../Cis/Mylv/LvUebersicht.js';
import ContentModal from '../Cis/Cms/ContentModal.js';

export default {
	mixins: [
		Phrasen,
		AbstractWidget
	],
	components: {
		FhcCalendar,
		LvUebersicht,
		ContentModal,
	},
	
	data() {
		return {
			stunden: [],
			minimized: true,
			events: null,
			currentDay: new Date(),
			// used for contentModal
			roomInfoContentID: null,
			ort_kurzbz: null,
			// used for LvUbersichtModal
			selectedEvent: null,
		}
	},
	computed: {
		
		currentEvents() {
			return (this.events || []).filter(evt => evt.end < this.dayAfterCurrentDay && evt.start >= this.currentDay);
		},
		dayAfterCurrentDay() {
			let currentDay = new Date(this.currentDay);
			currentDay.setDate(currentDay.getDate() + 1);
			return currentDay;
		}
	},
	methods: {
		
		showRoomInfoModal: function(ort_kurzbz){

			// getting the content_id of the ort_kurzbz
			this.$fhcApi.factory.ort.getContentID(ort_kurzbz).then(res =>{
				this.roomInfoContentID = res.data;
				this.ort_kurzbz = ort_kurzbz;

				// only showing the modal after vue was able to set the reactive data
				Vue.nextTick(()=>{this.$refs.contentModal.show();});
				
				
			}).catch(err =>{
				console.err(err);
				this.ort_kurzbz = null;
				this.roomInfoContentID = null;
			});
			
		},
		showLvUebersicht: function (event){
			this.selectedEvent= event;
			Vue.nextTick(()=>{this.$refs.lvUebersicht.show();});
			
		},
		
		selectDay(day) {
			this.currentDay = day;
			this.minimized = true;
		},
		// this function was the alternative of showing the room information in the content component instead of showing the room information inside a modal
		showRoomInfo: function($ort_kurzbz){
			
			this.$fhcApi.factory.ort.getContentID($ort_kurzbz).then(res =>{

				window.location.href = FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				"/CisHtml/Cms/content/" + res.data;
			
			})
		}

	},
	created() {

		
		
		this.$emit('setConfig', false);
		axios
			.get(this.apiurl + '/components/Cis/Stundenplan/Stunden').then(res => {
				res.data.retval.forEach(std => {
					this.stunden[std.stunde] = std; // TODO(chris): geht besser
				});
				axios
					.get(this.apiurl + '/components/Cis/Stundenplan')
					.then(res => {
						res.data.retval.forEach((el, i) => {
							el.id = i;
							el.color = '#' + (el.farbe || 'CCCCCC');
							el.start = new Date(el.datum + ' ' + this.stunden[el.stunde].beginn);
							el.end = new Date(el.datum + ' ' + this.stunden[el.stunde].ende);
							el.title = el.lehrfach;
							if (el.lehrform)
								el.title += '-' + el.lehrform;
						});
						this.events = res.data.retval || [];
					})
					.catch(err => { console.log(err);console.error('ERROR: ', err.response.data) });
			})
			.catch(err => { console.error('ERROR: ', err.response.data) });
	},
	template: /*html*/`
	<div class="dashboard-widget-stundenplan d-flex flex-column h-100">
		<lv-uebersicht ref="lvUebersicht" :event="selectedEvent"  />
		<content-modal :contentID="roomInfoContentID" :ort_kurzbz="" dialogClass="modal-lg" ref="contentModal"/>
		<fhc-calendar :initial-date="currentDay" class="border-0" class-header="p-0" @select:day="selectDay" v-model:minimized="minimized" :events="events" no-week-view :show-weeks="false" />
		<div v-show="minimized" class="flex-grow-1 overflow-scroll">
			<div v-if="events === null" class="d-flex h-100 justify-content-center align-items-center">
				<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
			</div>
			<div v-else-if="currentEvents.length" class="list-group list-group-flush">
				<div role="button" @click="showLvUebersicht(evt)" class="" v-for="evt in currentEvents" :key="evt.id" class="list-group-item small" :style="{'background-color':evt.color}">
					<b>{{evt.title}}</b>
					<br>
					<small class="d-flex w-100 justify-content-between">
						<!-- event modifier stop to prevent opening the modal for the lv Uebersicht when clicking on the ort_kurzbz -->
						<!-- old event: showRoomInfo(evt.ort_kurzbz) -->
						<span @click.stop="showRoomInfoModal(evt.ort_kurzbz)" style="text-decoration:underline" type="button">{{evt.ort_kurzbz}}</span>
						<span>{{evt.start.toLocaleTimeString(undefined, {hour:'numeric',minute:'numeric'})}}-{{evt.end.toLocaleTimeString(undefined, {hour:'numeric',minute:'numeric'})}}</span>
					</small>
				</div>
			</div>
			<div v-else class="d-flex h-100 justify-content-center align-items-center fst-italic text-center">
				{{ p.t('lehre/noLvFound') }}
			</div>
		</div>
	</div>`
}