// builder/row-settings.js
// הגדרות ספציפיות לשורות

// ייבוא פונקציות עזר מ-common-settings.js
import { 
    createSettingsGroup, 
    createLinkedInputs, 
    createColorInput, 
    createSlider, 
    createSelect, 
    createNumberInput, 
    createTextInput,
    createButtonGroup,
    rgbaToHex,
    createVisibilityControls
    // createImageInput // Removed - Not exported from common-settings.js yet
    // createCheckbox // Removed - Not exported from common-settings.js yet
} from './common-settings.js';

// --- הוספה: ייבוא פונקציית שמירה רספונסיבית ---
import { saveResponsiveSetting, getSettingOverrideStatus, getEffectiveConfig, getNestedValue, getCurrentBreakpoint } from './render-responsive.js';
// ---------------------------------------------

console.log('Row Settings module loaded');

// --- פונקציות לייצוא ---

export function populateRowContentTab(panel, rowData, effectiveConfig, updateCallback) {
    console.log("Populating row content tab for:", rowData.id);
    panel.innerHTML = ''; // ניקוי
    if (!rowData.config) rowData.config = {};
    const config = effectiveConfig;
    
    // אתחול ברירות מחדל לערכים חדשים
    if (config.contentWidth === undefined) config.contentWidth = 'boxed';
    if (config.columnGap === undefined) config.columnGap = 15;
    if (config.heightMode === undefined) config.heightMode = 'auto';
    if (!config.minHeight) config.minHeight = { value: 100, unit: 'px' };
    if (config.verticalAlign === undefined) config.verticalAlign = 'top';
    if (!config.htmlTag) config.htmlTag = 'div';

    // Initialize responsiveOverrides if it doesn't exist on the original data
    if (!rowData.config.responsiveOverrides) {
        rowData.config.responsiveOverrides = {};
    }

    // --- Structure Group ---
    const { accordionItem: structureAccordion, contentDiv: structureContent } = createSettingsGroup('מבנה', true);

    // Content Width
    const widthContainer = document.createElement('div');
    widthContainer.className = 'mb-4';
    const widthLabel = document.createElement('label');
    widthLabel.className = 'block text-sm text-gray-600 mb-1';
    widthLabel.textContent = 'רוחב תוכן';
    widthContainer.appendChild(widthLabel);
    widthContainer.appendChild(createButtonGroup(
        [
            { value: 'boxed', title: 'בתוך קופסה', label: 'קונטיינר' }, 
            { value: 'fullWidth', title: 'רוחב מלא', label: 'רוחב מלא' }
        ],
        config.contentWidth,
        (value) => { saveResponsiveSetting(rowData, ['contentWidth'], value, updateCallback); }
    ));
    structureContent.appendChild(widthContainer);
    
    // Column Gap
    const gapContainer = document.createElement('div');
    gapContainer.className = 'mb-4';
    const gapLabel = document.createElement('label');
    gapLabel.className = 'block text-sm text-gray-600 mb-1';
    gapLabel.textContent = 'רווח בין עמודות';
    gapContainer.appendChild(gapLabel);
    gapContainer.appendChild(createNumberInput(null, config.columnGap, (value) => { saveResponsiveSetting(rowData, ['columnGap'], parseInt(value) || 0, updateCallback); }, 0, 100, 1));
    structureContent.appendChild(gapContainer);
    
    // Height
    const heightContainer = document.createElement('div');
    heightContainer.className = 'mb-4';
    const heightLabel = document.createElement('label');
    heightLabel.className = 'block text-sm text-gray-600 mb-1';
    heightLabel.textContent = 'גובה';
    heightContainer.appendChild(heightLabel);
    heightContainer.appendChild(createSelect(
        [
            { value: 'auto', label: 'אוטומטי' },
            // { value: 'fullScreen', label: 'מסך מלא' }, // Coming soon?
            { value: 'minHeight', label: 'גובה מינימלי' }
        ],
        config.heightMode,
        (value) => { 
            saveResponsiveSetting(rowData, ['heightMode'], value, updateCallback);
            minHeightInputDiv.style.display = (value === 'minHeight' ? 'block' : 'none');
        }
    ));
    const minHeightInputDiv = document.createElement('div');
    minHeightInputDiv.className = 'mt-2 grid grid-cols-2 gap-2';
    minHeightInputDiv.style.display = (config.heightMode === 'minHeight' ? 'block' : 'none');
    minHeightInputDiv.appendChild(createNumberInput(null, config.minHeight.value, (value) => { saveResponsiveSetting(rowData, ['minHeight', 'value'], parseInt(value) || 0, updateCallback); }, 0));
    minHeightInputDiv.appendChild(createSelect([
        { value: 'px', label: 'px' },
        { value: 'vh', label: 'vh' }
    ], config.minHeight.unit, (value) => { saveResponsiveSetting(rowData, ['minHeight', 'unit'], value, updateCallback); }));
    heightContainer.appendChild(minHeightInputDiv);
    structureContent.appendChild(heightContainer);

    // Vertical Alignment
    const vAlignContainer = document.createElement('div');
    vAlignContainer.className = 'mb-4';
    const vAlignLabel = document.createElement('label');
    vAlignLabel.className = 'block text-sm text-gray-600 mb-1';
    vAlignLabel.textContent = 'יישור אנכי של עמודות';
    vAlignContainer.appendChild(vAlignLabel);
    vAlignContainer.appendChild(createButtonGroup(
        [
            { value: 'top', title: 'למעלה', icon: '<i class="ri-align-top"></i>' }, 
            { value: 'middle', title: 'אמצע', icon: '<i class="ri-align-vertically"></i>' }, 
            { value: 'bottom', title: 'למטה', icon: '<i class="ri-align-bottom"></i>' }
        ],
        config.verticalAlign,
        (value) => { saveResponsiveSetting(rowData, ['verticalAlign'], value, updateCallback); }
    ));
    structureContent.appendChild(vAlignContainer);

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
            { value: 'section', label: 'section' },
            { value: 'header', label: 'header' },
            { value: 'footer', label: 'footer' },
            { value: 'main', label: 'main' },
            { value: 'article', label: 'article' },
            { value: 'aside', label: 'aside' },
            { value: 'nav', label: 'nav' }
        ],
        config.htmlTag,
        (value) => { saveResponsiveSetting(rowData, ['htmlTag'], value, updateCallback); }
    ));
    structureContent.appendChild(tagContainer);

    panel.appendChild(structureAccordion);
}

export function populateRowDesignTab(panel, rowData, effectiveConfig, updateCallback) {
    console.log("Populating row design tab for:", rowData.id);
    panel.innerHTML = ''; // ניקוי
    if (!rowData.config) rowData.config = {};
    if (!rowData.config.styles) rowData.config.styles = {};
    const styles = rowData.config.styles;
    const config = effectiveConfig;

    // אתחול ערכים חדשים
    if (!styles.backgroundOverlay) styles.backgroundOverlay = { type: 'none', color: 'rgba(0,0,0,0.5)' };
    if (!styles.backgroundPosition) styles.backgroundPosition = 'center center';
    if (!styles.backgroundAttachment) styles.backgroundAttachment = 'scroll';
    if (!styles.backgroundRepeat) styles.backgroundRepeat = 'no-repeat';
    if (!styles.backgroundSize) styles.backgroundSize = 'cover';

    // Initialize responsiveOverrides if it doesn't exist on the original data
    if (!rowData.config.responsiveOverrides) {
        rowData.config.responsiveOverrides = {};
    }

    // --- רקע כללי --- 
    const { accordionItem: bgAccordion, contentDiv: bgContent } = createSettingsGroup('רקע כללי', true);
    
    // Background Color
    const bgColorContainer = document.createElement('div');
    bgColorContainer.className = 'mb-4';
    const bgColorLabel = document.createElement('label');
    bgColorLabel.className = 'block text-sm text-gray-600 mb-1';
    bgColorLabel.textContent = 'צבע רקע';
    bgColorContainer.appendChild(bgColorLabel);
    bgColorContainer.appendChild(createColorInput(styles.backgroundColor || '#ffffff', (value) => { saveResponsiveSetting(rowData, ['styles', 'backgroundColor'], value, updateCallback); }));
    bgContent.appendChild(bgColorContainer);

    // --- Background Image Upload ---
    const bgImageContainer = document.createElement('div');
    bgImageContainer.className = 'mb-4';
    const bgImageLabel = document.createElement('label');
    bgImageLabel.className = 'block text-sm text-gray-600 mb-1';
    bgImageLabel.textContent = 'תמונת רקע';
    bgImageContainer.appendChild(bgImageLabel);

    // Temporarily commented out until createImageInput is implemented
    /*
    const { input: imageInput, controls: imageControls, removeButton: removeImageButton, settingsDiv: bgImageSettingsDiv } = createImageInput(
        'תמונת רקע',
        styles.backgroundImage,
        (file) => { // On file selected
            const reader = new FileReader();
            reader.onload = (e) => {
                styles.backgroundImage = e.target.result;
                removeImageButton.style.display = 'inline-block';
                bgImageSettingsDiv.style.display = 'grid'; // Show settings
                updateCallback();
            }
            reader.readAsDataURL(file);
        },
        () => { // On image removed
            styles.backgroundImage = '';
            removeImageButton.style.display = 'none';
            bgImageSettingsDiv.style.display = 'none'; // Hide settings
            updateCallback();
        }
    );
    bgImageContainer.appendChild(imageInput);
    bgImageContainer.appendChild(imageControls);
    bgImageContainer.appendChild(bgImageSettingsDiv);
    bgContent.appendChild(bgImageContainer);

    // --- Image Settings (Position, Repeat, Size, Attachment) ---
    bgImageSettingsDiv.className = 'mt-3 grid grid-cols-2 gap-3 border-t border-gray-200 pt-3';
    bgImageSettingsDiv.style.display = styles.backgroundImage ? 'grid' : 'none'; // Show only if image exists

    // Position
    const posContainer = document.createElement('div');
    const posLabel = document.createElement('label'); posLabel.className = 'block text-xs text-gray-500 mb-1'; posLabel.textContent = 'מיקום';
    posContainer.appendChild(posLabel);
    posContainer.appendChild(createSelect(
        [
            { value: 'left top', label: 'שמאל למעלה' }, { value: 'center top', label: 'מרכז למעלה' }, { value: 'right top', label: 'ימין למעלה' },
            { value: 'left center', label: 'שמאל מרכז' }, { value: 'center center', label: 'מרכז מרכז' }, { value: 'right center', label: 'ימין מרכז' },
            { value: 'left bottom', label: 'שמאל למטה' }, { value: 'center bottom', label: 'מרכז למטה' }, { value: 'right bottom', label: 'ימין למטה' }
        ],
        styles.backgroundPosition,
        (value) => { saveResponsiveSetting(rowData, ['styles', 'backgroundPosition'], value, updateCallback); }
    ));
    bgImageSettingsDiv.appendChild(posContainer);

    // Attachment
    const attachContainer = document.createElement('div');
    const attachLabel = document.createElement('label'); attachLabel.className = 'block text-xs text-gray-500 mb-1'; attachLabel.textContent = 'גלילה';
    attachContainer.appendChild(attachLabel);
    attachContainer.appendChild(createSelect(
        [{ value: 'scroll', label: 'רגיל' }, { value: 'fixed', label: 'קבוע (Parallax)' }],
        styles.backgroundAttachment,
        (value) => { saveResponsiveSetting(rowData, ['styles', 'backgroundAttachment'], value, updateCallback); }
    ));
    bgImageSettingsDiv.appendChild(attachContainer);

    // Repeat
    const repeatContainer = document.createElement('div');
    const repeatLabel = document.createElement('label'); repeatLabel.className = 'block text-xs text-gray-500 mb-1'; repeatLabel.textContent = 'חזרה';
    repeatContainer.appendChild(repeatLabel);
    repeatContainer.appendChild(createSelect(
        [{ value: 'no-repeat', label: 'ללא חזרה' }, { value: 'repeat', label: 'חזור על הכל' }, { value: 'repeat-x', label: 'חזור אופקית' }, { value: 'repeat-y', label: 'חזור אנכית' }],
        styles.backgroundRepeat,
        (value) => { saveResponsiveSetting(rowData, ['styles', 'backgroundRepeat'], value, updateCallback); }
    ));
    bgImageSettingsDiv.appendChild(repeatContainer);

    // Size
    const sizeContainer = document.createElement('div');
    const sizeLabel = document.createElement('label'); sizeLabel.className = 'block text-xs text-gray-500 mb-1'; sizeLabel.textContent = 'גודל';
    sizeContainer.appendChild(sizeLabel);
    sizeContainer.appendChild(createSelect(
        [{ value: 'cover', label: 'כיסוי' }, { value: 'contain', label: 'הכלה' }, { value: 'auto', label: 'אוטומטי' }],
        styles.backgroundSize,
        (value) => { saveResponsiveSetting(rowData, ['styles', 'backgroundSize'], value, updateCallback); }
    ));
    bgImageSettingsDiv.appendChild(sizeContainer);
    
    bgContent.appendChild(bgImageSettingsDiv);
    */
    // Add a placeholder message instead
    const imagePlaceholder = document.createElement('p');
    imagePlaceholder.textContent = '(העלאת תמונת רקע תתווסף בקרוב)';
    imagePlaceholder.className = 'text-xs text-gray-400 italic mt-1';
    bgImageContainer.appendChild(imagePlaceholder);
    bgContent.appendChild(bgImageContainer);

    panel.appendChild(bgAccordion);

    // --- Background Overlay ---
    const { accordionItem: overlayAccordion, contentDiv: overlayContent } = createSettingsGroup('שכבת רקע');
    
    // Overlay Type (placeholder for gradient/etc later)
    // overlayContent.appendChild(createSelect(...))

    // Overlay Color
    const overlayColorContainer = document.createElement('div');
    overlayColorContainer.className = 'mb-3';
    const overlayColorLabel = document.createElement('label');
    overlayColorLabel.className = 'block text-sm text-gray-600 mb-1';
    overlayColorLabel.textContent = 'צבע שכבה';
    overlayColorContainer.appendChild(overlayColorLabel);
    overlayColorContainer.appendChild(createColorInput(styles.backgroundOverlay.color, (value) => {
        styles.backgroundOverlay.color = value;
        styles.backgroundOverlay.type = 'classic'; // Set type to classic when color changes
        updateCallback();
    }, true)); // Allow opacity via RGBA in color picker for now
    overlayContent.appendChild(overlayColorContainer);

    // Reset button
    const resetButton = document.createElement('button');
    resetButton.textContent = 'אפס שכבה';
    resetButton.className = 'text-xs text-blue-500 hover:underline';
    resetButton.onclick = (e) => {
        e.preventDefault();
        styles.backgroundOverlay.type = 'none';
        styles.backgroundOverlay.color = 'rgba(0,0,0,0.5)'; // Reset color
        updateCallback();
        // Need to re-populate this specific tab section or the whole tab
        populateRowDesignTab(panel, rowData, updateCallback);
    };
    overlayContent.appendChild(resetButton);

    panel.appendChild(overlayAccordion);

    // TODO: Add Shape Divider Section
}

export function populateRowAdvancedTab(panel, rowData, effectiveConfig, updateCallback) {
    console.log('Populating row advanced tab for:', rowData.id);
    panel.innerHTML = ''; // Clear previous

    if (!rowData || !rowData.config) {
        settingsPanel.innerHTML = '<p class="text-gray-500 text-sm p-4">בחר אלמנט לעריכה.</p>';
        return; // Exit if no valid element data
    }

    // --- שינוי: קבלת styles מהקונפיג המקורי של השורה ---
    const styles = rowData.config.styles || {};
    // --- שינוי: שימוש ב-effectiveConfig עבור ערכים שאינם נערכים כאן ישירות ---
    const config = effectiveConfig;
    // ----------------------------------------------------

    // Ensure styles structure exists on original data
    if (!rowData.config.styles) rowData.config.styles = {};
    // --- שינוי: אתחול padding/border/boxShadow על styles (האובייקט המקורי) ---
    if (!styles.padding) {
        styles.padding = { top: '0', right: '0', bottom: '0', left: '0', unit: 'px' };
    } else if (styles.padding.unit === undefined) {
        styles.padding.unit = 'px'; // ודא שיחידה קיימת
    }
    // --- שינוי: ברירת מחדל ל-margin ---
    if (!styles.margin) {
        styles.margin = { top: '0', right: 'auto', bottom: '0', left: 'auto', unit: 'px' }; // Usually only top/bottom margin for rows
    } else if (styles.margin.unit === undefined) {
        styles.margin.unit = 'px'; 
    }
    // -------------------------------
    if (!styles.border) styles.border = { width: '0', style: 'solid', color: '#000000' };
    if (!styles.boxShadow) styles.boxShadow = { type: 'none', x: '0', y: '0', blur: '0', spread: '0', color: 'rgba(0,0,0,0.1)'};
    // -----------------------------------------------------------------------
    // --- שינוי: אתחול visibility על הקונפיג המקורי ---
    if (!rowData.config.visibility) rowData.config.visibility = { desktop: true, tablet: true, mobile: true };
    // -----------------------------------------------

    // --- פונקציית עזר לטעינת ערך אפקטיבי לברייקפוינט ספציפי --- 
    const fetchSettingValue = (breakpoint, path) => {
        // קבל את הקונפיג האפקטיבי המלא לאותו ברייקפוינט
        const specificEffectiveConfig = getEffectiveConfig(rowData, breakpoint);
        // קרא את הערך הספציפי מהקונפיג האפקטיבי
        return getNestedValue(specificEffectiveConfig, path);
    };
    // -------------------------------------------------------------

    // --- Margin Section (Responsive) ---
    const { accordionItem: marginAccordion, contentDiv: marginContent } = createSettingsGroup('שוליים (Margin)');
    marginContent.appendChild(createLinkedInputs(
        'שוליים',
        rowData,
        ['styles', 'margin'],
        ['px', '%', 'em', 'rem', 'auto'],
        'px',
        updateCallback
    ));
    panel.appendChild(marginAccordion);

    // --- Padding Section (Accordion - Responsive) ---
    const { accordionItem: paddingAccordion, contentDiv: paddingContent } = createSettingsGroup('ריפוד (Padding)', true);
    paddingContent.appendChild(createLinkedInputs(
        'ריפוד',
        rowData,
        ['styles', 'padding'],
        ['px', '%', 'em', 'rem', 'vh', 'vw'],
        'px',
        updateCallback
    ));
    panel.appendChild(paddingAccordion);
    
    // --- Visibility Section (Responsive) --- 
    const { accordionItem: visibilityAccordion, contentDiv: visibilityContent } = createSettingsGroup('נראות (Visibility)');
    const currentVisibility = rowData.config.visibility || { desktop: true, tablet: true, mobile: true };
    if (typeof createVisibilityControls === 'function') {
        visibilityContent.appendChild(
            createVisibilityControls(currentVisibility, (newVisibility) => {
                const currentBreakpoint = window.currentBreakpoint || 'desktop';
                saveResponsiveSetting(rowData, ['visibility', currentBreakpoint], newVisibility[currentBreakpoint], updateCallback);
            })
        );
    } else {
        visibilityContent.textContent = 'Error: createVisibilityControls not found';
        console.error('createVisibilityControls not found in common-settings.js');
    }
    panel.appendChild(visibilityAccordion);

    // --- Custom Identifiers Section (Accordion - Not Responsive) ---
    const { accordionItem: idClassAccordion, contentDiv: idClassContent } = createSettingsGroup('מזהים וקלאסים');
    const customId = rowData.config.customId || '';
    const customClass = rowData.config.customClass || '';

    // Custom ID Input
    const idContainer = document.createElement('div');
    idContainer.className = 'mb-3';
    const idLabel = document.createElement('label');
    idLabel.className = 'block text-sm text-gray-600 mb-1';
    idLabel.htmlFor = `custom-id-${rowData.id}`;
    idLabel.textContent = 'Custom ID';
    const idInput = document.createElement('input');
    idInput.type = 'text';
    idInput.id = `custom-id-${rowData.id}`;
    idInput.className = 'w-full h-9 px-3 text-sm rounded-lg focus:outline-none bg-gray-50 text-gray-700';
    idInput.placeholder = 'my-unique-row-id';
    idInput.value = customId;
    idInput.addEventListener('input', (e) => {
        e.target.value = e.target.value.replace(/[^a-zA-Z0-9-_]/g, '');
        rowData.config.customId = e.target.value;
        updateCallback(false);
    });
    idContainer.appendChild(idLabel);
    idContainer.appendChild(idInput);
    idClassContent.appendChild(idContainer);

    // Custom Class Input
    const classContainer = document.createElement('div');
    classContainer.className = 'mb-3';
    const classLabel = document.createElement('label');
    classLabel.className = 'block text-sm text-gray-600 mb-1';
    classLabel.htmlFor = `custom-class-${rowData.id}`;
    classLabel.textContent = 'Custom CSS Classes';
    const classInput = document.createElement('input');
    classInput.type = 'text';
    classInput.id = `custom-class-${rowData.id}`;
    classInput.className = 'w-full h-9 px-3 text-sm rounded-lg focus:outline-none bg-gray-50 text-gray-700';
    classInput.placeholder = 'my-row-class another-class';
    classInput.value = customClass;
    classInput.addEventListener('input', (e) => {
        e.target.value = e.target.value.replace(/[^a-zA-Z0-9-_\s]/g, '');
        rowData.config.customClass = e.target.value.trim();
        updateCallback(true);
    });
    classContainer.appendChild(classLabel);
    classContainer.appendChild(classInput);
    idClassContent.appendChild(classContainer);

    panel.appendChild(idClassAccordion);
} 