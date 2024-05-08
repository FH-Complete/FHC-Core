<html>
<head>
	<title>Interessentenfreigabe mail</title>
</head>
<body>
<div class="container">
	{interessentbez} wurde freigegeben.
	<br><br>
	<table style="font-size:small">
		<tbody>
			<tr>
				<td><b>Studiengang</b></td>
				<td>{studiengangbez}&nbsp;{studiengangtypbez} {orgform} {sprache}</td>
			</tr>
			<tr>
				<td><b>Studiensemester</b></td>
				<td>{studiensemester}</td>
			</tr>
			<tr>
				<td><b>Geschlecht</b></td>
				<td>{geschlecht}</td>
			</tr>
			<tr>
				<td><b>Vorname</b></td>
				<td>{vorname}</td>
			</tr>
			<tr>
				<td><b>Nachname</b></td>
				<td>{nachname}</td>
			</tr>
			<tr>
				<td><b>Geburtsdatum</b></td>
				<td>{gebdatum}</td>
			</tr>
			<tr>
				<td><b>E-Mail Adresse</b></td>
				<td>{mailadresse}</td>
			</tr>
			<tr>
				<td><b>Prestudent ID</b></td>
				<td>{prestudentid}</td>
			</tr>
			<tr>
				<td><b>Zugangsvoraussetzung</b></td>
				<td>{zgvbez}{zgvort}{zgvnation}{zgvdatum}</td>
			</tr>
			<tr>
				<td valign="top"><b>Erbrachte Dokumente</b></td>
				<td>
					{dokumente}
					{dokument_bezeichnung}
					{/dokumente}
				</td>
			</tr>
			<tr>
				<td valign="top"><b>Nachzureichende Dokumente</b></td>
				<td>
					{dokumente_nachgereicht}
					{dokument_bezeichnung}{anmerkung}{nachgereicht_am}
					<br>
					{/dokumente_nachgereicht}
				</td>
			</tr>
			<tr>
				<td valign="top"><b>Anmerkungen zur Bewerbung</b></td>
				<td>{notizentext}</td>
			</tr>
		</tbody>
	</table>
	<br>
	Für mehr Details verwenden Sie die Personenansicht im FAS.
</div>
</body>
</html>

