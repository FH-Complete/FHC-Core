import Pagination from "../../Pagination/Pagination.js";
import StudiengangInformation from "./StudiengangInformation/StudiengangInformation.js";
import BsConfirm from "../../Bootstrap/Confirm.js";

import ApiCms from "../../../api/factory/cms.js";

export default {
	name: "NewsComponent",
	components: {
		Pagination,
		StudiengangInformation,
	},
	inject: ["isMobile"],
	data() {
		return {
			content: null,
			maxPageCount: 0,
			page_size: 10,
			page: 1,
		};
	},
	watch: {
		"$p.user_language.value": function (sprache) {
			this.fetchNews();
		},
	},
	computed: {
		sprache: function () {
			return this.$p.user_language.value;
		},
	},
	methods: {
		async fetchNews() {
			let newsResponse = await this.$api.call(
				ApiCms.getNews(this.page, this.page_size, this.sprache),
			);
			this.content = newsResponse.data;

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
						.catch(() => {});
				});
			});
			document.querySelectorAll("#cms [data-href]").forEach((el) => {
				el.href = el.dataset.href.replace(
					/^ROOT\//,
					FHC_JS_DATA_STORAGE_OBJECT.app_root,
				);
			});

			await this.$nextTick();
			this.formatExternalHtml();
		},
		async loadNewPageContent(data) {
			let newsResponse = await this.$api.call(
				ApiCms.getNews(data.page, data.rows),
			);
			this.content = newsResponse.data;

			await this.$nextTick();

			this.formatExternalHtml();
		},
		formatExternalHtml() {
			document
				.querySelectorAll(".news-list-item .card-header")
				.forEach((el) => {
					el.classList.add("fhc-primary");
				});
			document.querySelectorAll(".news-list-item .row").forEach((el) => {
				el.classList.add("w-100");
				el.classList.add("align-items-center");
			});
			document
				.querySelectorAll(".news-list-item .row h2")
				.forEach((el) => {
					el.classList.add("mb-0");
				});
		},
		afterPageUpdated(event) {
			this.page = event.page;
			this.page_size = event.rows;
			this.$refs.newsPageHeading.scrollIntoView({block: 'end'});
			this.loadNewPageContent(event);
		},
	},
	created() {
		this.fetchNews();

		this.$api
			.call(ApiCms.getNewsRowCount())
			.then((res) => res.data)
			.then((result) => {
				this.maxPageCount = result;
			});
	},
	template: /*html*/ `
	<div :class="{'overflow-y-scroll pb-3': isMobile}" class="overflow-x-hidden">
  		<h2 ref="newsPageHeading" class="fhc-primary-color">News</h2>
		<hr/>
		<pagination
			v-show="content?true:false"
			:page="page"
			:page_size="page_size"
			@pageUpdated="afterPageUpdated($event)"
			:maxPageCount="maxPageCount"
		></pagination>
		<div class="container-fluid mt-4">
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
		<pagination
			v-show="content?true:false"
			:page="page"
			:page_size="page_size"
			@pageUpdated="afterPageUpdated($event)"
			:maxPageCount="maxPageCount"
		></pagination>
	</div>
    `,
};
