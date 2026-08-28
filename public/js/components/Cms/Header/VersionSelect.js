export default {
	name: 'VersionSelect',
	props: {
		versions: { type: Array, default: () => [] },
		versionDetails: { type: Array, default: () => [] },
		version: Number
	},
	emits: ['select-version'],
	computed: {
		sichtbarMap() {
			const map = {};
			for (const v of this.versionDetails) {
				map[v.version] = v.sichtbar;
			}
			return map;
		}
	},
	methods: {
		isSichtbar(ver) {
			return this.sichtbarMap[ver] !== false;
		}
	},
	template: `
		<div class="btn-group btn-group-sm" role="group">
			<button
				v-for="ver in versions"
				:key="ver"
				type="button"
				class="btn"
				:class="[
					ver === version ? 'btn-primary' : 'btn-outline-secondary',
					{ 'opacity-50': !isSichtbar(ver) }
				]"
				@click="$emit('select-version', ver)"
			>{{ ver }}</button>
		</div>
	`
};
