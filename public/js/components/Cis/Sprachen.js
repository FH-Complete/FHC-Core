export default {
	data(){
		return {
			allActiveLanguages: null,
		}
	}, 
	methods:{
		changeLanguage: function(lang){
			if(this.allActiveLanguages.some(l => l === lang))
			{
				this.$p.setLanguage(lang, this.$fhcApi);
			}
		},
	},
	mounted(){
		this.$fhcApi.factory.phrasen.getActiveDbLanguages()
			.then(res => res.data)
			.then(
				(langs) => {
					this.allActiveLanguages = langs;
				}
		);
	},
	template:/*html*/`
	<div class="container flex-shrink-0">
		<div class="row justify-content-center align-items-center">
			<button v-for="lang in allActiveLanguages" @click.prevent="changeLanguage(lang)" class="col text-white fhc-entry btn text-center w-100" :selected="$p.user_language.value==lang?'':null">{{lang}}</button>
		</div>
	</div>
	`,
};