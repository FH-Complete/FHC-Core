export default {
	data:()=>{
		return{
			theme:'light',
			themes:['light','dark','purple'],
		}
	},
	methods:{
		toggleTheme(theme){
			if(!theme) return;

			for(const theme of this.themes){
				document.documentElement.classList.remove(theme);
			}
			document.documentElement.classList.add(theme);

			document.body.setAttribute("data-bs-theme", theme);

			let stylesheet = document.querySelector('link[href*="primevue/resources/themes"]');
			if(theme =="dark"){
				stylesheet.attributes.href.value = stylesheet.attributes.href.value.replace("bootstrap4-light-blue", "bootstrap4-dark-blue");
			}else if(theme =="light"){
				stylesheet.attributes.href.value = stylesheet.attributes.href.value.replace("bootstrap4-dark-blue", "bootstrap4-light-blue");
			} 

			localStorage.setItem("theme",theme);
			this.theme = theme;
		}
	},
	computed:{
		nextTheme(){
			return this.themes[(this.themes.indexOf(this.theme) + 1) % this.themes.length];
		},
	},
	mounted(){
		const theme =localStorage.getItem("theme");
		if(this.themes.includes(theme)){
			this.theme = theme;
		}
		this.toggleTheme(this.theme);

	},
	template:/*html*/`

	<button @click="toggleTheme(nextTheme)" class="fhc-primary-highlight-bg align-self-center btn btn-secondary rounded-5">
		<i v-if="theme == 'light'" class="fa-solid fa-moon fhc-text"></i>
		<i v-else-if="theme == 'dark'" class="fa-solid fa-sun fhc-text"></i>
		<i v-else-if="theme == 'purple'" class="fa-solid fa-wine-bottle"></i>
	</button>
	`
}