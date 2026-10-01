import Module from "../../../../vendor/olifolkerd/tabulator5/src/js/core/Module.js";

export default class TablePresetsModule extends Module {
	static moduleName = "tablePresets";
	static moduleInitNumber = 1;

	constructor(table) {
		super(table);
	}

	// presetColumns limits the preset to the given column fields. A table with dynamic
	// columns gives the fields of its constant columns here.
	applyPreset(preset, presetColumns = []) {
		if (presetColumns.length) {
			this.applyColumnsInScope(preset, presetColumns);
		} else {
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
				.filter(
					(column) =>
						!preset.displayedColumns.includes(column.field),
				)
				.map((column) => {
					column.visible = false;
					return column;
				});
			this.table.setColumns(
				reorderedColumnsConfig.concat(hiddenColumnsConfig),
			);
		}

		this.table.clearHeaderFilter();
		if (preset.headerFilters) {
			Object.entries(preset.headerFilters).forEach(
				([columnField, filterValue]) => {
					this.table.setHeaderFilterValue(columnField, filterValue);

					// double checking if filter inputs are filled in due to issues with custom header filters
					const headerFilterInputElement =
						this.table.columnManager.columns
							.find((column) => column.field === columnField)
							?.element.querySelector(
								".tabulator-header-filter input",
							);
					if (
						headerFilterInputElement &&
						headerFilterInputElement.value !== filterValue
					) {
						headerFilterInputElement.value = filterValue;
					}
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

	// The preset orders and hides the columns of the scope only. A column outside the
	// scope keeps its position, because the table does not show it in every state.
	applyColumnsInScope(preset, presetColumns) {
		const displayedColumns = preset.displayedColumns ?? [];

		// a displayed column takes the rank of the preset. A column that the preset
		// hides has no rank and keeps the current order at the end of its block.
		const rank = (columnConfig) => {
			const index = displayedColumns.indexOf(columnConfig.field);
			return index === -1 ? displayedColumns.length : index;
		};

		let columnsConfig = [];
		let scopeBlock = [];

		// the sort runs for each block of neighbouring scope columns. This way a scope
		// column never moves over a column outside the scope.
		const addScopeBlock = () => {
			scopeBlock.sort((a, b) => rank(a) - rank(b));
			columnsConfig = columnsConfig.concat(scopeBlock);
			scopeBlock = [];
		};

		this.table.getColumnDefinitions().forEach((columnConfig) => {
			if (!presetColumns.includes(columnConfig.field)) {
				addScopeBlock();
				columnsConfig.push(columnConfig);
				return;
			}

			columnConfig.visible = displayedColumns.includes(
				columnConfig.field,
			);
			scopeBlock.push(columnConfig);
		});
		addScopeBlock();

		this.table.setColumns(columnsConfig);
	}
}
