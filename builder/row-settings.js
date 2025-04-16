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
    rgbaToHex
    // createImageInput // Removed - Not exported from common-settings.js yet
    // createCheckbox // Removed - Not exported from common-settings.js yet
} from './common-settings.js';

console.log('Row Settings module loaded');

// --- פונקציות לייצוא ---

export function populateRowContentTab(panel, rowData, updateCallback) {
    console.log("Populating row content tab for:", rowData.id);
    panel.innerHTML = ''; // ניקוי
    if (!rowData.config) rowData.config = {};
    const config = rowData.config;
    
    // אתחול ברירות מחדל לערכים חדשים
    if (config.contentWidth === undefined) config.contentWidth = 'boxed';
    if (config.columnGap === undefined) config.columnGap = 15;
    if (config.heightMode === undefined) config.heightMode = 'auto';
    if (!config.minHeight) config.minHeight = { value: 100, unit: 'px' };
    if (config.verticalAlign === undefined) config.verticalAlign = 'top';
    if (!config.htmlTag) config.htmlTag = 'div';

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
        (value) => { config.contentWidth = value; updateCallback(); }
    ));
    structureContent.appendChild(widthContainer);
    
    // Column Gap
    const gapContainer = document.createElement('div');
    gapContainer.className = 'mb-4';
    const gapLabel = document.createElement('label');
    gapLabel.className = 'block text-sm text-gray-600 mb-1';
    gapLabel.textContent = 'רווח בין עמודות';
    gapContainer.appendChild(gapLabel);
    gapContainer.appendChild(createNumberInput(null, config.columnGap, (value) => {
        config.columnGap = parseInt(value) || 0;
        updateCallback();
    }, 0, 100, 1));
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
        (value) => { config.heightMode = value; updateCallback(); minHeightInputDiv.style.display = (value === 'minHeight' ? 'block' : 'none'); }
    ));
    const minHeightInputDiv = document.createElement('div');
    minHeightInputDiv.className = 'mt-2 grid grid-cols-2 gap-2';
    minHeightInputDiv.style.display = (config.heightMode === 'minHeight' ? 'block' : 'none');
    minHeightInputDiv.appendChild(createNumberInput(null, config.minHeight.value, (value) => { config.minHeight.value = parseInt(value) || 0; updateCallback(); }, 0));
    minHeightInputDiv.appendChild(createSelect([
        { value: 'px', label: 'px' },
        { value: 'vh', label: 'vh' }
    ], config.minHeight.unit, (value) => { config.minHeight.unit = value; updateCallback(); }));
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
        (value) => { config.verticalAlign = value; updateCallback(); }
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
        (value) => { config.htmlTag = value; updateCallback(); }
    ));
    structureContent.appendChild(tagContainer);

    panel.appendChild(structureAccordion);
}

export function populateRowDesignTab(panel, rowData, updateCallback) {
    console.log("Populating row design tab for:", rowData.id);
    panel.innerHTML = ''; // ניקוי
    if (!rowData.config) rowData.config = {};
    if (!rowData.config.styles) rowData.config.styles = {};
    const styles = rowData.config.styles;
    const config = rowData.config; // For custom ID/Class maybe

    // אתחול ערכים חדשים
    if (!styles.backgroundOverlay) styles.backgroundOverlay = { type: 'none', color: 'rgba(0,0,0,0.5)' };
    if (!styles.backgroundPosition) styles.backgroundPosition = 'center center';
    if (!styles.backgroundAttachment) styles.backgroundAttachment = 'scroll';
    if (!styles.backgroundRepeat) styles.backgroundRepeat = 'no-repeat';
    if (!styles.backgroundSize) styles.backgroundSize = 'cover';

    // --- רקע כללי --- 
    const { accordionItem: bgAccordion, contentDiv: bgContent } = createSettingsGroup('רקע כללי', true);
    
    // Background Color
    const bgColorContainer = document.createElement('div');
    bgColorContainer.className = 'mb-4';
    const bgColorLabel = document.createElement('label');
    bgColorLabel.className = 'block text-sm text-gray-600 mb-1';
    bgColorLabel.textContent = 'צבע רקע';
    bgColorContainer.appendChild(bgColorLabel);
    bgColorContainer.appendChild(createColorInput(styles.backgroundColor || '#ffffff', (value) => {
        styles.backgroundColor = value;
        updateCallback();
    }));
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
        (value) => { styles.backgroundPosition = value; updateCallback(); }
    ));
    bgImageSettingsDiv.appendChild(posContainer);

    // Attachment
    const attachContainer = document.createElement('div');
    const attachLabel = document.createElement('label'); attachLabel.className = 'block text-xs text-gray-500 mb-1'; attachLabel.textContent = 'גלילה';
    attachContainer.appendChild(attachLabel);
    attachContainer.appendChild(createSelect(
        [{ value: 'scroll', label: 'רגיל' }, { value: 'fixed', label: 'קבוע (Parallax)' }],
        styles.backgroundAttachment,
        (value) => { styles.backgroundAttachment = value; updateCallback(); }
    ));
    bgImageSettingsDiv.appendChild(attachContainer);

    // Repeat
    const repeatContainer = document.createElement('div');
    const repeatLabel = document.createElement('label'); repeatLabel.className = 'block text-xs text-gray-500 mb-1'; repeatLabel.textContent = 'חזרה';
    repeatContainer.appendChild(repeatLabel);
    repeatContainer.appendChild(createSelect(
        [{ value: 'no-repeat', label: 'ללא חזרה' }, { value: 'repeat', label: 'חזור על הכל' }, { value: 'repeat-x', label: 'חזור אופקית' }, { value: 'repeat-y', label: 'חזור אנכית' }],
        styles.backgroundRepeat,
        (value) => { styles.backgroundRepeat = value; updateCallback(); }
    ));
    bgImageSettingsDiv.appendChild(repeatContainer);

    // Size
    const sizeContainer = document.createElement('div');
    const sizeLabel = document.createElement('label'); sizeLabel.className = 'block text-xs text-gray-500 mb-1'; sizeLabel.textContent = 'גודל';
    sizeContainer.appendChild(sizeLabel);
    sizeContainer.appendChild(createSelect(
        [{ value: 'cover', label: 'כיסוי' }, { value: 'contain', label: 'הכלה' }, { value: 'auto', label: 'אוטומטי' }],
        styles.backgroundSize,
        (value) => { styles.backgroundSize = value; updateCallback(); }
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

export function populateRowAdvancedTab(panel, rowData, updateCallback) {
    console.log("Populating row advanced tab for:", rowData.id);
    panel.innerHTML = ''; // ניקוי
    if (!rowData.config) rowData.config = {};
    if (!rowData.config.styles) rowData.config.styles = {};
    const styles = rowData.config.styles;
    const config = rowData.config;
    
    // אתחול ערכי ברירת מחדל אם לא קיימים
    if (!styles.padding) styles.padding = { top: '', right: '', bottom: '', left: '', linked: true };
    if (!styles.margin) styles.margin = { top: '0', bottom: '0' }; // שוליים רק למעלה/למטה
    if (!styles.border) styles.border = { width: '0', style: 'none', color: '#000000' };
    if (!styles.boxShadow) styles.boxShadow = { type: 'none', x: '0', y: '0', blur: '0', spread: '0', color: 'rgba(0,0,0,0.1)'};
    if (config.customId === undefined) config.customId = '';
    if (config.customClass === undefined) config.customClass = '';
    
    // --- Padding Section ---
    const { accordionItem: paddingAccordion, contentDiv: paddingContent } = createSettingsGroup('ריפוד (Padding)');
    const paddingLabels = [
        { key: 'top', label: 'עליון', placeholder: '0' },
        { key: 'right', label: 'ימין', placeholder: '0' },
        { key: 'bottom', label: 'תחתון', placeholder: '0' },
        { key: 'left', label: 'שמאל', placeholder: '0' },
    ];
    paddingContent.appendChild(createLinkedInputs(paddingLabels, styles.padding, 'px', true, updateCallback));
    panel.appendChild(paddingAccordion);

    // --- Margin Section ---
    const { accordionItem: marginAccordion, contentDiv: marginContent } = createSettingsGroup('שוליים (Margin)');
    const marginTopContainer = document.createElement('div');
    marginTopContainer.className = 'mb-3';
    marginTopContainer.appendChild(createNumberInput('Margin Top (px)', parseInt(styles.margin.top) || 0, (value) => { styles.margin.top = parseInt(value) || 0; updateCallback(); }, 0));
    marginContent.appendChild(marginTopContainer);
    
    const marginBottomContainer = document.createElement('div');
    marginBottomContainer.className = 'mb-3';
    marginBottomContainer.appendChild(createNumberInput('Margin Bottom (px)', parseInt(styles.margin.bottom) || 0, (value) => { styles.margin.bottom = parseInt(value) || 0; updateCallback(); }, 0));
    marginContent.appendChild(marginBottomContainer);
    panel.appendChild(marginAccordion);

    // --- Border Section ---
    const { accordionItem: borderAccordion, contentDiv: borderContent } = createSettingsGroup('מסגרת (Border)');
    borderContent.appendChild(createColorInput(styles.border.color, (value) => { styles.border.color = value; updateCallback(); }));
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
        [{value: 'solid', label:'רציף'}, {value: 'dashed', label:'מקווקו'}, {value: 'dotted', label:'נקודות'}, {value: 'none', label: 'ללא'}],
        styles.border.style, (value) => { styles.border.style = value; updateCallback(); }
    ));
    borderControlsRow.appendChild(widthWithLabel);
    borderControlsRow.appendChild(styleWithLabel);
    borderContent.appendChild(borderControlsRow);
    panel.appendChild(borderAccordion);

    // --- Shadow Section ---
    const { accordionItem: shadowAccordion, contentDiv: shadowContent } = createSettingsGroup('צל (Shadow)');
    const shadowTypeRow = document.createElement('div'); shadowTypeRow.className = 'mb-3';
    const shadowTypeLabel = document.createElement('span'); shadowTypeLabel.className = 'text-xs text-gray-500 mb-1 block'; shadowTypeLabel.textContent = 'סוג צל';
    shadowTypeRow.appendChild(shadowTypeLabel);
    shadowTypeRow.appendChild(createSelect(
        [{value: 'none', label: 'ללא'}, {value: 'drop-shadow', label: 'Drop Shadow'}],
        styles.boxShadow.type, (value) => { styles.boxShadow.type = value; updateCallback(); }
    ));
    shadowContent.appendChild(shadowTypeRow);
    const shadowXYRow = document.createElement('div'); shadowXYRow.className = 'grid grid-cols-2 gap-2 mb-3';
    shadowXYRow.appendChild(createNumberInput('X', parseInt(styles.boxShadow.x) || 0, (value) => { styles.boxShadow.x = parseInt(value) || 0; updateCallback(); }));
    shadowXYRow.appendChild(createNumberInput('Y', parseInt(styles.boxShadow.y) || 0, (value) => { styles.boxShadow.y = parseInt(value) || 0; updateCallback(); }));
    shadowContent.appendChild(shadowXYRow);
    const shadowBlurSpreadRow = document.createElement('div'); shadowBlurSpreadRow.className = 'grid grid-cols-2 gap-2 mb-3';
    shadowBlurSpreadRow.appendChild(createNumberInput('Blur', parseInt(styles.boxShadow.blur) || 0, (value) => { styles.boxShadow.blur = parseInt(value) || 0; updateCallback(); }, 0));
    shadowBlurSpreadRow.appendChild(createNumberInput('Spread', parseInt(styles.boxShadow.spread) || 0, (value) => { styles.boxShadow.spread = parseInt(value) || 0; updateCallback(); }));
    shadowContent.appendChild(shadowBlurSpreadRow);
    shadowContent.appendChild(createColorInput(
        styles.boxShadow.color, (value) => { styles.boxShadow.color = value; updateCallback(); }, true
    ));
    panel.appendChild(shadowAccordion);

    // --- Custom Identifiers Section ---
    const { accordionItem: idClassAccordion, contentDiv: idClassContent } = createSettingsGroup('מזהים וקלאסים');
    const idContainer = document.createElement('div'); idContainer.className = 'mb-3';
    const idLabel = document.createElement('label'); idLabel.className = 'block text-sm text-gray-600 mb-1'; idLabel.htmlFor = `row-custom-id-${rowData.id}`; idLabel.textContent = 'Custom ID';
    const idInput = document.createElement('input'); idInput.type = 'text'; idInput.id = `row-custom-id-${rowData.id}`; idInput.className = 'w-full h-9 px-3 text-sm rounded-lg focus:outline-none bg-gray-50 text-gray-700'; idInput.placeholder = 'my-unique-row-id'; idInput.value = config.customId;
    idInput.addEventListener('input', (e) => {
        e.target.value = e.target.value.replace(/[^a-zA-Z0-9-_]/g, '');
        config.customId = e.target.value;
        // לא צריך callback כאן כי ID לא משפיע על הרינדור ישירות
    });
    idContainer.appendChild(idLabel); idContainer.appendChild(idInput); idClassContent.appendChild(idContainer);
    
    const classContainer = document.createElement('div'); classContainer.className = 'mb-3';
    const classLabel = document.createElement('label'); classLabel.className = 'block text-sm text-gray-600 mb-1'; classLabel.htmlFor = `row-custom-class-${rowData.id}`; classLabel.textContent = 'Custom CSS Classes';
    const classInput = document.createElement('input'); classInput.type = 'text'; classInput.id = `row-custom-class-${rowData.id}`; classInput.className = 'w-full h-9 px-3 text-sm rounded-lg focus:outline-none bg-gray-50 text-gray-700'; classInput.placeholder = 'my-row-class another-class'; classInput.value = config.customClass;
    classInput.addEventListener('input', (e) => {
        e.target.value = e.target.value.replace(/[^a-zA-Z0-9-_\s]/g, '');
        config.customClass = e.target.value.trim();
        updateCallback(); // צריך רינדור מחדש להחלת הקלאסים
    });
    classContainer.appendChild(classLabel); classContainer.appendChild(classInput); idClassContent.appendChild(classContainer);
    panel.appendChild(idClassAccordion);
    
    // TODO: Implement Visibility settings
} 