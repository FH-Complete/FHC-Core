import raum_contentmittitel from './Content_types/Raum_contentmittitel.js'
import general from './Content_types/General.js'


export default {
	name: "ContentComponent",
	
	props: {
		content_id: {
			type: Number,
			required: true,
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
		general,
	},
	data() {
		return {
			content: null,
			content_idInternal: this.content_id
		};
	},
	methods: {
		reload(id, api, context) {
			// to be called from app bound interceptor function that has access to the same api, but not via this
			context.content_idInternal = id
			this.load(api, context)
		},
		load(apiParam = null, context = this) {
			const api = apiParam ?? context.$fhcApi 
			api.factory.cms.content(context.content_idInternal, context.version, context.sprache, context.sichtbar).then(res => {
				context.content = res.data.content;
				context.content_type = res.data.type;

			});
		},
		fetchContent(){
			return this.$fhcApi.factory.cms.content(this.content_id, this.version, this.sprache, this.sichtbar).then(res => {
				this.content = res.data.content;
				this.content_type = res.data.type;
			});
		}
	},
	watch:{
		sprache: function(sprache){
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
				default:
					return "general";
			};
		},
	},
	created() {
		this.$fhcApi.factory.cms.content(this.content_id, this.version, this.sprache, this.sichtbar).then(res => {
			this.content = res.data.content;
			this.content_type = res.data.type;
		});
	},
	mounted() {

	},
	template: /*html*/ `
    <!-- div that contains the content -->
    <component ref="content" :is="computeContentType" v-if="content" :content="content" :content_id="content_idInternal" />
    <p v-else>No content is available to display</p>
    `,
};
