import {CoreRESTClient} from '../../../RESTClient.js';

export default {
	components: {
		TreeTable: primevue.treetable,
		TreeColumn: primevue.column
	},
	emits: [
		'selectVerband'
	],
	data() {
		return {
			loading: true,
			nodes: []
		}
	},
	methods: {
		onExpandTreeNode(node) {
			if (!node.children) {
				if (node.data.link) {
					this.loading = true;
					CoreRESTClient
						.get("components/stv/verband/" + node.data.link)
						.then(result => result.data)
						.then(result => {
							const subNodes = result.map(this.mapResultToTreeData);
							node.children = subNodes;
							this.loading = false;
						})
						.catch(error => {
							console.error(error);
						});
				}
			}
		},
		onSelectTreeNode(node) {
			if (node.data.link)
				this.$emit('selectVerband', 'components/stv/students/' + node.data.link);
		},
		mapResultToTreeData(el) {
			const cp = {
				key: ("" + el.link).replace('/', '-'),
				data: el
			};

			if (el.children)
				cp.children = el.children.map(this.mapResultToTreeData);
			else
				cp.leaf = el.leaf || false;

			return cp;
		}
	},
	mounted() {
		CoreRESTClient
			.get("components/stv/verband")
			.then(result => result.data)
			.then(result => {
				this.nodes = result.map(this.mapResultToTreeData);
				this.loading = false;
			})
			.catch(error => {
				console.error(error);
			});
	},
	template: `
	<tree-table class="stv-verband p-treetable-sm" :value="nodes" lazy @node-expand="onExpandTreeNode" selection-mode="single" @node-select="onSelectTreeNode" scrollable scroll-height="flex">
		<tree-column field="name" header="Verband" expander></tree-column>
	</tree-table>`
};