let dragEndCallback = null;

onconnect = e => {
	const port = e.ports[0];

	const cbList = [];

	port.onmessage = e => {
		const [ func, ...args ] = e.data;
		switch (func) {
		case 'init':
			dragEndCallback = () => {
				port.postMessage(['fire', args]);
			};
			break;
		case 'block':
			cbList[args[0]] = dragEndCallback;
			dragEndCallback = null;
			break;
		case 'unblock':
			cbList[args[0]]();
			break;
		case 'request':
			if (dragEndCallback) {
				dragEndCallback();
				dragEndCallback = null;
			}
		}
	};
};
