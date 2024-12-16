import BsConfirm from "../../components/Bootstrap/Confirm.js";
import CmsNews from "../../components/Cis/Cms/News.js";
import CmsContent from "../../components/Cis/Cms/Content.js";
import Phrasen from "../../plugin/Phrasen.js";
import fhcapifactory from "../api/fhcapifactory.js";
Vue.$fhcapi = fhcapifactory;
import {setScrollbarWidth} from "../../helpers/CssVarCalcHelpers";
import FhcApi from "../../plugin/FhcApi";

const ciPath = FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/(https:|)(^|\/\/)(.*?\/)/g, '') + FHC_JS_DATA_STORAGE_OBJECT.ci_router;
const router = VueRouter.createRouter({
	history: VueRouter.createWebHistory(`/${ciPath}/CisVue/Cms/`),
	routes: [
		{
			path: `/content/:content_id`,
			name: 'Content',
			component: CmsContent,
			props: (route) => ({ content_id: route.params.content_id })
		},
		{
			path: `/news`,
			name: 'News',
			component: CmsNews,
		}
	]
})


const app = Vue.createApp({
	name: 'CmsApp',
	data() {
		return {
			interceptHandler: this.intercept
		}
	},
	components: {
		CmsNews,
		CmsContent,
	},
	methods: {
		intercept(e) {
			// TODO: maybe a more sophisticated menu link click detection is possible?
			if (e.target.tagName === "A" && e.target.pathname?.includes(ciPath) && e.target.pathname.includes('Cms/content')) {
				const pathParts = e.target.pathname.split('/').filter(Boolean);
				const idString = pathParts[pathParts.length - 1];
				const content_id = idString && !isNaN(Number(idString)) ? idString : null; // only return id if it is a number string since the path might contain invalid elements

				e.preventDefault() // prevents normal browser page load
				this.$router.push({
					name: 'Content',
					params: {
						content_id
					}
				})

			} else if(e.target.tagName === "A" && e.target.pathname?.includes(ciPath) && e.target.pathname.includes('Cms/news')) {
				//handle news content
				
				e.preventDefault() // prevents normal browser page load
				this.$router.push({
					name: 'News',

				})
			}

		}
	},
	mounted() {
		document.querySelectorAll("#cms [data-confirm]").forEach((el) => {
			el.addEventListener("click", (evt) => {
				evt.preventDefault();
				BsConfirm.popup(el.dataset.confirm)
					.then(() => {
						Axios.get(el.href)
							.then((res) => {
								// TODO(chris): check for success then show message and/or reload
								location = location;
							})
							.catch((err) => console.error("ERROR:", err));
					})
					.catch(() => {
					});
			});
		});
		document.querySelectorAll("#cms [data-href]").forEach((el) => {
			el.href = el.dataset.href.replace(
				/^ROOT\//,
				FHC_JS_DATA_STORAGE_OBJECT.app_root
			);
		});
		document.addEventListener("click", this.interceptHandler, {capture: true, passive: false})
	},
	unmounted() {
		document.removeEventListener('click', this.interceptHandler)
	}
});

setScrollbarWidth();

app.use(router);
app.use(FhcApi);
app.use(primevue.config.default, { zIndex: { overlay: 9999 } });
app.use(Phrasen);
app.mount("#cms");