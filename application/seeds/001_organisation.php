<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Seed_Organisation
{

		public function seed($limit = 25)
        {
			// Organisationseinheiten
			echo "Seeding more than $limit OEs ";
			$this->fhc =& get_instance();
			$this->fhc->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');

			$pre = 'INSERT INTO public.tbl_organisationseinheit VALUES (';
			$post = '; ';

			//$sql .= "INSERT INTO public.tbl_organisationseinheittyp VALUES ('Seminar'); ";
			$sql = "INSERT INTO public.tbl_organisationseinheittyp VALUES ('Lehrgang'); ";
			$sql .= "INSERT INTO public.tbl_studiengangstyp VALUES ('d', 'Diplom'); ";

			$sql .= "$pre 'fhstp', NULL, 'Fachhochschule St. Pölten', 'Erhalter', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL)$post";
			$sql .= "$pre 'depcon', 'fhstp', 'Studienzentrum', 'Studienzentrum', false, false, NULL, NULL, false, NULL, NULL, NULL, NULL)$post";
			$sql .= "$pre 'depwi', 'depcon', 'Medien und Wirtschaft', 'Studienzentrum', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL)$post";
			$sql .= "$pre 'lmsc', 'depwi', 'LMSC', 'Lehrgang', true, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'depgus', 'depcon', 'Gesundheit', 'Studienzentrum', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'ldem', 'depgus', 'LDEM', 'Lehrgang', true, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'depsoz', 'depcon', 'Soziales', 'Studienzentrum', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lsbz', 'depsoz', 'LSBZ', 'Lehrgang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'bum', 'depwi', 'BUM', 'Studiengang', false, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lmom', 'depwi', 'LMOM', 'Lehrgang', true, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lmmo', 'depwi', 'LMMO', 'Lehrgang', true, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lmem', 'depwi', 'LMEM', 'Lehrgang', true, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'bdi', 'depgus', 'BDI', 'Studiengang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lsom', 'depsoz', 'LSOM', 'Lehrgang', true, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'laet', 'depgus', 'LAET', 'Lehrgang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'bpt', 'depgus', 'BPT', 'Studiengang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'depet', 'depcon', 'Bahntechnologie und Mobilität', 'Studienzentrum', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'mbm', 'depet', 'MBM', 'Studiengang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'bmk', 'depwi', 'BMK', 'Studiengang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'deptech', 'depcon', 'Medien und Digitale Technologien', 'Studienzentrum', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'levd', 'deptech', 'LEVD', 'Lehrgang', false, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'bmt', 'deptech', 'BMT', 'Studiengang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lsma', 'depsoz', 'LSMA', 'Lehrgang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'levm', 'depwi', 'LEVM', 'Lehrgang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'dso', 'depsoz', 'DSO', 'Studiengang', false, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lmev', 'depwi', 'LMEV', 'Lehrgang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'levt', 'deptech', 'LEVT', 'Lehrgang', false, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lcma', 'depwi', 'LCMA', 'Lehrgang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lpmf', 'deptech', 'LPMF', 'Lehrgang', true, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'mdh', 'deptech', 'MDH', 'Studiengang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lafg', 'deptech', 'LAFG', 'Lehrgang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lpwk', 'deptech', 'LPWK', 'Lehrgang', true, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lwkf', 'deptech', 'LWKF', 'Lehrgang', true, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'bdb', 'depwi', 'BDB', 'Studiengang', false, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'dtm', 'deptech', 'DTM', 'Studiengang', false, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'dmm', 'depwi', 'DMM', 'Studiengang', false, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'dcs', 'deptech', 'DCS', 'Studiengang', false, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'bid', 'deptech', 'BID', 'Studiengang', false, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lvjg', 'depwi', 'LVJG', 'Lehrgang', false, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lrdm', 'depwi', 'LRDM', 'Lehrgang', false, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lptm', 'deptech', 'LPTM', 'Lehrgang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'mid', 'deptech', 'MID', 'Studiengang', false, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lebs', 'depet', 'LEBS', 'Lehrgang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'depit', 'depcon', 'Informatik und Security', 'Studienzentrum', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'mis', 'depit', 'MIS', 'Studiengang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'bmm', 'depwi', 'BMM', 'Studiengang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'bso', 'depsoz', 'BSO', 'Studiengang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'mmm', 'depwi', 'MMM', 'Studiengang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'msa', 'depsoz', 'MSA', 'Studiengang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lfmr', 'depsoz', 'LFMR', 'Lehrgang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lspm', 'depsoz', 'LSPM', 'Lehrgang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'mso', 'depsoz', 'MSO', 'Studiengang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lsbl', 'depsoz', 'LSBL', 'Lehrgang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lsbm', 'depsoz', 'LSBM', 'Lehrgang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'bbm', 'depet', 'BBM', 'Studiengang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'mdm', 'deptech', 'MDM', 'Studiengang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'mmk', 'depwi', 'MMK', 'Studiengang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'bse', 'deptech', 'BSE', 'Studiengang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lspa', 'depsoz', 'LSPA', 'Lehrgang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'ltti', 'depet', 'LTTI', 'Lehrgang', true, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lcmm', 'depwi', 'LCMM', 'Lehrgang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lmcm', 'depwi', 'LMCM', 'Lehrgang', false, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lits', 'depit', 'LITS', 'Lehrgang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'bis', 'depit', 'BIS', 'Studiengang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'bgk', 'depgus', 'BGK', 'Studiengang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lfea', 'depet', 'LFEA', 'Lehrgang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lsak', 'depsoz', 'LSAK', 'Seminar', true, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lksp', 'depsoz', 'LKSP', 'Seminar', true, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lpvp', 'depgus', 'LPVP', 'Lehrgang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'latm', 'deptech', 'LATM', 'Lehrgang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lftm', 'deptech', 'LFTM', 'Lehrgang', true, false, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lsmm', 'depsoz', 'LSMM', 'Lehrgang', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lsbt', 'depet', 'LSBT', 'Seminar', true, true, NULL, NULL, true, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'gw', 'depgus', 'Institut für Gesundheitswissenschaften', 'Institut', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'fhsc', 'fhstp', 'Services', 'Abteilung', false, false, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'fhshsm', 'fhsc', 'Services für Hochschulmanagement und -Organisation', 'Abteilung', false, false, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'peui', 'fhshsm', 'Programmentwicklung und Innovation', 'Abteilung', false, false, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'pers_recht', 'fhshsm', 'Personal und Recht', 'Abteilung', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'fhsl', 'fhsc', 'Services für Lehre', 'Abteilung', false, false, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'skill', 'fhsl', 'SKILL (Service- und Kompetenzzentrum für Innovatives Lehren und Lernen)', 'Abteilung', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'mkt', 'fhshsm', 'Marketing und Unternehmenskommunikation', 'Abteilung', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'nsc', 'fhshsm', 'IT und Infrastruktur', 'Abteilung', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'qm', 'fhshsm', 'Qualitätsmanagement', 'Abteilung', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'frw', 'fhshsm', 'Finanzwesen und Controlling', 'Abteilung', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'bibl', 'fhshsm', 'Bibliothek', 'Abteilung', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'fhsfwt', 'fhsc', 'Services für Forschung und Wissenstransfer', 'Abteilung', false, false, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'csc', 'fhshsm', 'Campus Service Center', 'Abteilung', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'acc', 'fhshsm', 'Alumni und Career Center', 'Abteilung', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'luso', 'fhsl', 'Lehr- und Studienorganisation', 'Abteilung', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'fh_kollegium', 'fhstp', 'FH-Kollegium', 'Abteilung', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'ro', 'fhsfwt', 'Forschung und Wissenstransfer', 'Abteilung', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'io', 'fhsfwt', 'International Office', 'Abteilung', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'recht', 'pers_recht', 'Recht', 'Lehrgang', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'lppm', 'deptech', 'LPPM', 'Lehrgang', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'mi', 'deptech', 'Medieninformatik', 'Institut', false, false, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'ia', 'depsoz', 'Ilse Arlt Institut für Soziale Inklusionsforschung', 'Institut', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'icmt', 'deptech', 'Institut für CreativeMediaTechnologies', 'Institut', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'gf', 'fhstp', 'Geschäftsführung', 'Abteilung', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'sf', 'depit', 'Institut für IT Sicherheitsforschung', 'Institut', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'mw', 'depwi', 'Institut für Medienwirtschaft', 'Institut', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'crvg', 'depet', 'Carl Ritter von Ghega Institut für integrierte Mobilitätsforschung', 'Institut', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'hsm', 'fhstp', 'Hochschulmanagement', 'Abteilung', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'cr', 'fhstp', 'Campus Radio', 'Abteilung', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'abfctv', 'deptech', 'Ausbildungsfernsehen c-tv', 'Abteilung', true, false, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";
			$sql .= "$pre 'jrz', 'depit', 'Josef Ressel-Zentrum für konsolidierte Erkennung gezielter Angriffe', 'Studienzentrum', true, true, NULL, NULL, false, NULL, NULL, NULL, NULL) $post";

			// Studiengaenge
			$pre = 'INSERT INTO public.tbl_studiengang VALUES ';
			$post = '; ';

			$sql .= "$pre (3, 'bum', 'BUM', 'b', 'BUM', 'BUM', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (4, 'bdi', 'BDI', 'b', 'BDI', 'BDI', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (5, 'bpt', 'BPT', 'b', 'BPT', 'BPT', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (6, 'mbm', 'MBM', 'm', 'MBM', 'MBM', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (7, 'bmk', 'BMK', 'b', 'BMK', 'BMK', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (8, 'bmt', 'BMT', 'b', 'BMT', 'BMT', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (9, 'dso', 'DSO', 'd', 'DSO', 'DSO', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (10, 'mdh', 'MDH', 'm', 'MDH', 'MDH', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (11, 'bdb', 'BDB', 'b', 'BDB', 'BDB', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (12, 'dtm', 'DTM', 'd', 'DTM', 'DTM', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (13, 'dmm', 'DMM', 'd', 'DMM', 'DMM', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (14, 'dcs', 'DCS', 'd', 'DCS', 'DCS', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (15, 'bid', 'BID', 'b', 'BID', 'BID', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (16, 'mid', 'MID', 'm', 'MID', 'MID', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (17, 'mis', 'MIS', 'm', 'MIS', 'MIS', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (18, 'bmm', 'BMM', 'b', 'BMM', 'BMM', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (19, 'bso', 'BSO', 'b', 'BSO', 'BSO', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (20, 'mmm', 'MMM', 'm', 'MMM', 'MMM', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (21, 'msa', 'MSA', 'm', 'MSA', 'MSA', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (22, 'mso', 'MSO', 'm', 'MSO', 'MSO', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (23, 'bbm', 'BBM', 'b', 'BBM', 'BBM', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (24, 'mdm', 'MDM', 'm', 'MDM', 'MDM', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (25, 'mmk', 'MMK', 'm', 'MMK', 'MMK', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (26, 'bse', 'BSE', 'b', 'BSE', 'BSE', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (27, 'bis', 'BIS', 'b', 'BIS', 'BIS', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";
			$sql .= "$pre (28, 'bgk', 'BGK', 'b', 'BGK', 'BGK', NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, 'VZ')$post";


			$this->fhc->db->query($sql);
		    echo PHP_EOL;
          
		}

        public function truncate()
        {
              //$this->db->query('EMPTY TABLE public.person;');
        }
}

