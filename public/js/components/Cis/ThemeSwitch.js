export default {
	data:()=>{
		return{
			theme: null,
		}
	},
	methods:{
		switchTheme(nextTheme){
			this.theme = nextTheme;
			this.$theme.switchTheme(this.theme);
		},
		
	},
	computed:{
		nextTheme(){
			return this.$theme.theme_modes[(this.$theme.theme_modes.indexOf(this.theme) + 1) % this.$theme.theme_modes.length];
		},
	},
	created(){
		this.theme = localStorage.getItem('theme');
		if (!this.theme || !this.$theme.theme_modes.includes(this.theme)) {
			this.theme = this.$theme.theme_modes[0];
		}
	},
	template:/*html*/`

	<button id="themeSwitch" :aria-label="$p.t('global','switchTheme',[nextTheme])" @click="switchTheme(nextTheme)" class="fhc-primary-highlight-bg align-self-center btn btn-secondary rounded-5">
		<i v-if="theme == 'light'" class="fa-solid fa-sun " aria-hidden="true"></i>
		<i v-else-if="theme == 'dark'" class="fa-solid fa-moon " aria-hidden="true"></i>
		<i v-else-if="theme == 'contrast'" class="fa-solid fa-circle-half-stroke " aria-hidden="true"></i>
		<!--<i v-else-if="theme == 'purple'" class="fa-solid fa-wine-bottle" aria-hidden="true"></i>-->
	</button>
	`
}