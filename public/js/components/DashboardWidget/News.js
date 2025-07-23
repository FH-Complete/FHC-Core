import AbstractWidget from './Abstract.js';
import BsModal from '../Bootstrap/Modal.js';
import { numberPadding } from '../../helpers/DateHelpers.js';
import ApiCms from '../../api/factory/cms.js';

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
	props: ['width', 'height']
	,
	mixins: [AbstractWidget],
	computed: {
		sizeClass() {
			return 'fhc-news-' + ['xs', 'sm', 'md', 'lg'][this.size];
		},
		newsList() {
			//Return news amount depending on widget width and size
			// let quantity = this.width;
			let quantity = MAX_LOADED_NEWS;


			if (this.width === 1) {
				quantity = this.height === 1 ? 4 : MAX_LOADED_NEWS;
			}

			let slicedNews= this.allNewsList.slice(0, quantity);
			slicedNews.sort((a,b)=>{
				return new Date(b.insertamum) - new Date(a.insertamum);
			});
			
			return slicedNews;
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
		updateNewsContentClasses:function(){
			Vue.nextTick(() => {
				document.querySelectorAll(".fhc-news-card-item .card-body, .fhc-news-card-item .card, .fhc-news-card-item .card-header").forEach((el) => {
					if (!el.classList.contains("border-0")) {
						el.classList.add("border-0");
					}
				});
				document.querySelectorAll(".fhc-news-card-item .card-header").forEach((el) => {
					if (!el.classList.contains("px-5")) {
						el.classList.add("px-5");
					}
					if (!el.classList.contains("fhc-primary")) {
						el.classList.add("fhc-primary");
					}
					if (!el.classList.contains("position-sticky")) {
						el.classList.add("position-sticky");
					}
					if (!el.classList.contains("top-0")) {
						el.classList.add("top-0");
					}
					
					
				});
				document.querySelectorAll(".fhc-news-card-item .card-header .row").forEach((el) => {
					if (!el.classList.contains("w-100")) {
						el.classList.add("w-100");
					}
					if (!el.classList.contains("align-items-center")) {
						el.classList.add("align-items-center");
					}
				});
				document.querySelectorAll(".fhc-news-card-item .card-header .row h2").forEach((el) => {
					if (!el.classList.contains("mb-0")) {
						el.classList.add("mb-0");
					}
				});
			})
		},
		formatDate: function (dateTime) {
			const dt = new Date(dateTime);
			return numberPadding(dt.getDate()) + '.' + numberPadding((dt.getMonth() + 1)) + '.' + dt.getFullYear();				
		},
		formatTime: function (dateTime) {
			const dt = new Date(dateTime);
			return numberPadding(dt.getHours()) + ':' + numberPadding(dt.getMinutes());
		},
		isString(value){
			return Object.prototype.toString.call(value) === '[object String]';
		},
		setNext(){
			const thisIndex = this.allNewsList.findIndex(n=>n.news_id == this.selected.news_id)
			const nextIndex = thisIndex == (this.allNewsList.length - 1) ? 0 : thisIndex + 1

			this.setSelected(this.allNewsList[nextIndex])
			this.updateNewsContentClasses();
			
		},
		setPrev() {
			const thisIndex = this.allNewsList.findIndex(n=>n.news_id == this.selected.news_id)
			const prevIndex = thisIndex ? thisIndex - 1 : this.allNewsList.length - 1
			
			this.setSelected(this.allNewsList[prevIndex])
			this.updateNewsContentClasses();
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
		initCarouselInstance() {
			Vue.nextTick(()=> {
				if(this.$refs.carousel) { // carousel ref might not exist in every widget width/height
					this.carouselInstance = new bootstrap.Carousel(this.$refs.carousel, {
						wrap: false, // keep this off even though it actually wraps
						interval: false
					});
				}
			})
		},
		initActiveItem() {
			Vue.nextTick(()=> {
				if (Array.isArray(this.$refs.carouselItems) && this.$refs.carouselItems.length >0) {
					this.$refs.carouselItems[0].classList.add("active")
				}
			})
		}
	},
	watch: {
		width(newVal, oldVal) {
			if(oldVal == 1 && newVal > 1) { // carousel instance will have been disposed
				this.initCarouselInstance()
				this.initActiveItem()
			}
		}
	},
	created() {
		this.$emit("setConfig", false);
		this.$api
			.call(ApiCms.news(MAX_LOADED_NEWS))
			.then(res => res.data)
			.then((news) => {
				this.allNewsList = Array.from(Object.values(news));
				this.selected = this.allNewsList.length ? this.allNewsList[0] : null
				this.initActiveItem()

				this.updateNewsContentClasses();
				
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
		
		this.initCarouselInstance()

	
	},
	template: /*html*/ `
<div ref="container" class="widgets-news h-100" :class="sizeClass" >
    <div class="d-flex flex-column h-100">
        <div class="h-100 fhc-news-items-sm" style="overflow-y: auto" v-show="width == 1" >
            <div  v-for="(news, index) in newsList" :key="news.news_id" class="py-2">
				<div class="row m-0">
					<div class="col-12 d-flex">
						<span class="small">{{ formatDate(news.insertamum) }} </span>
						<span class="ms-auto small">{{ formatTime(news.insertamum) }} </span>
					</div>
					<div class="col">
						<a :href="contentURI(news.content_id)" >{{ news.content_obj.betreff?news.content_obj.betreff:getDate(news.insertamum) }}</a>
					</div>
				</div>
			</div>
		</div>
        <div v-show="width >1" class="row h-100 g-0">
        	<div :class="'col-'+(width == 4? 3: width == 3? 4 :6)" style="overflow: auto;" class="fhc-news-items-lg border-end h-100 g-0 " >
        		<template v-for="news in newsList" :key="'menu-'+news.news_id" >
				<div class="row m-0 py-2" @click="setSelected(news)">
					<div class="col-md-12 d-flex mb-2 pe-3">
						<span class="small ">{{ formatDate(news.insertamum) }} </span>
						<span class="ms-auto small ">{{ formatTime(news.insertamum) }} </span>
					</div>
					<div class="col-md-12 news-truncate">
						<span >{{ news.content_obj.betreff?news.content_obj.betreff:getDate(news.insertamum) }}</span>
					</div>
				</div>
				</template>
			</div>
			<div style="padding-left: 0px; padding-right: 0px;" ref="htmlContent" class="h-100 col">
				<div class="container h-100" style="padding: 0px;"  ref="carocontainer">
					<div id="FhcCarouselContainer" style="height: 100%;" ref="carousel" class="carousel slide fhc-carousel" data-bs-interval="false">

						<div class="carousel-inner" ref="carouselInner"  style="height: 100%; max-width: 100%;">
							<div ref="carouselItems" v-for="(news, index) in newsList" class="carousel-item fhc-news-card-item" style="overflow-y: auto; overflow-x: hidden; height: 100%;" :id="'card-'+news.news_id" v-html="news.content_obj.content"/>
						</div>
						<button @click="setPrev" style="z-index: 100; overflow: hidden; margin-left: 4px; width:35px;" data-bs-target="#FhcCarouselContainer" class="carousel-control-prev" type="button">
							<div style="padding-left: 0.4rem; padding-right: 0.4rem;">
								<i class="fa fa-chevron-left fhc-text-light"></i>
							</div>
						</button>
						<button @click="setNext" style="z-index: 100;  overflow: hidden; margin-right: 4px; width:35px;" data-bs-target="#FhcCarouselContainer" class="carousel-control-next"  type="button">
							<div style="padding-left: 0.4rem; padding-right: 0.4rem;">
								<i class="fa fa-chevron-right fhc-text-light"></i>
							</div>
						</button>
					</div>
				</div>
			</div>
        </div>
    </div>
</div>`,
};
