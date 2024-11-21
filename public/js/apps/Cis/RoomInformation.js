import Phrasen from "../../plugin/Phrasen.js";
import RoomInformation from "../../components/Cis/Mylv/RoomInformation.js";
import {setScrollbarWidth} from "../../helpers/CssVarCalcHelpers";

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

setScrollbarWidth();

app.use(Phrasen);
app.mount('#content');