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

import ApiTabulatorPresets from "../../../api/factory/tabulatorPresets.js";

/**
 *
 */
export default {
	name: "TablePresets",
	props: {
		presetsId: { type: String },
		tabulator: { type: Object },
		generalPresets: { type: Array, default: [] },
	},
	emits: ["applyTablePreset"],
	data: function () {
		return {
			customUserPresets: [],
			presetInfo: null,
			newPreset: null,
			newPresetName: "",
		};
	},
	computed: {
		allPresets() {
			return this.$props.generalPresets.concat(this.customUserPresets);
		},
		presetInfoFormattedStrings() {
			if (!this.presetInfo) return null;

			const columnTitles = this.getColumnTitles();

			let displayedColumns =
				this.presetInfo.displayedColumns?.map(
					(columnField) => columnTitles[columnField] ?? "",
				) ?? [];
			displayedColumns = displayedColumns.filter(
				(string) => string.length,
			);

			let headerFilters = this.presetInfo.headerFilters
				? Object.keys(this.presetInfo.headerFilters).map((column) =>
						columnTitles[column]
							? columnTitles[column] +
								'["' +
								this.presetInfo.headerFilters[column] +
								'"]'
							: "",
					)
				: [];
			headerFilters = headerFilters.filter((string) => string.length);

			const sortColumn = this.presetInfo.sort?.column
				? columnTitles[this.presetInfo.sort.column]
				: null;
			const sortDirection =
				this.presetInfo.sort?.direction === "desc"
					? this.$p.t("tabulator_presets/desc")
					: this.$p.t("tabulator_presets/asc");

			return {
				displayedColumns:
					this.$p.t("tabulator_presets/displayed_columns") +
					" (" +
					this.$p.t("tabulator_presets/in_order") +
					"): " +
					displayedColumns.join(", "),
				headerFilters:
					this.$p.t("tabulator_presets/column_header_filters") +
					": " +
					headerFilters.join(", "),
				sort:
					this.$p.t("tabulator_presets/sort") +
					": " +
					(sortColumn ? sortColumn + " (" + sortDirection + ")" : ""),
			};
		},
		newPresetFormattedStrings() {
			if (!this.newPreset) return null;

			const columnTitles = this.getColumnTitles();

			const displayedColumns = this.newPreset.displayedColumns.map(
				(columnField) => columnTitles[columnField],
			);

			const headerFilters = Object.keys(this.newPreset.headerFilters).map(
				(column) =>
					columnTitles[column] +
					'["' +
					this.newPreset.headerFilters[column] +
					'"]',
			);

			const sortColumn = this.newPreset.sort?.column
				? columnTitles[this.newPreset.sort.column]
				: null;
			const sortDirection =
				this.newPreset.sort?.direction === "desc"
					? this.$p.t("tabulator_presets/desc")
					: this.$p.t("tabulator_presets/asc");

			return {
				displayedColumns:
					this.$p.t("tabulator_presets/displayed_columns") +
					" (" +
					this.$p.t("tabulator_presets/in_order") +
					"): " +
					displayedColumns.join(", "),
				headerFilters:
					this.$p.t("tabulator_presets/column_header_filters") +
					": " +
					headerFilters.join(", "),
				sort:
					this.$p.t("tabulator_presets/sort") +
					": " +
					(sortColumn ? sortColumn + " (" + sortDirection + ")" : ""),
			};
		},
	},
	watch: {
		allPresets: {
			handler() {
				this.hidePresetInfo();
				this.hideNewPresetForm();
			},
			deep: true,
		},
	},
	methods: {
		async showPresetInfo(preset) {
			if (preset !== this.presetInfo) {
				this.presetInfo = preset;
			}

			this.hideNewPresetForm();

			await this.$nextTick();

			if (
				!this.$refs.presetInfoCollapsible
					.getAttribute("class")
					.split(" ")
					.includes("show")
			) {
				this.togglePresetInfoCollapsible();
			}
		},
		async hidePresetInfo() {
			if (
				this.$refs.presetInfoCollapsible
					.getAttribute("class")
					.split(" ")
					.includes("show")
			) {
				this.togglePresetInfoCollapsible();
			}
			await this.$nextTick();
			this.presetInfo = null;
		},
		togglePresetInfoCollapsible() {
			new bootstrap.Collapse(
				"#presetInfoCollapsible_" + this.$props.presetsId,
				{ toggle: true },
			);
		},
		async showNewPresetForm() {
			this.generateNewPreset();

			this.hidePresetInfo();

			await this.$nextTick();

			if (
				!this.$refs.newPresetFormCollapsible
					.getAttribute("class")
					.split(" ")
					.includes("show")
			) {
				this.toggleNewPresetFormCollapsible();
			}
		},
		generateNewPreset() {
			const columns = this.tabulator.columnManager.columns.filter(
				(column) => column.field !== "collapse",
			);

			const displayedColumns = columns
				.filter((column) => column.visible)
				.map((column) => column.field);

			const unparsedHeaderFilters =
				this.tabulator.modules.filter.headerFilters;
			const headerFilterFieldValuePairs = Object.entries(
				unparsedHeaderFilters,
			).map(([columnField, filter]) => [columnField, filter.value]);
			const headerFiltersFieldValuePairsExcludingHiddenColumns =
				headerFilterFieldValuePairs.filter(
					([columnField, filterValue]) => {
						return displayedColumns.includes(columnField);
					},
				);
			const headerFilters = Object.fromEntries(
				headerFiltersFieldValuePairsExcludingHiddenColumns,
			);

			const activeSort = this.tabulator.getSorters()[0];
			const sort = activeSort
				? {
						column: activeSort.field,
						direction: activeSort.dir,
					}
				: null;

			this.newPreset = {
				displayedColumns,
				headerFilters,
				sort,
			};
		},
		async hideNewPresetForm() {
			if (
				this.$refs.newPresetFormCollapsible
					.getAttribute("class")
					.split(" ")
					.includes("show")
			) {
				this.toggleNewPresetFormCollapsible();
			}
		},
		toggleNewPresetFormCollapsible() {
			this.newPresetName = "";
			new bootstrap.Collapse(
				"#newPresetFormCollapsible_" + this.$props.presetsId,
				{ toggle: true },
			);
		},
		async createPreset() {
			if (!this.newPresetName?.length) return;

			const presetCreationResponse = await this.$api.call(
				ApiTabulatorPresets.createTabulatorPreset(
					this.$props.presetsId,
					this.newPresetName,
					this.newPreset,
				),
			);

			if (presetCreationResponse.meta.status === "success") {
				this.fetchCustomUserTabulatorPresets();
				this.hideNewPresetForm();
			}
		},
		async deletePreset(preset) {
			if (!preset.id) return;

			if (
				window.confirm(
					this.$p
						.t("tabulator_presets/preset_deletion_confirmation")
						.replace("(((presetName)))", preset.name),
				)
			) {
				const presetDeletionResponse = await this.$api.call(
					ApiTabulatorPresets.deleteTabulatorPreset(preset.id),
				);

				if (presetDeletionResponse.meta.status === "success") {
					this.fetchCustomUserTabulatorPresets();
				}
			}
		},
		syncPresetWithConfig(preset) {
			const validColumns = this.$props.tabulator.getColumnDefinitions();
			let isUpdateNecessary = false;

			if (preset.displayedColumns) {
				const initialNumberOfDisplayedColumns =
					preset.displayedColumns.length;
				preset.displayedColumns = preset.displayedColumns.filter(
					(columnField) => {
						return validColumns.some(
							(column) => column.field === columnField,
						);
					},
				);
				const updatedNumberOfDisplayedColumns =
					preset.displayedColumns.length;

				isUpdateNecessary =
					updatedNumberOfDisplayedColumns <
					initialNumberOfDisplayedColumns;
			}

			if (preset.headerFilters) {
				const initialNumberOfHeaderFilters = Object.keys(
					preset.headerFilters,
				).length;
				Object.keys(preset.headerFilters).forEach((columnField) => {
					if (
						!validColumns.some(
							(column) =>
								column.field === columnField &&
								column.headerFilter,
						) ||
						!preset.displayedColumns.includes(columnField)
					) {
						delete preset.headerFilters[columnField];
					}
				});
				const updatedNumberOfHeaderFilters = Object.keys(
					preset.headerFilters,
				).length;

				isUpdateNecessary =
					isUpdateNecessary ||
					updatedNumberOfHeaderFilters < initialNumberOfHeaderFilters;
			}

			if (preset.sort) {
				if (
					!validColumns.some(
						(column) =>
							column.field === preset.sort.column &&
							column.headerSort,
					)
				) {
					preset.sort = null;
					isUpdateNecessary = true;
				}
			}

			if (isUpdateNecessary) {
				this.updatePreset(preset);
			}

			return preset;
		},
		async updatePreset(preset) {
			if (!preset.id) return;

			const tabulatorPresetUpdateResponse = await this.$api.call(
				ApiTabulatorPresets.updateTabulatorPreset(preset.id, {
					displayedColumns: preset.displayedColumns,
					headerFilters: preset.headerFilters,
					sort: preset.sort,
				}),
			);

			if (tabulatorPresetUpdateResponse.meta.status === "success") {
				this.fetchCustomUserTabulatorPresets();
			}
		},
		async applyPreset(preset) {
			preset = this.syncPresetWithConfig(preset);

			let columns = this.$props.tabulator
				.getColumnDefinitions()
				.filter((column) => column.field !== "collapse");
			columns.forEach((column) =>
				this.$props.tabulator.hideColumn(column.field),
			);

			preset.displayedColumns?.forEach(
				(columnField, index, displayedColumns) => {
					this.$props.tabulator.showColumn(columnField);
					if (index === 0) return;
					this.$props.tabulator.moveColumn(
						columnField,
						displayedColumns[index - 1],
						true,
					);
				},
			);

			this.$props.tabulator.clearHeaderFilter();
			if (preset.headerFilters) {
				Object.entries(preset.headerFilters).forEach(
					([columnField, filterValue]) => {
						this.$props.tabulator.setHeaderFilterValue(
							columnField,
							filterValue,
						);
					},
				);
			}

			this.$props.tabulator.clearSort();
			if (preset.sort?.column) {
				this.$props.tabulator.setSort(
					preset.sort.column,
					preset.sort.direction ?? "asc",
				);
			}

			this.$emit("tablePresetApplied", { preset });
		},
		async fetchCustomUserTabulatorPresets() {
			let tabulatorPresetsResponse = await this.$api.call(
				ApiTabulatorPresets.getTabulatorPresets(this.$props.presetsId),
			);

			if (tabulatorPresetsResponse.meta.status === "success") {
				this.customUserPresets = tabulatorPresetsResponse.data.map(
					(presetInfo) => {
						return {
							id: presetInfo.preset_id,
							name: presetInfo.preset_name,
							...JSON.parse(presetInfo.preset_json),
						};
					},
				);
			}
		},
		getColumnTitles() {
			const columnLocalizations =
				this.$props.tabulator.getLocale() !== "default"
					? this.$props.tabulator.getLang().columns
					: {};
			const columnFieldTitlePairs = this.$props.tabulator
				.getColumnDefinitions()
				.map((column) => [
					column.field,
					columnLocalizations[column.field] ?? column.title,
				]);
			return Object.fromEntries(columnFieldTitlePairs);
		},
	},
	async created() {
		await this.$p.loadCategory(["tabulator_presets", "global"]);
		await this.fetchCustomUserTabulatorPresets();
	},
	template: /*html*/ `
	<div>
		<div class="card">
			<div class="card-header">{{ $p.t("tabulator_presets/table_presets") }}</div>
			<div class="card-body d-flex flex-column">
				<div class="d-flex flex-row gap-1 justify-content-start flex-wrap">
					<div v-for="preset in allPresets" class="d-flex flex-column gap-1 mb-2">
						<div @click="applyPreset(preset)" class="btn btn-dark py-1 px-2">{{ preset.name }}</div>
						<div class="d-flex flex-row justify-content-center">
							<div class="d-flex flex-row gap-2 px-3">
								<span @click="showPresetInfo(preset)" type="button" class="fa-solid fa-circle-info"></span>
								<span v-if="preset.id" @click="deletePreset(preset)" type="button" class="fa-solid fa-trash-can"></span>
							</div>
						</div>
					</div>
					<div>
						<div @click="showNewPresetForm()" class="btn btn-dark py-1 px-2 d-flex flex-row align-items-center gap-1">
							<span class="fa-solid fa-plus"></span>
							<span class="flex-nowrap">{{ $p.t("tabulator_presets/save_current_config") }}</span>
						</div>
					</div>
				</div>
				<div :id="'presetInfoCollapsible_' + $props.presetsId" ref="presetInfoCollapsible" class="collapse">
					<div class="d-flex flex-column">
						<hr />
						<div class="d-flex flex-column gap-2">
							<div class="w-100 d-flex flex-row justify-content-between">
								<span class="fw-bold">{{ presetInfo?.name }}</span>
								<span @click="hidePresetInfo()" type="button" class="fa-solid fa-xmark"></span>
							</div>
							<span>{{ presetInfoFormattedStrings?.displayedColumns }}</span>
							<span>{{ presetInfoFormattedStrings?.headerFilters }}</span>
							<span>{{ presetInfoFormattedStrings?.sort }}</span>
						</div>
					</div>
				</div>
				<div :id="'newPresetFormCollapsible_' + $props.presetsId" ref="newPresetFormCollapsible" class="collapse">
					<div class="d-flex flex-column">
						<hr />
						<div class="d-flex flex-column gap-2">
							<div class="w-100 d-flex flex-row justify-content-between">
								<span class="fw-bold">{{ $p.t("tabulator_presets/save_current_config") }}</span>
								<span @click="hideNewPresetForm()" type="button" class="fa-solid fa-xmark"></span>
							</div>
							<span>{{ newPresetFormattedStrings?.displayedColumns }}</span>
							<span>{{ newPresetFormattedStrings?.headerFilters }}</span>
							<span>{{ newPresetFormattedStrings?.sort }}</span>
							<div class="d-flex flex-row justify-content-center align-items-center gap-2">
								<input v-model="newPresetName" :placeholder="$p.t('tabulator_presets/preset_name')" />
								<div
									@click="createPreset()"
									:class="{'opacity-50 pe-none': !newPresetName?.length}"
									class="btn btn-dark py-1 px-2"
								>
									{{ $p.t('global/speichern') }}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	`,
};
