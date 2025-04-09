export default {
	name: "GridLineBackground",
	inject: {
		flipAxis: "flipAxis"
	},
	props: {
		start: Number,
		end: Number,
		background: Object
	},
	computed: {
		styles() {
			if (!this.background.endsHere && !this.background.startsHere)
				return this.background.style;

			const perc = (this.end - this.start) / 100;
			
			let border = {};
			if (this.background.startsHere)
				border[this.flipAxis ? 'left' : 'top'] = (this.background.start - this.start) / perc + '%';
			if (this.background.endsHere)
				border[this.flipAxis ? 'right' : 'bottom'] = (this.end - this.background.end) / perc + '%';

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
	template: `
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
