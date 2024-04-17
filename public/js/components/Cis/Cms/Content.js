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
      Vue.$fhcapi.Cms.getNews(data.page, data.rows).then((result) => {
        this.content = result.data;
      });
    },
  },
  created() {
    Vue.$fhcapi.Cms.getNews(1, 10).then((result) => {
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
