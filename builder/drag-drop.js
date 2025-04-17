// builder/drag-drop.js
// Handles all SortableJS setup and logic

// Import SortableJS library (assuming it's loaded globally via CDN or bundled)
// If using npm: import Sortable from 'sortablejs';

console.log('Drag & Drop module loaded');

// Store Sortable instances
let rowSortableInstance = null;
let widgetSourceSortableInstance = null;
let columnSortableInstances = {}; // To store instances for columns and widgets within columns

/**
 * Initializes or re-initializes SortableJS for rows, columns, and widgets.
 * @param {HTMLElement} canvasElement - The main canvas container element.
 */
export function setupDragAndDrop(canvasElement) {
    console.log('Setting up Drag and Drop...');

    // Placeholder logic - A full implementation requires handling onEnd, onAdd etc.
    // to update the pageState correctly when items are moved.
    
    // Destroy existing instances before re-initializing
    if (rowSortableInstance) rowSortableInstance.destroy();
    if (widgetSourceSortableInstance) widgetSourceSortableInstance.destroy();
    Object.values(columnSortableInstances).forEach(instance => {
        if (instance && typeof instance.destroy === 'function') {
            instance.destroy();
        }
    });
    columnSortableInstances = {};

    if (!canvasElement) {
        console.error("Canvas element not found for drag & drop setup.");
        return;
    }

    // 1. Sort Rows (using the canvasElement as the container)
    rowSortableInstance = new Sortable(canvasElement, {
        animation: 150,
        group: 'rows',
        handle: '.row-drag-handle', // Make sure your rows have this handle
        ghostClass: 'sortable-ghost-row', 
        chosenClass: 'sortable-chosen-row',
        onEnd: (evt) => {
            console.log('Row move ended:', evt.oldIndex, '->', evt.newIndex);
            // TODO: Update pageState array order based on evt.oldIndex and evt.newIndex
            // Example: 
            // const movedRow = pageState.splice(evt.oldIndex, 1)[0];
            // pageState.splice(evt.newIndex, 0, movedRow);
            // savePageState(); // Need access to this function from core
        }
    });

    // 2. Sort Columns within each Row (requires iterating through rows)
    canvasElement.querySelectorAll('.columns-container').forEach(container => {
         const rowId = container.closest('.row-wrapper')?.dataset.rowId;
         if (!rowId) return;
         columnSortableInstances[`cols-${rowId}`] = new Sortable(container, {
             animation: 150,
             group: `columns-${rowId}`,
             handle: '.column-drag-handle', // Add this handle to columns
             ghostClass: 'sortable-ghost-col',
             chosenClass: 'sortable-chosen-col',
             onEnd: (evt) => {
                 console.log(`Column moved in row ${rowId}:`, evt.oldIndex, '->', evt.newIndex);
                 // TODO: Find rowData in pageState and update column order
                 // Example:
                 // const rowData = pageState.find(r => r.id === rowId);
                 // if (rowData) { ... update rowData.columns order ... }
                 // savePageState();
             }
         });
    });

    // 3. Sort Widgets within/between Columns 
    canvasElement.querySelectorAll('.column-widgets-container').forEach(container => {
        const colId = container.closest('.column-wrapper')?.dataset.columnId;
        if (!colId) return;
        columnSortableInstances[`widgets-${colId}`] = new Sortable(container, {
            animation: 150,
            group: {
                 name: 'widgets', // Common group for all widget containers
                 pull: true,
                 put: true // Allow putting items from other columns or source list
            },
            handle: '.widget-drag-handle', // Add handle to widgets if needed
            ghostClass: 'sortable-ghost-widget',
            chosenClass: 'sortable-chosen-widget',
            onAdd: (evt) => {
                const itemEl = evt.item; // The dragged element
                const toColId = evt.to.closest('.column-wrapper')?.dataset.columnId;
                const widgetType = itemEl.dataset.widgetType; // Check if it came from source
                
                console.log(`Widget added to column ${toColId}. From source? ${!!widgetType}`);
                
                if (widgetType) { // Item came from the source list
                    itemEl.remove(); // Remove the clone from the source list
                    // TODO: Create new widget data based on widgetType 
                    // TODO: Find target column in pageState and push new widget data
                    // TODO: Re-render relevant parts or whole page
                    // TODO: savePageState();
                    console.log(`   -> Need to add new ${widgetType} widget to state`);
                } else {
                     // Item moved from another column - handled by onEnd
                }
            },
            onEnd: (evt) => {
                const widgetId = evt.item.dataset.widgetId;
                const fromColId = evt.from.closest('.column-wrapper')?.dataset.columnId;
                const toColId = evt.to.closest('.column-wrapper')?.dataset.columnId;
                const oldIndex = evt.oldIndex;
                const newIndex = evt.newIndex;
                
                console.log(`Widget ${widgetId} move ended: ${fromColId}[${oldIndex}] -> ${toColId}[${newIndex}]`);
                
                if (fromColId === toColId && oldIndex === newIndex) return; // No change
                
                // TODO: Find source and target columns in pageState
                // TODO: Remove widget from source state
                // TODO: Add widget to target state at newIndex
                // TODO: savePageState();
            }
        });
    });

    // 4. Initialize the Widget Source List
    const widgetSourceListElement = document.getElementById('widget-source-list');
    if (widgetSourceListElement) {
        widgetSourceSortableInstance = new Sortable(widgetSourceListElement, {
            group: {
                name: 'widgets',
                pull: 'clone',
                put: false
            },
            sort: false,
            animation: 150,
            ghostClass: 'sortable-ghost-source',
            chosenClass: 'sortable-chosen-source',
             onStart: function(evt) {
                document.body.classList.add('is-dragging-new-widget'); 
             },
             onEnd: function(evt) {
                document.body.classList.remove('is-dragging-new-widget');
             }
        });
    } else {
        console.warn("Widget source list element not found.");
    }

    console.log('Drag & Drop setup complete.');
} 