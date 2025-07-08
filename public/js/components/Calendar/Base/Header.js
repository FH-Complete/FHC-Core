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
		date: luxon.DateTime,
		view: String,
		btnMonth: Boolean,
		btnWeek: Boolean,
		btnDay: Boolean,
		btnList: Boolean
	},
	emits: [
		"next",
		"prev",
		"click:view",
		"update:date",
		"update:view"
	],
	data() {
		return {
			open: false
		};
	},
	methods: {
		clickView(evt, view) {
			this.$emit('click:view', evt);
			if (!evt.defaultPrevented)
				this.$emit('update:view', view);
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
						:view="view"
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
						:class="{active: view === 'month'}"
						@click="clickView($event, 'month')"
					>
						<i class="fa fa-calendar-days"></i>
					</button>
					<button
						v-if="btnWeek"
						type="button"
						class="btn btn-outline-secondary"
						:class="{active: view === 'week'}"
						@click="clickView($event, 'week')"
					>
						<i class="fa fa-calendar-week"></i>
					</button>
					<button
						v-if="btnDay"
						type="button"
						class="btn btn-outline-secondary"
						:class="{active: view === 'day'}"
						@click="clickView($event, 'day')"
					>
						<i class="fa fa-calendar-day"></i>
					</button>
					<button
						v-if="btnList"
						type="button"
						class="btn btn-outline-secondary"
						:class="{active: view === 'list'}"
						@click="clickView($event, 'list')"
					>
						<i class="fa fa-table-list"></i>
					</button>
				</div>
			</div>
		</div>
	</div>
	`
}
