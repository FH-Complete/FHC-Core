import AbstractWidget from './Abstract';
import BsModal from '../Bootstrap/Modal';

const MAX_LOADED_NEWS = 30;

export default {
	name: "WidgetsNews",
	components: {
		BsModal
	},
	data: () => ({
		allNewsList: [],
		singleNews: {},
		selected: null,
		size:0,
	}),
	mixins: [AbstractWidget],
	computed: {
		sizeClass() {
			return 'fhc-news-' + ['xs', 'sm', 'md', 'lg'][this.size];
		},
		getNewsWidgetStyle() {
			return this.width == 1 ? "padding: 1rem 1rem;" : "padding: 0px;"
		},
		newsList() {
			//Return news amount depending on widget width and size
			// let quantity = this.width;
			let quantity = MAX_LOADED_NEWS;


			if (this.width === 1) {
				quantity = this.height === 1 ? 4 : MAX_LOADED_NEWS;
			}

			return this.allNewsList.slice(0, quantity);
		},
		carouselItems() {
			return this.allNewsList.reduce((acc, cur) => {
				const el = document.getElementById('card-'+cur.news_id)
				acc.push(el);
				return acc
			}, [])
		}
	},
	methods: {
		isString(value){
			return Object.prototype.toString.call(value) === '[object String]';
		},
		setNext(){
			const thisIndex = this.allNewsList.findIndex(n=>n.news_id == this.selected.news_id)
			const nextIndex = thisIndex == (this.allNewsList.length - 1) ? 0 : thisIndex + 1

			this.setSelected(this.allNewsList[nextIndex])
		},
		setPrev() {
			const thisIndex = this.allNewsList.findIndex(n=>n.news_id == this.selected.news_id)
			const prevIndex = thisIndex ? thisIndex - 1 : this.allNewsList.length - 1
			
			this.setSelected(this.allNewsList[prevIndex])
		},
		getMenuItemClass(news) {
			let classString = ''
			if(this.selected && this.selected.news_id === news.news_id) {
				classString += 'selected'
			}
			return classString
		},
		async setSelected(news) {
			let clickedElement = document.getElementById('card-'+news.news_id);
			let clickedElementIndex = this.allNewsList.indexOf(news);
			let oldElementIndex = this.allNewsList.indexOf(this.selected);
			
			//if the clicked element is already active, do nothing
			if(clickedElementIndex === oldElementIndex) return;
			//add prev/next class to the clicked element
			if(clickedElementIndex > oldElementIndex){
				clickedElement.classList.add('carousel-item-next');
			}else{
				clickedElement.classList.add('carousel-item-prev');
			}

			// move to clicked element
			await Vue.nextTick(() => { this.carouselInstance.to(clickedElementIndex); })
			this.selected = news;
		},
		contentURI: function (content_id) {
			return (
				FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				"/CisVue/Cms/content/" +
				content_id
			);
		},
		allNewsURI: function () {
			return (
				FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				"/CisVue/Cms/news"
			);
		},
		setSingleNews(singleNews) {
			this.singleNews = singleNews;
			this.$refs.newsModal.show();
		},
	},
	created() {
		this.$emit("setConfig", false);
		this.$fhcApi.factory.cms
			.news(MAX_LOADED_NEWS)
			.then(res => res.data)
			.then((news) => {
				this.allNewsList = Array.from(Object.values(news));
				this.selected = this.allNewsList.length ? this.allNewsList[0] : null
				Vue.nextTick(()=>{
					if (Array.isArray(this.$refs.carouselItems) && this.$refs.carouselItems.length >0) {
						this.$refs.carouselItems[0].classList.add("active")
					}
				})
				})
			.catch((err) => {
				console.error("ERROR: ", err);
			});
		
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
		Vue.nextTick(()=>{
			this.carouselInstance = new bootstrap.Carousel(this.$refs.carousel, {
				wrap: false, // keep this off even though it actually wraps
				interval: false
			});
		})
		
	},
	template: /*html*/ `
<div ref="container" class="widgets-news h-100" :class="sizeClass" :style="getNewsWidgetStyle">
    <div class="d-flex flex-column h-100">
        <div class="h-100" style="overflow-y: auto" v-if="width == 1">
            <div  v-for="(news, index) in newsList" :key="news.news_id" class="mt-2">
                <div v-if="index > 0 " class="fhc-seperator"></div>
                <a :href="contentURI(news.content_id)" >{{ news.content_obj.betreff?news.content_obj.betreff:getDate(news.insertamum) }}</a><br>
                <span class="small text-muted">{{ formatDateTime(news.insertamum) }}</span>
			</div>
		</div>
        <div v-else class="row h-100 g-0">
        	<div :class="'col-'+(width == 2? 6 : 4) + ' h-100 g-0'" style="overflow: auto;">
        		<template v-for="news in newsList" :key="'menu-'+news.news_id">
					<div class="position-relative">
						<div class="row fhc-news-menu-item " @click="setSelected(news)" :class="getMenuItemClass(news)" style="margin-right: 0px; margin-left: 0px; overflow-y: hidden;">
							<div class="col-8 fhc-news-menu-item-betreff">
								<p class="fhc-news-text mb-0">
									{{news.content_obj.betreff ?? ''}}
								</p>
							</div>
							<span style="top:2px; right:0" class=" position-absolute d-none d-xl-block fhc-news-text fhc-news-menu-item-date fw-bold">
								{{ news.datumformatted ?? ''}}
							</span>
						</div>
					</div>
				</template>
			</div>
			<div :class="'col-'+(width == 2? 6 : 8) + ' h-100'" style="padding-left: 0px; padding-right: 0px;" ref="htmlContent">
				<div class="container h-100" style="padding: 0px;"  ref="carocontainer">
					<div id="FhcCarouselContainer" style="height: 100%;" ref="carousel" class="carousel slide fhc-carousel" data-bs-ride="carousel" data-bs-interval="false">

						<div class="carousel-inner" ref="carouselInner"  style="height: 100%; max-width: 100%;">
							<div ref="carouselItems" v-for="(news, index) in newsList" class="carousel-item " style="overflow-y: auto; overflow-x: hidden; height: 100%;" :id="'card-'+news.news_id" v-html="news.content_obj.content"/>
						</div>
						<button @click="setPrev" @focus="$event.target.blur()" style="z-index: 100; color: black; overflow: hidden; margin-left: 10px; width:35px;" data-bs-target="#FhcCarouselContainer" class="carousel-control-prev" type="button">
							<div class="border rounded-circle" style="padding-left: 0.4rem; padding-right: 0.4rem; background-color:rgba(138,138,138,0.4)">
								<i class="fa fa-chevron-left"></i>
							</div>
						</button>
						<button @click="setNext" @focus="$event.target.blur()" style="z-index: 100; color: black; overflow: hidden; margin-right: 10px; width:35px;" data-bs-target="#FhcCarouselContainer" class="carousel-control-next"  type="button">
							<div class="border rounded-circle" style="padding-left: 0.4rem; padding-right: 0.4rem; background-color:rgba(138,138,138,0.4)">
								<i class="fa fa-chevron-right"></i>
							</div>
						</button>
					</div>
				</div>
			</div>
        </div>
    </div>
</div>`,
};
