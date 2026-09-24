export default {
	name: 'SpracheSelect',
	props: {
		languages: { type: Array, default: () => [] },
		sprache: String
	},
	emits: ['select-language'],
	computed: {
		// Two languages read faster as a pair of buttons, and that is the case here. Other
		// institutions run this software with more content languages, and there the row of
		// buttons pushes the rest of the header off the line.
		alsAuswahl() {
			return this.languages.length > 2;
		}
	},
	template: `
		<select v-if="alsAuswahl"
			class="form-select form-select-sm w-auto cms-sprache-select"
			:title="$p.t('cms/sprache')"
			:value="sprache"
			@change="$emit('select-language', $event.target.value)"
		>
			<option v-for="lang in languages" :key="lang" :value="lang">{{ lang }}</option>
		</select>
		<div v-else class="btn-group btn-group-sm cms-sprache-select" role="group">
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
