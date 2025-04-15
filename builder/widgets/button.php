<?php
/**
 * קומפוננטת כפתור מלאה
 */
function register_button_widget() {
    return [
        'id' => 'button',
        'title' => 'כפתור',
        'icon' => 'fas fa-button',
        'description' => 'רכיב להוספת כפתור',
        'render_callback' => 'render_button_widget'
    ];
}

class ButtonComponent {
    /**
     * יצירת אובייקט הרכיב
     * @param string $content תוכן הכפתור
     * @param array $style מערך סגנונות
     * @return array אובייקט הרכיב המוכן
     */
    public static function render($content = '', $style = []) {
        return [
            'type' => 'button',
            'content' => $content ?: 'לחץ כאן',
            'style' => array_merge([
                'fontFamily' => 'inherit',
                'fontSize' => '16px',
                'color' => '#ffffff',
                'backgroundColor' => '#0ea5e9',
                'padding' => '12px 24px',
                'margin' => '10px 0',
                'borderRadius' => '8px',
                'border' => 'none',
                'cursor' => 'pointer',
                'textAlign' => 'center',
                'display' => 'inline-block',
                'textDecoration' => 'none',
                'transition' => 'all 0.3s ease',
                'fontWeight' => 'normal'
            ], $style ?? []),
            'settings' => [
                'link' => '#',
                'target' => '_self',
                'fullWidth' => false,
                'style' => 'primary',
                'size' => 'medium'
            ]
        ];
    }

    /**
     * הגדרות ברירת מחדל של הרכיב
     * @return array הגדרות הרכיב
     */
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
                        'text' => 'טקסט בלבד'
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
                'backgroundColor' => [
                    'type' => 'color',
                    'label' => 'צבע רקע',
                    'condition' => [
                        'style' => ['primary', 'secondary']
                    ]
                ],
                'color' => [
                    'type' => 'color',
                    'label' => 'צבע טקסט'
                ],
                'borderRadius' => [
                    'type' => 'text',
                    'label' => 'רדיוס פינות'
                ],
                'fullWidth' => [
                    'type' => 'boolean',
                    'label' => 'רוחב מלא'
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
            'content' => 'לחץ כאן',
            'style' => [
                'fontFamily' => 'inherit',
                'fontSize' => '16px',
                'color' => '#ffffff',
                'backgroundColor' => '#0ea5e9',
                'padding' => '12px 24px',
                'margin' => '10px 0',
                'borderRadius' => '8px',
                'border' => 'none',
                'cursor' => 'pointer',
                'textAlign' => 'center',
                'display' => 'inline-block',
                'textDecoration' => 'none',
                'transition' => 'all 0.3s ease',
                'fontWeight' => 'normal'
            ],
            'settings' => [
                'link' => '#',
                'target' => '_self',
                'fullWidth' => false,
                'style' => 'primary',
                'size' => 'medium'
            ]
        ];
        
        // מיזוג הנתונים
        $content = $data['content'] ?? $defaults['content'];
        $style = isset($data['style']) ? array_merge($defaults['style'], $data['style']) : $defaults['style'];
        $settings = isset($data['settings']) ? array_merge($defaults['settings'], $data['settings']) : $defaults['settings'];

        // התאמות לפי סגנון
        if (isset($settings['style'])) {
            switch ($settings['style']) {
                case 'outline':
                    $style['backgroundColor'] = 'transparent';
                    $style['border'] = '2px solid ' . $style['color'];
                    break;
                case 'text':
                    $style['backgroundColor'] = 'transparent';
                    $style['border'] = 'none';
                    $style['padding'] = '0';
                    break;
                case 'secondary':
                    if (!isset($data['style']['backgroundColor'])) {
                        $style['backgroundColor'] = '#6B7280';
                    }
                    break;
            }
        }

        // התאמות לפי גודל
        if (isset($settings['size'])) {
            switch ($settings['size']) {
                case 'small':
                    $style['fontSize'] = '14px';
                    $style['padding'] = '8px 16px';
                    break;
                case 'large':
                    $style['fontSize'] = '18px';
                    $style['padding'] = '16px 32px';
                    break;
            }
        }

        // רוחב מלא
        if (isset($settings['fullWidth']) && $settings['fullWidth']) {
            $style['width'] = '100%';
            $style['display'] = 'block';
        }

        // בניית סגנון CSS
        $styleStr = '';
        foreach ($style as $key => $value) {
            $styleStr .= "{$key}: {$value}; ";
        }
        
        // החזרת קוד HTML
        return "<a href=\"" . htmlspecialchars($settings['link']) . "\" target=\"" . htmlspecialchars($settings['target']) . "\" style=\"{$styleStr}\">{$content}</a>";
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
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">טקסט הכפתור</label>';
        $html .= '<input type="text" id="button-content" name="content" value="' . htmlspecialchars($content) . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">קישור</label>';
        $html .= '<input type="text" id="button-link" name="settings[link]" value="' . htmlspecialchars($settings['link'] ?? '#') . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">פתיחת קישור</label>';
        $html .= '<select id="button-target" name="settings[target]" class="form-select w-full">';
        $selectedTarget = $settings['target'] ?? '_self';
        $html .= '<option value="_self" ' . ($selectedTarget === '_self' ? 'selected' : '') . '>באותו חלון</option>';
        $html .= '<option value="_blank" ' . ($selectedTarget === '_blank' ? 'selected' : '') . '>בחלון חדש</option>';
        $html .= '</select>';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">סגנון</label>';
        $html .= '<select id="button-style" name="settings[style]" class="form-select w-full">';
        $selectedStyle = $settings['style'] ?? 'primary';
        $html .= '<option value="primary" ' . ($selectedStyle === 'primary' ? 'selected' : '') . '>ראשי</option>';
        $html .= '<option value="secondary" ' . ($selectedStyle === 'secondary' ? 'selected' : '') . '>משני</option>';
        $html .= '<option value="outline" ' . ($selectedStyle === 'outline' ? 'selected' : '') . '>מתאר</option>';
        $html .= '<option value="text" ' . ($selectedStyle === 'text' ? 'selected' : '') . '>טקסט בלבד</option>';
        $html .= '</select>';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">צבע רקע</label>';
        $html .= '<div class="flex items-center">';
        $html .= '<input type="color" id="button-backgroundColor" name="style[backgroundColor]" value="' . ($style['backgroundColor'] ?? '#0ea5e9') . '" class="h-8 w-8 ml-2 border">';
        $html .= '<input type="text" id="button-backgroundColor-hex" value="' . ($style['backgroundColor'] ?? '#0ea5e9') . '" class="form-control flex-1">';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">צבע טקסט</label>';
        $html .= '<div class="flex items-center">';
        $html .= '<input type="color" id="button-color" name="style[color]" value="' . ($style['color'] ?? '#ffffff') . '" class="h-8 w-8 ml-2 border">';
        $html .= '<input type="text" id="button-color-hex" value="' . ($style['color'] ?? '#ffffff') . '" class="form-control flex-1">';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">רוחב מלא</label>';
        $html .= '<label class="inline-flex items-center">';
        $html .= '<input type="checkbox" id="button-fullWidth" name="settings[fullWidth]" value="1" ' . (isset($settings['fullWidth']) && $settings['fullWidth'] ? 'checked' : '') . ' class="form-checkbox">';
        $html .= '<span class="mr-2">הפעל רוחב מלא</span>';
        $html .= '</label>';
        $html .= '</div>';
        
        return $html;
    }
}