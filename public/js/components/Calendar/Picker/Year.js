import BaseSlider from '../Base/Slider.js';

import CalendarDate from '../../../helpers/Calendar/Date.js';

/**
 * TODO(chris): use timestamps instead of dates?
 */

export default {
	name: "PickerYear",
	components: {
		BaseSlider
	},
	inject: {
		title: "title"
	},
	props: {
		currentDate: Date
	},
	emits: [
		"update:currentDate"
	],
	data() {
		return {
			range: 24,
			focusDate: new Date(this.currentDate),
			clicked: ''
		};
	},
	computed: {
		start() {
			const y = this.focusDate.getFullYear();
			return Math.floor(y/this.range) * this.range;
		},
		end() {
			return this.start + this.range - 1;
		},
		years() {
			return [...Array(this.end - this.start + 1).keys()].map(i => i + this.start);
		},
		currentYear() {
			return this.currentDate.getFullYear();
		}
	},
	methods: {
		prevPage() {
			this.$refs.slider.prevPage();
		},
		nextPage() {
			this.$refs.slider.nextPage();
		},
		updatePage(dir) {
			this.focusDate = CalendarDate.addYears(this.currentDate, this.focusDate.getFullYear() - this.currentDate.getFullYear() + this.range * dir);
		},
		setYear(year) {
			this.clicked = 'clicked';
			
			this.focusDate = CalendarDate.addYears(this.focusDate, year - this.focusDate.getFullYear());
			
			this.$nextTick(
				() => this.$emit('update:currentDate', this.focusDate)
			);
		}
	},
	created() {
		this.title = Vue.computed(() => this.start + ' - ' + this.end);
	},
	beforeUnmount() {
		this.title = null;
	},
	template: `
	<div class="fhc-calendar-picker-year" :class="clicked">
		<base-slider ref="slider" v-slot="slot" @slid="updatePage">
			<div class="d-flex flex-wrap h-100">
				<div
					v-for="year in years"
					:key="year"
					class="d-grid col-2"
				>
					<button
						@click="setYear(year + range * slot.offset)"
						class="btn btn-outline-secondary m-2"
						:class="{'border-0': year + range * slot.offset != currentYear}"
					>
						{{ year + range * slot.offset }}
					</button>
				</div>
			</div>
		</base-slider>
	</div>
	`
}
