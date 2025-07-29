/**
 * TODO(chris): This is only a prototype!!!
 */
const DragAndDrop = {
	TYPE_LE: "lehreinheit",
	TYPE_VEVENT: "vevent",

	getValidTransferData(event, allowedTypes) {
		const json = event.dataTransfer.getData('text');
		let obj;
		try {
			obj = JSON.parse(json);
			if (!obj.type)
				return null;
			if (allowedTypes && !allowedTypes.includes(obj.type))
				return null;
		} catch (error) {
			return null;
		}
		return obj;
	},
	isValidTransferData(event, allowedTypes) {
		return this.getValidTransferData(event, allowedTypes) ? true : false;
	},
	getTransferData(event) {
		const json = event.dataTransfer.getData('text');
		return JSON.parse(json);
	},
	setTransferData(event, data) {
		switch (data.type) {
			case DragAndDrop.TYPE_LE:
				data = DragAndDrop.fromLe(data);
				break;
			default:
				if (data.dtstart && data.dtend && data.uid && data.summary) {
					data = DragAndDrop.fromVEvent(data);
					break;
				}
				return false; // No type found => abort
		}
		
		event.dataTransfer.setData('text', JSON.stringify(data));
		return true;
	},
	fromLe(data) {
		const {
			type = DragAndDrop.TYPE_LE,
			lehreinheit_id: id,
			stundenblockung
		} = data;
		
		return { type, id, stundenblockung };
	},
	fromVEvent(data) {
		const {
			type = DragAndDrop.TYPE_VEVENT,
			uid: id,
			dtstart,
			dtend,
			summary
		} = data;
		
		return { type, id, dtstart, dtend, summary };
	}
};

export default DragAndDrop;
