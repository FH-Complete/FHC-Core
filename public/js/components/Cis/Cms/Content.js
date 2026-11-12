import raum_contentmittitel from './Content_types/Raum_contentmittitel.js'
import general from './Content_types/General.js'
import BsConfirm from "../../Bootstrap/Confirm.js";
import news_content from './Content_types/News_content.js';
import iframe_content from './Content_types/Iframe_content.js';

import DefaultApiCms from '../../../api/factory/cms.js';

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
		},
		fetchContentURL: {
			type: Object,
			default: null,
		}
	},
	components: {
		raum_contentmittitel,
		news_content,
		general,
		iframe_content
	},
	data() {
		return {
			content_type: null,
			content: null,
			content_id_internal: this.content_id
		};
	},
	methods: {
		fetchContent(){
			let fetchContentURL = this.ApiCms.content(this.content_id_internal, this.version, this.sprache, this.sichtbar);
			if (this.$props.fetchContentURL)
				fetchContentURL = this.$props.fetchContentURL;

			this.$api
				.call(fetchContentURL)
				.then(res => {
					this.$nextTick(function() {
						this.content = res.data.content;
						this.content_type = res.data.type;
					});
				})
				.catch(res => {
					this.clearContentData();
				})
		},
		clearContentData() {
			this.content_type = null,
			this.content = null,
			this.content_id_internal = this.content_id
		}
	},
	watch:{
		sprache: function(sprache){
			this.fetchContent();
		},
		'$route.params.content_id'(newVal) {
			this.content_id_internal = newVal
			this.fetchContent();
		},
		content_id: function(newVal) {
			console.log(newVal)
			if (!newVal) this.clearContentData();

			this.content_id_internal = newVal
			this.fetchContent();
		},
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
				case "iframe":
					return "iframe_content";
				default:
					return "general";
			};
		},
		ApiCms() {
			return this.$props.initialApiFactory ?? DefaultApiCms;
		}
	},
	created() {
		this.fetchContent();
	},
	template: /*html*/ `
    <!-- div that contains the content -->
	<div id="fhc-cms-content" v-if="content">
    	<component ref="content" :is="computeContentType" :content="content" :content_id="content_id_internal" />
	</div>
    <p v-else>No content is available to display</p>
    `,
};
