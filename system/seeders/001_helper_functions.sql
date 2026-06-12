CREATE OR REPLACE FUNCTION NearestWintersemester(var_entfernung integer) RETURNS varchar(32) AS $$
DECLARE res varchar(32);
	BEGIN
		WITH nearestws AS
		(
			SELECT studiensemester_kurzbz, start FROM public.tbl_studiensemester WHERE studiensemester_kurzbz LIKE 'W%' AND ende>=now() ORDER BY start DESC LIMIT 1
		)
		SELECT studiensemester_kurzbz into res 
		FROM 
		(
			SELECT studiensemester_kurzbz, 0 as "entfernung" FROM nearestws
			UNION
			SELECT studiensemester_kurzbz, rank() over(order by start desc) as "entfernung" FROM public.tbl_studiensemester where start>(SELECT start FROM nearestws)
			UNION
			SELECT studiensemester_kurzbz, (-1 * rank() over(order by start desc)) as "entfernung" FROM public.tbl_studiensemester WHERE start<(SELECT start FROM nearestws)
		) x where entfernung=var_entfernung;
		
		return res;
	END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION CurrentSemester() RETURNS varchar(32) AS $$
DECLARE res varchar(32);
	BEGIN
		SELECT studiensemester_kurzbz into res FROM public.tbl_studiensemester WHERE start<=now() AND ende>=now() ORDER BY start DESC LIMIT 1;
		return res;
	END;
$$ LANGUAGE plpgsql;