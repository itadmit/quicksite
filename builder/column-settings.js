// builder/column-settings.js
// הגדרות ספציפיות לעמודות

import { 
    createSettingsGroup, 
    createColorInput, 
    createLinkedInputs, 
    createTextInput,
    createNumberInput, 
    createSelect,
    createSlider,
    createButtonGroup // Add this import
    // Import more helpers as needed
} from './common-settings.js';

console.log('Column Settings module loaded');

// --- Exported Functions ---

export function populateColumnContentTab(panel, columnData, updateCallback, rowData) {
    panel.innerHTML = ''; // Clear
    if (!columnData.config) columnData.config = {};
    const config = columnData.config;

    // Initialize defaults
    if (config.verticalAlign === undefined) config.verticalAlign = 'flex-start'; // Default top
    if (config.horizontalAlign === undefined) config.horizontalAlign = 'stretch'; // Default stretch
    if (config.widgetSpacing === undefined) config.widgetSpacing = 15; // Default spacing
    if (config.htmlTag === undefined) config.htmlTag = 'div';
    if (config.widthPercent === undefined) { 
        // Calculate default if missing (needs rowData)
        if (rowData && rowData.columns && rowData.columns.length > 0) {
            config.widthPercent = (100 / rowData.columns.length).toFixed(2);
        } else {
            config.widthPercent = 100; // Fallback
        }
    }

    // --- Layout Group ---
    const { accordionItem: layoutAccordion, contentDiv: layoutContent } = createSettingsGroup('פריסה', true);

    // Column Width (Existing logic - check if needs adjustment)
    const widthContainer = document.createElement('div');
    widthContainer.className = 'mb-4';
    const widthLabel = document.createElement('label');
    widthLabel.className = 'block text-sm text-gray-600 mb-1';
    widthLabel.textContent = 'רוחב עמודה (%)';
    widthContainer.appendChild(widthLabel);
    const widthValue = parseFloat(config.widthPercent).toFixed(2);
    const widthSlider = createSlider(null, widthValue, 5, 95, 0.1, (value) => { 
        const newValue = parseFloat(value).toFixed(2);
        config.widthPercent = newValue;
        widthNumberInput.querySelector('input').value = newValue;
        handleWidthChange(columnData.id, newValue, updateCallback, rowData);
    }, '%');
    const widthNumberInput = createNumberInput(null, widthValue, (value) => { 
        const newValue = parseFloat(value).toFixed(2);
        const clampedValue = Math.max(5, Math.min(95, newValue));
        config.widthPercent = clampedValue;
        widthSlider.querySelector('input[type="range"]').value = clampedValue;
        widthNumberInput.querySelector('input').value = clampedValue;
        handleWidthChange(columnData.id, clampedValue, updateCallback, rowData);
    }, 5, 95, 0.1);
    widthSlider.querySelector('input[type="range"]').className += ' mb-1'; 
    widthContainer.appendChild(widthSlider);
    widthContainer.appendChild(widthNumberInput);
    layoutContent.appendChild(widthContainer);

    // Vertical Alignment of Widgets
    const vAlignContainer = document.createElement('div');
    vAlignContainer.className = 'mb-4';
    vAlignContainer.appendChild(createSelect(
        'יישור אנכי (ווידג\'טים)',
        [
            { value: 'flex-start', label: 'למעלה' },
            { value: 'center', label: 'מרכז' },
            { value: 'flex-end', label: 'למטה' },
            { value: 'stretch', label: 'מתיחה' }, // If widgets have height
            { value: 'space-between', label: 'רווח בין' },
            { value: 'space-around', label: 'רווח מסביב' }
        ],
        config.verticalAlign,
        (value) => { config.verticalAlign = value; updateCallback(); } // Requires CSS on column-widgets-container
    ));
    layoutContent.appendChild(vAlignContainer);

    // Horizontal Alignment of Widgets
    const hAlignContainer = document.createElement('div');
    hAlignContainer.className = 'mb-4';
    hAlignContainer.appendChild(createSelect(
        'יישור אופקי (ווידג\'טים)',
        [
            { value: 'flex-start', label: 'שמאל' },
            { value: 'center', label: 'מרכז' },
            { value: 'flex-end', label: 'ימין' },
            { value: 'stretch', label: 'מתיחה' } // Default for block elements
        ],
        config.horizontalAlign,
        (value) => { config.horizontalAlign = value; updateCallback(); } // Requires CSS on column-widgets-container
    ));
    layoutContent.appendChild(hAlignContainer);

    // Widget Spacing
    const spacingContainer = document.createElement('div');
    spacingContainer.className = 'mb-4';
    spacingContainer.appendChild(createNumberInput('מרווח בין ווידג\'טים (px)', config.widgetSpacing, (value) => {
        config.widgetSpacing = parseInt(value) || 0;
        updateCallback(); 
    }, 0)); 
    layoutContent.appendChild(spacingContainer);
    
    // HTML Tag
    const tagContainer = document.createElement('div');
    tagContainer.className = 'mb-4';
    tagContainer.appendChild(createSelect(
        'תגית HTML',
        [
            { value: 'div', label: 'div' },
            { value: 'aside', label: 'aside' },
            { value: 'main', label: 'main' },
            { value: 'section', label: 'section' }
        ],
        config.htmlTag,
        (value) => { config.htmlTag = value; updateCallback(); }
    ));
    layoutContent.appendChild(tagContainer);

    panel.appendChild(layoutAccordion);
}

export function populateColumnDesignTab(panel, columnData, updateCallback) {
    panel.innerHTML = ''; // Clear
    if (!columnData.config) columnData.config = {};
    if (!columnData.config.styles) columnData.config.styles = {};
    const styles = columnData.config.styles;

    // Initialize defaults if they don't exist
    if (styles.backgroundColor === undefined) styles.backgroundColor = ''; 
    if (!styles.padding) styles.padding = { top: '10', right: '10', bottom: '10', left: '10', linked: true }; 
    if (!styles.border) styles.border = { width: '0', style: 'none', color: '#000000' };
    if (!styles.borderRadius) styles.borderRadius = { value: '0', unit: 'px' };

    // --- Background --- 
    const { accordionItem: bgAccordion, contentDiv: bgContent } = createSettingsGroup('רקע', true);
    bgContent.appendChild(createColorInput('צבע רקע', styles.backgroundColor || '#ffffff', (value) => {
        styles.backgroundColor = value;
        updateCallback();
    }));
    panel.appendChild(bgAccordion);

    // --- Padding Section --- 
    const { accordionItem: paddingAccordion, contentDiv: paddingContent } = createSettingsGroup('ריפוד (Padding)');
    const paddingLabels = [
        { key: 'top', label: 'עליון', placeholder: '10' },
        { key: 'right', label: 'ימין', placeholder: '10' },
        { key: 'bottom', label: 'תחתון', placeholder: '10' },
        { key: 'left', label: 'שמאל', placeholder: '10' }
    ];
    paddingContent.appendChild(createLinkedInputs(paddingLabels, styles.padding, 'px', true, updateCallback));
    panel.appendChild(paddingAccordion);

    // --- Border Section ---
    const { accordionItem: borderAccordion, contentDiv: borderContent } = createSettingsGroup('מסגרת (Border)');
    borderContent.appendChild(createColorInput('צבע', styles.border.color, (value) => { styles.border.color = value; updateCallback(); }));
    const borderControlsRow = document.createElement('div');
    borderControlsRow.className = 'grid grid-cols-2 gap-2 mt-3';
    const widthWithLabel = document.createElement('div'); widthWithLabel.className = 'flex flex-col';
    const widthLabel = document.createElement('span'); widthLabel.className = 'text-xs text-gray-500 mb-1'; widthLabel.textContent = 'עובי';
    widthWithLabel.appendChild(widthLabel);
    widthWithLabel.appendChild(createNumberInput(null, parseInt(styles.border.width) || 0, (value) => { styles.border.width = parseInt(value) || 0; updateCallback(); }, 0, 50, 1));
    const styleWithLabel = document.createElement('div'); styleWithLabel.className = 'flex flex-col';
    const styleLabel = document.createElement('span'); styleLabel.className = 'text-xs text-gray-500 mb-1'; styleLabel.textContent = 'סגנון';
    styleWithLabel.appendChild(styleLabel);
    styleWithLabel.appendChild(createSelect(
        null, // No main label
        [{value: 'solid', label:'רציף'}, {value: 'dashed', label:'מקווקו'}, {value: 'dotted', label:'נקודות'}, {value: 'none', label: 'ללא'}],
        styles.border.style, (value) => { styles.border.style = value; updateCallback(); }
    ));
    borderControlsRow.appendChild(widthWithLabel);
    borderControlsRow.appendChild(styleWithLabel);
    borderContent.appendChild(borderControlsRow);
    panel.appendChild(borderAccordion);

    // --- Border Radius Section ---
    const { accordionItem: radiusAccordion, contentDiv: radiusContent } = createSettingsGroup('עיגול פינות (Radius)');
    const radiusContainer = document.createElement('div');
    radiusContainer.className = 'grid grid-cols-2 gap-2';
    radiusContainer.appendChild(createNumberInput(null, parseInt(styles.borderRadius.value) || 0, (value) => { styles.borderRadius.value = parseInt(value) || 0; updateCallback(); }, 0, 100));
    radiusContainer.appendChild(createSelect([
        { value: 'px', label: 'px' },
        { value: '%', label: '%' }
    ], styles.borderRadius.unit, (value) => { styles.borderRadius.unit = value; updateCallback(); }));
    radiusContent.appendChild(radiusContainer);
    panel.appendChild(radiusAccordion);
}

export function populateColumnAdvancedTab(panel, columnData, updateCallback) {
    panel.innerHTML = ''; // Clear
    if (!columnData.config) columnData.config = {};
    if (!columnData.config.styles) columnData.config.styles = {}; // Ensure styles exist
    const config = columnData.config;
    const styles = columnData.config.styles;

    // Initialize defaults if they don't exist
    if (!styles.margin) styles.margin = { top: '', right: '', bottom: '', left: '', linked: true };
    if (!styles.boxShadow) styles.boxShadow = { type: 'none', x: '0', y: '0', blur: '0', spread: '0', color: 'rgba(0,0,0,0.1)'};
    if (config.customId === undefined) config.customId = '';
    if (config.customClass === undefined) config.customClass = '';
    if (!config.visibility) config.visibility = { desktop: true, tablet: true, mobile: true };

    // --- Margin Section ---
    const { accordionItem: marginAccordion, contentDiv: marginContent } = createSettingsGroup('שוליים (Margin)');
    const marginLabels = [
        { key: 'top', label: 'עליון', placeholder: '0' },
        { key: 'right', label: 'ימין', placeholder: '0' },
        { key: 'bottom', label: 'תחתון', placeholder: '0' },
        { key: 'left', label: 'שמאל', placeholder: '0' }
    ];
    marginContent.appendChild(createLinkedInputs(marginLabels, styles.margin, 'px', true, updateCallback));
    panel.appendChild(marginAccordion);

    // --- Shadow Section ---
    const { accordionItem: shadowAccordion, contentDiv: shadowContent } = createSettingsGroup('צל (Shadow)');
    const shadowTypeRow = document.createElement('div'); shadowTypeRow.className = 'mb-3';
    shadowTypeRow.appendChild(createSelect('סוג צל', [
        {value: 'none', label: 'ללא'}, 
        {value: 'drop-shadow', label: 'Drop Shadow'}
    ], styles.boxShadow.type, (value) => { styles.boxShadow.type = value; updateCallback(); }));
    shadowContent.appendChild(shadowTypeRow);
    // Add inputs only if type is not 'none'
    if (styles.boxShadow.type !== 'none') {
        const shadowXYRow = document.createElement('div'); shadowXYRow.className = 'grid grid-cols-2 gap-2 mb-3';
        shadowXYRow.appendChild(createNumberInput('X', parseInt(styles.boxShadow.x) || 0, (value) => { styles.boxShadow.x = parseInt(value) || 0; updateCallback(); }));
        shadowXYRow.appendChild(createNumberInput('Y', parseInt(styles.boxShadow.y) || 0, (value) => { styles.boxShadow.y = parseInt(value) || 0; updateCallback(); }));
        shadowContent.appendChild(shadowXYRow);
        const shadowBlurSpreadRow = document.createElement('div'); shadowBlurSpreadRow.className = 'grid grid-cols-2 gap-2 mb-3';
        shadowBlurSpreadRow.appendChild(createNumberInput('Blur', parseInt(styles.boxShadow.blur) || 0, (value) => { styles.boxShadow.blur = parseInt(value) || 0; updateCallback(); }, 0));
        shadowBlurSpreadRow.appendChild(createNumberInput('Spread', parseInt(styles.boxShadow.spread) || 0, (value) => { styles.boxShadow.spread = parseInt(value) || 0; updateCallback(); }));
        shadowContent.appendChild(shadowBlurSpreadRow);
        shadowContent.appendChild(createColorInput('צבע', styles.boxShadow.color, (value) => { styles.boxShadow.color = value; updateCallback(); }, true));
    }
    panel.appendChild(shadowAccordion);

    // --- Custom Identifiers Section ---
    const { accordionItem: idClassAccordion, contentDiv: idClassContent } = createSettingsGroup('מזהים וקלאסים');
    const idContainer = document.createElement('div'); idContainer.className = 'mb-3';
    idContainer.appendChild(createTextInput('Custom ID', config.customId, (value) => {
        config.customId = value.replace(/[^a-zA-Z0-9-_]/g, '').trim();
        // No callback needed for ID usually
    }, 'my-unique-col-id'));
    idClassContent.appendChild(idContainer);
    
    const classContainer = document.createElement('div'); classContainer.className = 'mb-3';
    classContainer.appendChild(createTextInput('Custom CSS Classes', config.customClass, (value) => {
        config.customClass = value.replace(/[^a-zA-Z0-9-_\s]/g, '').trim();
        updateCallback(); // Rerender needed for classes
    }, 'my-col-class another-class'));
    idClassContent.appendChild(classContainer);
    panel.appendChild(idClassAccordion);

    // --- Visibility Section (Placeholder) ---
    const { accordionItem: visibilityAccordion, contentDiv: visibilityContent } = createSettingsGroup('נראות (Visibility)');
    const placeholder = document.createElement('p');
    placeholder.textContent = 'הגדרות נראות יופיעו כאן (בקרוב).';
    placeholder.className = 'text-gray-500 text-sm p-2';
    visibilityContent.appendChild(placeholder);
    // TODO: Add checkboxes or toggles for desktop, tablet, mobile visibility
    // e.g., config.visibility = { desktop: true, tablet: true, mobile: false };
    // Need corresponding CSS classes applied in render.js based on these settings and current view
    panel.appendChild(visibilityAccordion);
}

// פונקציה פנימית לטיפול בשינוי רוחב והתאמת עמודות שכנות
function handleWidthChange(changedColumnId, newWidthPercent, generalCallback, rowData) {
    if (!rowData || !rowData.columns || rowData.columns.length <= 1) {
        generalCallback(); // אין עמודות אחרות להתאים
        return;
    }

    const currentWidth = parseFloat(newWidthPercent);
    const targetColumnIndex = rowData.columns.findIndex(c => c.id === changedColumnId);
    if (targetColumnIndex === -1) {
        console.error("Changed column not found in rowData for width adjustment");
        generalCallback();
        return;
    }

    const otherColumns = rowData.columns.filter(c => c.id !== changedColumnId);
    const numOtherColumns = otherColumns.length;
    if (numOtherColumns === 0) {
        // If this was the only other column, it should now be 100 - currentWidth
        // But the slider prevents going to full 100, so this case might not happen easily
        generalCallback();
        return;
    }

    let totalWidthOfOthersBeforeChange = 0;
    otherColumns.forEach(col => {
        totalWidthOfOthersBeforeChange += parseFloat(col.config?.widthPercent || (100 / rowData.columns.length));
    });
    
    // Calculate the available width for other columns
    const remainingWidth = 100 - currentWidth;
    
    // Distribute remaining width proportionally among other columns
    let distributedWidthSum = 0;
    otherColumns.forEach((col, index) => {
        const originalOtherWidth = parseFloat(col.config?.widthPercent || (100 / rowData.columns.length));
        let newOtherWidth = (originalOtherWidth / totalWidthOfOthersBeforeChange) * remainingWidth;
        
        // Clamp the new width to avoid extremes (e.g., 5% to 95%)
        newOtherWidth = Math.max(5, Math.min(95, newOtherWidth));
        
        col.config.widthPercent = newOtherWidth.toFixed(2);
        distributedWidthSum += newOtherWidth;
    });

    // Adjust the last column slightly if the sum isn't exactly 100 due to clamping/rounding
    const finalAdjustment = remainingWidth - distributedWidthSum;
    if (Math.abs(finalAdjustment) > 0.1 && numOtherColumns > 0) {
        const lastOtherCol = otherColumns[numOtherColumns - 1];
        let lastOtherWidth = parseFloat(lastOtherCol.config.widthPercent) + finalAdjustment;
        lastOtherWidth = Math.max(5, Math.min(95, lastOtherWidth)); // Clamp again
        lastOtherCol.config.widthPercent = lastOtherWidth.toFixed(2);
    }

    generalCallback();
} 