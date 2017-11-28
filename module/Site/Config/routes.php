<?php

return array(

    '/' => array(
        'controller' => 'Site@homeAction'
    ),

    '/crm/languages/switch/(:var)' => array(
        'controller' => 'Language@switchAction'
    ),
    
    '/crm/languages' => array(
        'controller' => 'Language@indexAction'
    ),
    
    '/crm/languages/save' => array(
        'controller' => 'Language@saveAction'
    ),

    '/crm/languages/edit/(:var)' => array(
        'controller' => 'Language@editAction'
    ),
    
    '/crm/languages/delete/(:var)' => array(
        'controller' => 'Language@deleteAction'
    ),
    
    '/payment' => array(
        'controller' => 'Site@paymentAction'
    ),
    
    '/search/(:var)' => array(
        'controller' => 'Site@searchAction'
    ),
    
    '/hotel/book/(:var)' => array(
        'controller' => 'Site@bookAction'
    ),

    '/hotel/(:var)' => array(
        'controller' => 'Site@hotelAction'
    ),
    
    '/site/captcha/(:var)' => array(
        'controller' => 'Site@captchaAction'
    ),

    '/crm/home' => array(
        'controller' => 'Crm@indexAction'
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

    '/crm/photo/add' => array(
        'controller' => 'Photo@addAction'
    ),

    '/crm/photo/save' => array(
        'controller' => 'Photo@saveAction'
    ),

    '/crm/photo/edit/(:var)' => array(
        'controller' => 'Photo@editAction'
    ),

    '/crm/photo/delete/(:var)' => array(
        'controller' => 'Photo@deleteAction'
    ),

    '/crm/facility' => array(
        'controller' => 'Facility:Grid@indexAction'
    ),

    '/crm/facility/checklist' => array(
        'controller' => 'Facility:Grid@checklistAction'
    ),

    '/crm/facility/category/view/(:var)' => array(
        'controller' => 'Facility:Grid@categoryAction'
    ),

    // Categories
    '/crm/facility/category/add' => array(
        'controller' => 'Facility:Category@addAction'
    ),

    '/crm/facility/category/save' => array(
        'controller' => 'Facility:Category@saveAction'
    ),

    '/crm/facility/category/edit/(:var)' => array(
        'controller' => 'Facility:Category@editAction'
    ),

    '/crm/facility/category/delete/(:var)' => array(
        'controller' => 'Facility:Category@deleteAction'
    ),

    // Item
    '/crm/facility/item/add' => array(
        'controller' => 'Facility:Item@addAction'
    ),

    '/crm/facility/item/save' => array(
        'controller' => 'Facility:Item@saveAction'
    ),

    '/crm/facility/item/edit/(:var)' => array(
        'controller' => 'Facility:Item@editAction'
    ),

    '/crm/facility/item/delete/(:var)' => array(
        'controller' => 'Facility:Item@deleteAction'
    ),

    '/crm/transaction/index/(:var)' => array(
        'controller' => 'Transaction@indexAction'
    ),

    '/crm/hotel' => array(
        'controller' => 'Hotel@indexAction'
    ),

    '/crm/hotel/save' => array(
        'controller' => 'Hotel@saveAction'
    ),

    '/crm/services' => array(
        'controller' => 'Service@indexAction'
    ),
    
    '/crm/services/edit/(:var)' => array(
        'controller' => 'Service@editAction'
    ),

    '/crm/services/delete/(:var)' => array(
        'controller' => 'Service@deleteAction'
    ),

    '/crm/services/save' => array(
        'controller' => 'Service@saveAction'
    ),

    // Inventory
    '/crm/inventory' => array(
        'controller' => 'Inventory@indexAction'
    ),

    '/crm/inventory/edit/(:var)' => array(
        'controller' => 'Inventory@editAction'
    ),

    '/crm/inventory/delete/(:var)' => array(
        'controller' => 'Inventory@deleteAction'
    ),
    
    '/crm/inventory/save' => array(
        'controller' => 'Inventory@saveAction'
    ),

    '/crm/reservation/table/(:var)' => array(
        'controller' => 'Reservation@tableAction'
    ),

    '/crm/reservation/find' => array(
        'controller' => 'Reservation@findAction'
    ),
    
    '/crm/reservation/table/taken/(:var)' => array(
        'controller' => 'Reservation@viewTakenAction'
    ),

    // Reservation add
    '/crm/reservation/add/(:var)' => array(
        'controller' => 'Reservation@addAction'
    ),

    '/crm/reservation/save' => array(
        'controller' => 'Reservation@saveAction'
    ),

    '/crm/reservation/view/(:var)' => array(
        'controller' => 'Reservation@viewAction'
    ),

    '/crm/reservation/print/(:var)' => array(
        'controller' => 'Reservation@printAction'
    ),

    '/crm/reservation/index/(:var)' => array(
        'controller' => 'Reservation@indexAction'
    ),

    '/crm/reservation/edit/(:var)' => array(
        'controller' => 'Reservation@editAction'
    ),
    
    '/crm/reservation/delete/(:var)' => array(
        'controller' => 'Reservation@deleteAction'
    ),

    '/crm/reservation/history/room/(:var)' => array(
        'controller' => 'Reservation@historyAction'
    ),

    // Room cleaning
    '/crm/architecture/room-cleaning' => array(
        'controller' => 'Architecture:RoomCleaning@indexAction'
    ),

    '/crm/architecture/room-cleaning/room/(:var)/mark/(:var)' => array(
        'controller' => 'Architecture:RoomCleaning@markAction'
    ),

    '/crm/architecture/room-cleaning/mark-batch/(:var)' => array(
        'controller' => 'Architecture:RoomCleaning@markBatchAction'
    ),

    '/crm/architecture/room-type' => array(
        'controller' => 'Architecture:RoomType@indexAction'
    ),

    '/crm/architecture/room-type/edit/(:var)' => array(
        'controller' => 'Architecture:RoomType@editAction'
    ),

    '/crm/architecture/room-type/delete/(:var)' => array(
        'controller' => 'Architecture:RoomType@deleteAction'
    ),

    '/crm/architecture/room-type/save' => array(
        'controller' => 'Architecture:RoomType@saveAction'
    ),

    '/crm/architecture' => array(
        'controller' => 'Architecture:Grid@indexAction'
    ),

    '/crm/architecture/view/(:var)' => array(
        'controller' => 'Architecture:Grid@floorAction'
    ),

    '/crm/architecture/room/add' => array(
        'controller' => 'Architecture:Room@addAction'
    ),

    '/crm/architecture/room/view/(:var)' => array(
        'controller' => 'Architecture:Room@viewAction'
    ),

    '/crm/architecture/room/save' => array(
        'controller' => 'Architecture:Room@saveAction'
    ),

    '/crm/architecture/room/edit/(:var)' => array(
        'controller' => 'Architecture:Room@editAction'
    ),

    '/crm/architecture/room/delete/(:var)' => array(
        'controller' => 'Architecture:Room@deleteAction'
    ),

    // Room inventory
    '/crm/architecture/room/(:var)/inventory' => array(
        'controller' => 'Architecture:RoomInventory@indexAction'
    ),

    '/crm/architecture/room/(:var)/inventory/edit/(:var)' => array(
        'controller' => 'Architecture:RoomInventory@editAction'
    ),

    '/crm/architecture/room/inventory/save' => array(
        'controller' => 'Architecture:RoomInventory@saveAction'
    ),

    '/crm/architecture/room/(:var)/inventory/delete/(:var)' => array(
        'controller' => 'Architecture:RoomInventory@deleteAction'
    ),

    '/crm/architecture/floor/add' => array(
        'controller' => 'Architecture:Floor@addAction'
    ),

    '/crm/architecture/floor/save' => array(
        'controller' => 'Architecture:Floor@saveAction'
    ),

    '/crm/architecture/floor/edit/(:var)' => array(
        'controller' => 'Architecture:Floor@editAction'
    ),

    '/crm/architecture/floor/delete/(:var)' => array(
        'controller' => 'Architecture:Floor@deleteAction'
    ),

    '/crm/stat' => array(
        'controller' => 'Stat@indexAction'
    )
);
