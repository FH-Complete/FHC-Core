import NewMessage from "../components/Messages/Details/NewMessage/NewDiv.js";
import FhcBase from "../plugins/FhcBase/FhcBase.js";

const ciPath = FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/(https:|)(^|\/\/)(.*?\/)/g, '') + FHC_JS_DATA_STORAGE_OBJECT.ci_router;

const router = VueRouter.createRouter({
	history: VueRouter.createWebHistory(),
	routes: [
		{ path: `/${ciPath}/NeueNachricht`, component: NewMessage, props: true },
		{ path: `/${ciPath}/NeueNachricht/:id/:typeId`, component: NewMessage, props: true },
		{ path: `/${ciPath}/NeueNachricht/:id/:typeId/:messageId`, component: NewMessage, props: true },
	]
});


const app = Vue.createApp();

app
	.use(router)
	.use(primevue.config.default, {
		zIndex: {
			overlay: 1100
		}
	})
	.use(FhcBase)
	.mount('#main');