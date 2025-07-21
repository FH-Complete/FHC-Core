export function useEventLoader(rangeInterval, getPromiseFunc) {
	const events = Vue.ref([]);
	const lv = Vue.ref(null);
	const eventsLoaded = [];

	const mergePromiseArr = (n, o) => {
		if (Array.isArray(n))
			return o.concat(n);
		return o.push(n), o;
	};

	const markEventsLoaded = (start, end) => {
		let result = [];
		if (!eventsLoaded.length) {
			// empty: add new chunk
			eventsLoaded.push(start.ts, end.ts);
		} else {
			if (eventsLoaded[eventsLoaded.length-1] + 1 == start.ts) {
				// add to the end of last chunk
				eventsLoaded[eventsLoaded.length-1] = end.ts;
			} else if (eventsLoaded[eventsLoaded.length-1] < start.ts) {
				// add new chunk after the last chunk
				eventsLoaded.push(start.ts, end.ts);
			} else if (eventsLoaded[0] == end.ts + 1) {
				// add to the start of first chunk
				eventsLoaded[0] = start.ts;
			} else if (eventsLoaded[0] > end.ts) {
				eventsLoaded.unshift(start.ts, end.ts);
			} else {
				let index = eventsLoaded.findIndex(e => e >= start.ts);

				if (index % 2) {
					// starts inside an existing chunk
					if (eventsLoaded[index] >= end.ts)
						return []; // Already loaded

					let indexIsLast = (index == eventsLoaded.length - 1);

					if (indexIsLast || eventsLoaded[index + 1] > end.ts) {
						// extend an existing chunk
						// and merge with the next if necessary
						let nStart = eventsLoaded[index] + 1;
						start = start.plus(nStart - start.ts);
						if (!indexIsLast && eventsLoaded[index + 1] == end.ts + 1)
							eventsLoaded.splice(index, 2);
						else
							eventsLoaded[index] = end.ts;
					} else {
						// merge exising chunks
						// and load the rest if necessary
						if (eventsLoaded[index + 2] < end.ts) {
							let rStart = eventsLoaded[index + 2] + 1;
							result = mergePromiseArr(markEventsLoaded(start.plus(rStart - start.ts), end), result);
						}

						let nStart = eventsLoaded[index] + 1;
						start = start.plus(nStart - start.ts);
						let nEnd = eventsLoaded[index + 1] - 1;
						end = end.plus(nEnd - end.ts);
						eventsLoaded.splice(index, 2);
					}
				} else {
					// starts between two chunks or before the first
					if (!index) {
						// extend the first chunk
						// and load the rest if necessary
						if (eventsLoaded[1] < end.ts) {
							let rStart = eventsLoaded[1] + 1;
							result = mergePromiseArr(markEventsLoaded(start.plus(rStart - start.ts), end), result);
						}
						let nEnd = eventsLoaded[0] - 1;
						end = end.plus(nEnd - end.ts);
						eventsLoaded[0] = start.ts;
					} else if (eventsLoaded[index] == start.ts) {
						// starts at the same position as an existing chunk
						if (eventsLoaded[index + 1] >= end.ts)
							return []; // Already loaded
						// load the rest
						let rStart = eventsLoaded[index + 1] + 1;
						result = mergePromiseArr(markEventsLoaded(start.plus(rStart - start.ts), end), result);
					} else {
						// extend an existing chunk
						// and load the rest if necessary
						if (eventsLoaded[index + 1] < end.ts) {
							let rStart = eventsLoaded[index + 1] + 1;
							result = mergePromiseArr(markEventsLoaded(start.plus(rStart - start.ts), end), result);
						}
						let nEnd = eventsLoaded[index] - 1;
						end = end.plus(nEnd - end.ts);
						eventsLoaded[index] = start.ts;
					}
				}
			}
		}

		if (start.ts > end.ts)
			return result;

		return mergePromiseArr(getPromiseFunc(start, end), result);
	};

	Vue.watchEffect(() => {
		const range = Vue.toValue(rangeInterval);
		if (!(range instanceof luxon.Interval))
			return;
		const promises = markEventsLoaded(range.start, range.end);
		Promise
			.allSettled(promises)
			.then(results => {
				results.forEach(res => {
					if (
						res.status === 'fulfilled'
						&& res.value.meta.status === "success"
					) {
						if (res.value.meta.lv)
							lv.value = res.value.meta.lv;

						events.value = events.value.concat(res.value.data);
					}
				})
			});
	})

	return { events, lv }
}