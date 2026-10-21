export function useResizeGhost() {
	let ghostEl = null;
	let labelEl = null;

	function create(gridEl, eventEl, edge)
	{
		const gridRect = gridEl.getBoundingClientRect();
		const eventRect = eventEl.getBoundingClientRect();
		const scrollTop = gridEl.scrollTop;

		const topInGrid = (eventRect.top - gridRect.top) + scrollTop;
		const leftInGrid = eventRect.left - gridRect.left;

		ghostEl = document.createElement('div');
		ghostEl.className = 'fhc-event-ghost';
		ghostEl.style.cssText = `
			position: absolute;
			left: ${leftInGrid}px;
			width: ${eventRect.width}px;
			top: ${topInGrid}px;
			height: ${eventRect.height}px;
			z-index: 9999;
			pointer-events: none;
			box-sizing: border-box;
			border-radius: 6px;
			outline: 2px dashed currentColor;
			opacity: 0.9;
		`;

		labelEl = document.createElement('div');
		labelEl.className = 'fhc-resize-preview';
		labelEl.style.cssText = `
			position: absolute;
			right: 6px;
			padding: 2px 6px;
			border-radius: 6px;
			font-size: 12px;
			background: rgba(0,0,0,0.75);
			color: white;
			white-space: nowrap;
			pointer-events: none;
		`;

		if (edge === 'start')
		{
			labelEl.style.top = '6px';
			labelEl.style.bottom = 'auto';
		}
		else
		{
			labelEl.style.top = 'auto';
			labelEl.style.bottom = '6px';
		}

		ghostEl.appendChild(labelEl);

		gridEl.style.position = 'relative';
		gridEl.appendChild(ghostEl);

		return {
			startTop: topInGrid,
			startHeight: eventRect.height
		};
	}

	function updateLabel(text)
	{
		if (labelEl)
			labelEl.textContent = text;
	}

	function updatePosition(top, height)
	{
		if (!ghostEl)
			return;
		if (top !== null)
			ghostEl.style.top = `${top}px`;
		if (height !== null)
			ghostEl.style.height = `${height}px`;
	}

	function getPosition()
	{
		if (!ghostEl)
			return { top: 0, height: 0 };
		return {
			top: parseFloat(ghostEl.style.top),
			height: parseFloat(ghostEl.style.height)
		};
	}

	function remove()
	{
		if (ghostEl?.parentNode)
			ghostEl.parentNode.removeChild(ghostEl);

		ghostEl = null;
		labelEl = null;
	}

	return { create, updateLabel, updatePosition, getPosition, remove };
}