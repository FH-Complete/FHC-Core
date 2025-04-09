import PickerYear from '../Picker/Year.js';
import BaseSlider from '../Base/Slider.js';

import CalendarDate from '../../../helpers/Calendar/Date.js';

/**
 * TODO(chris): use timestamps instead of dates?
 */

export default {
	name: "PickerWeek",
	components: {
		PickerYear,
		BaseSlider
	},
	inject: {
		locale: "locale",
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
			yearPicker: false,
			focusDate: new Date(this.currentDate),
			clicked: ''
		};
	},
	computed: {
		weeks() {
			const maxWeeks = CalendarDate.countWeeksOfYear(this.focusYear, this.locale);
			const weeknumbers = [...Array(maxWeeks+1).keys()];
			weeknumbers.shift();
			return weeknumbers;
		},
		currentWeek() {
			return CalendarDate.getWeek(this.currentDate, this.locale).number;
		},
		currentYear() {
			return CalendarDate.getWeek(this.currentDate, this.locale).year;
		},
		focusWeek() {
			return CalendarDate.getWeek(this.focusDate, this.locale).number;
		},
		focusYear() {
			return CalendarDate.getWeek(this.focusDate, this.locale).year;
		}
	},
	methods: {
		toggleYearPicker() {
			this.yearPicker = !this.yearPicker;
		},
		prevPage() {
			if (this.yearPicker)
				return this.$refs.picker.prevPage();

			this.$refs.slider.prevPage();
		},
		nextPage() {
			if (this.yearPicker)
				return this.$refs.picker.nextPage();

			this.$refs.slider.nextPage();
		},
		updatePage(dir) {
			if (!dir)
				return;

			let weeks = 0;
			
			if (dir > 0) {
				while (dir)
					weeks += CalendarDate.countWeeksOfYear(this.focusYear + dir--, this.locale);
			} else {
				while (dir)
					weeks -= CalendarDate.countWeeksOfYear(this.focusYear + dir++, this.locale);
			}

			this.focusDate = CalendarDate.addDays(this.focusDate, weeks * 7);
		},
		setWeek(week) {
			this.clicked = 'clicked';

			this.focusDate = CalendarDate.addDays(this.focusDate, (week - this.focusWeek) * 7);
			
			this.$nextTick(
				() => this.$emit('update:currentDate', this.focusDate)
			);
		},
		setYear(year) {
			this.focusDate = year;
			this.toggleYearPicker();
		}
	},
	created() {
		this.title = Vue.computed(() => this.focusYear + '');
	},
	beforeUnmount() {
		this.title = null;
	},
	template: `
	<div class="fhc-calendar-picker-week" :class="clicked">
		<Transition name="picker">
			<picker-year
				v-if="yearPicker"
				ref="picker"
				:current-date="focusDate"
				@update:current-date="setYear"
				class="position-absolute w-100 h-100"
			/>
		</Transition>
		<base-slider ref="slider" v-slot="slot" @slid="updatePage">
			<div class="d-flex flex-wrap h-100">
				<div
					v-for="week in weeks"
					:key="week"
					class="d-grid col-2"
				>
					<button
						@click="setWeek(week)"
						class="btn btn-outline-secondary m-2"
						:class="{'border-0': week != currentWeek || focusYear + slot.offset != currentYear}"
					>
						{{ week }}
					</button>
				</div>
			</div>
		</base-slider>
	</div>
	`
}
