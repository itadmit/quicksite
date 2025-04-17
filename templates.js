// templates.js
// מטפל בלוגיקה של מודאל התבניות (פתיחה/סגירה, אירועים)

import { stateToJson, jsonToState, downloadFile } from './import.js';
import { getPageState, loadPageState, renderPage } from './core.js'; // נצטרך לייצא את אלה מ-core.js

console.log('Templates module loaded');

let templatesModal = null;
let closeTemplatesBtn = null;
let saveTemplateBtn = null;
let loadTemplateInput = null;
let loadTemplateBtn = null;

/**
 * מאתחל את מודול התבניות, מחבר אירועים.
 */
export function initTemplates() {
    templatesModal = document.getElementById('templates-modal');
    closeTemplatesBtn = document.getElementById('close-templates-modal');
    saveTemplateBtn = document.getElementById('save-template-button');
    loadTemplateInput = document.getElementById('load-template-input');
    loadTemplateBtn = document.getElementById('load-template-button');

    if (!templatesModal || !closeTemplatesBtn || !saveTemplateBtn || !loadTemplateInput || !loadTemplateBtn) {
        console.error("Templates modal elements not found!");
        return;
    }

    // Open modal button (in header.php, handled likely in core.js or header-specific JS)
    const openTemplatesBtn = document.getElementById('templates-button');
    if (openTemplatesBtn) {
        openTemplatesBtn.addEventListener('click', openTemplatesModal);
    } else {
        console.warn('Open templates button not found in header');
    }

    // Close modal
    closeTemplatesBtn.addEventListener('click', closeTemplatesModal);
    templatesModal.addEventListener('click', (event) => {
        if (event.target === templatesModal) { // Click outside the content
            closeTemplatesModal();
        }
    });

    // Save template
    saveTemplateBtn.addEventListener('click', handleSaveTemplate);

    // Load template
    loadTemplateInput.addEventListener('change', handleFileSelect);
    loadTemplateBtn.addEventListener('click', handleLoadTemplate);
}

/**
 * פותח את מודאל התבניות.
 */
function openTemplatesModal() {
    if (templatesModal) {
        templatesModal.classList.remove('hidden');
        templatesModal.classList.add('flex');
        // Reset file input and button state
        loadTemplateInput.value = ''; 
        loadTemplateBtn.disabled = true;
    }
}

/**
 * סוגר את מודאל התבניות.
 */
function closeTemplatesModal() {
    if (templatesModal) {
        templatesModal.classList.add('hidden');
        templatesModal.classList.remove('flex');
    }
}

/**
 * מטפל בלחיצה על כפתור שמירת התבנית.
 */
function handleSaveTemplate() {
    const currentJson = stateToJson(getPageState()); // קבלת המצב הנוכחי
    if (currentJson) {
        downloadFile(currentJson, 'builder-template.json', 'application/json');
    }
}

/**
 * מאפשר את כפתור הטעינה כאשר נבחר קובץ.
 */
function handleFileSelect(event) {
    if (event.target.files && event.target.files.length > 0) {
        loadTemplateBtn.disabled = false;
    } else {
        loadTemplateBtn.disabled = true;
    }
}

/**
 * מטפל בלחיצה על כפתור טעינת התבנית.
 */
function handleLoadTemplate() {
    if (!loadTemplateInput.files || loadTemplateInput.files.length === 0) {
        alert('אנא בחר קובץ JSON לטעינה.');
        return;
    }

    const file = loadTemplateInput.files[0];
    const reader = new FileReader();

    reader.onload = (event) => {
        const jsonString = event.target.result;
        const newState = jsonToState(jsonString); // נסה להמיר ל-state

        if (newState) {
            if (confirm('טעינת תבנית תחליף את התוכן הנוכחי. האם להמשיך?')) {
                loadPageState(newState); // טען את המצב החדש
                renderPage(); // רנדר מחדש
                closeTemplatesModal(); // סגור את המודאל
            }
        }
        // אם newState הוא null, הודעת שגיאה כבר הוצגה ב-jsonToState
        
        // Reset input and disable button after attempt
        loadTemplateInput.value = ''; 
        loadTemplateBtn.disabled = true;
    };

    reader.onerror = (event) => {
        console.error("File reading error:", event.target.error);
        alert('שגיאה בקריאת הקובץ.');
        loadTemplateInput.value = ''; 
        loadTemplateBtn.disabled = true;
    };

    reader.readAsText(file);
} 