-- test in MAMP
-- will be js1160 if in university server
use kiwi_kloset;

DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS branches;
DROP TABLE IF EXISTS costumes;
DROP TABLE IF EXISTS rental;

CREATE TABLE customers (
	id INT PRIMARY KEY,
    email VARCHAR(255),
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20)
);

CREATE TABLE branches (
	id INT PRIMARY KEY,
    name varchar(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
	email VARCHAR(255) NOT NULL
);

CREATE TABLE costumes (
    id INT PRIMARY KEY,
	is_available TINYINT(1) NOT NULL,
	branch_id INT NOT NULL,
	name VARCHAR(255) NOT NULL,
	size VARCHAR(255) NOT NULL,
	daily_rate DECIMAL(10, 2) NOT NULL,
	category VARCHAR(255) NOT NULL
);

CREATE TABLE rentals (
	id INT PRIMARY KEY,
	start_datetime DATETIME NOT NULL,
	end_datetime DATETIME NOT NULL,
	costume_id INT NOT NULL,
	customer_id INT NOT NULL
);

INSERT INTO customers (id, email, first_name, last_name, phone) VALUES
(1, 'john.doe@example.com', 'John', 'Doe', '123-456-7890'),
(2, 'jane.smith@example.com', 'Jane', 'Smith', '987-654-3210'),
(3, 'michael.jackson@example.com', 'Michael', 'Jackson', '555-123-4567'),
(4, 'emily.brown@example.com', 'Emily', 'Brown', '111-222-3333'),
(5, 'david.williams@example.com', 'David', 'Williams', '444-555-6666'),
(6, 'sarah.miller@example.com', 'Sarah', 'Miller', '777-888-9999'),
(7, 'james.wilson@example.com', 'James', 'Wilson', '333-222-1111'),
(8, 'jennifer.taylor@example.com', 'Jennifer', 'Taylor', '666-777-8888'),
(9, 'robert.anderson@example.com', 'Robert', 'Anderson', '999-888-7777');

INSERT INTO branches (id, name, location, email) VALUES
(1, 'Auckland Central Branch', 'Auckland', 'auckland.central.branch@kiwikloset.com'),
(2, 'Wellington CBD Branch', 'Wellington', 'wellington.cbd.branch@kiwikloset.com'),
(3, 'Christchurch Central Branch', 'Christchurch', 'christchurch.central.branch@kiwikloset.com'),
(4, 'Hamilton East Branch', 'Hamilton', 'hamilton.east.branch@kiwikloset.com'),
(5, 'Tauranga Downtown Branch', 'Tauranga', 'tauranga.downtown.branch@kiwikloset.com');

INSERT INTO costumes (id, is_available, branch_id, name, size, daily_rate, category) VALUES
(1, 1, 1, 'Pilot Top Gun', 'Mens Medium', 45.50, 'Movies'),
(2, 1, 1, 'Pilot Top Gun', 'Mens Large', 45.50, 'Movies'),
(3, 1, 4, 'Pilot Top Gun', 'Mens Small', 45.50, 'Movies'),
(4, 1, 4, 'Pilot Top Gun', 'Mens Extra Large', 45.50, 'Movies'),
(5, 1, 2, 'Jedi Knight', 'Mens Large', 40.00, 'Movies'),
(6, 1, 2, 'Jedi Knight', 'Mens Large', 40.00, 'Movies'),
(7, 1, 2, 'Jedi Knight', 'Mens Extra Large', 40.00, 'Movies'),
(8, 1, 1, 'Grim Reaper', 'Mens Small', 64.90, 'Halloween'),
(9, 1, 2, 'Grim Reaper', 'Mens Small', 64.90, 'Halloween'),
(10, 1, 3, 'Grim Reaper', 'Mens Small', 64.90, 'Halloween'),
(11, 1, 4, 'Grim Reaper', 'Mens Small', 64.90, 'Halloween'),
(12, 1, 5, 'Grim Reaper', 'Mens Small', 64.90, 'Halloween'),
(13, 1, 3, 'Mime', 'Womens Small', 45.50, 'Circus'),
(14, 1, 3, 'Mime', 'Womens Medium', 45.50, 'Circus'),
(15, 1, 5, 'Mime', 'Womens Small', 45.50, 'Circus'),
(16, 1, 5, 'Mime', 'Womens Large', 45.50, 'Circus'),
(17, 1, 3, 'Zombie Sea Mate', 'Womens Medium', 40.00, 'Halloween'),
(18, 1, 3, 'Zombie Sea Mate', 'Womens Medium', 40.00, 'Halloween'),
(19, 1, 3, 'Zombie Sea Mate', 'Womens Medium', 40.00, 'Halloween'),
(20, 1, 1, 'Lara Croft', 'Womens Small', 54.30, 'Gaming'),
(21, 1, 1, 'Lara Croft', 'Womens Medium', 54.30, 'Gaming'),
(22, 1, 2, 'Lara Croft', 'Womens Extra Small', 54.30, 'Gaming'),
(23, 1, 2, 'Lara Croft', 'Womens Extra Small', 54.30, 'Gaming'),
(24, 1, 4, 'Christmas Tree', 'Standard', 60.99, 'Novelty'),
(25, 1, 4, 'Christmas Tree', 'Standard', 60.99, 'Novelty');

INSERT INTO rentals (id, start_datetime, end_datetime, costume_id, customer_id) VALUES
(1, '2023-01-15 12:30:00', '2023-01-16 10:30:00', 1, 7),
(2, '2024-01-15 12:30:00', '2024-01-16 10:30:00', 1, 7),
(3, '2025-01-15 12:30:00', '2025-01-16 10:30:00', 1, 7),
(4, '2025-02-15 12:30:00', '2025-02-16 10:30:00', 1, 6),
(5, '2025-02-15 12:30:00', '2025-02-16 10:30:00', 2, 6),
(6, '2025-02-15 12:30:00', '2025-02-16 10:30:00', 20, 6),
(7, '2025-02-15 12:30:00', '2025-02-16 10:30:00', 21, 6),
(8, '2025-10-28 12:30:00', '2025-10-29 10:30:00', 14, 4),
(9, '2025-10-28 12:30:00', '2025-10-29 10:30:00', 17, 4),
(10, '2024-01-15 12:30:00', '2024-01-16 10:30:00', 3, 3),
(11, '2024-01-15 12:30:00', '2024-01-16 10:30:00', 8, 3),
(12, '2025-05-15 12:30:00', '2025-05-16 10:30:00', 3, 3),
(13, '2025-05-15 12:30:00', '2025-05-16 10:30:00', 8, 3),
(14, '2023-12-20 12:30:00', '2023-12-21 10:30:00', 24, 2),
(15, '2024-12-20 12:30:00', '2024-12-21 10:30:00', 25, 2),
(16, '2025-11-20 12:30:00', '2025-11-21 10:30:00', 5, 9),
(17, '2025-11-20 12:30:00', '2025-11-21 10:30:00', 6, 9),
(18, '2025-11-20 12:30:00', '2025-11-21 10:30:00', 7, 9),
(19, '2025-11-20 12:30:00', '2025-11-21 10:30:00', 9, 9),
(20, '2025-11-20 12:30:00', '2025-11-21 10:30:00', 10, 9),
(21, '2025-11-20 12:30:00', '2025-11-21 10:30:00', 11, 9),
(22, '2025-11-20 12:30:00', '2025-11-21 10:30:00', 15, 8),
(23, '2025-11-20 12:30:00', '2025-11-21 10:30:00', 16, 8),
(24, '2025-11-20 12:30:00', '2025-11-21 10:30:00', 18, 8),
(25, '2025-11-20 12:30:00', '2025-11-21 10:30:00', 19, 8);
