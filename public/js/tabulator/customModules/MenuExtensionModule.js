import Menu from "../../../../vendor/olifolkerd/tabulator5/src/js/modules/Menu/Menu.js";

export default class MenuExtensionModule extends Menu {
	static moduleName = "menu";
	static moduleInitNumber = 1;

	constructor(table) {
		super(table);
	}

	loadMenu(e, component, menu, parentEl, parentPopup){
		const isLocalizationEnabled = !!component.table.options.locale;
		const menuItemTranslations = component.table.getLang().menuItems;
		if (isLocalizationEnabled && menuItemTranslations) {
			menu = menu.map((menuItem) => {
				if (menuItem.phrase && menuItemTranslations[menuItem.phrase]) {
					menuItem.label = menuItemTranslations[menuItem.phrase];
				}
				return menuItem;
			});
		}

		super.loadMenu(e, component, menu, parentEl, parentPopup);
	}
}
