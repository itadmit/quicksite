<?php
/**
 * קומפוננטת וידאו מלאה
 */
function register_video_widget() {
    return [
        'id' => 'video',
        'title' => 'וידאו',
        'icon' => 'fas fa-video',
        'description' => 'רכיב להוספת וידאו',
        'render_callback' => 'render_video_widget'
    ];
}

class VideoComponent {
    /**
     * יצירת אובייקט הרכיב
     * @param string $content תוכן הוידאו (URL)
     * @param array $style מערך סגנונות
     * @return array אובייקט הרכיב המוכן
     */
    public static function render($content = '', $style = []) {
        return [
            'type' => 'video',
            'content' => $content ?: '',
            'style' => array_merge([
                'width' => '100%',
                'maxWidth' => '100%',
                'height' => 'auto',
                'aspectRatio' => '16/9',
                'margin' => '10px 0',
                'borderRadius' => '0'
            ], $style ?? []),
            'settings' => [
                'videoType' => 'youtube', // youtube, vimeo, file
                'youtubeId' => '',
                'vimeoId' => '',
                'autoplay' => false,
                'controls' => true,
                'muted' => false,
                'loop' => false,
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
            'icon' => 'ri-video-line',
            'label' => 'וידאו',
            'category' => 'מדיה',
            'settings' => [
                'videoType' => [
                    'type' => 'select',
                    'label' => 'סוג וידאו',
                    'options' => [
                        'youtube' => 'YouTube',
                        'vimeo' => 'Vimeo',
                        'file' => 'קובץ'
                    ]
                ],
                'src' => [
                    'type' => 'text',
                    'label' => 'קישור לוידאו',
                    'placeholder' => 'הזן URL של וידאו או ID של YouTube/Vimeo'
                ],
                'aspectRatio' => [
                    'type' => 'select',
                    'label' => 'יחס תצוגה',
                    'options' => [
                        '16/9' => '16:9',
                        '4/3' => '4:3',
                        '1/1' => '1:1',
                        '21/9' => '21:9'
                    ]
                ],
                'autoplay' => [
                    'type' => 'boolean',
                    'label' => 'ניגון אוטומטי'
                ],
                'controls' => [
                    'type' => 'boolean',
                    'label' => 'הצג פקדים',
                    'default' => true
                ],
                'muted' => [
                    'type' => 'boolean',
                    'label' => 'השתק'
                ],
                'loop' => [
                    'type' => 'boolean',
                    'label' => 'ניגון חוזר'
                ],
                'caption' => [
                    'type' => 'text',
                    'label' => 'כיתוב'
                ],
                'alignment' => [
                    'type' => 'select',
                    'label' => 'יישור',
                    'options' => [
                        'left' => 'שמאל',
                        'center' => 'מרכז',
                        'right' => 'ימין'
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
            'content' => '',
            'style' => [
                'width' => '100%',
                'maxWidth' => '100%',
                'height' => 'auto',
                'aspectRatio' => '16/9',
                'margin' => '10px 0',
                'borderRadius' => '0'
            ],
            'settings' => [
                'videoType' => 'youtube',
                'youtubeId' => '',
                'vimeoId' => '',
                'autoplay' => false,
                'controls' => true,
                'muted' => false,
                'loop' => false,
                'caption' => '',
                'alignment' => 'center'
            ]
        ];
        
        // מיזוג הנתונים
        $content = $data['content'] ?? $defaults['content'];
        $style = isset($data['style']) ? array_merge($defaults['style'], $data['style']) : $defaults['style'];
        $settings = isset($data['settings']) ? array_merge($defaults['settings'], $data['settings']) : $defaults['settings'];
        
        // חילוץ ID מהתוכן אם לא הוגדר במפורש
        $videoType = $settings['videoType'];
        
        // עבור YouTube
        $youtubeId = $settings['youtubeId'] ?? '';
        if ($videoType === 'youtube' && empty($youtubeId) && !empty($content)) {
            // ניסיון לחלץ ID של YouTube מ-URL
            if (preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $content, $matches)) {
                $youtubeId = $matches[1];
            } else {
                // אולי הוכנס ID ישירות
                if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $content)) {
                    $youtubeId = $content;
                }
            }
        }
        
        // עבור Vimeo
        $vimeoId = $settings['vimeoId'] ?? '';
        if ($videoType === 'vimeo' && empty($vimeoId) && !empty($content)) {
            // ניסיון לחלץ ID של Vimeo מ-URL
            if (preg_match('/(?:vimeo\.com\/|player\.vimeo\.com\/video\/)([0-9]+)/', $content, $matches)) {
                $vimeoId = $matches[1];
            } else {
                // אולי הוכנס ID ישירות
                if (preg_match('/^[0-9]+$/', $content)) {
                    $vimeoId = $content;
                }
            }
        }
        
        // בניית סגנון CSS למכל
        $containerStyle = 'text-align: ' . $settings['alignment'] . ';';
        
        // חישוב גובה על פי יחס תצוגה
        $aspectRatio = $style['aspectRatio'] ?? '16/9';
        
        // יצירת קוד HTML
        $html = '<div style="' . $containerStyle . ' margin: ' . ($style['margin'] ?? '10px 0') . ';">';
        
        // רינדור הווידאו בהתאם לסוג
        switch ($videoType) {
            case 'youtube':
                if (!empty($youtubeId)) {
                    $params = [];
                    if ($settings['autoplay']) $params[] = 'autoplay=1';
                    if (!$settings['controls']) $params[] = 'controls=0';
                    if ($settings['muted']) $params[] = 'mute=1';
                    if ($settings['loop']) $params[] = 'loop=1&playlist=' . $youtubeId;
                    
                    $queryString = !empty($params) ? '?' . implode('&', $params) : '';
                    
                    $html .= '<div style="position: relative; padding-bottom: calc(' . str_replace('/', '*100%/', $aspectRatio) . '); height: 0; width: ' . ($style['width'] ?? '100%') . '; max-width: ' . ($style['maxWidth'] ?? '100%') . '; border-radius: ' . ($style['borderRadius'] ?? '0') . '; overflow: hidden;">';
                    $html .= '<iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" src="https://www.youtube.com/embed/' . htmlspecialchars($youtubeId) . $queryString . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                    $html .= '</div>';
                } else {
                    $html .= '<div class="video-placeholder" style="background-color: #f3f4f6; border: 1px dashed #d1d5db; padding: 2rem; text-align: center; border-radius: ' . ($style['borderRadius'] ?? '0') . ';">';
                    $html .= '<p>יש להגדיר ID או URL של YouTube</p>';
                    $html .= '</div>';
                }
                break;
                
            case 'vimeo':
                if (!empty($vimeoId)) {
                    $params = [];
                    if ($settings['autoplay']) $params[] = 'autoplay=1';
                    if (!$settings['controls']) $params[] = 'controls=0';
                    if ($settings['muted']) $params[] = 'muted=1';
                    if ($settings['loop']) $params[] = 'loop=1';
                    
                    $queryString = !empty($params) ? '?' . implode('&', $params) : '';
                    
                    $html .= '<div style="position: relative; padding-bottom: calc(' . str_replace('/', '*100%/', $aspectRatio) . '); height: 0; width: ' . ($style['width'] ?? '100%') . '; max-width: ' . ($style['maxWidth'] ?? '100%') . '; border-radius: ' . ($style['borderRadius'] ?? '0') . '; overflow: hidden;">';
                    $html .= '<iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" src="https://player.vimeo.com/video/' . htmlspecialchars($vimeoId) . $queryString . '" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
                    $html .= '</div>';
                } else {
                    $html .= '<div class="video-placeholder" style="background-color: #f3f4f6; border: 1px dashed #d1d5db; padding: 2rem; text-align: center; border-radius: ' . ($style['borderRadius'] ?? '0') . ';">';
                    $html .= '<p>יש להגדיר ID או URL של Vimeo</p>';
                    $html .= '</div>';
                }
                break;
                
            case 'file':
                if (!empty($content)) {
                    $autoplay = $settings['autoplay'] ? 'autoplay' : '';
                    $controls = $settings['controls'] ? 'controls' : '';
                    $muted = $settings['muted'] ? 'muted' : '';
                    $loop = $settings['loop'] ? 'loop' : '';
                    
                    $html .= '<video src="' . htmlspecialchars($content) . '" ' . $autoplay . ' ' . $controls . ' ' . $muted . ' ' . $loop . ' style="width: ' . ($style['width'] ?? '100%') . '; max-width: ' . ($style['maxWidth'] ?? '100%') . '; height: auto; border-radius: ' . ($style['borderRadius'] ?? '0') . ';">';
                    $html .= 'הדפדפן שלך אינו תומך בתג video.';
                    $html .= '</video>';
                } else {
                    $html .= '<div class="video-placeholder" style="background-color: #f3f4f6; border: 1px dashed #d1d5db; padding: 2rem; text-align: center; border-radius: ' . ($style['borderRadius'] ?? '0') . ';">';
                    $html .= '<p>יש להגדיר קישור לקובץ וידאו</p>';
                    $html .= '</div>';
                }
                break;
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
        
        $videoType = $settings['videoType'] ?? 'youtube';
        
        $html = '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">סוג וידאו</label>';
        $html .= '<select id="video-type" name="settings[videoType]" class="form-select w-full">';
        $html .= '<option value="youtube" ' . ($videoType === 'youtube' ? 'selected' : '') . '>YouTube</option>';
        $html .= '<option value="vimeo" ' . ($videoType === 'vimeo' ? 'selected' : '') . '>Vimeo</option>';
        $html .= '<option value="file" ' . ($videoType === 'file' ? 'selected' : '') . '>קובץ וידאו</option>';
        $html .= '</select>';
        $html .= '</div>';
        
        // שדות שונים בהתאם לסוג הוידאו
        $html .= '<div id="youtube-vimeo-fields" ' . ($videoType === 'file' ? 'style="display: none;"' : '') . '>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1" id="video-url-label">' . ($videoType === 'youtube' ? 'קישור YouTube או ID' : 'קישור Vimeo או ID') . '</label>';
        $html .= '<input type="text" id="video-url" name="content" value="' . htmlspecialchars($content) . '" class="form-control w-full">';
        $html .= '<span class="text-xs text-gray-500">אפשר להזין את הקישור המלא או רק את מזהה הסרטון</span>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        // שדות לקובץ וידאו
        $html .= '<div id="file-fields" ' . ($videoType !== 'file' ? 'style="display: none;"' : '') . '>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">קישור לקובץ וידאו</label>';
        $html .= '<div class="flex items-center">';
        $html .= '<input type="text" id="video-file-url" name="content" value="' . htmlspecialchars($content) . '" class="form-control flex-1 ml-2">';
        $html .= '<button type="button" id="upload-video-btn" class="btn btn-secondary">העלה</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        // הגדרות משותפות
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">יחס תצוגה</label>';
        $html .= '<select id="video-aspectRatio" name="style[aspectRatio]" class="form-select w-full">';
        $aspectRatio = $style['aspectRatio'] ?? '16/9';
        $html .= '<option value="16/9" ' . ($aspectRatio === '16/9' ? 'selected' : '') . '>16:9</option>';
        $html .= '<option value="4/3" ' . ($aspectRatio === '4/3' ? 'selected' : '') . '>4:3</option>';
        $html .= '<option value="1/1" ' . ($aspectRatio === '1/1' ? 'selected' : '') . '>1:1 (ריבוע)</option>';
        $html .= '<option value="21/9" ' . ($aspectRatio === '21/9' ? 'selected' : '') . '>21:9 (רחב)</option>';
        $html .= '</select>';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<div class="flex flex-wrap -mx-2">';
        
        $html .= '<div class="w-1/2 px-2 mb-2">';
        $html .= '<label class="inline-flex items-center">';
        $html .= '<input type="checkbox" id="video-autoplay" name="settings[autoplay]" value="1" ' . (isset($settings['autoplay']) && $settings['autoplay'] ? 'checked' : '') . ' class="form-checkbox">';
        $html .= '<span class="mr-2">ניגון אוטומטי</span>';
        $html .= '</label>';
        $html .= '</div>';
        
        $html .= '<div class="w-1/2 px-2 mb-2">';
        $html .= '<label class="inline-flex items-center">';
        $html .= '<input type="checkbox" id="video-controls" name="settings[controls]" value="1" ' . (!isset($settings['controls']) || $settings['controls'] ? 'checked' : '') . ' class="form-checkbox">';
        $html .= '<span class="mr-2">הצג פקדים</span>';
        $html .= '</label>';
        $html .= '</div>';
        
        $html .= '<div class="w-1/2 px-2 mb-2">';
        $html .= '<label class="inline-flex items-center">';
        $html .= '<input type="checkbox" id="video-muted" name="settings[muted]" value="1" ' . (isset($settings['muted']) && $settings['muted'] ? 'checked' : '') . ' class="form-checkbox">';
        $html .= '<span class="mr-2">השתק</span>';
        $html .= '</label>';
        $html .= '</div>';
        
        $html .= '<div class="w-1/2 px-2 mb-2">';
        $html .= '<label class="inline-flex items-center">';
        $html .= '<input type="checkbox" id="video-loop" name="settings[loop]" value="1" ' . (isset($settings['loop']) && $settings['loop'] ? 'checked' : '') . ' class="form-checkbox">';
        $html .= '<span class="mr-2">ניגון חוזר</span>';
        $html .= '</label>';
        $html .= '</div>';
        
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">כיתוב</label>';
        $html .= '<input type="text" id="video-caption" name="settings[caption]" value="' . htmlspecialchars($settings['caption'] ?? '') . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">יישור</label>';
        $html .= '<select id="video-alignment" name="settings[alignment]" class="form-select w-full">';
        $alignment = $settings['alignment'] ?? 'center';
        $html .= '<option value="left" ' . ($alignment === 'left' ? 'selected' : '') . '>שמאל</option>';
        $html .= '<option value="center" ' . ($alignment === 'center' ? 'selected' : '') . '>מרכז</option>';
        $html .= '<option value="right" ' . ($alignment === 'right' ? 'selected' : '') . '>ימין</option>';
        $html .= '</select>';
        $html .= '</div>';
        
        // JavaScript לטיפול באירועים
        $html .= '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const videoTypeSelect = document.getElementById("video-type");
                const youtubeVimeoFields = document.getElementById("youtube-vimeo-fields");
                const fileFields = document.getElementById("file-fields");
                const videoUrlLabel = document.getElementById("video-url-label");
                
                if (videoTypeSelect) {
                    videoTypeSelect.addEventListener("change", function() {
                        switch (this.value) {
                            case "youtube":
                                youtubeVimeoFields.style.display = "block";
                                fileFields.style.display = "none";
                                videoUrlLabel.textContent = "קישור YouTube או ID";
                                break;
                            case "vimeo":
                                youtubeVimeoFields.style.display = "block";
                                fileFields.style.display = "none";
                                videoUrlLabel.textContent = "קישור Vimeo או ID";
                                break;
                            case "file":
                                youtubeVimeoFields.style.display = "none";
                                fileFields.style.display = "block";
                                break;
                        }
                    });
                }
                
                // אירוע העלאת קובץ וידאו
                const uploadButton = document.getElementById("upload-video-btn");
                if (uploadButton) {
                    uploadButton.addEventListener("click", function() {
                        // פה יוטמע קוד להעלאת קובץ
                        alert("פונקציונליות העלאת וידאו תתווסף בהמשך");
                    });
                }
            });
        </script>';
        
        return $html;
    }
}