import { components } from './components.js';
import { scanContentcomponents } from './scan.js';

/**
 * Renders CMS content HTML and mounts the content components it declares.
 *
 * Use it in place of a plain `<div v-html="content">`. It owns the root element, the
 * change watch and the Teleports, so a host site needs no ref and no lifecycle code.
 *
 * Teleport, not a separate createApp: the components stay in this app's component tree.
 * They therefore share the one $api instance, the one $p instance, the router and the
 * provided keys. A createApp per marker would build a second of each.
 */
export default {
	name: 'ContentcomponentHost',
	props: {
		content: {
			type: String,
			default: ''
		},
		// The page this content belongs to. contentchild-menu needs it when the editor
		// gave no explicit id.
		contentId: {
			type: [Number, String],
			default: null
		}
	},
	provide() {
		// Vue.computed keeps the value live, the same way Cis.js provides its keys.
		return {
			contentcomponentContentId: Vue.computed(() => this.contentId)
		};
	},
	data() {
		return {
			markers: []
		};
	},
	methods: {
		rescan() {
			this.markers = scanContentcomponents(this.$refs.root);
		},
		resolve(name) {
			return components[name];
		}
	},
	watch: {
		// Vue replaces the whole v-html subtree when the string changes. A marker that
		// still points at a removed node makes Teleport write into a detached element,
		// and the component vanishes without an error. Drop the markers first, let the
		// new HTML render, then scan again.
		content() {
			this.markers = [];
			this.$nextTick(this.rescan);
		}
	},
	// A failing content component must not take down the content around it.
	errorCaptured(err, instance, info) {
		console.error('Contentcomponent failed:', info, err);
		return false;
	},
	mounted() {
		this.rescan();
	},
	beforeUnmount() {
		this.markers = [];
	},
	template: /*html*/ `
		<div ref="root" v-html="content"></div>
		<Teleport v-for="marker in markers" :key="marker.key" :to="marker.el">
			<component :is="resolve(marker.name)" v-bind="marker.props"></component>
		</Teleport>
	`
};
