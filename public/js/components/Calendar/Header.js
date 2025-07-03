export default {
	data(){
		return{
			selected: this.mode,
			modes:{
				day: { mode_bezeichnung: "day", icon: "fa-calendar-day" , condition:true}, 
				week: { mode_bezeichnung: "week", icon: "fa-calendar-week", condition: !this.noWeekView }, 
				month: { mode_bezeichnung: "month", icon: "fa-calendar-days", condition: !this.noMonthView }, 
			},
			headerPadding:null,
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
		'click'
	],
	methods:{
		modeAriaLabelText(mode) {
			switch (mode.toLowerCase()) {
				case "day": return this.$p.t('LvPlan', 'modeDay');
				case "week": return this.$p.t('LvPlan', 'modeWeek');
				case "month": return this.$p.t('LvPlan', 'modeMonth');
			}
		},
	},
	computed: {
		getHeaderClassSide() {
			return this.containerWidth > 780 ? 'col-3' : 'col-12'
		},
		getHeaderClassMiddle() {
			return this.containerWidth > 780 ? 'col-6' : 'col-12'
		},
		previousButtonAriaLabelText(){
			switch(this.mode.toLowerCase()){
				case "day": return this.$p.t('LvPlan', 'previousDay');
				case "week": return this.$p.t('LvPlan', 'previousWeek');
				case "weeks": return this.$p.t('LvPlan', 'previousYear');
				case "month": return this.$p.t('LvPlan', 'previousMonth');
			}
		},
		nextButtonAriaLabelText() {
			switch (this.mode.toLowerCase()) {
				case "day": return this.$p.t('LvPlan', 'nextDay');
				case "week": return this.$p.t('LvPlan', 'nextWeek');
				case "weeks": return this.$p.t('LvPlan', 'nextYear');
				case "month": return this.$p.t('LvPlan', 'nextMonth');
			}
		},
		
	},
	template: /*html*/`
	<div class="calendar-header card-header w-100">
		<div class="row align-items-center ">
			<div :class="getHeaderClassSide" class="d-flex justify-content-center justify-content-md-start align-items-center">
				<slot name="calendarDownloads"></slot>
			</div>
			<div :class="getHeaderClassMiddle" :style="{'padding-left':headerPadding}">
				<div class="row align-items-center justify-content-center">
					<div class="col-auto p-2">
						<button class="btn btn-outline-secondary border-0" :class="{'btn-sm':!this.size}" @click="$emit('prev')" v-tooltip.top="{ value: previousButtonAriaLabelText, showDelay: 1000}" :aria-label="previousButtonAriaLabelText"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
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
						<button class="btn btn-outline-secondary border-0" :class="{'btn-sm': !this.size}" @click="$emit('next')" v-tooltip.top="{value:nextButtonAriaLabelText, showDelay:1000}" :aria-label="previousButtonAriaLabelText"><i class="fa fa-chevron-right" aria-hide="true"></i></button>
					</div>
				</div>
			</div>
			<div ref="viewButtons" v-if="!noWeekView && !noMonthView" :class="getHeaderClassSide" class="d-flex justify-content-center justify-content-md-end align-items-center" style="pointer-events: none;">
				<div  style="pointer-events: all;">
					<div  role="group" aria-label="Kalender Modus">
						<button :aria-label="modeAriaLabelText(mode_bezeichnung)" v-tooltip.top="{value:modeAriaLabelText(mode_bezeichnung), showDelay:1000}" type="button" :class="{'active':mode_kurzbz.toLowerCase() === mode.toLowerCase()}" style="margin-right: 4px;" @click.prevent="$emit('updateMode',mode_kurzbz)" class="btn btn-outline-secondary" v-for="({mode_bezeichnung,icon,condition},mode_kurzbz) in modes">
							<i aria-hidden="true" v-if="condition" class="fa" :class="icon" ></i>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>`
}