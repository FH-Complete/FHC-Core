import CisMenu from "../../components/Cis/Menu.js";
import PluginsPhrasen from '../../plugins/Phrasen.js';
import Theme from "../../plugins/Theme.js";

import ApiAuthinfo from "../../api/factory/authinfo.js";

const app = Vue.createApp({
	name: 'CisMenuApp',
	components: {
		CisMenu,
	},
	data: () => ({
		windowWidth: 0,
		isStudent: null,
		isMitarbeiter: null,
	}),
	provide() {
		return {
			isNarrow: Vue.computed(() => this.windowWidth < 992),
			isMobile: Vue.computed(() => this.windowWidth < 767),
			isStudent: Vue.computed(() => this.isStudent),
			isMitarbeiter: Vue.computed(() => this.isMitarbeiter)
		}
	},
	methods: {
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
	mounted() {
		window.addEventListener("resize", this.handleWindowResize);
	},
	beforeUnmount() {
		window.removeEventListener("resize", this.handleWindowResize);
	},
});

FhcApps.makeExtendable(app);

app.use(primevue.config.default, {
	zIndex: {
		overlay: 9000,
		tooltip: 8000
	}
})
app.use(PluginsPhrasen);
app.use(Theme);

// On native CIS4 pages, CisApp handles the menu via Teleport.
// Menu.js only mounts on legacy pages that have no CisApp.
if (!document.getElementById('fhccontent')) {
	app.mount('#cis-header');
}
