<?php
/**
 * קומפוננטת המלצות מלאה
 */
function register_testimonial_widget() {
    return [
        'id' => 'testimonial',
        'title' => 'המלצה',
        'icon' => 'fas fa-comment-dots',
        'description' => 'רכיב להוספת המלצה',
        'render_callback' => 'render_testimonial_widget'
    ];
}

class TestimonialComponent {
    /**
     * יצירת אובייקט הרכיב
     * @param array $content תוכן ההמלצה
     * @param array $style מערך סגנונות
     * @return array אובייקט הרכיב המוכן
     */
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
                'padding' => '20px',
                'margin' => '10px 0',
                'borderRadius' => '8px',
                'boxShadow' => '0 2px 4px rgba(0,0,0,0.1)',
                'textAlign' => 'right',
                'color' => '#333333',
                'borderWidth' => '0',
                'borderStyle' => 'solid',
                'borderColor' => '#e5e7eb'
            ], $style ?? []),
            'settings' => [
                'layout' => 'standard', // standard, minimal, card, quote
                'showImage' => true,
                'showRating' => true,
                'imageStyle' => 'circle', // circle, square, rounded
                'quoteStyle' => 'modern' // modern, classic
            ]
        ];
    }

    /**
     * הגדרות ברירת מחדל של הרכיב
     * @return array הגדרות הרכיב
     */
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
                'layout' => [
                    'type' => 'select',
                    'label' => 'סגנון',
                    'options' => [
                        'standard' => 'רגיל',
                        'minimal' => 'מינימלי',
                        'card' => 'כרטיס',
                        'quote' => 'ציטוט'
                    ]
                ],
                'showImage' => [
                    'type' => 'boolean',
                    'label' => 'הצג תמונה'
                ],
                'showRating' => [
                    'type' => 'boolean',
                    'label' => 'הצג דירוג'
                ],
                'imageStyle' => [
                    'type' => 'select',
                    'label' => 'סגנון תמונה',
                    'options' => [
                        'circle' => 'עיגול',
                        'square' => 'ריבוע',
                        'rounded' => 'פינות מעוגלות'
                    ]
                ],
                'quoteStyle' => [
                    'type' => 'select',
                    'label' => 'סגנון ציטוט',
                    'options' => [
                        'modern' => 'מודרני',
                        'classic' => 'קלאסי'
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
                'name' => 'שם הממליץ',
                'role' => 'תפקיד',
                'company' => 'חברה',
                'text' => 'תוכן ההמלצה',
                'image' => '',
                'rating' => 5
            ],
            'style' => [
                'backgroundColor' => '#ffffff',
                'padding' => '20px',
                'margin' => '10px 0',
                'borderRadius' => '8px',
                'boxShadow' => '0 2px 4px rgba(0,0,0,0.1)',
                'textAlign' => 'right',
                'color' => '#333333',
                'borderWidth' => '0',
                'borderStyle' => 'solid',
                'borderColor' => '#e5e7eb'
            ],
            'settings' => [
                'layout' => 'standard',
                'showImage' => true,
                'showRating' => true,
                'imageStyle' => 'circle',
                'quoteStyle' => 'modern'
            ]
        ];
        
        // מיזוג הנתונים
        $content = isset($data['content']) ? array_merge($defaults['content'], $data['content']) : $defaults['content'];
        $style = isset($data['style']) ? array_merge($defaults['style'], $data['style']) : $defaults['style'];
        $settings = isset($data['settings']) ? array_merge($defaults['settings'], $data['settings']) : $defaults['settings'];
        
        // בניית סגנון CSS למכל
        $containerStyleStr = '';
        foreach ($style as $key => $value) {
            $containerStyleStr .= "{$key}: {$value}; ";
        }
        
        // בחירת פריסה
        $layout = $settings['layout'] ?? 'standard';
        
        // בניית HTML
        $html = '<div class="testimonial-container testimonial-' . $layout . '" style="' . $containerStyleStr . '">';
        
        // הוספת מרכאות לעיצוב ציטוט
        if ($layout === 'quote') {
            $quoteStyle = $settings['quoteStyle'] ?? 'modern';
            if ($quoteStyle === 'modern') {
                $html .= '<div class="testimonial-quote-mark" style="font-size: 4em; height: 1em; line-height: 1; opacity: 0.2; position: absolute; top: 10px; right: 10px;">&ldquo;</div>';
            } else {
                $html .= '<div class="testimonial-quote-mark" style="font-size: 2em; height: 1em; line-height: 1; margin-bottom: 10px; text-align: center;">&ldquo;</div>';
            }
        }
        
        // סגנון עיצוב שונה לכל פריסה
        switch ($layout) {
            case 'minimal':
                // הצגת תוכן המלצה
                $html .= '<div class="testimonial-text" style="margin-bottom: 15px; font-style: italic;">' . htmlspecialchars($content['text']) . '</div>';
                
                // פרטי הממליץ בפריסה מינימלית
                $html .= '<div class="testimonial-author" style="display: flex; align-items: center; ' . ($style['textAlign'] === 'center' ? 'justify-content: center;' : '') . '">';
                
                // תמונה
                if ($settings['showImage'] && !empty($content['image'])) {
                    $imgStyle = '';
                    switch ($settings['imageStyle']) {
                        case 'circle':
                            $imgStyle = 'border-radius: 50%;';
                            break;
                        case 'square':
                            $imgStyle = 'border-radius: 0;';
                            break;
                        case 'rounded':
                            $imgStyle = 'border-radius: 8px;';
                            break;
                    }
                    
                    $html .= '<div class="testimonial-image" style="margin-left: 10px;">';
                    $html .= '<img src="' . htmlspecialchars($content['image']) . '" alt="' . htmlspecialchars($content['name']) . '" style="width: 40px; height: 40px; object-fit: cover; ' . $imgStyle . '">';
                    $html .= '</div>';
                }
                
                // פרטי ממליץ
                $html .= '<div class="testimonial-author-info">';
                $html .= '<div class="testimonial-name" style="font-weight: bold;">' . htmlspecialchars($content['name']) . '</div>';
                
                if (!empty($content['role']) || !empty($content['company'])) {
                    $position = '';
                    if (!empty($content['role'])) {
                        $position .= htmlspecialchars($content['role']);
                    }
                    if (!empty($content['company'])) {
                        if (!empty($position)) {
                            $position .= ', ';
                        }
                        $position .= htmlspecialchars($content['company']);
                    }
                    
                    $html .= '<div class="testimonial-position" style="font-size: 0.9em; opacity: 0.8;">' . $position . '</div>';
                }
                
                $html .= '</div>'; // סגירת testimonial-author-info
                
                // דירוג
                if ($settings['showRating'] && $content['rating'] > 0) {
                    $html .= self::renderRating($content['rating']);
                }
                
                $html .= '</div>'; // סגירת testimonial-author
                break;
                
            case 'card':
                // פריסת כרטיס עם תמונה בראש
                
                // תמונה
                if ($settings['showImage'] && !empty($content['image'])) {
                    $imgStyle = '';
                    switch ($settings['imageStyle']) {
                        case 'circle':
                            $imgStyle = 'border-radius: 50%; margin: 0 auto 15px;';
                            break;
                        case 'square':
                            $imgStyle = 'border-radius: 0; margin: 0 auto 15px;';
                            break;
                        case 'rounded':
                            $imgStyle = 'border-radius: 8px; margin: 0 auto 15px;';
                            break;
                    }
                    
                    $html .= '<div class="testimonial-image" style="text-align: center; margin-bottom: 15px;">';
                    $html .= '<img src="' . htmlspecialchars($content['image']) . '" alt="' . htmlspecialchars($content['name']) . '" style="width: 80px; height: 80px; object-fit: cover; ' . $imgStyle . '">';
                    $html .= '</div>';
                }
                
                // דירוג
                if ($settings['showRating'] && $content['rating'] > 0) {
                    $html .= '<div style="text-align: center; margin-bottom: 10px;">';
                    $html .= self::renderRating($content['rating']);
                    $html .= '</div>';
                }
                
                // הצגת תוכן המלצה
                $html .= '<div class="testimonial-text" style="margin-bottom: 15px; text-align: center; font-style: italic;">' . htmlspecialchars($content['text']) . '</div>';
                
                // פרטי ממליץ
                $html .= '<div class="testimonial-author-info" style="text-align: center;">';
                $html .= '<div class="testimonial-name" style="font-weight: bold;">' . htmlspecialchars($content['name']) . '</div>';
                
                if (!empty($content['role']) || !empty($content['company'])) {
                    $position = '';
                    if (!empty($content['role'])) {
                        $position .= htmlspecialchars($content['role']);
                    }
                    if (!empty($content['company'])) {
                        if (!empty($position)) {
                            $position .= ', ';
                        }
                        $position .= htmlspecialchars($content['company']);
                    }
                    
                    $html .= '<div class="testimonial-position" style="font-size: 0.9em; opacity: 0.8;">' . $position . '</div>';
                }
                
                $html .= '</div>'; // סגירת testimonial-author-info
                break;
                
            case 'quote':
                // פריסת ציטוט
                
                // הצגת תוכן המלצה בתור ציטוט
                $html .= '<div class="testimonial-text" style="margin-bottom: 20px; font-style: italic; position: relative; z-index: 1;">' . htmlspecialchars($content['text']) . '</div>';
                
                // פרטי הממליץ
                $html .= '<div class="testimonial-author" style="display: flex; align-items: center; ' . ($style['textAlign'] === 'center' ? 'justify-content: center;' : '') . '">';
                
                // תמונה
                if ($settings['showImage'] && !empty($content['image'])) {
                    $imgStyle = '';
                    switch ($settings['imageStyle']) {
                        case 'circle':
                            $imgStyle = 'border-radius: 50%;';
                            break;
                        case 'square':
                            $imgStyle = 'border-radius: 0;';
                            break;
                        case 'rounded':
                            $imgStyle = 'border-radius: 8px;';
                            break;
                    }
                    
                    $html .= '<div class="testimonial-image" style="margin-left: 10px;">';
                    $html .= '<img src="' . htmlspecialchars($content['image']) . '" alt="' . htmlspecialchars($content['name']) . '" style="width: 50px; height: 50px; object-fit: cover; ' . $imgStyle . '">';
                    $html .= '</div>';
                }
                
                // פרטי ממליץ
                $html .= '<div class="testimonial-author-info">';
                $html .= '<div class="testimonial-name" style="font-weight: bold;">' . htmlspecialchars($content['name']) . '</div>';
                
                if (!empty($content['role']) || !empty($content['company'])) {
                    $position = '';
                    if (!empty($content['role'])) {
                        $position .= htmlspecialchars($content['role']);
                    }
                    if (!empty($content['company'])) {
                        if (!empty($position)) {
                            $position .= ', ';
                        }
                        $position .= htmlspecialchars($content['company']);
                    }
                    
                    $html .= '<div class="testimonial-position" style="font-size: 0.9em; opacity: 0.8;">' . $position . '</div>';
                }
                
                $html .= '</div>'; // סגירת testimonial-author-info
                
                // דירוג
                if ($settings['showRating'] && $content['rating'] > 0) {
                    $html .= self::renderRating($content['rating']);
                }
                
                $html .= '</div>'; // סגירת testimonial-author
                
                // סגירת מרכאות
                if ($settings['quoteStyle'] === 'classic') {
                    $html .= '<div class="testimonial-quote-mark" style="font-size: 2em; height: 1em; line-height: 1; margin-top: 10px; text-align: center;">&rdquo;</div>';
                }
                break;
                
            default: // standard
                // פריסה סטנדרטית
                $html .= '<div class="testimonial-inner" style="display: flex; ' . ($style['textAlign'] === 'center' ? 'flex-direction: column; align-items: center;' : '') . '">';
                
                // תמונה בצד שמאל
                if ($settings['showImage'] && !empty($content['image']) && $style['textAlign'] !== 'center') {
                    $imgStyle = '';
                    switch ($settings['imageStyle']) {
                        case 'circle':
                            $imgStyle = 'border-radius: 50%;';
                            break;
                        case 'square':
                            $imgStyle = 'border-radius: 0;';
                            break;
                        case 'rounded':
                            $imgStyle = 'border-radius: 8px;';
                            break;
                    }
                    
                    $html .= '<div class="testimonial-image" style="margin-left: 20px; flex-shrink: 0;">';
                    $html .= '<img src="' . htmlspecialchars($content['image']) . '" alt="' . htmlspecialchars($content['name']) . '" style="width: 80px; height: 80px; object-fit: cover; ' . $imgStyle . '">';
                    $html .= '</div>';
                }
                
                // תוכן
                $html .= '<div class="testimonial-content" style="flex-grow: 1;">';
                
                // תמונה למעלה (כשהיישור הוא מרכז)
                if ($settings['showImage'] && !empty($content['image']) && $style['textAlign'] === 'center') {
                    $imgStyle = '';
                    switch ($settings['imageStyle']) {
                        case 'circle':
                            $imgStyle = 'border-radius: 50%;';
                            break;
                        case 'square':
                            $imgStyle = 'border-radius: 0;';
                            break;
                        case 'rounded':
                            $imgStyle = 'border-radius: 8px;';
                            break;
                    }
                    
                    $html .= '<div class="testimonial-image" style="margin-bottom: 15px;">';
                    $html .= '<img src="' . htmlspecialchars($content['image']) . '" alt="' . htmlspecialchars($content['name']) . '" style="width: 80px; height: 80px; object-fit: cover; ' . $imgStyle . '">';
                    $html .= '</div>';
                }
                
                // דירוג
                if ($settings['showRating'] && $content['rating'] > 0) {
                    $html .= self::renderRating($content['rating']);
                }
                
                // הצגת תוכן המלצה
                $html .= '<div class="testimonial-text" style="margin-bottom: 15px;">' . htmlspecialchars($content['text']) . '</div>';
                
                // פרטי ממליץ
                $html .= '<div class="testimonial-author-info">';
                $html .= '<div class="testimonial-name" style="font-weight: bold;">' . htmlspecialchars($content['name']) . '</div>';
                
                if (!empty($content['role']) || !empty($content['company'])) {
                    $position = '';
                    if (!empty($content['role'])) {
                        $position .= htmlspecialchars($content['role']);
                    }
                    if (!empty($content['company'])) {
                        if (!empty($position)) {
                            $position .= ', ';
                        }
                        $position .= htmlspecialchars($content['company']);
                    }
                    
                    $html .= '<div class="testimonial-position" style="font-size: 0.9em; opacity: 0.8;">' . $position . '</div>';
                }
                
                $html .= '</div>'; // סגירת testimonial-author-info
                
                $html .= '</div>'; // סגירת testimonial-content
                $html .= '</div>'; // סגירת testimonial-inner
                break;
        }
        
        $html .= '</div>'; // סגירת testimonial-container
        
        return $html;
    }
    
    /**
     * רינדור דירוג כוכבים
     * @param int $rating דירוג (1-5)
     * @return string קוד HTML של הדירוג
     */
    private static function renderRating($rating) {
        $rating = max(0, min(5, (int)$rating));
        $html = '<div class="testimonial-rating" style="color: #FFB900; font-size: 1.2em; display: inline-block; margin-right: 10px;">';
        
        // כוכבים מלאים
        for ($i = 0; $i < $rating; $i++) {
            $html .= '<span style="margin: 0 1px;">★</span>';
        }
        
        // כוכבים ריקים
        for ($i = $rating; $i < 5; $i++) {
            $html .= '<span style="margin: 0 1px; opacity: 0.3;">★</span>';
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
        $content = $widget['content'] ?? [];
        $settings = $widget['settings'] ?? [];
        $style = $widget['style'] ?? [];
        
        // הגדרת ערכי ברירת מחדל
        $name = $content['name'] ?? 'שם הממליץ';
        $role = $content['role'] ?? 'תפקיד';
        $company = $content['company'] ?? 'חברה';
        $text = $content['text'] ?? 'תוכן ההמלצה';
        $image = $content['image'] ?? '';
        $rating = $content['rating'] ?? 5;
        
        $layout = $settings['layout'] ?? 'standard';
        $showImage = isset($settings['showImage']) ? $settings['showImage'] : true;
        $showRating = isset($settings['showRating']) ? $settings['showRating'] : true;
        $imageStyle = $settings['imageStyle'] ?? 'circle';
        $quoteStyle = $settings['quoteStyle'] ?? 'modern';
        
        $html = '<div class="mb-4">';
        $html .= '<h3 class="text-lg font-medium text-gray-700 mb-2">פרטי הממליץ</h3>';
        
        $html .= '<div class="mb-3">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">שם הממליץ</label>';
        $html .= '<input type="text" id="testimonial-name" name="content[name]" value="' . htmlspecialchars($name) . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '<div class="mb-3">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">תפקיד</label>';
        $html .= '<input type="text" id="testimonial-role" name="content[role]" value="' . htmlspecialchars($role) . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '<div class="mb-3">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">חברה</label>';
        $html .= '<input type="text" id="testimonial-company" name="content[company]" value="' . htmlspecialchars($company) . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '<div class="mb-3">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">תוכן ההמלצה</label>';
        $html .= '<textarea id="testimonial-text" name="content[text]" rows="4" class="form-control w-full">' . htmlspecialchars($text) . '</textarea>';
        $html .= '</div>';
        
        $html .= '<div class="mb-3">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">תמונת הממליץ</label>';
        $html .= '<div class="flex items-center">';
        $html .= '<input type="text" id="testimonial-image" name="content[image]" value="' . htmlspecialchars($image) . '" class="form-control flex-1 ml-2">';
        $html .= '<button type="button" id="upload-testimonial-image" class="btn btn-secondary">העלה</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="mb-3">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">דירוג (1-5)</label>';
        $html .= '<input type="number" id="testimonial-rating" name="content[rating]" min="1" max="5" value="' . htmlspecialchars($rating) . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<h3 class="text-lg font-medium text-gray-700 mb-2">מראה ועיצוב</h3>';
        
        $html .= '<div class="mb-3">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">סגנון</label>';
        $html .= '<select id="testimonial-layout" name="settings[layout]" class="form-select w-full">';
        $html .= '<option value="standard" ' . ($layout === 'standard' ? 'selected' : '') . '>רגיל</option>';
        $html .= '<option value="minimal" ' . ($layout === 'minimal' ? 'selected' : '') . '>מינימלי</option>';
        $html .= '<option value="card" ' . ($layout === 'card' ? 'selected' : '') . '>כרטיס</option>';
        $html .= '<option value="quote" ' . ($layout === 'quote' ? 'selected' : '') . '>ציטוט</option>';
        $html .= '</select>';
        $html .= '</div>';
        
        $html .= '<div class="mb-3">';
        $html .= '<label class="inline-flex items-center">';
        $html .= '<input type="checkbox" id="testimonial-showImage" name="settings[showImage]" value="1" ' . ($showImage ? 'checked' : '') . ' class="form-checkbox">';
        $html .= '<span class="mr-2">הצג תמונה</span>';
        $html .= '</label>';
        $html .= '</div>';
        
        $html .= '<div id="image-style-container" class="mb-3" ' . (!$showImage ? 'style="display: none;"' : '') . '>';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">סגנון תמונה</label>';
        $html .= '<select id="testimonial-imageStyle" name="settings[imageStyle]" class="form-select w-full">';
        $html .= '<option value="circle" ' . ($imageStyle === 'circle' ? 'selected' : '') . '>עיגול</option>';
        $html .= '<option value="square" ' . ($imageStyle === 'square' ? 'selected' : '') . '>ריבוע</option>';
        $html .= '<option value="rounded" ' . ($imageStyle === 'rounded' ? 'selected' : '') . '>פינות מעוגלות</option>';
        $html .= '</select>';
        $html .= '</div>';
        
        $html .= '<div class="mb-3">';
        $html .= '<label class="inline-flex items-center">';
        $html .= '<input type="checkbox" id="testimonial-showRating" name="settings[showRating]" value="1" ' . ($showRating ? 'checked' : '') . ' class="form-checkbox">';
        $html .= '<span class="mr-2">הצג דירוג</span>';
        $html .= '</label>';
        $html .= '</div>';
        
        $html .= '<div id="quote-style-container" class="mb-3" ' . ($layout !== 'quote' ? 'style="display: none;"' : '') . '>';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">סגנון ציטוט</label>';
        $html .= '<select id="testimonial-quoteStyle" name="settings[quoteStyle]" class="form-select w-full">';
        $html .= '<option value="modern" ' . ($quoteStyle === 'modern' ? 'selected' : '') . '>מודרני</option>';
        $html .= '<option value="classic" ' . ($quoteStyle === 'classic' ? 'selected' : '') . '>קלאסי</option>';
        $html .= '</select>';
        $html .= '</div>';
        
        $html .= '<div class="mb-3">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">צבע רקע</label>';
        $html .= '<div class="flex items-center">';
        $html .= '<input type="color" id="testimonial-backgroundColor" name="style[backgroundColor]" value="' . ($style['backgroundColor'] ?? '#ffffff') . '" class="h-8 w-8 ml-2 border">';
        $html .= '<input type="text" id="testimonial-backgroundColor-hex" value="' . ($style['backgroundColor'] ?? '#ffffff') . '" class="form-control flex-1">';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="mb-3">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">צבע טקסט</label>';
        $html .= '<div class="flex items-center">';
        $html .= '<input type="color" id="testimonial-color" name="style[color]" value="' . ($style['color'] ?? '#333333') . '" class="h-8 w-8 ml-2 border">';
        $html .= '<input type="text" id="testimonial-color-hex" value="' . ($style['color'] ?? '#333333') . '" class="form-control flex-1">';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="mb-3">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">רדיוס פינות</label>';
        $html .= '<input type="text" id="testimonial-borderRadius" name="style[borderRadius]" value="' . ($style['borderRadius'] ?? '8px') . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '</div>';
        
        // JavaScript לטיפול באירועים
        $html .= '<script>
            document.addEventListener("DOMContentLoaded", function() {
                // אירוע שינוי הצגת תמונה
                const showImageCheckbox = document.getElementById("testimonial-showImage");
                const imageStyleContainer = document.getElementById("image-style-container");
                
                if (showImageCheckbox && imageStyleContainer) {
                    showImageCheckbox.addEventListener("change", function() {
                        imageStyleContainer.style.display = this.checked ? "block" : "none";
                    });
                }
                
                // אירוע שינוי סגנון
                const layoutSelect = document.getElementById("testimonial-layout");
                const quoteStyleContainer = document.getElementById("quote-style-container");
                
                if (layoutSelect && quoteStyleContainer) {
                    layoutSelect.addEventListener("change", function() {
                        quoteStyleContainer.style.display = this.value === "quote" ? "block" : "none";
                    });
                }
                
                // אירוע שינוי צבע רקע
                const bgColorInput = document.getElementById("testimonial-backgroundColor");
                const bgColorHexInput = document.getElementById("testimonial-backgroundColor-hex");
                
                if (bgColorInput && bgColorHexInput) {
                    bgColorInput.addEventListener("input", function() {
                        bgColorHexInput.value = this.value;
                    });
                    
                    bgColorHexInput.addEventListener("input", function() {
                        bgColorInput.value = this.value;
                    });
                }
                
                // אירוע שינוי צבע טקסט
                const colorInput = document.getElementById("testimonial-color");
                const colorHexInput = document.getElementById("testimonial-color-hex");
                
                if (colorInput && colorHexInput) {
                    colorInput.addEventListener("input", function() {
                        colorHexInput.value = this.value;
                    });
                    
                    colorHexInput.addEventListener("input", function() {
                        colorInput.value = this.value;
                    });
                }
                
                // אירוע העלאת תמונה
                const uploadButton = document.getElementById("upload-testimonial-image");
                if (uploadButton) {
                    uploadButton.addEventListener("click", function() {
                        // פה יוטמע קוד להעלאת קובץ
                        alert("פונקציונליות העלאת תמונה תתווסף בהמשך");
                    });
                }
            });
        </script>';
        
        return $html;
    }
}