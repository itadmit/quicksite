<?php
/**
 * קומפוננטת תמונה מלאה
 */
function register_image_widget() {
    return [
        'id' => 'image',
        'title' => 'תמונה',
        'icon' => 'fas fa-image',
        'description' => 'רכיב להוספת תמונה',
        'render_callback' => 'render_image_widget'
    ];
}

class ImageComponent {
    /**
     * יצירת אובייקט הרכיב
     * @param string $content תוכן התמונה (URL)
     * @param array $style מערך סגנונות
     * @return array אובייקט הרכיב המוכן
     */
    public static function render($content = '', $style = []) {
        return [
            'type' => 'image',
            'content' => $content ?: '/assets/placeholder.jpg',
            'style' => array_merge([
                'width' => '100%',
                'maxWidth' => '100%',
                'height' => 'auto',
                'objectFit' => 'cover',
                'borderRadius' => '0',
                'margin' => '10px 0',
                'display' => 'block'
            ], $style ?? []),
            'settings' => [
                'alt' => '',
                'link' => '',
                'target' => '_self',
                'caption' => '',
                'alignment' => 'center'
            ]
        ];
    }

    /**
     * הגדרות ברירת מחדל של הרכיב
     * @return array הגדרות הרכיב
     */
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
                'caption' => [
                    'type' => 'text',
                    'label' => 'כיתוב'
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
                ],
                'alignment' => [
                    'type' => 'select',
                    'label' => 'יישור',
                    'options' => [
                        'left' => 'שמאל',
                        'center' => 'מרכז',
                        'right' => 'ימין'
                    ]
                ],
                'borderRadius' => [
                    'type' => 'text',
                    'label' => 'רדיוס פינות'
                ]
            ]
        ];
    }

    /**
     * רינדור HTML של הרכיב עבור תצוגה בדפדפן
     * @param array $data נתוני הרכיב
     * @return string קוד HTML
     */
    public static function renderHtml($data) {
        // ערכי ברירת מחדל
        $defaults = [
            'content' => '/assets/placeholder.jpg',
            'style' => [
                'width' => '100%',
                'maxWidth' => '100%',
                'height' => 'auto',
                'objectFit' => 'cover',
                'borderRadius' => '0',
                'margin' => '10px 0',
                'display' => 'block'
            ],
            'settings' => [
                'alt' => '',
                'link' => '',
                'target' => '_self',
                'caption' => '',
                'alignment' => 'center'
            ]
        ];
        
        // מיזוג הנתונים
        $content = $data['content'] ?? $defaults['content'];
        $style = isset($data['style']) ? array_merge($defaults['style'], $data['style']) : $defaults['style'];
        $settings = isset($data['settings']) ? array_merge($defaults['settings'], $data['settings']) : $defaults['settings'];
        
        // בניית סגנון CSS לתמונה
        $imgStyleStr = '';
        foreach ($style as $key => $value) {
            $imgStyleStr .= "{$key}: {$value}; ";
        }
        
        // בניית סגנון למכל בהתאם ליישור
        $containerStyle = '';
        if (isset($settings['alignment'])) {
            switch ($settings['alignment']) {
                case 'center':
                    $containerStyle = 'text-align: center;';
                    break;
                case 'left':
                    $containerStyle = 'text-align: left;';
                    break;
                case 'right':
                    $containerStyle = 'text-align: right;';
                    break;
            }
        }
        
        // יצירת קוד HTML
        $html = '<div style="' . $containerStyle . '">';
        
        // תמונה עם או בלי קישור
        if (!empty($settings['link'])) {
            $html .= '<a href="' . htmlspecialchars($settings['link']) . '" target="' . htmlspecialchars($settings['target']) . '">';
        }
        
        $html .= '<img src="' . htmlspecialchars($content) . '" alt="' . htmlspecialchars($settings['alt']) . '" style="' . $imgStyleStr . '">';
        
        if (!empty($settings['link'])) {
            $html .= '</a>';
        }
        
        // הוספת כיתוב אם יש
        if (!empty($settings['caption'])) {
            $html .= '<figcaption style="margin-top: 5px; font-size: 0.9em; text-align: center;">' . htmlspecialchars($settings['caption']) . '</figcaption>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * פונקציית עזר להצגת שדות בממשק העריכה
     * @param array $widget הווידג'ט הנערך
     * @return string קוד HTML של ממשק העריכה
     */
    public static function getEditFields($widget) {
        $settings = $widget['settings'] ?? [];
        $style = $widget['style'] ?? [];
        $content = $widget['content'] ?? '';
        
        $html = '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">כתובת התמונה</label>';
        $html .= '<div class="flex items-center">';
        $html .= '<input type="text" id="image-src" name="content" value="' . htmlspecialchars($content) . '" class="form-control flex-1 ml-2">';
        $html .= '<button type="button" id="upload-image-btn" class="btn btn-secondary">העלה</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">טקסט חלופי (alt)</label>';
        $html .= '<input type="text" id="image-alt" name="settings[alt]" value="' . htmlspecialchars($settings['alt'] ?? '') . '" class="form-control w-full">';
        $html .= '<span class="text-xs text-gray-500">תיאור התמונה עבור קוראי מסך וכאשר התמונה אינה נטענת</span>';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">כיתוב תמונה</label>';
        $html .= '<input type="text" id="image-caption" name="settings[caption]" value="' . htmlspecialchars($settings['caption'] ?? '') . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">קישור</label>';
        $html .= '<input type="text" id="image-link" name="settings[link]" value="' . htmlspecialchars($settings['link'] ?? '') . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">רוחב</label>';
        $html .= '<input type="text" id="image-width" name="style[width]" value="' . htmlspecialchars($style['width'] ?? '100%') . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">יישור</label>';
        $html .= '<select id="image-alignment" name="settings[alignment]" class="form-select w-full">';
        $alignment = $settings['alignment'] ?? 'center';
        $html .= '<option value="left" ' . ($alignment === 'left' ? 'selected' : '') . '>שמאל</option>';
        $html .= '<option value="center" ' . ($alignment === 'center' ? 'selected' : '') . '>מרכז</option>';
        $html .= '<option value="right" ' . ($alignment === 'right' ? 'selected' : '') . '>ימין</option>';
        $html .= '</select>';
        $html .= '</div>';
        
        return $html;
    }
}