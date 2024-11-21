import Info from "../../components/Cis/Mylv/Semester/Studiengang/Lv/Info.js";
import Phrasen from "../../plugin/Phrasen.js";
import {setScrollbarWidth} from "../../helpers/CssVarCalcHelpers";

const app = Vue.createApp({
	components: {
		Info
	}
})

setScrollbarWidth();

app.use(Phrasen, { reload: true }).mount('#content');