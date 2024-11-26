export default {
	data(){
		return {
			allActiveLanguages: null,
		}
	}, 
	methods:{
		changeLanguage: async function(lang){
			// only change the language if it is either German or English
			if(this.allActiveLanguages.some(l => l === lang))
			{
				await this.$p.setLanguage(lang, this.$fhcApi);
				// reloads the page after changing language to have an updated FHC_JS_DATA_STORAGE_OBJECT user_language value
				window.location.reload();
			}
			
		}
	},
	computed:{
		activeLanguage(){
			return FHC_JS_DATA_STORAGE_OBJECT.user_language ?? null;
		},
	},
	mounted(){
		//TODO: this method should be part of the FHC_JS_DATA_STORAGE_OBJECT, 
		//TODO: at the moment it always called when a refresh occurs, which is whenever a new Menu Punkt is selected
		this.$fhcApi.factory.phrasen.getActiveDbLanguages()
			.then(res => res.data)
			.then(
				(langs) => {
					this.allActiveLanguages = langs;
				}
			);
		/* function to get the active language 
		this.$fhcApi.factory.phrasen.getLanguage()
			.then(res => res.data)
			.then(
				(lang)=>
				{
					this.activeLanguage = lang;
				}
			); */
	},
	template:/*html*/`
	<div class="container text-white">
		<div class="row justify-content-center align-items-center">
			<div v-for="lang in allActiveLanguages" class="col fhc-entry" :selected="activeLanguage==lang?'':null">
				<div role="button" @click.prevent="changeLanguage(lang)" class="text-center w-100">{{lang}}</div>
			</div>
		</div>
	</div>
	`,
};