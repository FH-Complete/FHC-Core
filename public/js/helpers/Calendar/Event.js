import CalendarDate from './Date.js';

const CalendarEvent = {
	getType(event) {
		if (event.type && Object.keys(allowedTypes).includes(event.type))
			return event.type;

		if (event.dtstart && event.dtend) // TODO(chris): are those enough
			return 'vevent';

		return null;
	},
	smartConvert(event) {
		const type = CalendarEvent.getType(event);
	
		if (type === null)
			return null; // TODO(chris): what to do here?
	
		return CalendarEvent['from' + allowedTypes[type]](event);
	},
	fromLehreinheit(orig) {
		// TODO(chris): wrong type
		return {
			id: 'lehreinheit' + orig.lehreinheit_id,
			start: CalendarDate.UTC(new Date(orig.datum + ' ' + orig.beginn)),
			end: CalendarDate.UTC(new Date(orig.datum + ' ' + orig.ende)),
			orig
		};
	},
	fromVEvent(orig) {
		return {
			id: 'vevent' + orig.uid,
			start: CalendarDate.UTC(new Date(orig.dtstart)),
			end: CalendarDate.UTC(new Date(orig.dtend)),
			orig
		};
	}
	// TODO(chris): implement other types
};

const allowedTypes = Object.keys(CalendarEvent).reduce((result, key) => {
	if (key.substr(0,4) == 'from') {
		const type = key.substr(4);
		result[type.toLowerCase()] = type;
	}
	return result;
}, {});

export default CalendarEvent;
