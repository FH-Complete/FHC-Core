import Pagination from "../../Pagination/Pagination.js";

export default {
  components: {
    Pagination,
  },
  data() {
    return {
      content: null,
      maxPageCount: 0,
    };
  },
  methods: {
    loadNewPageContent: function (data) {
      Vue.$fhcapi.Cms.getNews(data.page).then((result) => {
        console.log("fetched url :", result.data);
        this.content = result.data;
      });
    },
  },
  created() {
    Vue.$fhcapi.Cms.getNews().then((result) => {
      this.content = result.data;
    });

    Vue.$fhcapi.Cms.getNewsMaxPage().then((result) => {
      this.maxPageCount = result.data;
    });
  },
  template: /*html*/ `
    <pagination @page="loadNewPageContent" :maxPageCount="maxPageCount">
    <div v-html="content"></div>
    </pagination>`,
};
