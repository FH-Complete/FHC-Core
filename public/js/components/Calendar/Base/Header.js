/**
 * TODO(chris): use click-directive
 */

export default {
	name: "CalendarHeader",
	props: {
		title: String,
		view: String,
		btnMonth: Boolean,
		btnWeek: Boolean,
		btnDay: Boolean,
		btnList: Boolean
	},
	emits: [
		"next",
		"prev",
		"click:title",
		"click:view",
		"update:view"
	],
	methods: {
		clickView(evt, view) {
			this.$emit('click:view', evt);
			if (!evt.defaultPrevented)
				this.$emit('update:view', view);
		}
	},
	template: `
	<div class="fhc-calendar-base-header">
		<div class="row">
			<div class="col">
				<slot />
			</div>
			<div class="col-auto d-flex justify-content-center">
				<div class="btn-group" role="group">
					<button class="btn btn-outline-secondary border-0" @click="$emit('prev')">
						<i class="fa fa-chevron-left"></i>
					</button>
					<button class="btn btn-outline-secondary border-0 title" @click="$emit('click:title')">
						{{ title }}
					</button>
					<button class="btn btn-outline-secondary border-0" @click="$emit('next')">
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
