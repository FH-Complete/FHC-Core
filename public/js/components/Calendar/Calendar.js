import CalendarMonth from './Month.js';
import CalendarMonths from './Months.js';
import CalendarYears from './Years.js';
import CalendarWeek from './Week.js';
import CalendarWeeks from './Weeks.js';
import CalendarMinimized from './Minimized.js';
import CalendarDate from '../../composables/CalendarDate.js';

// TODO(chris): week/month toggle

export default {
	components: {
		CalendarMonth,
		CalendarMonths,
		CalendarYears,
		CalendarWeek,
		CalendarWeeks,
		CalendarMinimized
	},
	provide() {
		return {
			date: this.date,
			focusDate: this.focusDate,
			size: Vue.computed({ get: () => this.size, set: v => this.size = v}),
			events: Vue.computed(() => this.eventsPerDay),
			minimized: Vue.computed({ get: () => this.minimized, set: v => this.$emit('update:minimized',v) }),
			showWeeks: this.showWeeks,
			noMonthView: this.noMonthView,
			noWeekView: this.noWeekView,
			eventsAreNull: Vue.computed(() => this.events === null),
			classHeader: this.classHeader
		};
	},
	props: {
		events: Array,
		initialDate: {
			type: [Date, String],
			default: new Date()
		},
		showWeeks: {
			type: Boolean,
			default: true
		},
		initialMode: {
			type: String,
			default: 'month'
		},
		classHeader: {
			type: [String,Object,Array],
			default: ''
		},
		minimized: Boolean,
		noWeekView: Boolean,
		noMonthView: Boolean
	},
	emits: [
		'select:day',
		'select:event',
		'change:range',
		'update:minimized'
	],
	data() {
		return {
			header: '',
			prevMode: null,
			currMode: null,
			date: new CalendarDate(),
			focusDate: new CalendarDate(),
			size: 0
		}
	},
	computed: {
		sizeClass() {
			return 'fhc-calendar-' + ['xs','sm','md','lg'][this.size];
		},
		mode: {
			get() { return this.minimized ? 'minimized' : this.currMode; },
			set(v) {
				if (!v && this.prevMode) {
					this.currMode = this.prevMode;
					this.prevMode = null;
				} else {
					this.prevMode = this.currMode;
					this.currMode = v;
				}
			}
		},
		eventsPerDay() {
			if (!this.events)
				return {};
			return this.events.reduce((result, event) => {
				let days = Math.ceil((event.end - event.start) / 86400000) || 1;
				while (days-- > 0) {
					let day = (new Date(event.start.getFullYear(), event.start.getMonth(), event.start.getDate() + days)).toDateString();
					if (!result[day])
						result[day] = [];
					result[day].push(event);
				}
				return result;
			}, {});
		}
	},
	methods: {
		handleInput(day) {
			this.$emit(day[0], day[1]);
		}
	},
	created() {
		const allowedInitialModes = ['years'];
		if (!this.noWeekView)
			allowedInitialModes.push('week');
		if (!this.noMonthView)
			allowedInitialModes.push('month');
		this.mode = allowedInitialModes[allowedInitialModes.indexOf(this.initialMode)] || allowedInitialModes.pop();
		this.date.set(new Date(this.initialDate));
		this.focusDate.set(this.date);
	},
	mounted() {
		if (this.$refs.container) {
			new ResizeObserver(entries => {
				for (const entry of entries) {
					let w = entry.contentBoxSize ? entry.contentBoxSize[0].inlineSize : entry.contentRect.width;
					// TODO(chris): rework sizing
					if (w > 600)
						this.size = 3;
					else if (w > 350)
						this.size = 2;
					else if (w > 250)
						this.size = 1;
					else
						this.size = 0;
				}
			}).observe(this.$refs.container);
		}
	},
	template: `
	<div ref="container" class="fhc-calendar card" :class="sizeClass">
		<component :is="'calendar-' + mode" @update:mode="mode=$event" @change:range="$emit('change:range',$event)" @input="handleInput" />
	</div>`
}
