export function getTempusShortcuts(self) {
	return [
		{
			key: 'F',
			ctrl: true,
			skipWhenTyping: false, 
			handler: () => self.focusSearchbar()
		},
		{
			key: '1',
			ctrl: true,
			skipWhenTyping: false,
			handler: () => self.handleChangeMode('month')
		},
		{
			key: '2',
			ctrl: true,
			skipWhenTyping: false,
			handler: () => self.handleChangeMode('week')
		},
		{
			key: '3',
			ctrl: true,
			skipWhenTyping: false,
			handler: () => self.handleChangeMode('tableList')
		},
		{
			key: 'ArrowLeft',
			ctrl: true,
			skipWhenTyping: false,
			handler: () => self.$refs.calendar.navigatePrev()
		},
		{
			key: 'ArrowRight',
			ctrl: true,
			skipWhenTyping: false,
			handler: () => self.$refs.calendar.navigateNext()
		},
		{ 
			key: 'p',
			skipWhenTyping: true,
			handler: () => self.parkHoveredEvent()
		},
		{ 
			key: 'P',
			skipWhenTyping: true,
			handler: () => self.parkHoveredEvent()
		},
		{
			key: 'Delete',
			skipWhenTyping: true,
			handler: () => self.deleteHoveredEvent()
		},

	];
}