import CoodleSurveyCalendarTimeslotCard from "./CoodleSurveyCalendar/CoodleSurveyCalendarTimeslotCard.js";
import FhcCalendar from "../../../../Calendar/Base.js";
import ModeWeek from "../../../../Calendar/Mode/Week.js";

import { numberPadding } from "../../../../../helpers/DateHelpers.js";

export default {
	name: "CoodleSurveyCalendar",
	components: { CoodleSurveyCalendarTimeslotCard, FhcCalendar },
	inject: ["renderers"],
	props: {
		surveyFormData: Object | null,
	},
	emits: [],
	data() {
		return {
			mode: "Week",
			modes: {
				week: Vue.markRaw(ModeWeek),
			},
			modeOptions: {
				week: {
					collapseEmptyDays: false,
				},
			},
			// todo: remove example timeslots
			timeslots: [
				{
					type: "coodle",
					datum: "2026-06-24",
					beginn: "10:45:00",
					ende: "12:00:00",
					isostart: "2026-06-24T08:45:00.000Z",
					isoend: "2026-06-24T10:00:00.000Z",
					farbe: "FFFFFF",
				},
				{
					type: "coodle",
					datum: "2026-06-25",
					beginn: "14:55:00",
					ende: "16:10:00",
					isostart: "2026-06-25T12:55:00.000Z",
					isoend: "2026-06-25T14:10:00.000Z",
					farbe: "FFFFFF",
				},
				{
					type: "coodle",
					datum: "2026-06-25",
					beginn: "12:05:00",
					ende: "13:20:00",
					isostart: "2026-06-25T10:05:00.000Z",
					isoend: "2026-06-25T11:20:00.000Z",
					farbe: "FFFFFF",
				},
				{
					type: "coodle",
					datum: "2026-06-26",
					beginn: "14:20:00",
					ende: "15:35:00",
					isostart: "2026-06-26T12:20:00.000Z",
					isoend: "2026-06-26T13:35:00.000Z",
					farbe: "FFFFFF",
				},
				{
					type: "coodle",
					datum: "2026-06-24",
					beginn: "14:35:00",
					ende: "15:50:00",
					isostart: "2026-06-24T12:35:00.000Z",
					isoend: "2026-06-24T13:50:00.000Z",
					farbe: "FFFFFF",
				},
				{
					type: "coodle",
					datum: "2026-06-24",
					beginn: "17:20:00",
					ende: "18:35:00",
					isostart: "2026-06-24T15:20:00.000Z",
					isoend: "2026-06-24T16:35:00.000Z",
					farbe: "FFFFFF",
				},
				{
					type: "coodle",
					datum: "2026-06-25",
					beginn: "17:30:00",
					ende: "18:45:00",
					isostart: "2026-06-25T15:30:00.000Z",
					isoend: "2026-06-25T16:45:00.000Z",
					farbe: "FFFFFF",
				},
				{
					type: "coodle",
					datum: "2026-06-26",
					beginn: "18:40:00",
					ende: "19:55:00",
					isostart: "2026-06-26T16:40:00.000Z",
					isoend: "2026-06-26T17:55:00.000Z",
					farbe: "FFFFFF",
				},
			],
			isTimeslotCardEditInProgress: false,
		};
	},
	computed: {
		timezone() {
			return FHC_JS_DATA_STORAGE_OBJECT.timezone;
		},
		backgrounds() {
			let now = luxon.DateTime.now().setZone(this.timezone);

			return [
				{
					class: "background-past",
					end: now,
					label: now.startOf("minute").toISOTime({
						suppressSeconds: true,
						includeOffset: false,
					}),
				},
			];
		},
		events() {
			// todo
			return this.timeslots;
		},
		roundedTimeslotDuration() {
			return (
				this.$props.surveyFormData.timeslotDuration -
				(this.$props.surveyFormData.timeslotDuration % 5)
			);
		},
	},
	watch: {
		"surveyFormData.timeslotDuration": {
			handler() {
				this.updateDurationOfExistingTimeslots();
			},
		},
		timeslots: {
			handler() {
				// todo: update form data
			},
			deep: true,
		},
	},
	methods: {
		addTimeslotOnCalendarClick(clickEvent) {
			if (this.formatTimeslotForCalendarBasedOnStart.length >= 50) {
				return;
			}

			const elementRectangle = clickEvent.target.getBoundingClientRect();
			const relativeClickLocation = clickEvent.y - elementRectangle.top;
			let minutes =
				Math.floor(
					(relativeClickLocation / elementRectangle.height) * 12,
				) * 5;
			if (minutes < 0 || minutes > 55) {
				minutes = 0;
			}

			let hours = clickEvent.params?.part?.values?.hours ?? 12;
			if (hours < 7 || hours > 22) {
				hours = 12;
			}

			let start = new Date(clickEvent.params.date.ts);
			start.setHours(hours);
			start.setMinutes(minutes);

			let newTimeslot = this.formatTimeslotForCalendarBasedOnStart(start);

			if (
				this.timeslots.some(
					(timeslot) => timeslot.isostart === newTimeslot.isostart,
				)
			) {
				return;
			}

			this.timeslots.push(newTimeslot);
			this.sortTimeslots();
		},
		formatTimeslotForCalendarBasedOnStart(start) {
			let end = new Date(start.getTime());
			end.setMinutes(end.getMinutes() + this.roundedTimeslotDuration);

			return {
				type: "coodle",
				datum: start.toISOString().slice(0, 10),
				beginn:
					numberPadding(start.getHours()) +
					":" +
					numberPadding(start.getMinutes()) +
					":00",
				ende:
					numberPadding(end.getHours()) +
					":" +
					numberPadding(end.getMinutes()) +
					":00",
				isostart: start.toISOString(),
				isoend: end.toISOString(),
				farbe: "FFFFFF",
			};
		},
		updateDurationOfExistingTimeslots() {
			this.timeslots = this.timeslots.map((timeslot) => {
				let start = new Date(timeslot.isostart);
				return this.formatTimeslotForCalendarBasedOnStart(start);
			});
			this.sortTimeslots();
		},
		createTimeslotFromCard(startDate) {
			if (
				this.timeslots.some(
					(existingTimeslot) =>
						existingTimeslot.isostart ===
						startDate.toISOString(),
				)
			) {
				window.alert("You cannot create duplicates!");
				return;
			}

			this.timeslots.push(this.formatTimeslotForCalendarBasedOnStart(startDate));
			this.sortTimeslots();
			this.isTimeslotCardEditInProgress = false;
		},
		updateTimeslotFromCard(timeslot, newStartDate) {
			if (
				this.timeslots.some(
					(existingTimeslot) =>
						existingTimeslot.isostart ===
						newStartDate.toISOString(),
				)
			) {
				window.alert("You cannot create duplicates!");
				return;
			}

			this.timeslots = this.timeslots.map((existingTimeslot) => {
				if (existingTimeslot.isostart !== timeslot.isostart) {
					return existingTimeslot;
				}

				return this.formatTimeslotForCalendarBasedOnStart(newStartDate);
			});

			this.sortTimeslots();
			this.isTimeslotCardEditInProgress = false;
		},
		deleteTimeslot(timeslot) {
			this.timeslots = this.timeslots.filter((existingTimeslot) => {
				return existingTimeslot.isostart !== timeslot.isostart;
			});
			this.sortTimeslots();
			this.isTimeslotCardEditInProgress = false;
		},
		sortTimeslots() {
			this.timeslots = this.timeslots.sort((timeslotA, timeslotB) => {
				let timeslotAStartDateTime = timeslotA.datum + timeslotA.beginn;
				let timeslotBStartDateTime = timeslotB.datum + timeslotB.beginn;
				if (timeslotAStartDateTime > timeslotBStartDateTime) {
					return 1;
				} else {
					return -1;
				}
			});
		},
	},
	created() {
		// todo: set timeslots
		this.sortTimeslots();
	},
	template: /*html*/ `
	<div class="d-flex flex-column gap-3">
		<div class="d-flex flex-column">
			<span class="fw-bold">{{ "Timeslot selection" }}</span>
			<span class="fst-italic">{{ "You can add timeslots directly to the calendar or from the list down below." }}</span>
		</div>
			<div style="height:800px;">
				<fhc-calendar 
					@emptyCellClicked="addTimeslotOnCalendarClick($event)"
					:ref="'coodleCalendar'"
					:timezone="timezone"
					:modes="modes"
					:modeOptions="modeOptions"
					:mode="'Week'"
					:timeGrid="null"
					:showBtns="false"
					:locale="$p.user_locale.value"
					:events="events"
					:backgrounds="backgrounds"
					:draggableEvents="true"			
					:droppableEvents="true"
					:onDrop="true"
				>
				<template v-slot="{ event, mode }">
					<div
						:class="'event-type-' + event.type + ' ' + mode + 'PageContainer'"
						:type="mode == 'day' ? 'button' : undefined"
					>
						<component
							v-if="mode == 'event'"
							:is="renderers[event.type]?.modalContent"
							:event="event"
						></component>
						<component
							v-else-if="mode == 'eventheader'"
							:is="renderers[event.type]?.modalTitle"
							:event="event"
						></component>
						<component
							v-else
							@click.stop=""
							@deleteCoodleTimeslot="deleteTimeslot($event.timeslot)"
							:is="renderers[event.type]?.calendarEvent"
							:event="event"
						></component>
					</div>
				</template>
				</fhc-calendar>
			</div>
		<div class="row">
			<div v-for="timeslot in timeslots.concat([null])" class="col-12 col-md-6 col-xl-4">
				<coodle-survey-calendar-timeslot-card
					@createTimeslot="createTimeslotFromCard($event.startDate)"
					@updateTimeslot="updateTimeslotFromCard(timeslot, $event.newStartDate)"
					@deleteTimeslot="deleteTimeslot(timeslot)"
					@setIsTimeslotCardEditInProgress="isTimeslotCardEditInProgress = $event.value"
					:timeslot="timeslot"
					:isTimeslotCardEditInProgress="isTimeslotCardEditInProgress"
				/>
			</div>
		</div>
	</div>
	`,
};
