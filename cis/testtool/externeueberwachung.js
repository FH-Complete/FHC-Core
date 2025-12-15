(function () {
	/*

	const params = new URLSearchParams(location.search);
	let expectedOrigin = params.get("examus-client-origin");

	if (!expectedOrigin)
	{
		window.top.location.href = 'resetconnection.php';
		return;
	}
	*/
	let proctoringOK = false;

	window.addEventListener("message", function (e) {
		/*if (e.origin !== expectedOrigin) {
			return;
		}*/

		const data = e.data || {};

		if (data.proctoringIsActive)
		{
			proctoringOK = true;
		}
	});

	setTimeout(function () {
		if (!proctoringOK)
		{
			top.location.href='resetconnection.php';
		}
	}, 1000);
})();