// builder/core.js
// קובץ ליבה המכיל את הפונקציונליות הבסיסית של הבילדר

let builder = {
    type: 'landing', // סוג הבילדר: landing או email
    contentId: 0,    // מזהה התוכן שנערך
    content: [],     // מערך התוכן
    selectedElement: null, // האלמנט הנבחר כרגע
    isDragging: false,    // האם יש גרירה פעילה
    hasChanges: false,    // האם יש שינויים שלא נשמרו
};

/**
 * אתחול הבילדר
 * @param {string} type - סוג הבילדר (landing או email)
 * @param {string} content - תוכן קיים (JSON)
 * @param {number} id - מזהה התוכן
 */
function initBuilder(type, content, id) {
    builder.type = type;
    builder.contentId = id;
    
    try {
        // בדיקה אם התוכן הוא מחרוזת או אובייקט
        if (typeof content === 'string') {
            builder.content = JSON.parse(content);
        } else {
            builder.content = content;
        }
    } catch (e) {
        console.error('שגיאה בניתוח התוכן:', e);
        builder.content = [];
    }
    
    // אתחול גרירת ווידג'טים
    initWidgetDragging();
    
    // רינדור התוכן הקיים
    renderContent();
    
    // אתחול אירועי לחיצה
    initClickEvents();
    
    // אתחול כפתורי פעולה
    initActionButtons();
    
    // מאזיני קלט לזיהוי שינויים
    window.addEventListener('beforeunload', function(e) {
        if (builder.hasChanges) {
            e.preventDefault();
            e.returnValue = 'יש לך שינויים שלא נשמרו. האם אתה בטוח שברצונך לעזוב?';
            return e.returnValue;
        }
    });
}

/**
 * אתחול גרירת ווידג'טים
 */
function initWidgetDragging() {
    // יצירת סורטבל עבור פנל הווידג'טים
    const widgetsContainer = document.querySelector('#widgets-panel .grid');
    
    // הפיכת הווידג'טים לגרירים
    const widgetItems = document.querySelectorAll('.widget-item');
    widgetItems.forEach(item => {
        item.setAttribute('draggable', 'true');
        
        item.addEventListener('dragstart', function(e) {
            const widgetType = this.dataset.widgetType;
            e.dataTransfer.setData('widget-type', widgetType);
            this.classList.add('widget-dragging');
            builder.isDragging = true;
        });
        
        item.addEventListener('dragend', function() {
            this.classList.remove('widget-dragging');
            clearColumnHighlights();
            builder.isDragging = false;
        });
    });
    
    // יצירת אירועי גרירה לקנבס
    setupCanvasDragEvents();
}

/**
 * הגדרת אירועי גרירה לקנבס
 */
function setupCanvasDragEvents() {
    const canvas = document.getElementById('canvas');
    
    // אם אין תוכן, נאפשר גרירה לקנבס הריק
    if (builder.content.length === 0) {
        canvas.addEventListener('dragover', function(e) {
            e.preventDefault();
            if (builder.isDragging) {
                this.classList.add('column-highlight');
            }
        });
        
        canvas.addEventListener('dragleave', function() {
            this.classList.remove('column-highlight');
        });
        
        canvas.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('column-highlight');
            
            const widgetType = e.dataTransfer.getData('widget-type');
            if (widgetType) {
                // כאשר גוררים רכיב לקנבס ריק, יש ליצור שורה חדשה
                addNewRow(1, widgetType);
            }
        });
    }
}

/**
 * הוספת שורה חדשה עם מספר עמודות מוגדר
 * @param {number} columnsCount - מספר העמודות בשורה
 * @param {string|null} widgetType - סוג הווידג'ט להוספה לעמודה הראשונה (אופציונלי)
 */
function addNewRow(columnsCount, widgetType = null) {
    // יצירת שורה חדשה
    const rowData = {
        type: 'row',
        id: generateUniqueId(),
        columns: []
    };
    
    // יצירת העמודות
    for (let i = 0; i < columnsCount; i++) {
        const columnData = {
            id: generateUniqueId(),
            widgets: []
        };
        
        // אם צוין סוג ווידג'ט והעמודה היא הראשונה, נוסיף אותו
        if (widgetType && i === 0) {
            const widgetData = createWidgetData(widgetType);
            columnData.widgets.push(widgetData);
        }
        
        rowData.columns.push(columnData);
    }
    
    // הוספת השורה למערך התוכן
    builder.content.push(rowData);
    
    // עדכון תצוגה
    renderContent();
    builder.hasChanges = true;
}

/**
 * יצירת נתוני ווידג'ט חדש
 * @param {string} widgetType - סוג הווידג'ט
 * @returns {Object} - אובייקט הווידג'ט
 */
function createWidgetData(widgetType) {
    return {
        type: widgetType,
        id: generateUniqueId(),
        settings: getDefaultWidgetSettings(widgetType)
    };
}

/**
 * קבלת הגדרות ברירת מחדל לווידג'ט
 * @param {string} widgetType - סוג הווידג'ט
 * @returns {Object} - הגדרות ברירת מחדל
 */
function getDefaultWidgetSettings(widgetType) {
    // כאן ניתן להגדיר ערכי ברירת מחדל לכל סוג ווידג'ט
    const defaults = {
        text: {
            content: 'טקסט לדוגמה',
            color: '#333333',
            fontSize: '16px',
            textAlign: 'right'
        },
        button: {
            text: 'לחץ כאן',
            color: '#ffffff',
            backgroundColor: '#0ea5e9',
            textAlign: 'center',
            url: '#',
            size: 'medium'
        },
        image: {
            src: '/assets/placeholder.jpg',
            alt: 'תמונה',
            width: '100%',
            height: 'auto'
        },
        video: {
            src: '',
            width: '100%',
            controls: true,
            autoplay: false
        },
        testimonial: {
            name: 'ישראל ישראלי',
            role: 'לקוח מרוצה',
            content: 'אני ממליץ בחום על השירות הזה!',
            image: '/assets/avatar.jpg'
        },
        countdown: {
            date: new Date(new Date().getTime() + 7 * 24 * 60 * 60 * 1000).toISOString(),
            format: 'days',
            textAlign: 'center'
        },
        form: {
            fields: [
                { type: 'text', label: 'שם מלא', required: true },
                { type: 'email', label: 'אימייל', required: true }
            ],
            submitText: 'שלח',
            successMessage: 'הטופס נשלח בהצלחה!'
        }
    };
    
    return defaults[widgetType] || {};
}

/**
 * רינדור התוכן
 */
function renderContent() {
    const canvas = document.getElementById('canvas');
    
    // ניקוי הקנבס
    canvas.innerHTML = '';
    
    if (builder.content.length === 0) {
        // אם אין תוכן, הצגת הודעה
        canvas.innerHTML = `
            <div class="flex justify-center items-center h-32 border-2 border-dashed border-gray-300 rounded bg-gray-50 text-gray-400">
                <div class="text-center">
                    <i class="fas fa-plus-circle text-2xl mb-2"></i>
                    <p>גרור רכיבים לכאן כדי להתחיל</p>
                </div>
            </div>
        `;
    } else {
        // רינדור כל השורות
        builder.content.forEach(row => {
            if (row.type === 'row') {
                renderRow(canvas, row);
            }
        });
    }
    
    // הגדרת אירועי גרירה לעמודות החדשות
    setupColumnsDropEvents();
    
    // הגדרת אירועי עריכה
    setupEditEvents();
}

/**
 * רינדור שורה
 * @param {HTMLElement} container - המכל להוספת השורה אליו
 * @param {Object} rowData - נתוני השורה
 */
function renderRow(container, rowData) {
    const rowWrapper = document.createElement('div');
    rowWrapper.className = 'row-wrapper bg-white mb-4 rounded shadow-sm fade-in';
    rowWrapper.dataset.rowId = rowData.id;
    
    // יצירת תצוגת העמודות בהתאם למספר העמודות
    const columnsCount = rowData.columns.length;
    const columnsClass = `grid-cols-${columnsCount}`;
    
    // כפתורי פעולה של השורה
    rowWrapper.innerHTML = `
        <div class="row-controls px-2 py-1 rounded-b flex gap-2 text-xs">
            <button class="p-1 hover:bg-primary-700 rounded" onclick="editRowSettings('${rowData.id}')">
                <i class="fas fa-cog"></i>
            </button>
            <button class="p-1 hover:bg-primary-700 rounded" onclick="duplicateRow('${rowData.id}')">
                <i class="fas fa-copy"></i>
            </button>
            <button class="p-1 hover:bg-primary-700 rounded" onclick="moveRowUp('${rowData.id}')">
                <i class="fas fa-arrow-up"></i>
            </button>
            <button class="p-1 hover:bg-primary-700 rounded" onclick="moveRowDown('${rowData.id}')">
                <i class="fas fa-arrow-down"></i>
            </button>
            <button class="p-1 hover:bg-primary-700 rounded" onclick="deleteRow('${rowData.id}')">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
        <div class="row-add-button row-add-top">
            <button class="bg-primary-500 hover:bg-primary-600 text-white rounded-full w-8 h-8 flex items-center justify-center shadow-md" onclick="addRowAbove('${rowData.id}')">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    `;
    
    // יצירת גריד העמודות
    const columnsContainer = document.createElement('div');
    columnsContainer.className = `grid ${columnsClass} gap-4 p-4`;
    
    // רינדור כל העמודות
    rowData.columns.forEach(column => {
        const columnEl = document.createElement('div');
        columnEl.className = 'column bg-gray-50 border border-gray-200 p-2 min-h-16 relative';
        columnEl.dataset.columnId = column.id;
        
        // רינדור ווידג'טים אם יש
        if (column.widgets && column.widgets.length > 0) {
            column.widgets.forEach(widget => {
                renderWidget(columnEl, widget);
            });
        } else {
            // עמודה ריקה - הצגת פלייסהולדר
            columnEl.innerHTML = `
                <div class="column-add-placeholder">
                    <div class="text-center">
                        <i class="fas fa-plus text-lg"></i>
                    </div>
                </div>
            `;
        }
        
        columnsContainer.appendChild(columnEl);
    });
    
    rowWrapper.appendChild(columnsContainer);
    
    // כפתור להוספת שורה חדשה מתחת
    const addBottomButton = document.createElement('div');
    addBottomButton.className = 'row-add-button row-add-bottom';
    addBottomButton.innerHTML = `
        <button class="bg-primary-500 hover:bg-primary-600 text-white rounded-full w-8 h-8 flex items-center justify-center shadow-md" onclick="addRowBelow('${rowData.id}')">
            <i class="fas fa-plus"></i>
        </button>
    `;
    rowWrapper.appendChild(addBottomButton);
    
    container.appendChild(rowWrapper);
}

/**
 * רינדור ווידג'ט בתוך עמודה
 * @param {HTMLElement} container - העמודה המכילה
 * @param {Object} widgetData - נתוני הווידג'ט
 */
function renderWidget(columnEl, widgetData) {
    const widgetWrapper = document.createElement('div');
    widgetWrapper.className = 'widget-wrapper relative mb-2';
    widgetWrapper.dataset.widgetId = widgetData.id;
    
    // כפתורי פעולה של הווידג'ט
    const widgetControls = document.createElement('div');
    widgetControls.className = 'widget-controls px-2 py-1 rounded-t flex gap-2 text-xs';
    widgetControls.innerHTML = `
        <button class="p-1 hover:bg-primary-700 rounded" onclick="editWidget('${widgetData.id}')">
            <i class="fas fa-cog"></i>
        </button>
        <button class="p-1 hover:bg-primary-700 rounded" onclick="duplicateWidget('${widgetData.id}')">
            <i class="fas fa-copy"></i>
        </button>
        <button class="p-1 hover:bg-primary-700 rounded" onclick="moveWidgetUp('${widgetData.id}')">
            <i class="fas fa-arrow-up"></i>
        </button>
        <button class="p-1 hover:bg-primary-700 rounded" onclick="moveWidgetDown('${widgetData.id}')">
            <i class="fas fa-arrow-down"></i>
        </button>
        <button class="p-1 hover:bg-primary-700 rounded" onclick="deleteWidget('${widgetData.id}')">
            <i class="fas fa-trash-alt"></i>
        </button>
    `;
    widgetWrapper.appendChild(widgetControls);
    
    // תוכן הווידג'ט
    const widgetContent = document.createElement('div');
    widgetContent.className = 'widget-content p-2 border border-gray-200 bg-white';
    
    // רינדור תוכן הווידג'ט בהתאם לסוג
    widgetContent.innerHTML = renderWidgetContent(widgetData);
    
    widgetWrapper.appendChild(widgetContent);
    columnEl.appendChild(widgetWrapper);
}

/**
 * רינדור תוכן הווידג'ט בהתאם לסוג
 * @param {Object} widgetData - נתוני הווידג'ט
 * @returns {string} - HTML של תוכן הווידג'ט
 */
function renderWidgetContent(widgetData) {
    const type = widgetData.type;
    const settings = widgetData.settings || {};
    
    // רינדור בהתאם לסוג הווידג'ט
    switch (type) {
        case 'text':
            return renderTextWidget(settings);
        case 'button':
            return renderButtonWidget(settings);
        case 'image':
            return renderImageWidget(settings);
        case 'video':
            return renderVideoWidget(settings);
        case 'testimonial':
            return renderTestimonialWidget(settings);
        case 'countdown':
            return renderCountdownWidget(settings);
        case 'form':
            return renderFormWidget(settings);
        default:
            return `<div class="p-4 text-center text-gray-500">ווידג'ט לא מוכר: ${type}</div>`;
    }
}

/**
 * הגדרת אירועי גרירה לעמודות
 */
function setupColumnsDropEvents() {
    const columns = document.querySelectorAll('.column');
    
    columns.forEach(column => {
        column.addEventListener('dragover', function(e) {
            e.preventDefault();
            if (builder.isDragging) {
                this.classList.add('column-highlight');
            }
        });
        
        column.addEventListener('dragleave', function() {
            this.classList.remove('column-highlight');
        });
        
        column.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('column-highlight');
            
            const widgetType = e.dataTransfer.getData('widget-type');
            if (widgetType) {
                const columnId = this.dataset.columnId;
                addWidgetToColumn(columnId, widgetType);
            }
        });
    });
}

/**
 * ניקוי הדגשות עמודות
 */
function clearColumnHighlights() {
    const columns = document.querySelectorAll('.column');
    columns.forEach(col => col.classList.remove('column-highlight'));
}

/**
 * הוספת ווידג'ט לעמודה
 * @param {string} columnId - מזהה העמודה
 * @param {string} widgetType - סוג הווידג'ט
 */
function addWidgetToColumn(columnId, widgetType) {
    // מציאת העמודה במודל הנתונים
    for (let i = 0; i < builder.content.length; i++) {
        const row = builder.content[i];
        if (row.type === 'row') {
            for (let j = 0; j < row.columns.length; j++) {
                const column = row.columns[j];
                if (column.id === columnId) {
                    // יצירת ווידג'ט חדש
                    const widgetData = createWidgetData(widgetType);
                    // הוספת הווידג'ט לעמודה
                    column.widgets.push(widgetData);
                    
                    // עדכון תצוגה
                    renderContent();
                    editWidget(widgetData.id); // פתיחת הגדרות הווידג'ט החדש
                    builder.hasChanges = true;
                    return;
                }
            }
        }
    }
}

/**
 * הגדרת אירועי עריכה
 */
function setupEditEvents() {
    // אירועי לחיצה על ווידג'טים לעריכה
    const widgetContents = document.querySelectorAll('.widget-content');
    widgetContents.forEach(content => {
        content.addEventListener('click', function(e) {
            // מניעת התקדמות האירוע אם לחצו על כפתור
            if (e.target.closest('button') && !e.target.closest('.edit-widget-trigger')) {
                return;
            }
            
            const widgetWrapper = this.closest('.widget-wrapper');
            if (widgetWrapper) {
                const widgetId = widgetWrapper.dataset.widgetId;
                editWidget(widgetId);
            }
        });
    });
}

/**
 * אתחול כפתורי פעולה
 */
function initActionButtons() {
    // כפתור שמירה
    const saveButton = document.getElementById('btn-save');
    if (saveButton) {
        saveButton.addEventListener('click', saveContent);
    }
    
    // כפתור תצוגה מקדימה
    const previewButton = document.getElementById('btn-preview');
    if (previewButton) {
        previewButton.addEventListener('click', previewContent);
    }
    
    // כפתור הגדרות
    const settingsButton = document.getElementById('btn-settings');
    if (settingsButton) {
        settingsButton.addEventListener('click', toggleSettingsPanel);
    }
}

/**
 * שמירת התוכן
 */
function saveContent() {
    const saveButton = document.getElementById('btn-save');
    const originalText = saveButton.innerHTML;
    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin ml-1"></i>שומר...';
    saveButton.disabled = true;
    
    // הכנת הנתונים לשמירה
    const contentData = {
        content: JSON.stringify(builder.content),
        id: builder.contentId,
        type: builder.type
    };
    
    // שליחת הנתונים לשרת
    fetch('save_content.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(contentData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // עדכון מצב שינויים
            builder.hasChanges = false;
            
            // עדכון כפתור
            saveButton.innerHTML = '<i class="fas fa-check ml-1"></i>נשמר';
            
            // הצגת הודעת הצלחה
            showMessage('התוכן נשמר בהצלחה!', 'success');
            
            // החזרת טקסט מקורי לכפתור אחרי 2 שניות
            setTimeout(() => {
                saveButton.innerHTML = originalText;
                saveButton.disabled = false;
            }, 2000);
            
            // אם זהו פריט חדש, עדכון ה-URL עם המזהה החדש
            if (!builder.contentId && data.id) {
                builder.contentId = data.id;
                const newUrl = window.location.pathname + '?id=' + data.id + '&type=' + builder.type;
                window.history.replaceState({}, '', newUrl);
            }
        } else {
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
            showMessage('שגיאה בשמירת התוכן: ' + (data.message || 'אירעה שגיאה לא ידועה'), 'error');
        }
    })
    .catch(error => {
        console.error('Error saving content:', error);
        saveButton.innerHTML = originalText;
        saveButton.disabled = false;
        showMessage('שגיאה בשמירת התוכן: ' + error.message, 'error');
    });
}

/**
 * תצוגה מקדימה של התוכן
 */
function previewContent() {
    // שמירה זמנית של התוכן לפני תצוגה מקדימה
    const tempData = {
        content: JSON.stringify(builder.content),
        id: 'temp',
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
            // פתיחת תצוגה מקדימה בחלון חדש
            window.open(data.preview_url, '_blank');
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
 * הצגת/הסתרת פנל הגדרות
 */
function toggleSettingsPanel() {
    const settingsPanel = document.getElementById('settings-panel');
    settingsPanel.classList.toggle('hidden');
    
    // אם הפנל מוצג, הצגת הגדרות כלליות
    if (!settingsPanel.classList.contains('hidden')) {
        showGeneralSettings();
    }
}

/**
 * הצגת הגדרות כלליות
 */
function showGeneralSettings() {
    const settingsPanel = document.getElementById('widget-settings');
    
    settingsPanel.innerHTML = `
        <div class="settings-group">
            <h3 class="settings-title">הגדרות כלליות</h3>
            <label class="block mb-2">
                <span class="text-sm text-gray-700">כותרת</span>
                <input type="text" id="general-title" class="settings-input" value="${document.title}">
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">רוחב קנבס</span>
                <select id="general-width" class="settings-select">
                    <option value="800px" ${document.getElementById('canvas').style.width === '800px' ? 'selected' : ''}>רגיל (800px)</option>
                    <option value="1000px" ${document.getElementById('canvas').style.width === '1000px' ? 'selected' : ''}>רחב (1000px)</option>
                    <option value="600px" ${document.getElementById('canvas').style.width === '600px' ? 'selected' : ''}>צר (600px)</option>
                </select>
            </label>
            
            <button class="px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded text-sm mt-2" onclick="applyGeneralSettings()">
                החל שינויים
            </button>
        </div>
        
        <div class="settings-group">
            <h3 class="settings-title">הוספת שורה</h3>
            <div class="grid grid-cols-3 gap-2 mt-2">
                <button class="p-2 bg-gray-100 hover:bg-gray-200 rounded text-center" onclick="addNewRow(1)">
                    <div class="border border-gray-300 p-1 mb-1">
                        <div class="bg-gray-300 h-4"></div>
                    </div>
                    <span class="text-xs">עמודה 1</span>
                </button>
                
                <button class="p-2 bg-gray-100 hover:bg-gray-200 rounded text-center" onclick="addNewRow(2)">
                    <div class="border border-gray-300 p-1 mb-1 grid grid-cols-2 gap-1">
                        <div class="bg-gray-300 h-4"></div>
                        <div class="bg-gray-300 h-4"></div>
                    </div>
                    <span class="text-xs">עמודות 2</span>
                </button>
                
                <button class="p-2 bg-gray-100 hover:bg-gray-200 rounded text-center" onclick="addNewRow(3)">
                    <div class="border border-gray-300 p-1 mb-1 grid grid-cols-3 gap-1">
                        <div class="bg-gray-300 h-4"></div>
                        <div class="bg-gray-300 h-4"></div>
                        <div class="bg-gray-300 h-4"></div>
                    </div>
                    <span class="text-xs">עמודות 3</span>
                </button>
            </div>
        </div>
    `;
}

/**
 * החלת הגדרות כלליות
 */
function applyGeneralSettings() {
    const title = document.getElementById('general-title').value;
    const width = document.getElementById('general-width').value;
    
    // עדכון כותרת הדף
    document.title = title;
    
    // עדכון רוחב הקנבס
    document.getElementById('canvas').style.width = width;
    
    // עדכון מצב שינויים
    builder.hasChanges = true;
    
    showMessage('ההגדרות הכלליות הוחלו בהצלחה', 'success');
}

/**
 * עריכת ווידג'ט
 * @param {string} widgetId - מזהה הווידג'ט
 */
function editWidget(widgetId) {
    // איפוס בחירה קודמת
    clearSelectedElement();
    
    // מציאת הווידג'ט
    const widgetData = findWidgetById(widgetId);
    if (!widgetData) return;
    
    // סימון הווידג'ט כנבחר
    const widgetEl = document.querySelector(`.widget-wrapper[data-widget-id="${widgetId}"]`);
    if (widgetEl) {
        widgetEl.classList.add('editing-active');
        builder.selectedElement = {
            type: 'widget',
            id: widgetId
        };
    }
    
    // הצגת פנל הגדרות אם הוא מוסתר
    const settingsPanel = document.getElementById('settings-panel');
    if (settingsPanel.classList.contains('hidden')) {
        settingsPanel.classList.remove('hidden');
    }
    
    // הצגת הגדרות הווידג'ט
    showWidgetSettings(widgetData);
}

/**
 * מציאת ווידג'ט לפי מזהה
 * @param {string} widgetId - מזהה הווידג'ט
 * @returns {Object|null} - אובייקט הווידג'ט או null אם לא נמצא
 */
function findWidgetById(widgetId) {
    for (let i = 0; i < builder.content.length; i++) {
        const row = builder.content[i];
        if (row.type === 'row') {
            for (let j = 0; j < row.columns.length; j++) {
                const column = row.columns[j];
                for (let k = 0; k < column.widgets.length; k++) {
                    const widget = column.widgets[k];
                    if (widget.id === widgetId) {
                        return widget;
                    }
                }
            }
        }
    }
    return null;
}

/**
 * ניקוי אלמנט נבחר
 */
function clearSelectedElement() {
    if (builder.selectedElement) {
        // הסרת סימון ויזואלי
        if (builder.selectedElement.type === 'widget') {
            const widgetEl = document.querySelector(`.widget-wrapper[data-widget-id="${builder.selectedElement.id}"]`);
            if (widgetEl) {
                widgetEl.classList.remove('editing-active');
            }
        } else if (builder.selectedElement.type === 'row') {
            const rowEl = document.querySelector(`.row-wrapper[data-row-id="${builder.selectedElement.id}"]`);
            if (rowEl) {
                rowEl.classList.remove('editing-active');
            }
        }
        
        // איפוס בחירה
        builder.selectedElement = null;
    }
}

/**
 * הצגת הגדרות ווידג'ט
 * @param {Object} widgetData - נתוני הווידג'ט
 */
function showWidgetSettings(widgetData) {
    const settingsPanel = document.getElementById('widget-settings');
    const type = widgetData.type;
    
    // הגדרות ספציפיות לסוג הווידג'ט
    switch (type) {
        case 'text':
            showTextWidgetSettings(settingsPanel, widgetData);
            break;
        case 'button':
            showButtonWidgetSettings(settingsPanel, widgetData);
            break;
        case 'image':
            showImageWidgetSettings(settingsPanel, widgetData);
            break;
        case 'video':
            showVideoWidgetSettings(settingsPanel, widgetData);
            break;
        case 'testimonial':
            showTestimonialWidgetSettings(settingsPanel, widgetData);
            break;
        case 'countdown':
            showCountdownWidgetSettings(settingsPanel, widgetData);
            break;
        case 'form':
            showFormWidgetSettings(settingsPanel, widgetData);
            break;
        default:
            settingsPanel.innerHTML = `<div class="p-4">אין הגדרות זמינות לווידג'ט זה</div>`;
    }
}

/**
 * עדכון הגדרות ווידג'ט
 * @param {string} widgetId - מזהה הווידג'ט
 * @param {Object} newSettings - הגדרות חדשות
 */
function updateWidgetSettings(widgetId, newSettings) {
    // מציאת הווידג'ט ועדכון ההגדרות
    for (let i = 0; i < builder.content.length; i++) {
        const row = builder.content[i];
        if (row.type === 'row') {
            for (let j = 0; j < row.columns.length; j++) {
                const column = row.columns[j];
                for (let k = 0; k < column.widgets.length; k++) {
                    const widget = column.widgets[k];
                    if (widget.id === widgetId) {
                        // מיזוג ההגדרות החדשות עם הקיימות
                        widget.settings = {...widget.settings, ...newSettings};
                        
                        // עדכון תצוגה
                        renderContent();
                        
                        // סימון הווידג'ט מחדש כנבחר
                        editWidget(widgetId);
                        
                        // עדכון מצב שינויים
                        builder.hasChanges = true;
                        return;
                    }
                }
            }
        }
    }
}

/**
 * יצירת מזהה ייחודי
 * @returns {string} - מזהה ייחודי
 */
function generateUniqueId() {
    return 'id_' + Math.random().toString(36).substr(2, 9);
}

/**
 * הצגת הודעה
 * @param {string} message - תוכן ההודעה
 * @param {string} type - סוג ההודעה (success, error, warning, info)
 */
function showMessage(message, type = 'info') {
    // בדיקה אם יש כבר אלמנט הודעות
    let messagesContainer = document.getElementById('messages-container');
    
    if (!messagesContainer) {
        // יצירת מכל הודעות אם לא קיים
        messagesContainer = document.createElement('div');
        messagesContainer.id = 'messages-container';
        messagesContainer.className = 'fixed bottom-4 left-4 z-50 flex flex-col gap-2';
        document.body.appendChild(messagesContainer);
    }
    
    // צבעים בהתאם לסוג ההודעה
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };
    
    // יצירת אלמנט ההודעה
    const messageEl = document.createElement('div');
    messageEl.className = `${colors[type] || colors.info} text-white px-4 py-2 rounded shadow-lg flex items-center fade-in`;
    
    // אייקון בהתאם לסוג
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    messageEl.innerHTML = `
        <i class="fas ${icons[type] || icons.info} ml-2"></i>
        <span>${message}</span>
    `;
    
    // הוספת ההודעה למכל
    messagesContainer.appendChild(messageEl);
    
    // הסרת ההודעה אחרי 3 שניות
    setTimeout(() => {
        messageEl.style.opacity = '0';
        messageEl.style.transform = 'translateX(-100%)';
        messageEl.style.transition = 'opacity 0.5s, transform 0.5s';
        
        setTimeout(() => {
            messagesContainer.removeChild(messageEl);
        }, 500);
    }, 3000);
}

/**
 * אתחול אירועי לחיצה
 */
function initClickEvents() {
    // לחיצה על הקנבס לניקוי בחירה
    document.getElementById('canvas').addEventListener('click', function(e) {
        // אם לחצו ישירות על הקנבס (לא על רכיב או שורה)
        if (e.target === this) {
            clearSelectedElement();
        }
    });
}

/**
 * פונקציות פעולה על שורות
 */

/**
 * הוספת שורה מעל שורה קיימת
 * @param {string} rowId - מזהה השורה הקיימת
 */
function addRowAbove(rowId) {
    // מציאת האינדקס של השורה הקיימת
    const rowIndex = findRowIndex(rowId);
    if (rowIndex === -1) return;
    
    // יצירת שורה חדשה
    const newRow = {
        type: 'row',
        id: generateUniqueId(),
        columns: [
            {
                id: generateUniqueId(),
                widgets: []
            }
        ]
    };
    
    // הוספת השורה לפני השורה הקיימת
    builder.content.splice(rowIndex, 0, newRow);
    
    // עדכון תצוגה
    renderContent();
    builder.hasChanges = true;
}

/**
 * הוספת שורה מתחת לשורה קיימת
 * @param {string} rowId - מזהה השורה הקיימת
 */
function addRowBelow(rowId) {
    // מציאת האינדקס של השורה הקיימת
    const rowIndex = findRowIndex(rowId);
    if (rowIndex === -1) return;
    
    // יצירת שורה חדשה
    const newRow = {
        type: 'row',
        id: generateUniqueId(),
        columns: [
            {
                id: generateUniqueId(),
                widgets: []
            }
        ]
    };
    
    // הוספת השורה אחרי השורה הקיימת
    builder.content.splice(rowIndex + 1, 0, newRow);
    
    // עדכון תצוגה
    renderContent();
    builder.hasChanges = true;
}

/**
 * מציאת אינדקס שורה לפי מזהה
 * @param {string} rowId - מזהה השורה
 * @returns {number} - אינדקס השורה או -1 אם לא נמצא
 */
function findRowIndex(rowId) {
    for (let i = 0; i < builder.content.length; i++) {
        if (builder.content[i].type === 'row' && builder.content[i].id === rowId) {
            return i;
        }
    }
    return -1;
}

/**
 * שכפול שורה
 * @param {string} rowId - מזהה השורה
 */
function duplicateRow(rowId) {
    // מציאת השורה
    const rowIndex = findRowIndex(rowId);
    if (rowIndex === -1) return;
    
    // העתקת השורה
    const originalRow = builder.content[rowIndex];
    const newRow = JSON.parse(JSON.stringify(originalRow)); // deep copy
    
    // יצירת מזהים חדשים
    newRow.id = generateUniqueId();
    for (let i = 0; i < newRow.columns.length; i++) {
        newRow.columns[i].id = generateUniqueId();
        for (let j = 0; j < newRow.columns[i].widgets.length; j++) {
            newRow.columns[i].widgets[j].id = generateUniqueId();
        }
    }
    
    // הוספת השורה אחרי המקורית
    builder.content.splice(rowIndex + 1, 0, newRow);
    
    // עדכון תצוגה
    renderContent();
    builder.hasChanges = true;
}

/**
 * הזזת שורה למעלה
 * @param {string} rowId - מזהה השורה
 */
function moveRowUp(rowId) {
    // מציאת האינדקס של השורה
    const rowIndex = findRowIndex(rowId);
    if (rowIndex <= 0) return; // אם זו השורה הראשונה, לא ניתן להזיז למעלה
    
    // החלפת השורה עם השורה שמעליה
    const temp = builder.content[rowIndex];
    builder.content[rowIndex] = builder.content[rowIndex - 1];
    builder.content[rowIndex - 1] = temp;
    
    // עדכון תצוגה
    renderContent();
    builder.hasChanges = true;
}

/**
 * הזזת שורה למטה
 * @param {string} rowId - מזהה השורה
 */
function moveRowDown(rowId) {
    // מציאת האינדקס של השורה
    const rowIndex = findRowIndex(rowId);
    if (rowIndex === -1 || rowIndex >= builder.content.length - 1) return; // אם זו השורה האחרונה, לא ניתן להזיז למטה
    
    // החלפת השורה עם השורה שמתחתיה
    const temp = builder.content[rowIndex];
    builder.content[rowIndex] = builder.content[rowIndex + 1];
    builder.content[rowIndex + 1] = temp;
    
    // עדכון תצוגה
    renderContent();
    builder.hasChanges = true;
}

/**
 * מחיקת שורה
 * @param {string} rowId - מזהה השורה
 */
function deleteRow(rowId) {
    // אישור מחיקה
    if (!confirm('האם אתה בטוח שברצונך למחוק שורה זו?')) {
        return;
    }
    
    // מציאת האינדקס של השורה
    const rowIndex = findRowIndex(rowId);
    if (rowIndex === -1) return;
    
    // מחיקת השורה
    builder.content.splice(rowIndex, 1);
    
    // עדכון תצוגה
    renderContent();
    builder.hasChanges = true;
}

/**
 * עריכת הגדרות שורה
 * @param {string} rowId - מזהה השורה
 */
function editRowSettings(rowId) {
    // איפוס בחירה קודמת
    clearSelectedElement();
    
    // מציאת האינדקס של השורה
    const rowIndex = findRowIndex(rowId);
    if (rowIndex === -1) return;
    
    const rowData = builder.content[rowIndex];
    
    // סימון השורה כנבחרת
    const rowEl = document.querySelector(`.row-wrapper[data-row-id="${rowId}"]`);
    if (rowEl) {
        rowEl.classList.add('editing-active');
        builder.selectedElement = {
            type: 'row',
            id: rowId
        };
    }
    
    // הצגת פנל הגדרות אם הוא מוסתר
    const settingsPanel = document.getElementById('settings-panel');
    if (settingsPanel.classList.contains('hidden')) {
        settingsPanel.classList.remove('hidden');
    }
    
    // הצגת הגדרות השורה
    showRowSettings(rowData);
}

/**
 * הצגת הגדרות שורה
 * @param {Object} rowData - נתוני השורה
 */
function showRowSettings(rowData) {
    const settingsPanel = document.getElementById('widget-settings');
    const settings = rowData.settings || {};
    
    settingsPanel.innerHTML = `
        <div class="settings-group">
            <h3 class="settings-title">הגדרות שורה</h3>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">מספר עמודות</span>
                <select id="row-columns-count" class="settings-select">
                    <option value="1" ${rowData.columns.length === 1 ? 'selected' : ''}>עמודה 1</option>
                    <option value="2" ${rowData.columns.length === 2 ? 'selected' : ''}>עמודות 2</option>
                    <option value="3" ${rowData.columns.length === 3 ? 'selected' : ''}>עמודות 3</option>
                </select>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">רקע</span>
                <div class="flex items-center">
                    <input type="color" id="row-backgroundColor" class="h-8 w-8 ml-2 border" value="${settings.backgroundColor || '#ffffff'}">
                    <input type="text" id="row-backgroundColor-hex" class="settings-input" value="${settings.backgroundColor || '#ffffff'}">
                </div>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">ריפוד פנימי (padding)</span>
                <input type="text" id="row-padding" class="settings-input" value="${settings.padding || '1rem'}">
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">מרווח בין עמודות (gap)</span>
                <input type="text" id="row-gap" class="settings-input" value="${settings.gap || '1rem'}">
            </label>
        </div>
        
        <div class="mt-4">
            <button id="update-row-settings" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded text-sm">
                החל שינויים
            </button>
        </div>
    `;
    
    // אירועי שינוי צבע
    document.getElementById('row-backgroundColor').addEventListener('input', function() {
        document.getElementById('row-backgroundColor-hex').value = this.value;
    });
    
    document.getElementById('row-backgroundColor-hex').addEventListener('input', function() {
        document.getElementById('row-backgroundColor').value = this.value;
    });
    
    // כפתור עדכון
    document.getElementById('update-row-settings').addEventListener('click', function() {
        // שמירת ההגדרות החדשות
        const newSettings = {
            backgroundColor: document.getElementById('row-backgroundColor').value,
            padding: document.getElementById('row-padding').value,
            gap: document.getElementById('row-gap').value
        };
        
        // עדכון מספר העמודות
        const newColumnsCount = parseInt(document.getElementById('row-columns-count').value);
        updateRowColumns(rowData.id, newColumnsCount);
        
        // עדכון הגדרות השורה
        updateRowSettings(rowData.id, newSettings);
    });
}

/**
 * עדכון מספר העמודות בשורה
 * @param {string} rowId - מזהה השורה
 * @param {number} newColumnsCount - מספר העמודות החדש
 */
function updateRowColumns(rowId, newColumnsCount) {
    // מציאת השורה
    const rowIndex = findRowIndex(rowId);
    if (rowIndex === -1) return;
    
    const rowData = builder.content[rowIndex];
    const currentColumnsCount = rowData.columns.length;
    
    // אם אין שינוי במספר העמודות
    if (newColumnsCount === currentColumnsCount) return;
    
    if (newColumnsCount > currentColumnsCount) {
        // הוספת עמודות
        for (let i = currentColumnsCount; i < newColumnsCount; i++) {
            rowData.columns.push({
                id: generateUniqueId(),
                widgets: []
            });
        }
    } else {
        // הסרת עמודות והעברת הווידג'טים לעמודה אחרת
        const columnsToRemove = currentColumnsCount - newColumnsCount;
        
        // העברת כל הווידג'טים לעמודה האחרונה שתישאר
        for (let i = newColumnsCount; i < currentColumnsCount; i++) {
            rowData.columns[newColumnsCount - 1].widgets = rowData.columns[newColumnsCount - 1].widgets.concat(rowData.columns[i].widgets);
        }
        
        // הסרת העמודות המיותרות
        rowData.columns.splice(newColumnsCount);
    }
    
    // עדכון תצוגה
    renderContent();
    builder.hasChanges = true;
}

/**
 * עדכון הגדרות שורה
 * @param {string} rowId - מזהה השורה
 * @param {Object} newSettings - הגדרות חדשות
 */
function updateRowSettings(rowId, newSettings) {
    // מציאת השורה
    const rowIndex = findRowIndex(rowId);
    if (rowIndex === -1) return;
    
    // אם אין הגדרות, יוצרים אובייקט חדש
    if (!builder.content[rowIndex].settings) {
        builder.content[rowIndex].settings = {};
    }
    
    // מיזוג ההגדרות החדשות עם הקיימות
    builder.content[rowIndex].settings = {
        ...builder.content[rowIndex].settings,
        ...newSettings
    };
    
    // עדכון תצוגה
    renderContent();
    
    // סימון השורה מחדש כנבחרת
    editRowSettings(rowId);
    
    // עדכון מצב שינויים
    builder.hasChanges = true;
}

/**
 * פונקציות פעולה על ווידג'טים
 */

/**
 * שכפול ווידג'ט
 * @param {string} widgetId - מזהה הווידג'ט
 */
function duplicateWidget(widgetId) {
    // מציאת הווידג'ט
    for (let i = 0; i < builder.content.length; i++) {
        const row = builder.content[i];
        if (row.type === 'row') {
            for (let j = 0; j < row.columns.length; j++) {
                const column = row.columns[j];
                for (let k = 0; k < column.widgets.length; k++) {
                    const widget = column.widgets[k];
                    if (widget.id === widgetId) {
                        // העתקת הווידג'ט
                        const newWidget = JSON.parse(JSON.stringify(widget)); // deep copy
                        newWidget.id = generateUniqueId();
                        
                        // הוספת הווידג'ט אחרי המקורי
                        column.widgets.splice(k + 1, 0, newWidget);
                        
                        // עדכון תצוגה
                        renderContent();
                        builder.hasChanges = true;
                        return;
                    }
                }
            }
        }
    }
}

/**
 * הזזת ווידג'ט למעלה
 * @param {string} widgetId - מזהה הווידג'ט
 */
function moveWidgetUp(widgetId) {
    // מציאת הווידג'ט
    for (let i = 0; i < builder.content.length; i++) {
        const row = builder.content[i];
        if (row.type === 'row') {
            for (let j = 0; j < row.columns.length; j++) {
                const column = row.columns[j];
                for (let k = 0; k < column.widgets.length; k++) {
                    if (column.widgets[k].id === widgetId) {
                        // אם זה הווידג'ט הראשון בעמודה, לא ניתן להזיז למעלה
                        if (k === 0) return;
                        
                        // החלפת הווידג'ט עם הווידג'ט שמעליו
                        const temp = column.widgets[k];
                        column.widgets[k] = column.widgets[k - 1];
                        column.widgets[k - 1] = temp;
                        
                        // עדכון תצוגה
                        renderContent();
                        builder.hasChanges = true;
                        return;
                    }
                }
            }
        }
    }
}

/**
 * הזזת ווידג'ט למטה
 * @param {string} widgetId - מזהה הווידג'ט
 */
function moveWidgetDown(widgetId) {
    // מציאת הווידג'ט
    for (let i = 0; i < builder.content.length; i++) {
        const row = builder.content[i];
        if (row.type === 'row') {
            for (let j = 0; j < row.columns.length; j++) {
                const column = row.columns[j];
                for (let k = 0; k < column.widgets.length; k++) {
                    if (column.widgets[k].id === widgetId) {
                        // אם זה הווידג'ט האחרון בעמודה, לא ניתן להזיז למטה
                        if (k === column.widgets.length - 1) return;
                        
                        // החלפת הווידג'ט עם הווידג'ט שמתחתיו
                        const temp = column.widgets[k];
                        column.widgets[k] = column.widgets[k + 1];
                        column.widgets[k + 1] = temp;
                        
                        // עדכון תצוגה
                        renderContent();
                        builder.hasChanges = true;
                        return;
                    }
                }
            }
        }
    }
}

/**
 * מחיקת ווידג'ט
 * @param {string} widgetId - מזהה הווידג'ט
 */
function deleteWidget(widgetId) {
    // אישור מחיקה
    if (!confirm('האם אתה בטוח שברצונך למחוק רכיב זה?')) {
        return;
    }
    
    // מציאת הווידג'ט
    for (let i = 0; i < builder.content.length; i++) {
        const row = builder.content[i];
        if (row.type === 'row') {
            for (let j = 0; j < row.columns.length; j++) {
                const column = row.columns[j];
                for (let k = 0; k < column.widgets.length; k++) {
                    if (column.widgets[k].id === widgetId) {
                        // מחיקת הווידג'ט
                        column.widgets.splice(k, 1);
                        
                        // עדכון תצוגה
                        renderContent();
                        
                        // סגירת פנל הגדרות אם זה היה הווידג'ט הנבחר
                        if (builder.selectedElement && builder.selectedElement.type === 'widget' && builder.selectedElement.id === widgetId) {
                            clearSelectedElement();
                        }
                        
                        builder.hasChanges = true;
                        return;
                    }
                }
            }
        }
    }
}