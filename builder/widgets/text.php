<?php
/**
 * קומפוננטת טקסט מלאה
 */
function register_text_widget() {
    return [
        'id' => 'text',
        'title' => 'טקסט',
        'icon' => 'fas fa-font',
        'description' => 'רכיב להוספת טקסט',
        'render_callback' => 'render_text_widget'
    ];
}
class TextComponent {
    /**
     * יצירת אובייקט הרכיב
     * @param string $content תוכן הטקסט
     * @param array $style מערך סגנונות
     * @return array אובייקט הרכיב המוכן
     */
    public static function render($content = '', $style = []) {
        return [
            'type' => 'text',
            'content' => $content ?: 'טקסט לדוגמה',
            'style' => array_merge([
                'color' => '#333333',
                'fontSize' => '16px',
                'fontWeight' => 'normal',
                'textAlign' => 'right',
                'lineHeight' => '1.5',
                'margin' => '10px 0',
                'padding' => '0',
            ], $style ?? []),
            'settings' => [
                'htmlTag' => 'p'
            ]
        ];
    }

    /**
     * הגדרות ברירת מחדל של הרכיב
     * @return array הגדרות הרכיב
     */
    public static function getDefaultProps() {
        return [
            'icon' => 'ri-text-line',
            'label' => 'טקסט',
            'category' => 'בסיסי',
            'settings' => [
                'content' => [
                    'type' => 'textarea',
                    'label' => 'תוכן'
                ],
                'htmlTag' => [
                    'type' => 'select',
                    'label' => 'תגית HTML',
                    'options' => [
                        'p' => 'פסקה (p)',
                        'h1' => 'כותרת 1 (h1)',
                        'h2' => 'כותרת 2 (h2)',
                        'h3' => 'כותרת 3 (h3)',
                        'h4' => 'כותרת 4 (h4)',
                        'h5' => 'כותרת 5 (h5)',
                        'h6' => 'כותרת 6 (h6)',
                        'div' => 'מיכל (div)'
                    ]
                ],
                'color' => [
                    'type' => 'color',
                    'label' => 'צבע טקסט'
                ],
                'fontSize' => [
                    'type' => 'select',
                    'label' => 'גודל טקסט',
                    'options' => [
                        '12px' => 'קטן (12px)',
                        '16px' => 'רגיל (16px)',
                        '20px' => 'גדול (20px)',
                        '24px' => 'גדול מאוד (24px)',
                        '32px' => 'ענק (32px)'
                    ]
                ],
                'fontWeight' => [
                    'type' => 'select',
                    'label' => 'משקל גופן',
                    'options' => [
                        'normal' => 'רגיל',
                        'bold' => 'מודגש',
                        'lighter' => 'דק'
                    ]
                ],
                'textAlign' => [
                    'type' => 'select',
                    'label' => 'יישור טקסט',
                    'options' => [
                        'right' => 'ימין',
                        'center' => 'מרכז',
                        'left' => 'שמאל',
                        'justify' => 'מיושר'
                    ]
                ],
                'lineHeight' => [
                    'type' => 'select',
                    'label' => 'גובה שורה',
                    'options' => [
                        '1' => 'צפוף (1)',
                        '1.5' => 'רגיל (1.5)',
                        '2' => 'מרווח (2)'
                    ]
                ],
                'margin' => [
                    'type' => 'spacing',
                    'label' => 'מרווח חיצוני'
                ],
                'padding' => [
                    'type' => 'spacing',
                    'label' => 'ריפוד פנימי'
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
            'content' => 'טקסט לדוגמה',
            'style' => [
                'color' => '#333333',
                'fontSize' => '16px',
                'fontWeight' => 'normal',
                'textAlign' => 'right',
                'lineHeight' => '1.5',
                'margin' => '10px 0',
                'padding' => '0'
            ],
            'settings' => [
                'htmlTag' => 'p'
            ]
        ];
        
        // מיזוג הנתונים
        $content = $data['content'] ?? $defaults['content'];
        $style = isset($data['style']) ? array_merge($defaults['style'], $data['style']) : $defaults['style'];
        $settings = isset($data['settings']) ? array_merge($defaults['settings'], $data['settings']) : $defaults['settings'];
        
        // בניית סגנון CSS
        $styleStr = '';
        foreach ($style as $key => $value) {
            $styleStr .= "{$key}: {$value}; ";
        }
        
        // וידוא תגית HTML תקינה
        $validTags = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div'];
        $htmlTag = in_array($settings['htmlTag'], $validTags) ? $settings['htmlTag'] : 'p';
        
        // החזרת קוד HTML
        return "<{$htmlTag} style=\"{$styleStr}\">{$content}</{$htmlTag}>";
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
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">תוכן</label>';
        $html .= '<textarea id="text-content" name="content" rows="4" class="form-control w-full">' . htmlspecialchars($content) . '</textarea>';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">תגית HTML</label>';
        $html .= '<select id="text-htmlTag" name="settings[htmlTag]" class="form-select w-full">';
        
        $options = [
            'p' => 'פסקה (p)',
            'h1' => 'כותרת 1 (h1)',
            'h2' => 'כותרת 2 (h2)',
            'h3' => 'כותרת 3 (h3)',
            'h4' => 'כותרת 4 (h4)',
            'h5' => 'כותרת 5 (h5)',
            'h6' => 'כותרת 6 (h6)',
            'div' => 'מיכל (div)'
        ];
        
        $selectedTag = $settings['htmlTag'] ?? 'p';
        
        foreach ($options as $value => $label) {
            $selected = ($value == $selectedTag) ? 'selected' : '';
            $html .= "<option value=\"{$value}\" {$selected}>{$label}</option>";
        }
        
        $html .= '</select>';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">צבע טקסט</label>';
        $html .= '<div class="flex items-center">';
        $html .= '<input type="color" id="text-color" name="style[color]" value="' . ($style['color'] ?? '#333333') . '" class="h-8 w-8 ml-2 border">';
        $html .= '<input type="text" id="text-color-hex" value="' . ($style['color'] ?? '#333333') . '" class="form-control flex-1">';
        $html .= '</div>';
        $html .= '</div>';
        
        // הוספת שאר השדות כאן...
        
        return $html;
    }
}