<?php
/**
 * API Endpoint to get template data for a specific page
 * 
 * Returns JSON data with the sections and their configuration
 */

// Include necessary files - commented out temporarily for debugging
// require_once(__DIR__ . '/../config/config.php');

// Set headers
header('Content-Type: application/json');

// Get page ID from request
$page_id = isset($_GET['page_id']) ? intval($_GET['page_id']) : 0;

// Security check
// TODO: Add proper authentication check here

// Fetch template data from database
// For now, return sample data
function get_template_data($page_id) {
    // In a real implementation, this would fetch data from a database
    // For now, we'll return sample data
    
    // Default empty template
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
                'testimonials' => [
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
                'videos' => [
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

// Return JSON response
echo json_encode($template_data); 