import raum_contentmittitel from './Content_types/Raum_contentmittitel.js'
import general from './Content_types/General.js'
import BsConfirm from "../../Bootstrap/Confirm.js";
import news_content from './Content_types/News_content.js';
import ApiCms from '../../../api/factory/cms.js';

export default {
	name: "ContentComponent",
	props: {
		content_id: {
			type: [Number, String],
			required: true
		},
		version: {
			type: [String, Number],
			default: null,
		},
		sichtbar: {
			type: [String, Number],
			default: null,
		}
	},
	components: {
		raum_contentmittitel,
		news_content,
		general,
	},
	data() {
		return {
			content: null,
			content_id_internal: this.content_id
		};
	},
	methods: {
		fetchContent(){
			return this.$api
				.call(ApiCms.content(this.content_id_internal, this.version, this.sprache, this.sichtbar))
				.then(res => {
					this.content = res.data.content;
					this.content_type = res.data.type;
					
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
		}
	},
	watch:{
		sprache: function(sprache){
			this.fetchContent();
		},
		'$route.params.content_id'(newVal) {
			this.content_id_internal = newVal
			this.fetchContent();
		}
	},
	computed: {
		sprache(){
			return this.$p.user_language.value;
		},
		computeContentType: function () {
			switch (this.content_type) {
				case "raum_contentmittitel":
					return "raum_contentmittitel";
				case "news":
					return "news_content";
				default:
					return "general";
			};
		},
	},
	created() {
		this.fetchContent();
	},
	mounted() {
	},
	template: /*html*/ `
    <!-- div that contains the content -->
	<div id="fhc-cms-content" v-if="content">
    	<component ref="content" :is="computeContentType" :content="content" :content_id="content_id_internal" />
	</div>
    <p v-else>No content is available to display</p>
    `,
};
