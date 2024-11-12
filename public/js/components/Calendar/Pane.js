export default {
	emits: [
		'slid'
	],
	data() {
		return {
			carousel: null,
			queue: 0,
			offset: 0,
			slideAnimation:false,
		}
	},
	provide() {
		return {
			isSliding: Vue.computed(() => this.slideAnimation),
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
			this.slideAnimation = false;
		},
		slide(evt) {
			this.slideAnimation = true;
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
	<div ref="carousel" class="calendar-pane carousel slide" @[\`slide.bs.carousel\`]="slide" @[\`slid.bs.carousel\`]="slid" :data-queue="queue">
		<!--height calc function just for user testing purpose (has to be fixed)-->
		<div class="carousel-inner " style="height:calc(100vh - 220px); overflow:scroll">
			<div v-for="i in [...Array(3).keys()]" :key="i" class="carousel-item">
				<slot :index="i" :offset="offsets[i]" />
			</div>
		</div>
	</div>`
}
