<?php
class CountdownComponent {
    public static function render($content = [], $style = []) {
        return [
            'type' => 'countdown',
            'content' => array_merge([
                'endDate' => date('Y-m-d H:i:s', strtotime('+7 days')),
                'showLabels' => true,
                'labels' => [
                    'days' => 'ימים',
                    'hours' => 'שעות',
                    'minutes' => 'דקות',
                    'seconds' => 'שניות'
                ]
            ], $content),
            'style' => array_merge([
                'backgroundColor' => 'transparent',
                'color' => '#000000',
                'fontSize' => '24px',
                'fontWeight' => 'bold',
                'textAlign' => 'center',
                'padding' => '20',
                'margin' => '10',
                'display' => 'flex',
                'justifyContent' => 'center',
                'gap' => '20px'
            ], $style ?? []),
            'settings' => [
                'onComplete' => [
                    'action' => 'none', // none, hide, redirect
                    'redirectUrl' => ''
                ]
            ]
        ];
    }

    public static function getDefaultProps() {
        return [
            'icon' => 'ri-timer-line',
            'label' => 'שעון ספירה לאחור',
            'category' => 'מתקדם',
            'settings' => [
                'endDate' => [
                    'type' => 'datetime',
                    'label' => 'תאריך יעד'
                ],
                'showLabels' => [
                    'type' => 'boolean',
                    'label' => 'הצג תוויות'
                ],
                'labels' => [
                    'type' => 'group',
                    'label' => 'תוויות',
                    'fields' => [
                        'days' => [
                            'type' => 'text',
                            'label' => 'ימים'
                        ],
                        'hours' => [
                            'type' => 'text',
                            'label' => 'שעות'
                        ],
                        'minutes' => [
                            'type' => 'text',
                            'label' => 'דקות'
                        ],
                        'seconds' => [
                            'type' => 'text',
                            'label' => 'שניות'
                        ]
                    ]
                ],
                'onComplete' => [
                    'type' => 'select',
                    'label' => 'בסיום הספירה',
                    'options' => [
                        'none' => 'ללא פעולה',
                        'hide' => 'הסתר רכיב',
                        'redirect' => 'הפנה לדף אחר'
                    ]
                ],
                'redirectUrl' => [
                    'type' => 'text',
                    'label' => 'כתובת הפניה',
                    'condition' => [
                        'onComplete' => 'redirect'
                    ]
                ],
                'style' => [
                    'type' => 'select',
                    'label' => 'סגנון',
                    'options' => [
                        'default' => 'רגיל',
                        'boxed' => 'קופסאות',
                        'minimal' => 'מינימלי'
                    ]
                ]
            ]
        ];
    }
} 