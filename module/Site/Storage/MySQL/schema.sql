
CREATE TABLE hotelia_floor (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` varchar(255) NULL
);

CREATE TABLE hotelia_floor_room (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `floor_id` INT NOT NULL,
    `name` varchar(255) NOT NULL
);
