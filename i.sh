#!/bin/bash

# קוד ליצירת מבנה התיקיות והקבצים עבור פרויקט quicksite

# יצירת התיקייה הראשית
mkdir -p quicksite

# כניסה לתיקיה הראשית
cd quicksite

# יצירת קובץ index.php
touch index.php
echo "<?php // דף הבית/דף נחיתה ראשי ?>" > index.php

# יצירת תיקיית assets וקבצים סטטיים
mkdir -p assets/css assets/js assets/images
touch assets/css/styles.css
touch assets/js/main.js

# יצירת תיקיית config
mkdir -p config
touch config/db.php config/config.php config/init.php

# קבצי הגדרות בסיסיים
echo "<?php
// קובץ חיבור למסד נתונים
define('DB_HOST', 'localhost');
define('DB_USER', 'username');
define('DB_PASS', 'password');
define('DB_NAME', 'quicksite_db');

try {
    \$pdo = new PDO(\"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=utf8mb4\", DB_USER, DB_PASS);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException \$e) {
    die(\"חיבור למסד הנתונים נכשל: \" . \$e->getMessage());
}
?>" > config/db.php

echo "<?php
// הגדרות כלליות
define('SITE_NAME', 'קוויק סייט');
define('SITE_URL', 'http://localhost/quicksite');
define('ADMIN_EMAIL', 'admin@example.com');

// הגדרות התחברויות API
define('TWILIO_SID', 'your_twilio_sid');
define('TWILIO_TOKEN', 'your_twilio_token');
define('RAPPELSEND_API_KEY', 'your_rappelsend_api_key');

// הגדרות נוספות
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
?>" > config/config.php

echo "<?php
// אתחול המערכת
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
?>" > config/init.php

# יצירת תיקיית includes
mkdir -p includes
touch includes/functions.php includes/auth.php includes/templates.php includes/contacts.php includes/email.php includes/sms.php includes/whatsapp.php includes/header.php includes/footer.php

# יצירת תיקיית auth
mkdir -p auth
touch auth/login.php auth/register.php auth/logout.php auth/reset-password.php auth/verify.php

# קובץ login.php בסיסי
echo "<?php
require_once '../config/init.php';

// טיפול בטופס התחברות
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // קוד טיפול בניסיון התחברות
}

// תבנית HTML לדף התחברות
include '../includes/header.php';
?>

<div class=\"container mx-auto px-4 py-8\">
    <h1 class=\"text-2xl font-bold mb-6\">התחברות למערכת</h1>
    <form method=\"POST\" class=\"max-w-md mx-auto\">
        <!-- טופס התחברות -->
    </form>
</div>

<?php include '../includes/footer.php'; ?>
" > auth/login.php

# יצירת תיקיית admin ותת-תיקיות
mkdir -p admin
mkdir -p admin/landing-pages admin/contacts admin/messaging admin/settings admin/components
touch admin/index.php admin/dashboard.php admin/profile.php admin/subscription.php
touch admin/landing-pages/index.php admin/landing-pages/create.php admin/landing-pages/edit.php admin/landing-pages/analytics.php admin/landing-pages/ab-testing.php
touch admin/contacts/index.php admin/contacts/import.php admin/contacts/export.php admin/contacts/lists.php
touch admin/messaging/campaigns.php admin/messaging/create-campaign.php admin/messaging/templates.php admin/messaging/automations.php admin/messaging/reports.php
touch admin/settings/account.php admin/settings/domains.php admin/settings/api.php admin/settings/integrations.php
touch admin/components/sidebar.php admin/components/navbar.php admin/components/modals.php

# קובץ admin/index.php בסיסי
echo "<?php
require_once '../config/init.php';

// וידוא שמשתמש מחובר
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

// הפניה לדשבורד
header('Location: dashboard.php');
exit;
?>" > admin/index.php

# יצירת תיקיית builder ותת-תיקיות
mkdir -p builder builder/components builder/landing builder/email
touch builder/index.php builder/api.php
touch builder/components/text.php builder/components/image.php builder/components/button.php builder/components/form.php
touch builder/landing/sections.php builder/landing/navigation.php builder/landing/templates.php
touch builder/email/headers.php builder/email/footers.php builder/email/templates.php

# קובץ builder/index.php בסיסי
echo "<?php
require_once '../config/init.php';

// וידוא שמשתמש מחובר
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

// קבלת מזהה דף נחיתה/תבנית אימייל אם קיים
\$id = isset(\$_GET['id']) ? (int)\$_GET['id'] : 0;
\$type = isset(\$_GET['type']) ? \$_GET['type'] : 'landing';

include '../includes/header.php';
?>

<div class=\"builder-container\">
    <!-- ממשק בילדר -->
</div>

<?php include '../includes/footer.php'; ?>
" > builder/index.php

# יצירת תיקיית api ותת-תיקיות
mkdir -p api/v1 api/webhook
touch api/v1/index.php api/v1/auth.php api/v1/landing-pages.php api/v1/contacts.php api/v1/messaging.php
touch api/webhook/twilio.php api/webhook/rappelsend.php

# קובץ api/v1/index.php בסיסי
echo "<?php
// נקודת כניסה לממשק ה-API
header('Content-Type: application/json');

// בדיקת ה-endpoint המבוקש והפניה לקובץ המתאים
\$endpoint = isset(\$_GET['endpoint']) ? \$_GET['endpoint'] : '';

switch (\$endpoint) {
    case 'auth':
        require_once 'auth.php';
        break;
    case 'landing-pages':
        require_once 'landing-pages.php';
        break;
    case 'contacts':
        require_once 'contacts.php';
        break;
    case 'messaging':
        require_once 'messaging.php';
        break;
    default:
        // תשובת שגיאה כאשר ה-endpoint אינו קיים
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
}
?>" > api/v1/index.php

# יצירת תיקיית view
mkdir -p view
touch view/index.php view/form-submit.php view/tracking.php

# קובץ view/index.php בסיסי
echo "<?php
require_once '../config/init.php';

// קבלת ה-slug של דף הנחיתה
\$slug = isset(\$_GET['slug']) ? \$_GET['slug'] : '';

if (empty(\$slug)) {
    http_response_code(404);
    echo '404 - דף לא נמצא';
    exit;
}

// קבלת פרטי דף הנחיתה מהמסד נתונים
// קוד להצגת דף הנחיתה
?>" > view/index.php

echo "מבנה התיקיות והקבצים נוצר בהצלחה!"