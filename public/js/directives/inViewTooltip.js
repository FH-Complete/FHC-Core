/**
 * v-tooltip with a panel that stays in view, also at the screen edge, in a modal and on a phone.
 *
 * - The panel is fixed and stays inside the viewport. It opens below the anchor, above it when
 *   it fits only there or that side has more space. It grows away from the nearer viewport side.
 * - On an anchor wider than the panel, e.g. a long table cell, it opens at the mouse, not at the anchor edge.
 * - One panel at a time, appended to the body. A modal or a scrolling container cannot clip it.
 * - A mouse hover or a keyboard focus opens it. Escape, a click outside or a scroll closes it.
 *
 * Sticky, for several sentences or a list: a short label is read before the hover pause ends, a long text is not.
 * - After HOLD_DELAY_MS + HOLD_FILL_MS of hovering the panel sticks, the border fills up in the end.
 *   A click on the anchor makes it stick at once. Touch has no hover: a tap toggles the sticky panel.
 * - A sticky panel stays until a click outside, Escape, a scroll or an opening bootstrap modal.
 *
 * The view needs public/css/components/inViewTooltip.css.
 *
 * Usage examples:
 *
 * 1) Directive, registered for the app or the component:
 *    app.directive('tooltip', InViewTooltip)  or  directives: { tooltip: InViewTooltip }
 *    <button v-tooltip="label">           a short label, hover only
 *    <span v-tooltip.sticky="longText">   several sentences or a list
 *    v-tooltip="{ value: text, disabled: bool, sticky: bool }", the modifier .top prefers the place above
 *
 * 2) Without a directive, e.g. a tabulator cell. The content is a text, a DOM node or a function:
 *    { event: 'cellMouseEnter', handler: (e, cell) => hoverTooltip(cell.getElement(), () => buildNode(cell), { delay: 300, sticky: true, closeOnClick: true }) },
 *    { event: 'cellMouseLeave', handler: (e, cell) => hideTooltip(cell.getElement()) }
 *
 * 3) A ? icon with a help text: components/InViewHelp.js
 */

// hover time until a sticky panel sticks: a pause without motion filters out a pointer that only passes by,
// then a short fill shows the pin. A long fill would draw the eye away from the text
const HOLD_DELAY_MS = 1000
const HOLD_FILL_MS = 400
const HIDE_DELAY_MS = 150 // the pointer can cross the gap from the anchor into the panel
const WATCH_MS = 250 // an anchor can leave the page without an unmount, e.g. in a hidden keep-alive tab
const PANEL_MAX_WIDTH = 512 // px
const PANEL_MARGIN = 16 // px between the panel and the viewport edge
const PANEL_GAP = 6 // px between the anchor and the panel
const PANEL_RADIUS = 5 // px, the inner radius of the panel border. The progress border follows it
const PANEL_POINTER_INSET = 16 // px, the horizontal padding of the panel: the text starts below the mouse

const clamp = (value, min, max) => Math.min(Math.max(value, min), max)

let active = null // the open panel, only one at a time
let pending = null // a delayed show
let panelCount = 0

// the last pointer. Its type tells a tap from a mouse also for the mouse events of tabulator, a tap fires
// emulated mouse events too: pointerover fires before them. Its place puts the panel next to the mouse
const pointer = { type: 'mouse', x: null, y: null }
const trackPointer = event => {
	pointer.type = event.pointerType
	pointer.x = event.clientX
	pointer.y = event.clientY
}
for (const name of ['pointerover', 'pointermove', 'pointerdown'])
	document.addEventListener(name, trackPointer, { capture: true, passive: true })

/**
 * Opens the panel for an anchor element.
 *
 * content: a text, a DOM node or a function that returns one of them.
 * options: prefer ('bottom' | 'top'), delay (ms), sticky (the hover timer pins the panel), pinned (sticks at once),
 *          closeOnClick (a click on the anchor closes the panel, e.g. a table cell with a click action)
 */
export function showTooltip(anchor, content, options = {}) {
	cancelPending()
	if (!options.delay) return open(anchor, content, options)

	pending = { anchor, timer: setTimeout(() => { pending = null; open(anchor, content, options) }, options.delay) }
}

// for mouse events without a pointer type, e.g. the cell events of tabulator
export function hoverTooltip(anchor, content, options) {
	if (pointer.type === 'mouse') showTooltip(anchor, content, options)
}

// closes the panel of the anchor after HIDE_DELAY_MS, a sticky panel stays
export function hideTooltip(anchor) {
	if (pending?.anchor === anchor) cancelPending()
	if (active?.anchor !== anchor || active.pinned) return

	clearTimeout(active.hideTimer)
	active.hideTimer = setTimeout(close, HIDE_DELAY_MS)
}

// closes the open panel, with an anchor only the panel of that anchor
export function closeTooltip(anchor) {
	if (!anchor || pending?.anchor === anchor) cancelPending()
	if (!anchor || active?.anchor === anchor) close()
}

function isTooltipPinned(anchor) {
	return active?.anchor === anchor && active.pinned
}

// new content for an open panel, an empty text closes it
function updateTooltip(anchor, content) {
	if (active?.anchor !== anchor) return
	if (!content) return close()

	render(active.panel, content)
	place()
}

function open(anchor, content, { prefer = 'bottom', sticky = false, pinned = false, closeOnClick = false }) {
	if (active?.anchor === anchor) {
		clearTimeout(active.hideTimer)
		if (pinned) pin()
		return
	}
	close()

	sticky = sticky || pinned
	const panel = document.createElement('div')
	panel.id = 'fhc-in-view-tooltip-' + ++panelCount
	panel.className = 'fhc-in-view-tooltip'
	panel.setAttribute('role', 'tooltip')
	panel.innerHTML = '<div class="fhc-in-view-tooltip-body"></div>'
	if (sticky) {
		panel.style.setProperty('--fhc-in-view-tooltip-hold-delay', HOLD_DELAY_MS + 'ms')
		panel.style.setProperty('--fhc-in-view-tooltip-hold-fill', HOLD_FILL_MS + 'ms')
		panel.insertAdjacentHTML('beforeend', '<svg class="fhc-in-view-tooltip-progress" aria-hidden="true">'
			+ '<rect width="100%" height="100%" rx="' + PANEL_RADIUS + '"></rect></svg>')
	}
	render(panel, content)
	document.body.appendChild(panel)

	// the pointer at the opening, while it is over the anchor. A keyboard focus has none
	const rect = anchor.getBoundingClientRect()
	const overAnchor = pointer.x !== null && pointer.x >= rect.left && pointer.x <= rect.right
		&& pointer.y >= rect.top && pointer.y <= rect.bottom

	active = {
		anchor,
		panel,
		prefer,
		pointerX: overAnchor ? pointer.x : null,
		sticky,
		closeOnClick,
		pinned: false,
		describedBy: anchor.getAttribute('aria-describedby'),
		holdTimer: null,
		hideTimer: null,
		watchTimer: setInterval(() => {
			if (!anchor.isConnected || !anchor.getClientRects().length) close()
		}, WATCH_MS)
	}
	place()
	anchor.setAttribute('aria-describedby', panel.id)

	if (pinned) {
		pin()
	} else if (sticky) {
		panel.classList.add('fhc-in-view-tooltip--holding')
		active.holdTimer = setTimeout(pin, HOLD_DELAY_MS + HOLD_FILL_MS)
	}

	panel.addEventListener('pointerenter', onPanelEnter)
	panel.addEventListener('pointerleave', onPanelLeave)
	panel.addEventListener('click', onPanelClick)
	document.addEventListener('pointerdown', onPointerDown, true)
	document.addEventListener('keydown', onKeyDown, true)
	// an anchor often opens a modal on click, the sticky panel must not cover it
	document.addEventListener('show.bs.modal', close)
	window.addEventListener('scroll', onScroll, true)
	window.addEventListener('resize', place)
}

function pin() {
	if (!active || active.pinned || !active.sticky) return

	clearTimeout(active.holdTimer)
	clearTimeout(active.hideTimer)
	active.pinned = true
	active.panel.classList.remove('fhc-in-view-tooltip--holding')
	active.panel.classList.add('fhc-in-view-tooltip--pinned')
}

function close() {
	if (!active) return

	const { anchor, panel, describedBy } = active
	clearTimeout(active.holdTimer)
	clearTimeout(active.hideTimer)
	clearInterval(active.watchTimer)
	active = null

	panel.remove()
	if (describedBy) anchor.setAttribute('aria-describedby', describedBy)
	else anchor.removeAttribute('aria-describedby')

	document.removeEventListener('pointerdown', onPointerDown, true)
	document.removeEventListener('keydown', onKeyDown, true)
	document.removeEventListener('show.bs.modal', close)
	window.removeEventListener('scroll', onScroll, true)
	window.removeEventListener('resize', place)
}

function cancelPending() {
	if (!pending) return

	clearTimeout(pending.timer)
	pending = null
}

function render(panel, content) {
	if (typeof content === 'function') content = content()

	const body = panel.querySelector('.fhc-in-view-tooltip-body')
	if (content instanceof Node) body.replaceChildren(content)
	else body.textContent = content
}

function place() {
	if (!active) return

	const { anchor, panel, prefer } = active
	const rect = anchor.getBoundingClientRect()
	const viewWidth = document.documentElement.clientWidth
	const viewHeight = document.documentElement.clientHeight

	// measure the natural size first
	panel.style.maxWidth = Math.min(PANEL_MAX_WIDTH, viewWidth - 2 * PANEL_MARGIN) + 'px'
	panel.style.maxHeight = ''
	panel.style.left = '0px'
	panel.style.top = '0px'
	panel.style.bottom = ''
	const width = panel.offsetWidth
	const height = panel.offsetHeight

	const space = {
		bottom: viewHeight - rect.bottom - PANEL_GAP - PANEL_MARGIN,
		top: rect.top - PANEL_GAP - PANEL_MARGIN
	}
	const other = prefer === 'top' ? 'bottom' : 'top'
	let side = prefer
	if (space[prefer] < height) side = space[other] >= height || space[other] > space[prefer] ? other : prefer

	// a panel at least as wide as the anchor starts at the anchor edge, grows away from the nearer viewport side
	// and spans the whole anchor. On a wider anchor that edge can be far from the mouse: the text starts below
	// the pointer, the clamp moves the panel to the left only at the right viewport edge
	const { pointerX } = active
	let left
	if (pointerX === null || width >= rect.width) left = rect.left + rect.width / 2 < viewWidth / 2 ? rect.left : rect.right - width
	else left = pointerX - PANEL_POINTER_INSET
	panel.style.left = clamp(left, PANEL_MARGIN, viewWidth - PANEL_MARGIN - width) + 'px'
	panel.style.maxHeight = space[side] + 'px'
	if (side === 'bottom') {
		panel.style.top = rect.bottom + PANEL_GAP + 'px'
	} else {
		panel.style.top = ''
		panel.style.bottom = viewHeight - rect.top + PANEL_GAP + 'px'
	}

	if (!active.sticky) return

	// the dash of the progress border is as long as the rounded rect along the inner edge of the 1 px border.
	// Rounded up, else a gap stays at its start: clientWidth drops the fraction of the width
	const box = panel.getBoundingClientRect()
	const perimeter = 2 * (box.width + box.height - 4) - (8 - 2 * Math.PI) * PANEL_RADIUS
	panel.style.setProperty('--fhc-in-view-tooltip-perimeter', Math.ceil(perimeter) + 1)
}

function onPanelEnter() {
	cancelPending()
	clearTimeout(active.hideTimer)
}

function onPanelLeave(event) {
	if (event.pointerType === 'mouse') hideTooltip(active.anchor)
}

// a click into the panel closes it, a text selection keeps it
function onPanelClick() {
	if (!String(window.getSelection())) close()
}

function onPointerDown(event) {
	const { anchor, panel, closeOnClick } = active
	if (panel.contains(event.target)) return
	if (anchor.contains(event.target) && !closeOnClick) return

	cancelPending()
	close()
}

// Escape closes only the panel, not a modal around the anchor
function onKeyDown(event) {
	if (event.key !== 'Escape') return

	event.stopPropagation()
	close()
}

// the panel is fixed, a scroll moves the anchor away from it. A scroll inside the panel keeps it
function onScroll(event) {
	if (!active.panel.contains(event.target)) close()
}

// reads the binding: "text" or { value, disabled, sticky }, the modifiers .sticky and .top
function read(state, binding) {
	const value = binding.value
	const isObject = value && typeof value === 'object'
	state.text = (isObject ? (value.disabled ? '' : value.value) : value) ?? ''
	state.sticky = !!(binding.modifiers.sticky || isObject && value.sticky)
	state.prefer = binding.modifiers.top ? 'top' : 'bottom'
}

export default {
	mounted(el, binding) {
		const state = el._fhcInViewTooltip = { pointerType: 'mouse' }
		read(state, binding)
		const show = options => {
			if (state.text) showTooltip(el, state.text, { prefer: state.prefer, sticky: state.sticky, ...options })
		}

		state.listeners = {
			pointerenter: event => { if (event.pointerType === 'mouse') show() },
			pointerleave: event => { if (event.pointerType === 'mouse') hideTooltip(el) },
			pointerdown: event => { state.pointerType = event.pointerType },
			// a mouse click or a key pins a sticky panel at once, a tap toggles it
			click: () => {
				if (!state.sticky) return

				if (state.pointerType !== 'mouse' && isTooltipPinned(el)) closeTooltip(el)
				else show({ pinned: true })

				state.pointerType = 'mouse' // a key click has no pointerdown
			},
			focusin: event => { if (event.target.matches(':focus-visible')) show() },
			focusout: () => hideTooltip(el)
		}
		// the click captures: it pins the panel before the click action of the anchor runs, so a modal
		// that this action opens closes the panel again. The pointer events must not capture the children
		for (const [name, listener] of Object.entries(state.listeners)) el.addEventListener(name, listener, name === 'click')
	},
	updated(el, binding) {
		const state = el._fhcInViewTooltip
		const text = state.text
		read(state, binding)
		if (state.text !== text) updateTooltip(el, state.text)
	},
	beforeUnmount(el) {
		const state = el._fhcInViewTooltip
		closeTooltip(el)
		for (const [name, listener] of Object.entries(state.listeners)) el.removeEventListener(name, listener, name === 'click')
		delete el._fhcInViewTooltip
	}
}
