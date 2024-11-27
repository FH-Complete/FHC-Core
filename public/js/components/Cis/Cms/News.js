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
		this.$fhcApi.factory.cms.getNews(data.page, data.rows)
		.then(res => res.data)
		.then(result => {
			this.content = result;
		});
		
    },
  },
  created() {
    this.$fhcApi.factory.cms.getNews(1, this.page_size)
	.then(res => res.data)
	.then(result => {
		this.content = result;
	});

    this.$fhcApi.factory.cms.getNewsRowCount()
	.then(res => res.data)
	.then(result => {
    	this.maxPageCount = result;
    });
  },
  template: /*html*/ `
  	<h2 >News</h2>
	<hr/>
    <pagination :page_size="page_size"  @page="loadNewPageContent" :maxPageCount="maxPageCount">
    <div v-html="content"></div>
    </pagination>`,
};
