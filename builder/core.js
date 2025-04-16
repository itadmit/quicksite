// Core builder logic
import * as Render from './render.js';
import * as TextWidget from './widgets/text.js';
import * as CommonSettings from './common-settings.js'; // Import common settings
// Import row settings functions
import { populateRowContentTab, populateRowDesignTab, populateRowAdvancedTab } from './row-settings.js';
// Import column settings functions
import { populateColumnContentTab, populateColumnDesignTab, populateColumnAdvancedTab } from './column-settings.js';
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
    settingsPanelContent.innerHTML = '';
    const emptyMessage = document.createElement('div');
    emptyMessage.className = 'p-4 text-center';
    emptyMessage.innerHTML = `
        <div class="mb-4">
            <i class="ri-settings-4-line text-4xl text-gray-300"></i>
        </div>
        <p class="text-gray-500 text-sm mb-2">בחר אלמנט לעריכה</p>
        <p class="text-gray-400 text-xs">לחץ על אלמנט בעמוד כדי לערוך את ההגדרות שלו</p>
    `;
    settingsPanelContent.appendChild(emptyMessage);
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
                styles: {} // מקום לסגנונות ספציפיים לעמודה
            }
        });
    }
    
    // הוספת השורה החדשה למבנה הנתונים
    pageState.push({
        id: rowId,
        columns: columns,
        config: {
            styles: {} // מקום לסגנונות ספציפיים לשורה
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

    // Load settings for the selected element
    const currentTab = document.querySelector('.tab-button[data-active="true"]')?.dataset.tab || 'content';
    loadSettingsTabContent(currentTab);
}

// Populate the settings panel based on the selected element and tab
function loadSettingsTabContent(tabName) {
    if (!selectedElement) {
        resetSettingsPanel();
        return;
    }

    const elementData = findElementData(pageState, selectedElement.id);
    if (!elementData) {
        console.error("Selected element data not found!", selectedElement.id);
        resetSettingsPanel();
        return;
    }

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

    // Define the update callback
    const updateCallback = () => {
        savePageState();

        if (selectedElement) {
            const elementData = findElementData(pageState, selectedElement.id);

            if (selectedElement.type === 'column') {
                const rowData = findRowContainingElement(pageState, selectedElement.id);
                if (rowData && rowData.columns) {
                    console.log('Column changed, applying styles to all columns in row:', rowData.id);
                    rowData.columns.forEach(col => {
                        const colNode = document.querySelector(`[data-element-id="${col.id}"]`);
                        if (colNode) {
                            Render.applyStylesToElement(colNode, col);
                        } else {
                            console.warn(`Could not find DOM node for column ${col.id} during sibling update.`);
                        }
                    });
                } else {
                    console.warn('Could not find parent row data for column update.');
                }
            } else if (elementData) {
                const elementNode = document.querySelector(`[data-element-id="${selectedElement.id}"]`);
                if (elementNode) {
                    console.log('Applying styles directly to selected element (non-column):', selectedElement.id);
                    Render.applyStylesToElement(elementNode, elementData);
                } else {
                    console.warn('Could not find element node for direct style update (non-column).');
                }
            } else {
                 console.warn('Could not find element data for style update.');
            }
        }
    };

    // Populate the active tab based on element type
    const elementType = selectedElement.type;
    switch (elementType) {
        case 'row':
            // שימוש בפונקציות המיובאות מ-row-settings.js
            if (tabName === 'content') {
                populateRowContentTab(activeTabPanel, elementData, updateCallback);
            } else if (tabName === 'design') {
                populateRowDesignTab(activeTabPanel, elementData, updateCallback);
            } else if (tabName === 'advanced') {
                populateRowAdvancedTab(activeTabPanel, elementData, updateCallback);
            }
            break;
        case 'column':
            // Placeholder for column settings
            // --- מציאת נתוני השורה והעברתם ---
            const parentRowData = findRowContainingElement(pageState, elementData.id);
            // ----------------------------------
            if (tabName === 'content' && typeof populateColumnContentTab === 'function') {
                // --- שינוי: הוספת parentRowData לקריאה ---
                populateColumnContentTab(activeTabPanel, elementData, updateCallback, parentRowData);
            } else if (tabName === 'design' && typeof populateColumnDesignTab === 'function') {
                populateColumnDesignTab(activeTabPanel, elementData, updateCallback);
            } else if (tabName === 'advanced') {
                populateColumnAdvancedTab(activeTabPanel, elementData, updateCallback);
            }
            break;
        case 'widget':
            const widgetModule = getWidgetModule(elementData.type);
            if (widgetModule && widgetModule.createSettingsTabs) {
                const widgetTabs = widgetModule.createSettingsTabs(activeTabPanel, elementData, updateCallback);
                if (widgetTabs[tabName]) {
                    widgetTabs[tabName](); // Call the specific tab function
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

    // Initial SortableJS setup
    initSortables();

    console.log('Builder initialized.');
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

function duplicateWidget(widget) {
    const widgetId = widget.dataset.widgetId;
    const widgetData = getWidgetById(widgetId);
    if (!widgetData) return;
    
    // יצירת העתק עמוק של נתוני הווידג'ט
    const newWidgetData = JSON.parse(JSON.stringify(widgetData));
    newWidgetData.id = generateUniqueId(); // יצירת מזהה חדש לווידג'ט
    
    // הוספה לאותה עמודה
    const column = widget.closest('.column-wrapper');
    if (column) {
        const columnId = column.dataset.columnId;
        addWidgetToColumn(newWidgetData, columnId);
        
        // רענון התצוגה
        renderContent();
    }
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