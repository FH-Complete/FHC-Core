export default {
	name: 'CisMenuLink',
	props: {
		href: {
			type: [String, null],
			default: null
		}
	},
	computed: {
		// Generaliserung von isRouterLink() aus CompatLinkHelpers.js:
		// prüft ALLE bekannten Routen, nicht nur Compat-URLs.
		resolvedRoute() {
			// Legacy-Seiten haben keinen Router (Menu.js mountet ohne)
			if (!this.href || !this.$router) return null;

			let path = this.href;

			// Menu-API liefert absolute URLs —> Pfad extrahieren, externe Links ignorieren
			if (path.startsWith('http')) {
				try {
					const url = new URL(path);
					if (url.origin !== window.location.origin) return null;
					path = url.pathname + url.search;
				} catch {
					return null;
				}
			}

			// Nur Pfade unterhalb der Router-Base kommen in Frage
			const base = this.$router.options.history.base;
			if (!path.startsWith(base)) return null;

			// Router die Route auflösen lassen
			const route = path.substring(base.length) || '/';
			const resolved = this.$router.resolve(route);

			if (!resolved?.matched?.length || resolved.name === 'Fallback') return null;

			return route;
		}
	},
	template: `
		<router-link v-if="resolvedRoute !== null" :to="resolvedRoute">
			<slot></slot>
		</router-link>
		<a v-else :href="href">
			<slot></slot>
		</a>
	`
};
