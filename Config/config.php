<?php

return [
    'name'        => 'MauticCustomContactBundle',
    'description' => 'Custom contact for Mautic',
    'author'      => 'ET Team',
    'version'     => '0.0.1',
    // 'routes'      => [
    //     'main' => [
    //         'mautic_plugin_custom_column' => [
    //             'path'       => '/custom/column',
    //             'controller' => 'MauticCustomContactBundle:CustomContact:show',
    //         ],
    //         'mautic_plugin_custom_column_save' => [
    //             'path'       => '/custom-column/saveconfig',
    //             'controller' => 'MauticCustomContactBundle:CustomContact:saveConfig',
    //         ],
    //     ],
    // ],
    'services'    => [
        'events' => [
            'mautic.plugin.custom.contact.subscriber' => [
                'class'     => \MauticPlugin\MauticCustomContactBundle\EventListener\CustomContactSubscriber::class,
                'arguments' => [
                    'mautic.helper.integration',
                    'mautic.section.helper'
                ],
            ],
           
        ],
        'forms'   => [
        ],
        'command' => [
        ],
        'other'   => [
        ],

        'helpers'      => [
            'mautic.section.helper' => [
                'class'     => \MauticPlugin\MauticCustomContactBundle\Helper\SectionHelper::class,
                'arguments' => [
                    'mautic.helper.templating',
                    'mautic.helper.user',
                    'mautic.security',
                    'mautic.helper.integration',
                    'mautic.helper.core_parameters',
                    'mautic.lead.model.lead',
                    'mautic.helper.template.avatar'
                ],
            ],
        ],
        'models'       => [],
        'integrations' => [
            'mautic.integration.custom_contact' => [
                'class'     => \MauticPlugin\MauticCustomContactBundle\Integration\CustomContactIntegration::class,
                'arguments' => [
                    'mautic.factory',
                    'mautic.configurator',
                    'mautic.helper.cache',
                ],
            ],
        ],
    ],
    'parameters' => [
    ],
];
