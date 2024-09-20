export default {
	emits: [
		'delete'
	],
	props: {
		modelValue: {
			type: [File, Object],
			required: true
		}
	},
	data() {
		return {
			preview: ''
		};
	},
	watch: {
		modelValue(n) {
			if (n.type == 'application/x.fhc-dms+json') {
				n.text().then(result => {
					const obj = JSON.parse(result);
					this.preview = obj.preview || '';
				});
			}
		}
	},
	template: `
	<li class="form-upload-dms-item">
		<span class="col-auto"><i class="fa fa-file me-1"></i></span>
		<span class="col">{{ modelValue.name }}</span>
		<a v-if="preview" :href="preview" target="_blank" class="col-auto btn btn-outline-secondary btn-p-0 me-1">
			<i class="fa fa-download"></i>
		</a>
		<button class="col-auto btn btn-outline-secondary btn-p-0" @click="$emit('delete')">
			<i class="fa fa-close"></i>
		</button>
	</li>`
}
