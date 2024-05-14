import AbstractWidget from './Abstract';
import BsModal from '../Bootstrap/Modal';
const MAX_LOADED_NEWS = 10;

export default {
	name: 'WidgetsNews',
	components: { BsModal },
	data: () => ({
		allNewsList: [],
		singleNews: {}
	}),
	mixins: [
		AbstractWidget
	],
	computed: {
		newsList(){
			//Return news amount depending on widget width and size
			let quantity = this.width;

			if (this.width === 1) {
				quantity = this.height === 1 ? 4 : 10;
			}

			return this.allNewsList.slice(0, quantity);
		},
                placeHolderImgURL: function() {
                    return FHC_JS_DATA_STORAGE_OBJECT.app_root + 'skin/images/fh_technikum_wien_illustration_klein.png';
                }
	},
	created(){
		axios
			.get(this.apiurl + '/dashboard/Api/getNews', {params: {limit: MAX_LOADED_NEWS}})
			.then(res => { this.allNewsList = res.data })
			.catch(err => { console.error('ERROR: ', err.response.data) });

		this.$emit('setConfig', false);
	},
	methods: {
		setSingleNews(singleNews){
			this.singleNews = singleNews;
			this.$refs.newsModal.show();
		}
	},
	template: `<div class="widgets-news w-100 h-100">
      <div class="d-flex flex-column h-100">
      <div class="d-flex">
        <header><b>Top News</b></header>
        <a href="#allNewsModal" data-bs-toggle="modal" class="ms-auto mb-2">
          <i class="fa fa-arrow-up-right-from-square me-1"></i>Alle News</a>
      </div>
      <div v-if="width == 1">
        <div v-for="news in newsList" :key="news.id" class="mt-2">
          <div class="card">
            <div class="card-body">
              <a href="#newsModal" class="stretched-link" @click="setSingleNews(news)">{{ news.betreff }}</a><br>
              <span class="small text-muted">{{ formatDateTime(news.insertamum) }}</span>
            </div>
          </div>
        </div>
      </div>
      <div v-else-if="width > 1 && height === 1" class="h-100" :class="'row row-cols-' + width">
        <div v-for="news in newsList" :key="news.id">
            <div class="card h-100">
				<div class="card-body">
				  <a href="#newsModal" class="card-title h5 stretched-link" @click="setSingleNews(news)">{{ news.betreff }}</a><br>
				  <span class="small text-muted">{{ formatDateTime(news.insertamum) }} </span>
				  <p class="card-text pt-3" style="overflow: hidden; display: -webkit-box; -webkit-line-clamp: 5; -webkit-box-orient: vertical;">{{ news.text }}</p>
				</div>       
            </div>
          </div>
 		</div>
       <div v-else class="h-100" :class="'row row-cols-' + width">
        <div v-for="news in newsList" :key="news.id">
            <div class="card h-100">
                <img :src="placeHolderImgURL" class="card-img-top">
                <div class="card-footer"><span class="card-subtitle small text-muted">{{ formatDateTime(news.insertamum) }}</span></div>
                <div class="card-body">
                  <a href="#newsModal" class="card-title h5 stretched-link" @click="setSingleNews(news)">{{ news.betreff }}</a><br>
                  <p class="card-text pt-3">{{ news.text }}</p>
                </div>
            </div>
          </div>
      </div>
</div>
</div>

  <!-- News Modal -->
  <BsModal ref="newsModal" id="newsModal" dialog-class="modal-lg">
    <template #title>
      <div class="row">
        <div class="col-5"><img :src="placeHolderImgURL" class="img-fluid rounded-start"></div>
        <div class="col-7 d-flex align-items-end">
          <p>{{ singleNews.betreff }}<br><small class="text-muted">{{ formatDateTime(singleNews.insertamum) }}</small></p>
        </div>
      </div>
    </template>
    <template #default>{{ singleNews.text }}</template>
  </BsModal>

  <!-- All News Modal -->
  <BsModal ref="allNewsModal" id="allNewsModal" dialog-class="modal-fullscreen">
    <template #title>Alle News</template>
    <template #default>
    <div class="row row-cols-5 g-4 h-100 px-5">
     <div v-for="news in allNewsList" :key="news.id">
            <div class="card h-100">
                <img :src="placeHolderImgURL" class="card-img-top">
                <div class="card-footer"><span class="card-subtitle small">{{ formatDateTime(news.insertamum) }}</span></div>
                <div class="card-body">
                  <a href="" class="card-title h5 stretched-link" @click="setSingleNews1(news)">{{ news.betreff }}</a><br>
                  <p class="card-text">{{ news.text }}</p>
                </div>
            </div>
          </div>
          </div>
	</template>
  </BsModal>`
}
