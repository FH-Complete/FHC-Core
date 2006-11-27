/**************************************************************************
	 * @brief Funktion draw_week_rdf Stundenplan im RDF-Format
	 *
	 * @param datum Datum eines Tages in der angeforderten Woche
	 *
	 * @return true oder false
	 *
	 */
	function draw_week_rdf()
	{
		// Stundentafel abfragen
		$sql_query="SELECT * FROM tbl_stunde ORDER BY stunde";
		if(!$result_stunde=pg_exec($this->conn, $sql_query))
			die(pg_last_error($this->conn));
		$num_rows_stunde=pg_numrows($result_stunde);

		//echo $this->datum;

		$rdf_url='http://www.technikum-wien.at/tempus/lehrstunde/';
		//RDF Kopf
		echo '<RDF:RDF
				xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
				xmlns:LEHRSTUNDE="'.$rdf_url.'rdf#">';

		// Von Montag bis Samstag
		for ($i=1; $i<7; $i++)
		{
			for ($k=0; $k<$num_rows_stunde; $k++)
			{
				$j=pg_result($result_stunde,$k,'"stunde"');
				if (isset($this->std_plan[$i][$j][0]->lehrfach))
				{
					// Daten aufbereiten
					$kollision=-1;
					unset($a_unr);
					foreach ($this->std_plan[$i][$j] as $lehrstunde)
						$a_unr[]=$lehrstunde->unr;

					// Unterrichtsnummer (Kollision?)
					$unr=array_unique($a_unr);
					$kollision+=count($unr);
					foreach ($a_unr as $unr)
						foreach ($this->std_plan[$i][$j] as $lehrstunde)
							if ($lehrstunde->unr==$unr)
							{
								// Ausgabe
								$lvb=$lehrstunde->stg.'-'.$lehrstunde->sem;
								if ($lehrstunde->ver!=null && $lehrstunde->ver!='0' && $lehrstunde->ver!='')
								{
									$lvb.=$lehrstunde->ver;
									if ($lehrstunde->grp!=null && $lehrstunde->grp!='0' && $lehrstunde->grp!='')
										$lvb.=$lehrstunde->grp;
								}
								echo '<RDF:Description RDF:about="'.$rdf_url.$lehrstunde->stundenplan_id.'" >
									<LEHRSTUNDE:stundenplan_id>'.$lehrstunde->stundenplan_id.'</LEHRSTUNDE:stundenplan_id>
    								<LEHRSTUNDE:lehrverband>'.$lvb.'</LEHRSTUNDE:lehrverband>
    								<LEHRSTUNDE:stg_kz>'.$lehrstunde->stg_kz.'</LEHRSTUNDE:stg_kz>
									<LEHRSTUNDE:stg>'.$lehrstunde->stg.'</LEHRSTUNDE:stg>
    								<LEHRSTUNDE:sem>'.$lehrstunde->sem.'</LEHRSTUNDE:sem>
    								<LEHRSTUNDE:ver>'.$lehrstunde->ver.'</LEHRSTUNDE:ver>
    								<LEHRSTUNDE:grp>'.$lehrstunde->grp.'</LEHRSTUNDE:grp>
    								<LEHRSTUNDE:einheit>'.$lehrstunde->einheit_kurzbz.'</LEHRSTUNDE:einheit>
    								<LEHRSTUNDE:datum>'.$lehrstunde->datum.'</LEHRSTUNDE:datum>
    								<LEHRSTUNDE:stunde>'.$lehrstunde->stunde.'</LEHRSTUNDE:stunde>
									<LEHRSTUNDE:tag>'.$i.'</LEHRSTUNDE:tag>

									<LEHRSTUNDE:kollision>'.($kollision ? 'true':'false').'</LEHRSTUNDE:kollision>

   								</RDF:Description>';
							}
				}
			}
		}

		// Sequenz Von Montag bis Samstag
		echo '<RDF:Seq RDF:about="'.$rdf_url.'alle-lehrstunden">';
		for ($i=1; $i<7; $i++)
		{
			for ($k=0; $k<$num_rows_stunde; $k++)
			{
				$j=pg_result($result_stunde,$k,'"stunde"');
				if (isset($this->std_plan[$i][$j][0]->lehrfach))
				{
					// Daten aufbereiten
					unset($a_unr);
					foreach ($this->std_plan[$i][$j] as $lehrstunde)
						$a_unr[]=$lehrstunde->unr;

					// Unterrichtsnummern
					$unr=array_unique($a_unr);
					foreach ($a_unr as $unr)
					{
						echo '<RDF:li RDF:resource="'.$rdf_url.$lehrstunde->stundenplan_id.'" />
							<RDF:li>
    							<RDF:Seq RDF:about="'.$rdf_url.$lehrstunde->stundenplan_id.'">';
						foreach ($this->std_plan[$i][$j] as $lehrstunde)
							if ($lehrstunde->unr==$unr)
							{
								// Ausgabe
								echo '<RDF:li RDF:resource="'.$rdf_url.$lehrstunde->stundenplan_id.'" />';
							}
						echo '</RDF:Seq>
    					</RDF:li>';
					}
				}
			}
		}
		echo '</RDF:Seq>';
		echo '</RDF:RDF>';
	}
	