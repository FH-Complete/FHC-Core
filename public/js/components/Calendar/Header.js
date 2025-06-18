export default {
	name: 'Fhc-Calendar-Header',
	components: {
		vuedatepicker: VueDatePicker
	},
	data(){
		return{
			selected: this.mode,
			modes:{
				day: { mode_bezeichnung: "day", icon: "fa-calendar-day" , condition:true}, 
				week: { mode_bezeichnung: "week", icon: "fa-calendar-week", condition: !this.noWeekView }, 
				month: { mode_bezeichnung: "month", icon: "fa-calendar-days", condition: !this.noMonthView }, 
			},
			headerPadding:null,
			selectedDate: null
		}
	},
	inject: [
		'eventsAreNull',
		'size',
		'mode',
		'noWeekView',
		'noMonthView',
		'containerWidth'
	],
	props: {
		title: String
	},
	emits: [
		'updateMode',
		'prev',
		'next',
		'click',
		'updateSelectedDate'
	],
	computed: {
		getHeaderClassSide() {
			return this.containerWidth > 780 ? 'col-3' : 'col-12'
		},
		getHeaderClassMiddle() {
			return this.containerWidth > 780 ? 'col-6' : 'col-12'
		}
	},
	methods: {
		selectedDateChanged: function() {
			console.log('selectedDateChanged: ' + this.selectedDate);
			let isodatestr = '';
			if( Array.isArray(this.selectedDate)) {
				let tmpdate = this.selectedDate[0];
				isodatestr = tmpdate.getFullYear() + "-" +
				String(tmpdate.getMonth() + 1).padStart(2, "0") + "-" +
				String(tmpdate.getDate()).padStart(2, "0");
			} else {
				isodatestr = this.selectedDate;
			}
			this.$emit('updateSelectedDate', isodatestr);
		}
	},
	template: /*html*/`
	<div class="calendar-header card-header w-100">
		<div class="row align-items-center ">
			<div :class="getHeaderClassSide" class="d-flex justify-content-center justify-content-md-start align-items-center">
				<slot name="calendarDownloads"></slot>
			</div>
			<div :class="getHeaderClassMiddle" :style="{'padding-left':headerPadding}">

				<div class="fhc-calendar-datepicker row align-items-center justify-content-center">
					<div style="max-width: 180px;">
					<vuedatepicker
						v-if="selected === 'month'"
						v-model="selectedDate"
						:month-picker="true"
						:action-row="{ showSelect: false, showCancel: false, showNow: false, showPreview: false }"
						:config="{keepActionRow: true}"
						:enable-time-picker="false"
						:teleport="true"
						:clearable="false"
						six-weeks
						auto-apply 
						text-input 
						locale="de"
						format="MMMM yyyy"
						model-type="yyyy-MM-dd"
						@update:model-value="selectedDateChanged"
					></vuedatepicker>
		
					<vuedatepicker
						v-else-if="selected === 'week'"
						v-model="selectedDate"
						:week-picker="true"
						:week-numbers="{ type: 'iso' }"
						:action-row="{ showSelect: false, showCancel: false, showNow: true, showPreview: false }"
						:config="{keepActionRow: true}"
						:enable-time-picker="false"
						:teleport="true"
						:clearable="false"
						six-weeks
						auto-apply 
						text-input 
						locale="de"
						format="yyyy 'KW' ww"
						model-type="yyyy-MM-dd"
						@update:model-value="selectedDateChanged"
					></vuedatepicker>
		
					<vuedatepicker
						v-else=""
						v-model="selectedDate"
						:enable-time-picker="false"
						:teleport="true"
						:week-numbers="{ type: 'iso' }"
						:action-row="{ showSelect: false, showCancel: false, showNow: true, showPreview: false }"
						:config="{keepActionRow: true}"
						:clearable="false"
						six-weeks
						auto-apply 
						text-input 
						locale="de"
						format="dd.MM.yyyy"
						model-type="yyyy-MM-dd"
						@update:model-value="selectedDateChanged"
					></vuedatepicker>
					</div>
				</div>

<!--
				<div class="row align-items-center justify-content-center">
					<div class="col-auto p-2">
						<button class="btn btn-outline-secondary border-0" :class="{'btn-sm':!this.size}" @click="$emit('prev')"><i class="fa fa-chevron-left"></i></button>
					</div>
					<div class="justify-content-center text-center col-auto">
						<div class="d-flex justify-content-center align-items-center">
							<button class="btn btn-link link-secondary text-decoration-none" :class="{'btn-sm': !this.size}" @click="$emit('click')">
								{{ title }}
								<i v-if="eventsAreNull" class="fa fa-spinner fa-pulse"></i>
							</button>
						</div>
					</div>
					<div class="col-auto p-2">
						<button class="btn btn-outline-secondary border-0" :class="{'btn-sm': !this.size}" @click="$emit('next')"><i class="fa fa-chevron-right"></i></button>
					</div>
				</div>
-->
			</div>
			<div ref="viewButtons" v-if="!noWeekView && !noMonthView" :class="getHeaderClassSide" class="d-flex justify-content-center justify-content-md-end align-items-center" style="pointer-events: none;">
				<div  style="pointer-events: all;">
					<div  role="group" aria-label="Kalender Modus">
						<button type="button" :class="{'active':mode_kurzbz.toLowerCase() === mode.toLowerCase()}" style="margin-right: 4px;" @click.prevent="$emit('updateMode',mode_kurzbz)" class="btn btn-outline-secondary" v-for="({mode_bezeichnung,icon,condition},mode_kurzbz) in modes">
							<i v-if="condition" class="fa" :class="icon" ></i>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>`
}