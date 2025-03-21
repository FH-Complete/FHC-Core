export default {
	data(){
		return {
			allActiveLanguages: FHC_JS_DATA_STORAGE_OBJECT.server_languages,
		}
	}, 
	emits: ['languageChanged'],
	methods:{
		changeLanguage: function(lang){
			if(this.allActiveLanguages.some(l => l.sprache === lang))
			{
				const isReload = document.querySelector('[cis4Reload]')
				this.$p.setLanguage(lang, this.$fhcApi)
				.then(res => res.data)
				.then(data =>
				{
					if(isReload) window.location.reload()
					else this.$emit('languageChanged', lang);
				})
			}
		},
	},
	template:/*html*/`
	<div class="container">
		<div class="row justify-content-center align-items-center flex-nowrap overflow-hidden">
			<button v-for="lang in allActiveLanguages" @click.prevent="changeLanguage(lang.sprache)" class="col fhc-text sprachen-entry btn text-center w-100" :selected="$p.user_language.value==lang.sprache">{{lang.bezeichnung}}</button>
		</div>
	</div>
	`,
};