<?php
// ודא שהקובץ לא נגיש ישירות
if (!defined('QUICKSITE')) {
    die('גישה ישירה לקובץ זה אסורה');
}

// בדוק אם זה מסך אדמין או התחברות
$is_admin = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$is_auth = strpos($_SERVER['PHP_SELF'], '/auth/') !== false;
$is_builder = strpos($_SERVER['PHP_SELF'], '/builder/') !== false;
?>

        </div><!-- /.content-wrapper -->
        
    </main>
    
    <?php if ($is_admin && !$is_builder): ?>
            </div><!-- /.flex-1 -->
        </div><!-- /.flex -->
    <?php endif; ?>
    
    <?php if (!$is_auth && !$is_builder): // הצג פוטר בכל הדפים מלבד דפי התחברות/הרשמה וביידלר ?>
        <footer class="bg-white border-t border-gray-200 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="md:flex md:justify-between">
                    <div class="mb-8 md:mb-0">
                        <a href="<?php echo SITE_URL; ?>" class="text-indigo-600 font-bold text-xl">
                            <?php echo SITE_NAME; ?>
                        </a>
                        <p class="mt-2 text-sm text-gray-500">
                            מערכת קלה ופשוטה ליצירת דפי נחיתה ודיוור
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-8 sm:grid-cols-3">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">המוצר שלנו</h3>
                            <ul class="mt-4 space-y-4">
                                <li>
                                    <a href="<?php echo SITE_URL; ?>/features.php" class="text-sm text-gray-600 hover:text-gray-900">תכונות</a>
                                </li>
                                <li>
                                    <a href="<?php echo SITE_URL; ?>/pricing.php" class="text-sm text-gray-600 hover:text-gray-900">מחירים</a>
                                </li>
                                <li>
                                    <a href="<?php echo SITE_URL; ?>/faq.php" class="text-sm text-gray-600 hover:text-gray-900">שאלות נפוצות</a>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">החברה</h3>
                            <ul class="mt-4 space-y-4">
                                <li>
                                    <a href="<?php echo SITE_URL; ?>/about.php" class="text-sm text-gray-600 hover:text-gray-900">אודות</a>
                                </li>
                                <li>
                                    <a href="<?php echo SITE_URL; ?>/contact.php" class="text-sm text-gray-600 hover:text-gray-900">צור קשר</a>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">משפטי</h3>
                            <ul class="mt-4 space-y-4">
                                <li>
                                    <a href="<?php echo SITE_URL; ?>/privacy.php" class="text-sm text-gray-600 hover:text-gray-900">מדיניות פרטיות</a>
                                </li>
                                <li>
                                    <a href="<?php echo SITE_URL; ?>/terms.php" class="text-sm text-gray-600 hover:text-gray-900">תנאי שימוש</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="mt-8 border-t border-gray-200 pt-8 md:flex md:items-center md:justify-between">
                    <div class="flex space-x-6 rtl:space-x-reverse">
                        <a href="#" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Facebook</span>
                            <i class="ri-facebook-fill text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Instagram</span>
                            <i class="ri-instagram-line text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Twitter</span>
                            <i class="ri-twitter-fill text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">LinkedIn</span>
                            <i class="ri-linkedin-fill text-xl"></i>
                        </a>
                    </div>
                    <p class="mt-8 text-sm text-gray-500 md:mt-0">
                        &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. כל הזכויות שמורות.
                    </p>
                </div>
            </div>
        </footer>
    <?php endif; ?>
    
</div><!-- /.page-wrapper -->

<?php if (isset($extra_js)): ?>
<!-- JS נוסף ספציפי לדף -->
<script>
    <?php echo $extra_js; ?>
</script>
<?php endif; ?>

<!-- Global JS -->
<script>
// פונקציה להסתרת הודעות מערכת לאחר זמן מוגדר
$(document).ready(function() {
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000); // 5 שניות
    
    // סגירת הודעת מערכת בלחיצה
    $('.close-alert').click(function() {
        $(this).parent().fadeOut('fast');
    });
});
</script>

</body>
</html>