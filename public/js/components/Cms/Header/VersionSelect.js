// Versions shown on each side of the open one.
const WINDOW = 3;

export default {
	name: 'VersionSelect',
	props: {
		versions: { type: Array, default: () => [] },
		versionDetails: { type: Array, default: () => [] },
		version: Number
	},
	emits: ['select-version'],
	computed: {
		// A content can carry dozens of versions, and one button each breaks the header.
		// Show the open version with three neighbours on each side. The first and the last
		// stay reachable in one click, and a gap marks what the window leaves out.
		// Below the threshold the window would hide nothing, so show every version.
		items() {
			const all = this.versions;
			const active = all.indexOf(this.version);

			if (active === -1 || all.length <= WINDOW * 2 + 3)
				return all.map(value => ({ type: 'version', value }));

			const from = Math.max(0, active - WINDOW);
			const to = Math.min(all.length - 1, active + WINDOW);
			const items = [];

			if (from > 0) {
				items.push({ type: 'version', value: all[0] });
				// One version between the first and the window needs no gap marker.
				if (from > 1) items.push({ type: 'gap', count: from - 1 });
			}

			for (let i = from; i <= to; i++)
				items.push({ type: 'version', value: all[i] });

			if (to < all.length - 1) {
				if (to < all.length - 2)
					items.push({ type: 'gap', count: all.length - 2 - to });
				items.push({ type: 'version', value: all[all.length - 1] });
			}

			return items;
		},
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
			<template v-for="(item, idx) in items" :key="item.type + idx">
				<button
					v-if="item.type === 'version'"
					type="button"
					class="btn"
					:class="[
						item.value === version ? 'btn-primary' : 'btn-outline-secondary',
						{ 'opacity-50': !isSichtbar(item.value) }
					]"
					@click="$emit('select-version', item.value)"
				>{{ item.value }}</button>
				<button
					v-else
					type="button"
					class="btn btn-outline-secondary"
					disabled
					:title="$p.t('cms/weitereVersionen', [item.count])"
				>&hellip;</button>
			</template>
		</div>
	`
};
