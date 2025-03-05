import CalendarAbstract from './Abstract.js';
import CalendarPane from './Pane.js';
import CalendarWeekPage from './Week/Page.js';
import BsModal from "../Bootstrap/Modal.js";
import Weeks from "./Weeks.js";

export default {
	mixins: [
		CalendarAbstract
	],
	components: {
		CalendarWeekPage,
		CalendarPane,
		BsModal,
		Weeks
	},
	emits: [
		"change:offset"
	],
	computed: {
		title() {
			return this.focusDate.wYear + ' KW ' + this.focusDate.w;
		}
	},
	methods: {
		handleWeekChanged(week) {
			// this.$emit('change:offset', { y: 0, m: month - this.focusDate.m, d: 0 });
			this.$refs.modalDatepickerContainer.hide()
		},
		hideMonthsModal() {
			this.$refs.modalDatepickerContainer.hide()
		},
		handleHeaderClickWeek() {
			this.$emit('updateMode', 'weeks');//
			//this.$refs.modalDatepickerContainer.show()
		},
		paneChanged(dir) {
			this.focusDate.d += dir * 7;
			this.emitRangeChanged();
		},
		emitRangeChanged(mounted = false) {
			let start = this.focusDate.firstDayOfWeek;
			let end = this.focusDate.lastDayOfWeek;
			this.$emit('change:range', { start, end, mounted });
		},
		prev() {
			this.$refs.pane.prev();
			this.$emit('change:offset', { y: 0, m: 0, d: -7 });
		},
		next() {
			this.$refs.pane.next();
			this.$emit('change:offset', { y: 0, m: 0, d: 7 });
		},
		selectEvent(event) {
			this.$emit('input', ['select:event',event]);
		}
	},
	mounted() {
		this.emitRangeChanged(true);
	},
	template: /*html*/`
	<div class="fhc-calendar-week">
		<calendar-header :title="title" @prev="prev" @next="next" @updateMode="$emit('updateMode', $event)" @click="handleHeaderClickWeek">
			<template #calendarDownloads>
				<slot name="calendarDownloads"></slot>
			</template>
		</calendar-header>
		<calendar-pane ref="pane" v-slot="slot" @slid="paneChanged">
			<calendar-week-page :active="slot.active" :year="focusDate.wYear" :week="focusDate.w+slot.offset" @updateMode="$emit('updateMode', $event)" @page:back="prev" @page:forward="next" @input="selectEvent" >
				<template #weekPage="{event,day}">
					<slot name="weekPage" :event="event" :day="day" ></slot>
				</template>
			</calendar-week-page>
		</calendar-pane>
	</div>

	<bs-modal ref="modalDatepickerContainer" dialogClass='modal-lg' class="bootstrap-prompt">
		<template v-slot:default>
			<weeks :header="false" @change="handleWeekChanged"></weeks>
		</template>
	</bs-modal>
`
}
