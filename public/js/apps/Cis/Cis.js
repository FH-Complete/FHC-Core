import PluginsPhrasen from '../../plugins/Phrasen.js';
import Theme from '../../plugins/Theme.js';
import contrast from '../../directives/contrast.js';
import {setScrollbarWidth} from "../../helpers/CssVarCalcHelpers.js";

import ApiRouteInfo from '../../api/factory/routeinfo.js';
import {capitalize} from "../../helpers/StringHelpers.js";
import ApiAuthinfo from "../../api/factory/authinfo.js";

import {router} from "../../routers/Cis/CisRouter.js";

const app = Vue.createApp({
	name: 'CisApp',
	data: () => ({
		appSideMenuEntries: {},
		windowWidth: 0,
		isStudent: null,
		isMitarbeiter: null,
	}),
	provide() {
		return { // provide injectable & watchable language property
			language: Vue.computed(() => this.$p.user_language),
			isMobile: Vue.computed(() => this.windowWidth < 767),
			isStudent: Vue.computed(() => this.isStudent),
			isMitarbeiter: Vue.computed(() => this.isMitarbeiter)
		}	
	},
	methods: {
		isInternalRoute(href) {
			const internalBase = window.location.origin
			return href.startsWith(internalBase);
		},
		handleClick(event) {
			const target = event.target.closest('a');

			if(target?.id == 'skiplink') return
			if (target && this.isInternalRoute(target.href)) {
				const url = new URL(target.href)

				const path = url.pathname + url.search;
				const base = this.$router.options.history.base
				const route = path.replace(base, '') || '/'

				// let click event propagate normally if we dont route internally
				const res = this.$router.resolve(route)
				if(!res?.matched?.length || res.name === 'Fallback') return

				event.preventDefault(); // Prevent browser navigation

				if(this.isMobile) { // toggle the menu
					const navMain = document.getElementById('nav-main');
					// fix unwanted toggle from off to on for some links on mobile
					if(navMain.classList.contains('show')){
						document.getElementById('nav-main-btn').click();
					}
				}

				this.$router.push(route);

			}
		},
		handleWindowResize() {
			this.windowWidth = window.innerWidth;
		},
	},
	async created() {
		this.windowWidth = window.innerWidth;
		await this.$api.call(ApiAuthinfo.getAuthInfo()).then((res) => {
			this.isMitarbeiter = res.data.isMitarbeiter;
			this.isStudent = res.data.isStudent;
		});
	},
	async mounted() {
		document.addEventListener('click', this.handleClick);
		window.addEventListener("resize", this.handleWindowResize);
	},
	beforeUnmount() {
		document.removeEventListener('click', this.handleClick);
		window.removeEventListener("resize", this.handleWindowResize);
	},
});

// kind of a bandaid for bad css on some pages to avoid horizontal scroll
setScrollbarWidth();
app.config.globalProperties.$capitalize = capitalize;

FhcApps.router.makeExtendable(router);
FhcApps.makeExtendable(app);

app.use(router);
app.use(primevue.config.default, {
	zIndex: {
		overlay: 9000,
		tooltip: 8000
	}
})
app.directive('tooltip', primevue.tooltip);
app.use(PluginsPhrasen);
app.use(Theme);
app.directive('contrast', contrast);
app.mount('#fhccontent');

router.afterEach((to, from, failure) => {
	app.config.globalProperties.$api.call(ApiRouteInfo.info('cis4', to.fullPath));
});
