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
	const { edge, event, timeGrid, startTop, startHeight } = activeResize;
	const { start, end } = event;

	const durationMinutes = end.diff(start, 'minutes').minutes;
	if (!durationMinutes || durationMinutes <= 0)
		return null;

	const pxPerMinute = startHeight / durationMinutes;

	let draggedPx = 0;
	if (edge === 'end')
		draggedPx = ghostPosition.height - startHeight;
	if (edge === 'start')
		draggedPx = ghostPosition.top - startTop;

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

		const maxBottom = activeResize.gridEl.scrollHeight;
		const pointerY = getPointerYInGrid(evt);
		const draggedPx = pointerY - activeResize.dragStartY;

		if (activeResize.edge === 'end')
		{
			let newHeight = Math.max(MIN_HEIGHT_PX, activeResize.startHeight + draggedPx);
			if (activeResize.startTop + newHeight > maxBottom)
				newHeight = maxBottom - activeResize.startTop;

			ghost.updatePosition(null, newHeight);
		}
		else if (activeResize.edge === 'start')
		{
			let newTop = activeResize.startTop + draggedPx;
			let newHeight = activeResize.startHeight - draggedPx;

			if (newTop < 0)
			{
				newHeight -= (0 - newTop);
				newTop = 0;
			}

			if (newHeight < MIN_HEIGHT_PX)
			{
				newTop = (activeResize.startTop + activeResize.startHeight) - MIN_HEIGHT_PX;
				newHeight = MIN_HEIGHT_PX;
			}

			ghost.updatePosition(newTop, newHeight);
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

	function startResize(edge, evt, { el, gridEl, event, timeGrid, onEnd })
	{
		const { startTop, startHeight } = ghost.create(gridEl, el, edge);

		activeResize = {
			edge,
			pointerId: evt.pointerId,
			eventEl: el,
			gridEl,
			event,
			timeGrid,
			onEnd,
			dragStartY: (evt.clientY - gridEl.getBoundingClientRect().top) + gridEl.scrollTop,
			startTop,
			startHeight,
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