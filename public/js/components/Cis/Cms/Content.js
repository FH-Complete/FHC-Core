import raum_contentmittitel from './Content_types/Raum_contentmittitel.js'
import general from './Content_types/General.js'
import BsConfirm from "../../Bootstrap/Confirm.js";
import news_content from './Content_types/News_content.js';
import iframe_content from './Content_types/Iframe_content.js';

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
			this.$api
				.call(ApiCms.content(
					this.content_id_internal, this.version, this.sprache, this.sichtbar, this.preview
				))
				.then(res => {
					this.$nextTick(function() {
						this.content = res.data.content;
						this.content_type = res.data.type;
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
		// The viewer language, unless the address names one. The CMS admin previews a
		// chosen language, which is not always the language of the editor interface.
		sprache(){
			return this.$route?.query?.sprache || this.$p.user_language.value;
		},
		// A preview renders the content without counting a view.
		preview(){
			return this.$route?.query?.preview === '1';
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
