<?php

// Public routes
return [
    '/state/price-group/(:var)' => [
        'controller' => 'Site@priceGroupAction',
    ],

    '/state/language/(:var)' => [
        'controller' => 'Site@languageAction',
    ],
    
    '/state/currency/(:var)' => [
        'controller' => 'Site@currencyAction',
    ],
    
    '/coupon/(:var)' => [
        'controller' => 'Site@couponAction'
    ],
    
    // Booking cancel
    '/cancel/(:var)' => [
        'controller' => 'Site@cancelAction'
    ],
    
    '/cancel-confirm/(:var)' => [
        'controller' => 'Site@cancelConfirmAction'
    ],
    
    // API
    '/api/payment' => [
        'controller' => 'Api@payment'
    ],
    
    '/api/available/(:var)' => [
        'controller' => 'Api@available'
    ],

    '/api/get-initial/(:var)' => [
        'controller' => 'Api@getInitial'
    ],
    
    '/api/search/(:var)' => [
        'controller' => 'Api@search'
    ],
    
    '/api/hotel/(:var)' => [
        'controller' => 'Api@hotel'
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
    
    '/api/get-bookings/(:var)' => [
        'controller' => 'Api@getBookings'
    ],
    
    '/api/statistic/(:var)' => [
        'controller' => 'Api@statistic'
    ],
    
    '/api/bookings/(:var)' => [
        'controller' => 'Api@bookings'
    ],
    
    '/api/run-mail-receivers' => [
        'controller' => 'Api@receivers'
    ],
    
    '/api/save-external/(:var)' => [
        'controller' => 'Api@saveExternal'
    ],
    
    '/api/thank-you/(:var)' => [
        'controller' => 'Api@thankAction'
    ],
    
    '/api/gateway/(:var)' => [
        'controller' => 'Api@mobileGatewayAction'
    ],
    
    '/' => [
        'controller' => 'Site@homeAction'
    ],
    
    '/home/(:var)' => [
        'controller' => 'Site@homeAction'
    ],
    
    '/invoice/(:var)' => [
        'controller' => 'Site@invoiceAction'
    ],
    
    '/confirm-payment/(:var)' => [
        'controller' => 'Site@confirmPaymentAction'
    ],

    '/reviews/(:var)' => [
        'controller' => 'Site@reviewsAction'
    ],
    
    '/leave-review/(:var)' => [
        'controller' => 'Site@leaveReviewAction'
    ],
    
    '/gateway/(:var)' => [
        'controller' => 'Site@gatewayAction'
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

    '/auth/login' => [
        'controller' => 'Auth@indexAction'
    ],

    '/auth/logout' => [
        'controller' => 'Auth@logoutAction'
    ],

    '/register' => [
        'controller' => 'Register@indexAction'
    ]
];
