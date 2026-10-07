import InViewTooltip from '../directives/inViewTooltip.js';

/**
 * ? icon with a help text in the in-view tooltip. The panel stays inside the viewport, also at the
 * screen edge, in a modal and on a phone: a plain tooltip ran out of the screen there.
 *
 * Help texts are long, so the panel is sticky by default: it sticks after a hover of 1.4 s or a click,
 * a tap toggles it on touch. sticky: false makes it a plain hover tooltip.
 *
 * The view needs public/css/components/inViewTooltip.css.
 *
 * Usage: <in-view-help :text="$p.t('category/phrase')" button-class="fs-5 text-body-secondary"></in-view-help>
 */
export default {
	name: 'InViewHelp',
	directives: {
		tooltip: InViewTooltip
	},
	props: {
		text: { type: String, default: '' },
		sticky: { type: Boolean, default: true },
		buttonClass: { type: [String, Array, Object], default: '' }
	},
	template: /*html*/ `
	<button
		type="button"
		class="btn btn-link p-0 fhc-in-view-help-btn"
		:class="buttonClass"
		:aria-label="$p.t('ui/hilfe')"
		v-tooltip="{ value: text, sticky }"
	>
		<i class="fa fa-circle-question" aria-hidden="true"></i>
	</button>`
};
