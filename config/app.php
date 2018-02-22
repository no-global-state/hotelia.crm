<?php

/**
 * This file is part of the Krystal Framework
 * 
 * Copyright (c) No Global State Lab
 * 
 * For the full copyright and license information, please view
 * the license file that was distributed with this source code.
 */

return array(

    'production' => false,
    'timezone' => 'UTC',

    /**
     * Framework components configuration
     */
    'components' => array(
        /**
         * Cookie salt
         */
        'cookie' => array(
            'salt' => $_ENV['cookie_salt']
        ),

        /**
         * CAPTCHA configuration
         */
        'captcha' => array(
            'type' => 'standard',
            'options' => array(
                'font' => 'Duality.ttf',
                'text' => 'math'
                // Default options can be overridden here
            )
        ),

        /**
         * Session component
         */
        'session' => array(
            'handler' => 'native',
            'cookie_params' => array(
                // Session cookie parameters can be set set
            )
        ),

        /**
         * Configuration service
         */
        'config' => array(
            'adapter' => 'sql',
            'options' => array(
                'connection' => 'mysql',
                'table' => 'config'
            )
        ),

        /**
         * Cache component
         */
        'cache' => array(
            // By default setting up file-based caching engine
            'engine' => 'file',
            'options' => array(
                'file' => 'data/cache/cache.data'
            ),
        ),

		/**
         * Configuration for view manager
         */
        'view' => array(
            'theme' => 'default',
            'plugins' => array(
                // Day pilot
                'daypilot' => [
                    'scripts' => [
                        '@Site/daypilot/js/daypilot-all.min.js',
                        '@Site/daypilot-handler.js',
                    ],
                    'stylesheets' => [
						'@Site/font-awesome/css/font-awesome.min.css'
                    ]
                ],
                
                // Map
                'map' => [
                    'scripts' => [
                        sprintf('https://maps.googleapis.com/maps/api/js?key=%s', $_ENV['map']['apiKey']),
						'@Site/map.js',
                    ]
                ],
                
                // Font awesome
                'font-awesome' => [
					'stylesheets' => array(
						'@Site/font-awesome/css/font-awesome.min.css'
					)
                ],

				// Datetime picker
				'datetimepicker' => array(
					'scripts' => array(
						'@Site/datetimepicker/js/moment-with-locales.min.js',
						'@Site/datetimepicker/js/bootstrap-datetimepicker.min.js'
					),
					'stylesheets' => array(
						'@Site/datetimepicker/css/bootstrap-datetimepicker.min.css'
					)
				),
				// Improved plugin for dropdowns
				'chosen' => array(
					'stylesheets' => array(
						'@Site/chosen/chosen.css',
						'@Site/chosen/chosen-bootstrap.css'
					),
					'scripts' => array(
						'@Site/chosen/chosen.jquery.min.js'
					)
				),
                // Chart.js library
                'chart' => array(
					'scripts' => array(
						'@Site/chart/chart.bundle.js',
                        '@Site/chart/utils.js',
					)
				)
			)
        ),

        /**
         * Translator configuration
         */
        'translator' => array(
            // Default language
            'default' => 'en',
        ),

        /**
         * Param bag which holds application-level parameters
         * This values can be accessed in controllers, like $this->paramBag->get(..key..)
         */
        'paramBag' => $_ENV,

        /**
         * Router configuration
         */
        'router' => array(
            'default' => 'Site:Site@notFoundAction',
        ),

        /**
         * Form validation component. It has two options only
         */
        'validator' => array(
            'translate' => true,
            'render' => 'MessagesOnly',
        ),

        /**
         * Database component provider
         * It needs to be configured here and accessed in mappers
         */
        'db' => array(
            'mysql' => $_ENV['mysql']
        ),

        /**
         * MapperFactory which relies on previous db section
         */
        'mapperFactory' => array(
            'connection' => 'mysql'
        ),

        /**
         * Pagination component used in data mappers. 
         */
        'paginator' => array(
            'style' => 'Digg',
        )
    )
);
