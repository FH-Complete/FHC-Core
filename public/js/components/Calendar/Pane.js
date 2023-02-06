export default {
	emits: [
		'slid'
	],
	data() {
		return {
			carousel: null,
			queue: 0,
			offset: 0
		}
	},
	computed: {
		offsets() {
			return [...Array(3).keys()].map(i => (3+i-this.offset)%3-1);
		}
	},
	methods: {
		prev() {
			if (!this.queue--)
				this.carousel.prev();
		},
		next() {
			if (!this.queue++)
				this.carousel.next();
		},
		slid(evt) {
			let dir = evt.direction == 'left' ? 1 : -1;
			this.queue -= dir;
			this.$emit('slid', dir);
			this.offset = (3+this.offset+dir)%3;
			if (this.queue) {
				if (this.queue > 0)
					this.carousel.next();
				else
					this.carousel.prev();
			}
		}
	},
	mounted() {
		if (this.$refs.carousel) {
			this.$refs.carousel.children[0].children[1].classList.add('active');
			this.carousel = new window.bootstrap.Carousel(this.$refs.carousel, {
				interval: false
			});
		}
	},
	template: `
	<div ref="carousel" class="calendar-pane carousel slide" @[\`slid.bs.carousel\`]="slid" :data-queue="queue">
		<div class="carousel-inner">
			<div v-for="i in [...Array(3).keys()]" :key="i" class="carousel-item">
				<slot :index="i" :offset="offsets[i]" />
			</div>
		</div>
	</div>`
}
