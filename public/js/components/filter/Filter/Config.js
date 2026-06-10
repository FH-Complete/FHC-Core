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

const FILTER_COMPONENT_NEW_FILTER = 'Filter Component New Filter';

/**
 *
 */
export default {
	props: {
		filters: {
			type: Array,
			default: []
		},
		columns: {
			type: Array,
			default: []
		},
		fields: {
			type: Array,
			default: []
		}
	},
	emits: {
		switchFilter: ['filterId'],
		applyFilterConfig: ['filterFields'],
		saveCustomFilter: ['customFilterName']
	},
	data: function() {
		return {
			currentFields: []
		};
	},
	computed: {
		types() {
			return this.columns.reduce((a,c) => {
				let type = c.type.toLowerCase();
				if (type.indexOf('int') >= 0)
					a[c.name] = 'Numeric';
				else if (
					type.indexOf('varchar') >= 0 || 
					type.indexOf('text') >= 0 || 
					type.indexOf('bpchar') >= 0
				)
					a[c.name] = 'Text';
				else if (
					type.indexOf('timestamp') >= 0 || 
					type.indexOf('date') >= 0
				)
					a[c.name] = 'Date';
				else
					a[c.name] = '';
				return a;
			}, {});
		}
	},
	watch: {
		fields(n) {
			this.currentFields = n;
		}
	},
	methods: {
		switchFilter(evt) {
			this.$emit('switchFilter', evt.currentTarget.value);
		},
		applyFilterConfig() {
			const filteredFields = this.currentFields.filter(el => el.name != FILTER_COMPONENT_NEW_FILTER);
			if (filteredFields.filter(el => el.condition == "").length)
				alert("Please fill all the filter options");
			else
				this.$emit('applyFilterConfig', filteredFields);
		},
		addField(evt) {
			this.currentFields.push({
				name: FILTER_COMPONENT_NEW_FILTER
			});
		},
		removeField(index) {
			this.currentFields.splice(index, 1);
		}
	},
	template: `
	<div class="filter-config">
		<div class="card">
			<div class="card-body">
				<div v-if="filters.length" class="mb-4">
					<select class="form-select" @change="switchFilter">
						<option value="">Bitte auswählen...</option>
						<option v-for="filter in filters" :value="filter.id">
							{{ filter.description }}
						</option>
					</select>
				</div>
				<div>
					<button class="btn btn-outline-dark" type="button" @click=addField>
						<span class="fa-solid fa-plus" aria-hidden="true"></span> Neuer Filter
					</button>
				</div>
				<div class="filter-config-fields my-3">
					<div v-for="(filterField, index) in currentFields" class="filter-config-field row">

						<div class="col-5">
							<div class="input-group">
								<span class="input-group-text">Filter {{ index + 1 }}</span>
								<select
									class="form-select"
									v-model="filterField.name"
								>
									<option value="">Feld zum Filter hinzufügen...</option>
									<option v-for="col in columns" :value="col.name">
										{{ col.title }}
									</option>
								</select>
							</div>
						</div>

						<!-- Numeric -->
						<template v-if="types[filterField.name] == 'Numeric'">
							<div class="col-2">
								<select class="form-select" v-model="filterField.operation">
									<option value="equal">Gleich</option>
									<option value="nequal">Nicht gleich</option>
									<option value="gt">Größer als</option>
									<option value="lt">Weniger als</option>
								</select>
							</div>
							<div class="col-3">
								<input type="number" class="form-control" v-model="filterField.condition">
							</div>
						</template>

						<!-- Text -->
						<template v-if="types[filterField.name] == 'Text'">
							<div class="col-2">
								<select class="form-select" v-model="filterField.operation">
									<option value="equal">Gleich</option>
									<option value="nequal">Nicht gleich</option>
									<option value="contains">Enthält</option>
									<option value="ncontains">Enthält nicht</option>
								</select>
							</div>
							<div class="col-3">
								<input type="text" class="form-control" v-model="filterField.condition">
							</div>
						</template>

						<!-- Timestamp and date -->
						<template v-if="types[filterField.name] == 'Date'">
							<div class="col-2">
								<select class="form-select" v-model="filterField.operation">
									<option value="gt">Größer als</option>
									<option value="lt">Weniger als</option>
									<option value="set">Eingestellt ist</option>
									<option value="nset">Eingestellt nicht ist</option>
								</select>
							</div>
							<div class="col-1">
								<input type="number" class="form-control" v-model="filterField.condition">
							</div>
							<div class="col-2">
								<select class="form-select" v-model="filterField.option">
									<option value="minutes">Minuten</option>
									<option value="hours">Stunden</option>
									<option value="days">Tage</option>
									<option value="months">Monate</option>
								</select>
							</div>
						</template>

						<div class="col text-end">
							<button
								class="btn btn-outline-dark"
								type="button"
								@click="removeField(index)"
								title="Filter entfernen"
								aria-title="Filter entfernen"
							>
								<span class="fa-solid fa-minus" aria-hidden="true"></span>
							</button>
						</div>
					</div>
				</div>

				<!-- Filter save options -->
				<div class="row">
					<div class="col-7">
						<div class="input-group">
							<input ref="filterName" type="text" class="form-control" placeholder="Filternamen eingeben...">
							<button type="button" class="btn btn-outline-secondary" @click="$emit('saveCustomFilter', $refs.filterName.value)">Filter speichern</button>
						</div>
					</div>
					<div class="col">
						<button type="button" class="btn btn-outline-dark" @click="applyFilterConfig">Filter anwenden</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	`
};

