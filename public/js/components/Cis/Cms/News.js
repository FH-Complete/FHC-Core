import Pagination from "../../Pagination/Pagination.js";
import StudiengangInformation from "./StudiengangInformation/StudiengangInformation.js";
import BsConfirm from "../../Bootstrap/Confirm";

export default {
	name: "NewsComponent",
  components: {
    Pagination,
	StudiengangInformation,
  },
  data() {
    return {
      content: null,
      maxPageCount: 0,
      page_size: 10,
	  page:1,
    };
  },
  watch:{
	'$p.user_language.value':function(sprache){
		this.fetchNews();
	}
  },
  computed:{
	sprache: function(){
		return this.$p.user_language.value;
	},
  },
  methods: {
	fetchNews: function(){
		return this.$fhcApi.factory.cms.getNews(this.page, this.page_size, this.sprache)
		.then(res => res.data)
		.then(result => {
			this.content = result;

			document.querySelectorAll("#cms [data-confirm]").forEach((el) => {
				el.addEventListener("click", (evt) => {
					evt.preventDefault();
					BsConfirm.popup(el.dataset.confirm)
						.then(() => {
							Axios.get(el.href)
								.then((res) => {
									// TODO(chris): check for success then show message and/or reload
									location = location;
								})
								.catch((err) => console.error("ERROR:", err));
						})
						.catch(() => {
						});
				});
			});
			document.querySelectorAll("#cms [data-href]").forEach((el) => {
				el.href = el.dataset.href.replace(
					/^ROOT\//,
					FHC_JS_DATA_STORAGE_OBJECT.app_root
				);
			});
		});
	},
    loadNewPageContent: function (data) {
		this.$fhcApi.factory.cms.getNews(data.page, data.rows)
		.then(res => res.data)
		.then(result => {
			this.content = result;
		});
		
    },
  },
  created() {
    this.fetchNews();

    this.$fhcApi.factory.cms.getNewsRowCount()
	.then(res => res.data)
	.then(result => {
    	this.maxPageCount = result;
    });
  },
  template: /*html*/ `
  	<h2 >News</h2>
	<hr/>
	<pagination v-show="content?true:false" :page_size="page_size"  @page="page=$event.page; loadNewPageContent($event)" :maxPageCount="maxPageCount">
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
	<pagination v-show="content?true:false" :page_size="page_size"  @page="loadNewPageContent" :maxPageCount="maxPageCount">
	</pagination>
    `,
};
