<?php
// קבוע לזיהוי גישה ישירה לקבצים
if (!defined('QUICKSITE')) {
    define('QUICKSITE', true);
}

// טעינת קבצי הגדרות ותצורה
require_once '../../config/init.php';



// יצירת קטגוריות תבניות אם אינן קיימות
function create_template_categories() {
    global $pdo;
    
    $categories = [
        ['name' => 'שיווק מוצר', 'description' => 'תבניות המיועדות לשיווק מוצרים'],
        ['name' => 'איסוף לידים', 'description' => 'תבניות המיועדות לאיסוף פרטי לקוחות פוטנציאליים'],
        ['name' => 'אירועים', 'description' => 'תבניות המיועדות לפרסום אירועים'],
        ['name' => 'דפי נחיתה לשירותים', 'description' => 'תבניות המיועדות להצגת שירותים']
    ];
    
    try {
        // בדיקה אם יש כבר קטגוריות
        $stmt = $pdo->query("SELECT COUNT(*) FROM template_categories");
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            echo "<p>קטגוריות תבניות כבר קיימות במערכת.</p>";
            return;
        }
        
        // הוספת קטגוריות
        $stmt = $pdo->prepare("
            INSERT INTO template_categories (name, description, created_at)
            VALUES (?, ?, NOW())
        ");
        
        foreach ($categories as $category) {
            $stmt->execute([$category['name'], $category['description']]);
            echo "<p>הקטגוריה '{$category['name']}' נוספה בהצלחה.</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>שגיאה ביצירת קטגוריות תבניות: " . $e->getMessage() . "</p>";
    }
}

// יצירת תבניות ברירת מחדל
function create_default_templates() {
    global $pdo;
    
    try {
        // בדיקה אם יש כבר תבניות
        $stmt = $pdo->query("SELECT COUNT(*) FROM templates");
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            echo "<p>תבניות דפי נחיתה כבר קיימות במערכת.</p>";
            return;
        }
        
        // קבלת קטגוריות
        $categories = [];
        $stmt = $pdo->query("SELECT id, name FROM template_categories");
        while ($row = $stmt->fetch()) {
            $categories[$row['name']] = $row['id'];
        }
        
        if (empty($categories)) {
            echo "<p style='color: red;'>לא נמצאו קטגוריות תבניות. יש ליצור קטגוריות תחילה.</p>";
            return;
        }
        
        // תבנית ברירת מחדל בסיסית
        $default_template = [
            'name' => 'תבנית בסיסית',
            'category_id' => $categories['איסוף לידים'] ?? array_values($categories)[0],
            'type' => 'landing_page',
            'thumbnail' => SITE_URL . '/assets/images/templates/basic.jpg',
            'html_content' => '<!DOCTYPE html>
<html dir="rtl" lang="he">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{TITLE}}</title>
    <style>
        /* כאן יבוא ה-CSS */
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>{{TITLE}}</h1>
            <p class="tagline">{{DESCRIPTION}}</p>
        </div>
    </header>
    
    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-content">
                    <h2>כותרת ראשית מושכת</h2>
                    <p>טקסט תיאורי שמסביר את המוצר או השירות שלך בצורה ברורה וממוקדת.</p>
                    <a href="#form" class="cta-button">לחץ כאן לפרטים</a>
                </div>
                <div class="hero-image">
                    <div class="placeholder-image">[תמונה ראשית]</div>
                </div>
            </div>
        </section>
        
        <section class="benefits">
            <div class="container">
                <h2>היתרונות שלנו</h2>
                <div class="benefits-grid">
                    <div class="benefit-item">
                        <div class="benefit-icon">✓</div>
                        <h3>יתרון ראשון</h3>
                        <p>הסבר קצר על היתרון הראשון של המוצר או השירות שלך.</p>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">✓</div>
                        <h3>יתרון שני</h3>
                        <p>הסבר קצר על היתרון השני של המוצר או השירות שלך.</p>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">✓</div>
                        <h3>יתרון שלישי</h3>
                        <p>הסבר קצר על היתרון השלישי של המוצר או השירות שלך.</p>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="testimonials">
            <div class="container">
                <h2>לקוחות מרוצים</h2>
                <div class="testimonials-grid">
                    <div class="testimonial-item">
                        <div class="testimonial-content">
                            <p>"ציטוט של לקוח מרוצה שמספר על החוויה החיובית שלו עם המוצר או השירות שלך."</p>
                        </div>
                        <div class="testimonial-author">
                            <p><strong>שם הלקוח</strong><br>תפקיד, חברה</p>
                        </div>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-content">
                            <p>"ציטוט של לקוח נוסף שמספר על החוויה החיובית שלו עם המוצר או השירות שלך."</p>
                        </div>
                        <div class="testimonial-author">
                            <p><strong>שם הלקוח</strong><br>תפקיד, חברה</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="contact-form" id="form">
            <div class="container">
                <h2>השאירו פרטים ונחזור אליכם</h2>
                <form class="lead-form">
                    <div class="form-group">
                        <label for="name">שם מלא</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">דוא"ל</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">טלפון</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="message">הודעה</label>
                        <textarea id="message" name="message" rows="4"></textarea>
                    </div>
                    <button type="submit" class="submit-button">שלח</button>
                </form>
            </div>
        </section>
    </main>
    
    <footer>
        <div class="container">
            <p>&copy; 2025 כל הזכויות שמורות</p>
        </div>
    </footer>
</body>
</html>',
            'css_content' => '/* Reset CSS */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Variables */
:root {
    --primary-color: #4f46e5;
    --secondary-color: #818cf8;
    --text-color: #333;
    --light-gray: #f3f4f6;
    --dark-gray: #4b5563;
    --white: #fff;
    --max-width: 1200px;
    --border-radius: 8px;
    --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Global styles */
body {
    font-family: "Segoe UI", Arial, sans-serif;
    color: var(--text-color);
    line-height: 1.6;
}

.container {
    width: 100%;
    max-width: var(--max-width);
    margin: 0 auto;
    padding: 0 20px;
}

h1, h2, h3 {
    margin-bottom: 1rem;
    line-height: 1.2;
}

h1 {
    font-size: 2.5rem;
}

h2 {
    font-size: 2rem;
    text-align: center;
    margin-bottom: 2rem;
}

h3 {
    font-size: 1.5rem;
}

p {
    margin-bottom: 1rem;
}

a {
    color: var(--primary-color);
    text-decoration: none;
}

/* Header */
header {
    background-color: var(--primary-color);
    color: var(--white);
    padding: 1rem 0;
    text-align: center;
}

.tagline {
    font-size: 1.2rem;
    opacity: 0.9;
}

/* Hero section */
.hero {
    padding: 5rem 0;
    background-color: var(--light-gray);
}

.hero .container {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.hero-content {
    flex: 1;
}

.hero-image {
    flex: 1;
}

.placeholder-image {
    background-color: #ddd;
    height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--border-radius);
    color: var(--dark-gray);
    font-weight: bold;
}

.cta-button {
    display: inline-block;
    background-color: var(--primary-color);
    color: var(--white);
    padding: 0.8rem 1.5rem;
    border-radius: var(--border-radius);
    font-weight: bold;
    margin-top: 1rem;
    transition: background-color 0.3s ease;
}

.cta-button:hover {
    background-color: var(--secondary-color);
}

/* Benefits section */
.benefits {
    padding: 5rem 0;
}

.benefits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.benefit-item {
    background-color: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    text-align: center;
}

.benefit-icon {
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

/* Testimonials section */
.testimonials {
    padding: 5rem 0;
    background-color: var(--light-gray);
}

.testimonials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.testimonial-item {
    background-color: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
}

/* Contact form */
.contact-form {
    padding: 5rem 0;
}

.lead-form {
    max-width: 600px;
    margin: 0 auto;
    background-color: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
}

.form-group {
    margin-bottom: 1.5rem;
}

label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
}

input, textarea {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    font-size: 1rem;
}

.submit-button {
    display: block;
    width: 100%;
    background-color: var(--primary-color);
    color: var(--white);
    border: none;
    padding: 1rem;
    border-radius: var(--border-radius);
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.submit-button:hover {
    background-color: var(--secondary-color);
}

/* Footer */
footer {
    background-color: var(--dark-gray);
    color: var(--white);
    padding: 2rem 0;
    text-align: center;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hero .container {
        flex-direction: column;
    }
    
    h1 {
        font-size: 2rem;
    }
    
    h2 {
        font-size: 1.7rem;
    }
    
    .hero {
        padding: 3rem 0;
    }
    
    .benefits, .testimonials, .contact-form {
        padding: 3rem 0;
    }
}',
            'js_content' => '',
            'is_premium' => 0,
            'plan_level' => 'all'
        ];
        
        // הוספת תבנית ברירת מחדל
        $stmt = $pdo->prepare("
            INSERT INTO templates (
                name, category_id, type, thumbnail, html_content, css_content, js_content,
                is_premium, plan_level, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
            )
        ");
        
        $stmt->execute([
            $default_template['name'],
            $default_template['category_id'],
            $default_template['type'],
            $default_template['thumbnail'],
            $default_template['html_content'],
            $default_template['css_content'],
            $default_template['js_content'],
            $default_template['is_premium'],
            $default_template['plan_level']
        ]);
        
        echo "<p>התבנית הבסיסית נוספה בהצלחה.</p>";
        
        // הוספת תבנית עסקית
        $business_template = $default_template;
        $business_template['name'] = 'תבנית עסקית';
        $business_template['category_id'] = $categories['דפי נחיתה לשירותים'] ?? array_values($categories)[0];
        $business_template['thumbnail'] = SITE_URL . '/assets/images/templates/business.jpg';
        
        $stmt->execute([
            $business_template['name'],
            $business_template['category_id'],
            $business_template['type'],
            $business_template['thumbnail'],
            $business_template['html_content'],
            $business_template['css_content'],
            $business_template['js_content'],
            $business_template['is_premium'],
            $business_template['plan_level']
        ]);
        
        echo "<p>התבנית העסקית נוספה בהצלחה.</p>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>שגיאה ביצירת תבניות ברירת מחדל: " . $e->getMessage() . "</p>";
    }
}

// בדיקת הטבלאות הנדרשות
function check_required_tables() {
    global $pdo;
    
    $required_tables = ['template_categories', 'templates'];
    $missing_tables = [];
    
    foreach ($required_tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() == 0) {
                $missing_tables[] = $table;
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>שגיאה בבדיקת טבלה $table: " . $e->getMessage() . "</p>";
        }
    }
    
    if (!empty($missing_tables)) {
        echo "<h2>טבלאות חסרות במסד הנתונים:</h2>";
        echo "<ul>";
        foreach ($missing_tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
        
        echo "<p>יש ליצור את הטבלאות החסרות לפני שניתן להמשיך.</p>";
        
        if (in_array('template_categories', $missing_tables)) {
            echo "<pre>
CREATE TABLE `template_categories` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            </pre>";
        }
        
        if (in_array('templates', $missing_tables)) {
            echo "<pre>
CREATE TABLE `templates` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `category_id` INT NOT NULL,
  `type` ENUM('landing_page', 'email', 'sms', 'whatsapp') NOT NULL DEFAULT 'landing_page',
  `thumbnail` VARCHAR(255),
  `html_content` LONGTEXT NOT NULL,
  `css_content` LONGTEXT,
  `js_content` LONGTEXT,
  `is_premium` TINYINT(1) DEFAULT 0,
  `plan_level` ENUM('all', 'popular', 'pro') DEFAULT 'all',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `template_categories`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            </pre>";
        }
        
        return false;
    }
    
    return true;
}

?>

<!DOCTYPE html>
<html dir="rtl" lang="he">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>הוספת תבניות ברירת מחדל</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/tailwind.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h1 class="text-lg font-medium text-gray-900">הוספת תבניות ברירת מחדל</h1>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">הכלי הזה מוסיף תבניות ברירת מחדל למערכת</p>
            </div>
            
            <div class="px-4 py-5 sm:p-6">
                <?php
                // בדיקת טבלאות
                $tables_exist = check_required_tables();
                
                if ($tables_exist) {
                    // הוספת קטגוריות ותבניות
                    echo "<h2 class='text-xl font-semibold mb-4'>יצירת קטגוריות תבניות</h2>";
                    create_template_categories();
                    
                    echo "<h2 class='text-xl font-semibold mb-4 mt-8'>יצירת תבניות ברירת מחדל</h2>";
                    create_default_templates();
                }
                ?>
                
                <div class="mt-8">
                    <a href="<?php echo SITE_URL; ?>/admin/landing-pages/create.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                        <i class="ri-arrow-go-back-line ml-2"></i>
                        חזרה ליצירת דף נחיתה
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>