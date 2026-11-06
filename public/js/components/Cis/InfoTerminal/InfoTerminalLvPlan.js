import FhcCalendar from "../../Calendar/LvPlan.js";

 import ApiRoomPlan from "../../../api/factory/calendar/roomPlan.js";
 import ApiInfoTerminal from "../../../api/factory/infoterminal.js";

export default {
	name: "InfoTerminalLvPlan",
	components: {
		FhcCalendar,
	},
	props: {
		propsViewData: Object,
		cardNumber: {
			required: true,
			default: null,
		}
	},
	data() {
		return {
			timezone: FHC_JS_DATA_STORAGE_OBJECT.timezone,
			semesterRangePresets: [],
			startSemester: null,
			endSemester: null,
			teachingUnits: [],
			compactibleEventTypes: [],
			renderers: {},
		};
	},
	provide() {
		return {
			initialRenderers: Vue.computed(() => this.renderers),
			rangeLength: Vue.computed(() => {
				if (!this.$route.params.range_length) return 30;
				else if (this.$route.params.range_length > 365) return 365;
				else return this.$route.params.range_length;
			}),
			rangeViewPresetsConfig: Vue.computed(() => {
				return {
					label: this.$p.t("LvPlan/view_specific_semester"),
					presets: this.semesterRangePresets,
				};
			}),
		};
	},
	computed: {
		currentDay() {
			if (
				!this.propsViewData?.focus_date ||
				isNaN(new Date(this.propsViewData?.focus_date))
			)
				return luxon.DateTime.now().setZone(this.timezone).toISODate();
			return this.propsViewData?.focus_date;
		},
		currentMode() {
			let validModes = ["day", "month", "week", "range"];

			if (
				!this.propsViewData?.mode ||
				!validModes.includes(this.propsViewData?.mode.toLowerCase())
			)
				return "week";
			return this.propsViewData?.mode;
		},
	},
	methods: {
		handleChangeDate(day, newMode, rangeLength) {
			return this.handleChangeMode(newMode, day, rangeLength);
		},
		handleChangeMode(newMode, day, range_length = null) {
			const mode = newMode[0].toUpperCase() + newMode.slice(1);
			const focus_date = day.toISODate();

			this.$router.push({
				name: "MyLvPlan",
				params: {
					mode,
					focus_date,
					range_length,
				},
			});
		},
		async updateRange(rangeInterval) {
			const semesterResponse = await this.$api.call(
				ApiInfoTerminal.getContainingOrNearestByDateRange(
					this.cardNumber,
					rangeInterval.s.toISO().slice(0, 10),
					rangeInterval.e.toISO().slice(0, 10),
				),
			);
			this.startSemester = 
				semesterResponse.data.length
					? semesterResponse.data[0]
					: null;
			this.endSemester =
				semesterResponse.data.length > 1
					? semesterResponse.data[1]
					: null;
		},
		getPromiseFunc(start, end) {
			return [
				this.$api.call(
					ApiInfoTerminal.eventsPersonal(
						this.cardNumber,
						start.toISODate(),
						end.toISODate(),
					),
				),
			];
		},
		deleteEvent(event) {
			if (event.type === "reservierung") {
				this.deleteReservation(event);
			}
		},
		async deleteReservation(event) {
			if (
				luxon.DateTime.fromISO(`${event.datum}T${event.beginn}`) <
				luxon.DateTime.now()
			)
				return;

			await this.$api.call(
				ApiRoomPlan.deleteRoomReservation(event.reservierung_id),
			);

			this.$refs.calendar.reset();
		},
		async getStunden() {
			let stundenResponse = await this.$api.call(ApiInfoTerminal.getStunden());
			this.teachingUnits = stundenResponse.data.map((el) => ({
				id: el.stunde,
				start: el.beginn,
				end: el.ende,
			}));
		},
		async getCompactibleEventTypes() {
			let compactibleEventTypesResponse = await this.$api.call(
				ApiInfoTerminal.getCompactibleEventTypes(),
			);
			this.compactibleEventTypes = compactibleEventTypesResponse.data;
		},
		async fetchSemesters() {
			let userCreatedAt = null;
			const userResponse = await this.$api.call(ApiInfoTerminal.getUserData(this.cardNumber,));
			if (userResponse.meta.status === "success" && userResponse.data[0].insertamum) {
				userCreatedAt = luxon.DateTime.fromFormat(userResponse.data[0].insertamum.split(" ")[0], "yy-MM-dd");
			}
			console.log(userResponse)
			const semestersResponse = await this.$api.call(
				ApiInfoTerminal.getAll(this.cardNumber,),
			);
			if (semestersResponse.meta.status === "success") {
				this.semesterRangePresets = semestersResponse.data
					.map((semester) => {
						let startDate = luxon.DateTime.fromISO(semester.start);
						let endDate = luxon.DateTime.fromISO(semester.ende);
						return {
							startDate,
							endDate,
							name: semester.studiensemester_kurzbz,
							description: semester.bezeichnung,
						};
					})
					.filter((semester) => {
						if (!userCreatedAt) return true;

						return userCreatedAt.ts < semester.endDate.ts;
					})
					.sort((semesterA, semesterB) =>
						semesterA.startDate.ts > semesterB.startDate.ts
							? -1
							: 1,
					);
			}
		},
	},
	async created() {
		await this.getStunden();
		await this.getCompactibleEventTypes();
		await this.fetchSemesters();

		await this.$api
					.call(ApiInfoTerminal.loadRenderers())
					.then((res) => res.data)
					.then((data) => {
						for (let rendertype of Object.keys(data)) {
							let modalTitle = null;
							let modalContent = null;
							let calendarEvent = null;
							if (data[rendertype].modalTitle)
								modalTitle = Vue.markRaw(
									Vue.defineAsyncComponent(
										() => import(data[rendertype].modalTitle),
									),
								);
							if (data[rendertype].modalContent)
								modalContent = Vue.markRaw(
									Vue.defineAsyncComponent(
										() => import(data[rendertype].modalContent),
									),
								);
							if (data[rendertype].calendarEvent)
								calendarEvent = Vue.markRaw(
									Vue.defineAsyncComponent(
										() => import(data[rendertype].calendarEvent),
									),
								);
		
							if (data[rendertype].calendarEventStyles) {
								var head = document.head;
								if (
									!head.querySelector(
										`link[href="${data[rendertype].calendarEventStyles}"]`,
									)
								) {
									var link = document.createElement('link');
									link.type = 'text/css';
									link.rel = 'stylesheet';
									link.href = data[rendertype].calendarEventStyles;
									head.appendChild(link);
								}
							}
		
							if (!this.renderers[rendertype]) {
								this.renderers[rendertype] = {};
							}
							this.renderers[rendertype].modalTitle = modalTitle;
							this.renderers[rendertype].modalContent = modalContent;
							this.renderers[rendertype].calendarEvent = calendarEvent;
						}
					});
	},
	template: /*html*/ `
	<div class="cis-lvplan-personal d-flex flex-column h-100">
		<h2 id="cis-lvplan-personal-heading">
			{{ $p.t('lehre/stundenplan') }}
			<span v-if="startSemester?.studiensemester_kurzbz">
				{{ startSemester.studiensemester_kurzbz }}
				<span v-if="endSemester?.studiensemester_kurzbz">
					{{ "- " + endSemester.studiensemester_kurzbz }}
				</span>
			</span>
		</h2>
		<hr>
		<fhc-calendar
			v-if="timezone && teachingUnits?.length && compactibleEventTypes.length"
			ref="calendar"
			:timezone="timezone"
			:get-promise-func="getPromiseFunc"
			:date="currentDay"
			:mode="currentMode"
			:shouldIncludeRangeMode="true"
			:initialTeachingUnits="teachingUnits"
			:initialCompactibleEventTypes="compactibleEventTypes"
			:custom-renderers="renderers"
			@update:date="handleChangeDate"
			@update:mode="handleChangeMode"
			@update:range="updateRange"
			@delete-event="(event) => deleteEvent(event)"
			class="responsive-calendar"
		>
			<div class="d-flex gap-1 justify-items-start">
			</div>
		</fhc-calendar>
	</div>`,
};
