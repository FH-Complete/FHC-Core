export default {
	emits: [
		'update:modelValue'
	],
	props: {
		modelValue: {
			type: [ FileList, Array ],
			required: true
		},
		multiple: Boolean,
		id: String,
		name: String,
		inputClass: [String, Array, Object],
		noList: Boolean,
		accept: {
			type: String,
			default: ''
		}
	},
	methods: {
		stringifyFile(file) {
			return JSON.stringify({
				lastModified: file.lastModified,
				lastModifiedDate: file.lastModifiedDate,
				name: file.name,
				size: file.size,
				type: file.type
			});
		},
		addFiles(event) {
			if (!this.multiple)
				return this.$emit('update:modelValue', event.target.files);
			
			const dt = new DataTransfer();
			const doubles = [];
			for (var file of this.modelValue) {
				dt.items.add(file);
				doubles.push(this.stringifyFile(file));
			}
			for (var file of event.target.files) {
				// NOTE(chris): deep check (with FileReader) would require an async function so we only check the basic attributes
				if (doubles.indexOf(this.stringifyFile(file)) < 0)
					dt.items.add(file);
			}
			this.$emit('update:modelValue', dt.files);
		},
		removeFile(id) {
			const fileToRemove = Array.from(this.modelValue)[id];
			
			const dt = new DataTransfer();
			for (var file of this.modelValue) {
				if (file !== fileToRemove)
					dt.items.add(file);
			}
			this.$emit('update:modelValue', dt.files);
		}
	},
	watch: {
		modelValue(n) {
			if (n instanceof FileList)
				return this.$refs.upload.files = n;

			const dt = new DataTransfer();
			const dms = [];
			for (var file of n) {
				if (file instanceof File) {
					dt.items.add(file);
				} else {
					const dmsFile = new File([JSON.stringify(file)], file.name, {
						type: 'application/x.fhc-dms+json'
					});
					dt.items.add(dmsFile);
				}
			}
			this.$emit('update:modelValue', dt.files);
		}
	},

	template: `
	<div class="form-upload-dms">
		<input ref="upload" class="form-control" :accept="accept" :class="inputClass" :id="id" :name="name" :multiple="multiple" type="file" @change="addFiles">
		<ul v-if="modelValue.length && multiple && !noList" :accept="accept" class="list-unstyled m-0">
			<li v-for="(file, index) in modelValue" :key="index" class="d-flex mx-1 mt-1 align-items-start">
				<span class="col-auto"><i class="fa fa-file me-1"></i></span>
				<span class="col">{{ file.name }}</span>
				<button class="col-auto btn btn-outline-secondary btn-p-0" @click="removeFile(index)">
					<i class="fa fa-close"></i>
				</button>
			</li>
		</ul>
	</div>`
}