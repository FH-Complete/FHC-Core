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

const DEFAULT_ICONS = {
	jsonLines: 'fa-file-lines',
	xlsx: 'fa-file-excel',
	pdf: 'fa-file-pdf',
	html: 'fa-file-code',
	json: 'fa-file',
	csv: 'fa-file-csv'
};
const DEFAULT_LABELS = {
	jsonLines: 'Download as JSONLINES',
	xlsx: 'Download as XLSX',
	pdf: 'Download as PDF',
	html: 'Download as HTML',
	json: 'Download as JSON',
	csv: 'Download as CSV '
};


/**
 *
 */
export default {
	props: {
		tabulator: Object,
		config: {
			type: [Boolean, String, Function, Array, Object],
			default: false
		},
		iconClass: [String, Array, Object]
	},
	computed: {
		currentConfig() {
			if (!this.config)
				return false;

			let config = this.config;

			if (config instanceof Function)
				return [config];

			if (config === null)
				return [];

			if (this.config === true)
				config = ['csv'];

			if (Object.prototype.toString.call(config) === "[object String]")
				config = config.split(',');

			if (typeof config === 'object' && !Array.isArray(config)) {
				let newConfig = [];
				for (var k in config) {
					var v = config[k], type;
					
					if (!v)
						continue;
					
					if (Object.prototype.toString.call(v) === "[object String]") {
						type = this.stringToFileFormatter(v);
						if (type !== null) {
							newConfig.push({
								icon: 'fa-solid ' + DEFAULT_ICONS[type],
								label: v === k ? DEFAULT_LABELS[type] : k,
								formatter: type
							});
						} else {
							type = this.stringToFileFormatter(k);
							if(type !== null) {
								newConfig.push({
									icon: 'fa-solid ' + DEFAULT_ICONS[type],
									label: v,
									formatter: type
								});
							} else {
								alert('neither ' + k + ' nor ' + v + ' are supported download file types');
							}
						}
					} else if (typeof v === 'object' && !Array.isArray(v)) {
						type = this.stringToFileFormatter(k);
						if (type !== null) {
							if (v.formatter === undefined)
								v.formatter = type;
							if (v.label === undefined)
								v.label = DEFAULT_LABELS[type];
							if (v.icon === undefined)
								v.icon = DEFAULT_ICONS[type];
							newConfig.push(v);
						} else {
							if (v.label === undefined)
								v.label = k;
							newConfig.push(v);
						}
					} else {
						type = this.stringToFileFormatter(k);
						if (type !== null) {
							newConfig.push({
								icon: 'fa-solid ' + DEFAULT_ICONS[type],
								label: DEFAULT_LABELS[type],
								formatter: type
							});
						} else {
							alert(k + ' is not a supported download file type');
						}
					}
				}
				config = newConfig;
			}

			if (Array.isArray(config))
			{
				config = config.map(el => {
					if (Object.prototype.toString.call(el) === "[object String]") {
						let formatter = this.stringToFileFormatter(el);
						if (formatter === null)
							return null;
						return {
							icon: 'fa-solid ' + DEFAULT_ICONS[formatter],
							label: DEFAULT_LABELS[formatter],
							formatter
						};
					}
					
					if (el instanceof Function)
						return {
							formatter: el
						}
					
					if (typeof el === 'object' && !Array.isArray(el) && el !== null) {
						if (el.formatter instanceof Function)
							return el;
						if (this.validateFileFormatter(el.formatter))
							return el;
					}
					
					return null;
				}).filter(el => el !== null);

				if (config.length < 2)
					return config;

				if (config.filter(el => el.label || el.icon).length == config.length)
					return config;

				alert('Config not valid');
			}

			return [];
		}
	},
	methods: {
		stringToFileFormatter(input) {
			let lcInput = input.toLowerCase();
			
			if (lcInput == 'jsonlines')
				return 'jsonLines';
			
			if (['xlsx', 'pdf', 'html', 'json', 'csv'].includes(lcInput))
				return lcInput;

			return null;
		},
		validateFileFormatter(input) {
			let formatter = this.stringToFileFormatter(input);
			if (!formatter) {
				alert(input + ' is not a supported file formatter');
				return false;
			}
			if (formatter == 'xlsx') {
				if (!window.XLSX) {
					alert('XLSX Library not loaded');
					return false;
				}
			}
			if (formatter == 'pdf') {
				if (!window.jspdf) {
					alert('jsPDF Library not loaded');
					return false;
				}
				var doc = new jspdf.jsPDF({});
				if (!doc.autoTable) {
					alert('jsPDF-AutoTable Plugin not loaded');
					return false;
				}
			}
			return true;
		},
		download(config) {
			this.tabulator.download(config.formatter, config.file, config.options)
		}
	},
	template: `
	<template v-if="currentConfig">
		<template v-if="currentConfig.length == 1">
			<a
				href="#"
				class="table-download"
				v-bind="$attrs"
				title="Download"
				aria-title="Download"
				@click.prevent="download(currentConfig[0])"
			>
				<span :class="iconClass || 'fa-solid fa-xl fa-download'" aria-hidden="true"></span>
			</a>
		</template>
		<div v-else class="dropdown d-inline">
			<a
				href="#"
				class="table-download"
				v-bind="$attrs"
				title="Download"
				aria-title="Download"
				role="button"
				data-bs-toggle="dropdown"
				aria-expanded="false"
			>
				<span :class="iconClass || 'fa-solid fa-xl fa-download'" aria-hidden="true"></span>
			</a>
			<ul class="dropdown-menu dropdown-menu-end">
				<li v-for="(conf, i) in currentConfig" :key="i">
					<a class="dropdown-item" href="#" @click.prevent="download(conf)">
						<span v-if="conf.icon" :class="conf.icon" aria-hidden="true"></span>
						{{conf.label}}
					</a>
				</li>
			</ul>
		</div>
	</template>
	`
};

