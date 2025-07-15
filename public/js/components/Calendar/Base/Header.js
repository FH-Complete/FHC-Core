/**
 * TODO(chris): use click-directive
 */
import DatePicker from './Header/Datepicker.js';

export default {
	name: "CalendarHeader",
	components: {
		DatePicker
	},
	props: {
		date: {
			type: luxon.DateTime,
			required: true
		},
		mode: {
			type: String,
			required: true
		},
		btnMonth: Boolean,
		btnWeek: Boolean,
		btnDay: Boolean,
		btnList: Boolean
	},
	emits: [
		"next",
		"prev",
		"click:mode",
		"update:date",
		"update:mode"
	],
	data() {
		return {
			open: false
		};
	},
	methods: {
		clickMode(evt, mode) {
			this.$emit('click:mode', evt);
			if (!evt.defaultPrevented)
				this.$emit('update:mode', mode);
		}
	},
	template: /* html */`
	<div class="fhc-calendar-base-header">
		<div class="row">
			<div class="col">
				<slot />
			</div>
			<div class="col-auto d-flex justify-content-center">
				<div class="btn-group" role="group">
					<button
						class="btn btn-outline-secondary border-0"
						@click="$emit('prev')"
						:disabled="open"
					>
						<i class="fa fa-chevron-left"></i>
					</button>
					<date-picker
						:mode="mode"
						:date="date"
						@update:date="$emit('update:date', $event)"
						@open="open = true"
						@closed="open = false"
					/>
					<button
						class="btn btn-outline-secondary border-0"
						@click="$emit('next')"
						:disabled="open"
					>
						<i class="fa fa-chevron-right"></i>
					</button>
				</div>
			</div>
			<div class="col">
				<div class="d-flex gap-1 justify-content-end" role="group">
					<button
						v-if="btnMonth"
						type="button"
						class="btn btn-outline-secondary"
						:class="{active: mode === 'month'}"
						@click="clickMode($event, 'month')"
					>
						<i class="fa fa-calendar-days"></i>
					</button>
					<button
						v-if="btnWeek"
						type="button"
						class="btn btn-outline-secondary"
						:class="{active: mode === 'week'}"
						@click="clickMode($event, 'week')"
					>
						<i class="fa fa-calendar-week"></i>
					</button>
					<button
						v-if="btnDay"
						type="button"
						class="btn btn-outline-secondary"
						:class="{active: mode === 'day'}"
						@click="clickMode($event, 'day')"
					>
						<i class="fa fa-calendar-day"></i>
					</button>
					<button
						v-if="btnList"
						type="button"
						class="btn btn-outline-secondary"
						:class="{active: mode === 'list'}"
						@click="clickMode($event, 'list')"
					>
						<i class="fa fa-table-list"></i>
					</button>
				</div>
			</div>
		</div>
	</div>
	`
}
