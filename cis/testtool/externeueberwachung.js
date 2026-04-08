(function () {
	function sendMessage() {
		let frame = window.frames['content'];
		if (frame)
			frame.postMessage({ type: "proctoringReady" });
	}

	window.addEventListener("message", function (e)
	{
		if (e.data.indexOf("proctoringReady_") === 0)
		{
			sendMessage();
		}
	});
})();
