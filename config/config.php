<?php
// הגדרות כלליות
define('SITE_NAME', 'קוויק סייט');
define('SITE_URL', 'http://localhost:8888');
define('ADMIN_EMAIL', 'admin@example.com');

// הגדרות הסביבה
define('ENVIRONMENT', 'development'); // development, staging, production

// הגדרות העלאת קבצים
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('UPLOAD_ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,zip');
define('UPLOAD_DIR', __DIR__ . '/../uploads');
define('UPLOAD_URL', SITE_URL . '/uploads');

// הגדרות AWS S3 (לשימוש עתידי)
define('AWS_ENABLED', false); // הגדרה לשימוש ב-S3 במקום אחסון מקומי
define('AWS_S3_BUCKET', 'quicksite-bucket');
define('AWS_S3_REGION', 'eu-west-1');
define('AWS_ACCESS_KEY', 'your_aws_access_key');
define('AWS_SECRET_KEY', 'your_aws_secret_key');
define('AWS_S3_URL', 'https://' . AWS_S3_BUCKET . '.s3.' . AWS_S3_REGION . '.amazonaws.com');

// הגדרות התחברויות API
define('TWILIO_SID', 'your_twilio_sid');
define('TWILIO_TOKEN', 'your_twilio_token');
define('RAPPELSEND_API_KEY', 'your_rappelsend_api_key');

// הגדרות אבטחה
define('SESSION_LIFETIME', 86400); // 24 שעות בשניות
define('PASSWORD_MIN_LENGTH', 8);
define('HASH_COST', 12); // עלות הצפנת הסיסמא (bcrypt)
define('CSRF_TOKEN_NAME', 'quicksite_csrf_token');

// תבניות והצגה
define('TEMPLATE_DIR', __DIR__ . '/../templates');
define('TEMPLATES_PER_PAGE', 12);
define('CONTACTS_PER_PAGE', 20);
define('LANDING_PAGES_PER_PAGE', 10);

// גרסת מערכת וקבצים סטטיים
define('SYSTEM_VERSION', '1.0.0');
define('ASSETS_VERSION', '1.0.0'); // לצורך cache busting
define('CSS_VERSION', '1.0.0');
define('JS_VERSION', '1.0.0');

// מפתח הצפנה רנדומלי למערכת
define('ENCRYPTION_KEY', 'change_this_to_a_random_string_in_production');