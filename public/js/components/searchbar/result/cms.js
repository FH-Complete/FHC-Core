import TemplateFrame from "./template/frame.js";

export default {
	name: 'SearchbarResultCms',
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
		preview() {
			if (this.res.template_kurzbz != 'redirect') {
				let text = this.res.content.replace(/<!\[CDATA\[|\]\]>/ig, '').replace(/<[^>]+>/ig, '').replace(/^\s+|\s+$/g, '');

				if (text.length > 1000) {
					// NOTE(chris): focus on searched text!
					let lower = text.toLowerCase();
					let firstOccurence = Math.min(this.query.split(' ').reduce((a, c) => {
						// NOTE(chris): filter query for words that affects the content field and get the lowest index of them
						if (c == 'or')
							return a;
						let i = c.indexOf(':');
						if (i < 0 || (i > 0 && ['content', 'inhalt'].includes(c.split(':')[0]))) {
							let posInText = lower.indexOf(c);
							if (posInText >= 0)
								a.push(posInText);
						}
						return a;
					}, []));

					if (firstOccurence) {
						if (firstOccurence + 997 >= text.length) {
							firstOccurence = text.length - 997;
							if (firstOccurence > 0)
								return '...' + text.substr(firstOccurence, 997);
						} else {
							return '...' + text.substr(firstOccurence, 994) + '...';
						}
					}

					text = text.substr(0, 997) + '...';
				}
				
				return text;
			}
			
			let url = this.res.content_url;
			if (url.substr(0, 16) == '../index.ci.php/')
				url = this.$fhcApi.getUri(url.substr(16));
			else if (url.substr(0, 3) == '../')
				url = FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/\/+$/, '') + url.substr(2);
			return '<a href="' + url + '">' + url + '</a>';
		}
	},
	template: `
	<template-frame
		class="searchbar-result-cms"
		:res="res"
		:actions="actions"
		:title="res.title"
		image-fallback="fas fa-newspaper fa-4x"
		@actionexecuted="$emit('actionexecuted')"
		>
		<div v-if="preview" class="searchbar_table" v-html="preview"></div>
		<div v-else class="searchbar_table text-muted">
			{{ $p.t('search/result_content_none') }}
		</div>
	</template-frame>`
};