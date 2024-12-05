import Pagination from "../../Pagination/Pagination.js";
import StudiengangInformation from "./StudiengangInformation/StudiengangInformation.js";

export default {
  components: {
    Pagination,
	StudiengangInformation,
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
	<pagination  :page_size="page_size"  @page="loadNewPageContent" :maxPageCount="maxPageCount">
	</pagination>
	<div class="container-fluid">
		<div class="row">
			<div class="col" v-html="content">
			</div>
			<div class="col-auto">
				<div style="width:15rem">
					<studiengang-information></studiengang-information>
				</div>
			</div>
		</div>
	</div>
    `,
};
