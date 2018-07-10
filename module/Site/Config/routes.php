<?php

use Site\Service\UserService;

return [

    // Meals
    '/crm/meals' => [
        'controller' => 'Meals@indexAction'
    ],
    
    '/crm/meals/add' => [
        'controller' => 'Meals@addAction'
    ],

    '/crm/meals/edit/(:var)' => [
        'controller' => 'Meals@editAction'
    ],
    
    '/crm/meals/delete/(:var)' => [
        'controller' => 'Meals@deleteAction'
    ],
    
    '/crm/meals/save' => [
        'controller' => 'Meals@saveAction'
    ],
    
    '/state/price-group/(:var)' => [
        'controller' => 'Site@priceGroupAction',
    ],

    '/state/language/(:var)' => [
        'controller' => 'Site@languageAction',
    ],
    
    // Dictionary
    '/crm/dictionary' => [
        'controller' => 'Dictionary@indexAction'
    ],
    
    '/crm/dictionary/add' => [
        'controller' => 'Dictionary@addAction'
    ],
    
    '/crm/dictionary/edit/(:var)' => [
        'controller' => 'Dictionary@editAction'
    ],

    '/crm/dictionary/delete/(:var)' => [
        'controller' => 'Dictionary@deleteAction'
    ],
    
    '/crm/dictionary/save' => [
        'controller' => 'Dictionary@saveAction'
    ],

    // District
    '/crm/district' => [
        'controller' => 'District@indexAction'
    ],

    '/crm/district/edit/(:var)' => [
        'controller' => 'District@editAction'
    ],

    '/crm/district/delete/(:var)' => [
        'controller' => 'District@deleteAction'
    ],

    '/crm/district/add' => [
        'controller' => 'District@addAction'
    ],

    '/crm/district/save' => [
        'controller' => 'District@saveAction'
    ],

    // Room category
    '/crm/room-category' => [
        'controller' => 'RoomCategory@indexAction'
    ],

    '/crm/room-category/add' => [
        'controller' => 'RoomCategory@addAction'
    ],

    '/crm/room-category/save' => [
        'controller' => 'RoomCategory@saveAction'
    ],

    '/crm/room-category/edit/(:var)' => [
        'controller' => 'RoomCategory@editAction'
    ],
    
    '/crm/room-category/delete/(:var)' => [
        'controller' => 'RoomCategory@deleteAction'
    ],

    // Hotel switch
    '/crm/hotel-switch/(:var)' => [
        'controller' => 'Crm@hotelSwitchAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],
    
    // User settings
    '/user/change-password' => [
        'controller' => 'Settings@changePasswordAction'
    ],

    // Hotel types
    '/crm/hotel-type' => [
        'controller' => 'HotelType@indexAction'
    ],

    '/crm/hotel-type/add' => [
        'controller' => 'HotelType@addAction'
    ],

    '/crm/hotel-type/edit/(:var)' => [
        'controller' => 'HotelType@editAction'
    ],

    '/crm/hotel-type/delete/(:var)' => [
        'controller' => 'HotelType@deleteAction'
    ],

    '/crm/hotel-type/save' => [
        'controller' => 'HotelType@saveAction'
    ],

    // Wizard
    '/crm/wizard' => [
        'controller' => 'Wizard@indexAction'
    ],

    // Payment system field
    '/crm/payment-system/fields/all/(:var)' => [
        'controller' => 'PaymentField@indexAction'
    ],

    '/crm/payment-system/fields/edit/(:var)' => [
        'controller' => 'PaymentField@editAction'
    ],

    '/crm/payment-system/fields/delete/(:var)' => [
        'controller' => 'PaymentField@deleteAction'
    ],

    '/crm/payment-system/fields/save' => [
        'controller' => 'PaymentField@saveAction'
    ],

    // Payment system
    '/crm/payment-system' => [
        'controller' => 'PaymentSystem@indexAction'
    ],

    '/crm/payment-system/save' => [
        'controller' => 'PaymentSystem@saveAction'
    ],

    '/crm/payment-system/delete/(:var)' => [
        'controller' => 'PaymentSystem@deleteAction'
    ],

    '/crm/payment-system/edit/(:var)' => [
        'controller' => 'PaymentSystem@editAction'
    ],
    
    // Discounts
    '/crm/discount' => [
        'controller' => 'Discount@indexAction'
    ],
    
    '/crm/discount/save' => [
        'controller' => 'Discount@saveAction',
        'disallow' => [UserService::USER_ROLE_GUEST]
    ],

    '/crm/discount/delete/(:var)' => [
        'controller' => 'Discount@deleteAction',
        'disallow' => [UserService::USER_ROLE_GUEST]
    ],
    
    '/crm/discount/edit/(:var)' => [
        'controller' => 'Discount@editAction'
    ],

    // API
    '/api/available/(:var)' => [
        'controller' => 'Api@available'
    ],

    '/api/all/(:var)' => [
        'controller' => 'Api@all'
    ],
    
    '/api/detail/(:var)' => [
        'controller' => 'Api@details'
    ],

    '/api/register' => [
        'controller' => 'Api@register'
    ],
    
    // Returns filter data
    '/api/get-filter/(:var)' => [
        'controller' => 'Api@getFilter'
    ],

    // Perform filtering
    '/api/filter' => [
        'controller' => 'Api@filter'
    ],

    // Property
    '/crm/property/(:var)' => [
        'controller' => 'Property@indexAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],
    
    '/crm/property/do/tweak' => [
        'controller' => 'Property@tweakAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],

    // Price groups
    '/crm/price-groups' => [
        'controller' => 'PriceGroup@indexAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],

    '/crm/price-groups/edit/(:var)' => [
        'controller' => 'PriceGroup@editAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],

    '/crm/price-groups/delete/(:var)' => [
        'controller' => 'PriceGroup@deleteAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],

    '/crm/price-groups/save' => [
        'controller' => 'PriceGroup@saveAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],
    
    // Room gallery
    '/crm/architecture/room/gallery/index/(:var)' => [
        'controller' => 'Architecture:RoomTypeGallery@indexAction'
    ],

    '/crm/architecture/room/gallery/add/(:var)' => [
        'controller' => 'Architecture:RoomTypeGallery@addAction'
    ],

    '/crm/architecture/room/gallery/edit/(:var)' => [
        'controller' => 'Architecture:RoomTypeGallery@editAction'
    ],

    '/crm/architecture/room/gallery/delete/(:var)' => [
        'controller' => 'Architecture:RoomTypeGallery@deleteAction'
    ],

    '/crm/architecture/room/gallery/save' => [
        'controller' => 'Architecture:RoomTypeGallery@saveAction'
    ],

    '/crm/architecture/room/gallery/tweak' => [
        'controller' => 'Architecture:RoomTypeGallery@tweakAction'
    ],
    
    // Regions
    '/crm/regions' => [
        'controller' => 'Region@indexAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],

    '/crm/regions/view/(:var)' => [
        'controller' => 'Region@districtAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],

    '/crm/regions/edit/(:var)' => [
        'controller' => 'Region@editAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],
    '/crm/regions/delete/(:var)' => [
        'controller' => 'Region@deleteAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],
    '/crm/regions/add' => [
        'controller' => 'Region@addAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],
    '/crm/regions/save' => [
        'controller' => 'Region@saveAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],

    // Reviews
    '/crm/reviews' => [
        'controller' => 'Review:Review@indexAction',
    ],
    '/crm/reviews/edit/(:var)' => [
        'controller' => 'Review:Review@editAction',
        'disallow' => [UserService::USER_ROLE_GUEST]
    ],
    '/crm/reviews/delete/(:var)' => [
        'controller' => 'Review:Review@deleteAction',
        'disallow' => [UserService::USER_ROLE_GUEST]
    ],
    '/crm/reviews/save' => [
        'controller' => 'Review:Review@saveAction',
        'disallow' => [UserService::USER_ROLE_GUEST]
    ],
    
    // Review types
    '/crm/review-type' => [
        'controller' => 'Review:ReviewType@indexAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],
    
    '/crm/review-type/add' => [
        'controller' => 'Review:ReviewType@addAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],

    '/crm/review-type/edit/(:var)' => [
        'controller' => 'Review:ReviewType@editAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],

    '/crm/review-type/delete/(:var)' => [
        'controller' => 'Review:ReviewType@deleteAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],
    
    '/crm/review-type/save' => [
        'controller' => 'Review:ReviewType@saveAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],
    
    '/crm/feedback' => [
        'controller' => 'Feedback@indexAction'
    ],
    
    '/crm/feedback/submit' => [
        'controller' => 'Feedback@submitAction',
        'disallow' => [UserService::USER_ROLE_GUEST]
    ],
    
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
        'controller' => 'Language@indexAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],
    
    '/crm/languages/save' => [
        'controller' => 'Language@saveAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],

    '/crm/languages/edit/(:var)' => [
        'controller' => 'Language@editAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],
    
    '/crm/languages/delete/(:var)' => [
        'controller' => 'Language@deleteAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],
    
    '/payment/(:var)' => [
        'controller' => 'Site@paymentAction'
    ],

    '/calculate/(:var)' => [
        'controller' => 'Site@calculate'
    ],

    '/search/(:var)' => [
        'controller' => 'Site@searchAction'
    ],
    
    '/hotel/review/add' => [
        'controller' => 'Site@reviewAction'
    ],
    
    '/hotel/book/(:var)' => [
        'controller' => 'Site@bookAction'
    ],

    '/hotel/(:var)' => [
        'controller' => 'Site@hotelAction'
    ],

    '/hotel/delete/(:var)' => [
        'controller' => 'Hotel@deleteAction'
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
        'controller' => 'Photo@saveAction',
        'disallow' => [UserService::USER_ROLE_GUEST],
    ],

    '/crm/photo/edit/(:var)' => [
        'controller' => 'Photo@editAction'
    ],

    '/crm/photo/delete/(:var)' => [
        'controller' => 'Photo@deleteAction',
        'disallow' => [UserService::USER_ROLE_GUEST],
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

    // Item data
    '/crm/facility/data/index/(:var)' => [
        'controller' => 'Facility:ItemData@indexAction'
    ],

    '/crm/facility/data/add/(:var)' => [
        'controller' => 'Facility:ItemData@addAction'
    ],

    '/crm/facility/data/edit/item/(:var)/(:var)' => [
        'controller' => 'Facility:ItemData@editAction'
    ],

    '/crm/facility/data/delete/(:var)' => [
        'controller' => 'Facility:ItemData@deleteAction'
    ],

    '/crm/facility/data/save' => [
        'controller' => 'Facility:ItemData@saveAction'
    ],

    // Categories
    '/crm/facility/category/add' => [
        'controller' => 'Facility:Category@addAction'
    ],

    '/crm/facility/category/save' => [
        'controller' => 'Facility:Category@saveAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],

    '/crm/facility/category/edit/(:var)' => [
        'controller' => 'Facility:Category@editAction'
    ],

    '/crm/facility/category/delete/(:var)' => [
        'controller' => 'Facility:Category@deleteAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],

    // Item
    '/crm/facility/item/add' => [
        'controller' => 'Facility:Item@addAction'
    ],

    '/crm/facility/item/save' => [
        'controller' => 'Facility:Item@saveAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],

    '/crm/facility/item/edit/(:var)' => [
        'controller' => 'Facility:Item@editAction'
    ],

    '/crm/facility/item/delete/(:var)' => [
        'controller' => 'Facility:Item@deleteAction',
        'disallow' => [UserService::USER_ROLE_GUEST, UserService::USER_ROLE_USER]
    ],

    '/crm/transaction/index/(:var)' => [
        'controller' => 'Transaction@indexAction'
    ],

    '/crm/hotel' => [
        'controller' => 'Hotel@indexAction'
    ],

    '/crm/hotel/save' => [
        'controller' => 'Hotel@saveAction',
        'disallow' => [UserService::USER_ROLE_GUEST],
    ],

    '/crm/services' => [
        'controller' => 'Service@indexAction'
    ],
    
    '/crm/services/edit/(:var)' => [
        'controller' => 'Service@editAction'
    ],

    '/crm/services/delete/(:var)' => [
        'controller' => 'Service@deleteAction',
        'disallow' => [UserService::USER_ROLE_GUEST],
    ],

    '/crm/services/save' => [
        'controller' => 'Service@saveAction',
        'disallow' => [UserService::USER_ROLE_GUEST],
    ],

    // Inventory
    '/crm/inventory' => [
        'controller' => 'Inventory@indexAction'
    ],

    '/crm/inventory/edit/(:var)' => [
        'controller' => 'Inventory@editAction'
    ],

    '/crm/inventory/delete/(:var)' => [
        'controller' => 'Inventory@deleteAction',
        'disallow' => [UserService::USER_ROLE_GUEST],
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

    // Reservation services
    '/crm/reservation/services/(:var)' => [
        'controller' => 'ReservationService@indexAction'
    ],
    
    '/crm/reservation/services/do/save' => [
        'controller' => 'ReservationService@saveAction'
    ],
    
    '/crm/reservation/services/do/edit/(:var)' => [
        'controller' => 'ReservationService@editAction'
    ],

    '/crm/reservation/services/do/delete/reservation/(:var)/(:var)' => [
        'controller' => 'ReservationService@deleteAction'
    ],
    
    '/crm/reservation/table/taken/(:var)' => [
        'controller' => 'Reservation@viewTakenAction'
    ],

    // Reservation add
    '/crm/reservation/add/(:var)' => [
        'controller' => 'Reservation@addAction'
    ],

    '/crm/reservation/save' => [
        'controller' => 'Reservation@saveAction',
        'disallow' => [UserService::USER_ROLE_GUEST],
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
        'controller' => 'Reservation@deleteAction',
        'disallow' => [UserService::USER_ROLE_GUEST],
    ],

    '/crm/reservation/history/room/(:var)' => [
        'controller' => 'Reservation@historyAction'
    ],

    // Room cleaning
    '/crm/architecture/room-cleaning' => [
        'controller' => 'Architecture:RoomCleaning@indexAction'
    ],

    '/crm/architecture/room-cleaning/room/(:var)/mark/(:var)' => [
        'controller' => 'Architecture:RoomCleaning@markAction',
        'disallow' => [UserService::USER_ROLE_GUEST],
    ],

    '/crm/architecture/room-cleaning/mark-batch/(:var)' => [
        'controller' => 'Architecture:RoomCleaning@markBatchAction',
        'disallow' => [UserService::USER_ROLE_GUEST],
    ],

    // Room bed
    '/crm/room-bed' => [
        'controller' => 'RoomBed@indexAction'
    ],
    
    '/crm/room-bed/add' => [
        'controller' => 'RoomBed@addAction'
    ],
    
    '/crm/room-bed/edit/(:var)' => [
        'controller' => 'RoomBed@editAction'
    ],

    '/crm/room-bed/delete/(:var)' => [
        'controller' => 'RoomBed@deleteAction'
    ],

    '/crm/room-bed/save' => [
        'controller' => 'RoomBed@saveAction'
    ],

    '/crm/architecture/room-type' => [
        'controller' => 'Architecture:RoomType@indexAction'
    ],

    '/crm/architecture/room-type/add' => [
        'controller' => 'Architecture:RoomType@addAction'
    ],

    '/crm/architecture/room-type/edit/(:var)' => [
        'controller' => 'Architecture:RoomType@editAction'
    ],

    '/crm/architecture/room-type/delete/(:var)' => [
        'controller' => 'Architecture:RoomType@deleteAction',
        'disallow' => [UserService::USER_ROLE_GUEST],
    ],

    '/crm/architecture/room-type/save' => [
        'controller' => 'Architecture:RoomType@saveAction',
        'disallow' => [UserService::USER_ROLE_GUEST],
    ],

    '/crm/architecture' => [
        'controller' => 'Architecture:Room@indexAction'
    ],

    '/crm/architecture/room/add' => [
        'controller' => 'Architecture:Room@addAction'
    ],

    '/crm/architecture/room/view/(:var)' => [
        'controller' => 'Architecture:Room@viewAction'
    ],

    '/crm/architecture/room/save' => [
        'controller' => 'Architecture:Room@saveAction',
        'disallow' => [UserService::USER_ROLE_GUEST],
    ],

    '/crm/architecture/room/edit/(:var)' => [
        'controller' => 'Architecture:Room@editAction'
    ],

    '/crm/architecture/room/delete/(:var)' => [
        'controller' => 'Architecture:Room@deleteAction',
        'disallow' => [UserService::USER_ROLE_GUEST],
    ],

    // Room inventory
    '/crm/architecture/room/(:var)/inventory' => [
        'controller' => 'Architecture:RoomInventory@indexAction'
    ],

    '/crm/architecture/room/(:var)/inventory/edit/(:var)' => [
        'controller' => 'Architecture:RoomInventory@editAction'
    ],

    '/crm/architecture/room/inventory/save' => [
        'controller' => 'Architecture:RoomInventory@saveAction',
        'disallow' => [UserService::USER_ROLE_GUEST],
    ],

    '/crm/architecture/room/(:var)/inventory/delete/(:var)' => [
        'controller' => 'Architecture:RoomInventory@deleteAction'
    ],

    '/crm/stat' => [
        'controller' => 'Stat@indexAction'
    ]
];
