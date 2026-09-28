import ResponsiveLayout from "../../../../vendor/olifolkerd/tabulator5/src/js/modules/ResponsiveLayout/ResponsiveLayout.js";

export default class ResponsiveLayoutExtensionModule extends ResponsiveLayout {
	static moduleName = "responsiveLayout";
	static moduleInitNumber = 1;

	constructor(table) {
		super(table);
	}

	generateCollapsedRowData(row) {
		let result = super.generateCollapsedRowData(row);
		const isLocalizationEnabled =
			this.table.options.locale &&
			this.table.options.locale !== "default";
		const columnHeadingTranslations = this.table?.initialized
			? this.table.getLang()?.columns
			: null;

		if (isLocalizationEnabled && columnHeadingTranslations) {
			result = result.map((column) => {
				if (columnHeadingTranslations[column.field]) {
					column.title = columnHeadingTranslations[column.field];
				}
				return column;
			});
		}

		return result;
	}
}
