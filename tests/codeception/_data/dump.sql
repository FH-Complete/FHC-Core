-- UPDATE tbl_studiengang
UPDATE public.tbl_studiengang SET onlinebewerbung = TRUE;

-- EMPTY public.tbl_preinteressent
DELETE FROM public.tbl_preinteressent;
-- EMPTY public.tbl_prestudentstatus
DELETE FROM public.tbl_prestudentstatus;
-- EMPTY public.tbl_prestudent
DELETE FROM public.tbl_prestudent;
-- EMPTY lehre.tbl_studienplan
DELETE FROM lehre.tbl_studienplan_semester;
-- EMPTY lehre.tbl_studienplan
DELETE FROM lehre.tbl_studienplan;
-- EMPTY lehre.tbl_studienordnung_semester
DELETE FROM lehre.tbl_studienordnung_semester;
-- EMPTY lehre.tbl_studienordnung
DELETE FROM lehre.tbl_studienordnung;
-- EMPTY public.tbl_studienjahr
DELETE FROM public.tbl_studienjahr;
-- EMPTY public.tbl_ort
DELETE FROM public.tbl_ort;
-- EMPTY public.tbl_kontakt
DELETE FROM public.tbl_kontakt WHERE person_id > 2;
-- EMPTY public.tbl_benutzer
DELETE FROM public.tbl_benutzer WHERE person_id > 2;
-- EMPTY public.tbl_preinteressent
DELETE FROM public.tbl_preinteressent WHERE person_id > 2;
-- EMPTY public.tbl_person
DELETE FROM public.tbl_person WHERE person_id > 2;

-- INSERT Persons (public.tbl_person)
INSERT INTO public.tbl_person VALUES (3, NULL, NULL, NULL, NULL, NULL, NULL, 'McKenzie', 'Vicenta', 'Abraham', '2002-12-30', 'Brooksburgh', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.624239', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '01234567A', false);
INSERT INTO public.tbl_person VALUES (4, NULL, NULL, NULL, NULL, NULL, NULL, 'Wilderman', 'Rocio', 'Jayson', '2002-09-03', 'Hermannshire', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.632551', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '01234567B', false);
INSERT INTO public.tbl_person VALUES (5, NULL, NULL, NULL, NULL, NULL, NULL, 'Harvey', 'Joshuah', 'Halie', '1930-01-18', 'Mitchellville', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.634179', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '01234567C', false);
INSERT INTO public.tbl_person VALUES (6, NULL, NULL, NULL, NULL, NULL, NULL, 'Kessler', 'Neil', 'Dashawn', '1948-06-10', 'Anitafort', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.63728', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '01234567D', false);
INSERT INTO public.tbl_person VALUES (7, NULL, NULL, NULL, NULL, NULL, NULL, 'Little', 'Marvin', 'Hassie', '1933-11-14', 'Chayatown', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.641078', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (8, NULL, NULL, NULL, NULL, NULL, NULL, 'Hamill', 'Roselyn', 'Retha', '1931-05-16', 'Horacioview', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.644311', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (9, NULL, NULL, NULL, NULL, NULL, NULL, 'Hartmann', 'Marcel', 'Porter', '1948-01-07', 'Lake Eulaliaborough', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.647474', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (10, NULL, NULL, NULL, NULL, NULL, NULL, 'Rice', 'Hilda', 'Gerson', '1950-07-04', 'Louveniahaven', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.650108', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (11, NULL, NULL, NULL, NULL, NULL, NULL, 'Wisozk', 'Bethel', 'Watson', '2009-08-27', 'Leuschkemouth', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.652681', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (12, NULL, NULL, NULL, NULL, NULL, NULL, 'Wisozk', 'Flavio', 'Valentin', '1979-05-25', 'Cindyville', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.655173', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (13, NULL, NULL, NULL, NULL, NULL, NULL, 'Graham', 'Francisca', 'Camron', '1953-05-08', 'North Furman', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.657715', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (14, NULL, NULL, NULL, NULL, NULL, NULL, 'Baumbach', 'Nickolas', 'Sherman', '1988-12-10', 'New Breana', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.659352', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (15, NULL, NULL, NULL, NULL, NULL, NULL, 'Stiedemann', 'Alexandre', 'Era', '1935-04-26', 'Robynland', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.660843', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (16, NULL, NULL, NULL, NULL, NULL, NULL, 'Koss', 'Stuart', 'Onie', '2013-10-07', 'Mauriceville', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.662338', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (17, NULL, NULL, NULL, NULL, NULL, NULL, 'Conroy', 'Royce', 'Ollie', '1948-05-10', 'Lake Derick', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.663905', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (18, NULL, NULL, NULL, NULL, NULL, NULL, 'Hand', 'Stuart', 'Adonis', '2008-11-26', 'Schimmelville', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.665442', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (19, NULL, NULL, NULL, NULL, NULL, NULL, 'Howell', 'Delmer', 'Vance', '1944-04-19', 'New Ritaville', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.667043', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (20, NULL, NULL, NULL, NULL, NULL, NULL, 'Towne', 'Nikita', 'Devin', '2004-12-30', 'Lindshire', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.668476', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (21, NULL, NULL, NULL, NULL, NULL, NULL, 'Reynolds', 'Oran', 'Gennaro', '2013-04-13', 'Aliciafort', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.670087', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (22, NULL, NULL, NULL, NULL, NULL, NULL, 'Quitzon', 'Billie', 'Dagmar', '2015-01-07', 'Blickstad', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.673039', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (23, NULL, NULL, NULL, NULL, NULL, NULL, 'Wisoky', 'Lonnie', 'Stacey', '1957-11-25', 'Lake Carolyn', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.676088', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (24, NULL, NULL, NULL, NULL, NULL, NULL, 'Gottlieb', 'Marianna', 'Lesley', '2005-04-13', 'Jacobsonport', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.679072', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (25, NULL, NULL, NULL, NULL, NULL, NULL, 'Schuster', 'Terrell', 'Jordy', '1917-06-10', 'Monicaport', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.682185', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (26, NULL, NULL, NULL, NULL, NULL, NULL, 'Reilly', 'Sadye', 'Trever', '1988-03-06', 'Port Lesly', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.684774', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (27, NULL, NULL, NULL, NULL, NULL, NULL, 'Langosh', 'Scarlett', 'Madie', '1959-04-20', 'Jewelside', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.687341', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (28, NULL, NULL, NULL, NULL, NULL, NULL, 'Paucek', 'Will', 'Lester', '1982-02-19', 'South Patience', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.831546', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (29, NULL, NULL, NULL, NULL, NULL, NULL, 'Champlin', 'Tamia', 'Shayne', '1996-06-05', 'Nathanielmouth', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.835765', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (30, NULL, NULL, NULL, NULL, NULL, NULL, 'Collier', 'Robyn', 'Dana', '2015-04-04', 'Gleichnerhaven', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.837262', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (31, NULL, NULL, NULL, NULL, NULL, NULL, 'Kassulke', 'Eleonore', 'Monty', '1941-08-14', 'West Joport', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.838802', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (32, NULL, NULL, NULL, NULL, NULL, NULL, 'Sauer', 'Danial', 'Deon', '1951-01-28', 'West Jovannyfurt', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.841786', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (33, NULL, NULL, NULL, NULL, NULL, NULL, 'Volkman', 'Jaycee', 'Nola', '1998-09-30', 'North Joany', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.84491', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (34, NULL, NULL, NULL, NULL, NULL, NULL, 'Blanda', 'Margarett', 'Foster', '1972-06-04', 'Lake Roger', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.848049', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (35, NULL, NULL, NULL, NULL, NULL, NULL, 'Stamm', 'Eduardo', 'Elton', '1991-12-08', 'West Laverna', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.851258', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (36, NULL, NULL, NULL, NULL, NULL, NULL, 'Ratke', 'Name', 'Mellie', '1947-09-06', 'East Terrance', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.853933', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (37, NULL, NULL, NULL, NULL, NULL, NULL, 'Brakus', 'Consuelo', 'Fabian', '1978-04-16', 'Mistyville', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.85665', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (38, NULL, NULL, NULL, NULL, NULL, NULL, 'Bode', 'Brianne', 'Yesenia', '1921-02-27', 'Port Antwanview', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.859328', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (39, NULL, NULL, NULL, NULL, NULL, NULL, 'Schmidt', 'Jaiden', 'Greyson', '1997-06-30', 'Othofort', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.862145', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (40, NULL, NULL, NULL, NULL, NULL, NULL, 'Lemke', 'Cooper', 'Eliane', '1990-12-18', 'North Brodychester', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.863992', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (41, NULL, NULL, NULL, NULL, NULL, NULL, 'Doyle', 'Gerhard', 'Birdie', '1932-07-23', 'West Marcia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.86628', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (42, NULL, NULL, NULL, NULL, NULL, NULL, 'Macejkovic', 'Jermain', 'Sophie', '1941-08-08', 'Leoraburgh', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.869542', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (43, NULL, NULL, NULL, NULL, NULL, NULL, 'McKenzie', 'Alisa', 'Jaclyn', '1967-11-14', 'Port Hillaryhaven', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.871741', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (44, NULL, NULL, NULL, NULL, NULL, NULL, 'Kling', 'Hailie', 'Brayan', '1967-04-26', 'Lake Hopetown', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.873744', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (45, NULL, NULL, NULL, NULL, NULL, NULL, 'Lebsack', 'Bo', 'Arno', '1988-08-19', 'Alexandreamouth', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.875473', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (46, NULL, NULL, NULL, NULL, NULL, NULL, 'Grant', 'Stacey', 'Magnolia', '1978-11-17', 'Lake Sonya', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.877099', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (47, NULL, NULL, NULL, NULL, NULL, NULL, 'Daniel', 'Nels', 'Guy', '1925-11-09', 'North Palma', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.878715', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (48, NULL, NULL, NULL, NULL, NULL, NULL, 'Veum', 'Rey', 'Candelario', '1989-12-10', 'Calliemouth', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.881668', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (49, NULL, NULL, NULL, NULL, NULL, NULL, 'Stokes', 'Berniece', 'Noel', '1938-11-24', 'Hamillhaven', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.884717', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (50, NULL, NULL, NULL, NULL, NULL, NULL, 'Haley', 'Mustafa', 'Hyman', '1973-09-19', 'East Allie', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.887636', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (51, NULL, NULL, NULL, NULL, NULL, NULL, 'Harris', 'Diana', 'Lilly', '1920-05-20', 'Lempiberg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.890715', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (52, NULL, NULL, NULL, NULL, NULL, NULL, 'Kovacek', 'Nya', 'Chesley', '1990-03-11', 'Goldnerland', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 16:03:08.89322', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (53, NULL, NULL, NULL, NULL, NULL, NULL, 'Dibbert', 'Tad', 'Neil', '1974-04-09', 'East Eunaland', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:11:56.824758', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (54, NULL, NULL, NULL, NULL, NULL, NULL, 'Batz', 'Ceasar', 'Janie', '1924-09-30', 'West Laurenfort', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.722855', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (55, NULL, NULL, NULL, NULL, NULL, NULL, 'Boyer', 'Amely', 'Joelle', '1998-01-18', 'Port Odell', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.727985', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (56, NULL, NULL, NULL, NULL, NULL, NULL, 'Hand', 'Walker', 'Francisca', '1926-12-21', 'Rozellahaven', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.731695', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (57, NULL, NULL, NULL, NULL, NULL, NULL, 'Zulauf', 'Juana', 'Hermann', '1949-06-30', 'Lake Lawsontown', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.735121', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (58, NULL, NULL, NULL, NULL, NULL, NULL, 'Becker', 'Dennis', 'Madilyn', '1995-07-20', 'Alyceberg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.738065', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (59, NULL, NULL, NULL, NULL, NULL, NULL, 'Hauck', 'German', 'Frederic', '1947-02-03', 'Lake Jordonfort', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.740693', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (60, NULL, NULL, NULL, NULL, NULL, NULL, 'Erdman', 'Tyreek', 'Robyn', '1949-07-17', 'Jacobsonborough', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.742964', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (61, NULL, NULL, NULL, NULL, NULL, NULL, 'Considine', 'Birdie', 'Irwin', '1984-01-04', 'Port Felicia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.745164', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (62, NULL, NULL, NULL, NULL, NULL, NULL, 'Gleason', 'Jaleel', 'Linnea', '2002-01-25', 'Corkeryville', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.747215', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (63, NULL, NULL, NULL, NULL, NULL, NULL, 'Mertz', 'Simone', 'Yoshiko', '2015-04-09', 'Port Quinton', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.749773', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (64, NULL, NULL, NULL, NULL, NULL, NULL, 'Rempel', 'Andres', 'Toby', '2010-03-30', 'South Reyesfort', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.75352', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (65, NULL, NULL, NULL, NULL, NULL, NULL, 'Schuster', 'Nat', 'Garett', '1941-04-01', 'Port Kurtiston', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.757301', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (66, NULL, NULL, NULL, NULL, NULL, NULL, 'Morar', 'Golda', 'Sammie', '1983-12-17', 'Lake Ottilietown', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.76097', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (67, NULL, NULL, NULL, NULL, NULL, NULL, 'Beatty', 'Diamond', 'Emmitt', '1965-04-14', 'Port Romainefurt', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.764847', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (68, NULL, NULL, NULL, NULL, NULL, NULL, 'Roberts', 'Kris', 'Christina', '1997-02-26', 'Lednerberg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.76866', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (69, NULL, NULL, NULL, NULL, NULL, NULL, 'Shanahan', 'Adell', 'Elza', '1945-06-15', 'North Adolphmouth', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.771946', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (70, NULL, NULL, NULL, NULL, NULL, NULL, 'Heidenreich', 'Florida', 'Estel', '1974-02-05', 'Allenechester', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.775166', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (71, NULL, NULL, NULL, NULL, NULL, NULL, 'O''Kon', 'May', 'Jovany', '1952-07-07', 'Carolynborough', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.778744', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (72, NULL, NULL, NULL, NULL, NULL, NULL, 'Schaefer', 'Grayce', 'Nathanial', '2014-11-03', 'Port Elody', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.782136', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (73, NULL, NULL, NULL, NULL, NULL, NULL, 'Ullrich', 'Jaydon', 'Kimberly', '1978-04-27', 'Ellenstad', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.784361', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (74, NULL, NULL, NULL, NULL, NULL, NULL, 'Borer', 'Tracey', 'Nona', '1918-08-12', 'Monahanton', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.786497', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (75, NULL, NULL, NULL, NULL, NULL, NULL, 'Jast', 'Asha', 'Duane', '1919-01-19', 'Zoieside', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.788705', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (76, NULL, NULL, NULL, NULL, NULL, NULL, 'Schamberger', 'Alexandrine', 'Eulah', '1955-03-09', 'Alvenachester', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.790916', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (77, NULL, NULL, NULL, NULL, NULL, NULL, 'Schiller', 'Ardella', 'Rebekah', '1944-05-21', 'North Josue', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:12:00.793131', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (78, NULL, NULL, NULL, NULL, NULL, NULL, 'Tremblay', 'Jay', 'Carter', '2002-09-19', 'West Margot', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.334168', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (79, NULL, NULL, NULL, NULL, NULL, NULL, 'Durgan', 'Lavinia', 'Veda', '1979-04-09', 'West Ernestineshire', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.338264', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (80, NULL, NULL, NULL, NULL, NULL, NULL, 'Hodkiewicz', 'Tom', 'Verla', '1938-04-24', 'West Zelmafurt', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.341592', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (81, NULL, NULL, NULL, NULL, NULL, NULL, 'Schulist', 'Arno', 'Tania', '1926-10-02', 'Hamillton', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.344909', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (82, NULL, NULL, NULL, NULL, NULL, NULL, 'Rogahn', 'Cassidy', 'Gregg', '1981-12-22', 'East Reyes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.349505', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (83, NULL, NULL, NULL, NULL, NULL, NULL, 'Kuhlman', 'Deion', 'Shany', '1995-08-29', 'Bergeland', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.352268', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (84, NULL, NULL, NULL, NULL, NULL, NULL, 'Blick', 'Elsie', 'Sven', '1926-08-21', 'Rickyfort', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.355178', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (85, NULL, NULL, NULL, NULL, NULL, NULL, 'Kuvalis', 'Tina', 'Kathlyn', '1966-07-02', 'New Kieran', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.358004', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (86, NULL, NULL, NULL, NULL, NULL, NULL, 'Cremin', 'Consuelo', 'Floy', '1935-08-16', 'Amaramouth', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.359951', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (87, NULL, NULL, NULL, NULL, NULL, NULL, 'O''Keefe', 'Maia', 'Emma', '1956-12-13', 'Jordimouth', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.361816', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (88, NULL, NULL, NULL, NULL, NULL, NULL, 'Rohan', 'Sydni', 'Katrine', '1983-11-10', 'New Vicentaton', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.363604', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (89, NULL, NULL, NULL, NULL, NULL, NULL, 'Quitzon', 'Riley', 'Emmet', '1953-08-24', 'Rhodaport', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.365404', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (90, NULL, NULL, NULL, NULL, NULL, NULL, 'Kautzer', 'Sheridan', 'Elijah', '1921-03-04', 'Mullerchester', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.367166', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (91, NULL, NULL, NULL, NULL, NULL, NULL, 'Keeling', 'Oren', 'Kiel', '1998-04-30', 'Wymantown', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.368904', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (92, NULL, NULL, NULL, NULL, NULL, NULL, 'Oberbrunner', 'Alfredo', 'Lila', '1930-12-10', 'North Jodie', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.370777', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (93, NULL, NULL, NULL, NULL, NULL, NULL, 'Von', 'Treva', 'Kara', '1928-08-16', 'Carrollburgh', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.372534', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (94, NULL, NULL, NULL, NULL, NULL, NULL, 'McLaughlin', 'Stanford', 'Peyton', '1956-09-03', 'West Tylertown', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.376105', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (95, NULL, NULL, NULL, NULL, NULL, NULL, 'Gutkowski', 'Buddy', 'Narciso', '1946-05-07', 'Orphafort', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.379542', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (96, NULL, NULL, NULL, NULL, NULL, NULL, 'Romaguera', 'General', 'Alan', '1940-05-21', 'New Linnea', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.38308', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (97, NULL, NULL, NULL, NULL, NULL, NULL, 'Schmeler', 'Leon', 'Abdul', '2005-10-02', 'Rosenbaumburgh', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.386484', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (98, NULL, NULL, NULL, NULL, NULL, NULL, 'O''Kon', 'Lilliana', 'Polly', '1918-06-04', 'North Emmy', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.389329', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (99, NULL, NULL, NULL, NULL, NULL, NULL, 'Carter', 'Robb', 'Delfina', '2006-10-18', 'West Maiaberg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.392189', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (100, NULL, NULL, NULL, NULL, NULL, NULL, 'Larkin', 'Nona', 'Trenton', '1965-08-02', 'Port Esteban', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.395106', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (101, NULL, NULL, NULL, NULL, NULL, NULL, 'Stokes', 'Otilia', 'Citlalli', '2006-07-15', 'Katherinemouth', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.397878', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (102, NULL, NULL, NULL, NULL, NULL, NULL, 'Aufderhar', 'Mauricio', 'Ahmad', '1945-01-21', 'North Brandonhaven', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-28 17:15:10.39983', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (103, NULL, NULL, NULL, NULL, NULL, NULL, 'Kunde', 'Vance', 'Alycia', '1954-12-25', 'Nathanielbury', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.344253', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (104, NULL, NULL, NULL, NULL, NULL, NULL, 'Kerluke', 'Elliot', 'Genoveva', '2006-12-02', 'Danieltown', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.356141', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (105, NULL, NULL, NULL, NULL, NULL, NULL, 'Lubowitz', 'Gilbert', 'Else', '2010-12-28', 'Cartwrightshire', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.358948', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (106, NULL, NULL, NULL, NULL, NULL, NULL, 'Gaylord', 'Jaunita', 'Griffin', '1932-01-15', 'North Aaron', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.361642', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (107, NULL, NULL, NULL, NULL, NULL, NULL, 'Rowe', 'Selena', 'Corene', '1976-11-11', 'East Raheem', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.365763', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (108, NULL, NULL, NULL, NULL, NULL, NULL, 'Pacocha', 'Chester', 'Sedrick', '1937-04-16', 'Willmsview', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.369766', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (109, NULL, NULL, NULL, NULL, NULL, NULL, 'Hauck', 'Josiane', 'Leopold', '1998-02-12', 'New Clarabelleshire', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.373884', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (110, NULL, NULL, NULL, NULL, NULL, NULL, 'Dicki', 'Price', 'Marian', '1997-09-02', 'North Kristin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.377925', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (111, NULL, NULL, NULL, NULL, NULL, NULL, 'Oberbrunner', 'Caden', 'Berneice', '1933-01-23', 'Halvorsonfurt', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.381464', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (112, NULL, NULL, NULL, NULL, NULL, NULL, 'Hills', 'Mary', 'Eliza', '1961-10-30', 'Gretaberg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.385188', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (113, NULL, NULL, NULL, NULL, NULL, NULL, 'Toy', 'Kaleigh', 'Salvatore', '1949-01-14', 'Twilamouth', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.388856', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (114, NULL, NULL, NULL, NULL, NULL, NULL, 'Shields', 'Gus', 'Kristian', '1968-12-05', 'North Jakob', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.392435', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (115, NULL, NULL, NULL, NULL, NULL, NULL, 'Nolan', 'Crystal', 'Savannah', '1975-04-30', 'North Sherwoodport', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.395178', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (116, NULL, NULL, NULL, NULL, NULL, NULL, 'Wolff', 'Alexandria', 'Chyna', '1977-07-04', 'Raynorchester', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.397762', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (117, NULL, NULL, NULL, NULL, NULL, NULL, 'Boyer', 'Dixie', 'Leonor', '2002-07-29', 'Beattyberg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.400342', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (118, NULL, NULL, NULL, NULL, NULL, NULL, 'McDermott', 'Presley', 'Reba', '1925-05-04', 'New Matt', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.402979', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (119, NULL, NULL, NULL, NULL, NULL, NULL, 'Oberbrunner', 'Luis', 'Brielle', '1997-08-22', 'South Rowlandmouth', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.405623', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (120, NULL, NULL, NULL, NULL, NULL, NULL, 'Feeney', 'Kitty', 'Kari', '1946-08-15', 'North Leland', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.408399', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (121, NULL, NULL, NULL, NULL, NULL, NULL, 'Hegmann', 'Angelo', 'Roberta', '1961-05-01', 'Port Mandyfort', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.41107', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (122, NULL, NULL, NULL, NULL, NULL, NULL, 'Hansen', 'Frederick', 'Ardella', '2006-01-15', 'Gleichnerfort', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.413633', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (123, NULL, NULL, NULL, NULL, NULL, NULL, 'Cremin', 'Hattie', 'Verner', '1978-12-27', 'Lake Devenstad', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.417766', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (124, NULL, NULL, NULL, NULL, NULL, NULL, 'Walsh', 'Elvis', 'Katelyn', '1975-11-07', 'East Rocky', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.421894', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (125, NULL, NULL, NULL, NULL, NULL, NULL, 'Abshire', 'Irwin', 'Janice', '1957-09-23', 'Orastad', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.425969', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (126, NULL, NULL, NULL, NULL, NULL, NULL, 'Wehner', 'Kameron', 'Drake', '1990-03-04', 'Bernadinehaven', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.430179', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);
INSERT INTO public.tbl_person VALUES (127, NULL, NULL, NULL, NULL, NULL, NULL, 'Terry', 'Clare', 'Genevieve', '1966-09-30', 'North Theodora', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-04-04 13:22:04.433865', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false);

-- INSERT Benutzer (public.tbl_benutzer)
INSERT INTO public.tbl_benutzer VALUES ('mckenzie', 3, 't', 'mckenzie.vicenta', NOW(), 'codeception', NOW(), 'codeception', NULL, 'codeception', NOW(), NULL);
INSERT INTO public.tbl_benutzer VALUES ('wilderman', 4, 't', 'wilderman.rocio', NOW(), 'codeception', NOW(), 'codeception', NULL, 'codeception', NOW(), NULL);
INSERT INTO public.tbl_benutzer VALUES ('harvey', 5, 't', 'harvey.joshuah', NOW(), 'codeception', NOW(), 'codeception', NULL, 'codeception', NOW(), NULL);
INSERT INTO public.tbl_benutzer VALUES ('kessler', 6, 't', 'kessler.neil', NOW(), 'codeception', NOW(), 'codeception', NULL, 'codeception', NOW(), NULL);

-- INSERT Kontakt (public.tbl_kontakt)
INSERT INTO public.tbl_kontakt VALUES (1, 3, 'email', NULL, 'mckenzie.vicenta@calva.dev', 't', NOW(), NULL, NOW(), 'codeception', NULL, NULL);
INSERT INTO public.tbl_kontakt VALUES (2, 4, 'email', NULL, 'wilderman.rocio@calva.dev', 't', NOW(), NULL, NOW(), 'codeception', NULL, NULL);
INSERT INTO public.tbl_kontakt VALUES (3, 5, 'email', NULL, 'harvey.joshuah@calva.dev', 't', NOW(), NULL, NOW(), 'codeception', NULL, NULL);
INSERT INTO public.tbl_kontakt VALUES (4, 6, 'email', NULL, 'kessler.neil@calva.dev', 't', NOW(), NULL, NOW(), 'codeception', NULL, NULL);

-- INSERT Ort (public.tbl_ort)
INSERT INTO public.tbl_ort VALUES ('Nirvana', 'Blablablabla', 'A-1', 2000, 't', 't', 't', NULL, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- INSERT Studienjahr (public.tbl_studienjahr)
INSERT INTO public.tbl_studienjahr VALUES ('2123/01', NULL);

-- INSERT Studienordnung (lehre.tbl_studienordnung)
INSERT INTO lehre.tbl_studienordnung VALUES (1, 1, 01, 'WS2016', 'WS2016', 'A', 180.00, 'Bla bla bla', 'Bla bla bla bla', 'A', NULL, NOW(), 'codeception', NOW(), 'codeception', NULL, NULL, NULL);

-- INSERT Studienordnung_semester (lehre.tbl_studienordnung_semester)
INSERT INTO lehre.tbl_studienordnung_semester VALUES (1, 1, 'WS2016', 1);

-- INSERT Studienplan (lehre.tbl_studienplan)
INSERT INTO lehre.tbl_studienplan VALUES (1, 1, 'VZ', 'A', '01234', 6, 'English', 't', 15, 't', NOW(), 'codeception', NOW(), NULL, NULL, NULL, NULL, NULL);
INSERT INTO lehre.tbl_studienplan VALUES (2, 1, 'VZ', 'A', '01234', 6, 'English', 't', 15, 't', NOW(), 'codeception', NOW(), NULL, NULL, NULL, NULL, NULL);
INSERT INTO lehre.tbl_studienplan VALUES (3, 1, 'VZ', 'A', '01234', 6, 'English', 't', 15, 't', NOW(), 'codeception', NOW(), NULL, NULL, NULL, NULL, NULL);
INSERT INTO lehre.tbl_studienplan VALUES (4, 1, 'VZ', 'A', '01234', 6, 'English', 't', 15, 't', NOW(), 'codeception', NOW(), NULL, NULL, NULL, NULL, NULL);

-- INSERT Prestudent (public.tbl_prestudent)
INSERT INTO public.tbl_prestudent VALUES (1, 'TGM', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'f', NULL, 't', NULL, NULL, NULL, NULL, NULL, NULL, 'f', NULL, NULL, NULL, 0.0, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- INSERT Prestudent status (public.tbl_prestudentstatus)
INSERT INTO public.tbl_prestudentstatus VALUES (1, 'Interessent', 'WS2016', 1, NOW(), NOW(), 'codeception', NOW(), 'codeception', NULL, 'VZ', 1, NULL, 'admin', NULL, NULL, NULL, NULL);

-- INSERT Preinteressent (public.tbl_preinteressent)
INSERT INTO public.tbl_preinteressent VALUES (1, 1, 'WS2016', 'TGM', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'bewerbungonline', NULL);

-- INSERT INTO lehre.tbl_studienplan_semester (studienplan_semester_id, studienplan_id, studiensemester_kurzbz, semester)
INSERT INTO lehre.tbl_studienplan_semester VALUES (1, 1, 'WS2016', 1);

-- DELETE FROM system.tbl_rolleberechtigung
DELETE FROM system.tbl_rolleberechtigung WHERE berechtigung_kurzbz IN (
	'basis/archiv', 
	'basis/ausbildung', 
	'basis/berufstaetigkeit', 
	'basis/beschaeftigungsausmass', 
	'basis/besqual', 
	'basis/bisfunktion', 
	'basis/bisio', 
	'basis/bisorgform', 
	'basis/bisverwendung', 
	'basis/bundesland', 
	'basis/entwicklungsteam', 
	'basis/gemeinde', 
	'basis/hauptberuf', 
	'basis/lgartcode', 
	'basis/mobilitaetsprogramm', 
	'basis/nation', 
	'basis/orgform', 
	'basis/verwendung', 
	'basis/zgv', 
	'basis/zgvdoktor', 
	'basis/zgvgruppe', 
	'basis/zgvmaster', 
	'basis/zweck', 
	'basis/abgabe', 
	'basis/anwesenheit', 
	'basis/beispiel', 
	'basis/content', 
	'basis/contentchild', 
	'basis/contentgruppe', 
	'basis/contentlog', 
	'basis/contentsprache', 
	'basis/coodle', 
	'basis/dms', 
	'basis/erreichbarkeit', 
	'basis/feedback', 
	'basis/freebusy', 
	'basis/freebusytyp', 
	'basis/infoscreen', 
	'basis/legesamtnote', 
	'basis/lvgesamtnote', 
	'basis/lvinfo', 
	'basis/news', 
	'basis/notenschluessel', 
	'basis/notenschluesseluebung', 
	'basis/paabgabe', 
	'basis/paabgabetyp', 
	'basis/pruefung', 
	'basis/pruefungsanmeldung', 
	'basis/pruefungsfenster', 
	'basis/pruefungsstatus', 
	'basis/pruefungstermin', 
	'basis/reservierung', 
	'basis/resturlaub', 
	'basis/studentbeispiel', 
	'basis/studentuebung', 
	'basis/template', 
	'basis/uebung', 
	'basis/veranstaltung', 
	'basis/veranstaltungskategorie', 
	'basis/zeitaufzeichnung', 
	'basis/zeitsperre', 
	'basis/zeitsperretyp', 
	'basis/zeitwunsch', 
	'basis/aktivitaet', 
	'basis/aufwandstyp', 
	'basis/projekt', 
	'basis/projekt_ressource', 
	'basis/projektphase', 
	'basis/projekttask', 
	'basis/ressource', 
	'basis/scrumsprint', 
	'basis/scrumteam', 
	'basis/abschlussbeurteilung', 
	'basis/abschlusspruefung', 
	'basis/akadgrad', 
	'basis/anrechnung', 
	'basis/betreuerart', 
	'basis/ferien', 
	'basis/lehreinheit', 
	'basis/lehreinheitgruppe', 
	'basis/lehreinheitmitarbeiter', 
	'basis/lehrfach', 
	'basis/lehrform', 
	'basis/lehrfunktion', 
	'basis/lehrmittel', 
	'basis/lehrtyp', 
	'basis/lehrveranstaltung', 
	'basis/lvangebot', 
	'basis/lvregel', 
	'basis/lvregeltyp', 
	'basis/moodle', 
	'basis/note', 
	'basis/notenschluesselaufteilung', 
	'basis/notenschluesselzuordnung', 
	'basis/projektarbeit', 
	'basis/projektbetreuer', 
	'basis/projekttyp', 
	'basis/pruefungstyp', 
	'basis/studienordnung', 
	'basis/studienordnungstatus', 
	'basis/studienplan', 
	'basis/studienplatz', 
	'basis/stunde', 
	'basis/stundenplan', 
	'basis/stundenplandev', 
	'basis/vertrag', 
	'basis/vertragsstatus', 
	'basis/vertragstyp', 
	'basis/zeitfenster', 
	'basis/zeugnis', 
	'basis/zeugnisnote', 
	'basis/adresse', 
	'basis/akte', 
	'basis/ampel', 
	'basis/aufmerksamdurch', 
	'basis/aufnahmeschluessel', 
	'basis/aufnahmetermin', 
	'basis/aufnahmetermintyp', 
	'basis/bankverbindung', 
	'basis/benutzer', 
	'basis/benutzerfunktion', 
	'basis/benutzergruppe', 
	'basis/bewerbungstermine', 
	'basis/buchungstyp', 
	'basis/dokument', 
	'basis/dokumentprestudent', 
	'basis/dokumentstudiengang', 
	'basis/erhalter', 
	'basis/fachbereich', 
	'basis/filter', 
	'basis/firma', 
	'basis/firmatag', 
	'basis/firmentyp', 
	'basis/fotostatus', 
	'basis/funktion', 
	'basis/geschaeftsjahr', 
	'basis/gruppe', 
	'basis/kontakt', 
	'basis/kontaktmedium', 
	'basis/kontakttyp', 
	'basis/konto', 
	'basis/lehrverband', 
	'basis/log', 
	'basis/mitarbeiter', 
	'basis/msg_message',
	'basis/message',
	'basis/msg_thread', 
	'basis/notiz', 
	'basis/notizzuordnung', 
	'basis/organisationseinheit', 
	'basis/organisationseinheittyp', 
	'basis/ort', 
	'basis/ortraumtyp', 
	'basis/person', 
	'basis/personfunktionstandort', 
	'basis/preincoming', 
	'basis/preinteressent', 
	'basis/preinteressentstudiengang', 
	'basis/preoutgoing', 
	'basis/prestudent', 
	'basis/prestudentstatus', 
	'basis/raumtyp', 
	'basis/reihungstest', 
	'basis/semesterwochen', 
	'basis/service', 
	'basis/sprache', 
	'basis/standort', 
	'basis/statistik', 
	'basis/status', 
	'basis/student', 
	'basis/studentlehrverband', 
	'basis/studiengang', 
	'basis/studiengangstyp', 
	'basis/studienjahr', 
	'basis/studiensemester', 
	'basis/tag', 
	'basis/variable', 
	'basis/vorlage', 
	'basis/vorlagestudiengang', 
	'basis/appdaten', 
	'basis/benutzerrolle', 
	'basis/berechtigung', 
	'basis/cronjob', 
	'basis/rolle', 
	'basis/rolleberechtigung', 
	'basis/server', 
	'basis/webservicelog', 
	'basis/webservicerecht', 
	'basis/webservicetyp', 
	'basis/ablauf', 
	'basis/antwort', 
	'basis/frage', 
	'basis/gebiet', 
	'basis/kategorie', 
	'basis/kriterien', 
	'basis/pruefling', 
	'basis/vorschlag', 
	'basis/aufteilung', 
	'basis/bestelldetail', 
	'basis/bestelldetailtag', 
	'basis/bestellstatus', 
	'basis/bestellung', 
	'basis/bestellungtag', 
	'basis/betriebsmittel', 
	'basis/betriebsmittelperson', 
	'basis/betriebsmittelstatus', 
	'basis/betriebsmitteltyp', 
	'basis/buchung', 
	'basis/budget', 
	'basis/kostenstelle', 
	'basis/rechnung', 
	'basis/rechnungsbetrag', 
	'basis/rechnungstyp', 
	'basis/zahlungstyp',
	'basis/studienplan_semester',
	'basis/dms_version',
	'student/stammdaten',
	'mitarbeiter/stammdaten',
	'basis/vw_studiensemester',
	'lehre/reservierung',
	'lehre/reihungstest',
	'wawi/inventar:begrenzt',
	'fs/dms',
	'basis/phrase',
	'system/vorlagestudiengang'
);

-- DELETE FROM system.tbl_berechtigung
DELETE FROM system.tbl_berechtigung WHERE berechtigung_kurzbz IN (
	'basis/archiv', 
	'basis/ausbildung', 
	'basis/berufstaetigkeit', 
	'basis/beschaeftigungsausmass', 
	'basis/besqual', 
	'basis/bisfunktion', 
	'basis/bisio', 
	'basis/bisorgform', 
	'basis/bisverwendung', 
	'basis/bundesland', 
	'basis/entwicklungsteam', 
	'basis/gemeinde', 
	'basis/hauptberuf', 
	'basis/lgartcode', 
	'basis/mobilitaetsprogramm', 
	'basis/nation', 
	'basis/orgform', 
	'basis/verwendung', 
	'basis/zgv', 
	'basis/zgvdoktor', 
	'basis/zgvgruppe', 
	'basis/zgvmaster', 
	'basis/zweck', 
	'basis/abgabe', 
	'basis/anwesenheit', 
	'basis/beispiel', 
	'basis/content', 
	'basis/contentchild', 
	'basis/contentgruppe', 
	'basis/contentlog', 
	'basis/contentsprache', 
	'basis/coodle', 
	'basis/dms', 
	'basis/erreichbarkeit', 
	'basis/feedback', 
	'basis/freebusy', 
	'basis/freebusytyp', 
	'basis/infoscreen', 
	'basis/legesamtnote', 
	'basis/lvgesamtnote', 
	'basis/lvinfo', 
	'basis/news', 
	'basis/notenschluessel', 
	'basis/notenschluesseluebung', 
	'basis/paabgabe', 
	'basis/paabgabetyp', 
	'basis/pruefung', 
	'basis/pruefungsanmeldung', 
	'basis/pruefungsfenster', 
	'basis/pruefungsstatus', 
	'basis/pruefungstermin', 
	'basis/reservierung', 
	'basis/resturlaub', 
	'basis/studentbeispiel', 
	'basis/studentuebung', 
	'basis/template', 
	'basis/uebung', 
	'basis/veranstaltung', 
	'basis/veranstaltungskategorie', 
	'basis/zeitaufzeichnung', 
	'basis/zeitsperre', 
	'basis/zeitsperretyp', 
	'basis/zeitwunsch', 
	'basis/aktivitaet', 
	'basis/aufwandstyp', 
	'basis/projekt', 
	'basis/projekt_ressource', 
	'basis/projektphase', 
	'basis/projekttask', 
	'basis/ressource', 
	'basis/scrumsprint', 
	'basis/scrumteam', 
	'basis/abschlussbeurteilung', 
	'basis/abschlusspruefung', 
	'basis/akadgrad', 
	'basis/anrechnung', 
	'basis/betreuerart', 
	'basis/ferien', 
	'basis/lehreinheit', 
	'basis/lehreinheitgruppe', 
	'basis/lehreinheitmitarbeiter', 
	'basis/lehrfach', 
	'basis/lehrform', 
	'basis/lehrfunktion', 
	'basis/lehrmittel', 
	'basis/lehrtyp', 
	'basis/lehrveranstaltung', 
	'basis/lvangebot', 
	'basis/lvregel', 
	'basis/lvregeltyp', 
	'basis/moodle', 
	'basis/note', 
	'basis/notenschluesselaufteilung', 
	'basis/notenschluesselzuordnung', 
	'basis/projektarbeit', 
	'basis/projektbetreuer', 
	'basis/projekttyp', 
	'basis/pruefungstyp', 
	'basis/studienordnung', 
	'basis/studienordnungstatus', 
	'basis/studienplan', 
	'basis/studienplatz', 
	'basis/stunde', 
	'basis/stundenplan', 
	'basis/stundenplandev', 
	'basis/vertrag', 
	'basis/vertragsstatus', 
	'basis/vertragstyp', 
	'basis/zeitfenster', 
	'basis/zeugnis', 
	'basis/zeugnisnote', 
	'basis/adresse', 
	'basis/akte', 
	'basis/ampel', 
	'basis/aufmerksamdurch', 
	'basis/aufnahmeschluessel', 
	'basis/aufnahmetermin', 
	'basis/aufnahmetermintyp', 
	'basis/bankverbindung', 
	'basis/benutzer', 
	'basis/benutzerfunktion', 
	'basis/benutzergruppe', 
	'basis/bewerbungstermine', 
	'basis/buchungstyp', 
	'basis/dokument', 
	'basis/dokumentprestudent', 
	'basis/dokumentstudiengang', 
	'basis/erhalter', 
	'basis/fachbereich', 
	'basis/filter', 
	'basis/firma', 
	'basis/firmatag', 
	'basis/firmentyp', 
	'basis/fotostatus', 
	'basis/funktion', 
	'basis/geschaeftsjahr', 
	'basis/gruppe', 
	'basis/kontakt', 
	'basis/kontaktmedium', 
	'basis/kontakttyp', 
	'basis/konto', 
	'basis/lehrverband', 
	'basis/log', 
	'basis/mitarbeiter', 
	'basis/msg_message', 
	'basis/message',
	'basis/msg_thread', 
	'basis/notiz', 
	'basis/notizzuordnung', 
	'basis/organisationseinheit', 
	'basis/organisationseinheittyp', 
	'basis/ort', 
	'basis/ortraumtyp', 
	'basis/person', 
	'basis/personfunktionstandort', 
	'basis/preincoming', 
	'basis/preinteressent', 
	'basis/preinteressentstudiengang', 
	'basis/preoutgoing', 
	'basis/prestudent', 
	'basis/prestudentstatus', 
	'basis/raumtyp', 
	'basis/reihungstest', 
	'basis/semesterwochen', 
	'basis/service', 
	'basis/sprache', 
	'basis/standort', 
	'basis/statistik', 
	'basis/status', 
	'basis/student', 
	'basis/studentlehrverband', 
	'basis/studiengang', 
	'basis/studiengangstyp', 
	'basis/studienjahr', 
	'basis/studiensemester', 
	'basis/tag', 
	'basis/variable', 
	'basis/vorlage', 
	'basis/vorlagestudiengang', 
	'basis/appdaten', 
	'basis/benutzerrolle', 
	'basis/berechtigung', 
	'basis/cronjob', 
	'basis/rolle', 
	'basis/rolleberechtigung', 
	'basis/server', 
	'basis/webservicelog', 
	'basis/webservicerecht', 
	'basis/webservicetyp', 
	'basis/ablauf', 
	'basis/antwort', 
	'basis/frage', 
	'basis/gebiet', 
	'basis/kategorie', 
	'basis/kriterien', 
	'basis/pruefling', 
	'basis/vorschlag', 
	'basis/aufteilung', 
	'basis/bestelldetail', 
	'basis/bestelldetailtag', 
	'basis/bestellstatus', 
	'basis/bestellung', 
	'basis/bestellungtag', 
	'basis/betriebsmittel', 
	'basis/betriebsmittelperson', 
	'basis/betriebsmittelstatus', 
	'basis/betriebsmitteltyp', 
	'basis/buchung', 
	'basis/budget', 
	'basis/kostenstelle', 
	'basis/rechnung', 
	'basis/rechnungsbetrag', 
	'basis/rechnungstyp', 
	'basis/zahlungstyp',
	'basis/studienplan_semester',
	'basis/dms_version',
	'student/stammdaten',
	'mitarbeiter/stammdaten',
	'basis/vw_studiensemester',
	'lehre/reservierung',
	'lehre/reihungstest',
	'wawi/inventar:begrenzt',
	'fs/dms',
	'basis/phrase',
	'system/vorlagestudiengang'
);

-- INSERT Permissions
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/archiv', 'Tbl_archiv');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/ausbildung', 'Tbl_ausbildung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/berufstaetigkeit', 'Tbl_berufstaetigkeit');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/beschaeftigungsausmass', 'Tbl_beschaeftigungsausmass');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/besqual', 'Tbl_besqual');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bisfunktion', 'Tbl_bisfunktion');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bisio', 'Tbl_bisio');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bisorgform', 'Tbl_bisorgform');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bisverwendung', 'Tbl_bisverwendung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bundesland', 'Tbl_bundesland');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/entwicklungsteam', 'Tbl_entwicklungsteam');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/gemeinde', 'Tbl_gemeinde');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/hauptberuf', 'Tbl_hauptberuf');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lgartcode', 'Tbl_lgartcode');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/mobilitaetsprogramm', 'Tbl_mobilitaetsprogramm');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/nation', 'Tbl_nation');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/orgform', 'Tbl_orgform');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/verwendung', 'Tbl_verwendung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zgv', 'Tbl_zgv');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zgvdoktor', 'Tbl_zgvdoktor');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zgvgruppe', 'Tbl_zgvgruppe');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zgvmaster', 'Tbl_zgvmaster');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zweck', 'Tbl_zweck');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/abgabe', 'Tbl_abgabe');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/anwesenheit', 'Tbl_anwesenheit');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/beispiel', 'Tbl_beispiel');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/content', 'Tbl_content');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/contentchild', 'Tbl_contentchild');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/contentgruppe', 'Tbl_contentgruppe');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/contentlog', 'Tbl_contentlog');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/contentsprache', 'Tbl_contentsprache');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/coodle', 'Tbl_coodle');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/dms', 'Tbl_dms');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/erreichbarkeit', 'Tbl_erreichbarkeit');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/feedback', 'Tbl_feedback');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/freebusy', 'Tbl_freebusy');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/freebusytyp', 'Tbl_freebusytyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/infoscreen', 'Tbl_infoscreen');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/legesamtnote', 'Tbl_legesamtnote');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lvgesamtnote', 'Tbl_lvgesamtnote');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lvinfo', 'Tbl_lvinfo');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/news', 'Tbl_news');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/notenschluessel', 'Tbl_notenschluessel');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/notenschluesseluebung', 'Tbl_notenschluesseluebung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/paabgabe', 'Tbl_paabgabe');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/paabgabetyp', 'Tbl_paabgabetyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/pruefung', 'Tbl_pruefung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/pruefungsanmeldung', 'Tbl_pruefungsanmeldung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/pruefungsfenster', 'Tbl_pruefungsfenster');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/pruefungsstatus', 'Tbl_pruefungsstatus');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/pruefungstermin', 'Tbl_pruefungstermin');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/reservierung', 'Tbl_reservierung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/resturlaub', 'Tbl_resturlaub');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/studentbeispiel', 'Tbl_studentbeispiel');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/studentuebung', 'Tbl_studentuebung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/template', 'Tbl_template');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/uebung', 'Tbl_uebung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/veranstaltung', 'Tbl_veranstaltung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/veranstaltungskategorie', 'Tbl_veranstaltungskategorie');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zeitaufzeichnung', 'Tbl_zeitaufzeichnung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zeitsperre', 'Tbl_zeitsperre');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zeitsperretyp', 'Tbl_zeitsperretyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zeitwunsch', 'Tbl_zeitwunsch');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/aktivitaet', 'Tbl_aktivitaet');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/aufwandstyp', 'Tbl_aufwandstyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/projekt', 'Tbl_projekt');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/projekt_ressource', 'Tbl_projekt_ressource');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/projektphase', 'Tbl_projektphase');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/projekttask', 'Tbl_projekttask');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/ressource', 'Tbl_ressource');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/scrumsprint', 'Tbl_scrumsprint');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/scrumteam', 'Tbl_scrumteam');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/abschlussbeurteilung', 'Tbl_abschlussbeurteilung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/abschlusspruefung', 'Tbl_abschlusspruefung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/akadgrad', 'Tbl_akadgrad');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/anrechnung', 'Tbl_anrechnung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/betreuerart', 'Tbl_betreuerart');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/ferien', 'Tbl_ferien');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lehreinheit', 'Tbl_lehreinheit');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lehreinheitgruppe', 'Tbl_lehreinheitgruppe');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lehreinheitmitarbeiter', 'Tbl_lehreinheitmitarbeiter');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lehrfach', 'Tbl_lehrfach');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lehrform', 'Tbl_lehrform');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lehrfunktion', 'Tbl_lehrfunktion');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lehrmittel', 'Tbl_lehrmittel');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lehrtyp', 'Tbl_lehrtyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lehrveranstaltung', 'Tbl_lehrveranstaltung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lvangebot', 'Tbl_lvangebot');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lvregel', 'Tbl_lvregel');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lvregeltyp', 'Tbl_lvregeltyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/moodle', 'Tbl_moodle');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/note', 'Tbl_note');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/notenschluesselaufteilung', 'Tbl_notenschluesselaufteilung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/notenschluesselzuordnung', 'Tbl_notenschluesselzuordnung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/projektarbeit', 'Tbl_projektarbeit');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/projektbetreuer', 'Tbl_projektbetreuer');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/projekttyp', 'Tbl_projekttyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/pruefungstyp', 'Tbl_pruefungstyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/studienordnung', 'Tbl_studienordnung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/studienordnungstatus', 'Tbl_studienordnungstatus');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/studienplan', 'Tbl_studienplan');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/studienplatz', 'Tbl_studienplatz');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/stunde', 'Tbl_stunde');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/stundenplan', 'Tbl_stundenplan');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/stundenplandev', 'Tbl_stundenplandev');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/vertrag', 'Tbl_vertrag');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/vertragsstatus', 'Tbl_vertragsstatus');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/vertragstyp', 'Tbl_vertragstyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zeitfenster', 'Tbl_zeitfenster');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zeugnis', 'Tbl_zeugnis');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zeugnisnote', 'Tbl_zeugnisnote');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/adresse', 'Tbl_adresse');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/akte', 'Tbl_akte');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/ampel', 'Tbl_ampel');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/aufmerksamdurch', 'Tbl_aufmerksamdurch');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/aufnahmeschluessel', 'Tbl_aufnahmeschluessel');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/aufnahmetermin', 'Tbl_aufnahmetermin');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/aufnahmetermintyp', 'Tbl_aufnahmetermintyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bankverbindung', 'Tbl_bankverbindung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/benutzer', 'Tbl_benutzer');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/benutzerfunktion', 'Tbl_benutzerfunktion');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/benutzergruppe', 'Tbl_benutzergruppe');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bewerbungstermine', 'Tbl_bewerbungstermine');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/buchungstyp', 'Tbl_buchungstyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/dokument', 'Tbl_dokument');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/dokumentprestudent', 'Tbl_dokumentprestudent');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/dokumentstudiengang', 'Tbl_dokumentstudiengang');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/erhalter', 'Tbl_erhalter');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/fachbereich', 'Tbl_fachbereich');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/filter', 'Tbl_filter');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/firma', 'Tbl_firma');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/firmatag', 'Tbl_firmatag');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/firmentyp', 'Tbl_firmentyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/fotostatus', 'Tbl_fotostatus');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/funktion', 'Tbl_funktion');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/geschaeftsjahr', 'Tbl_geschaeftsjahr');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/gruppe', 'Tbl_gruppe');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/kontakt', 'Tbl_kontakt');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/kontaktmedium', 'Tbl_kontaktmedium');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/kontakttyp', 'Tbl_kontakttyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/konto', 'Tbl_konto');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lehrverband', 'Tbl_lehrverband');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/log', 'Tbl_log');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/mitarbeiter', 'Tbl_mitarbeiter');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/msg_message', 'Tbl_msg_message');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/msg_thread', 'Tbl_msg_thread');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/notiz', 'Tbl_notiz');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/notizzuordnung', 'Tbl_notizzuordnung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/organisationseinheit', 'Tbl_organisationseinheit');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/organisationseinheittyp', 'Tbl_organisationseinheittyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/ort', 'Tbl_ort');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/ortraumtyp', 'Tbl_ortraumtyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/person', 'Tbl_person');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/personfunktionstandort', 'Tbl_personfunktionstandort');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/preincoming', 'Tbl_preincoming');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/preinteressent', 'Tbl_preinteressent');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/preinteressentstudiengang', 'Tbl_preinteressentstudiengang');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/preoutgoing', 'Tbl_preoutgoing');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/prestudent', 'Tbl_prestudent');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/prestudentstatus', 'Tbl_prestudentstatus');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/raumtyp', 'Tbl_raumtyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/reihungstest', 'Tbl_reihungstest');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/semesterwochen', 'Tbl_semesterwochen');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/service', 'Tbl_service');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/sprache', 'Tbl_sprache');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/standort', 'Tbl_standort');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/statistik', 'Tbl_statistik');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/status', 'Tbl_status');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/student', 'Tbl_student');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/studentlehrverband', 'Tbl_studentlehrverband');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/studiengang', 'Tbl_studiengang');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/studiengangstyp', 'Tbl_studiengangstyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/studienjahr', 'Tbl_studienjahr');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/studiensemester', 'Tbl_studiensemester');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/tag', 'Tbl_tag');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/variable', 'Tbl_variable');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/vorlage', 'Tbl_vorlage');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/vorlagestudiengang', 'Tbl_vorlagestudiengang');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/appdaten', 'Tbl_appdaten');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/benutzerrolle', 'Tbl_benutzerrolle');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/berechtigung', 'Tbl_berechtigung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/cronjob', 'Tbl_cronjob');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/rolle', 'Tbl_rolle');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/rolleberechtigung', 'Tbl_rolleberechtigung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/server', 'Tbl_server');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/webservicelog', 'Tbl_webservicelog');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/webservicerecht', 'Tbl_webservicerecht');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/webservicetyp', 'Tbl_webservicetyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/ablauf', 'Tbl_ablauf');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/antwort', 'Tbl_antwort');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/frage', 'Tbl_frage');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/gebiet', 'Tbl_gebiet');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/kategorie', 'Tbl_kategorie');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/kriterien', 'Tbl_kriterien');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/pruefling', 'Tbl_pruefling');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/vorschlag', 'Tbl_vorschlag');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/aufteilung', 'Tbl_aufteilung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bestelldetail', 'Tbl_bestelldetail');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bestelldetailtag', 'Tbl_bestelldetailtag');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bestellstatus', 'Tbl_bestellstatus');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bestellung', 'Tbl_bestellung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bestellungtag', 'Tbl_bestellungtag');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/betriebsmittel', 'Tbl_betriebsmittel');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/betriebsmittelperson', 'Tbl_betriebsmittelperson');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/betriebsmittelstatus', 'Tbl_betriebsmittelstatus');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/betriebsmitteltyp', 'Tbl_betriebsmitteltyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/buchung', 'Tbl_buchung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/budget', 'Tbl_budget');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/kostenstelle', 'Tbl_kostenstelle');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/rechnung', 'Tbl_rechnung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/rechnungsbetrag', 'Tbl_rechnungsbetrag');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/rechnungstyp', 'Tbl_rechnungstyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zahlungstyp', 'Tbl_zahlungstyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/studienplan_semester', 'Tbl_studienplan_semester');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/dms_version', 'Tbl_dms_version');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('student/stammdaten', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('mitarbeiter/stammdaten', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/vw_studiensemester', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('lehre/reservierung', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('lehre/reihungstest', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('wawi/inventar:begrenzt', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('fs/dms', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/message', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/phrase', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('system/vorlagestudiengang', '');

-- INSERT link between user admin and permissions
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/archiv', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/ausbildung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/berufstaetigkeit', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/beschaeftigungsausmass', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/besqual', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bisfunktion', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bisio', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bisorgform', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bisverwendung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bundesland', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/entwicklungsteam', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/gemeinde', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/hauptberuf', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lgartcode', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/mobilitaetsprogramm', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/nation', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/orgform', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/verwendung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zgv', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zgvdoktor', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zgvgruppe', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zgvmaster', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zweck', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/abgabe', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/anwesenheit', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/beispiel', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/content', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/contentchild', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/contentgruppe', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/contentlog', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/contentsprache', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/coodle', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/dms', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/erreichbarkeit', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/feedback', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/freebusy', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/freebusytyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/infoscreen', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/legesamtnote', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lvgesamtnote', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lvinfo', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/news', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/notenschluessel', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/notenschluesseluebung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/paabgabe', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/paabgabetyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/pruefung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/pruefungsanmeldung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/pruefungsfenster', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/pruefungsstatus', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/pruefungstermin', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/reservierung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/resturlaub', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/studentbeispiel', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/studentuebung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/template', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/uebung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/veranstaltung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/veranstaltungskategorie', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zeitaufzeichnung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zeitsperre', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zeitsperretyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zeitwunsch', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/aktivitaet', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/aufwandstyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/projekt', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/projekt_ressource', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/projektphase', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/projekttask', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/ressource', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/scrumsprint', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/scrumteam', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/abschlussbeurteilung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/abschlusspruefung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/akadgrad', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/anrechnung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/betreuerart', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/ferien', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lehreinheit', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lehreinheitgruppe', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lehreinheitmitarbeiter', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lehrfach', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lehrform', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lehrfunktion', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lehrmittel', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lehrtyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lehrveranstaltung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lvangebot', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lvregel', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lvregeltyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/moodle', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/note', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/notenschluesselaufteilung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/notenschluesselzuordnung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/projektarbeit', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/projektbetreuer', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/projekttyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/pruefungstyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/studienordnung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/studienordnungstatus', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/studienplan', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/studienplatz', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/stunde', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/stundenplan', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/stundenplandev', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/vertrag', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/vertragsstatus', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/vertragstyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zeitfenster', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zeugnis', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zeugnisnote', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/adresse', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/akte', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/ampel', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/aufmerksamdurch', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/aufnahmeschluessel', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/aufnahmetermin', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/aufnahmetermintyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bankverbindung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/benutzer', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/benutzerfunktion', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/benutzergruppe', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bewerbungstermine', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/buchungstyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/dokument', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/dokumentprestudent', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/dokumentstudiengang', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/erhalter', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/fachbereich', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/filter', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/firma', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/firmatag', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/firmentyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/fotostatus', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/funktion', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/geschaeftsjahr', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/gruppe', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/kontakt', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/kontaktmedium', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/kontakttyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/konto', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lehrverband', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/log', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/mitarbeiter', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/msg_message', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/msg_thread', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/notiz', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/notizzuordnung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/organisationseinheit', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/organisationseinheittyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/ort', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/ortraumtyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/person', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/personfunktionstandort', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/preincoming', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/preinteressent', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/preinteressentstudiengang', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/preoutgoing', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/prestudent', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/prestudentstatus', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/raumtyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/reihungstest', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/semesterwochen', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/service', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/sprache', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/standort', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/statistik', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/status', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/student', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/studentlehrverband', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/studiengang', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/studiengangstyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/studienjahr', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/studiensemester', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/tag', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/variable', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/vorlage', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/vorlagestudiengang', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/appdaten', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/benutzerrolle', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/berechtigung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/cronjob', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/rolle', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/rolleberechtigung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/server', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/webservicelog', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/webservicerecht', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/webservicetyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/ablauf', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/antwort', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/frage', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/gebiet', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/kategorie', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/kriterien', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/pruefling', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/vorschlag', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/aufteilung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bestelldetail', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bestelldetailtag', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bestellstatus', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bestellung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bestellungtag', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/betriebsmittel', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/betriebsmittelperson', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/betriebsmittelstatus', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/betriebsmitteltyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/buchung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/budget', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/kostenstelle', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/rechnung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/rechnungsbetrag', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/rechnungstyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zahlungstyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/studienplan_semester', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/dms_version', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('student/stammdaten', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('mitarbeiter/stammdaten', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/vw_studiensemester', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('lehre/reservierung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('lehre/reihungstest', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('wawi/inventar:begrenzt', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('fs/dms', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/message', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/phrase', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('system/vorlagestudiengang', 'admin', 'suid');

-- EMPTY public.tbl_statistik
DELETE FROM public.tbl_statistik;

-- INSERT Statistiks (public.tbl_statistik)
INSERT INTO public.tbl_statistik VALUES ('StudentenHistorie', 'StudentenHistorie', NULL, 'studenten_historie.php', 'Studierende', NULL, NULL, NULL, '2011-04-04 09:25:35', 'oesi', '2011-04-04 09:25:35', 'oesi', NULL, false, NULL);
INSERT INTO public.tbl_statistik VALUES ('Abgaengerstatistik', 'Abgängerstatistik', 9, '../../content/statistik/abgaengerstatistik.php', 'Studierende', NULL, NULL, NULL, '2011-04-01 10:57:05', 'oesi', '2011-04-01 11:13:55', 'oesi', NULL, false, NULL);
INSERT INTO public.tbl_statistik VALUES ('Absolventenstatistik', 'Absolventenstatistik', 10, '../../content/statistik/absolventenstatistik.php', 'Studierende', NULL, NULL, NULL, '2011-04-01 10:57:46', 'oesi', '2011-04-01 11:14:01', 'oesi', NULL, false, NULL);
INSERT INTO public.tbl_statistik VALUES ('Bewerberstatistik', 'Bewerberstatistik', 2, '../../content/statistik/bewerberstatistik.php?stsem=$Studiensemester', 'Studierende', NULL, NULL, NULL, '2011-04-01 10:43:44', 'oesi', '2011-04-01 11:14:19', 'oesi', NULL, false, NULL);
INSERT INTO public.tbl_statistik VALUES ('Lektorenstatistik', 'Lektorenstatistik', 13, '../../content/statistik/lektorenstatistik.php', 'Mitarbeiter', NULL, NULL, NULL, '2011-04-01 11:08:41', 'oesi', '2011-04-01 11:14:27', 'oesi', NULL, false, NULL);
INSERT INTO public.tbl_statistik VALUES ('Raumauslastung', 'Raumauslastung', 3, '../lehre/raumauslastung.php', 'LV-Plan', NULL, NULL, NULL, '2011-04-01 10:51:01', 'oesi', '2011-04-01 11:14:50', 'oesi', NULL, false, NULL);
INSERT INTO public.tbl_statistik VALUES ('Stromanalyse', 'Stromanalyse', 15, '../../content/statistik/bama_stromanalyse.php', 'Studierende', NULL, NULL, NULL, '2011-04-01 11:09:45', 'oesi', '2011-04-01 11:14:59', 'oesi', NULL, false, NULL);
INSERT INTO public.tbl_statistik VALUES ('Mitarbeiterstatistik', 'Mitarbeiterstatistik', 14, '../../content/statistik/mitarbeiterstatistik.php', 'Mitarbeiter', NULL, NULL, NULL, '2011-04-01 11:09:13', 'oesi', '2012-01-12 15:48:47', 'kollmitz', NULL, false, NULL);
INSERT INTO public.tbl_statistik VALUES ('Verplanungsübersicht', 'Verplanungsübersicht', 4, '../lehre/check/verplanungsuebersicht.php', 'LV-Plan', NULL, NULL, NULL, '2011-04-01 10:51:53', 'oesi', '2011-04-01 11:15:20', 'oesi', NULL, false, NULL);
INSERT INTO public.tbl_statistik VALUES ('Zeitwünsche', 'Zeitwünsche', 5, '../lehre/zeitwuensche.php', 'LV-Plan', NULL, NULL, NULL, '2011-04-01 10:52:37', 'oesi', '2011-04-01 11:15:27', 'oesi', NULL, false, NULL);
INSERT INTO public.tbl_statistik VALUES ('AnzahlStudierende', 'Aktuell Studierende im Haus', 16, '../../cis/private/lvplan/stpl_week_anzahl_studenten.php', 'Studierende', NULL, NULL, NULL, '2011-04-01 11:11:52', 'oesi', '2012-02-20 19:09:16', 'kindlm', NULL, false, NULL);
INSERT INTO public.tbl_statistik VALUES ('ALVS-Statistik', 'ALVS-Statistik', 7, '../../content/statistik/alvsstatistik.php', 'Lehre', NULL, NULL, NULL, '2011-04-01 10:54:03', 'oesi', '2011-04-01 11:23:12', 'oesi', NULL, false, NULL);