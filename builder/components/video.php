<?php
class VideoComponent {
    public static function render($content = '', $style = []) {
        return [
            'type' => 'video',
            'content' => $content,
            'style' => array_merge([
                'width' => '100%',
                'maxWidth' => '100%',
                'height' => 'auto',
                'aspectRatio' => '16/9',
                'margin' => '10'
            ], $style ?? []),
            'settings' => [
                'type' => 'youtube', // youtube, vimeo, file
                'autoplay' => false,
                'controls' => true,
                'muted' => false,
                'loop' => false
            ]
        ];
    }

    public static function getDefaultProps() {
        return [
            'icon' => 'ri-video-line',
            'label' => 'וידאו',
            'category' => 'מדיה',
            'settings' => [
                'src' => [
                    'type' => 'text',
                    'label' => 'קישור לוידאו'
                ],
                'type' => [
                    'type' => 'select',
                    'label' => 'סוג וידאו',
                    'options' => [
                        'youtube' => 'YouTube',
                        'vimeo' => 'Vimeo',
                        'file' => 'קובץ'
                    ]
                ],
                'aspectRatio' => [
                    'type' => 'select',
                    'label' => 'יחס תצוגה',
                    'options' => [
                        '16/9' => '16:9',
                        '4/3' => '4:3',
                        '1/1' => '1:1'
                    ]
                ],
                'autoplay' => [
                    'type' => 'boolean',
                    'label' => 'ניגון אוטומטי'
                ],
                'controls' => [
                    'type' => 'boolean',
                    'label' => 'הצג פקדים'
                ],
                'muted' => [
                    'type' => 'boolean',
                    'label' => 'השתק'
                ],
                'loop' => [
                    'type' => 'boolean',
                    'label' => 'ניגון חוזר'
                ]
            ]
        ];
    }
} 