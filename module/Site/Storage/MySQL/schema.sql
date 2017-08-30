
CREATE TABLE hotelia_floor (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` varchar(255) NULL
);

CREATE TABLE hotelia_floor_room (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `floor_id` INT NOT NULL,
    `type_id` INT NOT NULL COMMENT 'Room type ID',
    `persons` INT COMMENT 'Maximal amount of persons for the room'
    `name` varchar(255) NOT NULL,
    `square` float COMMENT 'Square of the room',
    `cleaned` varchar(1) NOT NULL COMMENT 'Cleaning code status of the room',
    `quality` SMALLINT(1) NOT NULL COMMENT 'Room quality code'
);

CREATE TABLE hotelia_floor_room_types (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `type` varchar(255) NOT NULL
);

CREATE TABLE hotelia_inventory (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` varchar(255) NOT NULL
);

CREATE TABLE hotelia_room_inventory (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `room_id` INT NOT NULL COMMENT 'Attached room ID',
    `inventory_id` INT NOT NULL COMMENT 'Attached inventory ID',
    `code` varchar(255) NOT NULL COMMENT 'Serial number or code',
    `qty` INT NOT NULL COMMENT 'Quantity',
    `comment` TEXT
);

CREATE TABLE hotelia_room_services (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `price` FLOAT NOT NULL,
    `unit` varchar(3) NOT NULL,
    `comment` TEXT
);

DROP TABLE IF EXISTS hotelia_reservation;
CREATE TABLE hotelia_reservation (
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
    `comment` TEXT NOT NULL COMMENT 'Additional note if any'

	/* Date */
	`arrival` DATE NOT NULL,
	`departure` DATE NOT NULL
);

CREATE TABLE hotelia_reservation_services (
    `master_id` INT NOT NULL COMMENT 'Reservation ID',
    `slave_id` INT NOT NULL COMMENT 'Service ID'
);
