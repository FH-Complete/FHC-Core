import CmsAdmin from "../components/Cms/CmsAdmin.js";
import PluginsPhrasen from "../plugins/Phrasen.js";

const ciPath = FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/(https:|)(^|\/\/)(.*?\/)/g, '')
	+ FHC_JS_DATA_STORAGE_OBJECT.ci_router;

const router = VueRouter.createRouter({
	history: VueRouter.createWebHistory('/' + ciPath),
	routes: [
		{
			name: 'index',
			path: '/cms',
			component: CmsAdmin,
			children: [
				{
					name: 'content',
					path: 'content/:content_id/:sprache/:version/:tab',
					component: CmsAdmin
				}
			]
		},
		{ path: '/:pathMatch(.*)*', redirect: { name: 'index' } }
	]
});

FhcApps.router.makeExtendable(router);

const app = Vue.createApp({
	name: 'CmsApp'
});

FhcApps.makeExtendable(app);

app
	.use(router)
	.use(primevue.config.default, {
		zIndex: {
			overlay: 1100
		}
	})
	.use(PluginsPhrasen)
	.mount('#main');
