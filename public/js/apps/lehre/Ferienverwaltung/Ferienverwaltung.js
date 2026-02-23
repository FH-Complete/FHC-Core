import Ferienverwaltung from '../../../components/Ferienverwaltung/Ferienverwaltung.js';
import PluginsPhrasen from '../../../plugins/Phrasen.js';

const app = Vue.createApp({
	name: 'FerienverwaltungApp',
	components: {
		Ferienverwaltung
	}
});
app
	.use(PluginsPhrasen)
	.mount('#main');