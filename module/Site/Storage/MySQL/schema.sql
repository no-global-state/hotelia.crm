
CREATE TABLE velveto_hotels_transactions (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `hotel_id` INT DEFAULT 1,
    `datetime` TIMESTAMP,
    `payment_system` varchar(30) NOT NULL,
    `amount` FLOAT NOT NULL,
    `currency` varchar(20) NOT NULL,
    `comment` TEXT NOT NULL
);

CREATE TABLE velveto_users (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` varchar(255) NULL,
    `email` varchar(255) NOT NULL,
    `password` varchar(100) NOT NULL,
    `role` varchar(1) NOT NULL
);

CREATE TABLE velveto_floor (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` varchar(255) NULL
);

CREATE TABLE velveto_floor_room (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `floor_id` INT NOT NULL,
    `type_id` INT NOT NULL COMMENT 'Room type ID',
    `persons` INT COMMENT 'Maximal amount of persons for the room'
    `name` varchar(255) NOT NULL,
    `square` float COMMENT 'Square of the room',
    `cleaned` varchar(1) NOT NULL COMMENT 'Cleaning code status of the room',
    `quality` SMALLINT(1) NOT NULL COMMENT 'Room quality code'
);

CREATE TABLE velveto_floor_room_types (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `type` varchar(255) NOT NULL,
    `unit_price` FLOAT NOT NULL
);

CREATE TABLE velveto_inventory (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` varchar(255) NOT NULL
);

CREATE TABLE velveto_room_inventory (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `room_id` INT NOT NULL COMMENT 'Attached room ID',
    `inventory_id` INT NOT NULL COMMENT 'Attached inventory ID',
    `code` varchar(255) NOT NULL COMMENT 'Serial number or code',
    `qty` INT NOT NULL COMMENT 'Quantity',
    `comment` TEXT
);

CREATE TABLE velveto_room_services (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `price` FLOAT NOT NULL,
    `unit` varchar(3) NOT NULL,
    `comment` TEXT
);

DROP TABLE IF EXISTS velveto_hotels;
CREATE TABLE velveto_hotels (
    `id` INT DEFAULT 1 PRIMARY KEY,
    `city` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `address` varchar(255) NOT NULL,
    `phone` varchar(255) NOT NULL,
    `description` TEXT NOT NULL,
    `start_price` FLOAT NOT NULL
);

DROP TABLE IF EXISTS velveto_reservation;
CREATE TABLE velveto_reservation (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`room_id` INT NOT NULL,
	`full_name` varchar(255) NOT NULL COMMENT 'First, last, middle names',
	`gender` varchar(1) NOT NULL,
	`country` varchar(2) NOT NULL,
	`status` varchar(1) NOT NULL COMMENT 'Person status code (like regular or VIP)',
    `state` SMALLINT(1) NOT NULL COMMENT 'Reservation state code',
    `purpose` SMALLINT(1) NOT NULL COMMENT 'Reservation purpose code',
    `payment_type` SMALLINT(1) NOT NULL COMMENT 'Payment type code',
    `legal_status ` SMALLINT(1) NOT NULL COMMENT 'Legal status code',
    `phone` varchar(250) TEXT NOT NULL,
    `email` varchar(100) NOT NULL,
    `passport` TEXT NOT NULL COMMENT 'Passport data',
    `discount` FLOAT NOT NULL COMMENT 'Discount percentage',
    `comment` TEXT NOT NULL COMMENT 'Additional note if any'

	/* Date */
	`arrival` DATE NOT NULL,
	`departure` DATE NOT NULL
);

CREATE TABLE velveto_reservation_services (
    `master_id` INT NOT NULL COMMENT 'Reservation ID',
    `slave_id` INT NOT NULL COMMENT 'Service ID'
);
