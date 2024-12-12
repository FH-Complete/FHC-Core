export default {
	data(){
		return {
			allActiveLanguages: null,
			sprachenTranslation:null,
		}
	}, 
	methods:{
		changeLanguage: function(lang){
			if(this.allActiveLanguages.some(l => l === lang))
			{
				this.$p.setLanguage(lang, this.$fhcApi);
			}
		},
		getSprachenBezeichnung: function(lang){
			if (!Array.isArray(this.sprachenTranslation) || this.sprachenTranslation.length == 0) return;
			return this.sprachenTranslation.find(s=>s.sprache == lang)?.bezeichnung;
		},
	},
	mounted(){
		this.$fhcApi.factory.phrasen.getActiveDbLanguages()
			.then(res => res.data)
			.then(
				(langs) => {
					this.allActiveLanguages = langs.map(l=>l.sprache);
					this.sprachenTranslation = langs;
				}
		);
	},
	template:/*html*/`
	<div class="container">
		<div class="row justify-content-center align-items-center flex-nowrap overflow-hidden">
			<button v-for="lang in allActiveLanguages" @click.prevent="changeLanguage(lang)" class="col text-white fhc-entry btn text-center w-100" :selected="$p.user_language.value==lang?'':null">{{getSprachenBezeichnung(lang)}}</button>
		</div>
	</div>
	`,
};