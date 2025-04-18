// Render module
import * as TextWidget from './widgets/text.js';
// Import other widget modules as needed
// הוספה: ייבוא פונקציות רספונסיביות
import { getEffectiveConfig, getCurrentBreakpoint } from './render-responsive.js';

console.log('Render module loaded');

// Function to render the entire page content
export function renderPageContent(pageState, containerElement) {
    console.log('Rendering page content...');
    // Clear container
    containerElement.innerHTML = '';

    // If no rows, show a message or empty state
    if (pageState.length === 0) {
        // הוספת קלאס empty למיכל וציור ההודעה
        containerElement.classList.add('empty');
        
        const emptyStateMessage = document.createElement('div');
        emptyStateMessage.className = 'empty-state-message flex flex-col items-center justify-center text-center p-8';
        
        const icon = document.createElement('div');
        icon.className = 'text-4xl text-gray-300 mb-4';
        icon.innerHTML = '<i class="ri-add-circle-line"></i>';
        
        const text = document.createElement('p');
        text.className = 'text-gray-500 text-lg mb-4';
        text.textContent = 'הקאנבס ריק';
        
        const subText = document.createElement('p');
        subText.className = 'text-gray-400 text-sm mb-6';
        subText.textContent = 'לחץ כאן להוספת שורה חדשה';
        
        const addButton = document.createElement('button');
        addButton.className = 'px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-md transition-colors';
        addButton.textContent = 'הוסף שורה';
        addButton.addEventListener('click', () => {
            // שינוי: שליחת אירוע ייעודי למצב ריק
            window.dispatchEvent(new CustomEvent('add-default-row'));
            // window.dispatchEvent(new CustomEvent('add-row')); 
        });
        
        emptyStateMessage.appendChild(icon);
        emptyStateMessage.appendChild(text);
        emptyStateMessage.appendChild(subText);
        emptyStateMessage.appendChild(addButton);
        
        containerElement.appendChild(emptyStateMessage);
        return;
    }

    containerElement.classList.remove('empty');

    // Render each row
    pageState.forEach(row => {
        // --- שינוי: שימוש בקונפיג האפקטיבי ---
        const currentBreakpoint = getCurrentBreakpoint();
        const effectiveConfig = getEffectiveConfig(row, currentBreakpoint);
        // בדיקה אם השורה צריכה להיות מוסתרת ב-breakpoint זה
        if (effectiveConfig.visibility && effectiveConfig.visibility[currentBreakpoint] === false) {
            return; // אל תרנדר את השורה
        }
        // -------------------------------------
        const rowElement = renderRow(row, effectiveConfig); // העברת הקונפיג האפקטיבי
        containerElement.appendChild(rowElement);
    });
}

// Function to render a single row
// --- שינוי: קבלת effectiveConfig כפרמטר --- 
function renderRow(rowData, effectiveConfig) {
    // const config = rowData.config || {}; // לא נשתמש ישירות
    const config = effectiveConfig; // שימוש בקונפיג האפקטיבי
    const styles = config.styles || {};

    // שינוי: קביעת תגית ה-HTML מהקונפיג האפקטיבי
    const rowElement = document.createElement(config.htmlTag || 'div');
    rowElement.className = 'row-wrapper relative mb-5 rounded-lg';
    rowElement.dataset.rowId = rowData.id;
    rowElement.dataset.elementId = rowData.id; // For selection

    // החלת ID וקלאסים מותאמים אישית
    if (config.customId) {
        rowElement.id = config.customId;
    }
    if (config.customClass) {
        rowElement.classList.add(...config.customClass.split(' ').filter(Boolean));
    }

    // --- החלת סגנונות רקע, גובה ו-inline --- 
    // שינוי: במקום להחיל כאן, נקרא ל-applyStylesToElement בסוף
    // Object.assign(rowElement.style, { ... }); // הסרת החלת סטיילים ישירה כאן

    // --- הוספת שכבת רקע (אם נבחרה) ---
    if (styles.backgroundOverlay && styles.backgroundOverlay.type !== 'none') {
        const overlayElement = document.createElement('div');
        overlayElement.className = 'row-background-overlay';
        // ... (קוד שכבת רקע נשאר זהה, אבל יוחל ע"י applyStylesToElement)
        rowElement.appendChild(overlayElement); 
    }
    
    // --- Row header/controls (מוזז אחרי ה-overlay) ---
    const rowHeader = document.createElement('div');
    // הוספת z-index גבוה יותר לכותרת כדי שתהיה מעל ה-overlay
    rowHeader.className = 'row-header relative z-10 flex justify-between items-center px-3 py-1.5 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors'; 
    
    const rowTitleArea = document.createElement('div');
    rowTitleArea.className = 'flex items-center pointer-events-none';
    
    const rowDragHandle = document.createElement('div');
    rowDragHandle.className = 'row-drag-handle cursor-move p-1 mr-2 text-gray-400 hover:text-gray-700 pointer-events-auto';
    rowDragHandle.innerHTML = '<i class="ri-drag-move-fill"></i>';
    
    const rowTitle = document.createElement('span');
    rowTitle.className = 'text-xs font-medium text-gray-500';
    rowTitle.textContent = 'שורה';
    
    rowTitleArea.appendChild(rowDragHandle);
    rowTitleArea.appendChild(rowTitle);
    
    const rowActions = document.createElement('div');
    rowActions.className = 'row-actions flex items-center gap-1';
    
    const settingsButton = document.createElement('button');
    settingsButton.className = 'p-1 text-gray-400 hover:text-blue-500';
    settingsButton.innerHTML = '<i class="ri-settings-line"></i>';
    settingsButton.title = 'הגדרות שורה';
    settingsButton.addEventListener('click', (e) => {
        e.stopPropagation();
        selectRow(rowData.id);
    });
    
    const deleteButton = document.createElement('button');
    deleteButton.className = 'p-1 text-gray-400 hover:text-red-500';
    deleteButton.innerHTML = '<i class="ri-delete-bin-line"></i>';
    deleteButton.title = 'מחק שורה';
    deleteButton.addEventListener('click', (e) => {
        e.stopPropagation();
        deleteRow(rowData.id);
    });
    
    rowActions.appendChild(settingsButton);
    rowActions.appendChild(deleteButton);
    
    rowHeader.appendChild(rowTitleArea);
    rowHeader.appendChild(rowActions);
    rowElement.appendChild(rowHeader);

    // הוספה: מאזין לחיצה על ה-header לבחירת השורה
    rowHeader.addEventListener('click', (e) => {
        if (!e.target.closest('button') && !e.target.closest('.row-drag-handle')) {
            selectRow(rowData.id);
        }
    });

    // --- Row content container --- 
    let contentTargetContainer = rowElement;
    // --- שינוי: שימוש ב-config (האפקטיבי) --- 
    if (config.contentWidth === 'boxed') {
        const innerContainer = document.createElement('div');
        innerContainer.className = 'boxed-content-container relative z-10 max-w-6xl mx-auto w-full px-4'; 
        rowElement.appendChild(innerContainer);
        contentTargetContainer = innerContainer;
    }
    
    const columnsContainer = document.createElement('div');
    // --- שינוי: שימוש ב-config (האפקטיבי) ---
    columnsContainer.className = 'columns-container relative z-10 flex py-2'; 
    if (config.contentWidth !== 'boxed') { columnsContainer.classList.add('px-2'); }
    // --- שינוי: הוספת טיפול בהיפוך עמודות במובייל --- 
    if (config.reverseColumnOrderOnMobile === true && getCurrentBreakpoint() === 'mobile') {
        columnsContainer.classList.add('flex-col-reverse'); // או flex-wrap-reverse תלוי בבסיס
    } else {
        // ודא שהקלאס מוסר אם לא רלוונטי
        columnsContainer.classList.remove('flex-col-reverse');
    }
    // -----------------------------------------------
    // --- שינוי: העברת הגדרות align ו-gap ל-applyStylesToElement ---
    // switch (config.verticalAlign) { ... } // יועבר
    // if (config.columnGap !== undefined) { ... } // יועבר
    
    // Render each column
    if (rowData.columns && rowData.columns.length > 0) {
        const totalColumns = rowData.columns.length;
        rowData.columns.forEach((column, index) => {
            // --- שינוי: שימוש בקונפיג האפקטיבי של העמודה ---
            const currentBreakpoint = getCurrentBreakpoint();
            const effectiveColumnConfig = getEffectiveConfig(column, currentBreakpoint);
            // בדיקה אם העמודה צריכה להיות מוסתרת
            if (effectiveColumnConfig.visibility && effectiveColumnConfig.visibility[currentBreakpoint] === false) {
                return; // אל תרנדר עמודה זו
            }
            // -------------------------------------------------
            const columnElement = renderColumn(column, totalColumns, index, effectiveColumnConfig); // העברת הקונפיג האפקטיבי
            columnsContainer.appendChild(columnElement);
        });
    }
    
    contentTargetContainer.appendChild(columnsContainer); 
    
    // --- שינוי: החלת סגנונות כולל רספונסיביים בסוף --- 
    applyStylesToElement(rowElement, rowData);
    // -------------------------------------------------
    
    return rowElement;
}

// Function to render a single column
// --- שינוי: קבלת effectiveConfig כפרמטר --- 
function renderColumn(columnData, totalColumnsInRow, columnIndex, effectiveConfig) {
    console.log('Rendering column with data:', columnData, 'Index:', columnIndex, 'Effective Config:', effectiveConfig); // הדפסה מורחבת

    // const config = columnData?.config || {}; // לא בשימוש ישיר
    // const styles = config?.styles || {}; // לא בשימוש ישיר
    const config = effectiveConfig; // שימוש בקונפיג האפקטיבי
    const styles = config.styles || {};

    const columnWrapper = document.createElement(config.htmlTag || 'div');
    columnWrapper.className = `column-wrapper group relative ${columnData?.widgets?.length === 0 ? 'is-empty' : ''} rounded-lg`; 
    columnWrapper.dataset.columnId = columnData.id;
    columnWrapper.dataset.elementId = columnData.id; // For selection
    
    if (config.customId) columnWrapper.id = config.customId;
    if (config.customClass) columnWrapper.classList.add(...config.customClass.split(' ').filter(Boolean));

    // --- שינוי: הסרת החלת סטיילים ישירה --- 
    // Object.assign(columnWrapper.style, { ... }); // מוסר, יוחל ע"י applyStylesToElement

    // --- Column Controls (Kebab Menu & Toolbar) ---
    const controlsContainer = document.createElement('div');
    // שינוי: הגדלת z-index ל-z-50
    controlsContainer.className = 'column-controls absolute top-1 left-1 z-50 flex items-center gap-1';

    // Kebab Button (מוצג תמיד, מפעיל את ה-toolbar)
    const kebabButton = document.createElement('button');
    kebabButton.className = 'kebab-button p-1 bg-white/80 backdrop-blur-sm rounded-full shadow text-gray-500 hover:text-blue-500 hover:bg-white opacity-0 group-hover:opacity-100 focus:opacity-100 transition-opacity';
    kebabButton.innerHTML = '<i class="ri-more-2-fill text-sm"></i>';
    kebabButton.title = 'אפשרויות עמודה';
    controlsContainer.appendChild(kebabButton); // הוספת כפתור הקבב ראשון

    // Toolbar (מוסתר בהתחלה)
    const toolbar = document.createElement('div');
    // שינוי: הגדלת z-index ל-z-40
    toolbar.className = 'column-toolbar absolute top-full left-0 mt-1 p-1 bg-white rounded-md shadow-lg border border-gray-100 flex flex-col gap-1 hidden z-40 w-max'; 
    controlsContainer.appendChild(toolbar); // הוספת הסרגל המוסתר

    // --- כפתורים בתוך הסרגל --- 

    // שינוי: החלפת סדר ופונקציונליות של כפתורי הזזה (עבור RTL)
    // כפתור הזז ימינה (מופיע אם לא העמודה הראשונה)
    if (columnIndex > 0) { 
        const moveRightBtn = document.createElement('button');
        moveRightBtn.className = 'flex items-center gap-2 px-2 py-1 text-sm text-gray-700 hover:bg-gray-100 rounded w-full text-right';
        moveRightBtn.innerHTML = '<span>הזז ימינה</span><i class="ri-arrow-right-s-line"></i>'; 
        moveRightBtn.title = 'הזז ימינה';
        moveRightBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            // שינוי: שליחת כיוון לוגי הפוך (left) עבור הזזה ויזואלית ימינה (הקטנת אינדקס)
            moveColumn(columnData.id, 'left'); 
            toolbar.classList.add('hidden');
        });
        toolbar.appendChild(moveRightBtn); 
    }

    // Settings Button (in toolbar) - נשאר באמצע
    const settingsBtn = document.createElement('button');
    settingsBtn.className = 'flex items-center gap-2 px-2 py-1 text-sm text-gray-700 hover:bg-gray-100 rounded w-full text-right';
    settingsBtn.innerHTML = '<i class="ri-settings-3-line"></i><span>הגדרות</span>';
    settingsBtn.title = 'הגדרות';
    settingsBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        selectElement('column', columnData.id);
        toolbar.classList.add('hidden'); 
    });
    toolbar.appendChild(settingsBtn); 

    // Delete Button (in toolbar) - נשאר כפי שהוא
    const deleteBtn = document.createElement('button');
    deleteBtn.className = 'flex items-center gap-2 px-2 py-1 text-sm text-red-600 hover:bg-red-50 rounded w-full text-right';
    deleteBtn.innerHTML = '<i class="ri-delete-bin-line"></i><span>מחיקה</span>';
    deleteBtn.title = 'מחיקה';
    deleteBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        deleteColumn(columnData.id); 
        toolbar.classList.add('hidden'); 
    });
    toolbar.appendChild(deleteBtn); 
    
    // כפתור הזז שמאלה (מופיע אם לא העמודה האחרונה)
    if (columnIndex < totalColumnsInRow - 1) { 
        const moveLeftBtn = document.createElement('button');
        moveLeftBtn.className = 'flex items-center gap-2 px-2 py-1 text-sm text-gray-700 hover:bg-gray-100 rounded w-full text-right';
        moveLeftBtn.innerHTML = '<i class="ri-arrow-left-s-line"></i><span>הזז שמאלה</span>';
        moveLeftBtn.title = 'הזז שמאלה';
        moveLeftBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            // שינוי: שליחת כיוון לוגי הפוך (right) עבור הזזה ויזואלית שמאלה (הגדלת אינדקס)
            moveColumn(columnData.id, 'right'); 
            toolbar.classList.add('hidden');
        });
        toolbar.appendChild(moveLeftBtn); 
    }

    // --- סוף כפתורים בסרגל ---

    // Close toolbar on outside click
    const outsideClickListener = (e) => {
        // Check if the click target is outside the toolbar AND outside the kebab button
        if (!toolbar.contains(e.target) && !kebabButton.contains(e.target)) {
            toolbar.classList.add('hidden'); // Hide the toolbar
            document.removeEventListener('click', outsideClickListener); // Remove this listener
            console.log('Closed column toolbar on outside click');
        }
    };
    
    // Toggle toolbar visibility AND manage outside click listener
    kebabButton.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent click bubbling up

        // Close other open toolbars in the same row first
        const currentRow = columnWrapper.closest('.row-wrapper');
        currentRow.querySelectorAll('.column-toolbar:not(.hidden)').forEach(tb => {
            if (tb !== toolbar) {
                tb.classList.add('hidden');
                // Also remove potential outside click listeners for those toolbars
                // We need a way to access their specific listeners, or use a general approach
                // For now, this might leave dangling listeners, but let's fix the toggle first.
            }
        });

        // Toggle the current toolbar
        const isNowHidden = toolbar.classList.toggle('hidden');

        // Manage the outside click listener based on the new state
        if (!isNowHidden) { // If the toolbar is now visible
            // Use setTimeout to ensure this listener is added *after* the current click event finishes
            setTimeout(() => document.addEventListener('click', outsideClickListener), 0); 
        } else { // If the toolbar is now hidden
            // Remove the listener
            document.removeEventListener('click', outsideClickListener);
        }
    });

    // הוספת controlsContainer ל-columnWrapper
    columnWrapper.appendChild(controlsContainer);

    // --- Widgets Container ---
    const widgetsContainer = document.createElement('div');
    widgetsContainer.className = 'column-widgets-container min-h-[50px] flex flex-col pb-2'; 
    Object.assign(widgetsContainer.style, {
        paddingTop: styles.padding?.top || null,
        paddingRight: styles.padding?.right || null,
        paddingLeft: styles.padding?.left || null,
        gap: (config.widgetSpacing !== undefined) ? `${parseInt(config.widgetSpacing)}px` : null 
    });

    // בדיקה אם widgets הוא מערך
    if (Array.isArray(columnData?.widgets)) {
        if (columnData.widgets.length > 0) {
            columnData.widgets.forEach(widget => {
                // --- שינוי: שימוש בקונפיג האפקטיבי של הווידג'ט ---
                const currentBreakpoint = getCurrentBreakpoint();
                const effectiveWidgetConfig = getEffectiveConfig(widget, currentBreakpoint);
                 // בדיקה אם הווידג'ט צריך להיות מוסתר
                if (effectiveWidgetConfig.visibility && effectiveWidgetConfig.visibility[currentBreakpoint] === false) {
                    return; // אל תרנדר ווידג'ט זה
                }
                // -------------------------------------------------
                const widgetElement = renderWidget(widget, effectiveWidgetConfig); // העברת הקונפיג האפקטיבי
                if (widgetElement instanceof Node) {
                    widgetsContainer.appendChild(widgetElement);
                } else {
                    console.error('RenderWidget did not return a valid Node for:', widget);
                    const errorElement = document.createElement('div');
                    errorElement.className = 'p-2 text-red-500 border border-red-300 rounded bg-red-50 text-xs';
                    errorElement.textContent = 'שגיאה ברינדור ווידג\'ט';
                    widgetsContainer.appendChild(errorElement);
                }
            });
        } 
        // אם המערך ריק, לא עושים כלום (רק מחילים CSS על is-empty)
    } else {
        console.error('columnData.widgets is not an array or missing!', columnData);
        // אפשר להוסיף הודעת שגיאה גם כאן
        const errorElement = document.createElement('div');
        errorElement.className = 'p-2 text-orange-600 border border-orange-300 rounded bg-orange-50 text-xs';
        errorElement.textContent = 'שגיאה בנתוני הווידג\'טים';
        widgetsContainer.appendChild(errorElement);
    }
    
    // לוג לפני הוספת קונטיינר הווידג'טים
    console.log('Before appending widgetsContainer:', { columnWrapper, widgetsContainer });
    if (!(widgetsContainer instanceof Node)) {
        console.error('widgetsContainer is not a valid Node!');
    } else {
        columnWrapper.appendChild(widgetsContainer);
    }

    // --- שינוי: החלת סגנונות כולל רספונסיביים בסוף --- 
    applyStylesToElement(columnWrapper, columnData);
    // -------------------------------------------------

    return columnWrapper;
}

// Function to render a single widget
// --- שינוי: קבלת effectiveConfig כפרמטר --- 
function renderWidget(widgetData, effectiveConfig) {
    // const config = widgetData.config || {}; // לא בשימוש ישיר
    const config = effectiveConfig;

    const widgetWrapper = document.createElement('div');
    widgetWrapper.className = 'widget-wrapper relative mb-4'; // מרווח ברירת מחדל
    widgetWrapper.dataset.widgetId = widgetData.id;
    widgetWrapper.dataset.elementId = widgetData.id; // For selection
    widgetWrapper.dataset.widgetType = widgetData.type;

    // Apply custom ID and Class if present in effectiveConfig
    if (config.customId) widgetWrapper.id = config.customId;
    if (config.customClass) widgetWrapper.classList.add(...config.customClass.split(' ').filter(Boolean));

    let widgetContentElement = null;
    const widgetModule = getWidgetModule(widgetData.type);

    if (widgetModule && typeof widgetModule.render === 'function') {
        // --- שינוי: העברת הקונפיג האפקטיבי לפונקציית הרינדור של הווידג'ט --- 
        widgetContentElement = widgetModule.render(widgetData, config); 
    } else if (widgetModule && typeof widgetModule.renderWidget === 'function') {
        console.log(`Using renderWidget for ${widgetData.type}`);
        // *** שינוי: קריאה ל-renderWidget והעברת הקונפיג האפקטיבי ***
        widgetContentElement = widgetModule.renderWidget(widgetData, config); 
    } else {
        widgetContentElement = document.createElement('div');
        widgetContentElement.className = 'widget-placeholder p-4 bg-red-100 border border-red-300 text-red-700 rounded';
        widgetContentElement.textContent = `Widget type '${widgetData.type}' cannot be rendered (no render or renderWidget function found).`; // הודעה משופרת
        console.error('Missing render/renderWidget function for widget type:', widgetData.type);
    }

    if (widgetContentElement) {
        widgetWrapper.appendChild(widgetContentElement);
    }

    // --- שינוי: החלת סגנונות כולל רספונסיביים בסוף --- 
    applyStylesToElement(widgetWrapper, widgetData);
    // -------------------------------------------------
    
    // Add controls (edit, duplicate, delete)
    addWidgetControls(widgetWrapper, widgetData);

    // Add event listener for selection
    widgetWrapper.addEventListener('click', (e) => {
        // Prevent selection if clicking on a control button
        if (!e.target.closest('.widget-toolbar-button')) {
            selectElement('widget', widgetData.id);
        }
    });

    return widgetWrapper;
}

// פונקציה להוספת כפתורי פעולה לווידג'ט - שיפור העיצוב
function addWidgetControls(widgetWrapper, widgetData) {
    // יצירת סרגל כלים עם עיצוב משופר
    const toolbar = document.createElement('div');
    // שינוי: הסרת group-hover, הוספת קלאס ייעודי לשליטה עם JS
    toolbar.className = 'widget-toolbar absolute -top-3 left-1 opacity-0 transition-opacity flex bg-white shadow-md rounded-full border border-gray-200 z-30';

    // כפתור עריכה
    const editButton = document.createElement('button');
    editButton.className = 'p-2 text-sm text-gray-500 hover:text-primary-600 hover:bg-gray-50 rounded-l-full';
    editButton.innerHTML = '<i class="ri-edit-line"></i>';
    editButton.title = 'ערוך';
    editButton.dataset.action = 'edit';

    // כפתור שכפול
    const duplicateButton = document.createElement('button');
    duplicateButton.className = 'p-2 text-sm text-gray-500 hover:text-primary-600 hover:bg-gray-50';
    duplicateButton.innerHTML = '<i class="ri-file-copy-line"></i>';
    duplicateButton.title = 'שכפל';
    duplicateButton.dataset.action = 'duplicate';

    // כפתור מחיקה
    const deleteButton = document.createElement('button');
    deleteButton.className = 'p-2 text-sm text-gray-500 hover:text-red-500 hover:bg-gray-50 rounded-r-full';
    deleteButton.innerHTML = '<i class="ri-delete-bin-line"></i>';
    deleteButton.title = 'מחק';
    deleteButton.dataset.action = 'delete';

    // הוספת הכפתורים לסרגל
    toolbar.appendChild(editButton);
    toolbar.appendChild(duplicateButton);
    toolbar.appendChild(deleteButton);

    // הוספת אירועי לחיצה (נשארים זהים)
    editButton.addEventListener('click', (e) => {
        e.stopPropagation();
        const widget = widgetWrapper.querySelector('.widget');
        if (widget) widget.click(); // Select the widget
    });
    duplicateButton.addEventListener('click', (e) => {
        e.stopPropagation();
        duplicateWidget(widgetData.id);
    });
    deleteButton.addEventListener('click', (e) => {
        e.stopPropagation();
        deleteWidget(widgetData.id);
    });

    // הוספת הסרגל לתוך ה-wrapper של הווידג'ט
    widgetWrapper.appendChild(toolbar);

    // שינוי: הסרת קלאס group מ-wrapper
    // widgetWrapper.classList.add('group', 'rounded-md'); // Remove group class
    widgetWrapper.classList.add('rounded-md'); // Keep rounded style

    // שינוי: הוספת מאזיני אירועים להופעת/הסתרת סרגל הכלים
    widgetWrapper.addEventListener('mouseenter', () => {
        toolbar.style.opacity = '1';
    });
    widgetWrapper.addEventListener('mouseleave', () => {
        toolbar.style.opacity = '0';
    });
}

// פונקציה לשכפול ווידג'ט
function duplicateWidget(widgetId) {
    console.log('שכפול ווידג\'ט:', widgetId);
    window.dispatchEvent(new CustomEvent('duplicate-widget', { detail: { id: widgetId }}));
}

// פונקציה למחיקת ווידג'ט
function deleteWidget(widgetId) {
    console.log('מחיקת ווידג\'ט:', widgetId);
    window.dispatchEvent(new CustomEvent('delete-widget', { detail: { id: widgetId }}));
}

// פונקציה למחיקת שורה
function deleteRow(rowId) {
    console.log('מחיקת שורה:', rowId);
    window.dispatchEvent(new CustomEvent('delete-row', { detail: { id: rowId }}));
}

// Function to apply widget styles
export function applyWidgetStyles(widgetElement, widgetData) {
    if (!widgetData.config) return;
    const config = widgetData.config;

    // -- Apply Custom Classes --
    // Store previous custom classes on the element to remove them before adding new ones
    const previousClasses = widgetElement.dataset.customClasses ? widgetElement.dataset.customClasses.split(' ') : [];
    previousClasses.forEach(cls => {
        if (cls) widgetElement.classList.remove(cls);
    });

    const currentClasses = config.customClass ? config.customClass.split(' ').filter(cls => cls) : [];
    currentClasses.forEach(cls => {
        widgetElement.classList.add(cls);
    });
    // Save current custom classes for the next update
    widgetElement.dataset.customClasses = currentClasses.join(' ');

    // Apply text alignment classes (נשאר)
    if (config.textAlign) {
        // Remove existing text alignment classes first
        widgetElement.classList.remove('text-left', 'text-center', 'text-right');
        widgetElement.classList.add(config.textAlign);
    }

    // Apply font size classes (נשאר)
    if (config.fontSize) {
        // Remove existing font size classes first
        ['text-xs', 'text-sm', 'text-base', 'text-lg', 'text-xl', 'text-2xl', 'text-3xl', 'text-4xl'].forEach(cls => {
            widgetElement.classList.remove(cls);
        });
        widgetElement.classList.add(config.fontSize);
    }

    // Apply inline styles (from config.styles)
    if (config.styles) {
        applyInlineStyles(widgetElement, widgetData);
    }

    // Add border radius for all widgets by default
    if (!widgetElement.classList.contains('rounded')) {
        widgetElement.classList.add('rounded');
    }
}

// שינוי: שינוי שם הפונקציה והוספת החלת color ו-backgroundColor
function applyInlineStyles(widgetElement, widgetData) {
    const styles = widgetData.config.styles;
    if (!styles) return;
    const typo = styles.typography || {}; // קבל את הגדרות הטיפוגרפיה אם קיימות

    // Apply Text Color
    widgetElement.style.color = styles.color || '';

    // Apply Background Color
    widgetElement.style.backgroundColor = styles.backgroundColor || '';

    // Apply Typography
    widgetElement.style.fontFamily = typo.fontFamily || '';
    widgetElement.style.fontWeight = typo.fontWeight || '';
    widgetElement.style.lineHeight = typo.lineHeight || '';
    widgetElement.style.letterSpacing = typo.letterSpacing || '';

    // Apply padding
    if (styles.padding) {
        // Check if we have individual padding values
        if (typeof styles.padding === 'object') {
            widgetElement.style.paddingTop = (styles.padding.top !== undefined) ? styles.padding.top + 'px' : '';
            widgetElement.style.paddingRight = (styles.padding.right !== undefined) ? styles.padding.right + 'px' : '';
            widgetElement.style.paddingBottom = (styles.padding.bottom !== undefined) ? styles.padding.bottom + 'px' : '';
            widgetElement.style.paddingLeft = (styles.padding.left !== undefined) ? styles.padding.left + 'px' : '';
        } else {
            // Single value for all sides
            widgetElement.style.padding = styles.padding + 'px';
        }
    } else {
        widgetElement.style.padding = ''; // Clear padding if not defined
    }

    // Apply opacity
    if (styles.opacity !== undefined) {
        widgetElement.style.opacity = styles.opacity;
    } else {
        widgetElement.style.opacity = ''; // Reset to default
    }

    // Apply stroke/border
    if (styles.border && styles.border.width > 0 && styles.border.style !== 'none') {
        widgetElement.style.borderColor = styles.border.color || '';
        widgetElement.style.borderWidth = styles.border.width + 'px';
        widgetElement.style.borderStyle = styles.border.style || 'solid';
    } else {
        // Reset border if not defined or width is 0
        widgetElement.style.borderColor = '';
        widgetElement.style.borderWidth = '';
        widgetElement.style.borderStyle = '';
    }

    // Apply shadow
    if (styles.boxShadow && styles.boxShadow.type !== 'none') {
        const x = styles.boxShadow.x || 0;
        const y = styles.boxShadow.y || 0;
        const blur = styles.boxShadow.blur || 0;
        const spread = styles.boxShadow.spread || 0;
        const color = styles.boxShadow.color || 'rgba(0,0,0,0.1)';

        widgetElement.style.boxShadow = `${parseInt(x)}px ${parseInt(y)}px ${parseInt(blur)}px ${parseInt(spread)}px ${color}`;
    } else {
        widgetElement.style.boxShadow = 'none'; // Reset shadow
    }
}

// הוספה: פונקציה לשליחת אירוע בחירת אלמנט
function selectElement(type, id) {
    console.log(`Requesting selection of ${type}: ${id}`);
    window.dispatchEvent(new CustomEvent('select-element', { detail: { type, id } }));
}

// הוספה: פונקציית עזר לבחירת שורה
function selectRow(rowId) {
    selectElement('row', rowId);
}

// הוספה: פונקציה לשליחת אירוע מחיקת עמודה
function deleteColumn(columnId) {
    console.log('Requesting deletion of column:', columnId);
    if (confirm('האם למחוק את העמודה וכל הווידג\'טים שבתוכה?')) {
        window.dispatchEvent(new CustomEvent('delete-column', { detail: { id: columnId }}));
    }
}

// הוספה: פונקציה לשליחת אירוע הזזת עמודה
function moveColumn(columnId, direction) {
    console.log(`Requesting move ${direction} for column:`, columnId);
    window.dispatchEvent(new CustomEvent('move-column', { detail: { id: columnId, direction }}));
}

// ==========================================
// NEW: Apply Styles Directly to Element
// ==========================================
// In render.js, find the applyStylesToElement function and update it to handle missing elements better

/**
 * Applies styles to an element based on its element data and config
 * @param {HTMLElement} element - The DOM element to apply styles to
 * @param {Object} elementData - The data structure for this element
 */
export function applyStylesToElement(element, elementData) {
    // Improved error handling - check for both element and elementData
    if (!element) {
        console.warn('applyStylesToElement: DOM element not found');
        return;
    }
    
    if (!elementData || !elementData.config) {
        console.warn('applyStylesToElement: Missing element data or config');
        return;
    }

    // Find element by ID if needed (this is what was missing!)
    if (typeof element === 'string') {
        const elementId = element;
        element = document.querySelector(`[data-element-id="${elementId}"]`) || 
                 document.querySelector(`[data-widget-id="${elementId}"]`) || 
                 document.querySelector(`[data-column-id="${elementId}"]`) || 
                 document.querySelector(`[data-row-id="${elementId}"]`);
        
        if (!element) {
            console.warn(`applyStylesToElement: Could not find element with ID: ${elementId}`);
            return;
        }
    }

    // Rest of the function remains the same
    const elementType = elementData.config ? (elementData.columns ? 'row' : (elementData.widgets ? 'column' : 'widget')) : 'unknown';
    // console.log(`Applying styles to ${elementType}: ${elementData.id}`);

    const currentBreakpoint = getCurrentBreakpoint();
    const config = getEffectiveConfig(elementData, currentBreakpoint);
    const styles = config.styles || {};
    
    // --- טיפול בנראות (Visibility) ---
    element.classList.remove('hidden-desktop', 'hidden-tablet', 'hidden-mobile'); // איפוס תחילה
    if (config.visibility) {
        if (config.visibility.desktop === false) element.classList.add('hidden-desktop');
        if (config.visibility.tablet === false) element.classList.add('hidden-tablet');
        if (config.visibility.mobile === false) element.classList.add('hidden-mobile');
    }
    
    // --- איפוס סגנונות inline לפני החלה מחדש (חשוב למניעת התנגשויות) ---
    element.style.cssText = ''; // דרך קלה לאפס הכל
    
    // --- החלת סגנונות כלליים (לכל סוגי האלמנטים) ---
    Object.assign(element.style, {
        backgroundColor: styles.backgroundColor || 'transparent',
        paddingTop: styles.padding?.top || null,
        paddingRight: styles.padding?.right || null,
        paddingBottom: styles.padding?.bottom || null,
        paddingLeft: styles.padding?.left || null,
        marginTop: styles.margin?.top || null,
        marginRight: styles.margin?.right || null,
        marginBottom: styles.margin?.bottom || null,
        marginLeft: styles.margin?.left || null,
        borderWidth: styles.border?.width ? `${parseInt(styles.border.width)}px` : null,
        borderStyle: styles.border?.style || 'none',
        borderColor: styles.border?.color || 'transparent',
        borderRadius: styles.borderRadius?.value ? `${parseInt(styles.borderRadius.value)}${styles.borderRadius.unit || 'px'}` : null,
    });

    // Box Shadow
    if (styles.boxShadow && styles.boxShadow.type !== 'none') {
        element.style.boxShadow = `${parseInt(styles.boxShadow.x)}px ${parseInt(styles.boxShadow.y)}px ${parseInt(styles.boxShadow.blur)}px ${parseInt(styles.boxShadow.spread)}px ${styles.boxShadow.color || 'rgba(0,0,0,0.1)'}`;
    } else {
        element.style.boxShadow = 'none';
    }
    
    // --- החלת סגנונות ספציפיים לסוג האלמנט ---
    if (elementType === 'row') {
        Object.assign(element.style, {
            backgroundImage: styles.backgroundImage ? `url('${styles.backgroundImage}')` : null,
            backgroundPosition: styles.backgroundPosition || null,
            backgroundRepeat: styles.backgroundRepeat || null,
            backgroundSize: styles.backgroundSize || null,
            backgroundAttachment: styles.backgroundAttachment || null,
            minHeight: (config.heightMode === 'minHeight' && config.minHeight) ? `${parseInt(config.minHeight.value)}${config.minHeight.unit}` : null,
        });
        // סגנונות הקונטיינר הפנימי של העמודות
        const columnsContainer = element.querySelector('.columns-container');
        if (columnsContainer) {
            Object.assign(columnsContainer.style, {
                alignItems: config.verticalAlign ? (config.verticalAlign === 'middle' ? 'center' : `flex-${config.verticalAlign}`) : 'flex-start',
                gap: config.columnGap !== undefined ? `${parseInt(config.columnGap) || 0}px` : null,
            });
            // טיפול בהיפוך עמודות
            if (config.reverseColumnOrderOnMobile === true && currentBreakpoint === 'mobile') {
                columnsContainer.classList.add('flex-col-reverse'); // או סגנון אחר מתאים
            } else {
                columnsContainer.classList.remove('flex-col-reverse');
            }
        }
         // סגנונות שכבת רקע (Overlay)
        const overlay = element.querySelector('.row-background-overlay');
        if (overlay && styles.backgroundOverlay && styles.backgroundOverlay.type !== 'none') {
            Object.assign(overlay.style, {
                backgroundColor: styles.backgroundOverlay.color || 'rgba(0,0,0,0.5)',
                // אולי נוסיף עוד הגדרות ל-overlay בעתיד
            });
        } else if (overlay) {
            overlay.style.backgroundColor = 'transparent'; // הסר אם לא פעיל
        }

    } else if (elementType === 'column') {
        Object.assign(element.style, {
            width: config.widthPercent ? `${config.widthPercent}%` : '100%', 
            flexBasis: config.widthPercent ? `${config.widthPercent}%` : '100%', 
            flexGrow: '0',
            flexShrink: '0',
        });
        // סגנונות הקונטיינר של הווידג'טים
        const widgetsContainer = element.querySelector('.column-widgets-container');
        if (widgetsContainer) {
            Object.assign(widgetsContainer.style, {
                display: 'flex', // להשתמש ב-flex כאן
                flexDirection: 'column', // ווידג'טים אחד מתחת לשני
                justifyContent: config.verticalAlign || 'flex-start', // יישור אנכי של הווידג'טים
                alignItems: config.horizontalAlign || 'stretch', // יישור אופקי של הווידג'טים
                gap: config.widgetSpacing !== undefined ? `${parseInt(config.widgetSpacing) || 0}px` : null, // מרווח בין ווידג'טים
            });
        }

    } else if (elementType === 'widget') {
        // --- שינוי: העברת החלת סטיילים של ווידג'ט לכאן --- 
        const widgetModule = getWidgetModule(elementData.type);
        if (widgetModule && typeof widgetModule.applyStyles === 'function') {
            widgetModule.applyStyles(element, config); // העברת הקונפיג האפקטיבי
        } else {
            // אפשר להוסיף כאן סגנונות ברירת מחדל לווידג'טים אם רוצים
            // Apply text content update for text widgets if applicable
            if (elementData.type === 'text') {
                const contentElement = element.querySelector('.widget-content');
                if (contentElement && config.content !== undefined) {
                    contentElement.textContent = config.content;
                }
                // Additionally apply color to both wrapper and content element for text widgets
                if (styles.color) {
                    element.style.color = styles.color;
                    if (contentElement) contentElement.style.color = styles.color;
                }
            }
        }
    }
    
    // Force an update notification to the browser
    element.style.display = element.style.display;
}

// --- פונקציית עזר שהוסרה מ-core.js אך שימושית כאן ---
function getWidgetModule(widgetType) {
    switch (widgetType) {
        case 'text':
            return TextWidget;
        // Add cases for other widget types here
        default:
            console.error('Unknown widget type in render.js:', widgetType);
            return null;
    }
}

// ... (פונקציות עזר אחרות כמו selectElement, deleteRow, duplicateWidget וכו' נשארות במקומן) 
// יש לוודא שפונקציות אלו לא מתנגשות או מכילות לוגיקה שצריכה לעבור ל-core.js