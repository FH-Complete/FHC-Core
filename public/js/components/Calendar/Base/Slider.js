export default {
	name: 'CalendarSlider',
	inject: {
		time: {
			from: "sliderTime",
			default: ".3s"
		}
	},
	emits: [
		'slid'
	],
	data() {
		return {
			target: 0,
			extrasAfter: 0,
			extrasBefore: 0,
			running: false,
			promiseResolve: null
		}
	},
	computed: {
		itemsAfter() {
			return [...Array(this.extrasAfter)].map((i, k) => 1+k);
		},
		itemsBefore() {
			return [...Array(this.extrasBefore)].map((i, k) => k-this.extrasBefore);
		},
		styleSlider() {
			const style = {
				position: 'absolute',
				top: 0,
				left: 0,
				width: '100%',
				height: '100%'
			};
			if (this.running) {
				style.left = (-this.target * 100) + '%';
				style.transition = 'left ' + this.time + ' ease-in-out';
			}
			return style;
		},
		styleBefore() {
			return {
				position: 'absolute',
				top: 0,
				height: '100%',
				display: 'flex',
				right: '100%',
				width: (this.extrasBefore * 100) + '%'
			};
		},
		styleAfter() {
			return {
				position: 'absolute',
				top: 0,
				height: '100%',
				display: 'flex',
				left: '100%',
				width: (this.extrasAfter * 100) + '%'
			};
		}
	},
	methods: {
		prevPage() {
			return this.slidePages(-1);
		},
		nextPage() {
			return this.slidePages(1);
		},
		slidePages(dir) {
			return new Promise(resolve => {
				this.promiseResolve = resolve;
				this.running = true;
				const newTarget = this.target + dir;
				if (newTarget > 0) {
					if (this.extrasAfter < newTarget)
						this.extrasAfter = newTarget;
				} else if (newTarget < 0) {
					if (-this.extrasBefore > newTarget)
						this.extrasBefore = -newTarget;
				}
				this.target = newTarget;
			});
		},
		endSlide() {
			if (this.promiseResolve) {
				this.promiseResolve(this.target);
				this.promiseResolve = null;
			}
			this.$emit('slid', this.target);
			this.running = false;
			this.target = 0;
			this.extrasAfter = this.extrasBefore = 0;
		}
	},
	template: /* html */`
	<div
		class="fhc-calendar-base-slider h-100"
		style="position:relative;overflow:hidden"
	>
		<div
			:style="styleSlider"
			@transitionend="endSlide"
		>
			<div :style="styleBefore">
				<div
					v-for="i in itemsBefore"
					:key="i"
					style="height:100%;width:100%"
				>
					<slot :offset="i" />
				</div>
			</div>
			<div :style="styleAfter">
				<div
					v-for="i in itemsAfter"
					:key="i"
					style="height:100%;width:100%"
				>
					<slot :offset="i" />
				</div>
			</div>
			<div style="height:100%;width:100%">
				<slot :offset="0" />
			</div>
		</div>
	</div>
	`
}
