// builder/column-settings.js
// הגדרות ספציפיות לעמודות

import { 
    createSettingsGroup, 
    createColorPicker,
    createLinkedInputs, 
    createTextInput,
    createNumberInput, 
    createSelect,
    createSlider,
    createButtonGroup,
    createVisibilityControls
} from './common-settings.js';

// --- הוספה: ייבוא פונקציית שמירה רספונסיבית ---
import { saveResponsiveSetting, getSettingOverrideStatus, getEffectiveConfig, getNestedValue, getCurrentBreakpoint } from './render-responsive.js';
// ---------------------------------------------

// --- הוספה: פונקציית עזר להמרת RGBA ל-HEX (אם היא לא קיימת ב common-settings) ---
function rgbaToHex(rgbaString) {
    // Simple check if it might be RGBA
    if (typeof rgbaString !== 'string' || !rgbaString.toLowerCase().startsWith('rgba')) {
        return rgbaString; // Return original if not RGBA or not string
    }
    try {
        const rgba = rgbaString.match(/\d+(\.\d+)?/g);
        if (!rgba || rgba.length < 3) return rgbaString; // Invalid format

        const r = parseInt(rgba[0]).toString(16).padStart(2, '0');
        const g = parseInt(rgba[1]).toString(16).padStart(2, '0');
        const b = parseInt(rgba[2]).toString(16).padStart(2, '0');
        
        // Handle optional alpha for opacity input, but hex doesn't include alpha
        // let alpha = 'ff'; 
        // if (rgba.length === 4) { alpha = Math.round(parseFloat(rgba[3]) * 255).toString(16).padStart(2, '0'); }

        return `#${r}${g}${b}`;
    } catch (e) {
        console.warn("Failed to convert RGBA to HEX:", e);
        return rgbaString; // Return original on error
    }
}
// ---------------------------------------------------------------------------------

console.log('Column Settings module loaded');

// --- Exported Functions ---

export function populateColumnContentTab(panel, elementData, effectiveConfig, updateCallback, rowData) {
    panel.innerHTML = ''; 
    const config = effectiveConfig; 

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

    // --- הוספה: קביעת גבול עליון דינמי ---
    const isSingleColumn = rowData && rowData.columns && rowData.columns.length === 1;
    const maxAllowedWidth = isSingleColumn ? 100 : 95;
    const minAllowedWidth = 5; // לשמור על גבול תחתון קבוע
    // ----------------------------------------

    // --- Layout Group ---
    const { accordionItem: layoutAccordion, contentDiv: layoutContent } = createSettingsGroup('פריסה', true);

    // Column Width (Revised logic)
    const widthContainer = document.createElement('div');
    widthContainer.className = 'mb-4';
    const widthLabel = document.createElement('label');
    widthLabel.className = 'block text-sm text-gray-600 mb-1';
    widthLabel.textContent = 'רוחב עמודה (%)';
    widthContainer.appendChild(widthLabel);
    
    const initialWidthValue = parseFloat(config.widthPercent).toFixed(2);

    // Create slider - immediate callback only updates number input and config (NO state update)
    const widthSliderWrapper = createSlider(null, initialWidthValue, minAllowedWidth, maxAllowedWidth, 0.1, (value) => {
        const newValue = parseFloat(value).toFixed(2);
        config.widthPercent = newValue; // Update config directly for immediate feedback
        widthNumberInput.querySelector('input').value = newValue; // Update linked input visually
        // DO NOT call handleWidthChange or updateCallback here
    }, '%');
    const sliderInput = widthSliderWrapper.querySelector('input[type="range"]');
    sliderInput.className += ' mb-1';

    // Create number input - immediate callback only updates slider and config (NO state update)
    const widthNumberInput = createNumberInput(null, initialWidthValue, (value) => {
        const newValue = parseFloat(value).toFixed(2);
        // --- שינוי: שימוש בגבולות דינמיים --- 
        const clampedValue = Math.max(minAllowedWidth, Math.min(maxAllowedWidth, newValue)); 
        // ------------------------------------
        config.widthPercent = clampedValue; // Update config directly
        sliderInput.value = clampedValue; // Update linked input visually
        widthNumberInput.querySelector('input').value = clampedValue; // Ensure number input shows clamped value
        // DO NOT call handleWidthChange or updateCallback here
    }, minAllowedWidth, maxAllowedWidth, 0.1);
    const numberInput = widthNumberInput.querySelector('input');

    // Function to finalize width change (called on 'change' event)
    const finalizeWidthChange = (event) => {
        let finalValue = parseFloat(event.target.value);
        finalValue = Math.max(minAllowedWidth, Math.min(maxAllowedWidth, finalValue));
        const finalValueStr = finalValue.toFixed(2);

        // Ensure both inputs and config reflect the final clamped value
        config.widthPercent = finalValueStr;
        sliderInput.value = finalValue;
        numberInput.value = finalValueStr; 

        console.log(`Finalizing width change for ${elementData.id} to ${finalValueStr}%`);
        
        // --- שינוי: הוספת getCurrentBreakpoint() כפרמטר רביעי ---
        saveResponsiveSetting(elementData, ['config', 'widthPercent'], finalValueStr, getCurrentBreakpoint(), updateCallback);
        // ---------------------------------------------------------
    };

    // Add 'change' event listeners to trigger the final update
    sliderInput.addEventListener('change', finalizeWidthChange);
    numberInput.addEventListener('change', finalizeWidthChange);
    // Consider 'blur' for number input as well? 'change' might be sufficient.
    // numberInput.addEventListener('blur', finalizeWidthChange); 

    widthContainer.appendChild(widthSliderWrapper);
    widthContainer.appendChild(widthNumberInput);
    layoutContent.appendChild(widthContainer);

    // Vertical Alignment of Widgets
    const vAlignContainer = document.createElement('div');
    vAlignContainer.className = 'mb-4';
    const vAlignLabel = document.createElement('label');
    vAlignLabel.className = 'block text-sm text-gray-600 mb-1';
    vAlignLabel.textContent = 'יישור אנכי (ווידג\'טים)';
    vAlignContainer.appendChild(vAlignLabel);
    vAlignContainer.appendChild(createSelect(
        [
            { value: 'flex-start', label: 'למעלה' },
            { value: 'center', label: 'מרכז' },
            { value: 'flex-end', label: 'למטה' },
            { value: 'stretch', label: 'מתיחה' }, // If widgets have height
            { value: 'space-between', label: 'רווח בין' },
            { value: 'space-around', label: 'רווח מסביב' }
        ],
        config.verticalAlign,
        (value) => { saveResponsiveSetting(elementData, ['config', 'verticalAlign'], value, getCurrentBreakpoint(), updateCallback); }
    ));
    layoutContent.appendChild(vAlignContainer);

    // Horizontal Alignment of Widgets
    const hAlignContainer = document.createElement('div');
    hAlignContainer.className = 'mb-4';
    const hAlignLabel = document.createElement('label');
    hAlignLabel.className = 'block text-sm text-gray-600 mb-1';
    hAlignLabel.textContent = 'יישור אופקי (ווידג\'טים)';
    hAlignContainer.appendChild(hAlignLabel);
    hAlignContainer.appendChild(createSelect(
        [
            { value: 'flex-start', label: 'שמאל' },
            { value: 'center', label: 'מרכז' },
            { value: 'flex-end', label: 'ימין' },
            { value: 'stretch', label: 'מתיחה' } // Default for block elements
        ],
        config.horizontalAlign,
        (value) => { saveResponsiveSetting(elementData, ['config', 'horizontalAlign'], value, getCurrentBreakpoint(), updateCallback); }
    ));
    layoutContent.appendChild(hAlignContainer);

    // Widget Spacing
    const spacingContainer = document.createElement('div');
    spacingContainer.className = 'mb-4';
    spacingContainer.appendChild(createNumberInput('מרווח בין ווידג\'טים (px)', config.widgetSpacing, (value) => {
        saveResponsiveSetting(elementData, ['config', 'widgetSpacing'], parseInt(value) || 0, getCurrentBreakpoint(), updateCallback);
    }, 0)); 
    layoutContent.appendChild(spacingContainer);
    
    // HTML Tag
    const tagContainer = document.createElement('div');
    tagContainer.className = 'mb-4';
    const tagLabel = document.createElement('label');
    tagLabel.className = 'block text-sm text-gray-600 mb-1';
    tagLabel.textContent = 'תגית HTML';
    tagContainer.appendChild(tagLabel);
    tagContainer.appendChild(createSelect(
        [
            { value: 'div', label: 'div' },
            { value: 'aside', label: 'aside' },
            { value: 'main', label: 'main' },
            { value: 'section', label: 'section' }
        ],
        config.htmlTag,
        (value) => { saveResponsiveSetting(elementData, ['config', 'htmlTag'], value, getCurrentBreakpoint(), updateCallback); }
    ));
    layoutContent.appendChild(tagContainer);

    panel.appendChild(layoutAccordion);
}

export function populateColumnDesignTab(panel, elementData, effectiveConfig, updateCallback) {
    panel.innerHTML = ''; 
    const config = effectiveConfig; 
    const styles = elementData.config.styles || {}; 

    // Initialize defaults if they don't exist
    if (styles.backgroundColor === undefined) styles.backgroundColor = ''; 
    if (!styles.padding) {
        styles.padding = { top: '10', right: '10', bottom: '10', left: '10', unit: 'px' };
    } else if (styles.padding.unit === undefined) {
        styles.padding.unit = 'px';
    }
    if (!styles.border) styles.border = { width: '0', style: 'none', color: '#000000' };
    if (!styles.borderRadius) styles.borderRadius = { value: '0', unit: 'px' };

    // --- פונקציית עזר לטעינת ערך אפקטיבי לברייקפוינט ספציפי --- 
    const fetchSettingValue = (breakpoint, path) => {
        const specificEffectiveConfig = getEffectiveConfig(elementData, breakpoint);
        return getNestedValue(specificEffectiveConfig, path);
    };
    // -------------------------------------------------------------

    // --- Background --- 
    const { accordionItem: bgAccordion, contentDiv: bgContent } = createSettingsGroup('רקע', true);
    const bgColorLabel = document.createElement('label');
    bgColorLabel.className = 'block text-sm text-gray-600 mb-1';
    bgColorLabel.textContent = 'צבע רקע';
    bgContent.appendChild(bgColorLabel);
    bgContent.appendChild(createColorPicker(
        config.styles?.backgroundColor || 'transparent',
        // --- שינוי: הוספת getCurrentBreakpoint() ---
        (value) => { saveResponsiveSetting(elementData, ['styles', 'backgroundColor'], value, getCurrentBreakpoint(), updateCallback); }
        // ----------------------------------------
    ));
    panel.appendChild(bgAccordion);

    // --- Padding Section --- 
    const { accordionItem: paddingAccordion, contentDiv: paddingContent } = createSettingsGroup('ריפוד (Padding)');
    const paddingSettingPath = ['styles', 'padding'];
    const effectivePadding = config.styles?.padding || { top: '10', right: '10', bottom: '10', left: '10', unit: 'px' };
    const paddingOverrideStatus = getSettingOverrideStatus(elementData, paddingSettingPath);
    paddingContent.appendChild(createLinkedInputs(
        'ריפוד',
        elementData,
        paddingSettingPath,
        ['px', '%', 'em', 'rem'],
        effectivePadding.unit || 'px',
        updateCallback
    ));
    panel.appendChild(paddingAccordion);

    // --- Border Section ---
    const { accordionItem: borderAccordion, contentDiv: borderContent } = createSettingsGroup('מסגרת (Border)');
    const borderColorLabel = document.createElement('label');
    borderColorLabel.className = 'block text-sm text-gray-600 mb-1';
    borderColorLabel.textContent = 'צבע';
    borderContent.appendChild(borderColorLabel);
    borderContent.appendChild(createColorPicker(
        config.styles?.border?.color || '#000000',
        // --- שינוי: הוספת getCurrentBreakpoint() ---
        (value) => { saveResponsiveSetting(elementData, ['styles', 'border', 'color'], value, getCurrentBreakpoint(), updateCallback); }
        // ----------------------------------------
    ));
    
    // Width and Style in a row
    const strokeControlsRow = document.createElement('div');
    strokeControlsRow.className = 'grid grid-cols-2 gap-2 mt-3';

    // Width with label
    const widthWithLabel = document.createElement('div');
    widthWithLabel.className = 'flex flex-col';
    const widthLabel = document.createElement('span');
    widthLabel.className = 'text-xs text-gray-500 mb-1';
    widthLabel.textContent = 'עובי';
    widthWithLabel.appendChild(widthLabel);
    widthWithLabel.appendChild(createNumberInput(null, parseInt(config.styles?.border?.width) || 0, 
        // --- שינוי: הוספת getCurrentBreakpoint() ---
        (value) => { saveResponsiveSetting(elementData, ['styles', 'border', 'width'], `${parseInt(value) || 0}px`, getCurrentBreakpoint(), updateCallback); }, 0, 50, 1));
        // ----------------------------------------

    // Style with label
    const styleWithLabel = document.createElement('div');
    styleWithLabel.className = 'flex flex-col';
    const styleLabel = document.createElement('span');
    styleLabel.className = 'text-xs text-gray-500 mb-1';
    styleLabel.textContent = 'סגנון';
    styleWithLabel.appendChild(styleLabel);
    styleWithLabel.appendChild(createSelect(
        [{value: 'solid', label:'רציף'}, {value: 'dashed', label:'מקווקו'}, {value: 'dotted', label:'נקודות'}, {value: 'none', label: 'ללא'}],
        config.styles?.border?.style || 'none',
        // --- שינוי: הוספת getCurrentBreakpoint() ---
        (value) => { saveResponsiveSetting(elementData, ['styles', 'border', 'style'], value, getCurrentBreakpoint(), updateCallback); }
        // ----------------------------------------
    ));

    strokeControlsRow.appendChild(widthWithLabel);
    strokeControlsRow.appendChild(styleWithLabel);
    borderContent.appendChild(strokeControlsRow);
    
    // Border Radius
    const radiusContainer = document.createElement('div');
    radiusContainer.className = 'mt-3 grid grid-cols-2 gap-2';
    const radiusLabel = document.createElement('span');
    radiusLabel.className = 'text-xs text-gray-500 mb-1 col-span-2';
    radiusLabel.textContent = 'עיגול פינות';
    radiusContainer.appendChild(radiusLabel);
    radiusContainer.appendChild(createNumberInput(null, parseInt(config.styles?.borderRadius?.value) || 0,
        // --- שינוי: הוספת getCurrentBreakpoint() ---
        (value) => { saveResponsiveSetting(elementData, ['styles', 'borderRadius', 'value'], `${parseInt(value) || 0}`, getCurrentBreakpoint(), updateCallback); }, 0));
        // ----------------------------------------
    radiusContainer.appendChild(createSelect(
        [{value: 'px', label: 'px'}, {value: '%', label: '%'}],
        config.styles?.borderRadius?.unit || 'px',
        // --- שינוי: הוספת getCurrentBreakpoint() ---
        (value) => { saveResponsiveSetting(elementData, ['styles', 'borderRadius', 'unit'], value, getCurrentBreakpoint(), updateCallback); }
        // ----------------------------------------
    ));
    borderContent.appendChild(radiusContainer);

    panel.appendChild(borderAccordion);
}

export function populateColumnAdvancedTab(panel, elementData, effectiveConfig, updateCallback) {
    panel.innerHTML = ''; 
    const config = effectiveConfig; 
    const styles = elementData.config.styles || {};

    // Initialize defaults if they don't exist
    if (!styles.margin) {
        styles.margin = { top: '0', right: '0', bottom: '0', left: '0', unit: 'px' };
    } else if (styles.margin.unit === undefined) {
        styles.margin.unit = 'px';
    }
    if (!styles.boxShadow) styles.boxShadow = { type: 'none', x: '0', y: '0', blur: '0', spread: '0', color: 'rgba(0,0,0,0.1)'};
    if (config.customId === undefined) config.customId = '';
    if (config.customClass === undefined) config.customClass = '';
    if (!config.visibility) config.visibility = { desktop: true, tablet: true, mobile: true };

    // --- פונקציית עזר לטעינת ערך אפקטיבי לברייקפוינט ספציפי --- 
    const fetchSettingValue = (breakpoint, path) => {
        const specificEffectiveConfig = getEffectiveConfig(elementData, breakpoint);
        return getNestedValue(specificEffectiveConfig, path);
    };
    // -------------------------------------------------------------

    // --- Margin Section ---
    const { accordionItem: marginAccordion, contentDiv: marginContent } = createSettingsGroup('שוליים (Margin)');
    const marginSettingPath = ['styles', 'margin'];
    const effectiveMargin = config.styles?.margin || { top: '0', right: '0', bottom: '0', left: '0', unit: 'px' };
    
    marginContent.appendChild(createLinkedInputs(
        'שוליים',
        elementData,
        marginSettingPath,
        ['px', '%', 'em', 'rem'],
        effectiveMargin.unit || 'px',
        updateCallback
    ));
    panel.appendChild(marginAccordion);

    // --- Shadow Section ---
    const { accordionItem: shadowAccordion, contentDiv: shadowContent } = createSettingsGroup('צל (Shadow)');
    const shadowTypeRow = document.createElement('div'); shadowTypeRow.className = 'mb-3';
    const shadowTypeLabel = document.createElement('label');
    shadowTypeLabel.className = 'block text-sm text-gray-600 mb-1';
    shadowTypeLabel.textContent = 'סוג צל';
    shadowTypeRow.appendChild(shadowTypeLabel);
    shadowTypeRow.appendChild(createSelect(
        [{value: 'none', label: 'ללא'}, 
         {value: 'drop-shadow', label: 'Drop Shadow'}
        ],
        styles.boxShadow.type, 
        (value) => { 
            styles.boxShadow.type = value; 
            updateCallback(); 
        }
    ));
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
        const shadowColorLabel = document.createElement('label');
        shadowColorLabel.className = 'block text-sm text-gray-600 mb-1';
        shadowColorLabel.textContent = 'צבע';
        shadowContent.appendChild(shadowColorLabel);
        shadowContent.appendChild(createColorPicker(
            rgbaToHex(styles.boxShadow.color) || '#000000', 
            (value) => { styles.boxShadow.color = value; updateCallback(); },
            true // Allow opacity
        ));
    }
    panel.appendChild(shadowAccordion);

    // --- Custom Identifiers Section ---
    const { accordionItem: idClassAccordion, contentDiv: idClassContent } = createSettingsGroup('מזהים וקלאסים');
    
    // Custom ID
    const idLabel = document.createElement('label');
    idLabel.className = 'block text-sm text-gray-600 mb-1';
    idLabel.textContent = 'Custom ID';
    idClassContent.appendChild(idLabel);
    idClassContent.appendChild(createTextInput(
        config.customId, // 1. value
        'my-unique-col-id', // 2. placeholder
        null, // 3. unit (none)
        (value) => { // 4. changeCallback
            const newId = value.replace(/[^a-zA-Z0-9-_]/g, '').trim();
            // --- שינוי: קריאה ל-saveResponsiveSetting --- 
            saveResponsiveSetting(elementData, ['config', 'customId'], newId, getCurrentBreakpoint(), updateCallback);
            // -------------------------------------------
        }
    ));
    
    // Custom CSS Classes
    const classLabel = document.createElement('label');
    classLabel.className = 'block text-sm text-gray-600 mb-1 mt-3'; // Added mt-3
    classLabel.textContent = 'Custom CSS Classes';
    idClassContent.appendChild(classLabel);
    idClassContent.appendChild(createTextInput(
        config.customClass, // 1. value
        'my-col-class another-class', // 2. placeholder
        null, // 3. unit (none)
        (value) => { // 4. changeCallback
            const newClasses = value.replace(/[^a-zA-Z0-9-_\s]/g, '').trim();
            // --- שינוי: קריאה ל-saveResponsiveSetting --- 
            saveResponsiveSetting(elementData, ['config', 'customClass'], newClasses, getCurrentBreakpoint(), updateCallback);
            // -------------------------------------------
        }
    ));
    // ---------------------------------------------------------
    
    panel.appendChild(idClassAccordion);

    // --- Visibility Section (Responsive) ---
    const { accordionItem: visibilityAccordion, contentDiv: visibilityContent } = createSettingsGroup('נראות (Visibility)');
    const currentVisibility = elementData.config.visibility || { desktop: true, tablet: true, mobile: true }; 
    if (typeof createVisibilityControls === 'function') {
        visibilityContent.appendChild(
            createVisibilityControls(currentVisibility, (newVisibility, localBreakpointContext) => {
                 saveResponsiveSetting(elementData, ['config', 'visibility', localBreakpointContext], newVisibility[localBreakpointContext], localBreakpointContext, updateCallback);
            })
        );
    } else {
       console.error('createVisibilityControls function not found in common-settings.js');
       visibilityContent.textContent = 'שגיאה בטעינת פקד הנראות.';
    }
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