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
    createButtonGroup 
} from './common-settings.js';

// --- הוספה: ייבוא פונקציית שמירה רספונסיבית ---
import { saveResponsiveSetting, getSettingOverrideStatus, getEffectiveConfig, getNestedValue } from './render-responsive.js';
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
        // --- שינוי: שימוש בגבולות דינמיים גם ב-finalization ---
        finalValue = Math.max(minAllowedWidth, Math.min(maxAllowedWidth, finalValue));
        // -----------------------------------------------------
        const finalValueStr = finalValue.toFixed(2);

        // Ensure both inputs and config reflect the final clamped value
        config.widthPercent = finalValueStr;
        sliderInput.value = finalValue;
        numberInput.value = finalValueStr; 

        console.log(`Finalizing width change for ${elementData.id} to ${finalValueStr}% (Update logic TBD)`);
        // קריאה ל-updateCallback - הוא יצטרך להיות חכם יותר בהמשך
        // כרגע, זה יגרום לשמירה של ה-state המלא ורינדור מחדש
        updateCallback(); // קריאה פשוטה ל-updateCallback כרגע
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
        (value) => { saveResponsiveSetting(elementData, ['verticalAlign'], value, updateCallback); }
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
        (value) => { saveResponsiveSetting(elementData, ['horizontalAlign'], value, updateCallback); }
    ));
    layoutContent.appendChild(hAlignContainer);

    // Widget Spacing
    const spacingContainer = document.createElement('div');
    spacingContainer.className = 'mb-4';
    spacingContainer.appendChild(createNumberInput('מרווח בין ווידג\'טים (px)', config.widgetSpacing, (value) => {
        saveResponsiveSetting(elementData, ['widgetSpacing'], parseInt(value) || 0, updateCallback);
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
        (value) => { saveResponsiveSetting(elementData, ['htmlTag'], value, updateCallback); }
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
        (value) => { saveResponsiveSetting(elementData, ['styles', 'backgroundColor'], value, updateCallback); }
    ));
    panel.appendChild(bgAccordion);

    // --- Padding Section --- 
    const { accordionItem: paddingAccordion, contentDiv: paddingContent } = createSettingsGroup('ריפוד (Padding)');
    const paddingLabels = [
        { key: 'top', label: 'עליון', placeholder: '10' },
        { key: 'right', label: 'ימין', placeholder: '10' },
        { key: 'bottom', label: 'תחתון', placeholder: '10' },
        { key: 'left', label: 'שמאל', placeholder: '10' }
    ];
    const paddingSettingPath = ['styles', 'padding']; // נתיב ההגדרה
    const effectivePadding = config.styles?.padding || { top: '10', right: '10', bottom: '10', left: '10', unit: 'px' };
    const paddingOverrideStatus = getSettingOverrideStatus(elementData, paddingSettingPath);
    paddingContent.appendChild(createLinkedInputs(
        paddingLabels, 
        effectivePadding, 
        effectivePadding.unit || 'px', 
        true, 
        (newValue, localBreakpointContext) => { 
            saveResponsiveSetting(elementData, paddingSettingPath, newValue, localBreakpointContext, updateCallback);
        },
        true, // isResponsive
        paddingOverrideStatus,
        // --- הוספה: העברת הקולבק לטעינה והנתיב ---
        (breakpoint) => fetchSettingValue(breakpoint, paddingSettingPath),
        paddingSettingPath
        // ------------------------------------------
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
        (value) => { saveResponsiveSetting(elementData, ['styles', 'border', 'color'], value, updateCallback); }
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
        (value) => { saveResponsiveSetting(elementData, ['styles', 'border', 'width'], `${parseInt(value) || 0}px`, updateCallback); }, 0, 50, 1));

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
        (value) => { saveResponsiveSetting(elementData, ['styles', 'border', 'style'], value, updateCallback); }
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
        (value) => { saveResponsiveSetting(elementData, ['styles', 'borderRadius', 'value'], `${parseInt(value) || 0}`, updateCallback); }, 0));
    radiusContainer.appendChild(createSelect(
        [{value: 'px', label: 'px'}, {value: '%', label: '%'}],
        config.styles?.borderRadius?.unit || 'px',
        (value) => { saveResponsiveSetting(elementData, ['styles', 'borderRadius', 'unit'], value, updateCallback); }
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
    const marginLabels = [
        { key: 'top', label: 'עליון', placeholder: '0' },
        { key: 'right', label: 'ימין', placeholder: '0' },
        { key: 'bottom', label: 'תחתון', placeholder: '0' },
        { key: 'left', label: 'שמאל', placeholder: '0' }
    ];
    const marginSettingPath = ['styles', 'margin']; // נתיב ההגדרה
    const effectiveMargin = config.styles?.margin || { top: '0', right: '0', bottom: '0', left: '0', unit: 'px' };
    const marginOverrideStatus = getSettingOverrideStatus(elementData, marginSettingPath);
    marginContent.appendChild(createLinkedInputs(
        marginLabels, 
        effectiveMargin, 
        effectiveMargin.unit || 'px', 
        true, 
        (newValue, localBreakpointContext) => { 
            saveResponsiveSetting(elementData, marginSettingPath, newValue, localBreakpointContext, updateCallback);
        },
        true, // isResponsive
        marginOverrideStatus,
        // --- הוספה: העברת הקולבק לטעינה והנתיב ---
        (breakpoint) => fetchSettingValue(breakpoint, marginSettingPath),
        marginSettingPath
        // ------------------------------------------
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
    const idContainer = document.createElement('div'); idContainer.className = 'mb-3';
    idContainer.appendChild(createTextInput('Custom ID', config.customId, (value) => {
        elementData.config.customId = value.replace(/[^a-zA-Z0-9-_]/g, '').trim();
        // No callback needed for ID usually
    }, 'my-unique-col-id'));
    idClassContent.appendChild(idContainer);
    
    const classContainer = document.createElement('div'); classContainer.className = 'mb-3';
    classContainer.appendChild(createTextInput('Custom CSS Classes', config.customClass, (value) => {
        elementData.config.customClass = value.replace(/[^a-zA-Z0-9-_\s]/g, '').trim();
        updateCallback(); // Rerender needed for classes
    }, 'my-col-class another-class'));
    idClassContent.appendChild(classContainer);
    panel.appendChild(idClassAccordion);

    // --- Visibility Section (Responsive) ---
    const { accordionItem: visibilityAccordion, contentDiv: visibilityContent } = createSettingsGroup('נראות (Visibility)');
    const currentVisibility = elementData.config.visibility || { desktop: true, tablet: true, mobile: true }; 
    if (typeof createVisibilityControls === 'function') {
        visibilityContent.appendChild(
            createVisibilityControls(currentVisibility, (newVisibility) => {
                // TODO: עדכון שמירת Visibility תצטרך גם היא לקבל הקשר מקומי 
                const currentBreakpoint = window.currentBreakpoint || 'desktop'; // << צריך לשנות
                saveResponsiveSetting(elementData, ['visibility', currentBreakpoint], newVisibility[currentBreakpoint], updateCallback);
            })
        );
    } else {
       // ... (error handling)
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