import MylvStudent from "../../../components/Cis/Mylv/Student.js";
import Phrasen from "../../../plugin/Phrasen.js";

Vue.createApp({
	components: {
		MylvStudent
	}
}).use(Phrasen, {reload: true}).mount('#content');