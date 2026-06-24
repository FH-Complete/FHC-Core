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
		class="calendar-event-default h-100 w-100 p-1 d-flex flex-row"
	>
		<div
			v-if="event?.beginn && event?.ende"
			class="d-flex flex-column justify-content-center align-items-center flex-grow-1"
		>
			<span>{{ start }}</span>
			<span>{{ end }}</span>
		</div>
		<div
			@click="$emit('deleteCoodleTimeslot', { timeslot: $props.event })"
			class="d-flex justify-content-end align-items-start pt-2 pe-1"
			v-tooltip="tooltipString"
		>
			<i class="fa-solid fa-xmark fa-lg"></i>
		</div>
	</div>
	`,
};
