
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
    `square` float COMMENT 'Square of the room'
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
	`status` varchar(1) NOT NULL,
	`includes` varchar(100),

	/* Date */
	`arrival` varchar(30) NOT NULL,
	`departure` varchar(20) NOT NULL,

	/* Food */
	`breakfast` varchar(1) NOT NULL,
	`dinner` varchar(1) NOT NULL,
	`snack` varchar(1) NOT NULL
);
