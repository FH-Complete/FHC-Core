import AbstractWidget from './Abstract';
import BsModal from '../Bootstrap/Modal';
const MAX_LOADED_NEWS = 10;

export default {
  name: "WidgetsNews",
  components: { BsModal },
  data: () => ({
    allNewsList: [],
    singleNews: {},
  }),
  mixins: [AbstractWidget],
  computed: {
    newsList() {
      //Return news amount depending on widget width and size
      let quantity = this.width;

      if (this.width === 1) {
        quantity = this.height === 1 ? 4 : 10;
      }

      return this.allNewsList.slice(0, quantity);
    },
    placeHolderImgURL: function () {
      return (
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        "skin/images/fh_technikum_wien_illustration_klein.png"
      );
    },
  },
  created() {
    this.$fhcApi.factory.cms
      .news(MAX_LOADED_NEWS)
      .then((res) => {
        this.allNewsList = res.data;
      })
      .catch((err) => {
        console.error("ERROR: ", err.response.data);
      });

    this.$emit("setConfig", false);
  },
  methods: {
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
  template: /*html*/ `<div class="widgets-news h-100">
  
      <div class="d-flex flex-column h-100 ">
      <div class="d-flex">
        <header><b>{{$p.t('news','topNews')}}</b></header>
        <a :href="allNewsURI()" class="ms-auto mb-2">
          <i class="fa fa-arrow-up-right-from-square me-1"></i>{{$p.t('news','allNews')}}</a>
      </div>
      <div class="h-100" style="overflow-y: auto" v-if="width == 1">
        <div  v-for="news in newsList" :key="news.id" class="mt-2">
          <div  class="card">
            <div class="card-body">
              <a :href="contentURI(news.content_id)" class="stretched-link" >{{ news.content_obj.betreff?news.content_obj.betreff:getDate(news.insertamum) }}</a><br>
              <span class="small text-muted">{{ formatDateTime(news.insertamum) }}</span>
            </div>
          </div>
        </div>
      </div>
      <div v-else-if="width > 1 && height === 1" class="h-100" :class="'row row-cols-' + width">
        <div class="h-100" v-for="news in newsList" :key="news.id">
            
              <div class="news-content h-100" :style="'--news-widget-height: '+height" ref="htmlContent" v-html="news.content_obj.content"></div>
                   
            
          </div>
 		</div>
       <div v-else class="h-100" :class="'row row-cols-' + width">
        <div class="h-100" v-for="news in newsList" :key="news.id">
            
              <div class="news-content h-100" :style="'--news-widget-height: '+height" ref="htmlContent" v-html="news.content_obj.content"></div>
            
          </div>
      </div>
</div>
</div>`,
};
