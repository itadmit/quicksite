// Core builder logic
import * as Render from './render.js';
import * as TextWidget from './widgets/text.js';
import * as CommonSettings from './common-settings.js'; // Import common settings
// Import row settings functions
import { populateRowContentTab, populateRowDesignTab, populateRowAdvancedTab } from './row-settings.js';
// Import column settings functions
import { populateColumnContentTab, populateColumnDesignTab, populateColumnAdvancedTab } from './column-settings.js';
// הוספה: ייבוא פונקציות רספונסיביות
import { getEffectiveConfig, getCurrentBreakpoint } from './render-responsive.js'; 
// Import other widget modules as needed
// import * as CommonSettings from './common-settings.js';

console.log('Core module loaded');

// --- State Management ---
// New State Structure: Array<Row>
// Row: { id: string, config: object, columns: Array<Column> }
// Column: { id: string, config: object, widgets: Array<Widget> }
// Widget: { id: string, type: string, config: object }
let pageState = [];
let selectedElement = null; // Can be { type: 'row'/'column'/'widget', id: string }

// --- DOM Elements ---
const pageContentContainer = document.getElementById('page-content');
const settingsPanelContent = document.getElementById('tab-content-area');
const widgetSourceList = document.getElementById('widget-source-list');
const addRowButton = document.getElementById('add-row-button');
const responsiveControls = document.getElementById('responsive-controls');
const settingsTabs = document.querySelectorAll('.tab-button');
const columnChoicePopover = document.getElementById('column-choice-popover'); // Get the popover

// Function to generate unique IDs
function generateId(prefix = 'el') {
    return `${prefix}-${Date.now().toString(36)}-${Math.random().toString(36).substring(2, 7)}`;
}

// פונקציה לשמירת מצב העמוד
function savePageState() {
    // שינוי: הדפסה מפורטת יותר של המצב הנשמר
    console.log('שמירת מצב העמוד (מפורט):', JSON.stringify(pageState, null, 2));
    localStorage.setItem('builderPageState', JSON.stringify(pageState));
}

// Function to load initial content (NEEDS REFACTORING FOR NESTED STRUCTURE)
function loadInitialContent() {
    // בדיקה אם יש נתונים שמורים בלוקל סטורג'
    const savedPageState = localStorage.getItem('builderPageState');
    
    if (savedPageState) {
        try {
            pageState = JSON.parse(savedPageState);
            console.log('טעינת מצב שמור מהאחסון המקומי:', pageState);
            Render.renderPageContent(pageState, pageContentContainer);
            return;
        } catch (error) {
            console.error('שגיאה בטעינת המצב מהאחסון המקומי:', error);
        }
    }
    
    // אם אין מצב שמור, בדוק אם יש תוכן התחלתי
    if (typeof initialContent !== 'undefined' && Array.isArray(initialContent)) {
        // TODO: Validate and parse the NESTED initialContent structure
        // For now, assume it's the new format or start fresh
        if (isValidNestedStructure(initialContent)) { // Placeholder validation
             pageState = initialContent;
             console.log('Loaded initial state:', pageState);
             Render.renderPageContent(pageState, pageContentContainer); // Render the initial structure
        } else {
             console.warn('Initial content is not the expected nested structure. Starting fresh.');
             pageState = [];
        }
    } else {
        pageState = [];
        console.log('No initial content found. Starting fresh.');
    }
    // Render even if empty (might show initial placeholder/message)
    if(pageState.length === 0) {
        Render.renderPageContent(pageState, pageContentContainer);
    }
    
    // איפוס פאנל ההגדרות
    resetSettingsPanel();
}

// פונקציה לאיפוס פאנל ההגדרות
function resetSettingsPanel() {
    console.log("Resetting settings panel (no element selected).");
    selectedElement = null; // Clear selection state
    
    const tabsContainer = document.getElementById('settings-tabs-container');
    const contentArea = document.getElementById('tab-content-area');
    const placeholder = document.getElementById('settings-panel-placeholder');

    if (tabsContainer) {
        tabsContainer.classList.add('hidden');
        console.log('resetSettingsPanel: Added hidden to settings-tabs-container. Has hidden class:', tabsContainer.classList.contains('hidden'));
    } else {
        console.error('resetSettingsPanel: Could not find settings-tabs-container!');
    }
    if (contentArea) {
        contentArea.classList.add('hidden');
    }
    if (placeholder) {
        placeholder.classList.remove('hidden');
    }

    // --- שינוי: ניקוי והסתרת כל פאנל טאב בנפרד במקום מחיקת הקונטיינר ---
    const contentTabPanel = document.getElementById('tab-content-content');
    const designTabPanel = document.getElementById('tab-content-design');
    const advancedTabPanel = document.getElementById('tab-content-advanced');

    if (contentTabPanel) { contentTabPanel.innerHTML = ''; contentTabPanel.style.display = 'none'; }
    if (designTabPanel) { designTabPanel.innerHTML = ''; designTabPanel.style.display = 'none'; }
    if (advancedTabPanel) { advancedTabPanel.innerHTML = ''; advancedTabPanel.style.display = 'none'; }
    // ---------------------------------------------------------------------

    // Optionally deactivate tabs visually
    document.querySelectorAll('.settings-tabs .tab-button').forEach(btn => btn.dataset.active = 'false');
}

// Placeholder for validation
function isValidNestedStructure(content) {
    // Basic check: is it an array?
    if (!Array.isArray(content)) return false;
    // Deeper validation needed here to check for rows, columns, widgets structure
    return true; // Assume valid for now
}

// --- Event Handlers ---
function handleToggleColumnChoice(event) {
    event.stopPropagation(); // Prevent click from bubbling up to document
    if (!columnChoicePopover) return;
    columnChoicePopover.classList.toggle('hidden');
    console.log('Toggled column choice popover');
}

function handleAddRowWithColumns(columnCount) {
    const rowId = generateId();
    const columns = [];
    const initialWidthPercent = (100 / columnCount).toFixed(2); // חישוב רוחב התחלתי באחוזים
    
    // יצירת העמודות בהתאם למספר המבוקש
    for (let i = 0; i < columnCount; i++) {
        const columnId = generateId();
        columns.push({
            id: columnId,
            widgets: [],
            config: {
                widthPercent: initialWidthPercent, // הוספת רוחב באחוזים
                widgetSpacing: 15, // ברירת מחדל למרווח פנימי
                htmlTag: 'div', // ברירת מחדל לתגית
                styles: {}, // מקום לסגנונות ספציפיים לעמודה
                visibility: { desktop: true, tablet: true, mobile: true }, // הוספת ברירת מחדל לנראות
                responsiveOverrides: {} // אתחול אובייקט לדריסות רספונסיביות
            }
        });
    }
    
    // הוספת השורה החדשה למבנה הנתונים
    pageState.push({
        id: rowId,
        columns: columns,
        config: {
            styles: {}, // מקום לסגנונות ספציפיים לשורה
            visibility: { desktop: true, tablet: true, mobile: true }, // הוספת ברירת מחדל לנראות לשורה
            responsiveOverrides: {} // אתחול אובייקט לדריסות רספונסיביות לשורה
        }
    });
    
    // רינדור מחדש של הקאנבס
    Render.renderPageContent(pageState, pageContentContainer);
    // שינוי: אתחול מחדש של SortableJS כדי לכלול את העמודה החדשה
    initSortables();

    // עדכון במערכת השמירה (אם יש)
    savePageState();
}

function handleColumnChoiceClick(event) {
    const button = event.target.closest('.column-choice-button');
    if (!button) return;

    const numColumns = parseInt(button.dataset.columns, 10);
    if (numColumns >= 1 && numColumns <= 4) {
         handleAddRowWithColumns(numColumns);
    } else {
        console.error('Invalid number of columns selected:', button.dataset.columns);
        if (columnChoicePopover) columnChoicePopover.classList.add('hidden');
    }
}

function handleResponsiveViewChange(event) {
    const button = event.target.closest('.responsive-button');
    if (!button || button.dataset.active === 'true') return;

    const view = button.dataset.view;
    console.log('Changing view to:', view);

    // Update button active state
    responsiveControls.querySelectorAll('.responsive-button').forEach(btn => {
        btn.dataset.active = (btn === button).toString();
    });

    // Update page content container effective width (instead of canvas)
    const previewArea = document.getElementById('preview-area'); // Find the container
    previewArea.classList.remove('view-desktop', 'view-tablet', 'view-mobile'); // Remove old classes
    pageContentContainer.style.transition = 'max-width 0.3s ease';

    switch (view) {
        case 'desktop':
            pageContentContainer.style.maxWidth = '100%';
            previewArea.classList.add('view-desktop');
            break;
        case 'tablet':
            pageContentContainer.style.maxWidth = '768px';
            previewArea.classList.add('view-tablet');
            break;
        case 'mobile':
            pageContentContainer.style.maxWidth = '420px';
            previewArea.classList.add('view-mobile');
            break;
    }

    // --- הוספה: רינדור מחדש של התוכן כדי להחיל סגנונות רספונסיביים ---
    console.log('Re-rendering page content for new breakpoint...');
    Render.renderPageContent(pageState, pageContentContainer);
    // שינוי: יש לאתחל מחדש את SortableJS אחרי כל רינדור מחדש
    initSortables(); 
    // --- סוף הוספה ---

    // --- הוספה: טעינה מחדש של פאנל ההגדרות לאלמנט הנבחר ---
    if (selectedElement) {
        console.log(`Reloading settings panel for ${selectedElement.type} ${selectedElement.id} at new breakpoint: ${view}`);
        // מצא את הטאב הפעיל כרגע
        const activeTabButton = document.querySelector('.tab-button[data-active="true"]');
        const activeTab = activeTabButton ? activeTabButton.dataset.tab : 'content'; // ברירת מחדל לתוכן
        loadSettingsTabContent(activeTab); // טען מחדש את הטאב הפעיל עם ההקשר החדש
    }
    // ------------------------------------------------------------
}

function handleTabClick(event) {
    const button = event.target.closest('.tab-button');
    if (!button || button.dataset.active === 'true') return;

    const tab = button.dataset.tab;
    console.log('Switching settings tab to:', tab);

    // Update tab active state
    settingsTabs.forEach(btn => {
         btn.dataset.active = (btn === button).toString();
    });

    // Load content for the selected tab
    loadSettingsTabContent(tab);
}

// הוספה: פונקציה חדשה לטיפול בבקשת בחירה מאירוע
function handleElementSelectionRequest(event) {
    const { type, id } = event.detail;
    console.log(`Handling selection request for ${type}: ${id}`);

    // --- מניעת טעינה מחדש אם אותו אלמנט נבחר ---
    if (selectedElement && selectedElement.id === id) {
        console.log('Element already selected, skipping settings panel reload.');
        // Optionally, re-apply visual cues if needed, but avoid full reload
        const currentElement = document.querySelector(`[data-element-id="${id}"]`);
        if (currentElement && !currentElement.classList.contains('selected')) {
            // Re-apply selection visuals without triggering full load
             if (selectedElement.type === 'widget') {
                currentElement.classList.add('selected', 'ring-2', 'ring-primary-500', 'ring-offset-1');
            } else if (selectedElement.type === 'row') {
                currentElement.querySelector('.row-header')?.classList.add('bg-blue-50');
                currentElement.classList.add('selected'); // Add generic selected if needed
            } else if (selectedElement.type === 'column') {
                currentElement.classList.add('selected', 'ring-2', 'ring-indigo-500', 'ring-offset-1');
            }
        }
        return; // Stop further processing
    }
    // --------------------------------------------------

    const newlySelected = { type, id };

    // Deselect previous
    if (selectedElement) {
         const previousElement = document.querySelector(`[data-element-id="${selectedElement.id}"]`);
         if (previousElement) {
             previousElement.classList.remove('selected');
             // הסרת סימונים ספציפיים
             previousElement.classList.remove('ring-2', 'ring-primary-500', 'ring-offset-1'); // Widget
             previousElement.classList.remove('ring-2', 'ring-indigo-500', 'ring-offset-1'); // Column
             previousElement.querySelector('.row-header')?.classList.remove('bg-blue-50'); // Row header
         }
    }

    // Select new element
    selectedElement = newlySelected;
    const currentElement = document.querySelector(`[data-element-id="${selectedElement.id}"]`);
    if (currentElement) {
        currentElement.classList.add('selected');
        // הוספת סימון ויזואלי בהתאם לסוג האלמנט
        if (selectedElement.type === 'widget') {
            currentElement.classList.add('ring-2', 'ring-primary-500', 'ring-offset-1');
        } else if (selectedElement.type === 'row') {
            // סימון שונה לשורה - למשל רקע בהיר ל-header
            currentElement.querySelector('.row-header')?.classList.add('bg-blue-50');
        } else if (selectedElement.type === 'column') {
            // סימון לעמודה: מסגרת בצבע אינדיגו
            currentElement.classList.add('ring-2', 'ring-indigo-500', 'ring-offset-1'); 
        }
    }
    console.log(`Element selected: ${selectedElement.type} - ${selectedElement.id}`);

    // --- הוספה: הצגת אזורי הטאבים והסתרת ההודעה ---
    const tabsContainer = document.getElementById('settings-tabs-container');
    const contentArea = document.getElementById('tab-content-area');
    const placeholder = document.getElementById('settings-panel-placeholder');

    if (tabsContainer) {
        tabsContainer.classList.remove('hidden');
        console.log('handleElementSelectionRequest: Removed hidden from settings-tabs-container. Has hidden class:', tabsContainer.classList.contains('hidden'));
    } else {
        console.error('handleElementSelectionRequest: Could not find settings-tabs-container!');
    }
     if (contentArea) {
        contentArea.classList.remove('hidden');
    }
    if (placeholder) {
        placeholder.classList.add('hidden');
    }
    // -------------------------------------------------

    // Load settings for the selected element
    const currentTab = document.querySelector('.tab-button[data-active="true"]')?.dataset.tab || 'content';
    loadSettingsTabContent(currentTab);
}

// Populate the settings panel based on the selected element and tab
function loadSettingsTabContent(tabName) {
    // --- שינוי: קריאה ל-reset אם אין בחירה ---
    if (!selectedElement) {
        // שינוי: ודא שגם הטאבים ויזואלית לא פעילים
        document.querySelectorAll('.settings-tabs .tab-button').forEach(btn => btn.dataset.active = 'false');
        // ושהטאב content (או ברירת מחדל) פעיל אם פותחים את הפאנל
        const contentTabButton = document.querySelector('.settings-tabs .tab-button[data-tab="content"]');
        if(contentTabButton) contentTabButton.dataset.active = 'true';
        
        resetSettingsPanel(); 
        return;
    }
    // ----------------------------------------
    document.querySelectorAll('.tab-button').forEach(tab => {
        // הסר את הסטיילינג הפעיל מכל הלשוניות
        tab.classList.remove('bg-primary-50', 'text-primary-600');
        tab.classList.add('text-gray-500', 'hover:text-gray-700');
        
        // סמן את הלשונית המתאימה כפעילה
        if (tab.dataset.tab === tabName) {
            tab.classList.add('bg-primary-50', 'text-primary-600');
            tab.classList.remove('text-gray-500', 'hover:text-gray-700');
        }
    });

    const elementData = findElementData(pageState, selectedElement.id);
    if (!elementData) {
        console.error("Selected element data not found!", selectedElement.id);
        resetSettingsPanel();
        return;
    }

    // --- שינוי: קבלת קונפיג אפקטיבי --- 
    const currentBreakpoint = getCurrentBreakpoint();
    console.log(`Loading settings for ${selectedElement.type} ${selectedElement.id} at breakpoint: ${currentBreakpoint}`);
    const effectiveConfig = getEffectiveConfig(elementData, currentBreakpoint);
    // -------------------------------------

    // Get the specific tab panel element
    const contentTab = document.getElementById('tab-content-content');
    const designTab = document.getElementById('tab-content-design');
    const advancedTab = document.getElementById('tab-content-advanced');

    // Hide all tabs initially
    contentTab.style.display = 'none';
    designTab.style.display = 'none';
    advancedTab.style.display = 'none';

    // Show the selected tab
    let activeTabPanel = null;
    switch (tabName) {
        case 'content': activeTabPanel = contentTab; break;
        case 'design': activeTabPanel = designTab; break;
        case 'advanced': activeTabPanel = advancedTab; break;
        default: activeTabPanel = contentTab; // Default to content
    }
    activeTabPanel.style.display = 'block';
    activeTabPanel.innerHTML = ''; // Clear previous content

    // Define the update callback function used by settings controls
    // In core.js, find the updateCallback function defined in loadSettingsTabContent

// Define the update callback function used by settings controls
const updateCallback = (updatedElementData) => {
    console.log('[UpdateCallback] Received updatedElementData:', JSON.stringify(updatedElementData));

    // Using the elementData that updateCallback was called with
    // or falling back to the selected element if no specific data provided
    const elementDataForCallback = updatedElementData || 
        (selectedElement ? findElementData(pageState, selectedElement.id) : null);
    
    if (!elementDataForCallback) {
        console.warn('[UpdateCallback] Could not get element data.');
        return; 
    }

    let stylesNeedReapply = false;
    let targetElementId = selectedElement ? selectedElement.id : null;
    let changedColumnData = null;

    // Update text content (if applicable)
    if (elementDataForCallback.type === 'text') {
        const widgetElement = document.querySelector(`[data-widget-id="${elementDataForCallback.id}"]`);
        if (widgetElement) {
            const effectiveConfig = getEffectiveConfig(elementDataForCallback, getCurrentBreakpoint());
            const contentElement = widgetElement.querySelector('.widget-content');
            if (contentElement) {
                contentElement.textContent = effectiveConfig.content || '';
                stylesNeedReapply = true;
            }
        }
    }

    // Handle column width changes
    if (elementDataForCallback.widgets !== undefined && // Is a column
        updatedElementData && 
        updatedElementData.config && 
        'widthPercent' in updatedElementData.config) {
        
        // Find parent row
        const columnData = elementDataForCallback;
        const rowData = findParentRow(pageState, columnData.id);
        if (!rowData) {
            console.warn('[UpdateCallback] Could not find parent row for column:', columnData.id);
            return;
        }

        // Calculate total width of other columns
        const otherColumns = rowData.columns.filter(col => col.id !== columnData.id);
        const totalWidthOfOthersBeforeChange = otherColumns.reduce((sum, col) => {
            const effectiveConfig = getEffectiveConfig(col, getCurrentBreakpoint());
            return sum + parseFloat(effectiveConfig.widthPercent || (100 / rowData.columns.length));
        }, 0);

        // Calculate remaining width
        const newWidth = parseFloat(columnData.config.widthPercent);
        const remainingWidth = 100 - newWidth;

        // Distribute remaining width among other columns
        let distributedWidthSum = 0;
        const minAllowedWidth = 10; // Minimum 10%
        const maxAllowedWidth = 100; // Maximum 100%

        otherColumns.forEach((col) => {
            const effectiveSiblingConfig = getEffectiveConfig(col, getCurrentBreakpoint());
            const originalOtherWidth = parseFloat(effectiveSiblingConfig.widthPercent || (100 / rowData.columns.length));
            let newOtherWidth = (totalWidthOfOthersBeforeChange > 0)
                                ? (originalOtherWidth / totalWidthOfOthersBeforeChange) * remainingWidth
                                : (100 / otherColumns.length);

            newOtherWidth = Math.max(minAllowedWidth, Math.min(maxAllowedWidth, newOtherWidth));
            
            if (!col.config) col.config = {};
            col.config.widthPercent = newOtherWidth.toFixed(2);
            console.log(` -> Updated width for sibling column ${col.id}: ${newOtherWidth.toFixed(2)}%`);
            
            stylesNeedReapply = true;
        });
    }

    // Apply styles to the modified element and its container
    if (stylesNeedReapply || updatedElementData) {
        console.log('Update Callback: Re-applying styles to element:', elementDataForCallback.id);
        
        // Try to find the element by ID
        let element;
        const elementId = elementDataForCallback.id;
        
        if (elementDataForCallback.type === 'text' || elementDataForCallback.type) {
            // Widget
            element = document.querySelector(`[data-widget-id="${elementId}"]`);
        } else if (elementDataForCallback.widgets !== undefined) {
            // Column
            element = document.querySelector(`[data-column-id="${elementId}"]`);
        } else if (elementDataForCallback.columns) {
            // Row
            element = document.querySelector(`[data-row-id="${elementId}"]`);
        }
        
        if (element) {
            const effectiveConfig = getEffectiveConfig(elementDataForCallback, getCurrentBreakpoint());
            
            // Option 1: Direct call to applyStylesToElement
            Render.applyStylesToElement(element, elementDataForCallback);
            
            // If modifying a column, re-render the entire row to ensure proper layout
            if (elementDataForCallback.widgets !== undefined) { // is a column
                const rowElement = element.closest('.row-wrapper');
                if (rowElement) {
                    const rowId = rowElement.dataset.rowId;
                    const rowData = findElementData(pageState, rowId);
                    if (rowData) {
                        // Force a complete re-render of the row
                        rowElement.innerHTML = '';
                        const effectiveRowConfig = getEffectiveConfig(rowData, getCurrentBreakpoint());
                        const newRowElement = Render.renderRow(rowData, effectiveRowConfig);
                        rowElement.parentNode.replaceChild(newRowElement, rowElement);
                        
                        // Re-initialize sortables after DOM change
                        initSortables();
                    }
                }
            } else if (elementDataForCallback.type === 'text') {
                // For text widgets, ensure content is updated
                const contentElement = element.querySelector('.widget-content');
                if (contentElement) {
                    const effectiveConfig = getEffectiveConfig(elementDataForCallback, getCurrentBreakpoint());
                    contentElement.textContent = effectiveConfig.content || '';
                    contentElement.style.color = effectiveConfig.styles?.color || '';
                }
            }
        } else {
            console.warn(`[UpdateCallback] Element not found in DOM: ${elementDataForCallback.id}`);
            
            // If element not found, consider re-rendering the entire page
            console.log('Re-rendering full page content as element was not found');
            Render.renderPageContent(pageState, pageContentContainer);
            initSortables();
        }
    }

    // Save the updated state
    savePageState();
}

    // Populate the active tab based on element type
    const elementType = selectedElement.type;
    // --- שינוי: העברת effectiveConfig לפונקציות populate ---
    switch (elementType) {
        case 'row':
            if (tabName === 'content') {
                populateRowContentTab(activeTabPanel, elementData, effectiveConfig, updateCallback);
            } else if (tabName === 'design') {
                populateRowDesignTab(activeTabPanel, elementData, effectiveConfig, updateCallback);
            } else if (tabName === 'advanced') {
                populateRowAdvancedTab(activeTabPanel, elementData, effectiveConfig, updateCallback);
            }
            break;
        case 'column':
            const parentRowData = findRowContainingElement(pageState, elementData.id);
            if (tabName === 'content' && typeof populateColumnContentTab === 'function') {
                populateColumnContentTab(activeTabPanel, elementData, effectiveConfig, updateCallback, parentRowData);
            } else if (tabName === 'design' && typeof populateColumnDesignTab === 'function') {
                populateColumnDesignTab(activeTabPanel, elementData, effectiveConfig, updateCallback);
            } else if (tabName === 'advanced') {
                populateColumnAdvancedTab(activeTabPanel, elementData, effectiveConfig, updateCallback);
            }
            break;
        case 'widget':
            const widgetModule = getWidgetModule(elementData.type);
            if (widgetModule && widgetModule.createSettingsTabs) {
                 // עדכון קריאה: העברת elementData ו-effectiveConfig בנפרד
                const widgetTabs = widgetModule.createSettingsTabs(activeTabPanel, elementData, effectiveConfig, updateCallback);
                if (widgetTabs[tabName]) {
                    widgetTabs[tabName](); 
                } else {
                    activeTabPanel.innerHTML = `<p class="p-4 text-gray-500">Widget '${elementData.type}' does not have a '${tabName}' tab.</p>`;
                }
            } else {
                activeTabPanel.innerHTML = `<p class="p-4 text-gray-500">Settings not available for widget type: ${elementData.type}</p>`;
            }
            break;
        default:
            activeTabPanel.innerHTML = '<p class="p-4 text-gray-500">Unknown element type selected.</p>';
    }
}

// --- Helper Functions ---
// Function to find element data (row, column, or widget) by ID in the state
function findElementData(state, elementId) {
    for (const row of state) {
        if (row.id === elementId) {
            return row; // Found the row
        }
        for (const column of row.columns) {
            if (column.id === elementId) {
                return column; // Found the column
            }
            for (const widget of column.widgets) {
                if (widget.id === elementId) {
                    return widget; // Found the widget
                }
            }
        }
    }
    return null; // Not found
}

// --- הוספה מחדש: פונקציית עזר למציאת השורה שמכילה אלמנט ---
function findRowContainingElement(state, elementId) {
    for (const row of state) {
        if (row.id === elementId) return row; // Element is the row itself
        for (const column of row.columns) {
            if (column.id === elementId) return row; // Found in this row's columns
            for (const widget of column.widgets) {
                if (widget.id === elementId) return row; // Found in this row's widgets
            }
        }
    }
    return null; // Row not found
}
// ---------------------------------------------------------

// Function to get the corresponding module for a widget type
function getWidgetModule(widgetType) {
    switch (widgetType) {
        case 'text':
            return TextWidget;
        // Add cases for other widget types here
        default:
            console.error('Unknown widget type:', widgetType);
            return null;
    }
}

// --- SortableJS Setup ---
let rowSortableInstance = null;
let widgetSourceSortableInstance = null;
let columnSortableInstances = {}; // Store instances for columns

function initSortables() {
    console.log('Initializing SortableJS...');
    // Destroy existing instances if re-initializing
    if (rowSortableInstance) rowSortableInstance.destroy();
    if (widgetSourceSortableInstance) widgetSourceSortableInstance.destroy();
    Object.values(columnSortableInstances).forEach(instance => {
        // הוספת בדיקה אם המופע תקין לפני ניסיון הריסה
        if (instance && typeof instance.destroy === 'function') {
            instance.destroy();
        }
    });
    // שינוי: איפוס האובייקט לאחר הריסת המופעים
    columnSortableInstances = {}; 

    // 1. Sort Rows
    rowSortableInstance = new Sortable(pageContentContainer, {
        animation: 150,
        group: 'rows',
        handle: '.row-drag-handle', // Need to add a drag handle to row controls
        ghostClass: 'sortable-ghost-row', // Custom ghost class for rows
        chosenClass: 'sortable-chosen-row',
        onEnd: (evt) => {
            console.log('Row moved', evt);
            const { oldIndex, newIndex } = evt;
            // Update pageState array order
             const movedRow = pageState.splice(oldIndex, 1)[0];
             pageState.splice(newIndex, 0, movedRow);
             console.log('Row state updated:', pageState);
            // No need to re-render, Sortable handles DOM move
        }
    });

    // 2. Sort Columns within each Row (Needs dynamic initialization)
    document.querySelectorAll('.row-wrapper .columns-container').forEach(container => {
         const rowId = container.closest('.row-wrapper')?.dataset.rowId;
         if (!rowId) return;
         columnSortableInstances[rowId] = new Sortable(container, {
             animation: 150,
             group: `columns-${rowId}`, // Group columns within the same row
             handle: '.column-drag-handle', // Need drag handle
             ghostClass: 'sortable-ghost-col',
             chosenClass: 'sortable-chosen-col',
             onEnd: (evt) => {
                 console.log('Column moved', evt);
                 const { oldIndex, newIndex } = evt;
                 const rowData = pageState.find(r => r.id === rowId);
                 if (rowData) {
                     const movedCol = rowData.columns.splice(oldIndex, 1)[0];
                     rowData.columns.splice(newIndex, 0, movedCol);
                     console.log('Column state updated:', rowData.columns);
                 }
             }
         });
    });

    // 3. Sort Widgets within/between Columns (Needs dynamic initialization)
    document.querySelectorAll('.column-wrapper').forEach(colEl => {
        const colId = colEl.dataset.columnId;
        const rowId = colEl.closest('.row-wrapper')?.dataset.rowId;
        if (!colId || !rowId) return;

        columnSortableInstances[colId] = new Sortable(colEl, {
            animation: 150,
            group: {
                 name: 'widgets',
                 pull: true,
                 put: true
            },
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag', // Optional: Class for the item being dragged

            // Add event listeners for custom highlighting
            onChoose: function(evt) {
                // Optional: Add class when starting to drag *from* this column
            },
            onUnchoose: function(evt) {
                 // Optional: Remove class when stopped dragging *from* this column
            },

            onStart: function(evt) {
                // When drag starts *within* or *from* this column
                // If dragging existing, ensure drop-target class is removed from others
                 document.querySelectorAll('.column-drop-target-active').forEach(el => el.classList.remove('column-drop-target-active'));
            },

            onMove: function (evt, originalEvent) {
                // Check if dragging a new widget and over an empty column
                const isDraggingNew = document.body.classList.contains('is-dragging-new-widget');
                const targetCol = evt.to;
                const isEmptyColumn = targetCol.classList.contains('is-empty');
                // const relatedElement = evt.related; // שינוי: אין צורך בזה יותר

                // Remove active class from previously targeted columns
                if (evt.from !== evt.to) { // Only if moving *between* columns
                     document.querySelectorAll('.column-drop-target-active').forEach(el => {
                         if (el !== targetCol) el.classList.remove('column-drop-target-active');
                     });
                }

                if (isDraggingNew && isEmptyColumn) {
                    // Add special highlight class to the target empty column
                    targetCol.classList.add('column-drop-target-active');
                    // שינוי: פשוט נאפשר את התנועה אם זה ווידג'ט חדש לעמודה ריקה
                    // אלמנט ה-ghost יופיע, אך המראה יוגדר ע"י CSS
                    return true;
                } else {
                    // Not dragging new OR target column is not empty
                    // Remove special highlight if it exists
                    targetCol.classList.remove('column-drop-target-active');
                    // Allow SortableJS default behavior (ghost placement etc.)
                     return true; // Allow the move
                }
            },

            // onEnd is potentially too late for removing highlight if dropped outside
            // onUnchoose might be better, or handle removal in source list onEnd

            onAdd: (evt) => {
                 console.log('Widget add detected', evt);
                 const itemEl = evt.item;
                 const sourceList = evt.from;
                 const toColEl = evt.to;
                 const toColId = toColEl.dataset.columnId;
                 const toRowId = toColEl.closest('.row-wrapper')?.dataset.rowId;
                 const widgetType = itemEl.dataset.widgetType;

                 // Ensure highlight is removed from the target column after add
                 toColEl.classList.remove('column-drop-target-active'); // Ensure removal

                 if (sourceList.id === 'widget-source-list' && widgetType) {
                     console.log(`Adding new widget of type ${widgetType} from source list.`);
                     itemEl.remove();
                     const targetRow = pageState.find(r => r.id === toRowId);
                     const targetColumn = targetRow?.columns.find(c => c.id === toColId);
                     if (targetColumn) {
                         let newWidgetData = null;
                         if (widgetType === 'text') {
                             newWidgetData = { id: generateId('w'), type: 'text', config: TextWidget.getDefaultConfig() };
                         }
                         if (newWidgetData) {
                             targetColumn.widgets.push(newWidgetData);
                             console.log(`Widget ${widgetType} data pushed to end of col ${toColId}`);
                             console.log('Updated column widgets:', targetColumn.widgets);
                             Render.renderPageContent(pageState, pageContentContainer);
                             initSortables();
                         }
                     } else {
                          console.error('Target column not found in state for adding widget from source');
                     }
                 } else if (widgetType) {
                      console.warn('Dragged item has widgetType but did not originate from source list?', evt);
                      itemEl.remove();
                 } else {
                     console.log('Widget moved from another column, handled by onEnd.');
                 }
            },
            onEnd: (evt) => {
                 console.log('Widget move ended', evt);
                 // Ensure highlight is removed if drag ends unexpectedly or normally
                 evt.to.classList.remove('column-drop-target-active');
                 evt.from.classList.remove('column-drop-target-active');

                 const { from, to, oldIndex, newIndex, item } = evt;
                 const widgetId = item.dataset.widgetId;
                 if (evt.pullMode === 'clone') {
                      console.log('onEnd ignored for clone operation (handled by onAdd).');
                      return;
                 }
                 // Find source and target columns/rows in state
                 const fromColId = from.dataset.columnId;
                 const fromRowId = from.closest('.row-wrapper')?.dataset.rowId;
                 const toColId = to.dataset.columnId;
                 const toRowId = to.closest('.row-wrapper')?.dataset.rowId;

                 if (fromColId === toColId && fromRowId === toRowId && oldIndex === newIndex) {
                      console.log('No actual move.');
                      return;
                 }

                 const sourceRow = pageState.find(r => r.id === fromRowId);
                 const sourceCol = sourceRow?.columns.find(c => c.id === fromColId);
                 const targetRow = pageState.find(r => r.id === toRowId);
                 const targetCol = targetRow?.columns.find(c => c.id === toColId);

                 if (sourceCol && targetCol) {
                     // Find the widget data using widgetId
                     const movedWidgetIndex = sourceCol.widgets.findIndex(w => w.id === widgetId);
                     if (movedWidgetIndex > -1) {
                         // Remove widget from source column state
                         const [movedWidget] = sourceCol.widgets.splice(movedWidgetIndex, 1);
                         // Add widget to target column state at the correct index
                         targetCol.widgets.splice(newIndex, 0, movedWidget);
                         console.log(`Widget ${widgetId} moved from ${fromColId}[${movedWidgetIndex}] to ${toColId}[${newIndex}]`);
                         console.log('Source widgets:', sourceCol.widgets);
                         console.log('Target widgets:', targetCol.widgets);

                         // Check if re-render is needed (e.g., if moving between different column types in future)
                         // For now, assume SortableJS DOM update is sufficient.
                         // If issues arise, uncomment below:
                         // Render.renderPageContent(pageState, pageContentContainer);
                         // initSortables();

                     } else {
                         console.error('Moved widget not found in source column state!', widgetId);
                         // Attempt to recover? Maybe re-render?
                     }
                 } else {
                     console.error('Could not find source or target column in state for move end');
                 }
            }
        });
    });

    // 4. Initialize the Widget Source List (for dragging NEW widgets)
    widgetSourceSortableInstance = new Sortable(widgetSourceList, {
        group: {
            name: 'widgets', // Same group as columns
            pull: 'clone',  // Clone the item when dragging
            put: false      // Can't drop items back into this list
        },
        sort: false, // Don't allow sorting within the source list
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        // Add start/end events to manage body class
         onStart: function(evt) {
             document.body.classList.add('is-dragging-new-widget');
             console.log('Started dragging new widget');
         },
         onEnd: function(evt) {
             document.body.classList.remove('is-dragging-new-widget');
             // Also ensure column highlights are cleared if drag ends outside
             document.querySelectorAll('.column-drop-target-active').forEach(el => el.classList.remove('column-drop-target-active'));
             console.log('Finished dragging new widget');
         }
    });

     console.log('SortableJS Initialized');
}


// --- Initialization ---
function initBuilder() {
    console.log('Initializing builder...');

    // Load initial data
    loadInitialContent();

    // Attach event listeners
    if (addRowButton) {
        // Change listener to toggle popover instead of adding row directly
        addRowButton.addEventListener('click', handleToggleColumnChoice);
    }
    // Add listener for clicks inside the popover
    if (columnChoicePopover) {
         columnChoicePopover.addEventListener('click', handleColumnChoiceClick);
    }
    // Add listener to close popover if clicking outside
    document.addEventListener('click', (event) => {
        if (columnChoicePopover && !columnChoicePopover.classList.contains('hidden')) {
            if (!columnChoicePopover.contains(event.target) && event.target !== addRowButton && !addRowButton.contains(event.target)) {
                columnChoicePopover.classList.add('hidden');
                console.log('Closed column choice popover due to outside click');
            }
        }
    });

    if (responsiveControls) {
        responsiveControls.addEventListener('click', handleResponsiveViewChange);
    }
    settingsTabs.forEach(tab => {
         tab.addEventListener('click', handleTabClick);
    });
    
    // מאזין אירועים להתאמה מיוחדת
    document.addEventListener('clear-page', resetPageState);
    
    // אירועים מותאמים אישית
    window.addEventListener('add-row', () => {
        // Ensure popover is closed before potentially opening it again if needed
        if (columnChoicePopover && !columnChoicePopover.classList.contains('hidden')) {
             columnChoicePopover.classList.add('hidden');
        }
        // Directly trigger adding a row, perhaps default to 1 column or open popover
        // For now, let's simulate clicking the add row button logic without event
         handleToggleColumnChoice({ stopPropagation: () => {} }); // Re-opens popover
    });

    // הוספה: מאזין לאירוע הוספת שורה במצב ריק
    window.addEventListener('add-default-row', () => {
        console.log('Handling add-default-row event');
        handleAddRowWithColumns(1); // הוספת שורה עם עמודה אחת
    });

    // מאזין למחיקת ווידג'ט
    window.addEventListener('delete-widget', (event) => {
        const widgetId = event.detail.id;
        console.log('Core handling delete-widget event for ID:', widgetId);
        if (deleteWidgetFromState(widgetId)) {
            if (selectedElement && selectedElement.id === widgetId) {
                selectedElement = null;
                resetSettingsPanel();
            }
            Render.renderPageContent(pageState, pageContentContainer);
            initSortables(); // Re-initialize sortables after DOM change
            savePageState(); // Save the change
        }
    });
    
    // מאזין למחיקת שורה
    window.addEventListener('delete-row', (event) => {
        const rowId = event.detail.id;
        console.log('Core handling delete-row event for ID:', rowId);
        if (deleteRowFromState(rowId)) {
             if (selectedElement && selectedElement.id === rowId) {
                selectedElement = null;
                resetSettingsPanel();
            }
            Render.renderPageContent(pageState, pageContentContainer);
            initSortables(); // Re-initialize sortables after DOM change
             savePageState(); // Save the change
        }
    });

    // הוספה: מאזין לאירוע מחיקת עמודה
    window.addEventListener('delete-column', (event) => {
        const columnId = event.detail.id;
        console.log('Core handling delete-column event for ID:', columnId);
        if (deleteColumnFromState(columnId)) {
            if (selectedElement && selectedElement.id === columnId) {
                selectedElement = null;
                resetSettingsPanel();
            }
            Render.renderPageContent(pageState, pageContentContainer);
            initSortables(); // Re-initialize sortables after DOM change
            savePageState(); // Save the change
        }
    });

    // הוספה: מאזין לאירוע הזזת עמודה
    window.addEventListener('move-column', (event) => {
        const { id: columnId, direction } = event.detail;
        console.log(`Core handling move-${direction} event for column ID:`, columnId);
        if (moveColumnInState(columnId, direction)) {
            // No need to deselect, just re-render
            Render.renderPageContent(pageState, pageContentContainer);
            initSortables(); 
            savePageState();
        }
    });

    // הוספה: מאזין לאירוע בחירת אלמנט מ-render.js
    window.addEventListener('select-element', handleElementSelectionRequest);

    // --- הוספה: מאזין לאירוע שינוי תצוגה גלובלית מהפקדים ---
    document.addEventListener('change-global-breakpoint', (event) => {
        const breakpoint = event.detail.breakpoint;
        console.log(`Core received change-global-breakpoint event for: ${breakpoint}`);
        // מצא את הכפתור הראשי המתאים בסרגל העליון
        const targetButton = responsiveControls.querySelector(`.responsive-button[data-view="${breakpoint}"]`);
        if (targetButton && targetButton.dataset.active !== 'true') {
            console.log('Simulating click on main responsive button:', targetButton);
            targetButton.click(); // הפעל לחיצה על הכפתור הראשי
        } else if (targetButton) {
            console.log('Global breakpoint already set to', breakpoint);
        } else {
            console.error('Could not find main responsive button for breakpoint:', breakpoint);
        }
    });
    // --- סוף הוספה ---

    // Initial SortableJS setup
    initSortables();

    console.log('Builder initialized.');
    // --- הוספה: וידוא איפוס פאנל ההגדרות בטעינה ראשונית --- 
    resetSettingsPanel();
    // ----------------------------------------------------
}

document.addEventListener('DOMContentLoaded', initBuilder);

// Example of how we might export functions if needed later
// export { addWidget, selectWidget }; 

function initializeWidgetControls(widget) {
    // יצירת סרגל כלים צידי
    const toolbar = document.getElementById('widget-toolbar-template').content.cloneNode(true).querySelector('.widget-toolbar');
    const widgetWrapper = widget.closest('.widget-wrapper');
    
    // מחיקת סרגל קיים אם יש
    const existingToolbar = widgetWrapper.querySelector('.widget-toolbar');
    if (existingToolbar) {
        existingToolbar.remove();
    }
    
    // הוספת אירועי לחיצה לכפתורים
    toolbar.querySelectorAll('.widget-toolbar-button').forEach(button => {
        button.addEventListener('click', (e) => {
            e.stopPropagation();
            const action = button.dataset.action;
            
            switch(action) {
                case 'edit':
                    // כבר מתרחש בלחיצה על הווידג'ט עצמו
                    widget.click();
                    break;
                case 'duplicate':
                    duplicateWidget(widget);
                    break;
                case 'delete':
                    deleteWidget(widget);
                    break;
                case 'move':
                    // התחלת גרירה
                    button.style.cursor = 'grabbing';
                    // יש לממש את זה בהתאם לספריית גרירה שבשימוש
                    break;
            }
        });
    });
    
    widgetWrapper.appendChild(toolbar);
}



function deleteWidget(widget) {
    const widgetId = widget.dataset.widgetId;
    if (!widgetId) return;
    
    // הסרת הווידג'ט מהמודל
    removeWidgetById(widgetId);
    
    // הסרה מה-DOM
    const widgetWrapper = widget.closest('.widget-wrapper');
    if (widgetWrapper) {
        widgetWrapper.remove();
    }
    
    // איפוס פאנל ההגדרות אם זה היה הווידג'ט הנבחר
    if (selectedElement && selectedElement.id === widgetId) {
        selectedElement = null;
        resetSettingsPanel();
    }
}

// פונקציות עזר נוספות שיש לממש במלואן בהתאם למימוש הקיים

// בפונקציה שמטפלת ביצירת ווידג'ט, אחרי יצירת האלמנט, הוספת הקוד:
// נניח שהפונקציה נקראת createWidgetElement(widgetData)

// בתוך הפונקציה createWidgetElement, אחרי יצירת כל האלמנטים של הווידג'ט
// widgetElement הוא אלמנט הווידג'ט שנוצר

// פונקציה להוספה: 
function initializeIconGroups() {
    // מאזין לקבוצות אייקונים (למשל בפדינג)
    document.querySelectorAll('.icon-group').forEach(group => {
        const buttons = group.querySelectorAll('.icon-button');
        
        buttons.forEach(button => {
            button.addEventListener('click', () => {
                // הסרת המצב פעיל מכל הכפתורים בקבוצה
                buttons.forEach(btn => btn.classList.remove('active'));
                
                // הוספת מצב פעיל לכפתור הנוכחי
                button.classList.add('active');
                
                // בדיקה אם זו קבוצת פדינג
                if (button.dataset.padding) {
                    const mode = button.dataset.padding;
                    handlePaddingModeChange(mode, group);
                }
                
                // טיפול באירועים של קבוצות אייקונים אחרות...
            });
        });
    });
}

function handlePaddingModeChange(mode, group) {
    // מציאת הקונטיינר הקרוב של הקלטים
    const inputsContainer = group.closest('.settings-accordion-content').querySelector('.padding-inputs');
    const inputs = inputsContainer.querySelectorAll('input');
    
    // טיפול במצבים שונים
    switch(mode) {
        case 'all':
            // כל הצדדים - רק הקלט הראשון פעיל, השאר משוכפלים ממנו
            inputs.forEach((input, index) => {
                if (index === 0) {
                    input.disabled = false;
                    input.classList.remove('opacity-50');
                } else {
                    input.disabled = true;
                    input.classList.add('opacity-50');
                    // העתקת הערך מהראשון
                    input.value = inputs[0].value;
                }
            });
            break;
        case 'vertical':
            // רק למעלה ולמטה פעילים
            inputs.forEach((input, index) => {
                if (index === 0 || index === 2) { // top, bottom
                    input.disabled = false;
                    input.classList.remove('opacity-50');
                } else {
                    input.disabled = true;
                    input.classList.add('opacity-50');
                    // העתקת ערכים
                    if (index === 1) input.value = inputs[3].value; // right = left
                    if (index === 3) input.value = inputs[1].value; // left = right
                }
            });
            break;
        case 'horizontal':
            // רק ימין ושמאל פעילים
            inputs.forEach((input, index) => {
                if (index === 1 || index === 3) { // right, left
                    input.disabled = false;
                    input.classList.remove('opacity-50');
                } else {
                    input.disabled = true;
                    input.classList.add('opacity-50');
                    // העתקת ערכים
                    if (index === 0) input.value = inputs[2].value; // top = bottom
                    if (index === 2) input.value = inputs[0].value; // bottom = top
                }
            });
            break;
        case 'individual':
            // כל הצדדים פעילים בנפרד
            inputs.forEach(input => {
                input.disabled = false;
                input.classList.remove('opacity-50');
            });
            break;
    }
    
    // עדכון ערכי הפדינג לפי המצב הנוכחי
    updatePaddingValues();
}

function updatePaddingValues() {
    // כאן יש לממש את העדכון של ערכי הפדינג במודל הנתונים
    // בהתאם לערכים הנוכחיים בפקדים
    // ...
}

/**
 * פונקציה לשכפול ווידג'ט כולל כל ההגדרות שלו
 * @param {string} widgetId המזהה של הווידג'ט לשכפול
 */
function duplicateWidget(widgetId) {
    console.log('Duplicating widget:', widgetId);
    
    // מצא את הווידג'ט בתוך מבנה העמוד
    let widgetData = null;
    let parentColumn = null;
    let widgetIndex = -1;
    
    // חיפוש הווידג'ט והעמודה המכילה אותו
    for (const row of pageState) {
        for (const column of row.columns) {
            const index = column.widgets.findIndex(w => w.id === widgetId);
            if (index !== -1) {
                widgetData = column.widgets[index];
                parentColumn = column;
                widgetIndex = index;
                break;
            }
        }
        if (widgetData) break;
    }
    
    if (!widgetData) {
        console.error(`Widget with ID ${widgetId} not found for duplication`);
        return;
    }
    
    // יצירת העתק עמוק של הווידג'ט (כולל כל ההגדרות)
    const clonedWidget = JSON.parse(JSON.stringify(widgetData));
    
    // הגדרת מזהה חדש לווידג'ט המשוכפל
    const oldId = clonedWidget.id;
    clonedWidget.id = generateId('w'); // שימוש בפונקציה שמייצרת מזהים ייחודיים
    
    console.log(`Created cloned widget with new ID: ${clonedWidget.id} (original: ${oldId})`);
    
    // הוספת הווידג'ט המשוכפל אחרי הווידג'ט המקורי
    parentColumn.widgets.splice(widgetIndex + 1, 0, clonedWidget);
    
    // רינדור מחדש של הדף
    Render.renderPageContent(pageState, pageContentContainer);    
    // איתחול מחדש של SortableJS אחרי שינוי במבנה ה-DOM
    initSortables();
    reinitializeWidgetToolbars();
    // שמירת מצב העמוד המעודכן
    savePageState();
    
    
    // בחירת הווידג'ט החדש בפאנל ההגדרות
    window.dispatchEvent(new CustomEvent('select-element', { 
        detail: { type: 'widget', id: clonedWidget.id } 
    }));
    
    console.log(`Widget duplication completed for: ${widgetId}`);
    
}

// עדכון הפונקציה שמטפלת באירוע 'duplicate-widget' אם כבר קיימת
window.addEventListener('duplicate-widget', (event) => {
    const widgetId = event.detail.id;
    duplicateWidget(widgetId);
});

// הוספת קריאה לפונקציה initializeIconGroups בעת אתחול הווידג'טים או בעת יצירת פקדי הגדרות
// למשל בתוך setupEventListeners או בפונקציה דומה:

// אחרי כל קריאה לפונקציה דומה ל-setupEventListeners
// או במקום מתאים בקוד שמתבצע בטעינת העמוד:
initializeIconGroups();

// ובכל פעם שווידג'ט נוצר, להוסיף קריאה ל:
// initializeWidgetControls(widgetElement); 

// פונקציה לאיפוס מצב העמוד
function resetPageState() {
    if (confirm('האם אתה בטוח שברצונך לאפס את העמוד? כל התוכן יימחק.')) {
        pageState = [];
        localStorage.removeItem('builderPageState');
        Render.renderPageContent(pageState, pageContentContainer);
        resetSettingsPanel();
        console.log('העמוד אופס בהצלחה');
    }
}

// פונקציה למחיקת ווידג'ט מה-state
function deleteWidgetFromState(widgetId) {
    for (let i = 0; i < pageState.length; i++) {
        const row = pageState[i];
        for (let j = 0; j < row.columns.length; j++) {
            const column = row.columns[j];
            const widgetIndex = column.widgets.findIndex(w => w.id === widgetId);
            if (widgetIndex !== -1) {
                column.widgets.splice(widgetIndex, 1);
                console.log(`Widget ${widgetId} removed from state.`);
                return true; // Indicate success
            }
        }
    }
    console.warn(`Widget ${widgetId} not found in state for deletion.`);
    return false; // Indicate failure
}

// פונקציה למחיקת שורה מה-state
function deleteRowFromState(rowId) {
    const rowIndex = pageState.findIndex(r => r.id === rowId);
    if (rowIndex !== -1) {
        pageState.splice(rowIndex, 1);
        console.log(`Row ${rowId} removed from state.`);
        return true; // Indicate success
    }
    console.warn(`Row ${rowId} not found in state for deletion.`);
    return false; // Indicate failure
}

// הוספה: פונקציה למחיקת עמודה מה-state
function deleteColumnFromState(columnId) {
    for (let i = 0; i < pageState.length; i++) {
        const row = pageState[i];
        const columnIndex = row.columns.findIndex(c => c.id === columnId);
        if (columnIndex !== -1) {
            row.columns.splice(columnIndex, 1);
            console.log(`Column ${columnId} removed from row ${row.id} state.`);

            // --- הוספה: התאמת רוחב עמודה נותרת ---
            if (row.columns.length === 1) {
                if (!row.columns[0].config) {
                    row.columns[0].config = {}; // ודא שאובייקט הקונפיג קיים
                }
                row.columns[0].config.widthPercent = '100'; 
                console.log(`Row ${row.id} now has 1 column, setting width to 100%.`);
            }
            // --------------------------------------

            // Optional: Check if row is now empty and delete it?
            // if (row.columns.length === 0) {
            //     deleteRowFromState(row.id); 
            // }
            return true; // Indicate success
        }
    }
    console.warn(`Column ${columnId} not found in state for deletion.`);
    return false; // Indicate failure
}

function reinitializeWidgetToolbars() {
    console.log('Reinitializing widget toolbars...');
    
    // מוצא את כל הווידג'טים בדף
    const allWidgets = document.querySelectorAll('.widget-wrapper[data-widget-id]');
    
    allWidgets.forEach(widgetWrapper => {
        const widgetId = widgetWrapper.dataset.widgetId;
        if (!widgetId) return;
        
        // מוצא את הנתונים של הווידג'ט
        const widgetData = findWidgetById(widgetId, pageState);
        if (!widgetData) {
            console.warn(`Cannot find data for widget: ${widgetId}`);
            return;
        }
        
        // מוודא שיש לווידג'ט סרגל כלים ושהוא מקושר נכון
        const toolbar = widgetWrapper.querySelector('.widget-toolbar');
        
        // אם אין סרגל כלים או שהוא לא מקושר נכון, מוסיף אחד חדש
        if (!toolbar || toolbar.id !== `toolbar-${widgetId}`) {
            // מוחק סרגל קיים במידה ויש
            if (toolbar) toolbar.remove();
            
            // קורא לפונקציה שמוסיפה סרגל כלים תקין
            Render.addWidgetControls(widgetWrapper, widgetData);
        }
    });
}

function findWidgetById(widgetId, state) {
    for (const row of state) {
        for (const column of row.columns) {
            const widget = column.widgets.find(w => w.id === widgetId);
            if (widget) return widget;
        }
    }
    return null;
}

// הוספה: פונקציה להזזת עמודה במערך ב-state
function moveColumnInState(columnId, direction) {
    for (let i = 0; i < pageState.length; i++) {
        const row = pageState[i];
        const columnIndex = row.columns.findIndex(c => c.id === columnId);
        
        if (columnIndex !== -1) {
            const numColumns = row.columns.length;
            let newIndex = columnIndex;

            if (direction === 'left' && columnIndex > 0) {
                newIndex = columnIndex - 1;
            } else if (direction === 'right' && columnIndex < numColumns - 1) {
                newIndex = columnIndex + 1;
            } else {
                console.warn('Cannot move column further in that direction.');
                return false; // Cannot move further
            }

            // Perform the move using splice
            const [movedColumn] = row.columns.splice(columnIndex, 1);
            row.columns.splice(newIndex, 0, movedColumn);

            console.log(`Column ${columnId} moved from index ${columnIndex} to ${newIndex} in row ${row.id}.`);
            return true; // Indicate success
        }
    }
    console.warn(`Column ${columnId} not found in state for moving.`);
    return false; // Indicate failure
} 