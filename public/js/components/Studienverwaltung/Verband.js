import {CoreRESTClient} from '../../RESTClient.js';

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
				let url = '';
				if (node.data.studiengang_kz) {
					url = "getStudiengang/" + node.data.studiengang_kz;
				}

				if (url) {
					this.loading = true;
					CoreRESTClient
						.get("components/Studentenverwaltung/" + url)
						.then(result => {
							const subNodes = result.data.map(this.mapResultToTreeData);
							node.children = subNodes;
							this.loading = false;
						});
				}
			}
		},
		onSelectTreeNode(node) {
			if (node.link)
				this.$emit('selectVerband', node.link);
		},
		mapResultToTreeData(el) {
			const cp = {
				data: el
			};
			if (el.studiengang_kz !== undefined) {
				cp.key = el.studiengang_kz;
				cp.data.name = el.kurzbzlang + ' (' + (el.typ + el.kurzbz).toUpperCase() + ') - ' + el.bezeichnung;
				cp.leaf = false;
				cp.link = 'components/Studentenverwaltung/getStudents/' + el.studiengang_kz;
			}
			if (el.children)
				cp.children = el.children.map(this.mapResultToTreeData);
			else
				cp.leaf = el.leaf || false;
			return cp;
		}
	},
	mounted() {
		CoreRESTClient
			.get("components/Studentenverwaltung")
			.then(result => result.data)
			.then(result => {
				if(CoreRESTClient.isError(result)) {
					console.error(CoreRESTClient.getError(result));
				} else if (CoreRESTClient.hasData(result)) {
					this.nodes = CoreRESTClient.getData(result).map(this.mapResultToTreeData);
				}
				this.loading = false;
			});
	},
	template: `
	<tree-table class="stv-verband p-treetable-sm" :value="nodes" lazy @node-expand="onExpandTreeNode" selection-mode="single" @node-select="onSelectTreeNode" scrollable scroll-height="flex">
		<tree-column field="name" header="Verband" expander></tree-column>
	</tree-table>`
};