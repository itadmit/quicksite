<?php
class ButtonComponent {
    public static function render($content, $style) {
        return [
            'type' => 'button',
            'content' => $content ?? 'לחץ כאן',
            'style' => array_merge([
                'fontFamily' => 'Heebo, sans-serif',
                'fontSize' => '16px',
                'color' => '#ffffff',
                'backgroundColor' => '#4F46E5',
                'padding' => '12px 24px',
                'margin' => '10',
                'borderRadius' => '8px',
                'border' => 'none',
                'cursor' => 'pointer',
                'textAlign' => 'center',
                'display' => 'inline-block',
                'textDecoration' => 'none',
                'transition' => 'all 0.3s ease'
            ], $style ?? []),
            'link' => '#',
            'target' => '_self'
        ];
    }

    public static function getDefaultProps() {
        return [
            'icon' => 'ri-button-line',
            'label' => 'כפתור',
            'category' => 'בסיסי',
            'settings' => [
                'content' => [
                    'type' => 'text',
                    'label' => 'טקסט'
                ],
                'link' => [
                    'type' => 'text',
                    'label' => 'קישור'
                ],
                'target' => [
                    'type' => 'select',
                    'label' => 'פתיחת קישור',
                    'options' => [
                        '_self' => 'באותו חלון',
                        '_blank' => 'בחלון חדש'
                    ]
                ],
                'style' => [
                    'type' => 'select',
                    'label' => 'סגנון',
                    'options' => [
                        'primary' => 'ראשי',
                        'secondary' => 'משני',
                        'outline' => 'מתאר',
                        'text' => 'טקסט'
                    ]
                ],
                'size' => [
                    'type' => 'select',
                    'label' => 'גודל',
                    'options' => [
                        'small' => 'קטן',
                        'medium' => 'בינוני',
                        'large' => 'גדול'
                    ]
                ],
                'fullWidth' => [
                    'type' => 'boolean',
                    'label' => 'רוחב מלא'
                ]
            ]
        ];
    }
}
