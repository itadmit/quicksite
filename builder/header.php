<?php
// header.php
?>
<header id="builder-header" class="bg-white shadow-sm px-4 py-2 flex items-center justify-between">
    <!-- Right Side: Logo & Title -->
    <div class="flex items-center">
        <h1 class="text-base font-medium text-gray-700 truncate">
            <?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Builder'; ?>
        </h1>
    </div>

    <!-- Center: Responsive Controls -->
    <div id="responsive-controls" class="flex-grow flex justify-center">
        <div class="responsive-buttons inline-flex items-center justify-center bg-gray-100 rounded-md p-1 space-x-1 rtl:space-x-reverse">
            <button data-view="desktop" data-active="true" class="responsive-button p-1.5 rounded-md text-gray-500 hover:bg-gray-200 data-[active='true']:bg-primary-100 data-[active='true']:text-primary-600" title="מחשב">
                <i class="ri-computer-line text-lg"></i>
            </button>
            <button data-view="tablet" data-active="false" class="responsive-button p-1.5 rounded-md text-gray-500 hover:bg-gray-200 data-[active='true']:bg-primary-100 data-[active='true']:text-primary-600" title="טאבלט">
                <i class="ri-tablet-line text-lg"></i>
            </button>
            <button data-view="mobile" data-active="false" class="responsive-button p-1.5 rounded-md text-gray-500 hover:bg-gray-200 data-[active='true']:bg-primary-100 data-[active='true']:text-primary-600" title="נייד">
                <i class="ri-smartphone-line text-lg"></i>
            </button>
        </div>
    </div>

    <!-- Left Side: Action Buttons and Logo -->
    <div class="header-actions flex items-center space-x-2 rtl:space-x-reverse">
        <button id="undo-button" class="header-action-button px-3 py-1.5 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md disabled:opacity-50 disabled:cursor-not-allowed" title="בטל" disabled>
            <i class="ri-arrow-go-back-line"></i>
            <!-- <span class="ml-1">בטל</span> -->
        </button>
        <button id="templates-button" class="header-action-button px-3 py-1.5 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
            <i class="ri-layout-masonry-line"></i>
            <span class="ml-1 rtl:mr-1">תבניות</span>
        </button>
        <button id="preview-button" class="header-action-button px-3 py-1.5 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
            <i class="ri-eye-line"></i>
            <span class="ml-1 rtl:mr-1">תצוגה מקדימה</span>
        </button>
        <button id="save-button" class="header-action-button px-4 py-1.5 text-sm text-white bg-green-500 hover:bg-green-600 rounded-md flex items-center">
            <i class="ri-save-line mr-1 rtl:ml-1"></i>
            <span>שמור שינויים</span>
        </button>
        <div class="header-logo ml-4 rtl:mr-4">
            <img src="../assets/images/logo.png" alt="Logo" style="height: 32px; width: auto;"> <!-- שינוי - גובה וגוון אוטומטי -->
        </div>
    </div>
</header>

<!-- Placeholder for Modals -->
<div id="preview-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full h-full max-w-full max-h-full overflow-hidden flex flex-col">
        <div class="flex justify-between items-center p-3 border-b">
            <h3 class="text-lg font-medium">תצוגה מקדימה</h3>
            <button id="close-preview-modal" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <div id="preview-content" class="flex-grow overflow-auto p-4 bg-gray-100"></div>
    </div>
</div>

<div id="templates-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full h-[90vh] max-w-5xl overflow-hidden flex flex-col">
        <div class="flex justify-between items-center p-3 border-b">
            <h3 class="text-lg font-medium">ספריית תבניות</h3>
            <div class="flex items-center space-x-2 rtl:space-x-reverse">
                <button class="text-gray-500 hover:text-gray-700 bg-gray-100 p-1.5 rounded-md" title="עזרה">
                    <i class="ri-question-line"></i>
                </button>
                <button id="close-templates-modal" class="text-gray-500 hover:text-gray-700 bg-gray-100 p-1.5 rounded-md" title="סגור">
                    <i class="ri-close-line"></i>
                </button>
            </div>
        </div>
        
        <div class="flex h-full">
            <!-- Sidebar -->
            <div class="w-64 border-e p-4 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-medium">תבניות</h4>
                    <div class="flex">
                        <button class="text-primary-600 hover:text-primary-700 p-1" title="ייבא תבנית">
                            <i class="ri-upload-line"></i>
                        </button>
                        <button class="text-primary-600 hover:text-primary-700 p-1" title="ייצא תבנית">
                            <i class="ri-download-line"></i>
                        </button>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <button class="w-full text-right py-2 px-3 rounded-md bg-primary-50 text-primary-600 hover:bg-primary-100 transition-colors">
                        <i class="ri-layout-masonry-line ml-2"></i>כל התבניות
                    </button>
                    <button class="w-full text-right py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100 transition-colors">
                        <i class="ri-pages-line ml-2"></i>דפים מלאים
                    </button>
                    <button class="w-full text-right py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100 transition-colors">
                        <i class="ri-layout-3-line ml-2"></i>אזורים
                    </button>
                    <button class="w-full text-right py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100 transition-colors">
                        <i class="ri-star-line ml-2"></i>מועדפים
                    </button>
                </div>
                
                <div class="mt-auto">
                    <div class="border-t pt-4">
                        <h4 class="font-medium mb-2">תבניות שלי</h4>
                        <div class="space-y-2">
                            <!-- Save Template Section -->
                            <div class="border p-3 rounded-md bg-gray-50">
                                <h5 class="text-sm font-medium mb-2">שמור תבנית נוכחית</h5>
                                <button id="save-template-button" class="w-full px-3 py-1.5 text-sm text-white bg-primary-500 hover:bg-primary-600 rounded-md">
                                    <i class="ri-save-line ml-1"></i>שמור כתבנית
                                </button>
                            </div>
                            <!-- Load Template Section -->
                            <div class="border p-3 rounded-md bg-gray-50">
                                <h5 class="text-sm font-medium mb-2">טען תבנית מקובץ</h5>
                                <input type="file" id="load-template-input" accept=".json" class="mb-2 block w-full text-sm text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"/>
                                <button id="load-template-button" class="w-full px-3 py-1.5 text-sm text-white bg-primary-500 hover:bg-primary-600 rounded-md disabled:opacity-50" disabled>
                                    <i class="ri-upload-line ml-1"></i>טען תבנית
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="flex-1 p-4 overflow-auto">
                <div class="mb-4">
                    <div class="relative">
                        <input type="text" placeholder="חפש תבניות..." class="w-full pl-10 pr-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="ri-search-line text-gray-400"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Templates Grid - Coming Soon Message -->
                <div class="mt-8 text-center py-16 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <i class="ri-layout-masonry-line text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-700 mb-2">ספריית תבניות</h3>
                    <p class="text-gray-500 max-w-md mx-auto">גריד התבניות יהיה זמין בקרוב. כאן יופיעו תבניות מוכנות מראש לבחירתך.</p>
                    <button class="mt-4 px-4 py-2 bg-primary-500 text-white rounded-md hover:bg-primary-600 inline-flex items-center">
                        <i class="ri-notification-line mr-2"></i>
                        עדכן אותי כשיהיה זמין
                    </button>
                </div>
            </div>
        </div>
    </div>
</div> 