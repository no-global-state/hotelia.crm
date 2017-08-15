
CREATE TABLE hotelia_floor (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` varchar(255) NULL
);

CREATE TABLE hotelia_floor_room (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `floor_id` INT NOT NULL,
    `name` varchar(255) NOT NULL
);

CREATE TABLE hotelia_floor_room_types (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `type` varchar(255) NOT NULL
);


DROP TABLE IF EXISTS hotelia_reservation;
CREATE TABLE hotelia_reservation (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`first_name` varchar(255) NOT NULL,
	`last_name` varchar(255) NOT NULL,
	`middle_name` varchar(255) NOT NULL,
	`gender` varchar(1) NOT NULL,
	`country` varchar(2) NOT NULL,
	`status` varchar(1) NOT NULL,
	`floor` SMALLINT NOT NULL,
	`room` SMALLINT NOT NULL,
	`includes` varchar(100),

	/* Date */
	`arrival` varchar(30) NOT NULL,
	`departure` varchar(20) NOT NULL,

	/* Food */
	`breakfast` varchar(1) NOT NULL,
	`dinner` varchar(1) NOT NULL,
	`snack` varchar(1) NOT NULL
);
