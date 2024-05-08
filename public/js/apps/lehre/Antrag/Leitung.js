import StudierendenantragLeitung from '../../../components/Studierendenantrag/Leitung.js';
import Phrasen from '../../../plugin/Phrasen.js';

const app = Vue.createApp({
	components: {
		StudierendenantragLeitung
	}
});
app
	.use(Phrasen)
	.use(primevue.config.default,{zIndex: {overlay: 9999}})
	.mount('#wrapper');
