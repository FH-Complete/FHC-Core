export default {
	data() {
		return {
			file: null,
		};
	},
	methods: {
		handleFileChange(event) {
			this.file = event.target.files[0];
		},
		uploadFile() {
			if (this.file) {
				// You can perform your file upload logic here
				console.log('Uploading file:', this.file);
				// Reset the file input
				this.$refs.fileInput.value = '';
				this.file = null;
			} else {
				console.error('No file selected');
			}
		},
	},
};
template: `
	<div>
		<h2>File Upload</h2>
		<form
		@submit.prevent="uploadFile">
		<input type="file" ref="fileInput" @change="handleFileChange" />
		<button type="submit">Upload</button>
	</form>
	<div v-if="file">
		<p>Selected File: {{file.name}}</p>
	</div>
</div>
</template>`