<?php
/**
 * Preview Template Page
 * 
 * This file renders a live preview of the template being customized
 */

// Define ABSPATH to allow template files to check if they are being included correctly
define('ABSPATH', dirname(__FILE__));

// Include necessary files - commented out temporarily for debugging
// require_once(__DIR__ . '/includes/header.php');

// Get page ID from URL
$page_id = isset($_GET['page_id']) ? intval($_GET['page_id']) : 0;

// Get template data
function get_template_data($page_id) {
    // נסיון לקרוא נתונים מקובץ נתונים בסיסי על בסיס מזהה הדף
    $data_file = __DIR__ . '/customizer/data/template_' . $page_id . '.json';
    
    // אם קובץ הנתונים קיים
    if (file_exists($data_file)) {
        $json_data = file_get_contents($data_file);
        $template_data = json_decode($json_data, true);
        
        // אם יש שגיאה בפענוח ה-JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error' => true,
                'message' => 'שגיאה בפענוח נתוני התבנית: ' . json_last_error_msg()
            ];
        }
        
        return $template_data;
    }
    
    // אם הקובץ לא קיים, נחזיר ערכי ברירת מחדל
    $template_data = [
        'sections' => [
            'header' => [
                'hidden' => false,
                'title' => 'כותרת עליונה',
                'logo' => '/assets/img/logo.png',
                'menu_items' => [
                    ['text' => 'בית', 'url' => '/'],
                    ['text' => 'אודות', 'url' => '/about'],
                    ['text' => 'שירותים', 'url' => '/services'],
                    ['text' => 'צור קשר', 'url' => '/contact']
                ]
            ],
            'hero' => [
                'hidden' => false,
                'title' => 'ברוכים הבאים לאתר שלנו',
                'subtitle' => 'אנחנו מציעים את השירותים הטובים ביותר',
                'cta_text' => 'לפרטים נוספים',
                'cta_url' => '/contact',
                'background_image' => '/assets/img/hero-bg.jpg'
            ],
            'testimonials' => [
                'hidden' => false,
                'title' => 'העדויות שלנו',
                'items' => [
                    [
                        'author_name' => 'ישראל ישראלי',
                        'author_role' => 'מנכ"ל חברת ABC',
                        'quote' => 'השירות היה מעולה ומקצועי. אני ממליץ בחום!',
                        'author_image' => 'https://randomuser.me/api/portraits/men/32.jpg'
                    ],
                    [
                        'author_name' => 'שרה כהן',
                        'author_role' => 'מעצבת גרפית',
                        'quote' => 'עבודה מהירה ואיכותית. בהחלט אעבוד איתם שוב.',
                        'author_image' => 'https://randomuser.me/api/portraits/women/44.jpg'
                    ],
                    [
                        'author_name' => 'דוד לוי',
                        'author_role' => 'יזם היי-טק',
                        'quote' => 'קיבלתי שירות יוצא מן הכלל והתוצאות עלו על כל הציפיות שלי. אני ממליץ בחום לכל מי שמחפש פתרון מקצועי ויעיל.',
                        'author_image' => 'https://randomuser.me/api/portraits/men/46.jpg'
                    ]
                ]
            ],
            'videos' => [
                'hidden' => false,
                'title' => 'הסרטונים שלנו',
                'items' => [
                    [
                        'title' => 'איך לבנות אתר מושלם',
                        'description' => 'סרטון המדריך כיצד לבנות אתר מושלם בקלות',
                        'url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                        'thumbnail' => 'https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg'
                    ],
                    [
                        'title' => 'טיפים לשיווק דיגיטלי',
                        'description' => 'טיפים מקצועיים לשיווק העסק שלך',
                        'url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                        'thumbnail' => 'https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg'
                    ]
                ]
            ],
            'contact' => [
                'hidden' => false,
                'title' => 'צור קשר',
                'subtitle' => 'נשמח לעמוד לשירותך',
                'email' => 'info@example.com',
                'phone' => '03-1234567',
                'address' => 'רחוב הראשי 123, תל אביב'
            ],
            'footer' => [
                'hidden' => false,
                'copyright' => '© כל הזכויות שמורות 2023',
                'social_links' => [
                    ['platform' => 'facebook', 'url' => 'https://facebook.com/example'],
                    ['platform' => 'instagram', 'url' => 'https://instagram.com/example'],
                    ['platform' => 'twitter', 'url' => 'https://twitter.com/example']
                ]
            ]
        ]
    ];
    
    return $template_data;
}

// Get template data
$template_data = get_template_data($page_id);
$has_error = isset($template_data['error']) && $template_data['error'] === true;
$error_message = $has_error ? $template_data['message'] : '';
$sections = $has_error ? [] : ($template_data['sections'] ?? []);

// Include section templates if no error
if (!$has_error) {
    $testimonials_template = __DIR__ . '/customizer/templates/testimonials.php';
    $videos_template = __DIR__ . '/customizer/templates/videos.php';
    
    if (file_exists($testimonials_template)) {
        require_once($testimonials_template);
    }
    
    if (file_exists($videos_template)) {
        require_once($videos_template);
    }
}

// Render section based on type
function render_section($section_id, $section_data) {
    switch ($section_id) {
        case 'testimonials':
            if (function_exists('render_testimonials_section')) {
                // תיקון נתיבי תמונות לפני הרינדור
                if (isset($section_data['items']) && is_array($section_data['items'])) {
                    foreach ($section_data['items'] as &$testimonial) {
                        if (isset($testimonial['author_image']) && !empty($testimonial['author_image'])) {
                            // אם הנתיב מתחיל עם '../', להפוך אותו לנתיב אבסולוטי
                            if (strpos($testimonial['author_image'], '../assets/') === 0) {
                                $testimonial['author_image'] = str_replace('../assets/', '/customizer/assets/', $testimonial['author_image']);
                            }
                        }
                    }
                }
                
                // לאחר בדיקות דיבאג, מתברר שיש פער בין השמות של השדות
                // הפונקציה render_testimonials_section מצפה ל'testimonials' ולא ל'items'
                if (isset($section_data['items']) && !isset($section_data['testimonials'])) {
                    $section_data['testimonials'] = $section_data['items'];
                }
                
                return render_testimonials_section($section_data);
            }
            break;
        case 'videos':
            if (function_exists('render_videos_section')) {
                // תיקון נתיבי תמונות לפני הרינדור
                if (isset($section_data['items']) && is_array($section_data['items'])) {
                    foreach ($section_data['items'] as &$video) {
                        if (isset($video['thumbnail']) && !empty($video['thumbnail'])) {
                            // אם הנתיב מתחיל עם '../', להפוך אותו לנתיב אבסולוטי
                            if (strpos($video['thumbnail'], '../assets/') === 0) {
                                $video['thumbnail'] = str_replace('../assets/', '/customizer/assets/', $video['thumbnail']);
                            }
                        }
                    }
                }
                
                // לאחר בדיקות דיבאג, מתברר שיש פער בין השמות של השדות
                // הפונקציה render_videos_section מצפה ל'videos' ולא ל'items'
                if (isset($section_data['items']) && !isset($section_data['videos'])) {
                    $section_data['videos'] = $section_data['items'];
                }
                
                return render_videos_section($section_data);
            }
            break;
        // Add other section types here
        default:
            return "<div class='section-placeholder'>סקשן {$section_id} בבנייה</div>";
    }
    
    return "<div class='section-placeholder'>סקשן {$section_id} לא זמין</div>";
}
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>תצוגה מקדימה של התבנית</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Hebrew:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3F6DFF;
            --primary-hover: #2951D4;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --light-bg: #f1f5f9;
            --dark-bg: #1e293b;
            --border-color: #e2e8f0;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --popup-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        body {
            font-family: 'Noto Sans Hebrew', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #334155;
            direction: rtl;
            text-align: right;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .section-placeholder {
            padding: 40px;
            background-color: #f1f5f9;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            text-align: center;
            margin: 20px 0;
            font-weight: 500;
            color: #64748b;
        }
        
        .preview-notice {
            position: fixed;
            top: 20px;
            left: 20px;
            background-color: #3F6DFF;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            box-shadow: var(--card-shadow);
            z-index: 1000;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .preview-notice:hover {
            transform: translateY(-2px);
            box-shadow: var(--popup-shadow);
        }
        
        .back-button {
            background-color: #1e293b;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            margin-right: 20px;
            font-family: 'Noto Sans Hebrew', sans-serif;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .back-button:hover {
            background-color: #0f172a;
            transform: translateY(-1px);
        }
        
        .error-message {
            background-color: #fecaca;
            border: 1px solid #f87171;
            color: #b91c1c;
            padding: 20px;
            border-radius: 12px;
            margin: 40px auto;
            max-width: 600px;
            text-align: center;
            box-shadow: var(--card-shadow);
        }
        
        .error-message h2 {
            margin-top: 0;
            color: #991b1b;
        }
        
        .error-message code {
            display: block;
            background: #fee2e2;
            padding: 10px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: right;
            direction: ltr;
            font-family: monospace;
            overflow-x: auto;
            white-space: pre-wrap;
        }
        
        /* Section styles */
        .testimonials-section, .videos-section {
            padding: 80px 0;
            background-color: white;
            box-shadow: var(--card-shadow);
            margin: 40px 0;
            border-radius: 16px;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            position: relative;
        }
        
        .section-title:after {
            content: "";
            position: absolute;
            width: 80px;
            height: 4px;
            background-color: var(--primary-color);
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }
        
        .testimonials-grid, .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            padding: 0 30px;
        }
        
        .testimonial-card {
            border: 1px solid #e5e5e5;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            background: white;
            transition: all 0.3s ease;
        }
        
        .testimonial-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--popup-shadow);
        }
        
        .testimonial-content {
            padding: 30px;
        }
        
        .quote-text {
            font-style: italic;
            margin-bottom: 25px;
            color: #475569;
            font-size: 1.1rem;
            line-height: 1.7;
            position: relative;
            padding-right: 24px;
        }
        
        .quote-text:before {
            content: """;
            position: absolute;
            right: 0;
            top: -10px;
            font-size: 50px;
            color: #cbd5e1;
            font-family: serif;
            line-height: 1;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        
        .author-image {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            overflow: hidden;
            margin-left: 20px;
            border: 3px solid #f1f5f9;
            box-shadow: 0 0 0 3px rgba(63, 109, 255, 0.1);
        }
        
        .author-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .testimonial-card:hover .author-image img {
            transform: scale(1.1);
        }
        
        .author-name {
            font-weight: 700;
            margin: 0 0 5px 0;
            color: #1e293b;
            font-size: 1.1rem;
        }
        
        .author-role {
            margin: 0;
            color: #64748b;
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        .video-card {
            border: 1px solid #e5e5e5;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            background: white;
            transition: all 0.3s ease;
        }
        
        .video-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--popup-shadow);
        }
        
        .video-thumbnail {
            position: relative;
            height: 220px;
        }
        
        .video-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .video-card:hover .video-thumbnail img {
            transform: scale(1.1);
        }
        
        .play-button {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.3);
            transition: background 0.3s, transform 0.3s;
        }
        
        .play-button svg {
            width: 80px;
            height: 80px;
            transition: transform 0.3s;
            filter: drop-shadow(0 0 8px rgba(0,0,0,0.3));
        }
        
        .video-card:hover .play-button {
            background: rgba(0,0,0,0.5);
        }
        
        .video-card:hover .play-button svg {
            transform: scale(1.1);
        }
        
        .video-info {
            padding: 25px;
        }
        
        .video-title {
            margin: 0 0 12px 0;
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
        }
        
        .video-description {
            margin: 0;
            color: #64748b;
            line-height: 1.6;
            font-size: 1rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
            font-size: 1.1rem;
        }
        
        @media (max-width: 768px) {
            .testimonials-grid, .videos-grid {
                grid-template-columns: 1fr;
                padding: 0 20px;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .testimonials-section, .videos-section {
                padding: 60px 0;
                margin: 30px 15px;
            }
        }
        
        /* הוספת סגנונות לאנימציית הדגשה */
        .highlight-section {
            animation: highlight-pulse 2s;
        }
        
        @keyframes highlight-pulse {
            0% { box-shadow: 0 0 0 0 rgba(63, 109, 255, 0.5); }
            70% { box-shadow: 0 0 0 20px rgba(63, 109, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(63, 109, 255, 0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Add preview notice -->
        <div class="preview-notice">תצוגה מקדימה <button onclick="window.close()" class="back-button">חזור לעורך</button></div>

        <?php if ($has_error): ?>
            <div class="error-message">
                <h2>שגיאה בטעינת נתוני התבנית</h2>
                <p>לא ניתן היה לטעון את נתוני התבנית. אנא ודא שכל הקבצים הנדרשים קיימים ותקינים.</p>
                <?php if (!empty($error_message)): ?>
                    <code><?php echo htmlspecialchars($error_message); ?></code>
                <?php endif; ?>
                <p>אנא נסה לרענן את הדף או לחזור לעורך.</p>
            </div>
        <?php else: ?>
            <?php
            // Render template sections
            $section_order = ['header', 'hero', 'features', 'testimonials', 'gallery', 'videos', 'contact', 'footer'];

            foreach ($section_order as $section_id) {
                if (isset($sections[$section_id]) && !$sections[$section_id]['hidden']) {
                    echo "<div id=\"section-{$section_id}\" class=\"section-wrapper\">";
                    echo render_section($section_id, $sections[$section_id]);
                    echo "</div>";
                }
            }
            ?>
        <?php endif; ?>
    </div>
    
    <script>
    // מאזין להודעות מהחלון המארח (העורך)
    window.addEventListener('message', function(event) {
        // וידוא שההודעה מגיעה ממקור מהימן
        if (event.origin !== window.location.origin) return;
        
        if (event.data && event.data.action === 'scrollToSection') {
            const sectionId = event.data.sectionId;
            scrollToSection(sectionId);
        }
    });
    
    // פונקציה לגלילה לסקשן מסוים
    function scrollToSection(sectionId) {
        const sectionElement = document.getElementById('section-' + sectionId);
        if (!sectionElement) return;
        
        // גלילה לסקשן
        sectionElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        // הוספת אפקט הדגשה
        sectionElement.classList.add('highlight-section');
        
        // הסרת האפקט אחרי הסיום
        setTimeout(() => {
            sectionElement.classList.remove('highlight-section');
        }, 2000);
    }
    </script>
</body>
</html> 