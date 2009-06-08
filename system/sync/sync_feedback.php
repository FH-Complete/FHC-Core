<?php

   include("../../vilesci/config.inc.php");

   //Connection öffnen
   if(!$conn = pg_pconnect(CONN_STRING))
      die("Fehler beim oeffnen der DB Connection");

   /**
    *Schaut nach ob dieser Feedbackeintrag bereits in der Datenbank existiert.
    * @param $datum Datum des Eintrages
    *        $betreff Betreff des Eintrages
    *        $text Text des Eintrages
    *        $uid Uid des Eintrages
    */
   function isInTab($datum,$betreff,$text,$uid)
   {
      $sql = "Select * from tbl_feedback where datum='$datum' AND betreff='$betreff' AND text='$text' and uid='$uid'";
      $checkresult=pg_exec($sql);
      if(pg_num_rows($checkresult)>0)
          return true;
      return false;
   }

   $qry = "SELECT * from lehre.tbl_feedback_lehrfach";
   $result = pg_exec($conn,$qry);

   echo "Uebernahme von tbl_feedback_lehrfach";
   flush();
   while($row=pg_fetch_object($result))
   {
   	   $sql_qry = "Select * from tbl_lehrfach where studiengang_kz =$row->studiengang_kz AND semester=$row->semester AND lehrevz=$lehrfachzuteilung_kurzbz";

   	   $lf_result=pg_exec($conn,$sql_qry);
   	   if($lf_row=pg_fetch_object($conn,$qry))
   	   {
   	   	  if(!isInTab($row->datum,$row->betreff,$row->text,$row->uid))
   	   	  {
	   	   	  $insert = "INSERT INTO tbl_feedback(datum,betreff,text,lfnr,uid) VALUES('$row->datum','$row->betreff','$row->text',$lf_row->lehrfach_nr,'$row->uid')";
	   	   	  pg_exec($conn,$insert);
	   	   	  echo "+";
	   	   	  flush();
   	   	  }
   	   }
   	   else
   	   {
   	      echo "Lehrfachaufloesung nicht moeglich"; //Oder Formular ausgeben
   	   }
   }

   $qry = "SELECT * from lehre.tbl_feedback_lehrfach";
   $result = pg_exec($conn,$qry);

   echo "Uebernahme von tbl_feedback_freifach";
   flush();
   while($row=pg_fetch_object($result))
   {
   	   $sql_qry = "Select * from tbl_lehrfach where lehrevz=$lehrfachzuteilung_kurzbz AND studiengang_kz=0 AND semester is null";

   	   $lf_result=pg_exec($conn,$sql_qry);
   	   if($lf_row=pg_fetch_object($conn,$qry))
   	   {
   	   	  $insert = "INSERT INTO tbl_feedback(datum,betreff,text,lfnr,uid) VALUES('$row->datum','$row->betreff','$row->text',$lf_row->lehrfach_nr,'$row->uid')";
   	   	  pg_exec($conn,$insert);
   	   	  echo "+";
   	   	  flush();
   	   }
   	   else
   	   {
   	      echo "Lehrfachaufloesung nicht moeglich"; //Oder Formular ausgeben
   	   }
   }




?>