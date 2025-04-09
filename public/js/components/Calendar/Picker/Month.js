import PickerYear from '../Picker/Year.js';
import BaseSlider from '../Base/Slider.js';

import CalendarDate from '../../../helpers/Calendar/Date.js';

/**
 * TODO(chris): use timestamps instead of dates?
 */

export default {
	name: "PickerMonth",
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
			monthIndices: [...Array(12).keys()],
			clicked: ''
		};
	},
	computed: {
		months() {
			return this.monthIndices.map(i => CalendarDate.format(new Date(0, i, 1), {month: 'long'}, this.locale));
		},
		currentMonth() {
			return this.currentDate.getMonth();
		},
		currentYear() {
			return this.currentDate.getFullYear();
		},
		focusYear() {
			return this.focusDate.getFullYear();
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
			this.focusDate = CalendarDate.addYears(this.currentDate, this.focusDate.getFullYear() - this.currentDate.getFullYear() + 1 * dir);
		},
		setMonth(month) {
			this.clicked = 'clicked';

			this.focusDate = CalendarDate.addMonths(this.focusDate, month - this.focusDate.getMonth());
			
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
		this.title = Vue.computed(() => this.focusDate.getFullYear() + '');
	},
	beforeUnmount() {
		this.title = null;
	},
	template: `
	<div class="fhc-calendar-picker-month" :class="clicked">
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
					v-for="(month, key) in months"
					:key="key"
					class="d-grid col-3"
				>
					<button
						@click="setMonth(key)"
						class="btn btn-outline-secondary m-2"
						:class="{'border-0': key != currentMonth || focusYear + slot.offset != currentYear}"
					>
						{{ month }}
					</button>
				</div>
			</div>
		</base-slider>
	</div>
	`
}
