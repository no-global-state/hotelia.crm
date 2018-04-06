
CREATE TABLE velveto_meals (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `order` INT NOT NULL
);

CREATE TABLE velveto_meals_translations (
    `id` INT NOT NULL,
    `lang_id` INT NOT NULL,
    `name` varchar(255) NOT NULL,

    FOREIGN KEY (lang_id) REFERENCES velveto_languages(id) ON DELETE CASCADE,
    FOREIGN KEY (id) REFERENCES velveto_meals(id) ON DELETE CASCADE
);

CREATE TABLE velveto_meals_relation (
    `master_id` INT NOT NULL COMMENT 'Hotel ID',
    `slave_id` INT NOT NULL COMMENT 'Meal ID',

    FOREIGN KEY (master_id) REFERENCES velveto_hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (slave_id) REFERENCES velveto_meals(id) ON DELETE CASCADE
);

CREATE TABLE velveto_meals_global_prices (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `hotel_id` INT NOT NULL,
    `price_group_id` INT NOT NULL,
    `price` FLOAT NOT NULL,

    FOREIGN KEY (hotel_id) REFERENCES velveto_hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (price_group_id) REFERENCES velveto_price_groups(id) ON DELETE CASCADE
);

CREATE TABLE velveto_dictionary (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `alias` varchar(255) NOT NULL COMMENT 'Alias name'
);

CREATE TABLE velveto_dictionary_translation (
    `id` INT NOT NULL,
    `lang_id` INT NOT NULL,
    `value` varchar(255) NOT NULL,

    FOREIGN KEY (id) REFERENCES velveto_dictionary(id) ON DELETE CASCADE
);

CREATE TABLE velveto_hotel_types (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `order` INT NOT NULL COMMENT 'Soring order'
);

CREATE TABLE velveto_hotel_types_translation (
    `id` INT NOT NULL,
    `lang_id` INT NOT NULL,
    `name` varchar(255) NOT NULL,

    FOREIGN KEY (lang_id) REFERENCES velveto_languages(lang_id) ON DELETE CASCADE,
    FOREIGN KEY (id) REFERENCES velveto_hotel_types(id) ON DELETE CASCADE
);

CREATE TABLE velveto_payment_systems (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `order` INT NOT NULL COMMENT 'Soring order',
    `name` varchar(255) NOT NULL
);

CREATE TABLE velveto_payment_systems_fields (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `payment_system_id` INT NOT NULL,
    `order` INT NOT NULL COMMENT 'Soring order',
    `name` varchar(255) NOT NULL,

    FOREIGN KEY (payment_system_id) REFERENCES velveto_payment_systems(id) ON DELETE CASCADE
);

CREATE TABLE velveto_payment_systems_fields_data (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `field_id` INT NOT NULL,
    `hotel_id` INT NOT NULL COMMENT 'Attached hotel ID',
    `value` TEXT NOT NULL,

    FOREIGN KEY (field_id) REFERENCES velveto_payment_systems_fields(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES velveto_hotels(id) ON DELETE CASCADE
);

CREATE TABLE velveto_discounts (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `hotel_id` INT NOT NULL COMMENT 'Attached hotel ID',
    `name` varchar(255) NOT NULL COMMENT 'Discount name',
    `percentage` FLOAT NOT NULL,

    FOREIGN KEY (hotel_id) REFERENCES velveto_hotels(id) ON DELETE CASCADE
);

CREATE TABLE velveto_price_groups (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` varchar(255) NOT NULL COMMENT 'Price Group Name',
    `order` INT NOT NULL COMMENT 'Price Group Sorting Order',
    `currency` varchar(255) NOT NULL COMMENT 'Attached currency',
    `daily_tax` FLOAT NOT NULL COMMENT 'Daily tax for the current group'
);

CREATE TABLE velveto_regions (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `order` INT NOT NULL COMMENT 'Region sorting order',
    `image` varchar(255) COMMENT 'Optional region image'
);

CREATE TABLE velveto_regions_translation (
    `id` INT NOT NULL,
    `lang_id` INT NOT NULL,
    `name` varchar(255),

    FOREIGN KEY (lang_id) REFERENCES velveto_languages(id) ON DELETE CASCADE,
    FOREIGN KEY (id) REFERENCES velveto_regions(id) ON DELETE CASCADE
);

CREATE TABLE velveto_regions_districts (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `region_id` INT NOT NULL COMMENT 'Attached region ID',
    `order` INT NOT NULL COMMENT 'Disctrict sorting order',

    FOREIGN KEY (region_id) REFERENCES velveto_regions(id) ON DELETE CASCADE
);

CREATE TABLE velveto_regions_districts_translation (
    `id` INT NOT NULL,
    `lang_id` INT NOT NULL,
    `name` varchar(255),

    FOREIGN KEY (lang_id) REFERENCES velveto_languages(id) ON DELETE CASCADE,
    FOREIGN KEY (id) REFERENCES velveto_regions_districts(id) ON DELETE CASCADE
);

CREATE TABLE velveto_reviews_types (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `mark` SMALLINT NOT NULL COMMENT 'Default review type mark',
    `order` INT NOT NULL COMMENT 'Sorting order',
    `name` varchar(255)
);

CREATE TABLE velveto_reviews (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `lang_id` INT NOT NULL,
    `hotel_id` INT NOT NULL,
    `date` TIMESTAMP NOT NULL,
    `title` varchar(255) NOT NULL COMMENT 'Review title',
    `review` TEXT COMMENT 'Review itself',
    `rating` SMALLINT NOT NULL COMMENT 'Summary rating',

    FOREIGN KEY (hotel_id) REFERENCES velveto_hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (lang_id) REFERENCES velveto_languages(id) ON DELETE CASCADE
);

CREATE TABLE velveto_reviews_marks (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `review_id` INT NOT NULL COMMENT 'Attached review ID',
    `review_type_id` INT NOT NULL COMMENT 'Attached review type ID',
    `mark` SMALLINT,

    FOREIGN KEY (review_id) REFERENCES velveto_reviews(id) ON DELETE CASCADE,
    FOREIGN KEY (review_type_id) REFERENCES velveto_reviews_types(id) ON DELETE CASCADE
);

CREATE TABLE velveto_languages (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` varchar(255) NOT NULL COMMENT 'Language name',
    `code` varchar(5) NOT NULL COMMENT 'Language code',
    `order` INT DEFAULT 0 COMMENT 'Sorting order',
    `image` varchar(255) COMMENT 'Optional image path'
);

CREATE TABLE velveto_facilitiy_relations (
    `master_id` INT NOT NULL COMMENT 'Hotel ID',
    `slave_id` INT NOT NULL COMMENT 'Item ID',
    `type` SMALLINT NOT NULL COMMENT 'Facility type const',

    FOREIGN KEY (master_id) REFERENCES velveto_hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (slave_id) REFERENCES velveto_facilitiy_items(id) ON DELETE CASCADE
);

CREATE TABLE velveto_facilitiy_categories (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `order` INT NOT NULL DEFAULT 0 COMMENT 'Sorting order'
);

CREATE TABLE velveto_facilitiy_categories_translation (
    `id` INT NOT NULL,
    `lang_id` INT NOT NULL,
    `name` varchar(255),

    FOREIGN KEY (lang_id) REFERENCES velveto_languages(id) ON DELETE CASCADE,
    FOREIGN KEY (id) REFERENCES velveto_facilitiy_categories(id) ON DELETE CASCADE
);

CREATE TABLE velveto_facilitiy_items (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `icon` varchar(255) NOT NULL,
    `front` SMALLINT(1) NOT NULL COMMENT 'Whether item must be front or not',
    `category_id` INT NOT NULL,
    `always_free` BOOLEAN NOT NULL COMMENT 'Whether the item is always free',

    FOREIGN KEY (category_id) REFERENCES velveto_facilitiy_categories(id) ON DELETE CASCADE
);

CREATE TABLE velveto_facilitiy_items_translation (
    `id` INT NOT NULL,
    `lang_id` INT NOT NULL,
    `name` varchar(255),
    `description` TEXT COMMENT 'Optional description',

    FOREIGN KEY (lang_id) REFERENCES velveto_languages(id) ON DELETE CASCADE,
    FOREIGN KEY (id) REFERENCES velveto_facilitiy_items(id) ON DELETE CASCADE
);

CREATE TABLE velveto_facility_items_data (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `item_id` INT NOT NULL COMMENT 'Attched item ID',
    `order` INT NOT NULL COMMENT 'Sorting order',

    FOREIGN KEY (item_id) REFERENCES velveto_facilitiy_items(id) ON DELETE CASCADE
);

CREATE TABLE velveto_facility_items_data_translation (
    `id` INT NOT NULL,
    `lang_id` INT NOT NULL,
    `name` varchar(255) NOT NULL,

    FOREIGN KEY (lang_id) REFERENCES velveto_languages(id) ON DELETE CASCADE,
    FOREIGN KEY (id) REFERENCES velveto_facility_items_data(id) ON DELETE CASCADE
);

CREATE TABLE velveto_hotels_photos (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `hotel_id` INT NOT NULL,
    `file` varchar(30) NOT NULL COMMENT 'Base name of photo file',
    `order` INT NOT NULL COMMENT 'Sorting order',

    FOREIGN KEY (hotel_id) REFERENCES velveto_hotels(id) ON DELETE CASCADE
);

CREATE TABLE velveto_hotels_photos_covers (
    `master_id` INT NOT NULL COMMENT 'Hotel ID',
    `slave_id` INT NOT NULL COMMENT 'Photo ID',

    FOREIGN KEY (master_id) REFERENCES velveto_hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (slave_id) REFERENCES velveto_hotels_photos(id) ON DELETE CASCADE
);

CREATE TABLE velveto_hotels_transactions (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `hotel_id` INT DEFAULT 1 COMMENT 'Attached hotel ID',
    `datetime` TIMESTAMP,
    `holder` varchar(255) NOT NULL COMMENT 'Card holder name',
    `payment_system` varchar(30) NOT NULL,
    `amount` FLOAT NOT NULL,
    `currency` varchar(20) NOT NULL,
    `comment` TEXT NOT NULL,

    FOREIGN KEY (hotel_id) REFERENCES velveto_hotels(id) ON DELETE CASCADE
);

CREATE TABLE velveto_users (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `hotel_id` INT NOT NULL COMMENT 'Attached hotel ID',
    `name` varchar(255) NULL NOT NULL,
    `email` varchar(255) NOT NULL,
    `login` varchar(255) NOT NULL,
    `password` varchar(100) NOT NULL,
    `role` varchar(1) NOT NULL,
    `wizard_finished` BOOLEAN DEFAULT 0 COMMENT 'Whether wizard has been finished'

    FOREIGN KEY (hotel_id) REFERENCES velveto_hotels(id) ON DELETE CASCADE
);

CREATE TABLE velveto_room (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `type_id` INT NOT NULL COMMENT 'Room type ID',
    `hotel_id` INT NOT NULL COMMENT 'Attached hotel ID',
    `floor` INT DEFAULT 0 COMMENT 'Optional floor number',
    `persons` INT COMMENT 'Maximal amount of persons for the room',
    `name` varchar(255) NOT NULL,
    `square` float COMMENT 'Square of the room',
    `cleaned` varchar(1) NOT NULL COMMENT 'Cleaning code status of the room',
    `quality` SMALLINT(1) NOT NULL COMMENT 'Room quality code',

    FOREIGN KEY (type_id) REFERENCES velveto_room_types(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES velveto_hotels(id) ON DELETE CASCADE
);

CREATE TABLE velveto_room_categories (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `order` INT NOT NULL COMMENT 'Sorting order'
);

CREATE TABLE velveto_room_categories_translations (
    `id` INT NOT NULL,
    `lang_id` INT NOT NULL,
    `name` varchar(255) NOT NULL COMMENT 'Room category name',

    FOREIGN KEY (id) REFERENCES velveto_room_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (lang_id) REFERENCES velveto_languages(id) ON DELETE CASCADE
);

CREATE TABLE velveto_room_types (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `hotel_id` INT NOT NULL COMMENT 'Attached hotel ID',
    `category_id` INT NOT NULL COMMENT 'Attached Category ID',
    `persons` INT NOT COMMENT 'Maximal amount of persons for the room',

    FOREIGN KEY (hotel_id) REFERENCES velveto_hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES velveto_room_categories(id) ON DELETE CASCADE
);

CREATE TABLE velveto_room_types_translations (
    `id` INT NOT NULL,
    `lang_id` INT NOT NULL COMMENT 'Attached language ID',
    `description` TEXT NOT NULL,

    FOREIGN KEY (id) REFERENCES velveto_room_types(id) ON DELETE CASCADE,
    FOREIGN KEY (lang_id) REFERENCES velveto_languages(id) ON DELETE CASCADE
);

CREATE TABLE velveto_room_type_prices (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `room_type_id` INT NOT NULL COMMENT 'Room type ID',
    `price_group_id` INT NOT NULL COMMENT 'Attached price group ID',
    `price` FLOAT NOT NULL,

    FOREIGN KEY (room_type_id) REFERENCES velveto_room_types(id) ON DELETE CASCADE,
    FOREIGN KEY (price_group_id) REFERENCES velveto_price_groups(id) ON DELETE CASCADE
);

CREATE TABLE velveto_room_type_gallery (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `room_type_id` INT NOT NULL COMMENT 'Room type ID',
    `file` varchar(255) COMMENT 'Photo file path',
    `order` INT COMMENT 'Sorting order',

    FOREIGN KEY (room_type_id) REFERENCES velveto_room_types(id) ON DELETE CASCADE
);

CREATE TABLE velveto_room_type_gallery_covers (
    `master_id` INT NOT NULL COMMENT 'Room type ID',
    `slave_id` INT NOT NULL COMMENT 'Gallery ID',

    FOREIGN KEY (master_id) REFERENCES velveto_room_types(id) ON DELETE CASCADE,
    FOREIGN KEY (slave_id) REFERENCES velveto_room_type_gallery(id) ON DELETE CASCADE
);

CREATE TABLE velveto_room_type_facility (
    `master_id` INT NOT NULL COMMENT 'Room type ID',
    `slave_id` INT NOT NULL COMMENT 'Facility ID',
    `type` SMALLINT NOT NULL COMMENT 'Facility type const',

    FOREIGN KEY (master_id) REFERENCES velveto_room_types(id) ON DELETE CASCADE,
    FOREIGN KEY (slave_id) REFERENCES velveto_facilitiy_items(id) ON DELETE CASCADE
);

CREATE TABLE velveto_inventory (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `hotel_id` INT NOT NULL COMMENT 'Attached hotel ID',
    `name` varchar(255) NOT NULL,

    FOREIGN KEY (hotel_id) REFERENCES velveto_hotels(id) ON DELETE CASCADE
);

CREATE TABLE velveto_room_inventory (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `room_id` INT NOT NULL COMMENT 'Attached room ID',
    `inventory_id` INT NOT NULL COMMENT 'Attached inventory ID',
    `code` varchar(255) NOT NULL COMMENT 'Serial number or code',
    `qty` INT NOT NULL COMMENT 'Quantity',
    `comment` TEXT,

    FOREIGN KEY (inventory_id) REFERENCES velveto_inventory(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES velveto_room(id) ON DELETE CASCADE
);

CREATE TABLE velveto_services (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `hotel_id` INT DEFAULT 1 COMMENT 'Attached hotel ID',
    `name` varchar(255) NOT NULL,
    `unit` varchar(3) NOT NULL,
    `comment` TEXT,

    FOREIGN KEY (hotel_id) REFERENCES velveto_hotels(id) ON DELETE CASCADE
);

CREATE TABLE velveto_service_prices (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `service_id` INT NOT NULL COMMENT 'Service ID',
    `price_group_id` INT NOT NULL COMMENT 'Attached price group ID',
    `price` FLOAT NOT NULL,

    FOREIGN KEY (service_id) REFERENCES velveto_services(id) ON DELETE CASCADE,
    FOREIGN KEY (price_group_id) REFERENCES velveto_price_groups(id) ON DELETE CASCADE
);

CREATE TABLE velveto_hotels (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `type_id` INT NOT NULL COMMENT 'Type ID',
    `region_id` INT NOT NULL COMMENT 'Attached region ID',
    `district_id` INT NOT NULL COMMENT 'Attached district ID',
    `phone` varchar(255) NOT NULL,
    `fax` varchar(255) NOT NULL,
    `zip` varchar(255) NOT NULL COMMENT 'ZIP code',
    `rate` SMALLINT COMMENT 'Hotel rate',
    `discount` FLOAT COMMENT 'Discount if available',
    `website` varchar(255) NOT NULL COMMENT 'Web-site URL',
    `email` varchar(255) NOT NULL COMMENT 'Hotel email',
    `active` BOOLEAN NOT NULL COMMENT 'Whether this hotel must be visible or not',
    `closed` BOOLEAN NOT NULL COMMENT 'Whether this hotel is closed or not',
    `legal_address` TEXT NOT NULL COMMENT 'Legal address',
    `legal_name` varchar(255) NOT NULL COMMENT 'Legal name',
    `lat` varchar(255),
    `lng` varchar(255),
    `city_tax_include` BOOLEAN NOT NULL COMMENT 'Whether city tax is used',
    `contact_full_name` varchar(255) NOT NULL,
    `contact_position` varchar(255) NOT NULL,
    `contact_email` varchar(255) NOT NULL,
    `contact_first_phone` varchar(255) NOT NULL,
    `contact_second_phone` varchar(255) NOT NULL,
    `checkin_from` varchar(5) NOT NULL,
    `checkin_to` varchar(5) NOT NULL,
    `checkout_from` varchar(5) NOT NULL,
    `checkout_to` varchar(5) NOT NULL,
    `payment_time` varchar(5) NOT NULL,
    `breakfast` SMALLINT COMMENT 'Breakfast constant',
    `has_restaurant` BOOLEAN,
    `restaurant_opening` varchar(5),
    `restaurant_closing` varchar(5),
    `center_distance` varchar(10),

    `penality_enabled` BOOLEAN,
    `penality_not_taken_after` INT,
    `penality_not_later_arrival` INT,
    `penality_cancelation_item` INT,
    `penality_cancelation_type` varchar(5),
    `penality_percentage` INT,
    `penality_percentage_type` varchar(5),

    FOREIGN KEY (district_id) REFERENCES velveto_regions_districts(id) ON DELETE CASCADE,
    FOREIGN KEY (region_id) REFERENCES velveto_regions(id) ON DELETE CASCADE
);

CREATE TABLE velveto_hotels_translation (
    `id` INT NOT NULL,
    `lang_id` INT NOT NULL,
    `name` varchar(255) NOT NULL,
    `address` varchar(255) NOT NULL,
    `description` TEXT NOT NULL,

    FOREIGN KEY (lang_id) REFERENCES velveto_languages(id) ON DELETE CASCADE,
    FOREIGN KEY (id) REFERENCES velveto_hotels(id) ON DELETE CASCADE
);

CREATE TABLE velveto_reservation (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `hotel_id` INT NOT NULL COMMENT 'Attached hotel ID',
	`room_id` INT NOT NULL,
    `price_group_id` INT COMMENT 'Attached price group ID',
    `payment_system_id` INT NOT NULL COMMENT 'Payment system attached ID',
	`full_name` varchar(255) NOT NULL COMMENT 'First, last, middle names',
	`gender` varchar(1) NOT NULL,
	`country` varchar(2) NOT NULL,
	`status` varchar(1) NOT NULL COMMENT 'Person status code (like regular or VIP)',
    `state` SMALLINT(1) NOT NULL COMMENT 'Reservation state code',
    `purpose` SMALLINT(1) NOT NULL COMMENT 'Reservation purpose code',
    `source` SMALLINT(1) NOT NULL COMMENT 'Source code',
    `legal_status` SMALLINT(1) NOT NULL COMMENT 'Legal status code',
    `phone` varchar(250) NOT NULL,
    `email` varchar(100) NOT NULL,
    `passport` TEXT NOT NULL COMMENT 'Passport data',
    `discount` FLOAT NOT NULL COMMENT 'Discount percentage',
    `comment` TEXT NOT NULL COMMENT 'Additional note if any',
    `company` varchar(255) NOT NULL,
    `tax` float NOT NULL COMMENT 'Fixed tax',
    `price` float NOT NULL COMMENT 'Total reservation price',

    /* Date */
	`arrival` DATE NOT NULL,
	`departure` DATE NOT NULL,

    FOREIGN KEY (hotel_id) REFERENCES velveto_hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_system_id) REFERENCES velveto_payment_systems(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES velveto_room(id) ON DELETE CASCADE,
    FOREIGN KEY (price_group_id) REFERENCES velveto_price_groups(id) ON DELETE CASCADE
);

CREATE TABLE velveto_reservation_services (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `master_id` INT NOT NULL COMMENT 'Reservation ID',
    `slave_id` INT NOT NULL COMMENT 'Service ID',
    `qty` FLOAT NOT NULL COMMENT 'Quantity',
    `rate` FLOAT COMMENT 'Per unit rate',
    `price` FLOAT COMMENT 'Total price',

    FOREIGN KEY (master_id) REFERENCES velveto_reservation(id) ON DELETE CASCADE,
    FOREIGN KEY (slave_id) REFERENCES velveto_services(id) ON DELETE CASCADE
);
