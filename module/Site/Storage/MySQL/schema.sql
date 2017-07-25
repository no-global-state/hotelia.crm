
CREATE TABLE hotelia_floor (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` INT NOT NULL
);

CREATE TABLE hotelia_floor_room (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `floor_id` INT NOT NULL,
    `name` INT NOT NULL
);
