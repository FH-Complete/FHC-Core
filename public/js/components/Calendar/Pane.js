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
			scrollTop:null,
			clientHeight:null,
			carouselItems:null,
		}
	},
	provide() {
		return {
			isSliding: Vue.computed(() => this.slideAnimation),
			calendarScrollTop: Vue.computed(() =>this.scrollTop),
			calendarClientHeight: Vue.computed(() => this.clientHeight),
		}
	},
	computed: {
		offsets() {
			return [...Array(3).keys()].map(i => (3+i-this.offset)%3-1);
		},
		activeCarouselItemIndex() {
			if (Array.isArray(this.carouselItems) && this.carouselItems.length > 0) {
				for(let index=0; index < this.carouselItems.length; index++){
					if (this.carouselItems[index] == true){
						return index;
					}
				}

			}
			return -1;
		}
		
	},
	methods: {
		scrollCalendar(event){
			this.scrollTop = this.$refs.calendarContainer.scrollTop;
			this.clientHeight = this.$refs.calendarContainer.clientHeight;
		},
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
			this.carouselItems = this.$refs.carouselItems.map((item) => { return item.classList.contains('active') });
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
		this.carouselItems = this.$refs.carouselItems.map((item)=>{return item.classList.contains('active')});
		this.scrollTop = this.$refs.calendarContainer.scrollTop;
		this.clientHeight = this.$refs.calendarContainer.clientHeight;
	},
	template: `
	<div ref="carousel" class="calendar-pane carousel slide" @[\`slide.bs.carousel\`]="slide" @[\`slid.bs.carousel\`]="slid" :data-queue="queue">
		<!--height calc function just for user testing purpose (has to be fixed)-->
		<div @scroll="scrollCalendar" ref="calendarContainer" class="carousel-inner " style="height:var(--fhc-calendar-pane-height); overflow:scroll">
			<div ref="carouselItems" v-for="i in [...Array(3).keys()]" :key="i" class="carousel-item">
				<slot :active="i == activeCarouselItemIndex" :index="i" :offset="offsets[i]" />
			</div>
		</div>
	</div>`
}
