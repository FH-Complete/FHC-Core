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
		<router-link v-if="this.isCompatLink()"
			:to="this.calcCompatRouterLink()"
		>
			<slot></slot> (routerlink)
		</router-link>
		<a v-else 
			:href="this.href"
		>
			<slot></slot> (ahref)
		</a>
	`
};