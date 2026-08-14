export default {
	name: 'CisMenuLink',
	props: {
		href: {
			type: [String, null],
			default: null
		}
	},
	computed: {
		// generalizes isRouterLink() from CompatLinkHelpers.js:
		// checks ALL known routes, not just Compat URLs.
		resolvedRoute() {
			// legacy pages have no router (Menu.js mounts without one)
			if (!this.href || !this.$router) return null;

			let path = this.href;

			// menu API returns absolute URLs — extract path, ignore external links
			if (path.startsWith('http')) {
				try {
					const url = new URL(path);
					if (url.origin !== window.location.origin) return null;
					path = url.pathname + url.search;
				} catch {
					return null;
				}
			}

			// only paths below the router base are candidates
			const base = this.$router.options.history.base;
			if (!path.startsWith(base)) return null;

			// let the router resolve the route
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
