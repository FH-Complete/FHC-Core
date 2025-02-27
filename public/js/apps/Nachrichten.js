import NewMessage from "../components/Messages/Details/NewMessage/NewDiv.js";

import Phrasen from "../plugin/Phrasen.js";

const ciPath = FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/(https:|)(^|\/\/)(.*?\/)/g, '') + FHC_JS_DATA_STORAGE_OBJECT.ci_router;

const router = VueRouter.createRouter({
	history: VueRouter.createWebHistory(),
	routes: [
		{ path: `/${ciPath}/NeueNachricht/:id/:typeId`, component: NewMessage },
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
	.use(Phrasen)
	.mount('#main');