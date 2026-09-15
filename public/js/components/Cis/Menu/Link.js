import {isCompatLink, calcCompatRouterLink} from '../../../helpers/CompatLinkHelpers.js';

export default {
	name: 'CisMenuLink',
	props: {
		href: {
			type: [String, null],
			default: null
		}
	},
	methods: {
		isRouterHandled() {
			let isrouterhandled = this.$route.matched.length > 0;
			return isrouterhandled;
		},
		isCompatLink() {
			if(this.href === null) {
				return false;
			}
			return isCompatLink(this.href);
		},
		calcCompatRouterLink() {
			return calcCompatRouterLink(this.href);
		}
	},
	template: `
		<router-link v-if="this.isRouterHandled() && this.isCompatLink()"
			:to="this.calcCompatRouterLink()"
		>
			<slot></slot>
		</router-link>
		<a v-else 
			:href="this.href"
		>
			<slot></slot>
		</a>
	`
};