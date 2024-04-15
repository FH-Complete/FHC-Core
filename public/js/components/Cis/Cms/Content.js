import Pagination from "../../Pagination/Pagination.js";

export default {
  components: {
    Pagination,
  },
  data() {
    return {
      content: null,
    };
  },
  methods: {
    loadNewPageContent: function (data) {
      Vue.$fhcapi.Cms.getNews(data.page).then((result) => {
        this.content = result.data;
      });
    },
  },
  created() {
    Vue.$fhcapi.Cms.getNews().then((result) => {
      this.content = result.data;
    });
  },
  template: /*html*/ `
    <pagination @page="loadNewPageContent">
    <div v-html="content"></div>
    </pagination>`,
};
