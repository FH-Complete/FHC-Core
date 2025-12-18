import FhcCalendar from "../../Calendar/LvPlan.js";

import ApiLvPlan from '../../../api/factory/lvPlan.js';
import ApiRoomPlan from '../../../api/factory/calendar/roomPlan.js';

export const DEFAULT_MODE_RAUMINFO = 'Week'

export default {
	name: "RoomInformation",
	components: {
		FhcCalendar
	},
	props:{
		viewData: Object, // NOTE(chris): this is inherited from router-view
		propsViewData: Object
	},
	computed: {
		currentDay() {
			return this.propsViewData?.focus_date || luxon.DateTime.now().setZone(this.viewData.timezone).toISODate();
		},
		currentMode() {
			return this.propsViewData?.mode || DEFAULT_MODE_RAUMINFO;
		}
	},
	data() {
		return {
			filteredGroups: [],
			abortController: null,
			createContext: {
				scope: 'slot_room',
				show_all_fields: false,
				room_create_information: {
					semester: [1, 2, 3, 4, 5, 6, 7, 8],
						verband: ['A', 'B', 'C', 'D', 'E', 'F', 'V'],
					gruppe: [1, 2, 3, 4],
					studiengaenge: [],
					searchGroup: this.searchGroup,
					searchLektor: this.searchLektor,
				},
			}
		}
	},
	created() {

		this.$api.call(ApiRoomPlan.getRoomCreationInfo())
			.then(result => result.data)
			.then(result => {
				if (result.berechtigt)
				{
					this.createContext.room_create_information.studiengaenge = result.studiengaenge
				}
				this.createContext.show_all_fields = result.berechtigt;
			});
	},
	methods:{
		handleChangeDate(day, newMode) {
			return this.handleChangeMode(newMode, day);
		},
		handleChangeMode(newMode, day) {
			const mode = newMode[0].toUpperCase() + newMode.slice(1)
			const focus_date = day.toISODate();

			this.$router.push({
				name: "RoomInformation",
				params: {
					mode,
					focus_date,
					ort_kurzbz: this.propsViewData.ort_kurzbz
				}
			});
		},
		async handleCreateEvent(event)
		{
			event.ort_kurzbz = this.propsViewData.ort_kurzbz;
			this.$api.call(ApiRoomPlan.addRoomReservation(event));
			this.$refs.calendar.resetEventLoader();
			this.$refs.calendar.closeModal();
		},
		async handleDeleteEvent(event)
		{
			if (event.type !== 'reservierung')
				return;

			if (luxon.DateTime.fromISO(`${event.datum}T${event.beginn}`) < luxon.DateTime.now())
				return;

			this.$api.call(ApiRoomPlan.deleteRoomReservation(event.reservierung_id));

			this.$refs.calendar.reset();

		},
		async searchGroup(event)
		{
			const query = event.query.trim();

			if (query.length < 2)
				return [];

			if (this.abortController)
				this.abortController.abort();

			this.abortController = new AbortController();
			const signal = this.abortController.signal;

			return this.$api.call(ApiRoomPlan.getGruppen(query), { signal })
				.then(result => {
					return result.data.map(gruppe => ({
							label: gruppe.bezeichnung
								? `${gruppe.gruppe_kurzbz.trim()} (${gruppe.bezeichnung})`
								: gruppe.gruppe_kurzbz.trim(),
							gid: gruppe.gid,
							gruppe_kurzbz: gruppe.gruppe_kurzbz.trim(),
							lehrverband: gruppe.lehrverband,
						})
					);
				})
				.catch((e)=> {
					this.$fhcAlert.handleSystemError(e)
					return []
				})
		},
		async searchLektor(event)
		{
			const query = event.query.trim();

			if (query.length < 2)
				return [];

			if (this.abortController)
				this.abortController.abort();

			this.abortController = new AbortController();
			const signal = this.abortController.signal;

			return this.$api.call(ApiRoomPlan.getLektor(query), { signal })
				.then(result => {
					return result.data.map(lektor => ({
							label: `${lektor.nachname} ${lektor.vorname} (${lektor.uid})`,
							uid: lektor.uid
						})
					)})
				.catch(this.$fhcAlert.handleSystemError)
		},
		getPromiseFunc(start, end) {
			return [
				this.$api.call(ApiRoomPlan.getReservableMap(this.propsViewData.ort_kurzbz, start.toISODate(), end.toISODate())),
				this.$api.call(ApiLvPlan.getRoomInfo(this.propsViewData.ort_kurzbz, start.toISODate(), end.toISODate())),
				this.$api.call(ApiLvPlan.getOrtReservierungen(this.propsViewData.ort_kurzbz, start.toISODate(), end.toISODate()))
			];
		}
	},
	template: /*html*/`
	<div class="fhc-roominformation d-flex flex-column h-100">
		<h2>{{ $p.t('rauminfo/rauminfo') }} {{ propsViewData.ort_kurzbz }}</h2>
		<hr>
		<fhc-calendar 
			ref="calendar"
			:timezone="viewData.timezone"
			:get-promise-func="getPromiseFunc"
			:date="currentDay"
			:mode="currentMode"
			:reservierbar="true"
			:create-context="createContext"
			@update:date="handleChangeDate"
			@update:mode="handleChangeMode"
			@create-event="handleCreateEvent"
			@delete-event="handleDeleteEvent"
			class="responsive-calendar"
		></fhc-calendar>
	</div>`
};
