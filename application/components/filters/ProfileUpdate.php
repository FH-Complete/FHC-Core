<?php
$filterCmptArray = array(
	"app" => 'core',
	'datasetName' => 'profileupdate',
	'query' =>  '
			SELECT
				profil_update_id,
				tbl_profil_update.uid,
				(tbl_person.vorname || \' \' || tbl_person.nachname) AS name ,
				topic,
				requested_change,
				tbl_profil_update.updateamum,
				tbl_profil_update.updatevon,
				tbl_profil_update.insertamum,
				tbl_profil_update.insertvon,
				status,
				public.tbl_profil_update_status.bezeichnung_mehrsprachig[(" . $lang . ")] as status_translated,
				status_timestamp,
				status_message,
				attachment_id,
				UPPER(public.tbl_studiengang.typ || public.tbl_studiengang.kurzbz) AS studiengang,
				COALESCE(of.orgform_kurzbz, public.tbl_studiengang.orgform_kurzbz) AS orgform,
				NULL as oezuordnung,
				tbl_student.semester
			FROM public.tbl_profil_update
			JOIN public.tbl_profil_update_status ON public.tbl_profil_update_status.status_kurzbz = public.tbl_profil_update.status 
			JOIN public.tbl_student ON public.tbl_student.student_uid=public.tbl_profil_update.uid
			JOIN public.tbl_benutzer ON public.tbl_benutzer.uid = public.tbl_student.student_uid
			JOIN public.tbl_person ON public.tbl_benutzer.person_id=public.tbl_person.person_id
			JOIN public.tbl_studiengang ON public.tbl_studiengang.studiengang_kz=public.tbl_student.studiengang_kz
			LEFT JOIN (
				select
					pss.prestudent_id, COALESCE(sp.orgform_kurzbz, pss.orgform_kurzbz) as orgform_kurzbz
				from (
					select
						prestudent_id, max(insertamum) as insertamum
					from
						public.tbl_prestudentstatus
					where
						datum <= NOW()
					group by
						prestudent_id
				) mpss
				join
					public.tbl_prestudentstatus pss on pss.prestudent_id  = mpss.prestudent_id and pss.insertamum = mpss.insertamum
				left join
					lehre.tbl_studienplan sp on pss.studienplan_id = sp.studienplan_id
			) of ON of.prestudent_id = public.tbl_student.prestudent_id
		',
	'requiredPermissions' => 'student/stammdaten'
);