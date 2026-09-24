const PLAN_REQUEST_INTERVAL_DAYS = 60;

export function useEventLoader(
  rangeInterval,
  getPromiseFunc,
  cacheMultiplier = 1,
  requestIntervalDays = PLAN_REQUEST_INTERVAL_DAYS,
  waitForAllPromises = true,
) {
  let hasFirstLoadOccurred = false;

  let allEvents = Vue.ref([]);
  const lv = Vue.ref(null);

  let newlyLoadedEvents = [];

  let cachedEventsStartTimestamp = null;
  let cachedEventsEndTimestamp = null;

  let allowedCacheStartTimestamp = null;
  let allowedCacheEndTimestamp = null;

  let currentlyDisplayedDateRange;

  const getCachePadding = () => {
    const cacheSize = Math.max(Vue.toValue(cacheMultiplier), 0);

    return Math.round(currentlyDisplayedDateRange.length() * cacheSize) + 1;
  };

  const getRequestIntervalDays = () => {
    const intervalDays = Number(Vue.toValue(requestIntervalDays));

    return Number.isFinite(intervalDays) && intervalDays > 0
      ? Math.max(1, Math.floor(intervalDays))
      : PLAN_REQUEST_INTERVAL_DAYS;
  };

  const reload = (isCacheEnabled = true, isLoaderEventVisualReset = true) => {
    if (
      currentlyDisplayedDateRange &&
      currentlyDisplayedDateRange?.start?.ts ===
        rangeInterval?.value?.start?.ts &&
      currentlyDisplayedDateRange?.end?.ts === rangeInterval?.value?.end?.ts &&
      isCacheEnabled
    ) {
      return;
    }

    currentlyDisplayedDateRange = Vue.toValue(rangeInterval);

    if (
      !currentlyDisplayedDateRange?.start ||
      !currentlyDisplayedDateRange?.end
    )
      return;

    if (!(currentlyDisplayedDateRange instanceof luxon.Interval)) return;
    if (
      cachedEventsStartTimestamp === currentlyDisplayedDateRange.start.ts &&
      cachedEventsEndTimestamp === currentlyDisplayedDateRange.end.ts &&
      isCacheEnabled
    ) {
      return;
    }

    const promises = requestEvents(
      currentlyDisplayedDateRange.start.ts,
      currentlyDisplayedDateRange.end.ts,
      isCacheEnabled,
	  isLoaderEventVisualReset
    );

    ensureCacheRangeIsInAllowedRange(
      allowedCacheStartTimestamp,
      allowedCacheEndTimestamp,
    );

    if (promises.length === 0) {
      allEvents.value = removeVisualForEventsLoading(
        ensureEventsAreInValidCacheRange(allEvents.value),
      );

      return;
    }
    
	addVisualForEventsLoading();

    if (waitForAllPromises) {
      Promise.allSettled(promises).then((results) => {
        let newlyLoadedEvents = [];
        let hasSuccessfullyLoadedEvents = false;

        results.forEach((res) => {
          if (
            !(res.status === "fulfilled" && res.value.meta.status === "success")
          )
            return;

          hasSuccessfullyLoadedEvents = true;
          if (res.value.meta.lv) lv.value = res.value.meta.lv;
          newlyLoadedEvents = newlyLoadedEvents.concat(res.value.data);
        });

        let tempAllEvents = Array.from(
          new Map(
            [...allEvents.value, ...newlyLoadedEvents].map((event) => [
              event.eindeutige_kalender_gruppen_id,
              event,
            ]),
          ).values(),
        );

        if (!isCacheEnabled && hasSuccessfullyLoadedEvents) {
          const newlyLoadedEventIds = new Set(
            newlyLoadedEvents.map(
              (event) => event.eindeutige_kalender_gruppen_id,
            ),
          );

          tempAllEvents = tempAllEvents.filter((event) => {
            const eventStart = getTimestampFromISODate(event.isostart);
            const eventEnd = getTimestampFromISODate(event.isoend);
            const overlapsDisplayedDateRange =
              eventStart < currentlyDisplayedDateRange.end.ts &&
              eventEnd > currentlyDisplayedDateRange.start.ts;

            return (
              !overlapsDisplayedDateRange ||
              newlyLoadedEventIds.has(event.eindeutige_kalender_gruppen_id)
            );
          });
        }

        allEvents.value = removeVisualForEventsLoading(
          ensureEventsAreInValidCacheRange(tempAllEvents),
        );

        hasFirstLoadOccurred = true;
      });

      return;
    }

    let newlyLoadedEvents = [];
    let pendingPromiseCount = promises.length;
    let hasSuccessfullyLoadedEvents = false;

    promises.forEach((promise) => {
      Promise.resolve(promise)
        .then((res) => {
          if (res.meta.status !== "success") return;

          hasSuccessfullyLoadedEvents = true;
          if (res.meta.lv) lv.value = res.meta.lv;
          newlyLoadedEvents = newlyLoadedEvents.concat(res.data);

          let tempAllEvents = Array.from(
            new Map(
              [...allEvents.value, ...newlyLoadedEvents].map((event) => [
                event.eindeutige_kalender_gruppen_id,
                event,
              ]),
            ).values(),
          );

          if (!isCacheEnabled) {
            const newlyLoadedEventIds = new Set(
              newlyLoadedEvents.map(
                (event) => event.eindeutige_kalender_gruppen_id,
              ),
            );

            tempAllEvents = tempAllEvents.filter((event) => {
              const eventStart = getTimestampFromISODate(event.isostart);
              const eventEnd = getTimestampFromISODate(event.isoend);
              const overlapsDisplayedDateRange =
                eventStart < currentlyDisplayedDateRange.end.ts &&
                eventEnd > currentlyDisplayedDateRange.start.ts;

              return (
                !overlapsDisplayedDateRange ||
                newlyLoadedEventIds.has(event.eindeutige_kalender_gruppen_id)
              );
            });
          }

          allEvents.value = removeVisualForEventsLoading(
            ensureEventsAreInValidCacheRange(tempAllEvents),
          );
        })
        .catch(() => {})
        .finally(() => {
          pendingPromiseCount -= 1;

          if (pendingPromiseCount === 0) {
            if (!hasSuccessfullyLoadedEvents) {
              allEvents.value = removeVisualForEventsLoading(
                ensureEventsAreInValidCacheRange(allEvents.value),
              );
            }
            hasFirstLoadOccurred = true;
          }
        });
    });
  };

  Vue.watchEffect(reload);

  const reset = (arePreviousEventsCleared = true, isLoaderEventVisualReset = true) => {
 	if (arePreviousEventsCleared) {
		allEvents.value = [];
	}
	console.log('reset', arePreviousEventsCleared, isLoaderEventVisualReset);
    reload(false, isLoaderEventVisualReset);
  };

  const requestEvents = (startTimestamp, endTimestamp, isCacheEnabled = true, isLoaderEventVisualReset = false) => {
    let result = [];

    if (!startTimestamp || !endTimestamp) return result;
    if (startTimestamp >= endTimestamp) return result;

    let modifiedRequestStartTimestamp = startTimestamp;
    let modifiedRequestEndTimestamp = endTimestamp;

    if (cachedEventsStartTimestamp && cachedEventsEndTimestamp && isCacheEnabled) {
      if (
        (
			(startTimestamp < cachedEventsStartTimestamp &&
        	endTimestamp > cachedEventsEndTimestamp) ||
			(startTimestamp < cachedEventsStartTimestamp &&
        	endTimestamp < cachedEventsEndTimestamp && Math.abs(getLuxonDateFromMillis(endTimestamp).diff(getLuxonDateFromMillis(cachedEventsStartTimestamp), 'days')) > 1) ||
			(startTimestamp > cachedEventsStartTimestamp &&
        	endTimestamp > cachedEventsEndTimestamp && Math.abs(getLuxonDateFromMillis(startTimestamp).diff(getLuxonDateFromMillis(cachedEventsEndTimestamp), 'days')) > 1)
		)
      ) {		
        modifiedRequestStartTimestamp = startTimestamp;
        modifiedRequestEndTimestamp = endTimestamp;
        cachedEventsStartTimestamp = startTimestamp;
        cachedEventsEndTimestamp = endTimestamp;
      } else if (
        startTimestamp < cachedEventsStartTimestamp &&
        endTimestamp <= cachedEventsEndTimestamp
      ) {
        modifiedRequestStartTimestamp = startTimestamp;
        modifiedRequestEndTimestamp = cachedEventsStartTimestamp;
        cachedEventsStartTimestamp = startTimestamp;
      } else if (
        startTimestamp >= cachedEventsStartTimestamp &&
        endTimestamp > cachedEventsEndTimestamp
      ) {
        modifiedRequestStartTimestamp = cachedEventsEndTimestamp;
        modifiedRequestEndTimestamp = endTimestamp;
        cachedEventsEndTimestamp = endTimestamp;
      } else {
        return result;
      }
    } else if (isCacheEnabled) {
      cachedEventsStartTimestamp = modifiedRequestStartTimestamp;
      cachedEventsEndTimestamp = modifiedRequestEndTimestamp;
    }

    const cachePadding = getCachePadding();
	if (isLoaderEventVisualReset) {
		hasFirstLoadOccurred = false;
	}

    allowedCacheStartTimestamp = startTimestamp - cachePadding;
    allowedCacheEndTimestamp = endTimestamp + cachePadding;

    const requestEnd = getLuxonDateFromMillis(modifiedRequestEndTimestamp);
    let requestStart = getLuxonDateFromMillis(modifiedRequestStartTimestamp);
    const intervalDays = getRequestIntervalDays();
    const intervalEnd = requestStart
      .plus({ days: intervalDays - 1 })
      .endOf("day");

    if (requestEnd > intervalEnd) {
      while (requestStart < requestEnd) {
        let intervalRequestEnd = requestStart
          .plus({ days: intervalDays - 1 })
          .endOf("day");
        if (intervalRequestEnd > requestEnd) intervalRequestEnd = requestEnd;

        result = mergePromiseElements(
          getPromiseFunc(requestStart, intervalRequestEnd),
          result,
        );

        requestStart = intervalRequestEnd.plus({ milliseconds: 1 });
      }

      return result;
    }

	return mergePromiseElements(
      getPromiseFunc(
        getLuxonDateFromMillis(modifiedRequestStartTimestamp),
        getLuxonDateFromMillis(modifiedRequestEndTimestamp),
      ),
      result,
    );
  };

  const addVisualForEventsLoading = (startTimestamp, endTimestamp) => {
	if (hasFirstLoadOccurred) return;

	allEvents.value.push({
      loading_id: 1,
      type: "loading",
      isostart: getISODateFromTimestamp(currentlyDisplayedDateRange.start.ts),
      isoend: getISODateFromTimestamp(currentlyDisplayedDateRange.end.ts),
    });
  };

  const removeVisualForEventsLoading = (events) => {
    return events.filter((event) => event.type !== "loading");
  };

  const ensureEventsAreInValidCacheRange = (events) => {
    events = events.filter((event) => {
      const start = getTimestampFromISODate(event.isostart);
      const end = getTimestampFromISODate(event.isoend);
      return (
        allowedCacheStartTimestamp <= start && allowedCacheEndTimestamp >= end
      );
    });

    return events;
  };

  const ensureCacheRangeIsInAllowedRange = (
    allowedCacheStartTimestamp,
    allowedCacheEndTimestamp,
  ) => {
    if (
      cachedEventsStartTimestamp < allowedCacheEndTimestamp &&
      cachedEventsEndTimestamp > allowedCacheStartTimestamp
    ) {
      if (cachedEventsStartTimestamp < allowedCacheStartTimestamp) {
        cachedEventsStartTimestamp = allowedCacheStartTimestamp;
      }
      if (cachedEventsEndTimestamp > allowedCacheEndTimestamp) {
        cachedEventsEndTimestamp = allowedCacheEndTimestamp;
      }
    }
  };

  return { events: allEvents, lv, reset };
}

const mergePromiseElements = (n, o) => {
  if (Array.isArray(n)) return o.concat(n);
  return (o.push(n), o);
};

const getDateFromTimestamp = (timestamp) => {
  return luxon.DateTime.fromMillis(timestamp).toFormat("yyyy-MM-dd");
};

const getISODateFromTimestamp = (timestamp) => {
  return luxon.DateTime.fromMillis(timestamp).toISO();
};

const getTimestampFromISODate = (isoDate) => {
  return luxon.DateTime.fromISO(isoDate).ts;
};

const getLuxonDateFromMillis = (millis) => {
  return luxon.DateTime.fromMillis(millis);
};
