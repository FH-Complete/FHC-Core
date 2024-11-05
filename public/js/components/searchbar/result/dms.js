import TemplateFrame from "./template/frame.js";

export default {
	components: {
		TemplateFrame
	},
	emits: [ 'actionexecuted' ],
	props: {
		res: Object,
		actions: Object
	},
	inject: [
		'query'
	],
	computed: {
		icon() {
			switch (this.res.mimetype) {
				case 'application/pdf':
					return 'file-pdf';
				case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
				case 'application/msword':
					return 'file-word';
				case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
				case 'application/mspowerpoint':
					return 'file-powerpoint';
				case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
				case 'application/vnd.ms-excel':
					return 'file-excel';
				case 'application/x-zip':
				case 'application/zip':
					return 'file-zipper';
				case 'image/jpeg':
				case 'image/gif':
				case 'image/png':
					return 'file-image';
				default:
					return 'file';
			}
		}
	},
	template: `
	<template-frame
		class="searchbar-result-dms"
		:res="res"
		:actions="actions"
		:title="res.name"
		:image-fallback="'fas fa-' + icon + ' fa-4x'"
		@actionexecuted="$emit('actionexecuted')"
		>
		<div class="searchbar_table">
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">DMS ID</div>
				<div class="searchbar_tablecell">
					{{ res.dms_id }}
				</div>
			</div>
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">Version</div>
				<div class="searchbar_tablecell">
					{{ res.version }}
				</div>
			</div>
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">Keywords</div>
				<div class="searchbar_tablecell">
					{{ res.keywords }}
				</div>
			</div>
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">Description</div>
				<div class="searchbar_tablecell">
					{{ res.description }}
				</div>
			</div>
		</div>
	</template-frame>`
};