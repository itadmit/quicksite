<?php
class TestimonialComponent {
    public static function render($content = [], $style = []) {
        return [
            'type' => 'testimonial',
            'content' => array_merge([
                'name' => 'שם הממליץ',
                'role' => 'תפקיד',
                'company' => 'חברה',
                'text' => 'תוכן ההמלצה',
                'image' => '',
                'rating' => 5
            ], $content),
            'style' => array_merge([
                'backgroundColor' => '#ffffff',
                'padding' => '20',
                'margin' => '10',
                'borderRadius' => '8px',
                'boxShadow' => '0 2px 4px rgba(0,0,0,0.1)',
                'textAlign' => 'right'
            ], $style ?? [])
        ];
    }

    public static function getDefaultProps() {
        return [
            'icon' => 'ri-chat-quote-line',
            'label' => 'המלצה',
            'category' => 'תוכן',
            'settings' => [
                'name' => [
                    'type' => 'text',
                    'label' => 'שם הממליץ'
                ],
                'role' => [
                    'type' => 'text',
                    'label' => 'תפקיד'
                ],
                'company' => [
                    'type' => 'text',
                    'label' => 'חברה'
                ],
                'text' => [
                    'type' => 'textarea',
                    'label' => 'תוכן ההמלצה'
                ],
                'image' => [
                    'type' => 'image',
                    'label' => 'תמונת הממליץ'
                ],
                'rating' => [
                    'type' => 'number',
                    'label' => 'דירוג',
                    'min' => 1,
                    'max' => 5
                ],
                'style' => [
                    'type' => 'select',
                    'label' => 'סגנון',
                    'options' => [
                        'card' => 'כרטיס',
                        'minimal' => 'מינימלי',
                        'quote' => 'ציטוט'
                    ]
                ]
            ]
        ];
    }
} 