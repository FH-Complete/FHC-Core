== README ==
Dieser Ordner enthält Anonyme Demo Daten
Diese können auf einen leeren FH-Complete Dump angewandt werden um Demo/Testdaten einzufügen.

Die Datensätze werden mit fixen IDs erstellt um die Verknüpfung der Elemente Untereinander zu ermöglichen.
Danach werden die Sequences entsprechend aktualisiert.

Die Inserts können daher nur auf einen leeren Dump gespielt werden da es sonst zu ID Konflikten kommmen kann.

== Logik der ID Generierung ==

Studiengangskennzahl 1
Studierende des Studiengangs 1 haben Person ID 3-Stellig mit Studiengangskennzahl startend : 101, 102, 103
PrestudentID ist 4-Stellig mit Studiengangskennzahl+PersonID: 1101, 1102, 1103
Dadurch können Inserts leichter erstellt werden.

Bei Lehrveranstaltungen gilt ähnliches Konzept für
Studienordnung
Studienplan
Modul
Lehrveranstaltung

== Helper Funktionen ==
Damit die Daten immer im aktuellen Semester angelegt werden gibt es eine Helper Funktion damit das Studiensemester nicht hartcodiert werden muss:

=== NearestWintersemester ===

NearestWintersemester(0) -> Liefert das näheste Wintersemester
NearestWintersemester(-1) -> Liefert Studiensemester VOR dem nähesten Wintersemester
NearestWintersemester(+1) -> Liefert Studiensemester NACH dem nähesten Wintersemester


