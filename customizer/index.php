<?php
/**
 * Customizer Main Page
 * 
 * This file serves as the entry point for the Customizer interface.
 * Instead of a drag-and-drop builder, this provides pre-made templates
 * and section configurations that users can customize.
 */

// Include necessary files - commented out temporarily for debugging
// require_once(__DIR__ . '/../includes/header.php');

// Security check - commented out temporarily for debugging
// if (!current_user_can('edit_pages')) {
//     wp_die('אין לך הרשאות מתאימות לעריכת דפים.');
// }

$page_id = isset($_GET['page_id']) ? intval($_GET['page_id']) : 0;
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>מערכת התאמה אישית</title>
    
    <!-- Noto Sans Hebrew Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Hebrew:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3F6DFF',
                        'primary-dark': '#2951D4',
                        secondary: '#64748b',
                        dark: '#1e293b',
                        light: '#f1f5f9',
                    },
                    fontFamily: {
                        sans: ['Noto Sans Hebrew', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    
    <!-- Remix Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/customizer.css">
    
    <style>
        /* Additional inline styles */
        .device-frame {
            transition: all 0.3s;
            background-color: white;
            border-radius: 24px;
            margin: 0 auto;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        }
        
        .device-frame.mobile {
            width: 375px;
            height: 720px;
            border: 10px solid #1e293b;
        }
        
        .device-frame.tablet {
            width: 768px;
            height: 80vh;
            border: 12px solid #1e293b;
        }
        
        .device-frame.desktop {
            width: 100%;
            height: 80vh;
            border: 12px solid #1e293b;
            border-radius: 12px;
        }
        
        .device-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: #f1f5f9;
            color: #64748b;
            transition: all 0.3s;
            border: 1px solid #e2e8f0;
        }
        
        .device-button.active {
            background-color: #3F6DFF;
            color: white;
            border-color: #3F6DFF;
        }
        
        .accordion-header {
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .accordion-header:hover {
            background-color: #f8fafc;
        }
        
        .accordion-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        
        .accordion-body.open {
            max-height: 1000px;
        }
        
        .iframe-container {
            width: 100%;
            height: 100%;
            overflow: hidden;
            background-color: white;
        }
        
        .iframe-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800">

<!-- Main Container -->
<div class="flex flex-col h-screen">
    <!-- Top Bar -->
    <header class="bg-dark text-white px-6 py-3 flex items-center justify-between shadow-md">
        <div class="flex items-center gap-3">
            <div class="text-xl font-bold">QuickSite</div>
            <div class="text-sm text-gray-300">מערכת התאמה אישית</div>
        </div>
        <div class="flex items-center gap-3">
            <button id="save-template" class="px-4 py-2 rounded bg-green-600 hover:bg-green-700 transition">
                <i class="ri-save-line mr-1"></i> שמור שינויים
            </button>
            <button id="preview-template" class="px-4 py-2 rounded bg-primary hover:bg-primary-dark transition">
                <i class="ri-eye-line mr-1"></i> תצוגה מקדימה במסך מלא
            </button>
        </div>
    </header>

    <!-- Main Content -->
    <div class="flex flex-1 overflow-hidden">
        <!-- Left Sidebar - Sections -->
        <div class="w-64 bg-white border-l border-gray-200 overflow-y-auto">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-medium">סקשנים</h2>
                <p class="text-sm text-gray-500">בחר סקשן לעריכה</p>
            </div>
            <ul id="available-sections" class="divide-y divide-gray-200">
                <li data-section="header" class="p-4 hover:bg-gray-50 cursor-pointer transition flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="ri-layout-top-line text-xl text-gray-400"></i>
                        <span>כותרת עליונה</span>
                    </div>
                    <span class="section-status w-3 h-3 rounded-full bg-green-500" title="פעיל"></span>
                </li>
                <li data-section="hero" class="p-4 hover:bg-gray-50 cursor-pointer transition flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="ri-layout-masonry-line text-xl text-gray-400"></i>
                        <span>באנר ראשי</span>
                    </div>
                    <span class="section-status w-3 h-3 rounded-full bg-green-500" title="פעיל"></span>
                </li>
                <li data-section="features" class="p-4 hover:bg-gray-50 cursor-pointer transition flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="ri-layout-grid-line text-xl text-gray-400"></i>
                        <span>תכונות</span>
                    </div>
                    <span class="section-status w-3 h-3 rounded-full bg-gray-300" title="לא פעיל"></span>
                </li>
                <li data-section="testimonials" class="p-4 hover:bg-gray-50 cursor-pointer transition flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="ri-chat-quote-line text-xl text-gray-400"></i>
                        <span>המלצות</span>
                    </div>
                    <span class="section-status w-3 h-3 rounded-full bg-green-500" title="פעיל"></span>
                </li>
                <li data-section="gallery" class="p-4 hover:bg-gray-50 cursor-pointer transition flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="ri-gallery-line text-xl text-gray-400"></i>
                        <span>גלריה</span>
                    </div>
                    <span class="section-status w-3 h-3 rounded-full bg-gray-300" title="לא פעיל"></span>
                </li>
                <li data-section="videos" class="p-4 hover:bg-gray-50 cursor-pointer transition flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="ri-video-line text-xl text-gray-400"></i>
                        <span>סרטונים</span>
                    </div>
                    <span class="section-status w-3 h-3 rounded-full bg-green-500" title="פעיל"></span>
                </li>
                <li data-section="contact" class="p-4 hover:bg-gray-50 cursor-pointer transition flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="ri-contacts-line text-xl text-gray-400"></i>
                        <span>צור קשר</span>
                    </div>
                    <span class="section-status w-3 h-3 rounded-full bg-green-500" title="פעיל"></span>
                </li>
                <li data-section="footer" class="p-4 hover:bg-gray-50 cursor-pointer transition flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="ri-layout-bottom-line text-xl text-gray-400"></i>
                        <span>כותרת תחתונה</span>
                    </div>
                    <span class="section-status w-3 h-3 rounded-full bg-green-500" title="פעיל"></span>
                </li>
            </ul>
        </div>

        <!-- Center - Preview -->
        <div class="flex-1 flex flex-col">
            <!-- Toolbar with device selector and section navigation -->
            <div class="bg-white p-3 border-b border-gray-200 flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <button id="scroll-to-selected" class="px-3 py-1 bg-gray-100 rounded-md hover:bg-gray-200 flex items-center text-sm">
                        <i class="ri-focus-3-line mr-1"></i> גלול לסקשן נבחר
                    </button>
                    <div class="border-r border-gray-300 h-6 mx-2"></div>
                    <div class="text-sm text-gray-600">
                        סקשן נבחר: <span id="active-section-name">לא נבחר</span>
                    </div>
                </div>
                <div class="inline-flex items-center bg-gray-100 rounded-full p-1">
                    <button id="mobile-view" class="device-button active" aria-label="תצוגת מובייל">
                        <i class="ri-smartphone-line"></i>
                    </button>
                    <button id="tablet-view" class="device-button" aria-label="תצוגת טאבלט">
                        <i class="ri-tablet-line"></i>
                    </button>
                    <button id="desktop-view" class="device-button" aria-label="תצוגת מחשב">
                        <i class="ri-computer-line"></i>
                    </button>
                </div>
            </div>
            
            <!-- Preview area (full height) -->
            <div class="flex-1 overflow-auto p-6 bg-gray-100">
                <div class="device-frame mobile mx-auto">
                    <div class="iframe-container">
                        <iframe id="preview-iframe" src="../preview.php?page_id=<?php echo $page_id; ?>" title="תצוגה מקדימה"></iframe>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Sidebar - Settings -->
        <div class="w-80 bg-white border-r border-gray-200 overflow-y-auto">
            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-medium">הגדרות <span id="settings-section-name"></span></h2>
                    <p class="text-sm text-gray-500">התאם את הסקשן הנבחר</p>
                </div>
            </div>
            
            <div id="section-settings" class="p-5">
                <div class="text-center text-gray-500 py-10">
                    <i class="ri-settings-3-line text-5xl block mb-2"></i>
                    <p>בחר סקשן לעריכה</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Templates for settings panels -->
<template id="template-settings-panel">
    <div class="settings-panel space-y-5">
        <div class="flex items-center justify-between mb-4">
            <label class="flex items-center justify-between w-full">
                <span class="font-medium">הצג סקשן</span>
                <label class="toggle-switch">
                    <input type="checkbox" class="section-toggle" checked>
                    <span class="slider"></span>
                </label>
            </label>
        </div>
        
        <!-- Accordion for general settings -->
        <div class="accordion border rounded-lg overflow-hidden">
            <div class="accordion-header bg-gray-50 px-4 py-3 flex items-center justify-between">
                <span class="font-medium">הגדרות כלליות</span>
                <i class="ri-arrow-down-s-line"></i>
            </div>
            <div class="accordion-body">
                <div class="p-4 space-y-4 border-t border-gray-100">
                    <div class="form-group">
                        <label class="block mb-2 text-sm font-medium text-gray-700">כותרת הסקשן</label>
                        <input type="text" class="section-title w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary" value="">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Content items area -->
        <div class="content-items space-y-3">
            <!-- Will be populated based on section type -->
        </div>
    </div>
</template>

<template id="template-testimonials-items">
    <div class="accordion border rounded-lg overflow-hidden">
        <div class="accordion-header bg-gray-50 px-4 py-3 flex items-center justify-between">
            <span class="font-medium">המלצות</span>
            <i class="ri-arrow-down-s-line"></i>
        </div>
        <div class="accordion-body open">
            <div class="p-4 space-y-4 border-t border-gray-100">
                <div class="testimonials-list space-y-3">
                    <!-- Will be populated with items -->
                </div>
                <button class="add-item flex items-center justify-center w-full py-2 mt-2 border-2 border-dashed border-gray-300 rounded-md text-gray-500 hover:text-primary hover:border-primary transition" data-type="testimonial">
                    <i class="ri-add-line mr-1"></i> הוסף המלצה חדשה
                </button>
            </div>
        </div>
    </div>
</template>

<template id="template-videos-items">
    <div class="accordion border rounded-lg overflow-hidden">
        <div class="accordion-header bg-gray-50 px-4 py-3 flex items-center justify-between">
            <span class="font-medium">סרטונים</span>
            <i class="ri-arrow-down-s-line"></i>
        </div>
        <div class="accordion-body open">
            <div class="p-4 space-y-4 border-t border-gray-100">
                <div class="videos-list space-y-3">
                    <!-- Will be populated with items -->
                </div>
                <button class="add-item flex items-center justify-center w-full py-2 mt-2 border-2 border-dashed border-gray-300 rounded-md text-gray-500 hover:text-primary hover:border-primary transition" data-type="video">
                    <i class="ri-add-line mr-1"></i> הוסף סרטון חדש
                </button>
            </div>
        </div>
    </div>
</template>

<template id="template-item-row">
    <div class="item-row flex justify-between items-center p-3 bg-white border rounded-md">
        <div class="item-preview flex items-center">
            <div class="item-icon mr-2 text-gray-400"></div>
            <div class="item-title font-medium"></div>
        </div>
        <div class="item-actions flex gap-2">
            <button class="edit-item p-1 text-gray-500 hover:text-primary transition">
                <i class="ri-edit-line"></i>
            </button>
            <button class="delete-item p-1 text-gray-500 hover:text-red-500 transition">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>
    </div>
</template>

<!-- Include widget editors -->
<script src="widgets/testimonial-editor.js"></script>
<script src="widgets/video-editor.js"></script>

<!-- Main JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Device view switching
    const deviceFrame = document.querySelector('.device-frame');
    const mobileBtn = document.getElementById('mobile-view');
    const tabletBtn = document.getElementById('tablet-view');
    const desktopBtn = document.getElementById('desktop-view');
    
    mobileBtn.addEventListener('click', function() {
        deviceFrame.className = 'device-frame mobile';
        setActiveDeviceButton(this);
    });
    
    tabletBtn.addEventListener('click', function() {
        deviceFrame.className = 'device-frame tablet';
        setActiveDeviceButton(this);
    });
    
    desktopBtn.addEventListener('click', function() {
        deviceFrame.className = 'device-frame desktop';
        setActiveDeviceButton(this);
    });
    
    function setActiveDeviceButton(button) {
        document.querySelectorAll('.device-button').forEach(btn => {
            btn.classList.remove('active');
        });
        button.classList.add('active');
    }
    
    // Accordion functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.accordion-header')) {
            const header = e.target.closest('.accordion-header');
            const body = header.nextElementSibling;
            body.classList.toggle('open');
            
            // Toggle icon
            const icon = header.querySelector('i');
            if (icon) {
                if (body.classList.contains('open')) {
                    icon.className = 'ri-arrow-up-s-line';
                } else {
                    icon.className = 'ri-arrow-down-s-line';
                }
            }
        }
    });
    
    // Initialize customizer
    // The rest of your customizer.js code will be loaded via the script tag above
});
</script>
<script src="js/customizer.js"></script>

</body>
</html> 