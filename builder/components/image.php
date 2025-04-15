<?php
class ImageComponent {
    public static function render($content = '', $style = []) {
        return [
            'type' => 'image',
            'content' => $content,
            'style' => array_merge([
                'width' => '100%',
                'maxWidth' => '100%',
                'height' => 'auto',
                'objectFit' => 'cover',
                'borderRadius' => '0',
                'margin' => '10'
            ], $style ?? []),
            'settings' => [
                'alt' => '',
                'link' => '',
                'target' => '_self'
            ]
        ];
    }

    public static function getDefaultProps() {
        return [
            'icon' => 'ri-image-line',
            'label' => 'תמונה',
            'category' => 'מדיה',
            'settings' => [
                'src' => [
                    'type' => 'image',
                    'label' => 'בחר תמונה'
                ],
                'alt' => [
                    'type' => 'text',
                    'label' => 'טקסט חלופי'
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
                'width' => [
                    'type' => 'text',
                    'label' => 'רוחב'
                ],
                'height' => [
                    'type' => 'text',
                    'label' => 'גובה'
                ],
                'objectFit' => [
                    'type' => 'select',
                    'label' => 'מילוי',
                    'options' => [
                        'cover' => 'כיסוי',
                        'contain' => 'הכלה',
                        'fill' => 'מילוי',
                        'none' => 'ללא'
                    ]
                ]
            ]
        ];
    }
}
