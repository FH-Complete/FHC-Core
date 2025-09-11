/**
 * TODO(chris): This is only a prototype!!!
 */

const TYPE_DEFINITION = {
	lehreinheit: {
		id: "lehreinheit_id",
		dragIcon: "fa-solid fa-chalkboard-user",
		extras: [
			"stundenblockung"
		]
	},
	vevent: {
		id: "uid",
		dragIcon: "fa-solid fa-calendar",
		extras: [
			"dtstart",
			"dtend",
			"summary"
		]
	},
	person: {
		id: "person_id",
		dragIcon: "fa-solid fa-user"
	},
	student: {
		id: "student_uid",
		dragIcon: "fa-solid fa-user-graduate"
	},
	prestudent: {
		id: "prestudent_id",
		dragIcon: "fa-solid fa-user-graduate text-muted"
	}
	// TODO: IMPLEMENT OTHER TYPES
};

const VALID_TYPES = Object.keys(TYPE_DEFINITION);

const TYPE_CONSTANTS = Object.keys(TYPE_DEFINITION).reduce((res, type) => {
	res['TYPE_' + type.toUpperCase()] = type;
	return res;
}, {});

function isValidDragObject(value) {
	if (!value)
		return false;
	if (Array.isArray(value))
		return value.every(isValidDragObject);
	if (!value.type)
		return false;

	if (value.type.substr(-11) == '-collection') {
		if (!value.hasOwnProperty('values'))
			return false;

		if (!VALID_TYPES.includes(value.type.substr(0, value.type.length-11)))
			return false;
	} else {
		if (!value.hasOwnProperty('id'))
			return false;

		if (!VALID_TYPES.includes(value.type))
			return false;

		if (TYPE_DEFINITION[value.type].extras) {
			if (!TYPE_DEFINITION[value.type].extras.every(extra => value.hasOwnProperty(extra)))
				return false;
		}
	}

	return true;
}

function getValidTransferData(event, allowedTypes, strict) {
	let obj = null;

	try {
		obj = getTransferData(event, strict);
		if (!obj)
			return null;

		if (!strict && Array.isArray(obj)) {
			obj = obj.filter(isValidDragObject);
			if (!obj.length)
				return null;
		} else if (!isValidDragObject(obj))
			return null;

		if (allowedTypes && allowedTypes.length) {
			if (Array.isArray(obj)) {
				if (strict && !obj.every(v => allowedTypes.includes(v.type))) {
					return null;
				} else if (!strict) {
					obj = obj.filter(v => allowedTypes.includes(v.type));
					if (!obj.length)
						return null;
				}
			} else if (!allowedTypes.includes(obj.type)) {
				return null;
			}
		}
	} catch(error) {
		return null;
	}

	if (Array.isArray(obj) && obj.length == 1)
		return obj.find(Boolean);

	return obj;
}

function isValidTransferData(event, allowedTypes, strict) {
	return getValidTransferData(event, allowedTypes, strict) ? true : false;
}

function getTransferData(event, strict) {
	const result = [];

	for (const type of event.dataTransfer.types) {
		if (type.substr(0, 16) != 'application/fhc-') {
			if (strict)
				return null;
			continue;
		}
		let base_type = type.substr(16);
		let collection = false;
		if (base_type.substr(-11) == '-collection') {
			base_type = base_type.substr(0, base_type.length-11);
			collection = true;
		}
		if (!VALID_TYPES.includes(base_type)) {
			if (strict)
				return null;
			continue;
		}
		let data = JSON.parse(event.dataTransfer.getData(type));
		if (collection)
			result.push(...data.values);
		else
			result.push(data);
	}

	if (!result.length)
		return null;

	if (result.length == 1)
		return result[0];

	return result;
}

function convertToValidDragObject(data, strict) {
	if (Array.isArray(data)) {
		const converted = data.map(convertToValidDragObject).filter(Boolean);
		if (!converted.length)
			return undefined;
		if (strict && converted.length != data.length)
			return undefined;

		const sorted = converted.reduce((res, item) => {
			if (!res[item.type])
				res[item.type] = [];
			res[item.type].push(item);
			return res;
		}, {});

		return Object.entries(sorted).map(([type, values]) => {
			if (values.length > 1) {
				return {
					type: type + '-collection',
					values
				};
			}
			return values[0];
		});
	}

	if (data.hasOwnProperty('type') && isValidDragObject(data)) {
		return data;
	}

	const found = Object.entries(TYPE_DEFINITION).find(([type, typedef]) => {
		if (!data.hasOwnProperty(typedef.id))
			return false;
		if (typedef.extras) {
			if (!typedef.extras.every(extra => data.hasOwnProperty("extra")))
				return false;
		}
		return true;
	});

	if (!found) {
		return undefined;
	}

	const [ type, typedef ] = found;

	const newData = {};
	newData.type = type;
	newData.id = data[typedef.id];
	if (typedef.extras)
		typedef.extras.forEach(extra => newData[extras] = data[extra]);

	return newData;
}

function setTransferData(event, validDragObject, setDragImage = false) {
	if (setDragImage) {
		const dragItems = Array.isArray(validDragObject) ? validDragObject : [ validDragObject ];
		const dragElements = dragItems.map(item => {
			const icon = document.createElement('i');
			const label = document.createElement('span');
			const iconContainer = document.createElement('span');

			iconContainer.className = 'btn btn-outline-dark bg-light';
			label.className = 'small';

			if (TYPE_DEFINITION[item.type]) {
				icon.className = TYPE_DEFINITION[item.type].dragIcon || 'fa-solid fa-question';
				label.textContent = item.id;
			} else if (item.type.substr(-11) == '-collection' && TYPE_DEFINITION[item.type.substr(0, item.type.length-11)]) {
				iconContainer.style.boxShadow = '3px 3px var(--bs-btn-border-color)';
				icon.className = TYPE_DEFINITION[item.type.substr(0, item.type.length-11)].dragIcon || 'fa-solid fa-question';
				label.textContent = 'x' + item.values.length;
			} else {
				icon.className = 'fa-solid fa-question';
				label.textContent = item.id || '';
			}
			
			iconContainer.append(icon);

			const itemContainer = document.createElement('div');
			itemContainer.className = 'd-flex flex-column align-items-center gap-2 small';
			itemContainer.append(iconContainer, label);
			return itemContainer;
		});

		const container = document.createElement('div');
		container.className = 'd-flex flex-row gap-2 small';
		container.append(...dragElements);

		document.body.append(container);
		event.dataTransfer.setDragImage(container, -25, 0);
		requestAnimationFrame(() => {
			document.body.removeChild(container);
		});
	}
	if (Array.isArray(validDragObject)) {
		return validDragObject.forEach(data => setTransferData(event, data));
	}
	
	event.dataTransfer.setData('application/fhc-' + validDragObject.type, JSON.stringify(validDragObject));
}

/**
 * check if the dataTransfer types are in the allowed types array
 * if strict is disabled at least one type must be the allowed array
 * otherwise all types have to be in the allowed array
 *
 * @param Event		event
 * @param Array		allowedTypes
 * @param Boolean	strict
 */
function eventHasTypes(event, allowedTypes, strict) {
	if (!allowedTypes || !allowedTypes.length)
		allowedTypes = VALID_TYPES;
	allowedTypes = allowedTypes.map(type => 'application/fhc-' + type);

	const dataTypes = [...event.dataTransfer.types];
	
	// NOTE(chris): if dragging across browsers the dataTransfer object is
	// set to a default one without data. Since we do not support dragging
	// across browsers (yet) we return false which will disallow dropping.
	if (!dataTypes.length)
		return false;
	
	if (!strict)
		return allowedTypes.some(type => [...event.dataTransfer.types].includes(type));
	
	return [...event.dataTransfer.types].every(type => allowedTypes.includes(type));
}

function bindDragEnterLeave(el, onEnter, onLeave) {
	// NOTE(chris): add save dragenter and dragleave events
	// that won't fire when hovering over child elements

	let skipLeave = false;
	let skipLeaveParent = true;

	function init(evt) {
		skipLeave = false;
		skipLeaveParent = true;
		// add global listeners
		window.addEventListener('dragenter', globalDragenter, true);
		window.addEventListener('dragleave', globalDragleave, true);
		window.addEventListener('drop', globalDrop, true);
		// call enter
		onEnter(evt);
		// remove self
		el.removeEventListener('dragenter', init);
	}

	function cleanup(evt, wasDropped) {
		// remove global listeners
		window.removeEventListener('dragenter', globalDragenter, true);
		window.removeEventListener('dragleave', globalDragleave, true);
		window.removeEventListener('drop', globalDrop, true);
		// call leave
		onLeave(evt, wasDropped);
		// add init
		el.addEventListener('dragenter', init);
	}

	function globalDragenter(evt) {
		skipLeaveParent = false;
		if (el != evt.target && !el.contains(evt.target)) {
			cleanup(evt);
		} else {
			skipLeave = true;
		}
	}
	function globalDragleave(evt) {
		if (el != evt.target && !el.contains(evt.target)) {
			if (skipLeaveParent) {
				skipLeaveParent = false;
				return;
			}
		} else {
			if (skipLeave) {
				skipLeave = false;
				return;
			}
		}
		cleanup(evt);
	}
	function globalDrop(evt) {
		cleanup(evt, true);
	}

	el.addEventListener('dragenter', init);

	return () => {
		// cleanup
		el.removeEventListener('dragenter', init);
	}
}

export {
	isValidDragObject,
	getValidTransferData,
	isValidTransferData,
	getTransferData,
	convertToValidDragObject,
	setTransferData,
	eventHasTypes,
	bindDragEnterLeave
};
export default {
	...TYPE_CONSTANTS,
	isValidDragObject,
	getValidTransferData,
	isValidTransferData,
	getTransferData,
	convertToValidDragObject,
	setTransferData,
	eventHasTypes,
	bindDragEnterLeave
};
