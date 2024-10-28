import Info from "../../components/Cis/Mylv/Semester/Studiengang/Lv/Info.js";
import Phrasen from "../../plugin/Phrasen.js";

Vue.createApp({
	components: {
		Info
	}
}).use(Phrasen, { reload: true }).mount('#content');