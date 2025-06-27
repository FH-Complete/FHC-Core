/**
 * TODO(chris): use click-directive
 */

export default {
	name: "CalendarHeader",
	components: {
		vuedatepicker: VueDatePicker
	},
	props: {
		currentDate: Number,
		title: String,
		view: String,
		btnMonth: Boolean,
		btnWeek: Boolean,
		btnDay: Boolean,
		btnList: Boolean
	},
	data: function() {
		return {
			internCurrentDate: null,
			selectedMonth: null,
			selectedWeek: null,
			selectedDay: null
		};
	},
	emits: [
		"next",
		"prev",
		"click:title",
		"click:view",
		"update:view",
		"update:currentDate"
	],
	watch: {
		currentDate: function(newVal, oldVal) {
			if(newVal === oldVal) {
				return;
			}
			console.log(newVal);
		}
	},
	methods: {
		clickView(evt, view) {
			this.$emit('click:view', evt);
			if (!evt.defaultPrevented)
				this.$emit('update:view', view);
		},
		selectedMonthChanged: function() {
			console.log(this.selectedMonth);
			this.selectedDay = this.selectedMonth;
			//TODO set selectedWeek
			let tmpdate = new Date(this.selectedMonth);
			this.selectedWeek = [
				tmpdate, tmpdate
			];
			this.internCurrentDate = tmpdate.getTime();
			this.$emit('update:currentDate', this.internCurrentDate);
		},
		selectedWeekChanged: function() {
			console.log(this.selectedWeek);
			let isodatestr = '';
			if( Array.isArray(this.selectedWeek)) {
				let tmpdate = this.selectedWeek[0];
				tmpdate.setHours(12);
				isodatestr = tmpdate.getFullYear() + "-" +
				String(tmpdate.getMonth() + 1).padStart(2, "0") + "-" +
				String(tmpdate.getDate()).padStart(2, "0");
				this.selectedMonth = isodatestr;
				this.selectedDay = isodatestr;
				this.internCurrentDate = tmpdate.getTime();
				this.$emit('update:currentDate', this.internCurrentDate);
			}
		},
		selectedDayChanged: function() {
			console.log(this.selectedDay);
			this.selectedMonth = this.selectedDay;
			//TODO set selectedWeek
			let tmpdate = new Date(this.selectedDay);
			this.selectedWeek = [
				tmpdate, tmpdate
			];
			this.internCurrentDate = tmpdate.getTime();
			this.$emit('update:currentDate', this.internCurrentDate);
		}
	},
	template: `
	<div class="fhc-calendar-base-header">
		<div class="row">
			<div class="col">
				<slot />
			</div>
			<div class="col-auto d-flex justify-content-center">
		
<div class="fhc-calendar-datepicker row align-items-center justify-content-center">
					<div style="max-width: 200px;">
					<vuedatepicker
						v-if="view === 'month'"
						v-model="selectedMonth"
						:month-picker="true"
						:action-row="{ showSelect: false, showCancel: false, showNow: false, showPreview: false }"
						:config="{keepActionRow: true}"
						:enable-time-picker="false"
						:teleport="true"
						:clearable="false"
						six-weeks
						auto-apply 
						:text-input="false"
						locale="de"
						format="MMMM yyyy"
						model-type="yyyy-MM-dd"
						@update:model-value="selectedMonthChanged"
						:timezone="{ convertModel: false, timezone: 'UTC' }"
					></vuedatepicker>
		
					<vuedatepicker
						v-else-if="view === 'week'"
						v-model="selectedWeek"
						:week-picker="true"
						:week-numbers="{ type: 'iso' }"
						:action-row="{ showSelect: false, showCancel: false, showNow: true, showPreview: false }"
						:config="{keepActionRow: true}"
						:enable-time-picker="false"
						:teleport="true"
						:clearable="false"
						six-weeks
						auto-apply 
						:text-input="false" 
						locale="de"
						format="yyyy 'KW' ww"
						model-type="yyyy-MM-dd"
						@update:model-value="selectedWeekChanged"
						:timezone="{ convertModel: false, timezone: 'UTC' }"
					></vuedatepicker>
		
					<vuedatepicker
						v-else=""
						v-model="selectedDay"
						:enable-time-picker="false"
						:teleport="true"
						:week-numbers="{ type: 'iso' }"
						:action-row="{ showSelect: false, showCancel: false, showNow: true, showPreview: false }"
						:config="{keepActionRow: true}"
						:clearable="false"
						six-weeks
						auto-apply 
						:text-input="true"
						locale="de"
						format="dd.MM.yyyy"
						model-type="yyyy-MM-dd"
						@update:model-value="selectedDayChanged"
						:timezone="{ convertModel: false, timezone: 'UTC' }"
					></vuedatepicker>
					</div>
				</div>
<!--
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
-->
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
