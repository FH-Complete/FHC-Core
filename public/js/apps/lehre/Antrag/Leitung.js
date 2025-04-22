import StudierendenantragLeitung from '../../../components/Studierendenantrag/Leitung.js';
import PluginsPhrasen from '../../../plugins/Phrasen.js';

const app = Vue.createApp({
	name: 'LeitungApp',
	components: {
		StudierendenantragLeitung
	}
});
app
	.use(PluginsPhrasen)
	.use(primevue.config.default,{zIndex: {overlay: 9999}})
	.mount('#wrapper');