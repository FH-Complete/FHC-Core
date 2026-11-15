import ApiRoomPlan from '../../../api/factory/calendar/roomPlan.js';
import ApiInfoTerminal from '../../../api/factory/infoterminal.js';


export default {
	name: 'InfoTerminalRoomsAvailabilityOverview',
	props: {
		cardNumber: {
			required: true,
			default: null,
		},
	},
	data() {
		return {
			timezone: FHC_JS_DATA_STORAGE_OBJECT.timezone,
			teachingTimeSlots: [],
			roomShortCodes: [],
			eventsPerRoom: {},
			selectedRoomShortCode: null,
			isLoaded: false,
		};
	},
	computed: {
		sortedRoomShortCodes() {
			const now = luxon.DateTime.now().setZone(this.timezone);

			return [...new Set(this.roomShortCodes)]
				.map((roomShortCode) => {
					const firstEvent = (this.eventsPerRoom[roomShortCode] ?? [])
						.filter((event) => event.end > now)
						.reduce((first, event) => (!first || event.start < first.start ? event : first), null);
					return { roomShortCode, firstEvent };
				})
				.sort((a, b) => {
					if (!a.firstEvent && !b.firstEvent) return a.roomShortCode.localeCompare(b.roomShortCode);
					if (!a.firstEvent) return 1;
					if (!b.firstEvent) return -1;
					return a.firstEvent.start.toMillis() - b.firstEvent.start.toMillis();
				})
				.map((room) => room.roomShortCode);
		},
		roomStatuses() {
			return Object.fromEntries(
				this.sortedRoomShortCodes.map((roomShortCode) => [roomShortCode, this.getRoomStatus(roomShortCode)])
			);
		},
		selectedRoomSchedule() {
			if (!this.selectedRoomShortCode) return [];

			const events = this.eventsPerRoom[this.selectedRoomShortCode] ?? [];
			const now = luxon.DateTime.now().setZone(this.timezone);
			const day = now.startOf('day');

			return this.teachingTimeSlots.map((slot) => {
				const [startHour, startMinute] = slot.start.split(':').map(Number);
				const [endHour, endMinute] = slot.end.split(':').map(Number);
				const start = day.set({ hour: startHour, minute: startMinute });
				const end = day.set({ hour: endHour, minute: endMinute });

				const slotEvents = events.filter((event) => event.start < end && event.end > start);

				return {
					...slot,
					startTime: start,
					endTime: end,
					events: slotEvents,
					occupied: slotEvents.length > 0,
					isCurrent: now >= start && now < end,
				};
			});
		},
		scheduleRows() {
			const half = Math.ceil(this.selectedRoomSchedule.length / 2);
			return [
				this.selectedRoomSchedule.slice(0, half),
				this.selectedRoomSchedule.slice(half),
			].filter((row) => row.length);
		},
		columnsPerRow() {
			return Math.ceil(this.selectedRoomSchedule.length / 2) || 1;
		},
		freeSlotCount() {
			return this.selectedRoomSchedule.filter((slot) => !slot.occupied).length;
		},
	},
	watch: {
		eventsPerRoom() {
			if (!this.selectedRoomShortCode && this.sortedRoomShortCodes.length) {
				this.selectedRoomShortCode = this.sortedRoomShortCodes[0];
			}
		},
	},
	methods: {
		async getTodaysPersonalEvents() {
			let now = luxon.DateTime.now().setZone(this.timezone);
			let start = now.startOf('day');
			let end = now.endOf('day');

			const personalEventsResponse = await this.$api.call(ApiInfoTerminal.getPersonalEvents(this.cardNumber, start.toISODate(), end.toISODate()));
			this.roomShortCodes = personalEventsResponse.data?.map((event) => event.ort_kurzbz) || [];
			
			if (!this.roomShortCodes?.length) return;

			const roomEventsResponse = await this.$api.call(ApiInfoTerminal.getEventsForRooms(this.cardNumber, this.roomShortCodes, start.toISODate(), end.toISODate()));
			const roomEvents = roomEventsResponse.data;

			this.eventsPerRoom = (roomEventsResponse.data ?? [])
				.map((event) => ({
					...event,
					start: luxon.DateTime.fromISO(event.isostart, { zone: this.timezone }),
					end: luxon.DateTime.fromISO(event.isoend, { zone: this.timezone }),
				}))
				.reduce((acc, event) => {
					const key = event.ort_kurzbz;
					if (!key) return acc;
					(acc[key] ??= []).push(event);
					return acc;
				}, {});
		},
		async getTeachingTimeSlots() {
			let timeSlotsResponse = await this.$api.call(ApiInfoTerminal.getHours());
			this.teachingTimeSlots = timeSlotsResponse.data.map((el) => ({
				id: el.stunde,
				start: el.beginn,
				end: el.ende,
			}));
		},
		displayRoomOverview(roomShortCode) {
			this.selectedRoomShortCode = roomShortCode;
		},
		isRoomOccupiedNow(roomShortCode) {
			const now = luxon.DateTime.now().setZone(this.timezone);
			return (this.eventsPerRoom[roomShortCode] ?? []).some((event) => event.start <= now && event.end > now);
		},
		getRoomStatus(roomShortCode) {
			if (this.isRoomOccupiedNow(roomShortCode)) return 'occupied';

			const now = luxon.DateTime.now().setZone(this.timezone);
			const day = now.startOf('day');
			const events = this.eventsPerRoom[roomShortCode] ?? [];

			const nextSlots = this.teachingTimeSlots
				.map((slot) => {
					const [startHour, startMinute] = slot.start.split(':').map(Number);
					const [endHour, endMinute] = slot.end.split(':').map(Number);
					return {
						start: day.set({ hour: startHour, minute: startMinute }),
						end: day.set({ hour: endHour, minute: endMinute }),
					};
				})
				.filter((slot) => slot.end > now)
				.slice(0, 4);

			const occupiedSoon = nextSlots.some((slot) =>
				events.some((event) => event.start < slot.end && event.end > slot.start)
			);

			return occupiedSoon ? 'freeCurrently' : 'free';
		},
		formatTime(dateTime) {
			return dateTime.toFormat('HH:mm');
		},
	},
	async created() {
		try {
			await Promise.all([
				this.getTodaysPersonalEvents(),
				this.getTeachingTimeSlots(),
			]);
		} finally {
			this.isLoaded = true;
		}
	},
	template: /*html*/ `
	<div v-if="!isLoaded" class="d-flex justify-content-center align-items-center gap-2 py-5 text-muted">
		<div class="spinner-border spinner-border-sm" role="status"></div>
		{{ $p.t('ui', 'loading') }}
	</div>

	<div v-else class="py-3">
		<div v-if="sortedRoomShortCodes?.length">
			<h5 class="mb-3">{{ $p.t('ui', 'upcomingRoomsTitle')}}</h5>
			<div class="d-flex flex-wrap gap-2 mb-4">
				<button
					v-for="roomShortCode in sortedRoomShortCodes"
					:key="roomShortCode"
					type="button"
					class="btn d-flex flex-column align-items-start text-start px-3 py-2 shadow-sm border"
					:class="[
						{
							occupied: 'bg-danger-subtle border-danger text-danger-emphasis',
							freeCurrently: 'bg-warning-subtle border-warning text-warning-emphasis',
							free: 'bg-success-subtle border-success text-success-emphasis',
						}[roomStatuses[roomShortCode]],
						{ 'border-3': selectedRoomShortCode === roomShortCode }
					]"
					:style="selectedRoomShortCode === roomShortCode
						? 'min-width: 10rem; box-shadow: 0 0 0 .2rem var(--bs-primary) !important;'
						: 'min-width: 10rem;'"
					:aria-pressed="selectedRoomShortCode === roomShortCode"
					@click="displayRoomOverview(roomShortCode)"
				>
					<span class="fw-semibold fs-5">
						<i class="fa fa-door-open me-2"></i>{{ roomShortCode }}
					</span>
					<span class="small">
						<template v-if="roomStatuses[roomShortCode] === 'occupied'">{{ $p.t('info_terminal', 'occupied')}}</template>
						<template v-else-if="roomStatuses[roomShortCode] === 'freeCurrently'">
							<i class="fa fa-clock me-1"></i>{{ $p.t('info_terminal', 'freeCurrently')}}
						</template>
						<template v-else>{{ $p.t('info_terminal', 'free')}}</template>
					</span>
				</button>
			</div>
			<div v-if="selectedRoomShortCode" class="card shadow-sm">
				<div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
					<span class="fw-semibold fs-5">
						<i class="fa fa-door-open me-2"></i>{{ selectedRoomShortCode }}
					</span>
				</div>

				<div class="card-body p-2" style="overflow-x: auto;">
					<div
						v-for="(row, rowIndex) in scheduleRows"
						:key="rowIndex"
						class="border rounded overflow-hidden bg-white"
						:class="{ 'mb-3': rowIndex < scheduleRows.length - 1 }"
						:style="'display: grid; grid-template-columns: repeat(' + columnsPerRow + ', minmax(9rem, 1fr));'"
					>
						<div
							v-for="(slot, index) in row"
							:key="'header-' + slot.id"
							class="text-center py-2 px-1"
							:class="{ 'border-start': index > 0 }"
							:style="{ gridRow: 1, gridColumn: index + 1, borderBottom: '3px solid var(--bs-gray-300)' }"
						>
							<div class="fw-semibold text-nowrap" :class="{ 'text-primary': slot.isCurrent }">
								{{ formatTime(slot.startTime) }} – {{ formatTime(slot.endTime) }}
							</div>
							<span class="badge mt-1" :class="slot.isCurrent ? 'bg-primary' : 'invisible'">
								{{ $p.t('info_terminal', 'now')}}
							</span>
						</div>
						<div
							v-for="(slot, index) in row"
							:key="'cell-' + slot.id"
							class="p-1 d-flex flex-column gap-1"
							:class="{ 'border-start': index > 0 }"
							:style="{ gridRow: 2, gridColumn: index + 1, minHeight: '7rem', backgroundColor: '#fbf4ea' }"
						>
							<div
								v-for="(event, eventIndex) in slot.events"
								:key="eventIndex"
								class="d-flex flex-grow-1 bg-white border rounded"
								style="border-color: var(--bs-gray-500) !important;"
							>
								<div
									class="d-flex flex-column justify-content-center px-2 border-end small text-nowrap"
									style="border-color: var(--bs-gray-500) !important;"
								>
									<span>{{ formatTime(event.start) }}</span>
									<span>{{ formatTime(event.end) }}</span>
								</div>

								<!-- Event details -->
								<div class="flex-grow-1 d-flex flex-column justify-content-center text-center px-2 py-1 small" style="min-width: 0;">
									<div class="text-truncate" :title="event.titel ?? event.title">
										{{ event.titel ?? event.title }}
									</div>
									<div
										v-for="lektor in event.lektor ?? []"
										:key="lektor.kurzbz"
										class="text-truncate"
									>
										{{ lektor.kurzbz }}
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div
			v-else
			class="h-100 d-flex justify-content-center align-items-center"
		>
			<h5 class="m-0">{{ $p.t('info_terminal', 'noUpcomingRoomsTitle') }}</h5>
		</div>
	</div>`,
};