<?php
// קבוע לזיהוי גישה ישירה לקבצים
if (!defined('QUICKSITE')) {
    define('QUICKSITE', true);
}

// טעינת קבצי הגדרות ותצורה
require_once '../../config/init.php';

// וידוא שהמשתמש מחובר
require_login();

// וידוא שיש למשתמש מנוי פעיל
require_subscription();

// כותרת הדף
$page_title = 'ניהול דפי נחיתה';
$section = 'landing';

// פרמטרי סינון ומיון
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'updated_at';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'desc';

// מספר פריטים בעמוד
$per_page = LANDING_PAGES_PER_PAGE;

// בדיקה למגבלת דפי נחיתה
$subscription = get_active_subscription($current_user['id']);
$has_reached_limit = false;

if ($subscription) {
    // ספירת דפי נחיתה פעילים
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM landing_pages WHERE user_id = ? AND status != 'archived'");
        $stmt->execute([$current_user['id']]);
        $active_pages_count = $stmt->fetchColumn();
        
        // בדיקה אם הגיע למגבלה (אם יש מגבלה)
        if ($subscription['landing_pages_limit'] > 0 && $active_pages_count >= $subscription['landing_pages_limit']) {
            $has_reached_limit = true;
        }
    } catch (PDOException $e) {
        error_log("שגיאה בבדיקת מגבלת דפי נחיתה: " . $e->getMessage());
    }
}

// קבלת דפי הנחיתה של המשתמש
$result = [];
try {
    // בניית תנאי חיפוש
    $where_clauses = ["lp.user_id = :user_id"];
    $params = [':user_id' => $current_user['id']];
    
    if ($status !== 'all') {
        $where_clauses[] = "lp.status = :status";
        $params[':status'] = $status;
    }
    
    if (!empty($search)) {
        $where_clauses[] = "(lp.title LIKE :search OR lp.description LIKE :search OR lp.slug LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    $where_clause = implode(' AND ', $where_clauses);
    
    // ספירת סך כל דפי הנחיתה
    $count_query = "
        SELECT COUNT(*) 
        FROM landing_pages lp 
        WHERE $where_clause
    ";
    
    $stmt = $pdo->prepare($count_query);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->execute();
    $total_count = $stmt->fetchColumn();
    
    $total_pages = ceil($total_count / $per_page);
    
    // תקנון מספר העמוד הנוכחי
    $page = max(1, min($page, $total_pages > 0 ? $total_pages : 1));
    
    // חישוב הסטייה
    $offset = ($page - 1) * $per_page;
    
    // קביעת שדה המיון
    $allowed_sort_fields = ['title', 'created_at', 'updated_at', 'status'];
    if (!in_array($sort_by, $allowed_sort_fields)) {
        $sort_by = 'updated_at';
    }
    
    // קביעת סדר המיון
    $sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';
    
    // שליפת דפי הנחיתה
    $query = "
        SELECT lp.*, 
               (SELECT COUNT(*) FROM page_visits WHERE landing_page_id = lp.id) AS visits_count,
               (SELECT COUNT(*) FROM form_submissions WHERE landing_page_id = lp.id) AS conversions_count,
               cd.domain as custom_domain
        FROM landing_pages lp
        LEFT JOIN custom_domains cd ON lp.custom_domain_id = cd.id
        WHERE $where_clause
        ORDER BY lp.$sort_by $sort_order
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $landing_pages = $stmt->fetchAll();
    
    $result = [
        'pages' => $landing_pages,
        'total' => $total_count,
        'page' => $page,
        'total_pages' => $total_pages
    ];
} catch (PDOException $e) {
    error_log("שגיאה בקבלת דפי נחיתה: " . $e->getMessage());
    $result = [
        'pages' => [],
        'total' => 0,
        'page' => 1,
        'total_pages' => 0
    ];
}

// טעינת תבנית העיצוב - הדר
include_once '../../includes/header.php';
?>

<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
    <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
        <div>
            <h2 class="text-lg leading-6 font-medium text-gray-900">ניהול דפי נחיתה</h2>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">צור ונהל את דפי הנחיתה שלך</p>
        </div>
        <div>
            <?php if (!$has_reached_limit): ?>
                <a href="<?php echo SITE_URL; ?>/admin/landing-pages/create.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                    <i class="ri-add-line ml-2"></i>
                    צור דף נחיתה חדש
                </a>
            <?php else: ?>
                <div class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-gray-200 cursor-not-allowed">
                    <i class="ri-information-line ml-2"></i>
                    הגעת למגבלת דפי נחיתה
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- סינון ומיון -->
    <div class="px-4 py-5 sm:px-6 bg-gray-50 border-t border-b border-gray-200">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET" class="flex flex-wrap items-center gap-4">
            <!-- חיפוש -->
            <div class="flex-1 min-w-0">
                <label for="search" class="sr-only">חיפוש</label>
                <div class="relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="ri-search-line text-gray-400"></i>
                    </div>
                    <input type="text" name="search" id="search" class="block w-full pr-10 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-2" placeholder="חיפוש לפי כותרת, תיאור או URL" value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            
            <!-- סינון לפי סטטוס -->
            <div class="w-auto">
                <label for="status" class="sr-only">סטטוס</label>
                <select id="status" name="status" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>כל הסטטוסים</option>
                    <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>פורסם</option>
                    <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>טיוטה</option>
                    <option value="archived" <?php echo $status === 'archived' ? 'selected' : ''; ?>>בארכיון</option>
                </select>
            </div>
            
            <!-- סידור לפי -->
            <div class="w-auto">
                <label for="sort_by" class="sr-only">סדר לפי</label>
                <select id="sort_by" name="sort_by" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="updated_at" <?php echo $sort_by === 'updated_at' ? 'selected' : ''; ?>>תאריך עדכון</option>
                    <option value="created_at" <?php echo $sort_by === 'created_at' ? 'selected' : ''; ?>>תאריך יצירה</option>
                    <option value="title" <?php echo $sort_by === 'title' ? 'selected' : ''; ?>>כותרת</option>
                </select>
            </div>
            
            <!-- סדר מיון -->
            <div class="w-auto">
                <label for="sort_order" class="sr-only">סדר</label>
                <select id="sort_order" name="sort_order" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="desc" <?php echo $sort_order === 'desc' ? 'selected' : ''; ?>>מהחדש לישן</option>
                    <option value="asc" <?php echo $sort_order === 'asc' ? 'selected' : ''; ?>>מהישן לחדש</option>
                </select>
            </div>
            
            <!-- כפתור סינון -->
            <div class="w-auto">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="ri-filter-3-line ml-2"></i>
                    סנן
                </button>
                
                <?php if (!empty($search) || $status !== 'all' || $sort_by !== 'updated_at' || $sort_order !== 'desc'): ?>
                    <a href="<?php echo SITE_URL; ?>/admin/landing-pages/index.php" class="mr-2 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="ri-refresh-line ml-2"></i>
                        אפס
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- רשימת דפי נחיתה -->
    <div class="overflow-x-auto">
        <?php if (empty($result['pages'])): ?>
            <div class="py-12 text-center">
                <div class="inline-flex items-center justify-center p-6 rounded-full bg-gray-100 text-gray-500 mb-4">
                    <i class="ri-file-list-3-line text-4xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">אין דפי נחיתה</h3>
                <p class="text-gray-500 mb-6"><?php echo empty($search) ? 'טרם יצרת דפי נחיתה' : 'לא נמצאו דפי נחיתה התואמים את החיפוש'; ?></p>
                
                <?php if (!$has_reached_limit && empty($search)): ?>
                    <a href="<?php echo SITE_URL; ?>/admin/landing-pages/create.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                        <i class="ri-add-line ml-2"></i>
                        צור דף נחיתה ראשון
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">דף נחיתה</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">סטטוס</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">צפיות</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">המרות</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">עדכון אחרון</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">פעולות</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($result['pages'] as $page): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded flex items-center justify-center text-gray-500">
                                        <i class="ri-file-text-line text-lg"></i>
                                    </div>
                                    <div class="mr-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($page['title']); ?></div>
                                        <div class="text-sm text-gray-500">נוצר: <?php echo format_date($page['created_at'], 'd/m/Y'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php 
                                $page_url = get_landing_page_url($page['slug'], $page['custom_domain_id']);
                                $display_url = $page['custom_domain'] ? $page['custom_domain'] . '/' . $page['slug'] : 'view/' . $page['slug'];
                                ?>
                                <a href="<?php echo $page_url; ?>" target="_blank" class="text-indigo-600 hover:text-indigo-900 inline-flex items-center">
                                    <span class="truncate max-w-xs inline-block"><?php echo htmlspecialchars($display_url); ?></span>
                                    <i class="ri-external-link-line mr-1"></i>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php 
                                    switch ($page['status']) {
                                        case 'published':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'draft':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'archived':
                                            echo 'bg-gray-100 text-gray-800';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800';
                                    }
                                ?>">
                                    <?php 
                                    switch ($page['status']) {
                                        case 'published':
                                            echo 'פורסם';
                                            break;
                                        case 'draft':
                                            echo 'טיוטה';
                                            break;
                                        case 'archived':
                                            echo 'בארכיון';
                                            break;
                                        default:
                                            echo $page['status'];
                                    }
                                    ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                <?php echo number_format($page['visits_count']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                <?php 
                                echo number_format($page['conversions_count']);
                                
                                // חישוב אחוז המרה
                                $conversion_rate = 0;
                                if ($page['visits_count'] > 0) {
                                    $conversion_rate = round(($page['conversions_count'] / $page['visits_count']) * 100, 1);
                                }
                                if ($page['visits_count'] > 0) {
                                    echo ' <span class="text-xs text-gray-400">(' . $conversion_rate . '%)</span>';
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo format_date($page['updated_at'], 'd/m/Y H:i'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                <div class="flex space-x-2 rtl:space-x-reverse">
                                    <a href="<?php echo SITE_URL; ?>/admin/landing-pages/edit.php?id=<?php echo $page['id']; ?>" class="text-indigo-600 hover:text-indigo-900" title="ערוך">
                                        <i class="ri-edit-line text-lg"></i>
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/admin/landing-pages/analytics.php?id=<?php echo $page['id']; ?>" class="text-blue-600 hover:text-blue-900" title="אנליטיקס">
                                        <i class="ri-line-chart-line text-lg"></i>
                                    </a>
                                    <a href="<?php echo $page_url; ?>" target="_blank" class="text-gray-600 hover:text-gray-900" title="צפה">
                                        <i class="ri-eye-line text-lg"></i>
                                    </a>
                                    <button type="button" class="text-red-600 hover:text-red-900 delete-page" data-id="<?php echo $page['id']; ?>" data-title="<?php echo htmlspecialchars($page['title']); ?>" title="מחק">
                                        <i class="ri-delete-bin-line text-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- דפדוף -->
            <?php if ($result['total_pages'] > 1): ?>
                <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    מציג
                                    <span class="font-medium"><?php echo ($result['page'] - 1) * $per_page + 1; ?></span>
                                    עד
                                    <span class="font-medium"><?php echo min($result['page'] * $per_page, $result['total']); ?></span>
                                    מתוך
                                    <span class="font-medium"><?php echo $result['total']; ?></span>
                                    דפי נחיתה
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px rtl:space-x-reverse" aria-label="Pagination">
                                    <!-- לעמוד הקודם -->
                                    <?php if ($result['page'] > 1): ?>
                                        <a href="<?php echo SITE_URL; ?>/admin/landing-pages/index.php?page=<?php echo $result['page'] - 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&sort_by=<?php echo $sort_by; ?>&sort_order=<?php echo $sort_order; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <span class="sr-only">הקודם</span>
                                            <i class="ri-arrow-right-s-line"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                            <span class="sr-only">הקודם</span>
                                            <i class="ri-arrow-right-s-line"></i>
                                        </span>
                                    <?php endif; ?>

                                    <!-- מספרי עמודים -->
                                    <?php
                                    $start_page = max(1, $result['page'] - 2);
                                    $end_page = min($result['total_pages'], $result['page'] + 2);
                                    
                                    // אם אנחנו קרובים להתחלה, נוסיף עמודים בסוף
                                    if ($start_page == 1) {
                                        $end_page = min($result['total_pages'], 5);
                                    }
                                    
                                    // אם אנחנו קרובים לסוף, נוסיף עמודים בהתחלה
                                    if ($end_page == $result['total_pages']) {
                                        $start_page = max(1, $result['total_pages'] - 4);
                                    }
                                    
                                    // הצגת עמוד ראשון אם צריך
                                    if ($start_page > 1) {
                                        echo '<a href="' . SITE_URL . '/admin/landing-pages/index.php?page=1&status=' . $status . '&search=' . urlencode($search) . '&sort_by=' . $sort_by . '&sort_order=' . $sort_order . '" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>';
                                        
                                        if ($start_page > 2) {
                                            echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                        }
                                    }
                                    
                                    // הצגת עמודים
                                    for ($i = $start_page; $i <= $end_page; $i++) {
                                        if ($i == $result['page']) {
                                            echo '<span aria-current="page" class="z-10 bg-indigo-50 border-indigo-500 text-indigo-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">' . $i . '</span>';
                                        } else {
                                            echo '<a href="' . SITE_URL . '/admin/landing-pages/index.php?page=' . $i . '&status=' . $status . '&search=' . urlencode($search) . '&sort_by=' . $sort_by . '&sort_order=' . $sort_order . '" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">' . $i . '</a>';
                                        }
                                    }
                                    
                                    // הצגת עמוד אחרון אם צריך
                                    if ($end_page < $result['total_pages']) {
                                        if ($end_page < $result['total_pages'] - 1) {
                                            echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                        }
                                        
                                        echo '<a href="' . SITE_URL . '/admin/landing-pages/index.php?page=' . $result['total_pages'] . '&status=' . $status . '&search=' . urlencode($search) . '&sort_by=' . $sort_by . '&sort_order=' . $sort_order . '" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">' . $result['total_pages'] . '</a>';
                                    }
                                    ?>
                                    
                                    <!-- לעמוד הבא -->
                                    <?php if ($result['page'] < $result['total_pages']): ?>
                                        <a href="<?php echo SITE_URL; ?>/admin/landing-pages/index.php?page=<?php echo $result['page'] + 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&sort_by=<?php echo $sort_by; ?>&sort_order=<?php echo $sort_order; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <span class="sr-only">הבא</span>
                                            <i class="ri-arrow-left-s-line"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                            <span class="sr-only">הבא</span>
                                            <i class="ri-arrow-left-s-line"></i>
                                        </span>
                                    <?php endif; ?>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- מודל אישור מחיקת דף נחיתה -->
<div id="delete-modal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="ri-delete-bin-line text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:mr-4 sm:text-right">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            מחיקת דף נחיתה
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                האם אתה בטוח שברצונך למחוק את דף הנחיתה "<span id="page-title-to-delete"></span>"?
                                פעולה זו לא ניתנת לביטול ותגרום למחיקת כל הנתונים הקשורים לדף זה.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <form id="delete-form" method="POST" action="<?php echo SITE_URL; ?>/admin/landing-pages/delete.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                    <input type="hidden" name="page_id" id="page-id-to-delete" value="">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        מחק
                    </button>
                </form>
                <button type="button" id="cancel-delete" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    ביטול
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// פונקציות לטיפול במחיקת דף נחיתה
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-page');
    const deleteModal = document.getElementById('delete-modal');
    const cancelButton = document.getElementById('cancel-delete');
    const pageTitleElement = document.getElementById('page-title-to-delete');
    const pageIdElement = document.getElementById('page-id-to-delete');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const pageId = this.getAttribute('data-id');
            const pageTitle = this.getAttribute('data-title');
            
            pageTitleElement.textContent = pageTitle;
            pageIdElement.value = pageId;
            
            deleteModal.classList.remove('hidden');
        });
    });
    
    cancelButton.addEventListener('click', function() {
        deleteModal.classList.add('hidden');
    });
    
    // סגירת המודל בלחיצה על הרקע
    deleteModal.addEventListener('click', function(event) {
        if (event.target === deleteModal) {
            deleteModal.classList.add('hidden');
        }
    });
});
</script>

<?php include_once '../../includes/footer.php'; ?>
                    
                                    