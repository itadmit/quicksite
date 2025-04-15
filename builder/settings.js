// builder/settings.js
// קובץ המכיל את הפונקציונליות של הגדרות שונות בבילדר

/**
 * הגדרות ברירת מחדל לעיצוב כללי
 */
const defaultStyleSettings = {
    // צבעים
    primaryColor: '#0ea5e9',
    secondaryColor: '#f59e0b',
    textColor: '#333333',
    backgroundColor: '#ffffff',
    
    // פונטים
    headingFont: 'Noto Sans Hebrew, sans-serif',
    bodyFont: 'Noto Sans Hebrew, sans-serif',
    
    // גדלים
    headingSize: '24px',
    bodySize: '16px',
    
    // מרווחים
    sectionSpacing: '40px',
    elementSpacing: '20px'
};

/**
 * הגדרות ברירת מחדל למטא-תגיות
 */
const defaultMetaSettings = {
    title: '',
    description: '',
    keywords: '',
    ogImage: '',
    customHead: ''
};

/**
 * הגדרות ברירת מחדל לאינטגרציות
 */
const defaultIntegrationSettings = {
    analytics: {
        googleAnalyticsId: '',
        facebookPixelId: '',
        customScript: ''
    },
    autoresponder: {
        type: 'none', // none, mailchimp, activecampaign, etc.
        apiKey: '',
        listId: '',
        customFields: []
    }
};

/**
 * הגדרות ברירת מחדל לאתר
 */
const defaultSiteSettings = {
    favicon: '',
    logo: '',
    footerText: '',
    rtl: true
};

/**
 * הצגת הגדרות עיצוב כלליות
 */
function showStyleSettings() {
    const settings = builder.settings ? builder.settings.style || {} : {};
    const mergedSettings = {...defaultStyleSettings, ...settings};
    
    const settingsPanel = document.getElementById('widget-settings');
    
    settingsPanel.innerHTML = `
        <div class="settings-group">
            <h3 class="settings-title">הגדרות צבעים</h3>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">צבע ראשי</span>
                <div class="flex items-center">
                    <input type="color" id="style-primaryColor" class="h-8 w-8 ml-2 border" value="${mergedSettings.primaryColor}">
                    <input type="text" id="style-primaryColor-hex" class="settings-input" value="${mergedSettings.primaryColor}">
                </div>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">צבע משני</span>
                <div class="flex items-center">
                    <input type="color" id="style-secondaryColor" class="h-8 w-8 ml-2 border" value="${mergedSettings.secondaryColor}">
                    <input type="text" id="style-secondaryColor-hex" class="settings-input" value="${mergedSettings.secondaryColor}">
                </div>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">צבע טקסט</span>
                <div class="flex items-center">
                    <input type="color" id="style-textColor" class="h-8 w-8 ml-2 border" value="${mergedSettings.textColor}">
                    <input type="text" id="style-textColor-hex" class="settings-input" value="${mergedSettings.textColor}">
                </div>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">צבע רקע</span>
                <div class="flex items-center">
                    <input type="color" id="style-backgroundColor" class="h-8 w-8 ml-2 border" value="${mergedSettings.backgroundColor}">
                    <input type="text" id="style-backgroundColor-hex" class="settings-input" value="${mergedSettings.backgroundColor}">
                </div>
            </label>
        </div>
        
        <div class="settings-group">
            <h3 class="settings-title">הגדרות פונטים</h3>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">פונט כותרות</span>
                <select id="style-headingFont" class="settings-select">
                    <option value="Noto Sans Hebrew, sans-serif" ${mergedSettings.headingFont === 'Noto Sans Hebrew, sans-serif' ? 'selected' : ''}>נוטו סאנס</option>
                    <option value="Rubik, sans-serif" ${mergedSettings.headingFont === 'Rubik, sans-serif' ? 'selected' : ''}>רוביק</option>
                    <option value="Heebo, sans-serif" ${mergedSettings.headingFont === 'Heebo, sans-serif' ? 'selected' : ''}>היבו</option>
                    <option value="Assistant, sans-serif" ${mergedSettings.headingFont === 'Assistant, sans-serif' ? 'selected' : ''}>אסיסטנט</option>
                    <option value="Varela Round, sans-serif" ${mergedSettings.headingFont === 'Varela Round, sans-serif' ? 'selected' : ''}>ורלה ראונד</option>
                </select>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">פונט טקסט רגיל</span>
                <select id="style-bodyFont" class="settings-select">
                    <option value="Noto Sans Hebrew, sans-serif" ${mergedSettings.bodyFont === 'Noto Sans Hebrew, sans-serif' ? 'selected' : ''}>נוטו סאנס</option>
                    <option value="Rubik, sans-serif" ${mergedSettings.bodyFont === 'Rubik, sans-serif' ? 'selected' : ''}>רוביק</option>
                    <option value="Heebo, sans-serif" ${mergedSettings.bodyFont === 'Heebo, sans-serif' ? 'selected' : ''}>היבו</option>
                    <option value="Assistant, sans-serif" ${mergedSettings.bodyFont === 'Assistant, sans-serif' ? 'selected' : ''}>אסיסטנט</option>
                    <option value="Varela Round, sans-serif" ${mergedSettings.bodyFont === 'Varela Round, sans-serif' ? 'selected' : ''}>ורלה ראונד</option>
                </select>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">גודל כותרות</span>
                <select id="style-headingSize" class="settings-select">
                    <option value="20px" ${mergedSettings.headingSize === '20px' ? 'selected' : ''}>קטן (20px)</option>
                    <option value="24px" ${mergedSettings.headingSize === '24px' ? 'selected' : ''}>בינוני (24px)</option>
                    <option value="32px" ${mergedSettings.headingSize === '32px' ? 'selected' : ''}>גדול (32px)</option>
                    <option value="40px" ${mergedSettings.headingSize === '40px' ? 'selected' : ''}>גדול מאוד (40px)</option>
                </select>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">גודל טקסט רגיל</span>
                <select id="style-bodySize" class="settings-select">
                    <option value="14px" ${mergedSettings.bodySize === '14px' ? 'selected' : ''}>קטן (14px)</option>
                    <option value="16px" ${mergedSettings.bodySize === '16px' ? 'selected' : ''}>בינוני (16px)</option>
                    <option value="18px" ${mergedSettings.bodySize === '18px' ? 'selected' : ''}>גדול (18px)</option>
                </select>
            </label>
        </div>
        
        <div class="settings-group">
            <h3 class="settings-title">מרווחים</h3>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">מרווח בין מקטעים</span>
                <select id="style-sectionSpacing" class="settings-select">
                    <option value="20px" ${mergedSettings.sectionSpacing === '20px' ? 'selected' : ''}>קטן (20px)</option>
                    <option value="40px" ${mergedSettings.sectionSpacing === '40px' ? 'selected' : ''}>בינוני (40px)</option>
                    <option value="60px" ${mergedSettings.sectionSpacing === '60px' ? 'selected' : ''}>גדול (60px)</option>
                    <option value="80px" ${mergedSettings.sectionSpacing === '80px' ? 'selected' : ''}>גדול מאוד (80px)</option>
                </select>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">מרווח בין אלמנטים</span>
                <select id="style-elementSpacing" class="settings-select">
                    <option value="10px" ${mergedSettings.elementSpacing === '10px' ? 'selected' : ''}>קטן (10px)</option>
                    <option value="20px" ${mergedSettings.elementSpacing === '20px' ? 'selected' : ''}>בינוני (20px)</option>
                    <option value="30px" ${mergedSettings.elementSpacing === '30px' ? 'selected' : ''}>גדול (30px)</option>
                </select>
            </label>
        </div>
        
        <div class="mt-4">
            <button id="update-style-settings" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded text-sm">
                החל עיצוב
            </button>
        </div>
    `;
    
    // אירועי שינוי צבע
    setupColorPickers();
    
    // כפתור עדכון
    document.getElementById('update-style-settings').addEventListener('click', function() {
        const newStyleSettings = {
            primaryColor: document.getElementById('style-primaryColor').value,
            secondaryColor: document.getElementById('style-secondaryColor').value,
            textColor: document.getElementById('style-textColor').value,
            backgroundColor: document.getElementById('style-backgroundColor').value,
            headingFont: document.getElementById('style-headingFont').value,
            bodyFont: document.getElementById('style-bodyFont').value,
            headingSize: document.getElementById('style-headingSize').value,
            bodySize: document.getElementById('style-bodySize').value,
            sectionSpacing: document.getElementById('style-sectionSpacing').value,
            elementSpacing: document.getElementById('style-elementSpacing').value
        };
        
        updateStyleSettings(newStyleSettings);
    });
}

/**
 * הגדרת אירועי שינוי צבע לכל בחירי הצבע
 */
function setupColorPickers() {
    // קביעת אירועי שינוי צבע
    document.getElementById('style-primaryColor').addEventListener('input', function() {
        document.getElementById('style-primaryColor-hex').value = this.value;
    });
    
    document.getElementById('style-primaryColor-hex').addEventListener('input', function() {
        document.getElementById('style-primaryColor').value = this.value;
    });
    
    document.getElementById('style-secondaryColor').addEventListener('input', function() {
        document.getElementById('style-secondaryColor-hex').value = this.value;
    });
    
    document.getElementById('style-secondaryColor-hex').addEventListener('input', function() {
        document.getElementById('style-secondaryColor').value = this.value;
    });
    
    document.getElementById('style-textColor').addEventListener('input', function() {
        document.getElementById('style-textColor-hex').value = this.value;
    });
    
    document.getElementById('style-textColor-hex').addEventListener('input', function() {
        document.getElementById('style-textColor').value = this.value;
    });
    
    document.getElementById('style-backgroundColor').addEventListener('input', function() {
        document.getElementById('style-backgroundColor-hex').value = this.value;
    });
    
    document.getElementById('style-backgroundColor-hex').addEventListener('input', function() {
        document.getElementById('style-backgroundColor').value = this.value;
    });
}

/**
 * עדכון הגדרות העיצוב
 * @param {Object} newSettings - הגדרות חדשות
 */
function updateStyleSettings(newSettings) {
    // יצירת אובייקט הגדרות אם לא קיים
    if (!builder.settings) {
        builder.settings = {};
    }
    
    // עדכון הגדרות עיצוב
    builder.settings.style = { 
        ...defaultStyleSettings,
        ...builder.settings.style,
        ...newSettings
    };
    
    // החלת העיצוב
    applyStyleSettings();
    
    // עדכון מצב שינויים
    builder.hasChanges = true;
    
    // הצגת הודעה
    showMessage('הגדרות העיצוב הוחלו בהצלחה', 'success');
}

/**
 * החלת הגדרות העיצוב על הקנבס
 */
function applyStyleSettings() {
    if (!builder.settings || !builder.settings.style) return;
    
    const style = builder.settings.style;
    
    // יצירת גיליון סגנון דינמי
    let styleElement = document.getElementById('dynamic-style');
    if (!styleElement) {
        styleElement = document.createElement('style');
        styleElement.id = 'dynamic-style';
        document.head.appendChild(styleElement);
    }
    
    // הגדרת CSS דינמי
    styleElement.textContent = `
        #canvas {
            background-color: ${style.backgroundColor};
            color: ${style.textColor};
        }
        
        #canvas h1, #canvas h2, #canvas h3, #canvas h4, #canvas h5, #canvas h6 {
            font-family: ${style.headingFont};
            margin-bottom: ${style.elementSpacing};
        }
        
        #canvas h1 {
            font-size: ${style.headingSize};
        }
        
        #canvas p, #canvas div, #canvas span, #canvas a {
            font-family: ${style.bodyFont};
            font-size: ${style.bodySize};
        }
        
        #canvas .row-wrapper {
            margin-bottom: ${style.sectionSpacing};
        }
        
        #canvas .widget-wrapper {
            margin-bottom: ${style.elementSpacing};
        }
        
        #canvas .primary-button {
            background-color: ${style.primaryColor};
            color: white;
        }
        
        #canvas .secondary-button {
            background-color: ${style.secondaryColor};
            color: white;
        }
    `;
    
    // עדכון משתני CSS לשימוש בכל הממשק
    document.documentElement.style.setProperty('--primary-color', style.primaryColor);
    document.documentElement.style.setProperty('--secondary-color', style.secondaryColor);
}

/**
 * הצגת הגדרות מטא ו-SEO
 */
function showMetaSettings() {
    const settings = builder.settings ? builder.settings.meta || {} : {};
    const mergedSettings = {...defaultMetaSettings, ...settings};
    
    const settingsPanel = document.getElementById('widget-settings');
    
    settingsPanel.innerHTML = `
        <div class="settings-group">
            <h3 class="settings-title">הגדרות SEO ומטא</h3>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">כותרת העמוד (title)</span>
                <input type="text" id="meta-title" class="settings-input" value="${mergedSettings.title}">
                <span class="text-xs text-gray-500">ישפיע על כותרת הדף בחיפוש וברשתות חברתיות</span>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">תיאור (description)</span>
                <textarea id="meta-description" class="settings-input h-24">${mergedSettings.description}</textarea>
                <span class="text-xs text-gray-500">תיאור קצר שיופיע בתוצאות חיפוש</span>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">מילות מפתח (keywords)</span>
                <input type="text" id="meta-keywords" class="settings-input" value="${mergedSettings.keywords}">
                <span class="text-xs text-gray-500">מילות מפתח מופרדות בפסיקים</span>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">תמונת שיתוף (Open Graph Image)</span>
                <div class="flex items-center">
                    <input type="text" id="meta-ogImage" class="settings-input flex-1 ml-2" value="${mergedSettings.ogImage}">
                    <button id="upload-og-image" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm">
                        <i class="fas fa-upload"></i>
                    </button>
                </div>
                <span class="text-xs text-gray-500">תמונה שתופיע בשיתוף ברשתות חברתיות</span>
            </label>
        </div>
        
        <div class="settings-group">
            <h3 class="settings-title">קוד מותאם אישית</h3>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">קוד HTML מותאם לתג head</span>
                <textarea id="meta-customHead" class="settings-input h-32 font-mono text-xs">${mergedSettings.customHead}</textarea>
                <span class="text-xs text-gray-500">הוסף קוד מותאם שיתווסף לתג head (למשל: קוד מעקב, פונטים חיצוניים וכו')</span>
            </label>
        </div>
        
        <div class="mt-4">
            <button id="update-meta-settings" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded text-sm">
                שמור הגדרות
            </button>
        </div>
    `;
    
    // אירוע העלאת תמונה
    document.getElementById('upload-og-image').addEventListener('click', function() {
        // פתיחת דיאלוג העלאת תמונה
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        
        input.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                // העלאת הקובץ לשרת
                uploadImage(this.files[0], function(response) {
                    if (response.success && response.url) {
                        document.getElementById('meta-ogImage').value = response.url;
                    } else {
                        showMessage('שגיאה בהעלאת התמונה: ' + (response.message || 'אירעה שגיאה לא ידועה'), 'error');
                    }
                });
            }
        });
        
        input.click();
    });
    
    // כפתור עדכון
    document.getElementById('update-meta-settings').addEventListener('click', function() {
        const newMetaSettings = {
            title: document.getElementById('meta-title').value,
            description: document.getElementById('meta-description').value,
            keywords: document.getElementById('meta-keywords').value,
            ogImage: document.getElementById('meta-ogImage').value,
            customHead: document.getElementById('meta-customHead').value
        };
        
        updateMetaSettings(newMetaSettings);
    });
}

/**
 * עדכון הגדרות מטא
 * @param {Object} newSettings - הגדרות חדשות
 */
function updateMetaSettings(newSettings) {
    // יצירת אובייקט הגדרות אם לא קיים
    if (!builder.settings) {
        builder.settings = {};
    }
    
    // עדכון הגדרות מטא
    builder.settings.meta = { 
        ...defaultMetaSettings,
        ...builder.settings.meta,
        ...newSettings
    };
    
    // עדכון מצב שינויים
    builder.hasChanges = true;
    
    // הצגת הודעה
    showMessage('הגדרות המטא נשמרו בהצלחה', 'success');
}

/**
 * הצגת הגדרות אינטגרציות
 */
function showIntegrationSettings() {
    const settings = builder.settings ? builder.settings.integration || {} : {};
    const mergedSettings = {...defaultIntegrationSettings, ...settings};
    
    const settingsPanel = document.getElementById('widget-settings');
    
    settingsPanel.innerHTML = `
        <div class="settings-group">
            <h3 class="settings-title">אינטגרציות אנליטיקה</h3>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">Google Analytics ID</span>
                <input type="text" id="integration-googleAnalyticsId" class="settings-input" value="${mergedSettings.analytics.googleAnalyticsId}" placeholder="UA-XXXXXXXX-X או G-XXXXXXXXXX">
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">Facebook Pixel ID</span>
                <input type="text" id="integration-facebookPixelId" class="settings-input" value="${mergedSettings.analytics.facebookPixelId}" placeholder="XXXXXXXXXXXXXXXXXX">
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">קוד מעקב מותאם אישית</span>
                <textarea id="integration-customScript" class="settings-input h-32 font-mono text-xs">${mergedSettings.analytics.customScript}</textarea>
                <span class="text-xs text-gray-500">הוסף קוד JavaScript מותאם אישית לניתוח נתונים</span>
            </label>
        </div>
        
        <div class="settings-group">
            <h3 class="settings-title">אינטגרציית אוטורספונדר</h3>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">סוג אוטורספונדר</span>
                <select id="integration-autoresponderType" class="settings-select">
                    <option value="none" ${mergedSettings.autoresponder.type === 'none' ? 'selected' : ''}>ללא</option>
                    <option value="mailchimp" ${mergedSettings.autoresponder.type === 'mailchimp' ? 'selected' : ''}>Mailchimp</option>
                    <option value="activecampaign" ${mergedSettings.autoresponder.type === 'activecampaign' ? 'selected' : ''}>ActiveCampaign</option>
                    <option value="convertkit" ${mergedSettings.autoresponder.type === 'convertkit' ? 'selected' : ''}>ConvertKit</option>
                    <option value="custom" ${mergedSettings.autoresponder.type === 'custom' ? 'selected' : ''}>אחר</option>
                </select>
            </label>
            
            <div id="autoresponder-settings" class="${mergedSettings.autoresponder.type === 'none' ? 'hidden' : ''}">
                <label class="block mb-2">
                    <span class="text-sm text-gray-700">מפתח API</span>
                    <input type="text" id="integration-apiKey" class="settings-input" value="${mergedSettings.autoresponder.apiKey}">
                </label>
                
                <label class="block mb-2">
                    <span class="text-sm text-gray-700">מזהה רשימה</span>
                    <input type="text" id="integration-listId" class="settings-input" value="${mergedSettings.autoresponder.listId}">
                </label>
                
                <div class="mb-2">
                    <span class="text-sm text-gray-700 block mb-1">שדות מותאמים אישית</span>
                    <div id="custom-fields-container">
                        ${renderCustomFields(mergedSettings.autoresponder.customFields)}
                    </div>
                    <button id="add-custom-field" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm mt-2">
                        <i class="fas fa-plus ml-1"></i>הוסף שדה מותאם
                    </button>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <button id="update-integration-settings" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded text-sm">
                שמור הגדרות
            </button>
        </div>
    `;
    
    // אירוע שינוי סוג אוטורספונדר
    document.getElementById('integration-autoresponderType').addEventListener('change', function() {
        const autoresponderSettings = document.getElementById('autoresponder-settings');
        if (this.value === 'none') {
            autoresponderSettings.classList.add('hidden');
        } else {
            autoresponderSettings.classList.remove('hidden');
        }
    });
    
    // אירוע הוספת שדה מותאם
    document.getElementById('add-custom-field').addEventListener('click', function() {
        const customFieldsContainer = document.getElementById('custom-fields-container');
        const fieldId = Date.now();
        
        const fieldHtml = `
            <div class="flex items-center mb-2" id="custom-field-${fieldId}">
                <input type="text" class="settings-input w-1/3 ml-2" placeholder="שם השדה" data-field-key>
                <input type="text" class="settings-input flex-1 ml-2" placeholder="קוד שדה" data-field-value>
                <button class="px-2 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded" onclick="removeCustomField('custom-field-${fieldId}')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        customFieldsContainer.insertAdjacentHTML('beforeend', fieldHtml);
    });
    
    // כפתור עדכון
    document.getElementById('update-integration-settings').addEventListener('click', function() {
        // איסוף שדות מותאמים
        const customFields = [];
        const customFieldElements = document.querySelectorAll('#custom-fields-container > div');
        
        customFieldElements.forEach(element => {
            const keyInput = element.querySelector('[data-field-key]');
            const valueInput = element.querySelector('[data-field-value]');
            
            if (keyInput.value && valueInput.value) {
                customFields.push({
                    key: keyInput.value,
                    value: valueInput.value
                });
            }
        });
        
        const newIntegrationSettings = {
            analytics: {
                googleAnalyticsId: document.getElementById('integration-googleAnalyticsId').value,
                facebookPixelId: document.getElementById('integration-facebookPixelId').value,
                customScript: document.getElementById('integration-customScript').value
            },
            autoresponder: {
                type: document.getElementById('integration-autoresponderType').value,
                apiKey: document.getElementById('integration-apiKey') ? document.getElementById('integration-apiKey').value : '',
                listId: document.getElementById('integration-listId') ? document.getElementById('integration-listId').value : '',
                customFields: customFields
            }
        };
        
        updateIntegrationSettings(newIntegrationSettings);
    });
}

/**
 * רינדור שדות מותאמים
 * @param {Array} customFields - מערך של שדות מותאמים
 * @returns {string} - HTML של השדות המותאמים
 */
function renderCustomFields(customFields) {
    if (!customFields || !customFields.length) {
        return '';
    }
    
    let html = '';
    
    customFields.forEach((field, index) => {
        const fieldId = `custom-field-${index}`;
        html += `
            <div class="flex items-center mb-2" id="${fieldId}">
                <input type="text" class="settings-input w-1/3 ml-2" placeholder="שם השדה" data-field-key value="${field.key}">
                <input type="text" class="settings-input flex-1 ml-2" placeholder="קוד שדה" data-field-value value="${field.value}">
                <button class="px-2 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded" onclick="removeCustomField('${fieldId}')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    });
    
    return html;
}

/**
 * הסרת שדה מותאם
 * @param {string} fieldId - מזהה השדה להסרה
 */
function removeCustomField(fieldId) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.remove();
    }
}

/**
 * עדכון הגדרות אינטגרציות
 * @param {Object} newSettings - הגדרות חדשות
 */
function updateIntegrationSettings(newSettings) {
    // יצירת אובייקט הגדרות אם לא קיים
    if (!builder.settings) {
        builder.settings = {};
    }
    
    // עדכון הגדרות אינטגרציות
    builder.settings.integration = { 
        ...defaultIntegrationSettings,
        ...builder.settings.integration,
        ...newSettings
    };
    
    // עדכון מצב שינויים
    builder.hasChanges = true;
    
    // הצגת הודעה
    showMessage('הגדרות האינטגרציות נשמרו בהצלחה', 'success');
}

/**
 * הצגת הגדרות כלליות לאתר
 */
function showSiteSettings() {
    const settings = builder.settings ? builder.settings.site || {} : {};
    const mergedSettings = {...defaultSiteSettings, ...settings};
    
    const settingsPanel = document.getElementById('widget-settings');
    
    settingsPanel.innerHTML = `
        <div class="settings-group">
            <h3 class="settings-title">הגדרות אתר</h3>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">לוגו האתר</span>
                <div class="flex items-center">
                    <input type="text" id="site-logo" class="settings-input flex-1 ml-2" value="${mergedSettings.logo}">
                    <button id="upload-logo" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm">
                        <i class="fas fa-upload"></i>
                    </button>
                </div>
                <span class="text-xs text-gray-500">תמונת הלוגו שתופיע בכותרת האתר</span>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">Favicon</span>
                <div class="flex items-center">
                    <input type="text" id="site-favicon" class="settings-input flex-1 ml-2" value="${mergedSettings.favicon}">
                    <button id="upload-favicon" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm">
                        <i class="fas fa-upload"></i>
                    </button>
                </div>
                <span class="text-xs text-gray-500">אייקון שיופיע בטאב של הדפדפן (מומלץ 32x32)</span>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">טקסט כותרת תחתונה</span>
                <input type="text" id="site-footerText" class="settings-input" value="${mergedSettings.footerText}">
                <span class="text-xs text-gray-500">טקסט שיופיע בתחתית האתר</span>
            </label>
            
            <label class="block mb-2 flex items-center">
                <input type="checkbox" id="site-rtl" class="ml-2" ${mergedSettings.rtl ? 'checked' : ''}>
                <span class="text-sm text-gray-700">כיוון האתר מימין לשמאל (RTL)</span>
            </label>
        </div>
        
        <div class="mt-4">
            <button id="update-site-settings" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded text-sm">
                שמור הגדרות
            </button>
        </div>
    `;
    
    // אירוע העלאת לוגו
    document.getElementById('upload-logo').addEventListener('click', function() {
        uploadImageFile('site-logo');
    });
    
    // אירוע העלאת favicon
    document.getElementById('upload-favicon').addEventListener('click', function() {
        uploadImageFile('site-favicon');
    });
    
    // כפתור עדכון
    document.getElementById('update-site-settings').addEventListener('click', function() {
        const newSiteSettings = {
            logo: document.getElementById('site-logo').value,
            favicon: document.getElementById('site-favicon').value,
            footerText: document.getElementById('site-footerText').value,
            rtl: document.getElementById('site-rtl').checked
        };
        
        updateSiteSettings(newSiteSettings);
    });
}

/**
 * פונקציה להעלאת קובץ תמונה וקישורו לשדה קלט
 * @param {string} inputId - מזהה שדה הקלט
 */
function uploadImageFile(inputId) {
    // פתיחת דיאלוג העלאת תמונה
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    
    input.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            // העלאת הקובץ לשרת
            uploadImage(this.files[0], function(response) {
                if (response.success && response.url) {
                    document.getElementById(inputId).value = response.url;
                } else {
                    showMessage('שגיאה בהעלאת התמונה: ' + (response.message || 'אירעה שגיאה לא ידועה'), 'error');
                }
            });
        }
    });
    
    input.click();
}

/**
 * עדכון הגדרות אתר
 * @param {Object} newSettings - הגדרות חדשות
 */
function updateSiteSettings(newSettings) {
    // יצירת אובייקט הגדרות אם לא קיים
    if (!builder.settings) {
        builder.settings = {};
    }
    
    // עדכון הגדרות אתר
    builder.settings.site = { 
        ...defaultSiteSettings,
        ...builder.settings.site,
        ...newSettings
    };
    
    // החלת הגדרות RTL
    document.documentElement.dir = newSettings.rtl ? 'rtl' : 'ltr';
    
    // עדכון מצב שינויים
    builder.hasChanges = true;
    
    // הצגת הודעה
    showMessage('הגדרות האתר נשמרו בהצלחה', 'success');
}

/**
 * הצגת הגדרות ייצוא ופרסום
 */
function showPublishSettings() {
    const settingsPanel = document.getElementById('widget-settings');
    
    settingsPanel.innerHTML = `
        <div class="settings-group">
            <h3 class="settings-title">פרסום והפצה</h3>
            
            <div class="mb-4">
                <h4 class="text-sm font-bold mb-2">כתובת URL נוכחית</h4>
                ${builder.contentId ? `
                <div class="flex items-center">
                    <input type="text" id="current-url" class="settings-input flex-1" value="${window.location.origin}/view.php?id=${builder.contentId}&type=${builder.type}" readonly>
                    <button id="copy-url" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm mr-2" onclick="copyToClipboard('current-url')">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                ` : `
                <p class="text-sm text-gray-500">שמור תחילה את הדף כדי לקבל כתובת URL</p>
                `}
            </div>
            
            <div class="mb-4">
                <h4 class="text-sm font-bold mb-2">ייצוא HTML</h4>
                <button id="export-html" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded text-sm">
                    <i class="fas fa-file-export ml-1"></i>ייצא כקובץ HTML
                </button>
                <p class="text-xs text-gray-500 mt-1">ייצא את הדף כקובץ HTML שניתן להעלות לאתר אחר</p>
            </div>
            
            <div class="mb-4">
                <h4 class="text-sm font-bold mb-2">שליחה לשרת</h4>
                <button id="publish-ftp" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded text-sm">
                    <i class="fas fa-upload ml-1"></i>פרסם לשרת FTP
                </button>
                <p class="text-xs text-gray-500 mt-1">פרסם ישירות לשרת שלך (דורש הגדרת חיבור FTP)</p>
            </div>
            
            <div class="mb-4">
                <h4 class="text-sm font-bold mb-2">התאמה לדומיין מותאם</h4>
                <div class="flex items-center">
                    <input type="text" id="custom-domain" class="settings-input flex-1 ml-2" placeholder="example.com" value="${builder.settings && builder.settings.publish && builder.settings.publish.customDomain || ''}">
                    <button id="save-domain" class="px-3 py-1 bg-primary-600 hover:bg-primary-700 text-white rounded text-sm">
                        שמור
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">הגדר דומיין מותאם לדף זה (דורש הגדרות DNS)</p>
            </div>
        </div>
    `;
    
    // אירוע ייצוא HTML
    document.getElementById('export-html').addEventListener('click', function() {
        exportToHtml();
    });
    
    // אירוע פרסום לשרת
    document.getElementById('publish-ftp').addEventListener('click', function() {
        showFtpPublishDialog();
    });
    
    // אירוע שמירת דומיין מותאם
    document.getElementById('save-domain').addEventListener('click', function() {
        const customDomain = document.getElementById('custom-domain').value;
        saveCustomDomain(customDomain);
    });
}

/**
 * העתקה ללוח
 * @param {string} elementId - מזהה האלמנט להעתקה
 */
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    document.execCommand('copy');
    
    showMessage('הטקסט הועתק ללוח', 'success');
}

/**
 * ייצוא הדף לקובץ HTML
 */
function exportToHtml() {
    // שאילתה לשרת לקבלת HTML מלא
    fetch('export_html.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            content: JSON.stringify(builder.content),
            settings: JSON.stringify(builder.settings || {}),
            type: builder.type
        })
    })
    .then(response => response.blob())
    .then(blob => {
        // יצירת קישור להורדה
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${builder.type === 'landing' ? 'landing-page' : 'email-template'}.html`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        
        showMessage('הדף יוצא בהצלחה לקובץ HTML', 'success');
    })
    .catch(error => {
        console.error('Error exporting HTML:', error);
        showMessage('שגיאה בייצוא HTML: ' + error.message, 'error');
    });
}

/**
 * הצגת דיאלוג פרסום FTP
 */
function showFtpPublishDialog() {
    // יצירת דיאלוג מודאלי
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="modal-overlay fixed inset-0 bg-black opacity-50"></div>
        <div class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded shadow-lg z-50 overflow-y-auto">
            <div class="modal-content p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">פרסום לשרת FTP</h3>
                    <button class="modal-close text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="ftp-form">
                    <div class="mb-4">
                        <label class="block text-sm text-gray-700 mb-1">שרת FTP</label>
                        <input type="text" name="ftp_host" class="settings-input" placeholder="ftp.example.com" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm text-gray-700 mb-1">שם משתמש</label>
                        <input type="text" name="ftp_user" class="settings-input" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm text-gray-700 mb-1">סיסמה</label>
                        <input type="password" name="ftp_pass" class="settings-input" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm text-gray-700 mb-1">תיקיית יעד</label>
                        <input type="text" name="ftp_dir" class="settings-input" placeholder="/public_html/" required>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <button type="button" class="modal-close px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded text-sm ml-2">
                            ביטול
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded text-sm">
                            פרסם
                        </button>
                    </div>
                </form>
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
    
    // אירוע שליחת טופס
    const form = document.getElementById('ftp-form');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        formData.append('content', JSON.stringify(builder.content));
        formData.append('settings', JSON.stringify(builder.settings || {}));
        formData.append('type', builder.type);
        
        // שליחה לשרת
        fetch('publish_ftp.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('הדף פורסם בהצלחה לשרת', 'success');
                document.body.removeChild(modal);
            } else {
                showMessage('שגיאה בפרסום לשרת: ' + (data.message || 'אירעה שגיאה לא ידועה'), 'error');
            }
        })
        .catch(error => {
            console.error('Error publishing to FTP:', error);
            showMessage('שגיאה בפרסום לשרת: ' + error.message, 'error');
        });
    });
}

/**
 * שמירת דומיין מותאם
 * @param {string} domain - הדומיין המותאם
 */
function saveCustomDomain(domain) {
    // בדיקת תקינות הדומיין
    if (domain && !/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i.test(domain)) {
        showMessage('שגיאה: הדומיין אינו תקין', 'error');
        return;
    }
    
    // שמירת הדומיין
    if (!builder.settings) {
        builder.settings = {};
    }
    
    if (!builder.settings.publish) {
        builder.settings.publish = {};
    }
    
    builder.settings.publish.customDomain = domain;
    builder.hasChanges = true;
    
    // עדכון הגדרות בשרת
    updateSettings();
    
    showMessage('הדומיין המותאם נשמר בהצלחה', 'success');
}

/**
 * עדכון כל ההגדרות בשרת
 */
function updateSettings() {
    // שמירת ההגדרות בשרת
    fetch('save_settings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: builder.contentId,
            type: builder.type,
            settings: builder.settings
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Error saving settings:', data.message);
        }
    })
    .catch(error => {
        console.error('Error updating settings:', error);
    });
}

/**
 * פנל תפריט הגדרות ראשי
 */
function showSettingsMenu() {
    const settingsPanel = document.getElementById('widget-settings');
    
    settingsPanel.innerHTML = `
        <div class="settings-menu">
            <h3 class="settings-title mb-4">הגדרות</h3>
            
            <div class="grid gap-3">
                <button class="p-3 bg-gray-50 hover:bg-gray-100 rounded border border-gray-200 flex items-center" onclick="showStyleSettings()">
                    <i class="fas fa-palette text-primary-500 ml-3 text-lg"></i>
                    <div>
                        <span class="font-medium block">עיצוב וסגנון</span>
                        <span class="text-xs text-gray-500">צבעים, פונטים ועיצוב כללי</span>
                    </div>
                </button>
                
                <button class="p-3 bg-gray-50 hover:bg-gray-100 rounded border border-gray-200 flex items-center" onclick="showMetaSettings()">
                    <i class="fas fa-search text-primary-500 ml-3 text-lg"></i>
                    <div>
                        <span class="font-medium block">מטא ו-SEO</span>
                        <span class="text-xs text-gray-500">כותרת, תיאור ותגיות מטא</span>
                    </div>
                </button>
                
                <button class="p-3 bg-gray-50 hover:bg-gray-100 rounded border border-gray-200 flex items-center" onclick="showIntegrationSettings()">
                    <i class="fas fa-plug text-primary-500 ml-3 text-lg"></i>
                    <div>
                        <span class="font-medium block">אינטגרציות</span>
                        <span class="text-xs text-gray-500">אנליטיקה ואוטורספונדרים</span>
                    </div>
                </button>
                
                <button class="p-3 bg-gray-50 hover:bg-gray-100 rounded border border-gray-200 flex items-center" onclick="showSiteSettings()">
                    <i class="fas fa-cog text-primary-500 ml-3 text-lg"></i>
                    <div>
                        <span class="font-medium block">הגדרות אתר</span>
                        <span class="text-xs text-gray-500">לוגו, פביקון וכותרת תחתונה</span>
                    </div>
                </button>
                
                <button class="p-3 bg-gray-50 hover:bg-gray-100 rounded border border-gray-200 flex items-center" onclick="showPublishSettings()">
                    <i class="fas fa-globe text-primary-500 ml-3 text-lg"></i>
                    <div>
                        <span class="font-medium block">פרסום והפצה</span>
                        <span class="text-xs text-gray-500">ייצוא, פרסום ושיתוף</span>
                    </div>
                </button>
            </div>
        </div>
    `;
}