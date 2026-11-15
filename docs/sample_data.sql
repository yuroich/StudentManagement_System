-- Student Management System | sample data for testing, screenshots and the demo video
-- All people below are made up. Names, numbers and addresses are placeholders, not real students.
--
-- HOW TO USE: phpMyAdmin > student_management > Import > choose this file > Go.
-- WARNING: the next two lines DELETE every student already in the table (user accounts are NOT touched).
-- Remove them if you want to keep the students you already added.

USE student_management;

DELETE FROM students;
ALTER TABLE students AUTO_INCREMENT = 1;

-- Link the sample records to the admin account (falls back to NULL if there is no admin)
SET @admin_id = (SELECT id FROM users WHERE username = 'admin' LIMIT 1);

INSERT INTO students (full_name, contact, gender, address, birth_date, course, year_level, block, status, created_by) VALUES
  ('Maria Angela Santos', '09171230001', 'Female', 'Purok 3, Centro Occidental, Polangui, Albay', '2005-03-14', 'BSIT', 1, 'A', 'Regular', @admin_id),
  ('Mark Anthony Reyes', '09171230002', 'Male', 'Purok 1, Ponso, Polangui, Albay', '2004-11-02', 'BSIT', 2, 'A', 'Regular', @admin_id),
  ('Maricel Dela Cruz', '09171230003', 'Female', 'Purok 5, Lanigay, Polangui, Albay', '2005-07-21', 'BSCS', 1, 'B', 'Regular', @admin_id),
  ('John Paul Garcia', '09171230004', 'Male', 'Purok 2, Magurang, Polangui, Albay', '2003-01-30', 'BSIT', 3, 'B', 'Regular', @admin_id),
  ('Kristine Joy Ramos', 'N/A', 'Female', 'Purok 4, Santicon, Polangui, Albay', '2004-05-09', 'BSIS', 2, 'A', 'Regular', @admin_id),
  ('Joshua Miguel Bautista', '09171230006', 'Male', 'Purok 6, Centro Oriental, Polangui, Albay', '2002-09-17', 'BSCS', 4, 'C', 'Regular', @admin_id),
  ('Angelica Mae Villanueva', '09171230007', 'Female', 'Purok 7, Sugcad, Polangui, Albay', '2005-12-25', 'BSIS', 1, 'B', 'Regular', @admin_id),
  ('Carlo Dominic Mendoza', '09171230008', 'Male', 'Purok 2, Alnay, Polangui, Albay', '2003-06-11', 'BSIT', 3, 'A', 'Irregular', @admin_id),
  ('Jasmine Faith Aquino', '09171230009', 'Female', 'Purok 1, Basud, Polangui, Albay', '2004-02-28', 'BSCS', 2, 'C', 'Regular', @admin_id),
  ('Rafael Jose Navarro', '09171230010', 'Male', 'Purok 3, Kinale, Polangui, Albay', '2002-04-05', 'BSIS', 4, 'A', 'Graduate', @admin_id),
  ('Nicole Anne Castillo', '09171230011', 'Female', 'Purok 6, Matacon, Polangui, Albay', '2005-08-19', 'BSIT', 1, 'C', 'Regular', @admin_id),
  ('Mark Vincent Flores', '09171230012', 'Male', 'Purok 4, Gabon, Polangui, Albay', '2003-10-03', 'BSCS', 3, 'D', 'Irregular', @admin_id),
  ('Princess Mae Lopez', 'N/A', 'Female', 'Purok 2, Magpanambo, Polangui, Albay', '2004-01-16', 'BSIS', 2, 'B', 'Regular', @admin_id),
  ('Daniel Gabriel Cruz', '09171230014', 'Male', 'Purok 5, Itaran, Polangui, Albay', '2002-07-27', 'BSIT', 4, 'D', 'Graduate', @admin_id),
  ('Shaira Nicole Domingo', '09171230015', 'Female', 'Purok 1, Lourdes, Polangui, Albay', '2005-04-12', 'BSCS', 1, 'A', 'Regular', @admin_id),
  ('Patrick James Soriano', '09171230016', 'Male', 'Purok 7, Salvacion, Polangui, Albay', '2003-03-08', 'BSIS', 3, 'C', 'Regular', @admin_id),
  ('Maria Cristina Tolentino', '09171230017', 'Female', 'Purok 3, Balinad, Polangui, Albay', '2004-09-23', 'BSIT', 2, 'D', 'Regular', @admin_id),
  ('Ericka Louise Mercado', '09171230018', 'Female', 'Purok 2, Amoroy, Polangui, Albay', '2002-11-14', 'BSCS', 4, 'B', 'Graduate', @admin_id),
  ('Kenneth Ray Pascual', '09171230019', 'Male', 'Purok 4, Cotmon, Polangui, Albay', '2005-06-30', 'BSIS', 1, 'D', 'Regular', @admin_id),
  ('Bianca Isabel Aguilar', '09171230020', 'Female', 'Purok 6, Maynaga, Polangui, Albay', '2003-12-01', 'BSIT', 3, 'C', 'Regular', @admin_id),
  ('Joshua Paul Rivera', '09171230021', 'Male', 'Purok 1, Ubaliw, Polangui, Albay', '2004-08-07', 'BSCS', 2, 'A', 'Irregular', @admin_id),
  ('Hannah Grace Padilla', '09171230022', 'Female', 'Purok 5, Agos, Polangui, Albay', '2005-02-20', 'BSIS', 2, 'D', 'Regular', @admin_id),
  ('Marlon Jay Estrada', '09171230023', 'Male', 'Purok 3, Balaba, Polangui, Albay', '2002-05-18', 'BSIT', 4, 'A', 'Regular', @admin_id),
  ('Trisha Marie Fernandez', '09171230024', 'Female', 'Purok 2, Centro Occidental, Polangui, Albay', '2004-10-29', 'BSIS', 3, 'B', 'Regular', @admin_id);
