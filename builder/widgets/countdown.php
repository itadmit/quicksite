<?php
/**
 * קומפוננטת ספירה לאחור מלאה
 */
function register_countdown_widget() {
    return [
        'id' => 'countdown',
        'title' => 'ספירה לאחור',
        'icon' => 'ri-timer-line',
        'description' => 'רכיב לספירה לאחור',
        'render_callback' => 'render_countdown_widget'
    ];
}
class CountdownComponent {
    /**
     * יצירת אובייקט הרכיב
     * @param array $content נתוני תוכן
     * @param array $style מערך סגנונות
     * @return array אובייקט הרכיב המוכן
     */
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
                'padding' => '20px',
                'margin' => '10px 0',
                'display' => 'flex',
                'justifyContent' => 'center',
                'gap' => '20px'
            ], $style ?? []),
            'settings' => [
                'displayStyle' => 'default', // שינוי שם המשתנה מ-style ל-displayStyle למניעת בלבול
                'onComplete' => [
                    'action' => 'none', // none, hide, redirect
                    'redirectUrl' => ''
                ]
            ]
        ];
    }

    /**
     * הגדרות ברירת מחדל של הרכיב
     * @return array הגדרות הרכיב
     */
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
                'displayStyle' => [ // שינוי שם המשתנה מ-style ל-displayStyle למניעת בלבול
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

    /**
     * רינדור HTML של הרכיב עבור תצוגה בדפדפן
     * @param array $data נתוני הרכיב
     * @return string קוד HTML
     */
    public static function renderHtml($data) {
        // ערכי ברירת מחדל
        $defaults = [
            'content' => [
                'endDate' => date('Y-m-d H:i:s', strtotime('+7 days')),
                'showLabels' => true,
                'labels' => [
                    'days' => 'ימים',
                    'hours' => 'שעות',
                    'minutes' => 'דקות',
                    'seconds' => 'שניות'
                ]
            ],
            'style' => [
                'backgroundColor' => 'transparent',
                'color' => '#000000',
                'fontSize' => '24px',
                'fontWeight' => 'bold',
                'textAlign' => 'center',
                'padding' => '20px',
                'margin' => '10px 0',
                'display' => 'flex',
                'justifyContent' => 'center',
                'gap' => '20px'
            ],
            'settings' => [
                'displayStyle' => 'default', // שינוי שם המשתנה מ-style ל-displayStyle למניעת בלבול
                'onComplete' => [
                    'action' => 'none',
                    'redirectUrl' => ''
                ]
            ]
        ];
        
        // מיזוג הנתונים
        $content = isset($data['content']) ? array_merge($defaults['content'], $data['content']) : $defaults['content'];
        $style = isset($data['style']) ? array_merge($defaults['style'], $data['style']) : $defaults['style'];
        $settings = isset($data['settings']) ? array_merge($defaults['settings'], $data['settings']) : $defaults['settings'];
        
        // בניית סגנון CSS
        $containerStyleStr = '';
        foreach ($style as $key => $value) {
            $containerStyleStr .= "{$key}: {$value}; ";
        }
        
        // יצירת ID ייחודי לספירה לאחור
        $countdownId = 'countdown_' . uniqid();
        
        // בחירת סגנון תצוגה
        $displayStyle = $settings['displayStyle'] ?? 'default'; // שינוי שם המשתנה מ-style ל-displayStyle למניעת בלבול
        $itemClass = '';
        $itemStyle = '';
        
        switch ($displayStyle) {
            case 'boxed':
                $itemClass = 'countdown-box';
                $itemStyle = 'background-color: rgba(0,0,0,0.05); border-radius: 8px; padding: 10px 15px;';
                break;
            case 'minimal':
                $itemClass = 'countdown-minimal';
                $itemStyle = 'border-bottom: 2px solid currentColor;';
                break;
            default:
                $itemClass = 'countdown-default';
                break;
        }
        
        // יצירת קוד HTML
        $html = '<div id="' . $countdownId . '_container" style="' . $containerStyleStr . '" class="countdown-container">';
        
        // תאים לספירה לאחור
        $html .= '<div class="' . $itemClass . ' countdown-days" style="' . $itemStyle . '">';
        $html .= '<div class="countdown-value">00</div>';
        if ($content['showLabels']) {
            $html .= '<div class="countdown-label" style="font-size: 0.7em; opacity: 0.8;">' . htmlspecialchars($content['labels']['days']) . '</div>';
        }
        $html .= '</div>';
        
        $html .= '<div class="' . $itemClass . ' countdown-hours" style="' . $itemStyle . '">';
        $html .= '<div class="countdown-value">00</div>';
        if ($content['showLabels']) {
            $html .= '<div class="countdown-label" style="font-size: 0.7em; opacity: 0.8;">' . htmlspecialchars($content['labels']['hours']) . '</div>';
        }
        $html .= '</div>';
        
        $html .= '<div class="' . $itemClass . ' countdown-minutes" style="' . $itemStyle . '">';
        $html .= '<div class="countdown-value">00</div>';
        if ($content['showLabels']) {
            $html .= '<div class="countdown-label" style="font-size: 0.7em; opacity: 0.8;">' . htmlspecialchars($content['labels']['minutes']) . '</div>';
        }
        $html .= '</div>';
        
        $html .= '<div class="' . $itemClass . ' countdown-seconds" style="' . $itemStyle . '">';
        $html .= '<div class="countdown-value">00</div>';
        if ($content['showLabels']) {
            $html .= '<div class="countdown-label" style="font-size: 0.7em; opacity: 0.8;">' . htmlspecialchars($content['labels']['seconds']) . '</div>';
        }
        $html .= '</div>';
        
        $html .= '</div>';
        
        // JavaScript לספירה לאחור
        $html .= '<script>
            document.addEventListener("DOMContentLoaded", function() {
                // תאריך היעד
                const endDate = new Date("' . htmlspecialchars($content['endDate']) . '").getTime();
                const countdownContainer = document.getElementById("' . $countdownId . '_container");
                
                if (!countdownContainer) return;
                
                const daysEl = countdownContainer.querySelector(".countdown-days .countdown-value");
                const hoursEl = countdownContainer.querySelector(".countdown-hours .countdown-value");
                const minutesEl = countdownContainer.querySelector(".countdown-minutes .countdown-value");
                const secondsEl = countdownContainer.querySelector(".countdown-seconds .countdown-value");
                
                function updateCountdown() {
                    // הזמן הנוכחי
                    const now = new Date().getTime();
                    
                    // ההפרש בין הזמן הנוכחי ותאריך היעד
                    const distance = endDate - now;
                    
                    if (distance <= 0) {
                        // הספירה הסתיימה
                        daysEl.textContent = "00";
                        hoursEl.textContent = "00";
                        minutesEl.textContent = "00";
                        secondsEl.textContent = "00";
                        
                        // ביצוע פעולה בסיום הספירה
                        const onCompleteAction = "' . htmlspecialchars($settings['onComplete']['action'] ?? 'none') . '";
                        
                        if (onCompleteAction === "hide") {
                            countdownContainer.style.display = "none";
                        } else if (onCompleteAction === "redirect") {
                            const redirectUrl = "' . htmlspecialchars($settings['onComplete']['redirectUrl'] ?? '') . '";
                            if (redirectUrl) {
                                window.location.href = redirectUrl;
                            }
                        }
                        
                        clearInterval(countdownInterval);
                        return;
                    }
                    
                    // חישוב ימים, שעות, דקות ושניות
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    
                    // עדכון האלמנטים בדף
                    daysEl.textContent = String(days).padStart(2, "0");
                    hoursEl.textContent = String(hours).padStart(2, "0");
                    minutesEl.textContent = String(minutes).padStart(2, "0");
                    secondsEl.textContent = String(seconds).padStart(2, "0");
                }
                
                // עדכון הספירה בפעם הראשונה
                updateCountdown();
                
                // הגדרת אינטרוול לעדכון הספירה
                const countdownInterval = setInterval(updateCountdown, 1000);
            });
        </script>';
        
        return $html;
    }
    
    /**
     * פונקציית עזר להצגת שדות בממשק העריכה
     * @param array $widget הווידג'ט הנערך
     * @return string קוד HTML של ממשק העריכה
     */
    public static function getEditFields($widget) {
        $content = $widget['content'] ?? [];
        $settings = $widget['settings'] ?? [];
        $style = $widget['style'] ?? [];
        
        // הגדרת ערכי ברירת מחדל
        $endDate = $content['endDate'] ?? date('Y-m-d\TH:i', strtotime('+7 days'));
        $showLabels = isset($content['showLabels']) ? $content['showLabels'] : true;
        $labels = $content['labels'] ?? [
            'days' => 'ימים',
            'hours' => 'שעות',
            'minutes' => 'דקות',
            'seconds' => 'שניות'
        ];
        
        $displayStyle = $settings['displayStyle'] ?? 'default'; // שינוי שם המשתנה מ-style ל-displayStyle למניעת בלבול
        $onCompleteAction = $settings['onComplete']['action'] ?? 'none';
        $redirectUrl = $settings['onComplete']['redirectUrl'] ?? '';
        
        $html = '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">תאריך יעד</label>';
        $html .= '<input type="datetime-local" id="countdown-endDate" name="content[endDate]" value="' . htmlspecialchars(str_replace(' ', 'T', $endDate)) . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="inline-flex items-center">';
        $html .= '<input type="checkbox" id="countdown-showLabels" name="content[showLabels]" value="1" ' . ($showLabels ? 'checked' : '') . ' class="form-checkbox">';
        $html .= '<span class="mr-2">הצג תוויות</span>';
        $html .= '</label>';
        $html .= '</div>';
        
        $html .= '<div id="labels-container" class="mb-4" ' . (!$showLabels ? 'style="display: none;"' : '') . '>';
        $html .= '<h4 class="text-sm font-medium text-gray-700 mb-2">תוויות</h4>';
        
        $html .= '<div class="grid grid-cols-2 gap-2">';
        $html .= '<div>';
        $html .= '<label class="block text-xs text-gray-700 mb-1">ימים</label>';
        $html .= '<input type="text" id="countdown-label-days" name="content[labels][days]" value="' . htmlspecialchars($labels['days'] ?? 'ימים') . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '<div>';
        $html .= '<label class="block text-xs text-gray-700 mb-1">שעות</label>';
        $html .= '<input type="text" id="countdown-label-hours" name="content[labels][hours]" value="' . htmlspecialchars($labels['hours'] ?? 'שעות') . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '<div>';
        $html .= '<label class="block text-xs text-gray-700 mb-1">דקות</label>';
        $html .= '<input type="text" id="countdown-label-minutes" name="content[labels][minutes]" value="' . htmlspecialchars($labels['minutes'] ?? 'דקות') . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '<div>';
        $html .= '<label class="block text-xs text-gray-700 mb-1">שניות</label>';
        $html .= '<input type="text" id="countdown-label-seconds" name="content[labels][seconds]" value="' . htmlspecialchars($labels['seconds'] ?? 'שניות') . '" class="form-control w-full">';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">סגנון תצוגה</label>';
        $html .= '<select id="countdown-displayStyle" name="settings[displayStyle]" class="form-select w-full">';
        $html .= '<option value="default" ' . ($displayStyle === 'default' ? 'selected' : '') . '>רגיל</option>';
        $html .= '<option value="boxed" ' . ($displayStyle === 'boxed' ? 'selected' : '') . '>קופסאות</option>';
        $html .= '<option value="minimal" ' . ($displayStyle === 'minimal' ? 'selected' : '') . '>מינימלי</option>';
        $html .= '</select>';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">בסיום הספירה</label>';
        $html .= '<select id="countdown-onComplete" name="settings[onComplete][action]" class="form-select w-full">';
        $html .= '<option value="none" ' . ($onCompleteAction === 'none' ? 'selected' : '') . '>ללא פעולה</option>';
        $html .= '<option value="hide" ' . ($onCompleteAction === 'hide' ? 'selected' : '') . '>הסתר רכיב</option>';
        $html .= '<option value="redirect" ' . ($onCompleteAction === 'redirect' ? 'selected' : '') . '>הפנה לדף אחר</option>';
        $html .= '</select>';
        $html .= '</div>';
        
        $html .= '<div id="redirect-url-container" class="mb-4" ' . ($onCompleteAction !== 'redirect' ? 'style="display: none;"' : '') . '>';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">כתובת הפניה</label>';
        $html .= '<input type="text" id="countdown-redirectUrl" name="settings[onComplete][redirectUrl]" value="' . htmlspecialchars($redirectUrl) . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">צבע טקסט</label>';
        $html .= '<div class="flex items-center">';
        $html .= '<input type="color" id="countdown-color" name="style[color]" value="' . htmlspecialchars($style['color'] ?? '#000000') . '" class="h-8 w-8 ml-2 border">';
        $html .= '<input type="text" id="countdown-color-hex" value="' . htmlspecialchars($style['color'] ?? '#000000') . '" class="form-control flex-1">';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">גודל טקסט</label>';
        $html .= '<select id="countdown-fontSize" name="style[fontSize]" class="form-select w-full">';
        $fontSize = $style['fontSize'] ?? '24px';
        $html .= '<option value="16px" ' . ($fontSize === '16px' ? 'selected' : '') . '>קטן (16px)</option>';
        $html .= '<option value="24px" ' . ($fontSize === '24px' ? 'selected' : '') . '>בינוני (24px)</option>';
        $html .= '<option value="32px" ' . ($fontSize === '32px' ? 'selected' : '') . '>גדול (32px)</option>';
        $html .= '<option value="48px" ' . ($fontSize === '48px' ? 'selected' : '') . '>גדול מאוד (48px)</option>';
        $html .= '</select>';
        $html .= '</div>';
        
        // JavaScript לטיפול באירועים
        $html .= '<script>
            document.addEventListener("DOMContentLoaded", function() {
                // אירוע שינוי הצגת תוויות
                const showLabelsCheckbox = document.getElementById("countdown-showLabels");
                const labelsContainer = document.getElementById("labels-container");
                
                if (showLabelsCheckbox && labelsContainer) {
                    showLabelsCheckbox.addEventListener("change", function() {
                        labelsContainer.style.display = this.checked ? "block" : "none";
                    });
                }
                
                // אירוע שינוי פעולה בסיום
                const onCompleteSelect = document.getElementById("countdown-onComplete");
                const redirectUrlContainer = document.getElementById("redirect-url-container");
                
                if (onCompleteSelect && redirectUrlContainer) {
                    onCompleteSelect.addEventListener("change", function() {
                        redirectUrlContainer.style.display = this.value === "redirect" ? "block" : "none";
                    });
                }
                
                // אירוע שינוי צבע
                const colorInput = document.getElementById("countdown-color");
                const colorHexInput = document.getElementById("countdown-color-hex");
                
                if (colorInput && colorHexInput) {
                    colorInput.addEventListener("input", function() {
                        colorHexInput.value = this.value;
                    });
                    
                    colorHexInput.addEventListener("input", function() {
                        colorInput.value = this.value;
                    });
                }
            });
        </script>';
        
        return $html;
    }
}