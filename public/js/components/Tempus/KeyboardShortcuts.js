export default {
	name: "KeyboardShortcuts",
	props: {
		shortcuts: {
			type: Array,
			default: () => []
		},
	},
	methods: {
		isInputFocused(target)
		{
			if (!target)
				return false;

			return (target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement || target.isContentEditable)
		},
		shortcutMatches(shortcut, e)
		{
			let shortcutWantsCtrl = shortcut.ctrl === true;
			let ctrlIsPressed = e.ctrlKey || e.metaKey;

			if (shortcutWantsCtrl !== ctrlIsPressed)
				return false;

			if (e.key !== shortcut.key)
				return false;

			let shouldSkipWhenTyping = shortcut.skipWhenTyping !== false;
			return !(shouldSkipWhenTyping && this.isInputFocused(e.target));
		},
		handleKeydown(e)
		{
			for (let shortcut of this.shortcuts)
			{
				if (!this.shortcutMatches(shortcut, e))
					continue;

				let shouldPreventDefault = shortcut.preventDefault !== false;
				if (shouldPreventDefault)
					e.preventDefault();

				shortcut.handler(e);
				return;
			}
		}
	},
	mounted() {
		window.addEventListener('keydown', this.handleKeydown);
	},
	beforeUnmount() {
		window.removeEventListener('keydown', this.handleKeydown);
	},
	render() {
		return null;
	}
};