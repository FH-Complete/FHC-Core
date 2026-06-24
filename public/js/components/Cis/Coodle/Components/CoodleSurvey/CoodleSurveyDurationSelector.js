export default {
	name: "CoodleSurveyDurationSelector",
	props: {
		durationModelValue: Number | null,
		survey: Object,
	},
	emits: ["update:durationModelValue"],
	data() {
		return {
			appointmentDurationPresets: [15, 30, 45, 60, 90, 120],
			selectedDurationPreset: null,
			durationCustomInput: null,
		};
	},
	computed: {
		duration: {
			get() {
				return this.$props.durationModelValue;
			},
			set(newValue) {
				this.$emit("update:durationModelValue", newValue);
			},
		},
	},
	watch: {
		durationCustomInput() {
			if (this.durationCustomInput) {
				this.duration = this.durationCustomInput;
				this.selectedDurationPreset = null;
			}
		},
		survey: {
			handler() {
				this.initializeInputs();
			},
			deep: true,
		},
	},
	methods: {
		selectAppointmentDurationPreset(preset) {
			this.selectedDurationPreset = preset;
			this.duration = preset;
			this.durationCustomInput = null;
		},
		initializeInputs() {
			this.selectedDurationPreset = null;
			this.durationCustomInput = null;
			if (this.appointmentDurationPresets.includes(this.duration)) {
				this.selectedDurationPreset = this.duration;
			} else {
				this.durationCustomInput = this.duration;
			}
		},
	},
	created() {
		this.initializeInputs();
	},
	template: /*html*/ `
	<div class="d-flex flex-column gap-2">
		<span class="fw-bold">{{ "* " + "Appointment duration (in minutes)" }}</span>
		<div class="overflow-x-auto">
			<div class="d-flex flex-row align-items-center gap-2">
				<div
					v-for="durationPreset in appointmentDurationPresets"
					@click="selectAppointmentDurationPreset(durationPreset)"
					type="button"
					:class="{'fhc-primary-border-color': selectedDurationPreset === durationPreset}"
					class="border border-2 d-flex justify-content-center align-items-center"
					style="min-width:60px;height:40px;"
				>
					<span>{{ durationPreset }}</span>
				</div>
				<span class="text-nowrap ps-2">{{ "Custom duration: " }}</span>
				<div
					:class="{'fhc-primary-border-color': durationCustomInput}"
					class="border border-2 d-flex justify-content-center align-items-center px-1"
					style="width:60px;height:40px;"
				>
					<input
						v-model="durationCustomInput"
						type="number"
						min="1"
						class="border-0"
						style="min-width:0; outline: none; background: none;"
					/>
				</div>
			</div>
		</div>
	</div>
	`,
};
