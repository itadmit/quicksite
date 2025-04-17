// builder/utils.js
// Shared utility functions

console.log('Utils module loaded');

/**
 * Function to find element data (row, column, or widget) by ID in the state
 * @param {Array} state - The current page state
 * @param {string} elementId - The ID of the element to find
 * @returns {object|null} The found element data or null
 */
export function findElementData(state, elementId) {
    for (const row of state) {
        if (row.id === elementId) return row;
        for (const column of row.columns) {
            if (column.id === elementId) return column;
            for (const widget of column.widgets) {
                if (widget.id === elementId) return widget;
            }
        }
    }
    return null;
}

/**
 * Function to find the row containing a specific element
 * @param {Array} state - The current page state
 * @param {string} elementId - The ID of the element to find
 * @returns {object|null} The containing row or null
 */
export function findRowContainingElement(state, elementId) {
    if (!state || !Array.isArray(state)) {
        console.error('Invalid state passed to findRowContainingElement:', state);
        return null;
    }

    for (const row of state) {
        if (row.id === elementId) return row; // Element is the row itself
        if (Array.isArray(row.columns)) {
            for (const column of row.columns) {
                if (column.id === elementId) return row; // Found in this row's columns
                if (Array.isArray(column.widgets)) {
                    for (const widget of column.widgets) {
                        if (widget.id === elementId) return row; // Found in this row's widgets
                    }
                }
            }
        }
    }
    return null; // Row not found
}

/**
 * Function to generate unique IDs
 * @param {string} [prefix='el'] - Optional prefix for the ID.
 * @returns {string} A unique ID.
 */
export function generateId(prefix = 'el') {
    return `${prefix}-${Date.now().toString(36)}-${Math.random().toString(36).substring(2, 7)}`;
}

// Add other utility functions here if needed 