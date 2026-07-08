export default {
	name: "GridLineBackground",
	inject: {
		flipAxis: "flipAxis"
	},
	props: {
		start: {
			type: luxon.DateTime,
			required: true
		},
		end: {
			type: luxon.DateTime,
			required: true
		},
		background: {
			type: Object,
			required: true,
			validator(value) {
				if (!value.start && !value.end)
					return false;
				if (value.start && !(value.start instanceof luxon.DateTime))
					return false;
				if (value.end && !(value.end instanceof luxon.DateTime))
					return false;
				return true;
			}
		}
	},
	computed: {
		styles() {
			if (!this.background.endsHere && !this.background.startsHere)
				return this.background.style;

			const perc = (this.end.ts - this.start.ts) / 100;
			
			let border = {};
			if (this.background.startsHere)
				border[this.flipAxis ? 'left' : 'top'] = (this.background.start.diff(this.start)) / perc + '%';
			if (this.background.endsHere)
				border[this.flipAxis ? 'right' : 'bottom'] = (this.end.diff(this.background.end)) / perc + '%';

			if (!this.background.style)
				return border;
			
			return [this.background.style, border];
		},
		classes() {
			if (!this.background.endsHere && !this.background.startsHere)
				return this.background.class;
			
			const result = [];
			if (this.background.class)
				result.push(this.background.class);
			if (this.background.startsHere)
				result.push('bg-begin');
			if (this.background.endsHere)
				result.push('bg-end');
			return result;
		}
	},
	template: /* html */`
	<div
		class="fhc-calendar-base-grid-line-background"
		:class="classes"
		style="position:absolute;inset:0;z-index:0"
		:style="styles"
		:title="background.title"
	>
		<span v-if="background.label">{{ background.label }}</span>
	</div>
	`
}
