import { useResizeGhost } from './ResizeGhost.js';

const MIN_HEIGHT_PX = 20;
const MIN_DURATION_MIN = 5;
const SNAP_MINUTES = 5;

function snapToGrid(minutes, edge)
{
	if (edge === 'start')
	{
		return minutes >= 0 ? Math.floor(minutes / SNAP_MINUTES) * SNAP_MINUTES : Math.ceil(minutes / SNAP_MINUTES) * SNAP_MINUTES;
	}

	return minutes >= 0 ? Math.ceil(minutes / SNAP_MINUTES) * SNAP_MINUTES : Math.floor(minutes / SNAP_MINUTES) * SNAP_MINUTES;
}

function getSnapTimes(timeGrid, dayISO, zoneName)
{
	const parseTime = (time) =>
	{
		if (!time)
			return null;
		let dt = luxon.DateTime.fromFormat(`${dayISO} ${time}`, 'yyyy-MM-dd HH:mm:ss', { zone: zoneName });

		if (!dt.isValid)
			dt = luxon.DateTime.fromFormat(`${dayISO} ${time}`, 'yyyy-MM-dd HH:mm', { zone: zoneName });

		return dt.isValid ? dt : null;
	};

	const startTimes = timeGrid.map(s => parseTime(s?.start)).filter(Boolean);
	const endTimes = timeGrid.map(s => parseTime(s?.end)).filter(Boolean);

	const sort = arr => arr.sort((a, b) => a.toMillis() - b.toMillis());

	return {
		start: sort(startTimes),
		end: sort(endTimes),
	};
}

function calculateNewTimes(activeResize, ghostPosition)
{
	const { edge, event, timeGrid, horizontal, startTop, startHeight, startLeft, startWidth } = activeResize;
	const { start, end } = event;

	const durationMinutes = end.diff(start, 'minutes').minutes;
	if (!durationMinutes || durationMinutes <= 0)
		return null;

	const startSize = horizontal ? startWidth : startHeight;
	if (!Number.isFinite(startSize) || startSize <= 0)
		return null;

	const pxPerMinute = startSize / durationMinutes;

	let draggedPx = 0;
	if (edge === 'end')
		draggedPx = horizontal
			? ghostPosition.width - startWidth
			: ghostPosition.height - startHeight;
	if (edge === 'start')
		draggedPx = horizontal
			? ghostPosition.left - startLeft
			: ghostPosition.top - startTop;
	if (!Number.isFinite(draggedPx))
		return null;

	const draggedMinutes = snapToGrid(draggedPx / pxPerMinute, edge);

	let newStart = start;
	let newEnd = end;

	if (edge === 'start')
		newStart = start.plus({ minutes: draggedMinutes });
	if (edge === 'end')
		newEnd = end.plus({ minutes: draggedMinutes });

	if (Array.isArray(timeGrid) && timeGrid.length)
	{
		const snapTimes = getSnapTimes(timeGrid, start.toISODate(), start.zoneName);

		if (edge === 'start')
		{
			const targets = snapTimes.start;
			newStart = [...targets].reverse().find(t => t <= newStart) || targets[0];
		}
		else
		{
			const targets = snapTimes.end;
			newEnd = targets.find(t => t >= newEnd) || targets[targets.length - 1];
		}
	}

	return { newStart, newEnd };
}


export function useResizeHandler() {
	const ghost = useResizeGhost();

	let activeResize = null;

	function getPointerYInGrid(evt)
	{
		const gridRect = activeResize.gridEl.getBoundingClientRect();
		return (evt.clientY - gridRect.top) + activeResize.gridEl.scrollTop;
	}

	function getPointerXInGrid(evt)
	{
		const gridRect = activeResize.gridEl.getBoundingClientRect();
		return (evt.clientX - gridRect.left) + activeResize.gridEl.scrollLeft;
	}

	function updateGhostLabel()
	{
		const result = calculateNewTimes(activeResize, ghost.getPosition());
		if (!result)
			return;
		ghost.updateLabel(`${result.newStart.toFormat('HH:mm')}–${result.newEnd.toFormat('HH:mm')}`);
	}

	function onPointerMove(evt)
	{
		if (!activeResize || evt.pointerId !== activeResize.pointerId)
			return;
		evt.preventDefault();

		const maxEnd = activeResize.horizontal
			? activeResize.gridEl.scrollWidth
			: activeResize.gridEl.scrollHeight;
		const pointer = activeResize.horizontal
			? getPointerXInGrid(evt)
			: getPointerYInGrid(evt);
		const draggedPx = pointer - (activeResize.horizontal
			? activeResize.dragStartX
			: activeResize.dragStartY);

		if (activeResize.edge === 'end')
		{
			const minSize = MIN_HEIGHT_PX;
			const newSize = Math.max(minSize, (activeResize.horizontal
				? activeResize.startWidth
				: activeResize.startHeight) + draggedPx);
			const startPosition = activeResize.horizontal
				? activeResize.startLeft
				: activeResize.startTop;
			const constrainedSize = Math.min(newSize, maxEnd - startPosition);

			if (activeResize.horizontal)
				ghost.updatePosition(null, null, constrainedSize);
			else
				ghost.updatePosition(null, constrainedSize);
		}
		else if (activeResize.edge === 'start')
		{
			const startPosition = activeResize.horizontal
				? activeResize.startLeft
				: activeResize.startTop;
			const startSize = activeResize.horizontal
				? activeResize.startWidth
				: activeResize.startHeight;
			let newPosition = startPosition + draggedPx;
			let newSize = startSize - draggedPx;

			if (newPosition < 0)
			{
				newSize -= (0 - newPosition);
				newPosition = 0;
			}

			if (newSize < MIN_HEIGHT_PX)
			{
				newPosition = (startPosition + startSize) - MIN_HEIGHT_PX;
				newSize = MIN_HEIGHT_PX;
			}

			if (activeResize.horizontal)
				ghost.updatePosition(null, null, newSize, newPosition);
			else
				ghost.updatePosition(newPosition, newSize);
		}

		updateGhostLabel();
	}

	function onPointerUp(evt)
	{
		if (!activeResize || evt.pointerId !== activeResize.pointerId)
			return;

		window.removeEventListener('pointermove', onPointerMove);
		window.removeEventListener('pointerup', onPointerUp);

		if (activeResize.eventEl)
			activeResize.eventEl.style.opacity = activeResize.originalOpacity ?? '';

		const result = calculateNewTimes(activeResize, ghost.getPosition());

		ghost.remove();

		if (result)
		{
			if ((activeResize.event.start.toISO() !== result.newStart.toISO()) || (activeResize.event.end.toISO() !== result.newEnd.toISO()))
			{
				activeResize.onEnd({
					event: activeResize.event,
					newStart: result.newStart.toISO(),
					newEnd: result.newEnd.toISO()
				});
			}
		}

		activeResize = null;
	}

	function startResize(edge, evt, { el, gridEl, event, horizontal = false, timeGrid, onEnd })
	{
		const { startTop, startHeight, startLeft, startWidth } = ghost.create(gridEl, el, edge);

		activeResize = {
			edge,
			pointerId: evt.pointerId,
			eventEl: el,
			gridEl,
			event,
			horizontal,
			timeGrid,
			onEnd,
			dragStartY: (evt.clientY - gridEl.getBoundingClientRect().top) + gridEl.scrollTop,
			dragStartX: (evt.clientX - gridEl.getBoundingClientRect().left) + gridEl.scrollLeft,
			startTop,
			startHeight,
			startLeft,
			startWidth,
			originalOpacity: el.style.opacity,
		};

		el.style.opacity = '0.35';
		ghost.updateLabel(`${event.start.toFormat('HH:mm')}–${event.end.toFormat('HH:mm')}`);

		evt.currentTarget.setPointerCapture(evt.pointerId);
		window.addEventListener('pointermove', onPointerMove, { passive: false });
		window.addEventListener('pointerup', onPointerUp, { passive: false });
	}

	function cleanup()
	{
		if (!activeResize)
			return;

		window.removeEventListener('pointermove', onPointerMove);
		window.removeEventListener('pointerup', onPointerUp);
		ghost.remove();
		activeResize = null;
	}

	return { startResize, cleanup };
}
