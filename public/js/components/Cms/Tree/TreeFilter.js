export default {
	name: 'CmsTreeFilter',
	emits: ['filter'],
	data() {
		return {
			text: ''
		};
	},
	methods: {
		submit() {
			this.$emit('filter', this.text);
		}
	},
	template: `
		<div class="input-group input-group-sm">
			<input type="text"
				class="form-control"
				:placeholder="$p.t('cms/filterPlatzhalter')"
				v-model="text"
				@keydown.enter="submit">
			<button class="btn btn-outline-secondary" type="button" @click="submit">
				<i class="fa-solid fa-magnifying-glass"></i>
			</button>
		</div>
	`
};
