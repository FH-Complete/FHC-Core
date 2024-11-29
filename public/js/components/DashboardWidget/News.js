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
		setSelected(news, direction = "next") {
			if (this.selected && news && this.selected === news) return

			if(this.selected){
				const otherDirection = direction === "next" ? "prev" : "next"

				const oldCard = document.getElementById('card-'+this.selected.news_id)
				oldCard.classList.remove('active')
				oldCard.classList.add('carousel-item-'+otherDirection)
			}

			const newCard = document.getElementById('card-'+news.news_id)
			newCard.classList.add('carousel-item-'+direction)
			void newCard.offsetWidth;
			newCard.classList.add('active')

			newCard.addEventListener('transitionend', () => {
				newCard.classList.remove('carousel-item-'+direction);
			}, { once: true });

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
		<div v-else-if="width > 1 && height === 1" class="h-100" :class="'row row-cols-' + width">
			<div class="h-100" v-for="news in newsList" :key="news.id">
				<div class="news-content h-100" :style="'--news-widget-height: '+height" ref="htmlContent" v-html="news.content_obj.content"></div>
			</div>
		</div> 
        <div v-else class="row h-100">
        	<div :class="'col-'+(width == 2? 6 : 4) + ' h-100'" style="overflow: auto; padding-right: 0px; margin: 0px;">
        		<template v-for="news in newsList" :key="'menu-'+news.news_id">
					
					<div class="row fhc-news-menu-item" @click="setSelected(news)">
						<div class="col-8 fhc-news-menu-item-betreff" style="overflow-y: hidden;"><p>{{news.content_obj.betreff ?? ''}}</p></div>
						<span class="fhc-news-menu-item-date"
						 >{{ news.datum ?? ''}}</span>
					</div>
					
				</template>
			</div>
			<div :class="'col-'+(width == 2? 6 : 8) + ' h-100'" ref="htmlContent" style="padding: 0px; margin: 0px;">
				<div class="container" style="padding: 0px; height: 100%;" ref="carocontainer">
				
					<div id="carouselExampleControls" style="height: 100%;" class="carousel slide fhc-carousel" data-bs-ride="carousel" 
						data-bs-interval="false"
						ref="carocontrols">
						<div class="carousel-indicators">
							<button v-for="(news, index) in newsList" :id="'indicator-'+news_news_id" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="index"></button>
						 </div>

						<div class="carousel-inner" style="height: 100%;">
							<div v-for="news in newsList" class="carousel-item" :class="(this.selected.news_id === news.news_id ? 'active' : '')" :id="'card-'+news.news_id" style="height: 100%; overflow-y: auto; margin: 0px;" v-html="news.content_obj.content">
								
							</div>
						</div>
						<button @click="setPrev" style="z-index: 9999; color: black; opacity: 1;" class="carousel-control-prev" type="button">
							<i class="fa fa-chevron-left"></i>
						</button>
						<button @click="setNext" style="z-index: 9999; color: black; opacity: 1;" class="carousel-control-next"  type="button">
							<i class="fa fa-chevron-right"></i>
						</button>
					</div>
				</div>
			</div>
        </div>
    </div>
</div>`,
};
