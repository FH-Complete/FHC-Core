import LvZuweisung from '../../../components/Studierendenantrag/Lvzuweisung.js';

const app = Vue.createApp({
	components: {
		LvZuweisung
	},
	computed: {
		notinframe() {
			return window.self === window.top;
		}
	}
});
app.mount('#wrapper');
