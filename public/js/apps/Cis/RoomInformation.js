import Phrasen from "../../plugin/Phrasen.js";
import RoomInformation from "../../components/Cis/Mylv/RoomInformation.js";

const app = Vue.createApp({
	name: 'RoomInformationApp',
	components: {
        RoomInformation
	},
	data() {
		return {
		}
	}
});
app.config.unwrapInjectedRef = true;
app.use(Phrasen);
app.mount('#content');