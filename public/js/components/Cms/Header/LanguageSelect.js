export default {
	name: 'SpracheSelect',
	props: {
		languages: { type: Array, default: () => [] },
		sprache: String
	},
	emits: ['select-language'],
	template: `
		<div class="btn-group btn-group-sm" role="group">
			<button
				v-for="lang in languages"
				:key="lang"
				type="button"
				class="btn"
				:class="lang === sprache ? 'btn-primary' : 'btn-outline-secondary'"
				@click="$emit('select-language', lang)"
			>{{ lang }}</button>
		</div>
	`
};
