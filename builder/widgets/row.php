<?php
/**
 * קומפוננטת שורה מלאה
 */
function register_row_widget() {
    return [
        'id' => 'row',
        'title' => 'שורה',
        'icon' => 'fas fa-th',
        'description' => 'רכיב להוספת שורות',
        'render_callback' => 'render_row_widget'
    ];
}

class RowComponent {
    /**
     * יצירת אובייקט הרכיב
     * @param array $content תוכן השורה (נוסף לעמודות)
     * @param array $style מערך סגנונות
     * @return array אובייקט הרכיב המוכן
     */
    public static function render($content = [], $style = []) {
        return [
            'type' => 'row',
            'content' => $content,
            'style' => array_merge([
                'display' => 'grid',
                'gridTemplateColumns' => 'repeat(12, 1fr)',
                'gap' => '20px',
                'padding' => '20px',
                'margin' => '0 0 30px 0',
                'backgroundColor' => 'transparent',
                'minHeight' => '50px'
            ], $style ?? []),
            'columns' => []
        ];
    }

    /**
     * הגדרות ברירת מחדל של הרכיב
     * @return array הגדרות הרכיב
     */
    public static function getDefaultProps() {
        return [
            'icon' => 'ri-layout-row-line',
            'label' => 'שורה',
            'category' => 'מבנה',
            'settings' => [
                'columns' => [
                    'type' => 'columns',
                    'label' => 'עמודות',
                    'max' => 6,
                    'options' => [
                        1 => '100%',
                        2 => '50% / 50%',
                        3 => '33% / 33% / 33%',
                        4 => '25% / 25% / 25% / 25%',
                        6 => '16.6% x 6'
                    ]
                ],
                'columnsLayout' => [
                    'type' => 'select',
                    'label' => 'פריסת עמודות',
                    'options' => [
                        'equal' => 'שוות',
                        'custom' => 'מותאם אישית',
                        'sidebar_right' => 'סרגל צד ימין',
                        'sidebar_left' => 'סרגל צד שמאל',
                        'wide_center' => 'מרכז רחב'
                    ]
                ],
                'verticalAlign' => [
                    'type' => 'select',
                    'label' => 'יישור אנכי',
                    'options' => [
                        'start' => 'למעלה',
                        'center' => 'מרכז',
                        'end' => 'למטה'
                    ]
                ],
                'backgroundColor' => [
                    'type' => 'color',
                    'label' => 'צבע רקע'
                ],
                'backgroundImage' => [
                    'type' => 'image',
                    'label' => 'תמונת רקע'
                ],
                'backgroundOverlay' => [
                    'type' => 'color',
                    'label' => 'צבע שכבת כיסוי'
                ],
                'padding' => [
                    'type' => 'spacing',
                    'label' => 'מרווח פנימי'
                ],
                'margin' => [
                    'type' => 'spacing',
                    'label' => 'מרווח חיצוני'
                ],
                'borderRadius' => [
                    'type' => 'text',
                    'label' => 'רדיוס פינות'
                ],
                'boxShadow' => [
                    'type' => 'select',
                    'label' => 'צל',
                    'options' => [
                        'none' => 'ללא',
                        'sm' => 'קטן',
                        'md' => 'בינוני',
                        'lg' => 'גדול'
                    ]
                ],
                'animation' => [
                    'type' => 'select',
                    'label' => 'אנימציה',
                    'options' => [
                        'none' => 'ללא',
                        'fade-in' => 'הופעה',
                        'slide-up' => 'החלקה למעלה',
                        'slide-down' => 'החלקה למטה',
                        'slide-right' => 'החלקה ימינה',
                        'slide-left' => 'החלקה שמאלה',
                        'zoom-in' => 'הגדלה',
                        'bounce' => 'קפיצה'
                    ]
                ]
            ],
            'droppable' => true // ניתן לגרור אליו אלמנטים
        ];
    }

    /**
     * עדכון עמודות בשורה
     * @param array $row נתוני השורה
     * @param int $numColumns מספר העמודות הרצוי
     * @param string $layout פריסת העמודות
     * @return array השורה המעודכנת
     */
    public static function updateColumns($row, $numColumns, $layout = 'equal') {
        $columns = [];
        
        // קביעת רוחב העמודות בהתאם לפריסה
        switch ($layout) {
            case 'sidebar_right':
                if ($numColumns == 2) {
                    $widths = [8, 4]; // 2/3 לתוכן, 1/3 לסרגל צד
                } else if ($numColumns == 3) {
                    $widths = [6, 3, 3]; // 1/2 לתוכן, 1/4 לכל אחד משני סרגלי הצד
                } else {
                    $widths = array_fill(0, $numColumns, 12 / $numColumns);
                }
                break;
                
            case 'sidebar_left':
                if ($numColumns == 2) {
                    $widths = [4, 8]; // 1/3 לסרגל צד, 2/3 לתוכן
                } else if ($numColumns == 3) {
                    $widths = [3, 3, 6]; // 1/4 לכל אחד משני סרגלי הצד, 1/2 לתוכן
                } else {
                    $widths = array_fill(0, $numColumns, 12 / $numColumns);
                }
                break;
                
            case 'wide_center':
                if ($numColumns == 3) {
                    $widths = [3, 6, 3]; // 1/4, 1/2, 1/4
                } else {
                    $widths = array_fill(0, $numColumns, 12 / $numColumns);
                }
                break;
                
            case 'custom':
                // לפי ערכים קודמים או ברירת מחדל
                $widths = isset($row['columns']) ? array_map(function($col) {
                    return $col['width'] ?? 12 / count($row['columns']);
                }, $row['columns']) : array_fill(0, $numColumns, 12 / $numColumns);
                
                // התאמת מספר העמודות
                if (count($widths) > $numColumns) {
                    $widths = array_slice($widths, 0, $numColumns);
                } else if (count($widths) < $numColumns) {
                    $widths = array_merge($widths, array_fill(0, $numColumns - count($widths), 12 / $numColumns));
                }
                break;
                
            case 'equal':
            default:
                // עמודות שוות
                $widths = array_fill(0, $numColumns, 12 / $numColumns);
                break;
        }
        
        // יצירת העמודות החדשות
        for ($i = 0; $i < $numColumns; $i++) {
            // שימור תוכן העמודות הקיימות
            $existingWidgets = [];
            if (isset($row['columns']) && !empty($row['columns'])) {
                foreach ($row['columns'] as $col) {
                    if (isset($col['widgets']) && !empty($col['widgets'])) {
                        $existingWidgets[] = $col['widgets'];
                    } else {
                        $existingWidgets[] = [];
                    }
                }
            }
            
            // מוסיף עמודה עם הרוחב שהוגדר
            $columns[] = [
                'width' => $widths[$i],
                'widgets' => isset($existingWidgets[$i]) ? $existingWidgets[$i] : []
            ];
        }

        $row['columns'] = $columns;
        return $row;
    }
    
    /**
     * רינדור HTML של הרכיב עבור תצוגה בדפדפן
     * @param array $data נתוני הרכיב
     * @return string קוד HTML
     */
    public static function renderHtml($data) {
        // ערכי ברירת מחדל
        $defaults = [
            'style' => [
                'display' => 'grid',
                'gridTemplateColumns' => 'repeat(12, 1fr)',
                'gap' => '20px',
                'padding' => '20px',
                'margin' => '0 0 30px 0',
                'backgroundColor' => 'transparent',
                'minHeight' => '50px'
            ],
            'columns' => []
        ];
        
        // מיזוג נתונים
        $style = isset($data['style']) ? array_merge($defaults['style'], $data['style']) : $defaults['style'];
        $columns = $data['columns'] ?? $defaults['columns'];
        
        // בניית סגנון CSS למכל השורה
        $rowStyleStr = '';
        foreach ($style as $key => $value) {
            // דילוג על gridTemplateColumns כי אנו מגדירים זאת לפי העמודות בפועל
            if ($key !== 'gridTemplateColumns') {
                $rowStyleStr .= "{$key}: {$value}; ";
            }
        }
        
        // בניית תבנית העמודות אם יש עמודות
        if (!empty($columns)) {
            $gridTemplateColumns = '';
            foreach ($columns as $column) {
                $width = $column['width'] ?? 1;
                $gridTemplateColumns .= $width . 'fr ';
            }
            $rowStyleStr .= "grid-template-columns: " . trim($gridTemplateColumns) . ";";
        } else {
            $rowStyleStr .= "grid-template-columns: 1fr;"; // ברירת מחדל - עמודה אחת
        }
        
        // בניית HTML
        $html = '<div class="builder-row" style="' . $rowStyleStr . '">';
        
        // רינדור של כל עמודה
        foreach ($columns as $column) {
            $html .= '<div class="builder-column" style="min-height: 10px;">';
            
            // רינדור הווידג'טים בעמודה
            if (isset($column['widgets']) && !empty($column['widgets'])) {
                foreach ($column['widgets'] as $widget) {
                    $widgetType = $widget['type'] ?? '';
                    
                    // בדיקה אם קיימת פונקציית רינדור לסוג הווידג'ט
                    $widgetClass = ucfirst($widgetType) . 'Component';
                    if (class_exists($widgetClass) && method_exists($widgetClass, 'renderHtml')) {
                        $html .= $widgetClass::renderHtml($widget);
                    } else {
                        $html .= '<div>Widget type not supported: ' . htmlspecialchars($widgetType) . '</div>';
                    }
                }
            }
            
            $html .= '</div>';
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
        $style = $widget['style'] ?? [];
        $columns = $widget['columns'] ?? [];
        $numColumns = count($columns);
        $columnsLayout = $widget['settings']['columnsLayout'] ?? 'equal';
        
        $html = '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">מספר עמודות</label>';
        $html .= '<select id="row-columns-count" name="settings[numColumns]" class="form-select w-full">';
        
        for ($i = 1; $i <= 6; $i++) {
            $html .= '<option value="' . $i . '" ' . ($numColumns === $i ? 'selected' : '') . '>' . $i . ' עמודות</option>';
        }
        
        $html .= '</select>';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">פריסת עמודות</label>';
        $html .= '<select id="row-columns-layout" name="settings[columnsLayout]" class="form-select w-full">';
        
        $layouts = [
            'equal' => 'עמודות שוות',
            'custom' => 'מותאם אישית',
            'sidebar_right' => 'סרגל צד ימין',
            'sidebar_left' => 'סרגל צד שמאל',
            'wide_center' => 'מרכז רחב'
        ];
        
        foreach ($layouts as $value => $label) {
            $html .= '<option value="' . $value . '" ' . ($columnsLayout === $value ? 'selected' : '') . '>' . $label . '</option>';
        }
        
        $html .= '</select>';
        $html .= '</div>';
        
        // שדות לרוחב עמודות מותאם אישית
        $html .= '<div id="custom-columns-container" class="mb-4" ' . ($columnsLayout !== 'custom' ? 'style="display: none;"' : '') . '>';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">רוחב עמודות</label>';
        
        $html .= '<div class="grid grid-cols-12 gap-2 mb-2">';
        for ($i = 0; $i < $numColumns; $i++) {
            $width = $columns[$i]['width'] ?? (12 / $numColumns);
            $colSpan = max(1, min(12, round($width))); // הגבלה בין 1 ל-12
            
            $html .= '<div class="col-span-' . $colSpan . '">';
            $html .= '<input type="number" min="1" max="12" id="column-width-' . $i . '" name="column_widths[' . $i . ']" value="' . $width . '" class="form-control w-full" data-col-index="' . $i . '">';
            $html .= '</div>';
        }
        $html .= '</div>';
        
        $html .= '<div class="text-xs text-gray-500">סכום רוחב העמודות צריך להיות 12</div>';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">יישור אנכי</label>';
        $html .= '<select id="row-vertical-align" name="settings[verticalAlign]" class="form-select w-full">';
        
        $verticalAligns = [
            'start' => 'למעלה',
            'center' => 'מרכז',
            'end' => 'למטה'
        ];
        
        $currentAlign = $style['alignItems'] ?? 'start';
        
        foreach ($verticalAligns as $value => $label) {
            $html .= '<option value="' . $value . '" ' . ($currentAlign === $value ? 'selected' : '') . '>' . $label . '</option>';
        }
        
        $html .= '</select>';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">צבע רקע</label>';
        $html .= '<div class="flex items-center">';
        $html .= '<input type="color" id="row-backgroundColor" name="style[backgroundColor]" value="' . ($style['backgroundColor'] ?? 'transparent') . '" class="h-8 w-8 ml-2 border">';
        $html .= '<input type="text" id="row-backgroundColor-hex" value="' . ($style['backgroundColor'] ?? 'transparent') . '" class="form-control flex-1">';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">ריווח פנימי (padding)</label>';
        $html .= '<input type="text" id="row-padding" name="style[padding]" value="' . ($style['padding'] ?? '20px') . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">מרווח בין עמודות (gap)</label>';
        $html .= '<input type="text" id="row-gap" name="style[gap]" value="' . ($style['gap'] ?? '20px') . '" class="form-control w-full">';
        $html .= '</div>';
        
        // JavaScript לטיפול באירועים
        $html .= '<script>
            document.addEventListener("DOMContentLoaded", function() {
                // אירוע שינוי פריסת עמודות
                const columnsLayoutSelect = document.getElementById("row-columns-layout");
                const customColumnsContainer = document.getElementById("custom-columns-container");
                
                if (columnsLayoutSelect && customColumnsContainer) {
                    columnsLayoutSelect.addEventListener("change", function() {
                        customColumnsContainer.style.display = this.value === "custom" ? "block" : "none";
                    });
                }
                
                // אירוע שינוי צבע רקע
                const bgColorInput = document.getElementById("row-backgroundColor");
                const bgColorHexInput = document.getElementById("row-backgroundColor-hex");
                
                if (bgColorInput && bgColorHexInput) {
                    bgColorInput.addEventListener("input", function() {
                        bgColorHexInput.value = this.value;
                    });
                    
                    bgColorHexInput.addEventListener("input", function() {
                        bgColorInput.value = this.value;
                    });
                }
            });
        </script>';
        
        return $html;
    }
}