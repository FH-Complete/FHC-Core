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
		selected: null
	}),
	mixins: [AbstractWidget],
	computed: {
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
		placeHolderImgURL: function () {
			return (
				FHC_JS_DATA_STORAGE_OBJECT.app_root +
				"skin/images/fh_technikum_wien_illustration_klein.png"
			);
		},
		activeNews() {
			return this.allNewsList.find(news => news.minimized === false) ?? this.allNewsList[0] ?? null
		}
	},
	created() {
		this.$fhcApi.factory.cms
			.news(MAX_LOADED_NEWS)
			.then((res) => {
				this.allNewsList = Array.from(Object.values(res.data));

				this.selected = this.allNewsList.length ? this.allNewsList[0] : null
				
			})
			.catch((err) => {
				console.error("ERROR: ", err.response.data);
			});

		this.$emit("setConfig", false);
	},
	methods: {
		setNext(){
			const thisIndex = this.allNewsList.findIndex(n=>n.news_id == this.selected.news_id)
			const nextIndex = thisIndex == (this.allNewsList.length - 1) ? 0 : thisIndex + 1
			this.setSelected(this.allNewsList[nextIndex]) 
		},
		setPrev() {
			const thisIndex = this.allNewsList.findIndex(n=>n.news_id == this.selected.news_id)
			const prevIndex = thisIndex ? thisIndex - 1 : this.allNewsList.length - 1
			this.setSelected(this.allNewsList[prevIndex], 'prev')
		},
		getMenuItemClass(news) {
			let classString = ''
			if(this.selected && this.selected.news_id === news.news_id) {
				classString += 'selected'
			}
			return classString
		},
		getDynClassCarouselItem(news, index) {
			// sets classes prev/active/next for bootstrap carousel
			let classString = ''
			
			// return active class to news === selected OR very first news
			if((this.selected.news_id === news.news_id) || (this.selected === null && index === 0)) {
				classString = 'active';
			} else { // set prev/next class for news
				const selectedIndex = this.newsList.indexOf(this.selected)
				const ownIndex = this.newsList.indexOf(news)
				const isPrev = (ownIndex + 1) === selectedIndex || (ownIndex === this.newsList.length - 1 && selectedIndex === 0)
				if(isPrev) {
					classString += ' carousel-item-prev'
				}
				const isNext = (ownIndex - 1) === selectedIndex || (ownIndex === 0 && selectedIndex === this.newsList.length - 1)
				if(isNext) {
					classString += ' carousel-item-next'
				}
			}
			
			return classString;
		},
		setSelected(news, direction = "next") {
			if (this.selected && news && this.selected === news) return
			
			const oldCard = document.getElementById('card-'+this.selected.news_id)

			// TODO: to show animation of non neighbour item through menu reapply css classes 
			if(direction === 'next') {
				// set nextCard .carousel-item-next.carousel-item-start
				oldCard.classList.add('carousel-item-start')

			} else {
				// set prevCard .carousel-item-prev.carousel-item-end
				oldCard.classList.add('carousel-item-end')
			}

			this.selected = news

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
	template: /*html*/ `
<div class="widgets-news h-100" :style="getNewsWidgetStyle">
    <div class="d-flex flex-column h-100">
    
        <div class="h-100" style="overflow-y: auto" v-if="width == 1">
            <div  v-for="(news, index) in newsList" :key="news.news_id" class="mt-2">
                <div v-if="index > 0 " class="fhc-seperator"></div>
                <a :href="contentURI(news.content_id)" >{{ news.content_obj.betreff?news.content_obj.betreff:getDate(news.insertamum) }}</a><br>
                <span class="small text-muted">{{ formatDateTime(news.insertamum) }}</span>
			</div>
		</div>
        <div v-else class="row h-100">
<!--        	TODO: mobile responsiveness of this part-->
        	<div :class="'col-'+(width == 2? 6 : 4) + ' h-100 g-0'" style="overflow: auto;">
        		<template v-for="news in newsList" :key="'menu-'+news.news_id">
					
					<div class="row fhc-news-menu-item" @click="setSelected(news)" :class="getMenuItemClass(news)" style="margin-right: 0px; margin-left: 0px;">
						<div class="col-8 fhc-news-menu-item-betreff" style="overflow-y: hidden;"><p>{{news.content_obj.betreff ?? ''}}</p></div>
						<span class="fhc-news-menu-item-date fw-bold"
						 >{{ news.datum ?? ''}}</span>
					</div>
					
				</template>
			</div>
			<div :class="'col-'+(width == 2? 6 : 8) + ' h-100'" style="padding-left: 0px; padding-right: 0px;" ref="htmlContent">
				<div class="container h-100" style="padding: 0px;"  ref="carocontainer">
				
					<div id="carouselExample" style="height: 100%;" class="carousel slide fhc-carousel" data-bs-ride="carousel" 
						data-bs-interval="false"
						ref="carocontrols">
						<div class="carousel-indicators">
							<button v-for="(news, index) in newsList" :id="'indicator-'+news_news_id" type="button" data-bs-target="#carouselExample" data-bs-slide-to="index"></button>
						 </div>

						<div class="carousel-inner"  style="height: 100%; max-width: 100%;">
							<div v-for="(news, index) in newsList" class="carousel-item" :class="getDynClassCarouselItem(news, index)" style="overflow-y: auto; height: 100%;" :id="'card-'+news.news_id" v-html="news.content_obj.content">
								
							</div>
						</div>
<!--						TODO: prev/next button styling && placement-->
						<button @click="setPrev" style="z-index: 9999; color: black; opacity: 1;" data-bs-target="#carouselExample" class="carousel-control-prev" type="button">
							<i class="fa fa-chevron-left"></i>
						</button>
						<button @click="setNext" style="z-index: 9999; color: black; opacity: 1;" data-bs-target="#carouselExample" class="carousel-control-next"  type="button">
							<i class="fa fa-chevron-right"></i>
						</button>
					</div>
				</div>
			</div>
        </div>
    </div>
</div>`,
};
