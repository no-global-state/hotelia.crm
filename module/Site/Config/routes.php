<?php

return array(

	// Inventory
	'/inventory' => array(
		'controller' => 'Inventory@indexAction'
	),

	'/inventory/edit/(:var)' => array(
		'controller' => 'Inventory@editAction'
	),

	'/inventory/delete/(:var)' => array(
		'controller' => 'Inventory@deleteAction'
	),
    
	'/inventory/save' => array(
		'controller' => 'Inventory@saveAction'
	),
    
	// Reservation add
	'/reservation/table' => array(
		'controller' => 'Reservation@tableAction'
	),
	
	// Reservation add
	'/reservation/add' => array(
		'controller' => 'Reservation@addAction'
	),

	'/reservation/index' => array(
		'controller' => 'Reservation@indexAction'
	),

	'/site/captcha/(:var)' => array(
		'controller' => 'Site@captchaAction'
	),

	'/' => array(
		'controller' => 'Site@indexAction'
	),

	'/auth/login' => array(
		'controller' => 'Auth@indexAction'
	),
	'/auth/logout' => array(
		'controller' => 'Auth@logoutAction'
	),
	'/register' => array(
		'controller' => 'Register@indexAction'
	),

	'/architecture/room-type' => array(
		'controller' => 'Architecture:RoomType@indexAction'
	),

	'/architecture/room-type/edit/(:var)' => array(
		'controller' => 'Architecture:RoomType@editAction'
	),

	'/architecture/room-type/delete/(:var)' => array(
		'controller' => 'Architecture:RoomType@deleteAction'
	),

	'/architecture/room-type/save' => array(
		'controller' => 'Architecture:RoomType@saveAction'
	),
	
	'/architecture' => array(
		'controller' => 'Architecture:Grid@indexAction'
	),

	'/architecture/view/(:var)' => array(
		'controller' => 'Architecture:Grid@floorAction'
	),
	
	'/architecture/room/add' => array(
		'controller' => 'Architecture:Room@saveAction'
	),
	
	'/architecture/room/save' => array(
		'controller' => 'Architecture:Room@saveAction'
	),
	
	'/architecture/room/edit/(:var)' => array(
		'controller' => 'Architecture:Room@editAction'
	),

	'/architecture/room/delete/(:var)' => array(
		'controller' => 'Architecture:Room@deleteAction'
	),

    // Room inventory
	'/architecture/room/(:var)/inventory' => array(
		'controller' => 'Architecture:RoomInventory@indexAction'
	),

	'/architecture/room/(:var)/inventory/edit/(:var)' => array(
		'controller' => 'Architecture:RoomInventory@editAction'
	),

	'/architecture/room/inventory/save' => array(
		'controller' => 'Architecture:RoomInventory@saveAction'
	),

	'/architecture/room/(:var)/inventory/delete/(:var)' => array(
		'controller' => 'Architecture:RoomInventory@deleteAction'
	),

	'/architecture/floor/add' => array(
		'controller' => 'Architecture:Floor@saveAction'
	),

	'/architecture/floor/save' => array(
		'controller' => 'Architecture:Floor@saveAction'
	),
	
	'/architecture/floor/edit/(:var)' => array(
		'controller' => 'Architecture:Floor@editAction'
	),

	'/architecture/floor/delete/(:var)' => array(
		'controller' => 'Architecture:Floor@deleteAction'
	),
);
