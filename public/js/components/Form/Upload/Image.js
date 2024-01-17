export default {
	emits: [
		'update:modelValue'
	],
	props: {
		modelValue: String
	},
	computed: {
		valueAsBase64DataString() {
			if (!this.modelValue || this.modelValue.substring(0, 10) == 'data:image')
				return this.modelValue;
			return 'data:image/jpeg;charset=utf-8;base64,' + this.modelValue;
		}
	},
	methods: {
		openUploadDialog() {
			this.$refs.fileInput.click();
		},
		pickFile() {
			let file = this.$refs.fileInput.files;
			if (file && file[0]) {
				let reader = new FileReader();
				reader.onload = e => {
					this.$emit('update:modelValue', e.target.result);
				}
				reader.readAsDataURL(file[0]);
			}
		},
		deleteImage() {
			this.$emit('update:modelValue', '');
		}
	},
	template: `
	<div class="form-upload-image">
		<template v-if="modelValue">
			<img class="img-thumbnail" :src="valueAsBase64DataString" />
			<div class="fotobutton">
				<div class="d-grid gap-2 d-md-flex">
					<button type="button" class="btn btn-outline-dark btn-sm" @click="deleteImage">
						<i class="fa fa-close"></i>
					</button>
					<button type="button" class="btn btn-outline-dark btn-sm" @click="openUploadDialog">
						<i class="fa fa-pen"></i>
					</button>
				</div>
			</div>
		</template>
		<template v-else>
			<slot>
				<svg class="bd-placeholder-img img-thumbnail" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="A generic square placeholder image with a white border around it, making it resemble a photograph taken with an old instant camera: 200x200" preserveAspectRatio="xMidYMid slice" focusable="false"><title>A generic square placeholder image with a white border around it, making it resemble a photograph taken with an old instant camera</title><rect width="100%" height="100%" fill="#868e96"></rect><text x="50%" y="50%" fill="#dee2e6" dy=".3em"></text></svg>
			</slot>
			<div class="fotobutton-visible">
				<div class="d-grid gap-2 d-md-flex">
					<button type="button" class="btn btn-outline-dark btn-sm" @click="openUploadDialog">
						<i class="fa fa-pen"></i>
					</button>
				</div>
			</div>
		</template>
		<input :id="$attrs.id" class="d-none" type="file" ref="fileInput" @input="pickFile" accept="image/*">
	</div>`
}