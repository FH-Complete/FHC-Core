export default {
	props: {
		event: {
			type: Object,
			required: true,
		},
	},
	emits: ["deleteCoodleTimeslot"],
	computed: {
		tooltipString() {
			const tooltipArray = [];

			const dateFragments = this.$props.event.datum.split("-");
			tooltipArray.push(
				dateFragments[2] +
					"." +
					dateFragments[1] +
					"." +
					dateFragments[0] +
					".",
			);
			tooltipArray.push(this.start + "-" + this.end);

			return tooltipArray.join("\n");
		},
		start() {
			return luxon.Duration.fromISOTime(this.event.beginn).toISOTime({
				suppressSeconds: true,
			});
		},
		end() {
			return luxon.Duration.fromISOTime(this.event.ende).toISOTime({
				suppressSeconds: true,
			});
		},
	},
	template: /* html */ `
	<div
		class="coodle-calendar-event calendar-event-default h-100 w-100 p-1 d-flex flex-row"
		v-tooltip="tooltipString"
	>
		<div
			v-if="event?.beginn && event?.ende"
			class="d-flex flex-column justify-content-center align-items-center flex-grow-1"
		>
			<span>{{ start }}</span>
			<span>{{ end }}</span>
		</div>
		<div
			class="d-flex justify-content-end align-items-start"
		>
			<div
				@click="$emit('deleteCoodleTimeslot', { timeslot: $props.event })"
				class="coodle-calendar-event-clear"
			>
				<i class="fa-solid fa-xmark fa-lg"></i>
			</div>
		</div>
	</div>
	`,
};
