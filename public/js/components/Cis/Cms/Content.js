import Pagination from "../../Pagination/Pagination.js";

export default {
  components: {
    Pagination,
  },
  data() {
    return {
      content: null,
      maxPageCount: 0,
      page_size: 10,
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
    Vue.$fhcapi.Cms.getNews(1, this.page_size).then((result) => {
      this.content = result.data;
    });

    Vue.$fhcapi.Cms.getNewsRowCount().then((result) => {
      this.maxPageCount = result.data;
    });
  },
  template: /*html*/ `
    <pagination :page_size="page_size"  @page="loadNewPageContent" :maxPageCount="maxPageCount">
    <div v-html="content"></div>
    </pagination>`,
};
