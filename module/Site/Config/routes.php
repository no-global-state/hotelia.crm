<?php

return array(

    '/transaction/index/(:var)' => array(
        'controller' => 'Transaction@indexAction'
    ),

    '/hotel' => array(
        'controller' => 'Hotel@indexAction'
    ),

    '/hotel/save' => array(
        'controller' => 'Hotel@saveAction'
    ),

    '/services' => array(
        'controller' => 'Service@indexAction'
    ),
    
    '/services/edit/(:var)' => array(
		'controller' => 'Service@editAction'
    ),

    '/services/delete/(:var)' => array(
		'controller' => 'Service@deleteAction'
    ),

    '/services/save' => array(
		'controller' => 'Service@saveAction'
    ),

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

	'/reservation/table/taken/(:var)' => array(
		'controller' => 'Reservation@viewTakenAction'
	),
	
	// Reservation add
	'/reservation/add/(:var)' => array(
		'controller' => 'Reservation@addAction'
	),

	'/reservation/save' => array(
		'controller' => 'Reservation@saveAction'
	),

	'/reservation/index' => array(
		'controller' => 'Reservation@indexAction'
	),

	'/reservation/view/(:var)' => array(
        'controller' => 'Reservation@viewAction'
	),

    '/reservation/index/(:var)' => array(
		'controller' => 'Reservation@indexAction'
	),

	'/reservation/edit/(:var)' => array(
		'controller' => 'Reservation@editAction'
	),
    
	'/reservation/delete/(:var)' => array(
		'controller' => 'Reservation@deleteAction'
	),
    
    '/reservation/history/room/(:var)' => array(
        'controller' => 'Reservation@historyAction'
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

    // Room cleaning
    '/architecture/room-cleaning' => array(
		'controller' => 'Architecture:RoomCleaning@indexAction'
    ),
    
    '/architecture/room-cleaning/room/(:var)/mark/(:var)' => array(
        'controller' => 'Architecture:RoomCleaning@markAction'
    ),
    
    '/architecture/room-cleaning/mark-batch/(:var)' => array(
        'controller' => 'Architecture:RoomCleaning@markBatchAction'
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
	
	'/architecture/room/view/(:var)' => array(
		'controller' => 'Architecture:Room@viewAction'
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
