// builder/templates.js
// מודול מינימלי לניהול תבניות - רק פתיחה וסגירה של המודל

// ייבוא פונקציות מינימליות מקובץ ה-import.js
import { downloadFile } from './import.js';

console.log('Templates module loaded');

// משתנים לשמירת האלמנטים של המודאל
let templatesModal = null;
let closeTemplatesBtn = null;
let saveTemplateBtn = null;
let loadTemplateInput = null;
let loadTemplateBtn = null;

/**
 * מאתחל את מודול התבניות: מוצא אלמנטים ומחבר מאזיני אירועים
 */
export function initTemplates() {
    console.log("Initializing Templates module...");
    templatesModal = document.getElementById('templates-modal');
    closeTemplatesBtn = document.getElementById('close-templates-modal');
    saveTemplateBtn = document.getElementById('save-template-button');
    loadTemplateInput = document.getElementById('load-template-input');
    loadTemplateBtn = document.getElementById('load-template-button');
    
    if (!templatesModal || !closeTemplatesBtn) {
        console.error("Templates modal elements not found! Cannot initialize templates module.");
        return;
    }

    // מאזין לכפתור פתיחת המודאל (נמצא בהדר)
    const openTemplatesBtn = document.getElementById('templates-button');
    if (openTemplatesBtn) {
        console.log("Adding click event listener to templates button");
        openTemplatesBtn.addEventListener('click', function() {
            console.log("Templates button clicked!");
            openTemplatesModal();
        });
    } else {
        console.warn(`Open templates button ('#templates-button') not found in header.`);
    }

    // מאזינים לסגירת המודאל
    closeTemplatesBtn.addEventListener('click', closeTemplatesModal);
    templatesModal.addEventListener('click', (event) => {
        // סגירה בלחיצה על הרקע השחור
        if (event.target === templatesModal) { 
            closeTemplatesModal();
        }
    });
    
    // מאזין לכפתור שמירת התבנית
    if (saveTemplateBtn) {
        saveTemplateBtn.addEventListener('click', handleSaveTemplate);
        console.log("Added click event listener to save template button");
    } else {
        console.warn("Save template button not found");
    }
    
    // מאזינים לבחירת קובץ וטעינתו
    if (loadTemplateInput && loadTemplateBtn) {
        loadTemplateInput.addEventListener('change', function(event) {
            // כאשר נבחר קובץ, מפעילים את כפתור הטעינה
            const fileSelected = event.target.files && event.target.files.length > 0;
            loadTemplateBtn.disabled = !fileSelected;
            
            if (fileSelected) {
                console.log("File selected:", event.target.files[0].name);
            }
        });
        
        loadTemplateBtn.addEventListener('click', handleLoadTemplate);
        console.log("Added event listeners for template loading");
    } else {
        console.warn("Template loading elements not found");
    }
    
    // הוספת מאזין לכפתור "עדכן אותי כשיהיה זמין"
    const notifyBtn = templatesModal?.querySelector('.mt-4.px-4.py-2.bg-primary-500');
    if (notifyBtn) {
        notifyBtn.addEventListener('click', function() {
            alert('תודה! נעדכן אותך כאשר ספריית התבניות תהיה זמינה.');
        });
    }

    // מאזינים לכל כפתורי הקטגוריות בסייד-באר
    const categoryButtons = templatesModal?.querySelectorAll('.w-64 .space-y-2 button');
    if (categoryButtons && categoryButtons.length > 0) {
        categoryButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                // הסרת הסגנון הפעיל מכל הכפתורים
                categoryButtons.forEach(b => {
                    b.classList.remove('bg-primary-50', 'text-primary-600');
                    b.classList.add('text-gray-700', 'hover:bg-gray-100');
                });
                
                // הוספת הסגנון הפעיל לכפתור הנוכחי
                this.classList.remove('text-gray-700', 'hover:bg-gray-100');
                this.classList.add('bg-primary-50', 'text-primary-600');
            });
        });
    }
    
    // מאזין לחיפוש תבניות
    const searchInput = templatesModal?.querySelector('input[placeholder="חפש תבניות..."]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            console.log("Search query:", this.value.trim());
        });
    }
    
    // מאזין לכפתורי ייבוא/ייצוא בפינה
    const importBtn = templatesModal?.querySelector('button[title="ייבא תבנית"]');
    const exportBtn = templatesModal?.querySelector('button[title="ייצא תבנית"]');
    
    if (importBtn) {
        importBtn.addEventListener('click', function() {
            // הפעלת לחיצה על אלמנט בחירת הקובץ
            if (loadTemplateInput) {
                loadTemplateInput.click();
            } else {
                alert('פונקציית ייבוא תבנית אינה זמינה כרגע');
            }
        });
    }
    
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            handleSaveTemplate();
        });
    }
    
    console.log("Templates module initialized successfully.");
}

/**
 * פותח את מודאל התבניות
 */
export function openTemplatesModal() {
    console.log("Opening templates modal...");
    if (!templatesModal) {
        console.error("Cannot open templates modal: templatesModal element is null");
        templatesModal = document.getElementById('templates-modal');
        if (!templatesModal) {
            console.error("Still cannot find templates modal element!");
            return;
        }
    }
    
    console.log("Templates modal before: ", templatesModal.classList.contains('hidden') ? "hidden" : "visible");
    templatesModal.classList.remove('hidden');
    templatesModal.classList.add('flex'); // שימוש ב-flex ליישור במרכז
    console.log("Templates modal after: ", templatesModal.classList.contains('hidden') ? "hidden" : "visible");
    
    // איפוס שדה ה-input וכפתור הטעינה
    if (loadTemplateInput) {
        loadTemplateInput.value = '';
    }
    
    if (loadTemplateBtn) {
        loadTemplateBtn.disabled = true;
    }
    
    console.log("Templates modal opened.");
}

/**
 * סוגר את מודאל התבניות
 */
function closeTemplatesModal() {
    console.log("Closing templates modal...");
    if (templatesModal) {
        templatesModal.classList.add('hidden');
        templatesModal.classList.remove('flex');
        console.log("Templates modal closed.");
    } else {
        console.error("Cannot close templates modal: templatesModal element is null");
    }
}

/**
 * מטפל בלחיצה על כפתור שמירת התבנית
 * מקבל את מצב העמוד ישירות מה-localStorage
 */
function handleSaveTemplate() {
    console.log("Save template button clicked");
    
    try {
        // קבלת המצב ישירות מה-localStorage
        const pageState = localStorage.getItem('builderPageState');
        
        if (!pageState) {
            console.error("Could not get page state from localStorage");
            alert('שגיאה: לא ניתן לגשת לנתוני העמוד הנוכחי');
            return;
        }
        
        // יצירת שם קובץ עם תאריך ושעה
        const now = new Date();
        const dateStr = now.toISOString().slice(0, 10).replace(/-/g, '');
        const timeStr = now.toTimeString().slice(0, 8).replace(/:/g, '');
        const fileName = `builder-template-${dateStr}-${timeStr}.json`;
        
        // הורדת הקובץ
        downloadFile(pageState, fileName, 'application/json');
        console.log("Template saved successfully");
        
    } catch (error) {
        console.error("Error saving template:", error);
        alert(`שגיאה בשמירת התבנית: ${error.message}`);
    }
}

/**
 * מטפל בלחיצה על כפתור טעינת התבנית
 * קורא את הקובץ הנבחר וטוען אותו ל-localStorage
 */
function handleLoadTemplate() {
    console.log("Load template button clicked");
    
    if (!loadTemplateInput || !loadTemplateInput.files || loadTemplateInput.files.length === 0) {
        alert('אנא בחר קובץ JSON לטעינה');
        return;
    }
    
    const file = loadTemplateInput.files[0];
    
    // בדיקת סוג הקובץ
    if (file.type !== 'application/json' && !file.name.endsWith('.json')) {
        alert('אנא בחר קובץ מסוג JSON בלבד');
        loadTemplateInput.value = '';
        loadTemplateBtn.disabled = true;
        return;
    }
    
    // קריאת הקובץ
    const reader = new FileReader();
    
    reader.onload = function(event) {
        try {
            const jsonContent = event.target.result;
            
            // בדיקה בסיסית שזה JSON תקין
            const parsedContent = JSON.parse(jsonContent);
            
            if (!Array.isArray(parsedContent)) {
                throw new Error('מבנה הנתונים אינו תקין - חייב להיות מערך');
            }
            
            // שאל את המשתמש לפני דריסת התוכן הקיים
            if (confirm('טעינת תבנית תחליף את התוכן הנוכחי בעמוד. האם להמשיך?')) {
                // שמירה ל-localStorage
                localStorage.setItem('builderPageState', jsonContent);
                console.log("Template loaded to localStorage successfully");
                
                // סגירת המודל
                closeTemplatesModal();
                
                // הודעה למשתמש
                alert('התבנית נטענה בהצלחה. העמוד יתרענן כעת.');
                
                // רענון העמוד כדי להציג את השינויים
                window.location.reload();
            }
        } catch (error) {
            console.error("Error parsing JSON file:", error);
            alert(`שגיאה בקריאת קובץ התבנית: ${error.message}`);
        } finally {
            // איפוס תיבת הקלט
            loadTemplateInput.value = '';
            loadTemplateBtn.disabled = true;
        }
    };
    
    reader.onerror = function(error) {
        console.error("Error reading file:", error);
        alert('שגיאה בקריאת הקובץ');
        loadTemplateInput.value = '';
        loadTemplateBtn.disabled = true;
    };
    
    reader.readAsText(file);
} 