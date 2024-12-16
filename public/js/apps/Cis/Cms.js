import BsConfirm from "../../components/Bootstrap/Confirm.js";
import CmsNews from "../../components/Cis/Cms/News.js";
import CmsContent from "../../components/Cis/Cms/Content.js";
import Phrasen from "../../plugin/Phrasen.js";
import fhcapifactory from "../api/fhcapifactory.js";
Vue.$fhcapi = fhcapifactory;
import {setScrollbarWidth} from "../../helpers/CssVarCalcHelpers";
import FhcApi from "../../plugin/FhcApi";

const ciPath = FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/(https:|)(^|\/\/)(.*?\/)/g, '') + FHC_JS_DATA_STORAGE_OBJECT.ci_router;
console.log('ciPath', ciPath)
const router = VueRouter.createRouter({
	history: VueRouter.createWebHistory(`/${ciPath}/CisVue/Cms/`),
	routes: [
		{
			path: `/content/:content_id`,
			name: 'Content',
			component: CmsContent,
			props: true
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
			instance: null,
			interceptHandler: this.intercept//.bind(this)
		}
	},
	components: {
		CmsNews,
		CmsContent,
	},
	methods: {
		intercept(e) {
			if (e.target.tagName === "A" && e.target.pathname?.includes(ciPath) && e.target.pathname.includes('Cms/content')) {
				const pathParts = e.target.pathname.split('/').filter(Boolean);
				const idString = pathParts[pathParts.length - 1];
				const content_id = idString && !isNaN(Number(idString)) ? idString : null; // only return id if it is a number string since the path might contain invalid elements

				e.preventDefault() // prevents normal browser page load
                
				this.$router.push({ // add new content id to browser history
					name: 'Content',
					params: {
						content_id
					}
				})

                // load and show new content from id without reloading page with menu overhead etc.
                // from a generic reload function required by every affected child component
				this.instance.subTree.type.methods.reload(
					content_id,
					this.instance.appContext.config.globalProperties.$fhcApi,
					this.instance.subTree.component.proxy // this pointer of component reloading and setting its own content
				)

			} else if(e.target.tagName === "A" && e.target.pathname?.includes(ciPath) && e.target.pathname.includes('Cms/news')) {
				//handle news content
			}

		}
	},
	mounted() {
		this.instance = Vue.getCurrentInstance()
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

app.use(primevue.config.default, { zIndex: { overlay: 9999 } });
app.use(Phrasen);
app.mount("#cms");