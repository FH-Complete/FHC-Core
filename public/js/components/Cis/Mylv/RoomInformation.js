import FhcCalendar from "../../Calendar/LvPlan.js";
import FormInput from "../../Form/Input.js";

import ApiLvPlan from '../../../api/factory/lvPlan.js';
import ApiRoomPlan from '../../../api/factory/calendar/roomPlan.js';
import ApiRoom from '../../../api/factory/ort.js';

export const DEFAULT_MODE_RAUMINFO_MOBILE = 'List';
export const DEFAULT_MODE_RAUMINFO_DESKTOP = 'Week';

export default {
	name: "RoomInformation",
	components: {
		FhcCalendar,
		FormInput,
	},
	props:{
		viewData: Object, // NOTE(chris): this is inherited from router-view
		propsViewData: Object
	},
	inject: ["isMobile"],
	computed: {
		currentDay() {
			return this.propsViewData?.focus_date || luxon.DateTime.now().setZone(FHC_JS_DATA_STORAGE_OBJECT.timezone).toISODate();
		},
		currentMode() {
			const defaultMode = this.isMobile ? DEFAULT_MODE_RAUMINFO_MOBILE : DEFAULT_MODE_RAUMINFO_DESKTOP;
			return this.propsViewData?.mode || defaultMode;
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
					studiengaenge: [],
					searchGroup: this.searchGroup,
					searchLektor: this.searchLektor,
				},
			},
			roomAdditionalInfo: null,
			reservableRooms: [],
			selectedRoom: null,
		}
	},
	watch: {
		'propsViewData.ort_kurzbz': async function(newVal, oldVal) {
			if (!this.propsViewData.ort_kurzbz) {
				return;
			} else if (this.propsViewData.ort_kurzbz !== this.selectedRoom) {
				this.selectedRoom = this.propsViewData.ort_kurzbz;
			}

			await this.$nextTick();
			if (newVal !== oldVal) {
				this.fetchRoomInformation();
				if (oldVal) {
					this.$refs.calendar.resetEventLoader();
				}
			}
		},
		selectedRoom() {
			if (this.selectedRoom && this.selectedRoom !== this.$route.params.ort_kurzbz) {
				this.$router.push({
					name: "RoomInformation",
					params: {
						mode: this.currentMode,
						focus_date: this.currentDay,
						ort_kurzbz: this.selectedRoom,
					}
				});
			}
		},
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
		async handleCreateEvent(event) {
			event.ort_kurzbz = this.propsViewData.ort_kurzbz;
			const reservationCreationResponse = await this.$api.call(
				ApiRoomPlan.addRoomReservation(event),
			);
			if (reservationCreationResponse.meta.status === "success") {
				this.$refs.calendar.resetEventLoader();
				this.$refs.calendar.closeModal();
			}
		},
		async handleDeleteEvent(event)
		{
			if (event.type !== 'reservierung')
				return;

			if (
				luxon.DateTime.fromISO(`${event.datum}T${event.beginn}`) <
				luxon.DateTime.now().startOf("day")
			)
				return;

			await this.$api.call(ApiRoomPlan.deleteRoomReservation(event.reservierung_id));

			this.$refs.calendar.reset();

		},
		// the Raumsuche link passes its times, the modal opens if the user may reserve on this day
		openSearchedReservation(reservierbarMap) {
			const { von, bis } = this.$route.query;
			if (!von || !bis || !reservierbarMap[this.currentDay])
				return;

			const zone = FHC_JS_DATA_STORAGE_OBJECT.timezone;
			const start = luxon.DateTime.fromISO(this.currentDay + 'T' + von, { zone });
			const end = luxon.DateTime.fromISO(this.currentDay + 'T' + bis, { zone });
			if (!start.isValid || !end.isValid)
				return;

			// only once, every reload of the events gives a new map
			this.$router.replace({ query: {} });
			this.$refs.calendar.openModal({
				type: this.createContext.scope,
				start,
				end,
				createContext: this.createContext
			});
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
		},
		async fetchRoomInformation() {
			if (!this.propsViewData.ort_kurzbz) {
				return;
			}

			const roomCreationInfoResponse = await this.$api.call(
				ApiRoomPlan.getRoomCreationInfo(),
			);
			if (roomCreationInfoResponse.meta?.status === "success") {
				const berechtigt = !!roomCreationInfoResponse.data?.berechtigt;
				if (roomCreationInfoResponse.data?.berechtigt) {
					this.createContext.room_create_information.studiengaenge =
						roomCreationInfoResponse.data?.studiengaenge;
				}
				this.createContext.show_all_fields =
					roomCreationInfoResponse.data?.berechtigt;
			}

			const roomInfoResponse = await this.$api.call(
				ApiRoom.getRoomInfo(this.$props.propsViewData.ort_kurzbz),
			);
			this.roomAdditionalInfo = roomInfoResponse.data.ausstattung;
		},
		async fetchAllReservableRooms() {
			const reservableRoomsResponse = await this.$api.call(
				ApiRoom.getAllReservableRooms(),
			);

			if (reservableRoomsResponse.meta.status === "success") {
				this.reservableRooms = reservableRoomsResponse.data.map(
					(room) => {
						return {
							value: room.ort_kurzbz,
							label:
								room.ort_kurzbz + " (" + room.bezeichnung + ")",
						};
					},
				);

				if (
					this.$route.params.ort_kurzbz &&
					this.reservableRooms.some(
						(room) => room.value === this.$route.params.ort_kurzbz,
					)
				) {
					this.selectedRoom = this.$route.params.ort_kurzbz;
				}
			}
		},
	},
	async created() {
		await this.fetchAllReservableRooms();
		this.fetchRoomInformation();
	},
	template: /*html*/`
	<div class="fhc-roominformation d-flex flex-column h-100">
		<div class="d-flex flex-row gap-2">
			<div class="flex-grow-1 d-flex flex-column">
				<h2>{{ $p.t('rauminfo/rauminfo') }} {{ propsViewData.ort_kurzbz }}</h2>
				<span v-if="roomAdditionalInfo?.length">
					{{ $p.t("search/result_equipment") + ":" }}
					<span v-html="roomAdditionalInfo"></span>
				</span>
			</div>
			<div style="max-width: 25%">
				<form-input v-model="selectedRoom" type="select">
					<option
						v-for="room in reservableRooms"
						:key="room.value"
						:value="room.value"
					>
						{{ room.label }}
					</option>
				</form-input>
			</div>
		</div>
		<hr>
		<fhc-calendar 
			v-if="propsViewData.ort_kurzbz"
			ref="calendar"
			:get-promise-func="getPromiseFunc"
			:date="currentDay"
			:mode="currentMode"
			:reservierbar="true"
			:create-context="createContext"
			@update:date="handleChangeDate"
			@update:mode="handleChangeMode"
			@create-event="handleCreateEvent"
			@delete-event="handleDeleteEvent"
			@update:reservierbar-map="openSearchedReservation"
			class="responsive-calendar"
		></fhc-calendar>
		<div v-else class="w-100 d-flex justify-content-center pt-4">
			<h4> {{ $p.t('rauminfo/no_room_selected') }} </h4>
		<div>
	</div>`
};
