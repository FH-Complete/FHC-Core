import StudierendenantragLeitung from '../../../components/Studierendenantrag/Leitung.js';
import FhcBase from "../../../plugins/FhcBase/FhcBase.js";

const app = Vue.createApp({
	name: 'LeitungApp',
	components: {
		StudierendenantragLeitung
	}
});
app
	.use(FhcBase)
	.use(primevue.config.default,{zIndex: {overlay: 9999}})
	.mount('#wrapper');