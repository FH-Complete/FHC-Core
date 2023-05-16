/**
 * Copyright (C) 2022 fhcomplete.org
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

/**
 *
 */
export default {
	props: {
		fields: Array,
		selected: {
			type: Array,
			default: []
		},
		names: {
			type: Array,
			default: []
		}
	},
	emits: [
		'hide',
		'show'
	],
	data: function() {
		return {
			selectedFields: []
		};
	},
	computed: {
		
	},
	watch: {
		selected(n) {
			this.selectedFields = n;
		}
	},
	methods: {
		toggle(field) {
			if (this.selectedFields.indexOf(field) != -1)
			{
				this.selectedFields.splice(this.selectedFields.indexOf(field), 1);
				this.$emit('hide', field);
			}
			else
			{
				this.selectedFields.push(field);
				this.$emit('show', field);
			}
		}
	},
	template: `
	<div class="filter-columns">
		<div class="card">
			<div class="row card-body filter-options-div">
				<div class="filter-fields-area">
					<div
						v-for="fieldToDisplay in fields"
						class="filter-fields-field"
						:class="selected.indexOf(fieldToDisplay) != -1 ? 'text-light bg-dark' : '' "
						@click="toggle(fieldToDisplay)"
					>
						{{ names[fieldToDisplay] || fieldToDisplay }}
					</div>
				</div>
			</div>
		</div>
	</div>
	`
};

