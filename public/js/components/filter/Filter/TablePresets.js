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
	name: "TablePresets",
	props: ["identifier", "tabulator"],
	emits: {},
	data: function () {
		return {
			// todo: remove once actual data is fetched
			testDataForUIDev: [
				{
					id: null,
					name: "Test Preset 1",
					displayedColumns: {
						testCol1: true,
						testCol2: false,
						testCol3: true,
						testCol4: false,
						testCol5: true,
					},
					filters: {
						testCol3: "adis",
						testCol5: "01.06.2026.-31.12.2026.",
					},
					columnsOrder: ["testCol3", "testCol5", "testCol1"],
					sort: {
						column: "testCol1",
						direction: "asc",
					},
					isUserCustom: false,
				},
				{
					id: 1,
					name: "Test Preset 2",
					displayedColumns: {
						testCol1: true,
						testCol2: true,
						testCol3: true,
						testCol4: false,
						testCol5: false,
					},
					filters: {
						testCol1: "posko",
					},
					columnsOrder: ["testCol1", "testCol3", "testCol2"],
					sort: {
						column: "testCol3",
						direction: "desc",
					},
				},
			],
			presetInfo: null,
		};
	},
	computed: {
		presetInfoFormattedStrings() {
			if (!this.presetInfo) return null;

			const displayedColumns = Object.keys(this.presetInfo.displayedColumns).filter((column) => this.presetInfo.displayedColumns[column]);
			const filters = Object.keys(this.presetInfo.filters).map((column) => column + ": \"" + this.presetInfo.filters[column] + "\"");
			return {
				displayedColumns: "Displayed columns: " + displayedColumns.join(", "),
				columnsOrder: "Columns ordering: " + this.presetInfo.columnsOrder.join(", "),
				filters: "Column filters: " + filters.join(", "),
				sort: "Sort: " + this.presetInfo.sort.column + " (" + (this.presetInfo.sort.direction === "asc" ? "ascending" : "descending") + ")",
			};
		},
	},
	watch: {
		// todo: watch presets, once they change nullify presetInfo and collapse preset info section
	},
	methods: {
		async showPresetInfo(preset) {
			if (preset !== this.presetInfo) {
				this.presetInfo = preset;
			}
			await this.$nextTick();
			if (!this.$refs.presetInfoCollapsible.getAttribute("class").split(" ").includes("show")) {
				this.togglePresetInfoCollapsible();
			}
		},
		hidePresetInfo() {
			this.presetInfo = null;
			this.togglePresetInfoCollapsible();
		},
		togglePresetInfoCollapsible() {
			new bootstrap.Collapse("#presetInfoCollapsible" + this.$props.identifier, {toggle: true});
		},
		deletePreset(preset) {
			// todo
		},
		showNewPresetForm() {
			// todo
			if (!this.$refs.newPresetFormCollapsible.getAttribute("class").split(" ").includes("show")) {
				this.toggleNewPresetFormCollapsible();
			}

		},
		toggleNewPresetFormCollapsible() {
			new bootstrap.Collapse("#newPresetFormCollapsible" + this.$props.identifier, {toggle: true});
		},
		applyPreset() {
			// todo
		},
	},
	template: /*html*/ `
	<div>
		<div class="card">
			<div class="card-header">Table Presets</div>
			<div class="card-body d-flex flex-column">
				<div class="d-flex flex-row gap-1 justify-content-start flex-wrap">
					<div v-for="preset in testDataForUIDev" class="d-flex flex-column gap-1">
						<div @click="applyPreset()" class="btn btn-dark py-1 px-2">{{ preset.name }}</div>
						<div class="d-flex flex-row justify-content-center">
							<div class="d-flex flex-row gap-2">
								<span @click="showPresetInfo(preset)" type="button" class="fa-solid fa-circle-info"></span>
								<span v-if="preset.id" @click="deletePreset(preset)" type="button" class="fa-solid fa-trash-can"></span>
							</div>
						</div>
					</div>
					<div>
						<div @click="showNewPresetForm()" class="btn btn-dark py-1 px-2 d-flex flex-row align-items-center gap-1">
							<span class="fa-solid fa-plus"></span>
							<span class="flex-nowrap">{{ "Save current configuration" }}</span>
						</div>
					</div>
				</div>
				<div :id="'presetInfoCollapsible' + $props.identifier" ref="presetInfoCollapsible" class="collapse">
					<div class="d-flex flex-column">
						<hr />
						<div class="d-flex flex-column gap-2">
							<div class="w-100 d-flex flex-row justify-content-between">
								<span class="fw-bold">{{ presetInfo?.name }}</span>
								<span @click="hidePresetInfo()" type="button" class="fa-solid fa-xmark"></span>
							</div>
							<span>{{ presetInfoFormattedStrings?.displayedColumns }}</span>
							<span>{{ presetInfoFormattedStrings?.columnsOrder }}</span>
							<span>{{ presetInfoFormattedStrings?.filters }}</span>
							<span>{{ presetInfoFormattedStrings?.sort }}</span>
						</div>
					</div>
				</div>
				<div :id="'newPresetFormCollapsible' + $props.identifier" ref="newPresetFormCollapsible" class="collapse">
					<!-- todo -->
					<div class="d-flex flex-column">
						<hr />
						<div class="d-flex flex-column gap-2">
							<div class="w-100 d-flex flex-row justify-content-between">
								<span class="fw-bold">{{ "Save current configuration" }}</span>
								<span @click="toggleNewPresetFormCollapsible()" type="button" class="fa-solid fa-xmark"></span>
							</div>
							adis
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	`,
};
