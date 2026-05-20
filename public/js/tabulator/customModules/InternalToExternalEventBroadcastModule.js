// changes within tabulator are often not adequately broadcast to the external component
// this module exposes tabulator's internal events
// if external event name is not specified, the dispatched external event will be the camelCase version of the kebab-case internal event

import Module from "../../../../vendor/olifolkerd/tabulator5/src/js/core/Module.js";
import { convertCase } from "../../helpers/StringHelpers.js";

export default class InternalToExternalEventBroadcastModule extends Module {
	static moduleName = "internalToExternalEventBroadcastModule";
	static moduleInitNumber = 1;

	constructor(table) {
		super(table);

		this.eventsToBroadcast = [
			{
				internal: "layout-refreshed",
				external: null,
			},
		];
	}

	initialize() {
		this.eventsToBroadcast.forEach((event) => {
			this.subscribe(event.internal, () => {
				this.dispatchExternal(event.external ?? convertCase(event.internal, "kebab", "camel"));
			});
		});
	}

	
}
