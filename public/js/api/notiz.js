//TODO(Manu) refactor for extensions with require or async
//sonst Error wenn extension file nicht vorhanden

import person from "./notiz/person.js";
import prestudent from "./notiz/prestudent.js";
import mitarbeiter from "./notiz/mitarbeiter.js";
import projekt from "./notiz/projekt.js";
import anrechnung from "./notiz/anrechnung.js";
import bestellung from "./notiz/bestellung.js";
import lehreinheit from "./notiz/lehreinheit.js";
import projektphase from "./notiz/projektphase.js";
import projekttask from "./notiz/projekttask.js";
//import softwarenotiz from "../../extensions/FHC-Core-Softwarebereitstellung/js/api/softwarenotiz.js";
//import pppnotiz from "../../extensions/FHC-Core-PEP/js/api/pppnotiz.js";


export default {
	person,
	prestudent,
	mitarbeiter,
	anrechnung,
	bestellung,
	lehreinheit,
	projekt,
	projektphase,
	projekttask,
//	softwarenotiz,
//	pppnotiz
}