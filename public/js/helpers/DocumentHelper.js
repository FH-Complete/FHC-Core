export function highlightGesamtnote(zeugnisTable, teacherTable)
{
	let zeugnisData = zeugnisTable.getData();

	let studentsWithNote = new Set();

	zeugnisData.forEach(row => {
		let student = row.uid;
		let lv = row.lehrveranstaltung_id;
		let note = row.note;

		if (!student || !lv)
			return;

		if (note == null || note === "")
			return;

		let key = `${student}_${lv}`;
		studentsWithNote.add(key);
	});

	teacherTable.deselectRow();

	teacherTable.getRows().forEach(row => {
		let data = row.getData();

		let student = data.student_uid;
		let lv = data.lehrveranstaltung_id;
		let note = data.note;

		if (!student || !lv)
			return;

		if (note == null || note === "")
			return;

		let key = `${student}_${lv}`;

		if (!studentsWithNote.has(key))
		{
			row.select();
		}
	});
}