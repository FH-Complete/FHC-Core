(function () {
	let ok = false;
	let blocker;

	function showBlocker() {
		blocker = document.getElementById("proctoringBlocker");

		if (!blocker)
		{
			blocker = document.createElement("div");
			blocker.id = "proctoringBlocker";
			blocker.className = "proctoring-blocker";
			blocker.innerHTML = '<div class="proctoring-text">Loading...</div>';
			document.body.appendChild(blocker);
		}
		document.documentElement.classList.add("proctoring-blur-fallback");
	}

	function block() {
		showBlocker();
		blocker.classList.remove("hidden");
	}

	function unblock() {
		document.documentElement.classList.remove("proctoring-blur-fallback");
		if (!blocker) return;
		blocker.classList.add("hidden");
	}

	const blockTimer = setTimeout(function () {
		if (!ok)
			block();
	}, 1500);

	window.addEventListener("message", function (e) {
		const data = e.data || {};

		if (data.type === "proctoringReady")
		{
			ok = true;
			clearTimeout(blockTimer);
			unblock();
		}
	});

	setTimeout(function () {
		if (!ok) {
			top.location.href = "resetconnection.php";
		}
	}, 3000);
})();


