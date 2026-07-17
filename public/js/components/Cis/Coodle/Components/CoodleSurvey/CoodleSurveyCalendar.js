import CoodleSurveyCalendarTimeslotCard from "./CoodleSurveyCalendar/CoodleSurveyCalendarTimeslotCard.js";
import FhcCalendar from "../../../../Calendar/Base.js";
import ModeWeek from "../../../../Calendar/Mode/Week.js";
import ModeDay from "../../../../Calendar/Mode/Day.js";

import FreeBusyApi from "../../../../../api/factory/freeBusy.js";
import { useRenderers } from "../../../../../composables/Renderers.js";
import { numberPadding } from "../../../../../helpers/DateHelpers.js";

export default {
	name: "CoodleSurveyCalendar",
	components: { CoodleSurveyCalendarTimeslotCard, FhcCalendar },
	props: {
		timeslotsModelValue: Array | null,
		survey: Object | null,
		surveyFormDataParticipants: Array,
		participantScheduleColors: Array,
		timeslotDuration: Number | null,
		renderers: [],
	},
	emits: ["update:timeslotsModelValue"],
	setup() {
		const { renderers } = useRenderers();

		return {
			renderers,
		};
	},
	inject: {
		isMobile: "isMobile",
	},
	data() {
		return {
			mode: "Week",
			modeOptions: {
				week: {
					collapseEmptyDays: false,
				},
			},
			timeslotCalendarEvents: [],
			participantSchedules: {},
			isTimeslotCardEditInProgress: false,
			renderers: [],
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
			let events = this.timeslotCalendarEvents;

			const participantsWithDisplayedSchedules =
				this.$props.surveyFormDataParticipants.filter(
					(participant) => participant.isCalendarShown,
				);
			participantsWithDisplayedSchedules.forEach((participant) => {
				let participantEvents =
					this.participantSchedules[participant.uid] ?? [];
				participantEvents = participantEvents.map((event) => {
					event.farbe =
						this.$props.participantScheduleColors
							.find(
								(participantColor) =>
									participantColor.uid === participant.uid,
							)
							?.color.slice(1) ?? "DDDDDD";
					return event;
				});
				events = events.concat(participantEvents);
			});

			return events;
		},
		roundedTimeslotDuration() {
			if (!this.$props.timeslotDuration) {
				return 5;
			}

			return (
				this.$props.timeslotDuration -
				(this.$props.timeslotDuration % 5)
			);
		},
		timeslots: {
			get() {
				return this.timeslotsModelValue;
			},
			set(value) {
				this.$emit("update:timeslotsModelValue", value);
			},
		},
		sortedTimeslotCalendarEvents() {
			return this.timeslotCalendarEvents.sort(
				(timeslotCalendarEventA, timeslotCalendarEventB) => {
					let timeslotAStartsAt =
						timeslotCalendarEventA.datum +
						timeslotCalendarEventA.beginn;
					let timeslotBStartsAt =
						timeslotCalendarEventB.datum +
						timeslotCalendarEventB.beginn;
					if (timeslotAStartsAt > timeslotBStartsAt) {
						return 1;
					} else {
						return -1;
					}
				},
			);
		},
		modes() {
			let modes = {
				day: Vue.markRaw(ModeDay),
			};
			if (!this.isMobile) {
				modes.week = Vue.markRaw(ModeWeek);
			}
			return modes;
		},
	},
	watch: {
		timeslotDuration: {
			handler() {
				this.updateDurationOfExistingTimeslotCalendarEvents();
			},
		},
		timeslotCalendarEvents: {
			handler() {
				let oldTimeslotStartTimes =
					this.$props.survey?.timeslots.map(
						(timeslot) => timeslot.startsAt,
					) ?? [];
				let updatedTimeslotStartTimes = this.timeslotCalendarEvents.map(
					(timeslotCalendarEvent) =>
						timeslotCalendarEvent.datum +
						" " +
						timeslotCalendarEvent.beginn,
				);

				let updatedTimeslots =
					this.$props.survey?.timeslots.filter((timeslot) =>
						updatedTimeslotStartTimes.includes(timeslot.startsAt),
					) ?? [];

				updatedTimeslotStartTimes.forEach((startTime) => {
					if (
						!updatedTimeslots.some(
							(timeslot) => timeslot.startsAt === startTime,
						)
					) {
						updatedTimeslots.push({
							id: null,
							startsAt: startTime,
						});
					}
				});

				this.timeslots = updatedTimeslots;
			},
			deep: true,
		},
		survey: {
			handler() {
				this.setTimeslotCalendarEvents();
			},
			deep: true,
		},
		surveyFormDataParticipants: {
			handler() {
				this.updateDisplayedSchedules();
			},
			deep: true,
		},
	},
	methods: {
		setTimeslotCalendarEvents() {
			this.timeslotCalendarEvents =
				this.$props.survey?.timeslots.map((timeslot) => {
					let startDate = new Date(timeslot.startsAt);
					return this.generateTimeslotCalendarEvent(startDate);
				}) ?? [];
		},
		addTimeslotOnCalendarClick(event) {
			if (this.timeslotCalendarEvents.length >= 50) {
				return;
			}

			const elementRectangle = event.event.target.getBoundingClientRect();
			const relativeClickLocation = event.event.y - elementRectangle.top;
			let minutes =
				Math.floor(
					(relativeClickLocation / elementRectangle.height) * 12,
				) * 5;
			if (minutes < 0 || minutes > 55) {
				minutes = 0;
			}

			let hours = event.params?.part?.values?.hours ?? 12;
			if (hours < 7 || hours > 22) {
				hours = 12;
			}

			let start = new Date(event.params.date.ts);
			start.setHours(hours);
			start.setMinutes(minutes);

			let newTimeslotCalendarEvent =
				this.generateTimeslotCalendarEvent(start);

			if (
				this.timeslotCalendarEvents.some(
					(timeslotCalendarEvent) =>
						timeslotCalendarEvent.isostart ===
						newTimeslotCalendarEvent.isostart,
				)
			) {
				return;
			}

			this.timeslotCalendarEvents.push(newTimeslotCalendarEvent);
		},
		generateTimeslotCalendarEvent(start) {
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
				farbe: "DDDDDD",
			};
		},
		updateDurationOfExistingTimeslotCalendarEvents() {
			this.timeslotCalendarEvents = this.timeslotCalendarEvents.map(
				(timeslotCalendarEvent) => {
					let start = new Date(timeslotCalendarEvent.isostart);
					return this.generateTimeslotCalendarEvent(start);
				},
			);
		},
		createTimeslotFromCard(startDate) {
			if (
				this.timeslotCalendarEvents.some(
					(existingTimeslotCalendarEvent) =>
						existingTimeslotCalendarEvent.isostart ===
						startDate.toISOString(),
				)
			) {
				this.$fhcAlert.alertError("You cannot create duplicates!");
				return;
			}

			this.timeslotCalendarEvents.push(
				this.generateTimeslotCalendarEvent(startDate),
			);
			this.isTimeslotCardEditInProgress = false;
		},
		updateTimeslotFromCard(timeslotCalendarEvent, newStartDate) {
			if (
				this.timeslotCalendarEvents.some(
					(existingTimeslotCalendarEvent) =>
						existingTimeslotCalendarEvent.isostart ===
						newStartDate.toISOString(),
				)
			) {
				this.$fhcAlert.alertError("You cannot create duplicates!");
				return;
			}

			this.timeslotCalendarEvents = this.timeslotCalendarEvents.map(
				(existingTimeslotCalendarEvent) => {
					if (
						existingTimeslotCalendarEvent.isostart !==
						timeslotCalendarEvent.isostart
					) {
						return existingTimeslotCalendarEvent;
					}

					return this.generateTimeslotCalendarEvent(newStartDate);
				},
			);

			this.isTimeslotCardEditInProgress = false;
		},
		deleteTimeslot(timeslotCalendarEvent) {
			this.timeslotCalendarEvents = this.timeslotCalendarEvents.filter(
				(existingTimeslotCalendarEvent) => {
					return (
						existingTimeslotCalendarEvent.isostart !==
						timeslotCalendarEvent.isostart
					);
				},
			);
			this.isTimeslotCardEditInProgress = false;
		},
		getEventStyle(event) {
			if (!event.farbe) return undefined;
			return "--event-bg:#" + event.farbe;
		},
		updateDisplayedSchedules() {
			let participantsWithDisplayedSchedules =
				this.$props.surveyFormDataParticipants.filter(
					(participant) => participant.isCalendarShown,
				);
			participantsWithDisplayedSchedules.forEach((participant) => {
				if (!(participant.uid in this.participantSchedules)) {
					this.fetchFreeBusySchedule(participant.uid);
				}
			});
		},
		async fetchFreeBusySchedule(uid) {
			this.participantSchedules[uid] = null;
			const freeBusyScheduleResponse = await this.$api.call(
				FreeBusyApi.getFreeBusySchedule(uid),
			);
			this.participantSchedules[uid] = freeBusyScheduleResponse.data.map(
				(event) => {
					const startDate = new Date(event.start);
					const endDate = new Date(event.end);
					return {
						datum: event.start.split(" ")[0],
						beginn: event.start.split(" ")[1],
						ende: event.end.split(" ")[1],
						isostart: startDate.toISOString(),
						isoend: endDate.toISOString(),
						type: "freeBusy",
					};
				},
			);
		},
	},
	created() {
		this.setTimeslotCalendarEvents();
	},
	template: /*html*/ `
	<div id="coodleCalendar" class="d-flex flex-column gap-3">
		<div class="d-flex flex-column">
			<span class="fw-bold">{{ "Appointment selection" }}</span>
			<span class="fst-italic">{{ "You can add appointment options directly to the calendar or from the list down below." }}</span>
		</div>
		<div style="height:800px;">
			<fhc-calendar
				v-if="renderers"
				@emptyClicked="addTimeslotOnCalendarClick($event)"
				:ref="'coodleCalendar'"
				:timezone="timezone"
				:modes="modes"
				:modeOptions="modeOptions"
				:mode="!isMobile ? 'Week' : 'Day'"
				:timeGrid="null"
				:locale="$p.user_locale.value"
				:events="events"
				:backgrounds="backgrounds"
				:draggableEvents="true"			
				:droppableEvents="true"
				:onDrop="true"
				:isAutoScrollEnabled="false"
				:showBtns="!isMobile"
			>
				<template v-slot="{ event, mode }">
					<div
						:class="'event-type-' + event.type + ' ' + mode + 'PageContainer'"
						:type="mode == 'day' ? 'button' : undefined"
						:style="getEventStyle(event)"
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
			<div v-for="timeslot in sortedTimeslotCalendarEvents.concat([null])" class="col-12 col-md-6 col-xl-4">
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
