import Module from "../../../../vendor/olifolkerd/tabulator5/src/js/core/Module.js";

export default class TablePresetsModule extends Module {
	static moduleName = "tablePresets";
	static moduleInitNumber = 1;

	constructor(table) {
		super(table);
	}

	applyPreset(preset) {
		let columnsConfig = this.table.options.columns;
		let reorderedColumnsConfig = [];
		preset.displayedColumns.forEach((columnField) => {
			let columnConfig = columnsConfig.find(
				(column) => column.field === columnField,
			);
			if (!columnConfig) return;
			columnConfig.visible = true;
			reorderedColumnsConfig.push(columnConfig);
		});

		let hiddenColumnsConfig = columnsConfig
			.filter((column) => !preset.displayedColumns.includes(column.field))
			.map((column) => {
				column.visible = false;
				return column;
			});
		this.table.setColumns(
			reorderedColumnsConfig.concat(hiddenColumnsConfig),
		);

		this.table.clearHeaderFilter();
		if (preset.headerFilters) {
			Object.entries(preset.headerFilters).forEach(
				([columnField, filterValue]) => {
					this.table.setHeaderFilterValue(
						columnField,
						filterValue,
					);
				},
			);
		}

		this.table.clearSort();
		if (preset.sort?.column) {
			this.table.setSort(
				preset.sort.column,
				preset.sort.direction ?? "asc",
			);
		}
	}
}
