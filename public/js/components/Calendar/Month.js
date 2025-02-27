import CalendarAbstract from './Abstract.js';
import CalendarPane from './Pane.js';
import CalendarMonthPage from './Month/Page.js';
import BsModal from "../Bootstrap/Modal.js";
import Months from "./Months";
export default {
	mixins: [
		CalendarAbstract
	],
	components: {
		CalendarMonthPage,
		CalendarPane,
		BsModal,
		Months
	},
	data() {
		return {
			syncOnNextChange: false
		}
	},
	computed: {
		title() {
			return this.focusDate.format({month: ['short','long','long','long'][this.size], year: 'numeric'}, this.$p.user_locale.value);
		}
	},
	methods: {
		handleMonthChanged(month) {
			this.$emit('change:offset', { y: 0, m: month - this.focusDate.m, d: 0 });
			this.$refs.modalDatepickerContainer.hide()
		},
		hideMonthsModal() {
			this.$refs.modalDatepickerContainer.hide()
		},
		handleHeaderClickMonth() {
			this.$refs.modalDatepickerContainer.show()
		},
		paneChanged(dir) {
			if (this.syncOnNextChange) {
				this.syncOnNextChange = false;
				this.focusDate.set(this.date);
			} else {
				this.focusDate.moveMonthInDirection(dir)
			}
			this.emitRangeChanged()
		},
		emitRangeChanged(mounted = false) {
			this.$emit('change:range', {
				start: new Date(this.focusDate.y, this.focusDate.m, 1),
				end: new Date(this.focusDate.y, this.focusDate.m+1, 0),
				mounted
			});
		},
		prev() {
			this.$refs.pane.prev();
			this.$emit('change:offset', { y: 0, m: -1, d: 0 });
		},
		next() {
			this.$refs.pane.next();
			this.$emit('change:offset', { y: 0, m: 1, d: 0 });
		},
		selectDay(day) {
			let m = day.getMonth();
			if (this.focusDate.m != m) {
				this.syncOnNextChange = true;
				if (this.focusDate.m-1 == m || (m == 11 && !this.focusDate.m))
					this.$refs.pane.prev();
				else
					this.$refs.pane.next();
			} else {
				this.focusDate.set(this.date);
			}
			this.$emit('input', ['select:day',day])
		}
	},
	mounted() {
		this.emitRangeChanged(true)
	},
	template: `
	<div class="fhc-calendar-month">
		<calendar-header :title="title" @prev="prev" @next="next" @updateMode="$emit('updateMode', $event)" @click="handleHeaderClickMonth">
			<template #calendarDownloads>
				<slot name="calendarDownloads"></slot>
			</template>
		</calendar-header>
		<calendar-pane ref="pane" v-slot="slot" @slid="paneChanged">
			<calendar-month-page :year="focusDate.y" :month="focusDate.m+slot.offset" @updateMode="$emit('updateMode', $event)" @page:back="prev" @page:forward="next" @input="selectDay" >
				<template #monthPage="{event,day}">
					<slot name="monthPage" :event="event" :day="day" ></slot>
				</template>
			</calendar-month-page>
		</calendar-pane>
	</div>

	<bs-modal ref="modalDatepickerContainer" dialogClass='modal-lg' class="bootstrap-prompt">
		<template v-slot:default>
			<months @change="handleMonthChanged"></months>
		</template>
	</bs-modal>
`
}
