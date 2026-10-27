export function highlightGesamtnote(zeugnisTable, teacherTable)
{
	let zeugnisData = zeugnisTable.getData();

	let studentsWithNote = {};

	zeugnisData.forEach(row => {
		let student = row.uid;
		let lv = row.lehrveranstaltung_id;
		let note = row.note;
		let datum = row.benotungsdatum;

		if (!student || !lv)
			return;

		if (note == null || note === "")
			return;

		let key = `${student}_${lv}`;

		if (!studentsWithNote[key])
			studentsWithNote[key] = [];

		studentsWithNote[key].push({ note: note, datum: datum });
	});

	teacherTable.deselectRow();

	teacherTable.getRows().forEach(row => {
		let data = row.getData();

		let student = data.student_uid;
		let lv = data.lehrveranstaltung_id;
		let note = data.note;
		let datum = data.benotungsdatum;

		if (!student || !lv)
			return;

		if (note == null || note === "")
			return;

		let key = `${student}_${lv}`;
		let found = false;

		let zeugnisEintraege = studentsWithNote[key] || [];

		zeugnisEintraege.forEach(eintrag => {
			if (eintrag.note === note && eintrag.datum === datum)
				found = true;

			if (eintrag.datum > datum)
				found = true;
		});

		if (!found)
		{
			row.select();
		}
	});
}