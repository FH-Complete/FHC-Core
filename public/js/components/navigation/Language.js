/**
 * Copyright (C) 2025 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

export default {
	emits: [
		'changed'
	],
	props: {
		activeClass: {
			type: String,
			default: 'active'
		},
		itemClass: {
			type: [String, Array, Object],
			default: ''
		}
	},
	data() {
		return {
			languages: FHC_JS_DATA_STORAGE_OBJECT.server_languages
		};
	}, 
	methods:{
		onChange(lang) {
			if (this.languages.some(l => l.sprache === lang)) {
				this.$p
					.setLanguage(lang)
					.then(() => {
						if (document.querySelector('[cis4Reload]'))
							window.location.reload();
						else
							this.$emit('changed', lang);
					});
			}
		}
	},
	template: /*html*/`
	<div class="navigation-language d-flex justify-content-center align-items-center flex-nowrap overflow-hidden">
		<button
			v-for="lang in languages"
			:class="[itemClass, {[activeClass]: $p.user_language.value == lang.sprache}]"
			:selected="$p.user_language.value == lang.sprache"
			@click.prevent="onChange(lang.sprache)"
		>
			{{ lang.bezeichnung }}
		</button>
	</div>`
};