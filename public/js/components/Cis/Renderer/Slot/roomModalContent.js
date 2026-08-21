import FormInput from "../../../Form/Input.js";
import ApiRoomPlan from "../../../../api/factory/calendar/roomPlan.js";

export default {
	emits: ["create-event"],
	components: {
		FormInput,
	},
	inject: {
		timeGrid: "timeGrid",
	},
	props: {
		event: { type: Object, required: true },
	},
	data() {
		return {
			timezone: FHC_JS_DATA_STORAGE_OBJECT.timezone,
			data: {},
			filteredGroups: [],
			filteredLektoren: [],
			selectedStart: null,
			selectedEnd: null,
			title: null,
			beschreibung: null,
			studiengang: null,
			semester: null,
			verband: null,
			gruppe: null,
			selectedGruppe: null,
			selectedLektoren: [],
			nestedGroupOptions: {},
		};
	},
	computed: {
		semesterOptions() {
			if (!this.studiengang) return [];

			return Object.keys(this.nestedGroupOptions)
				.map((val) => parseInt(val))
				.sort((a, b) => a - b);
		},
		verbandOptions() {
			if (!this.semester) return [];

			return Object.keys(this.nestedGroupOptions[this.semester]).sort();
		},
		gruppeOptions() {
			if (!this.verband) return [];

			return this.nestedGroupOptions[this.semester][this.verband]
				.map((val) => parseInt(val))
				.sort((a, b) => a - b);
		},
	},
	watch: {
		event: {
			handler(newEvent) {
				this.syncFromEvent(newEvent);
			},
			deep: true,
		},
		studiengang() {
			this.semester = null;
			this.verband = null;
			this.gruppe = null;
			this.nestedGroupOptions = {};
			this.fetchGroupOptions();
		},
		semester() {
			this.verband = null;
		},
		verband() {
			this.gruppe = null;
		},
	},
	methods: {
		syncFromEvent(newEvent) {
			if (!newEvent) return;

			const startTime = newEvent.start
				?.setZone?.(this.timezone)
				?.toFormat?.("HH:mm:ss");
			const endTime = newEvent.end
				?.setZone?.(this.timezone)
				?.toFormat?.("HH:mm:ss");

			this.selectedStart =
				this.timeGrid.find((t) => t.start === startTime)?.start ||
				this.timeGrid[0]?.start;
			this.selectedEnd =
				this.timeGrid.find((t) => t.end === endTime)?.end ||
				this.timeGrid.at(-1)?.end;
		},
		saveEvent() {
			const [startHour, startMinute] = this.selectedStart
				.split(":")
				.map(Number);
			const [endHour, endMinute] = this.selectedEnd
				.split(":")
				.map(Number);
			const selectedStart = this.event.start
				.startOf("day")
				.set({ hour: startHour, minute: startMinute });
			const selectedEnd = this.event.start
				.startOf("day")
				.set({ hour: endHour, minute: endMinute });

			const lektoren_uid = this.selectedLektoren.map((m) => m.uid);

			const spezialgruppe = this.selectedGruppe?.gruppe_kurzbz;

			const event = {
				selectedStart: selectedStart,
				selectedEnd: selectedEnd,
				title: this.title,
				beschreibung: this.beschreibung,
				studiengang: this.studiengang,
				semester: this.semester,
				verband: this.verband,
				gruppe: this.gruppe,
				spezialgruppe: spezialgruppe,
				lektoren: lektoren_uid,
			};

			this.$emit("create-event", event);
		},
		async searchGroup(event) {
			this.filteredGroups =
				await this.event.createContext.room_create_information.searchGroup(
					event,
				);
		},
		async searchLektor(event) {
			this.filteredLektoren =
				await this.event.createContext.room_create_information.searchLektor(
					event,
				);
		},
		capitalize(text) {
			if (!text) return "";
			return text.charAt(0).toUpperCase() + text.slice(1);
		},
		async fetchGroupOptions() {
			if (!this.studiengang) return;

			const groupOptionsResponse = await this.$api.call(
				ApiRoomPlan.getGroupOptions(this.studiengang),
			);

			if (groupOptionsResponse.meta.status === "success") {
				this.nestedGroupOptions = groupOptionsResponse.data;
			}
		},
	},
	mounted() {
		this.syncFromEvent(this.event);
	},

	template: `
	<div class="p-3">
		<h5 class="mb-3">Neue Reservierung</h5>
		<div class="row">
			<form-input
				:label="capitalize($p.t('ui', 'dateFrom'))"
				type="select"
				container-class="col-3"
				v-model="selectedStart"
				name="selectedStart"
			>
				<option
					v-for="slot in timeGrid"
					:value="slot.start"
					:key="slot.id"
				>
					{{ slot.start }}
				</option>
			</form-input>
			
			<form-input
				:label="capitalize($p.t('ui', 'dateTo'))"
				type="select"
				container-class="col-3"
				v-model="selectedEnd"
				name="selectedEnd"
			>
				<option
					v-for="slot in timeGrid"
					:value="slot.end"
					:key="slot.id"
				>
					{{ slot.end }}
				</option>
			</form-input>
		</div>
		<div class="row">
			<form-input
				:label="capitalize($p.t('global', 'titel'))"
				type="text"
				container-class="col-3"
				maxlength="10"
				v-model="title"
				name="title"
			/>
				
			<form-input
				:label="capitalize($p.t('global', 'beschreibung'))"
				type="text"
				container-class="col-4"
				maxlength="32"
				v-model="beschreibung"
				name="Beschreibung"
			/>
			<form-input
				v-if="event.createContext.show_all_fields"
				type="autocomplete"
				:minLength="2"
				:label="capitalize($p.t('lehre', 'lektor'))"
				:suggestions="filteredLektoren"
				placeholder="Mitarbeiter hinzufügen"
				field="label"
				v-model="selectedLektoren"
				container-class="col-5"
				@complete="searchLektor"
				multiple
				name="lektorautocomplete"
			>
			</form-input>
		</div>
		
		<div v-if="event.createContext.show_all_fields">
			<div class="row">
				<form-input
					:label="capitalize($p.t('lehre', 'studiengang'))"
					type="select"
					container-class="col-3"
					v-model="studiengang"
					name="studiengang"
				>
					<option
						v-for="studiengang in event.createContext.room_create_information.studiengaenge"
						:value="studiengang.studiengang_kz"
						:key="studiengang.studiengang_kz"
					>
						{{ studiengang.kuerzel }} ({{ studiengang.kurzbzlang }}) 
					</option>
				</form-input>
				
				<form-input
					:label="capitalize($p.t('lehre', 'semester'))"
					type="select"
					container-class="col-2"
					v-model="semester"
					name="semester"
				>
					<option
						v-for="semester in semesterOptions"
						:value="semester"
						:key="semester"
					>
						{{ semester }} 
					</option>
				</form-input>
				
				<form-input
					:label="capitalize($p.t('lehre', 'verband'))"
					type="select"
					container-class="col-2"
					v-model="verband"
					name="semester"
				>
					<option
						v-for="verband in verbandOptions"
						:value="verband"
						:key="verband"
					>
						{{ verband }} 
					</option>
				</form-input>
				
				<form-input
					:label="capitalize($p.t('lehre', 'gruppe'))"
					type="select"
					container-class="col-2"
					v-model="gruppe"
					name="gruppe"
				>
					<option
						v-for="gruppe in gruppeOptions"
						:value="gruppe"
						:key="gruppe"
					>
						{{ gruppe }} 
					</option>
				</form-input>
				
				<form-input
					:label="capitalize($p.t('lehre', 'special_group'))"
					type="autocomplete"
					:suggestions="filteredGroups"
					:placeholder="$p.t('lehre', 'addGroup')"
					field="label"
					:minLength="2"
					container-class="col-5"
					v-model="selectedGruppe"
					name="gruppeautocomplete"
					@complete="searchGroup"
				/>
			</div>
		</div>
		

		<button class="btn btn-primary mt-3" @click="saveEvent">Speichern</button>
	</div>
	`,
};
