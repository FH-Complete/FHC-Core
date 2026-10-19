// only these types get a preview. html or svg in the iframe would run their scripts in the page origin
const IMAGE_TYPES = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];

const HEIGHTS = {
	small: '30vh',
	medium: '50vh',
	large: '80vh'
};

/**
 * Preview of one chosen file before the upload: an image as img, a pdf in the pdf viewer of the browser.
 * height: small, medium, large or a css length like '400px'.
 */
export default {
	props: {
		file: {
			type: File,
			required: true
		},
		height: {
			type: String,
			default: 'medium'
		}
	},
	data() {
		return {
			url: ''
		};
	},
	computed: {
		isImage() {
			return IMAGE_TYPES.includes(this.file.type);
		},
		// android chrome has no inline pdf viewer, there a pdf gets no preview
		isPdf() {
			return this.file.type == 'application/pdf' && navigator.pdfViewerEnabled !== false;
		},
		cssHeight() {
			return HEIGHTS[this.height] || this.height;
		}
	},
	watch: {
		file: {
			handler() {
				this.revokeUrl();
				// an object url only references the file, no base64 copy in memory
				if (this.isImage || this.isPdf)
					this.url = URL.createObjectURL(this.file);
			},
			immediate: true
		}
	},
	unmounted() {
		this.revokeUrl();
	},
	methods: {
		revokeUrl() {
			if (this.url)
				URL.revokeObjectURL(this.url);
			this.url = '';
		}
	},
	template: `
	<div v-if="url" class="form-upload-dms-preview mt-2">
		<img v-if="isImage" :src="url" :alt="file.name" class="img-fluid border rounded" :style="{ maxHeight: cssHeight }">
		<iframe v-else :src="url" :title="file.name" class="d-block w-100 border rounded" :style="{ height: cssHeight }"></iframe>
	</div>`
}
