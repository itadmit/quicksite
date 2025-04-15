// builder/landing_pages.js
// קובץ זה מכיל פונקציונליות ספציפית לדפי נחיתה

/**
 * אובייקט הגדרות דף נחיתה
 */
const landingPageSettings = {
    // הגדרות כלליות
    general: {
        title: '',
        description: '',
        favicon: '',
        language: 'he',
        direction: 'rtl'
    },
    
    // הגדרות עיצוב
    design: {
        headerFixed: false,
        navbarStyle: 'light',
        headerBackground: '#ffffff',
        footerBackground: '#f8f9fa',
        containerWidth: '1200px',
        buttonStyle: 'rounded',
        formStyle: 'shadow',
        effectsEnabled: true
    },
    
    // הגדרות SEO
    seo: {
        googleAnalyticsId: '',
        facebookPixelId: '',
        customHeadCode: '',
        customBodyCode: ''
    },
    
    // הגדרות למוביילים
    mobile: {
        hideSectionsOnMobile: [],
        customMobileStyles: ''
    }
};

/**
 * אתחול נוסף ספציפי לדפי נחיתה
 */
function initLandingPageBuilder() {
    // טעינת ספריות נוספות הדרושות לדפי נחיתה
    loadExtraLibraries();
    
    // הוספת לחצני תבניות
    addTemplateButtons();
    
    // אירועי ספציפיים לדפי נחיתה
    setupLandingPageEvents();
}

/**
 * טעינת ספריות נוספות לדפי נחיתה
 */
function loadExtraLibraries() {
    // טעינת ספריית אנימציה
    const animateCss = document.createElement('link');
    animateCss.rel = 'stylesheet';
    animateCss.href = 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css';
    document.head.appendChild(animateCss);
    
    // טעינת ספריית AOS לאנימציות גלילה
    const aosScript = document.createElement('script');
    aosScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js';
    document.head.appendChild(aosScript);
    
    const aosCss = document.createElement('link');
    aosCss.rel = 'stylesheet';
    aosCss.href = 'https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css';
    document.head.appendChild(aosCss);
    
    // אתחול AOS אחרי טעינת הדף
    window.addEventListener('load', function() {
        if (typeof AOS !== 'undefined') {
            AOS.init();
        }
    });
}

/**
 * הוספת לחצני תבניות לדפי נחיתה
 */
function addTemplateButtons() {
    const widgetsPanel = document.querySelector('#widgets-panel .grid');
    if (!widgetsPanel) return;
    
    // הוספת כותרת חדשה
    const templatesHeader = document.createElement('div');
    templatesHeader.className = 'font-bold text-gray-700 mt-6 mb-2';
    templatesHeader.textContent = 'תבניות מוכנות';
    widgetsPanel.appendChild(templatesHeader);
    
    // הוספת תבניות
    const templates = [
        { id: 'hero', title: 'כותר ראשי', icon: 'fas fa-star' },
        { id: 'features', title: 'תכונות', icon: 'fas fa-th-large' },
        { id: 'about', title: 'אודות', icon: 'fas fa-info-circle' },
        { id: 'testimonials', title: 'המלצות', icon: 'fas fa-quote-right' },
        { id: 'pricing', title: 'מחירים', icon: 'fas fa-tag' },
        { id: 'contact', title: 'צור קשר', icon: 'fas fa-envelope' },
        { id: 'faq', title: 'שאלות נפוצות', icon: 'fas fa-question-circle' },
        { id: 'cta', title: 'קריאה לפעולה', icon: 'fas fa-bullhorn' }
    ];
    
    templates.forEach(template => {
        const templateItem = document.createElement('div');
        templateItem.className = 'template-item';
        templateItem.setAttribute('draggable', 'true');
        templateItem.dataset.templateId = template.id;
        
        templateItem.innerHTML = `
            <div class="p-3 bg-gray-50 hover:bg-gray-100 rounded border border-gray-200 cursor-move flex items-center">
                <i class="${template.icon} text-primary-500 ml-3"></i>
                <span>${template.title}</span>
            </div>
        `;
        
        // אירועי גרירה
        templateItem.addEventListener('dragstart', function(e) {
            const templateId = this.dataset.templateId;
            e.dataTransfer.setData('template-id', templateId);
            this.classList.add('widget-dragging');
            builder.isDragging = true;
        });
        
        templateItem.addEventListener('dragend', function() {
            this.classList.remove('widget-dragging');
            clearColumnHighlights();
            builder.isDragging = false;
        });
        
        widgetsPanel.appendChild(templateItem);
    });
    
    // עדכון אירועי גרירה לתבניות
    setupTemplateDragEvents();
}

/**
 * הגדרת אירועי גרירה לתבניות דפי נחיתה
 */
function setupTemplateDragEvents() {
    const canvas = document.getElementById('canvas');
    
    // עדכון האירועים לתמיכה בגרירת תבניות
    canvas.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('column-highlight');
        
        const templateId = e.dataTransfer.getData('template-id');
        if (templateId) {
            // אם גוררים תבנית
            addTemplateToCanvas(templateId);
        }
    });
    
    // עדכון אירועי גרירה לעמודות
    const updateColumnDropEvent = function() {
        const columns = document.querySelectorAll('.column');
        
        columns.forEach(column => {
            // הוספת אירוע drop ספציפי לתבניות
            column.addEventListener('drop', function(e) {
                const templateId = e.dataTransfer.getData('template-id');
                if (templateId) {
                    e.preventDefault();
                    this.classList.remove('column-highlight');
                    
                    // הוספת התבנית לעמודה
                    const columnId = this.dataset.columnId;
                    addTemplateToColumn(columnId, templateId);
                }
            });
        });
    };
    
    // הוספת אירוע לאחר רינדור התוכן
    const originalRenderContent = renderContent;
    renderContent = function() {
        originalRenderContent();
        updateColumnDropEvent();
    };
    
    // עדכון אירועי גרירה ראשוני
    updateColumnDropEvent();
}

/**
 * הוספת תבנית לקנבס
 * @param {string} templateId - מזהה התבנית
 */
function addTemplateToCanvas(templateId) {
    // יצירת שורה חדשה
    const rowData = {
        type: 'row',
        id: generateUniqueId(),
        columns: [
            {
                id: generateUniqueId(),
                widgets: []
            }
        ]
    };
    
    // הוספת התבנית לעמודה
    const templateData = createTemplateData(templateId);
    rowData.columns[0].widgets = templateData.widgets || [];
    
    // הוספת השורה למערך התוכן
    builder.content.push(rowData);
    
    // עדכון תצוגה
    renderContent();
    builder.hasChanges = true;
}

/**
 * הוספת תבנית לעמודה
 * @param {string} columnId - מזהה העמודה
 * @param {string} templateId - מזהה התבנית
 */
function addTemplateToColumn(columnId, templateId) {
    // מציאת העמודה
    for (let i = 0; i < builder.content.length; i++) {
        const row = builder.content[i];
        if (row.type === 'row') {
            for (let j = 0; j < row.columns.length; j++) {
                const column = row.columns[j];
                if (column.id === columnId) {
                    // יצירת תבנית
                    const templateData = createTemplateData(templateId);
                    
                    // הוספת הווידג'טים של התבנית לעמודה
                    if (templateData.widgets && templateData.widgets.length > 0) {
                        templateData.widgets.forEach(widget => {
                            column.widgets.push(widget);
                        });
                    }
                    
                    // עדכון תצוגה
                    renderContent();
                    builder.hasChanges = true;
                    return;
                }
            }
        }
    }
}

/**
 * יצירת נתוני תבנית
 * @param {string} templateId - מזהה התבנית
 * @returns {Object} - אובייקט התבנית
 */
function createTemplateData(templateId) {
    // נתוני התבנית בהתאם למזהה
    switch (templateId) {
        case 'hero':
            return createHeroTemplate();
        case 'features':
            return createFeaturesTemplate();
        case 'about':
            return createAboutTemplate();
        case 'testimonials':
            return createTestimonialsTemplate();
        case 'pricing':
            return createPricingTemplate();
        case 'contact':
            return createContactTemplate();
        case 'faq':
            return createFaqTemplate();
        case 'cta':
            return createCtaTemplate();
        default:
            return { widgets: [] };
    }
}

/**
 * יצירת תבנית כותר ראשי
 * @returns {Object} - נתוני התבנית
 */
function createHeroTemplate() {
    return {
        widgets: [
            {
                id: generateUniqueId(),
                type: 'text',
                settings: {
                    content: 'ברוכים הבאים לאתר שלנו',
                    color: '#333333',
                    fontSize: '40px',
                    fontWeight: 'bold',
                    textAlign: 'center',
                    htmlTag: 'h1',
                    margin: '20px 0 10px 0'
                }
            },
            {
                id: generateUniqueId(),
                type: 'text',
                settings: {
                    content: 'תיאור קצר של המוצר או השירות שלך והערך שאתה מעניק ללקוחות',
                    color: '#666666',
                    fontSize: '20px',
                    textAlign: 'center',
                    margin: '0 0 30px 0'
                }
            },
            {
                id: generateUniqueId(),
                type: 'button',
                settings: {
                    text: 'לחץ כאן',
                    color: '#ffffff',
                    backgroundColor: '#0ea5e9',
                    textAlign: 'center',
                    url: '#',
                    size: 'large',
                    borderRadius: '30px',
                    margin: '10px auto 20px auto',
                    padding: '12px 30px'
                }
            },
            {
                id: generateUniqueId(),
                type: 'image',
                settings: {
                    src: '/assets/placeholder-hero.jpg',
                    alt: 'תמונת כותרת',
                    width: '100%',
                    height: 'auto',
                    alignment: 'center'
                }
            }
        ]
    };
}

/**
 * יצירת תבנית תכונות
 * @returns {Object} - נתוני התבנית
 */
function createFeaturesTemplate() {
    return {
        widgets: [
            {
                id: generateUniqueId(),
                type: 'text',
                settings: {
                    content: 'התכונות שלנו',
                    color: '#333333',
                    fontSize: '32px',
                    fontWeight: 'bold',
                    textAlign: 'center',
                    htmlTag: 'h2',
                    margin: '20px 0'
                }
            },
            {
                id: generateUniqueId(),
                type: 'text',
                settings: {
                    content: 'הסבר קצר על היתרונות והתכונות של המוצר או השירות שלך',
                    color: '#666666',
                    fontSize: '16px',
                    textAlign: 'center',
                    margin: '0 0 30px 0'
                }
            }
        ]
    };
}

/**
 * יצירת תבנית אודות
 * @returns {Object} - נתוני התבנית
 */
function createAboutTemplate() {
    return {
        widgets: [
            {
                id: generateUniqueId(),
                type: 'text',
                settings: {
                    content: 'אודותינו',
                    color: '#333333',
                    fontSize: '32px',
                    fontWeight: 'bold',
                    textAlign: 'center',
                    htmlTag: 'h2',
                    margin: '20px 0'
                }
            },
            {
                id: generateUniqueId(),
                type: 'text',
                settings: {
                    content: 'כאן מקום לספר על העסק שלך, ההיסטוריה, הערכים והחזון. טקסט זה צריך לעזור לבנות אמון ולהתחבר עם הלקוחות הפוטנציאליים שלך.',
                    color: '#666666',
                    fontSize: '16px',
                    textAlign: 'right',
                    lineHeight: '1.6',
                    margin: '20px 0'
                }
            },
            {
                id: generateUniqueId(),
                type: 'text',
                settings: {
                    content: 'אתה יכול להוסיף כאן מידע נוסף על הצוות, המומחיות והמחויבות שלך לאיכות ושירות מעולה.',
                    color: '#666666',
                    fontSize: '16px',
                    textAlign: 'right',
                    lineHeight: '1.6',
                    margin: '20px 0'
                }
            }
        ]
    };
}

/**
 * יצירת תבנית המלצות
 * @returns {Object} - נתוני התבנית
 */
function createTestimonialsTemplate() {
    return {
        widgets: [
            {
                id: generateUniqueId(),
                type: 'text',
                settings: {
                    content: 'מה הלקוחות שלנו אומרים',
                    color: '#333333',
                    fontSize: '32px',
                    fontWeight: 'bold',
                    textAlign: 'center',
                    htmlTag: 'h2',
                    margin: '20px 0'
                }
            },
            {
                id: generateUniqueId(),
                type: 'testimonial',
                settings: {
                    name: 'ישראל ישראלי',
                    role: 'מנכ"ל, חברת ABC',
                    content: 'השירות שקיבלתי היה מעולה! הצוות מקצועי, אדיב ותמיד זמין לענות על שאלות. אני ממליץ בחום!',
                    image: '/assets/avatar1.jpg'
                }
            },
            {
                id: generateUniqueId(),
                type: 'testimonial',
                settings: {
                    name: 'שרה כהן',
                    role: 'מנהלת שיווק, חברת XYZ',
                    content: 'המוצר שלכם חסך לנו זמן וכסף רב. הוא פשוט לשימוש ומשיג תוצאות מדהימות. נמשיך להשתמש בו ללא ספק!',
                    image: '/assets/avatar2.jpg'
                }
            }
        ]
    };
}

/**
 * יצירת תבנית מחירים
 * @returns {Object} - נתוני התבנית
 */
function createPricingTemplate() {
    // כאן צריך להוסיף מימוש לתבנית מחירים
    return {
        widgets: [
            {
                id: generateUniqueId(),
                type: 'text',
                settings: {
                    content: 'תוכניות המחירים שלנו',
                    color: '#333333',
                    fontSize: '32px',
                    fontWeight: 'bold',
                    textAlign: 'center',
                    htmlTag: 'h2',
                    margin: '20px 0'
                }
            },
            {
                id: generateUniqueId(),
                type: 'text',
                settings: {
                    content: 'בחר את התוכנית המתאימה לצרכים שלך',
                    color: '#666666',
                    fontSize: '16px',
                    textAlign: 'center',
                    margin: '0 0 30px 0'
                }
            }
        ]
    };
}

/**
 * יצירת תבנית צור קשר
 * @returns {Object} - נתוני התבנית
 */
function createContactTemplate() {
    return {
        widgets: [
            {
                id: generateUniqueId(),
                type: 'text',
                settings: {
                    content: 'צור קשר',
                    color: '#333333',
                    fontSize: '32px',
                    fontWeight: 'bold',
                    textAlign: 'center',
                    htmlTag: 'h2',
                    margin: '20px 0'
                }
            },
            {
                id: generateUniqueId(),
                type: 'text',
                settings: {
                    content: 'יש לך שאלות? נשמח לעזור! מלא את הטופס ונחזור אליך בהקדם.',
                    color: '#666666',
                    fontSize: '16px',
                    textAlign: 'center',
                    margin: '0 0 30px 0'
                }
            },
            {
                id: generateUniqueId(),
                type: 'form',
                settings: {
                    fields: [
                        { type: 'text', label: 'שם מלא', required: true },
                        { type: 'email', label: 'אימייל', required: true },
                        { type: 'tel', label: 'טלפון', required: false },
                        { type: 'textarea', label: 'הודעה', required: true }
                    ],
                    submitText: 'שלח',
                    successMessage: 'הטופס נשלח בהצלחה! נחזור אליך בהקדם.'
                }
            }
        ]
    };
}

/**
 * יצירת תבנית שאלות נפוצות
 * @returns {Object} - נתוני התבנית
 */
function createFaqTemplate() {
    return {
        widgets: [
            {
                id: generateUniqueId(),
                type: 'text',
                settings: {
                    content: 'שאלות נפוצות',
                    color: '#333333',
                    fontSize: '32px',
                    fontWeight: 'bold',
                    textAlign: 'center',
                    htmlTag: 'h2',
                    margin: '20px 0 30px 0'
                }
            },
            {
                id: generateUniqueId(),
                type: 'text',
                settings: {
                    content: 'שאלה 1: כיצד אפשר להתחיל להשתמש במוצר?',
                    color: '#333333',
                    fontSize: '20px',
                    fontWeight: 'bold',
                    htmlTag: 'h3',
                    margin: '20px 0 10px 0'
                }
            },
            {
                id: generateUniqueId(),
                type: 'text',
                settings: {
                    content: 'פשוט מאוד! הירשם באתר, בחר את התוכנית המתאימה לך, והתחל להשתמש במוצר באופן מיידי. אנחנו מציעים גם הדרכה אישית למתחילים.',
                    color: '#666666',
                    fontSize: '16px',
                    margin: '0 0 20px 0'
                }
            },
            {
                id: generateUniqueId(),
                type: 'text',
                settings: {
                    content: 'שאלה 2: מה כלול בתוכנית הבסיסית?',
                    color: '#333333',
                    fontSize: '20px',
                    fontWeight: 'bold',
                    htmlTag: 'h3',
                    margin: '20px 0 10px 0'
                }
            },
            {
                id: generateUniqueId(),
                type: 'text',
                settings: {
                    content: 'התוכנית הבסיסית כוללת את כל התכונות העיקריות של המוצר, תמיכה טכנית בדוא"ל, ועד 3 משתמשים. לפרטים נוספים, בקר בעמוד התוכניות והמחירים.',
                    color: '#666666',
                    fontSize: '16px',
                    margin: '0 0 20px 0'
                }
            }
        ]
    };
}

/**
 * יצירת תבנית קריאה לפעולה
 * @returns {Object} - נתוני התבנית
 */
function createCtaTemplate() {
    return {
        widgets: [
            {
                id: generateUniqueId(),
                type: 'text',
                settings: {
                    content: 'מוכנים להתחיל?',
                    color: '#333333',
                    fontSize: '32px',
                    fontWeight: 'bold',
                    textAlign: 'center',
                    htmlTag: 'h2',
                    margin: '20px 0 10px 0'
                }
            },
            {
                id: generateUniqueId(),
                type: 'text',
                settings: {
                    content: 'הצטרף היום וקבל 30 יום ניסיון בחינם ללא התחייבות!',
                    color: '#666666',
                    fontSize: '20px',
                    textAlign: 'center',
                    margin: '0 0 30px 0'
                }
            },
            {
                id: generateUniqueId(),
                type: 'button',
                settings: {
                    text: 'הירשם עכשיו',
                    color: '#ffffff',
                    backgroundColor: '#0ea5e9',
                    textAlign: 'center',
                    url: '#',
                    size: 'large',
                    borderRadius: '30px',
                    margin: '10px auto',
                    padding: '15px 40px'
                }
            }
        ]
    };
}

/**
 * הגדרת אירועים ספציפיים לדפי נחיתה
 */
function setupLandingPageEvents() {
    // הוספת כפתור מוביילים בכותרת לצפייה מקדימה במובייל
    const headerActions = document.querySelector('.header-actions');
    if (headerActions) {
        const mobilePreviewButton = document.createElement('button');
        mobilePreviewButton.id = 'btn-mobile-preview';
        mobilePreviewButton.className = 'px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm transition-colors';
        mobilePreviewButton.innerHTML = '<i class="fas fa-mobile-alt ml-1"></i>מובייל';
        
        // הכנסת הכפתור לפני כפתור התצוגה המקדימה
        const previewButton = document.getElementById('btn-preview');
        if (previewButton) {
            headerActions.insertBefore(mobilePreviewButton, previewButton);
        } else {
            headerActions.appendChild(mobilePreviewButton);
        }
        
        // אירוע לחיצה על כפתור תצוגה מקדימה במובייל
        mobilePreviewButton.addEventListener('click', function() {
            previewMobile();
        });
    }
    
    // אירוע להוספת הגדרות דף נחיתה לתפריט ההגדרות
    const settingsButton = document.getElementById('btn-settings');
    if (settingsButton) {
        settingsButton.addEventListener('click', function() {
            const settingsPanel = document.getElementById('settings-panel');
            if (!settingsPanel.classList.contains('hidden')) {
                setTimeout(() => {
                    addLandingPageSettingsToMenu();
                }, 100);
            }
        });
    }
}

/**
 * הוספת הגדרות דף נחיתה לתפריט ההגדרות
 */
function addLandingPageSettingsToMenu() {
    const settingsMenu = document.querySelector('.settings-menu .grid');
    if (!settingsMenu) return;
    
    // בדיקה אם כבר הוספנו את הכפתור
    if (document.getElementById('landing-page-settings-button')) return;
    
    // יצירת כפתור הגדרות דף נחיתה
    const landingPageSettingsButton = document.createElement('button');
    landingPageSettingsButton.id = 'landing-page-settings-button';
    landingPageSettingsButton.className = 'p-3 bg-gray-50 hover:bg-gray-100 rounded border border-gray-200 flex items-center';
    landingPageSettingsButton.onclick = showLandingPageSettings;
    landingPageSettingsButton.innerHTML = `
        <i class="fas fa-file-alt text-primary-500 ml-3 text-lg"></i>
        <div>
            <span class="font-medium block">הגדרות דף נחיתה</span>
            <span class="text-xs text-gray-500">כותרת, סגנון, התאמה למובייל</span>
        </div>
    `;
    
    // הוספת הכפתור לתפריט
    settingsMenu.appendChild(landingPageSettingsButton);
}

/**
 * הצגת הגדרות דף נחיתה
 */
function showLandingPageSettings() {
    const settings = builder.settings ? builder.settings.landingPage || {} : {};
    const mergedSettings = {
        general: {...landingPageSettings.general, ...settings.general},
        design: {...landingPageSettings.design, ...settings.design},
        mobile: {...landingPageSettings.mobile, ...settings.mobile}
    };
    
    const settingsPanel = document.getElementById('widget-settings');
    
    settingsPanel.innerHTML = `
        <div class="settings-tabs flex border-b border-gray-200 mb-4">
            <button id="tab-general" class="py-2 px-4 font-medium text-primary-600 border-b-2 border-primary-600">כללי</button>
            <button id="tab-design" class="py-2 px-4 font-medium text-gray-500 hover:text-gray-700">עיצוב</button>
            <button id="tab-mobile" class="py-2 px-4 font-medium text-gray-500 hover:text-gray-700">מובייל</button>
        </div>
        
        <div id="tab-general-content">
            <div class="settings-group">
                <h3 class="settings-title">הגדרות כלליות</h3>
                
                <label class="block mb-2">
                    <span class="text-sm text-gray-700">כותרת דף (title)</span>
                    <input type="text" id="landing-title" class="settings-input" value="${mergedSettings.general.title}">
                </label>
                
                <label class="block mb-2">
                    <span class="text-sm text-gray-700">תיאור (description)</span>
                    <textarea id="landing-description" class="settings-input h-24">${mergedSettings.general.description}</textarea>
                </label>
                
                <label class="block mb-2">
                    <span class="text-sm text-gray-700">שפה</span>
                    <select id="landing-language" class="settings-select">
                        <option value="he" ${mergedSettings.general.language === 'he' ? 'selected' : ''}>עברית</option>
                        <option value="en" ${mergedSettings.general.language === 'en' ? 'selected' : ''}>אנגלית</option>
                        <option value="ar" ${mergedSettings.general.language === 'ar' ? 'selected' : ''}>ערבית</option>
                        <option value="ru" ${mergedSettings.general.language === 'ru' ? 'selected' : ''}>רוסית</option>
                    </select>
                </label>
                
                <label class="block mb-2">
                    <span class="text-sm text-gray-700">כיוון</span>
                    <select id="landing-direction" class="settings-select">
                        <option value="rtl" ${mergedSettings.general.direction === 'rtl' ? 'selected' : ''}>ימין לשמאל (RTL)</option>
                        <option value="ltr" ${mergedSettings.general.direction === 'ltr' ? 'selected' : ''}>שמאל לימין (LTR)</option>
                    </select>
                </label>
            </div>
        </div>
        
        <div id="tab-design-content" class="hidden">
            <div class="settings-group">
                <h3 class="settings-title">הגדרות עיצוב</h3>
                
                <label class="block mb-2 flex items-center">
                    <input type="checkbox" id="landing-headerFixed" class="ml-2" ${mergedSettings.design.headerFixed ? 'checked' : ''}>
                    <span class="text-sm text-gray-700">כותרת צמודה (נשארת קבועה בגלילה)</span>
                </label>
                
                <label class="block mb-2">
                    <span class="text-sm text-gray-700">סגנון תפריט</span>
                    <select id="landing-navbarStyle" class="settings-select">
                        <option value="light" ${mergedSettings.design.navbarStyle === 'light' ? 'selected' : ''}>בהיר</option>
                        <option value="dark" ${mergedSettings.design.navbarStyle === 'dark' ? 'selected' : ''}>כהה</option>
                        <option value="transparent" ${mergedSettings.design.navbarStyle === 'transparent' ? 'selected' : ''}>שקוף</option>
                    </select>
                </label>
                
                <label class="block mb-2">
                    <span class="text-sm text-gray-700">רקע כותרת</span>
                    <div class="flex items-center">
                        <input type="color" id="landing-headerBackground" class="h-8 w-8 ml-2 border" value="${mergedSettings.design.headerBackground}">
                        <input type="text" id="landing-headerBackground-hex" class="settings-input" value="${mergedSettings.design.headerBackground}">
                    </div>
                </label>
                
                <label class="block mb-2">
                    <span class="text-sm text-gray-700">רקע כותרת תחתונה</span>
                    <div class="flex items-center">
                        <input type="color" id="landing-footerBackground" class="h-8 w-8 ml-2 border" value="${mergedSettings.design.footerBackground}">
                        <input type="text" id="landing-footerBackground-hex" class="settings-input" value="${mergedSettings.design.footerBackground}">
                    </div>
                </label>
                
                <label class="block mb-2">
                    <span class="text-sm text-gray-700">רוחב מכל (container)</span>
                    <select id="landing-containerWidth" class="settings-select">
                        <option value="1000px" ${mergedSettings.design.containerWidth === '1000px' ? 'selected' : ''}>צר (1000px)</option>
                        <option value="1200px" ${mergedSettings.design.containerWidth === '1200px' ? 'selected' : ''}>בינוני (1200px)</option>
                        <option value="1400px" ${mergedSettings.design.containerWidth === '1400px' ? 'selected' : ''}>רחב (1400px)</option>
                        <option value="100%" ${mergedSettings.design.containerWidth === '100%' ? 'selected' : ''}>מלא (100%)</option>
                    </select>
                </label>
                
                <label class="block mb-2">
                    <span class="text-sm text-gray-700">סגנון כפתורים</span>
                    <select id="landing-buttonStyle" class="settings-select">
                        <option value="rounded" ${mergedSettings.design.buttonStyle === 'rounded' ? 'selected' : ''}>מעוגל</option>
                        <option value="square" ${mergedSettings.design.buttonStyle === 'square' ? 'selected' : ''}>מרובע</option>
                        <option value="pill" ${mergedSettings.design.buttonStyle === 'pill' ? 'selected' : ''}>אליפסה</option>
                    </select>
                </label>
                
                <label class="block mb-2">
                    <span class="text-sm text-gray-700">סגנון טפסים</span>
                    <select id="landing-formStyle" class="settings-select">
                        <option value="shadow" ${mergedSettings.design.formStyle === 'shadow' ? 'selected' : ''}>צל</option>
                        <option value="outline" ${mergedSettings.design.formStyle === 'outline' ? 'selected' : ''}>מתאר</option>
                        <option value="minimal" ${mergedSettings.design.formStyle === 'minimal' ? 'selected' : ''}>מינימלי</option>
                    </select>
                </label>
                
                <label class="block mb-2 flex items-center">
                    <input type="checkbox" id="landing-effectsEnabled" class="ml-2" ${mergedSettings.design.effectsEnabled ? 'checked' : ''}>
                    <span class="text-sm text-gray-700">אפקטים ואנימציות</span>
                </label>
            </div>
        </div>
        
        <div id="tab-mobile-content" class="hidden">
            <div class="settings-group">
                <h3 class="settings-title">התאמה למובייל</h3>
                
                <div class="mb-4">
                    <span class="text-sm text-gray-700 block mb-1">הסתרת מקטעים במובייל</span>
                    <div id="hide-sections-container" class="border border-gray-200 rounded p-2 max-h-60 overflow-y-auto">
                        ${renderHideSectionsOptions(mergedSettings.mobile.hideSectionsOnMobile)}
                    </div>
                </div>
                
                <label class="block mb-2">
                    <span class="text-sm text-gray-700">CSS מותאם למובייל</span>
                    <textarea id="landing-customMobileStyles" class="settings-input h-40 font-mono text-xs">${mergedSettings.mobile.customMobileStyles || ''}</textarea>
                    <span class="text-xs text-gray-500">CSS שיופעל רק במכשירים ניידים (רוחב מסך עד 768px)</span>
                </label>
            </div>
        </div>
        
        <div class="mt-4">
            <button id="update-landing-settings" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded text-sm">
                שמור הגדרות
            </button>
        </div>
    `;
    
    // אירועי טאבים
    document.getElementById('tab-general').addEventListener('click', function() {
        switchTab('general');
    });
    
    document.getElementById('tab-design').addEventListener('click', function() {
        switchTab('design');
    });
    
    document.getElementById('tab-mobile').addEventListener('click', function() {
        switchTab('mobile');
    });
    
    // אירועי שינוי צבע
    setupColorPickers([
        ['landing-headerBackground', 'landing-headerBackground-hex'],
        ['landing-footerBackground', 'landing-footerBackground-hex']
    ]);
    
    // כפתור עדכון
    document.getElementById('update-landing-settings').addEventListener('click', function() {
        const newSettings = {
            general: {
                title: document.getElementById('landing-title').value,
                description: document.getElementById('landing-description').value,
                language: document.getElementById('landing-language').value,
                direction: document.getElementById('landing-direction').value
            },
            design: {
                headerFixed: document.getElementById('landing-headerFixed').checked,
                navbarStyle: document.getElementById('landing-navbarStyle').value,
                headerBackground: document.getElementById('landing-headerBackground').value,
                footerBackground: document.getElementById('landing-footerBackground').value,
                containerWidth: document.getElementById('landing-containerWidth').value,
                buttonStyle: document.getElementById('landing-buttonStyle').value,
                formStyle: document.getElementById('landing-formStyle').value,
                effectsEnabled: document.getElementById('landing-effectsEnabled').checked
            },
            mobile: {
                hideSectionsOnMobile: getHiddenSections(),
                customMobileStyles: document.getElementById('landing-customMobileStyles').value
            }
        };
        
        updateLandingPageSettings(newSettings);
    });
}

/**
 * החלפת טאבים בהגדרות דף נחיתה
 * @param {string} tabId - מזהה הטאב
 */
function switchTab(tabId) {
    // הסרת סימון מכל הטאבים
    document.querySelectorAll('.settings-tabs button').forEach(tab => {
        tab.classList.remove('text-primary-600', 'border-b-2', 'border-primary-600');
        tab.classList.add('text-gray-500', 'hover:text-gray-700');
    });
    
    // הסתרת כל התוכן
    document.getElementById('tab-general-content').classList.add('hidden');
    document.getElementById('tab-design-content').classList.add('hidden');
    document.getElementById('tab-mobile-content').classList.add('hidden');
    
    // סימון הטאב שנבחר והצגת התוכן שלו
    document.getElementById(`tab-${tabId}`).classList.remove('text-gray-500', 'hover:text-gray-700');
    document.getElementById(`tab-${tabId}`).classList.add('text-primary-600', 'border-b-2', 'border-primary-600');
    document.getElementById(`tab-${tabId}-content`).classList.remove('hidden');
}

/**
 * הגדרת אירועי שינוי צבע
 * @param {Array} pickers - מערך של זוגות בוחרי צבע [colorPicker, hexInput]
 */
function setupColorPickers(pickers) {
    pickers.forEach(([colorPickerId, hexInputId]) => {
        document.getElementById(colorPickerId).addEventListener('input', function() {
            document.getElementById(hexInputId).value = this.value;
        });
        
        document.getElementById(hexInputId).addEventListener('input', function() {
            document.getElementById(colorPickerId).value = this.value;
        });
    });
}

/**
 * רינדור אפשרויות להסתרת מקטעים במובייל
 * @param {Array} hiddenSections - מערך של מזהי מקטעים מוסתרים
 * @returns {string} - HTML של האפשרויות
 */
function renderHideSectionsOptions(hiddenSections = []) {
    // קבלת רשימת המקטעים (שורות) מהתוכן
    const sections = [];
    
    builder.content.forEach((row, index) => {
        if (row.type === 'row') {
            const sectionTitle = getSectionTitle(row);
            sections.push({
                id: row.id,
                title: sectionTitle || `מקטע ${index + 1}`,
                hidden: hiddenSections.includes(row.id)
            });
        }
    });
    
    if (sections.length === 0) {
        return '<div class="text-gray-500 text-sm p-2">אין מקטעים זמינים</div>';
    }
    
    let html = '';
    
    sections.forEach(section => {
        html += `
            <div class="flex items-center py-1">
                <input type="checkbox" id="hide-section-${section.id}" data-section-id="${section.id}" class="ml-2 hide-section-checkbox" ${section.hidden ? 'checked' : ''}>
                <label for="hide-section-${section.id}" class="text-sm cursor-pointer">${section.title}</label>
            </div>
        `;
    });
    
    return html;
}

/**
 * קבלת כותרת מקטע (שורה)
 * @param {Object} row - נתוני השורה
 * @returns {string|null} - כותרת המקטע או null אם אין
 */
function getSectionTitle(row) {
    // חיפוש ווידג'ט טקסט מסוג כותרת (h1, h2, h3)
    for (let i = 0; i < row.columns.length; i++) {
        const column = row.columns[i];
        for (let j = 0; j < column.widgets.length; j++) {
            const widget = column.widgets[j];
            if (widget.type === 'text' && widget.settings && 
                (widget.settings.htmlTag === 'h1' || widget.settings.htmlTag === 'h2' || widget.settings.htmlTag === 'h3')) {
                return widget.settings.content;
            }
        }
    }
    
    return null;
}

/**
 * קבלת מקטעים מוסתרים
 * @returns {Array} - מערך של מזהי מקטעים מוסתרים
 */
function getHiddenSections() {
    const hiddenSections = [];
    const checkboxes = document.querySelectorAll('.hide-section-checkbox');
    
    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            hiddenSections.push(checkbox.dataset.sectionId);
        }
    });
    
    return hiddenSections;
}

/**
 * עדכון הגדרות דף נחיתה
 * @param {Object} newSettings - הגדרות חדשות
 */
function updateLandingPageSettings(newSettings) {
    // יצירת אובייקט הגדרות אם לא קיים
    if (!builder.settings) {
        builder.settings = {};
    }
    
    // עדכון הגדרות דף נחיתה
    builder.settings.landingPage = { 
        ...builder.settings.landingPage,
        ...newSettings
    };
    
    // החלת הגדרות כיוון
    document.documentElement.dir = newSettings.general.direction;
    document.documentElement.lang = newSettings.general.language;
    
    // עדכון מצב שינויים
    builder.hasChanges = true;
    
    // הצגת הודעה
    showMessage('הגדרות דף הנחיתה נשמרו בהצלחה', 'success');
}

/**
 * תצוגה מקדימה במובייל
 */
function previewMobile() {
    // שמירה זמנית של התוכן לפני תצוגה מקדימה
    const tempData = {
        content: JSON.stringify(builder.content),
        settings: JSON.stringify(builder.settings || {}),
        id: 'temp_mobile',
        type: builder.type
    };
    
    // שליחת הנתונים לשרת והצגת תצוגה מקדימה
    fetch('save_temp_preview.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(tempData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.preview_url) {
            // פתיחת מודאל עם תצוגה מקדימה במובייל
            showMobilePreviewDialog(data.preview_url);
        } else {
            showMessage('שגיאה ביצירת תצוגה מקדימה: ' + (data.message || 'אירעה שגיאה לא ידועה'), 'error');
        }
    })
    .catch(error => {
        console.error('Error creating preview:', error);
        showMessage('שגיאה ביצירת תצוגה מקדימה: ' + error.message, 'error');
    });
}

/**
 * הצגת דיאלוג תצוגה מקדימה במובייל
 * @param {string} previewUrl - כתובת התצוגה המקדימה
 */
function showMobilePreviewDialog(previewUrl) {
    // יצירת דיאלוג מודאלי
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="modal-overlay fixed inset-0 bg-black opacity-50"></div>
        <div class="modal-container bg-white w-auto mx-auto rounded shadow-lg z-50 overflow-hidden">
            <div class="modal-content p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">תצוגה מקדימה במובייל</h3>
                    <button class="modal-close text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="mobile-preview-container bg-gray-100 p-4 rounded flex justify-center">
                    <div class="mobile-device bg-black rounded-2xl p-2 shadow-lg">
                        <div class="mobile-header flex justify-center rounded-lg mb-2">
                            <div class="w-16 h-6 bg-black rounded-b-xl"></div>
                        </div>
                        <iframe src="${previewUrl}" class="bg-white w-72 h-[600px] border-none rounded-lg" frameborder="0"></iframe>
                        <div class="mobile-footer flex justify-center mt-3">
                            <div class="w-16 h-1 bg-gray-500 rounded-full"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // אירועי סגירת מודאל
    const closeButtons = modal.querySelectorAll('.modal-close, .modal-overlay');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            document.body.removeChild(modal);
        });
    });
}

/**
 * ייצוא דף נחיתה לקובץ HTML
 */
function exportLandingPage() {
    // שאילתה לשרת לקבלת HTML מלא
    fetch('export_landing_page.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            content: JSON.stringify(builder.content),
            settings: JSON.stringify(builder.settings || {})
        })
    })
    .then(response => response.blob())
    .then(blob => {
        // יצירת קישור להורדה
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'landing_page.html';
        document.body.appendChild(a);
        a.click();
        a.remove();
        
        showMessage('דף הנחיתה יוצא בהצלחה לקובץ HTML', 'success');
    })
    .catch(error => {
        console.error('Error exporting landing page:', error);
        showMessage('שגיאה בייצוא דף הנחיתה: ' + error.message, 'error');
    });
}

/**
 * פרסום דף נחיתה
 */
function publishLandingPage() {
    // לפני פרסום יש לשמור את הדף
    if (builder.hasChanges) {
        showMessage('יש לשמור את השינויים לפני פרסום', 'warning');
        return;
    }
    
    // בדיקה שיש מזהה לדף
    if (!builder.contentId) {
        showMessage('יש לשמור את הדף תחילה לפני פרסום', 'warning');
        return;
    }
    
    // שליחת בקשת פרסום לשרת
    fetch('publish_landing_page.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: builder.contentId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showPublishSuccessDialog(data.publish_url);
        } else {
            showMessage('שגיאה בפרסום דף הנחיתה: ' + (data.message || 'אירעה שגיאה לא ידועה'), 'error');
        }
    })
    .catch(error => {
        console.error('Error publishing landing page:', error);
        showMessage('שגיאה בפרסום דף הנחיתה: ' + error.message, 'error');
    });
}

/**
 * הצגת דיאלוג הצלחת פרסום
 * @param {string} publishUrl - כתובת URL של הדף המפורסם
 */
function showPublishSuccessDialog(publishUrl) {
    // יצירת דיאלוג מודאלי
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="modal-overlay fixed inset-0 bg-black opacity-50"></div>
        <div class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded shadow-lg z-50 overflow-y-auto">
            <div class="modal-content p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">דף הנחיתה פורסם בהצלחה!</h3>
                    <button class="modal-close text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="mb-4">
                    <p class="mb-2">דף הנחיתה שלך זמין כעת בכתובת:</p>
                    <div class="flex items-center">
                        <input type="text" id="publish-url" class="settings-input flex-1" value="${publishUrl}" readonly>
                        <button class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm mr-2" onclick="copyToClipboard('publish-url')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-4">
                    <p class="mb-2">שתף ברשתות חברתיות:</p>
                    <div class="flex gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(publishUrl)}" target="_blank" class="bg-blue-600 text-white p-2 rounded">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=${encodeURIComponent(publishUrl)}" target="_blank" class="bg-blue-400 text-white p-2 rounded">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://wa.me/?text=${encodeURIComponent(publishUrl)}" target="_blank" class="bg-green-500 text-white p-2 rounded">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url=${encodeURIComponent(publishUrl)}" target="_blank" class="bg-blue-700 text-white p-2 rounded">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <a href="${publishUrl}" target="_blank" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded text-sm">
                        צפייה בדף המפורסם
                    </a>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // אירועי סגירת מודאל
    const closeButtons = modal.querySelectorAll('.modal-close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            document.body.removeChild(modal);
        });
    });
}