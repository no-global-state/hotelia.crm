<?php

return [
    '/' => [
        'controller' => 'Site@homeAction'
    ],
    
    '/crm' => [
        'controller' => 'Crm@indexAction'
    ],

    '/crm/languages/switch/(:var)' => [
        'controller' => 'Language@switchAction'
    ],
    
    '/crm/languages' => [
        'controller' => 'Language@indexAction'
    ],
    
    '/crm/languages/save' => [
        'controller' => 'Language@saveAction'
    ],

    '/crm/languages/edit/(:var)' => [
        'controller' => 'Language@editAction'
    ],
    
    '/crm/languages/delete/(:var)' => [
        'controller' => 'Language@deleteAction'
    ],
    
    '/payment' => [
        'controller' => 'Site@paymentAction'
    ],
    
    '/search/(:var)' => [
        'controller' => 'Site@searchAction'
    ],
    
    '/hotel/book/(:var)' => [
        'controller' => 'Site@bookAction'
    ],

    '/hotel/(:var)' => [
        'controller' => 'Site@hotelAction'
    ],
    
    '/site/captcha/(:var)' => [
        'controller' => 'Site@captchaAction'
    ],

    '/crm/home' => [
        'controller' => 'Crm@indexAction'
    ],

    '/auth/login' => [
        'controller' => 'Auth@indexAction'
    ],

    '/auth/logout' => [
        'controller' => 'Auth@logoutAction'
    ],

    '/register' => [
        'controller' => 'Register@indexAction'
    ],

    '/crm/photo/add' => [
        'controller' => 'Photo@addAction'
    ],

    '/crm/photo/save' => [
        'controller' => 'Photo@saveAction'
    ],

    '/crm/photo/edit/(:var)' => [
        'controller' => 'Photo@editAction'
    ],

    '/crm/photo/delete/(:var)' => [
        'controller' => 'Photo@deleteAction'
    ],

    '/crm/facility' => [
        'controller' => 'Facility:Grid@indexAction'
    ],

    '/crm/facility/checklist' => [
        'controller' => 'Facility:Grid@checklistAction'
    ],

    '/crm/facility/category/view/(:var)' => [
        'controller' => 'Facility:Grid@categoryAction'
    ],

    // Categories
    '/crm/facility/category/add' => [
        'controller' => 'Facility:Category@addAction'
    ],

    '/crm/facility/category/save' => [
        'controller' => 'Facility:Category@saveAction'
    ],

    '/crm/facility/category/edit/(:var)' => [
        'controller' => 'Facility:Category@editAction'
    ],

    '/crm/facility/category/delete/(:var)' => [
        'controller' => 'Facility:Category@deleteAction'
    ],

    // Item
    '/crm/facility/item/add' => [
        'controller' => 'Facility:Item@addAction'
    ],

    '/crm/facility/item/save' => [
        'controller' => 'Facility:Item@saveAction'
    ],

    '/crm/facility/item/edit/(:var)' => [
        'controller' => 'Facility:Item@editAction'
    ],

    '/crm/facility/item/delete/(:var)' => [
        'controller' => 'Facility:Item@deleteAction'
    ],

    '/crm/transaction/index/(:var)' => [
        'controller' => 'Transaction@indexAction'
    ],

    '/crm/hotel' => [
        'controller' => 'Hotel@indexAction'
    ],

    '/crm/hotel/save' => [
        'controller' => 'Hotel@saveAction'
    ],

    '/crm/services' => [
        'controller' => 'Service@indexAction'
    ],
    
    '/crm/services/edit/(:var)' => [
        'controller' => 'Service@editAction'
    ],

    '/crm/services/delete/(:var)' => [
        'controller' => 'Service@deleteAction'
    ],

    '/crm/services/save' => [
        'controller' => 'Service@saveAction'
    ],

    // Inventory
    '/crm/inventory' => [
        'controller' => 'Inventory@indexAction'
    ],

    '/crm/inventory/edit/(:var)' => [
        'controller' => 'Inventory@editAction'
    ],

    '/crm/inventory/delete/(:var)' => [
        'controller' => 'Inventory@deleteAction'
    ],
    
    '/crm/inventory/save' => [
        'controller' => 'Inventory@saveAction'
    ],

    '/crm/reservation/table/(:var)' => [
        'controller' => 'Reservation@tableAction'
    ],

    '/crm/reservation/find' => [
        'controller' => 'Reservation@findAction'
    ],
    
    '/crm/reservation/table/taken/(:var)' => [
        'controller' => 'Reservation@viewTakenAction'
    ],

    // Reservation add
    '/crm/reservation/add/(:var)' => [
        'controller' => 'Reservation@addAction'
    ],

    '/crm/reservation/save' => [
        'controller' => 'Reservation@saveAction'
    ],

    '/crm/reservation/view/(:var)' => [
        'controller' => 'Reservation@viewAction'
    ],

    '/crm/reservation/print/(:var)' => [
        'controller' => 'Reservation@printAction'
    ],

    '/crm/reservation/index/(:var)' => [
        'controller' => 'Reservation@indexAction'
    ],

    '/crm/reservation/edit/(:var)' => [
        'controller' => 'Reservation@editAction'
    ],
    
    '/crm/reservation/delete/(:var)' => [
        'controller' => 'Reservation@deleteAction'
    ],

    '/crm/reservation/history/room/(:var)' => [
        'controller' => 'Reservation@historyAction'
    ],

    // Room cleaning
    '/crm/architecture/room-cleaning' => [
        'controller' => 'Architecture:RoomCleaning@indexAction'
    ],

    '/crm/architecture/room-cleaning/room/(:var)/mark/(:var)' => [
        'controller' => 'Architecture:RoomCleaning@markAction'
    ],

    '/crm/architecture/room-cleaning/mark-batch/(:var)' => [
        'controller' => 'Architecture:RoomCleaning@markBatchAction'
    ],

    '/crm/architecture/room-type' => [
        'controller' => 'Architecture:RoomType@indexAction'
    ],

    '/crm/architecture/room-type/edit/(:var)' => [
        'controller' => 'Architecture:RoomType@editAction'
    ],

    '/crm/architecture/room-type/delete/(:var)' => [
        'controller' => 'Architecture:RoomType@deleteAction'
    ],

    '/crm/architecture/room-type/save' => [
        'controller' => 'Architecture:RoomType@saveAction'
    ],

    '/crm/architecture' => [
        'controller' => 'Architecture:Grid@indexAction'
    ],

    '/crm/architecture/view/(:var)' => [
        'controller' => 'Architecture:Grid@floorAction'
    ],

    '/crm/architecture/room/add' => [
        'controller' => 'Architecture:Room@addAction'
    ],

    '/crm/architecture/room/view/(:var)' => [
        'controller' => 'Architecture:Room@viewAction'
    ],

    '/crm/architecture/room/save' => [
        'controller' => 'Architecture:Room@saveAction'
    ],

    '/crm/architecture/room/edit/(:var)' => [
        'controller' => 'Architecture:Room@editAction'
    ],

    '/crm/architecture/room/delete/(:var)' => [
        'controller' => 'Architecture:Room@deleteAction'
    ],

    // Room inventory
    '/crm/architecture/room/(:var)/inventory' => [
        'controller' => 'Architecture:RoomInventory@indexAction'
    ],

    '/crm/architecture/room/(:var)/inventory/edit/(:var)' => [
        'controller' => 'Architecture:RoomInventory@editAction'
    ],

    '/crm/architecture/room/inventory/save' => [
        'controller' => 'Architecture:RoomInventory@saveAction'
    ],

    '/crm/architecture/room/(:var)/inventory/delete/(:var)' => [
        'controller' => 'Architecture:RoomInventory@deleteAction'
    ],

    '/crm/architecture/floor/add' => [
        'controller' => 'Architecture:Floor@addAction'
    ],

    '/crm/architecture/floor/save' => [
        'controller' => 'Architecture:Floor@saveAction'
    ],

    '/crm/architecture/floor/edit/(:var)' => [
        'controller' => 'Architecture:Floor@editAction'
    ],

    '/crm/architecture/floor/delete/(:var)' => [
        'controller' => 'Architecture:Floor@deleteAction'
    ],

    '/crm/stat' => [
        'controller' => 'Stat@indexAction'
    ]
];
