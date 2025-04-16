// Logic for common settings (Design & Advanced tabs)

console.log('Common Settings module loaded');

// --- Helper Functions ---

// Updated helper to create an accordion settings group with more modern styling
export function createSettingsGroup(title, initiallyOpen = false) {
    const accordionItem = document.createElement('div');
    accordionItem.className = 'mb-4 bg-white rounded-xl shadow-sm'; // Modern rounded container with subtle shadow

    const headerButton = document.createElement('button');
    headerButton.type = 'button';
    headerButton.className = 'flex items-center justify-between w-full px-4 py-3 focus:outline-none text-sm font-medium text-gray-800';

    const titleSpan = document.createElement('span');
    titleSpan.textContent = title;

    const iconSpan = document.createElement('span');
    iconSpan.className = 'transition-transform duration-200 ease-in-out text-gray-500';
    iconSpan.innerHTML = `<i class="ri-arrow-down-s-line"></i>`; // Default to closed arrow

    headerButton.appendChild(titleSpan);
    headerButton.appendChild(iconSpan);

    const contentDiv = document.createElement('div');
    contentDiv.className = 'px-4 py-3'; // Clean padding for content
    if (!initiallyOpen) {
        contentDiv.classList.add('hidden'); // Start closed unless specified
        iconSpan.style.transform = 'rotate(-90deg)';
    }

    headerButton.addEventListener('click', () => {
        const isHidden = contentDiv.classList.toggle('hidden');
        iconSpan.style.transform = isHidden ? 'rotate(-90deg)' : 'rotate(0deg)';
    });

    accordionItem.appendChild(headerButton);
    accordionItem.appendChild(contentDiv);

    // Return the CONTENT div so controls can be added to it
    return { accordionItem, contentDiv };
}

// Helper function to create select dropdown with modern styling
export function createSelect(options, selectedValue, changeCallback) {
    const select = document.createElement('select');
    select.className = 'w-full h-9 px-3 text-sm rounded-lg focus:outline-none bg-gray-50 text-gray-700';
    options.forEach(opt => {
        const option = document.createElement('option');
        option.value = opt.value;
        option.textContent = opt.label;
        if (opt.value === selectedValue) {
            option.selected = true;
        }
        select.appendChild(option);
    });
    select.addEventListener('change', (e) => changeCallback(e.target.value));
    return select;
}

// Helper to create number input with label, modernized
export function createNumberInput(label, value, changeCallback, min = null, max = null, step = null) {
    const container = document.createElement('div');
    if (label) {
        const labelEl = document.createElement('label');
        labelEl.className = 'block text-xs text-gray-500 mb-1';
        labelEl.textContent = label;
        container.appendChild(labelEl);
    }
    const input = document.createElement('input');
    input.type = 'number';
    input.className = 'w-full h-9 px-2 py-1 text-sm text-center rounded-lg focus:outline-none bg-gray-50 text-gray-700';
    input.value = value;
    if (min !== null) input.min = min;
    if (max !== null) input.max = max;
    if (step !== null) input.step = step;
    input.addEventListener('input', (e) => changeCallback(e.target.value));
    container.appendChild(input);
    return container;
}

// Helper to create text input with placeholder/unit, modernized
export function createTextInput(value, placeholder, unit, changeCallback) {
    const container = document.createElement('div');
    container.className = 'relative';
    const input = document.createElement('input');
    input.type = 'text'; // Use text to allow units like %
    input.className = 'w-full h-9 pl-3 pr-8 py-2 text-sm rounded-lg focus:outline-none bg-gray-50 text-gray-700';
    input.value = value;
    input.placeholder = placeholder;
    input.addEventListener('input', (e) => changeCallback(e.target.value));
    container.appendChild(input);
    if (unit) {
        const unitSpan = document.createElement('span');
        unitSpan.className = 'absolute right-3 top-1/2 transform -translate-y-1/2 text-xs text-gray-400';
        unitSpan.textContent = unit;
        container.appendChild(unitSpan);
    }
    return container;
}

// Helper to create modern button group
export function createButtonGroup(buttons, selectedValue, changeCallback) {
    const group = document.createElement('div');
    group.className = 'flex items-center rounded-lg overflow-hidden bg-gray-50';

    const updateSelection = (newValue) => {
        group.querySelectorAll('button').forEach(btn => {
            btn.classList.toggle('bg-primary-500', btn.dataset.value === newValue);
            btn.classList.toggle('text-white', btn.dataset.value === newValue);
            btn.classList.toggle('text-gray-600', btn.dataset.value !== newValue);
            btn.classList.toggle('hover:bg-gray-100', btn.dataset.value !== newValue);
        });
        changeCallback(newValue);
    };

    buttons.forEach((btnData, index) => {
        const button = document.createElement('button');
        button.className = `flex-1 px-3 py-1.5 text-sm focus:outline-none transition duration-150 ease-in-out`;
        button.dataset.value = btnData.value;
        if (btnData.icon) {
            button.innerHTML = btnData.icon;
        } else if (btnData.label) {
            button.textContent = btnData.label;
        }
        button.title = btnData.title;
        button.addEventListener('click', () => updateSelection(btnData.value));
        if (btnData.value === selectedValue) {
            button.classList.add('bg-primary-500', 'text-white');
        } else {
            button.classList.add('text-gray-600', 'hover:bg-gray-100');
        }
        group.appendChild(button);
    });

    return group;
}

// Helper function to create modernized linked padding/margin inputs
export function createLinkedInputs(labels, values, unit, linkable, changeCallback) {
    const container = document.createElement('div');
    container.className = 'grid grid-cols-5 gap-2 items-center'; // 4 inputs + 1 link button
    const inputs = {};
    let isLinked = linkable ? true : false; // Default to linked if linkable

    // Initial value setup (use top value if linked)
    const initialValue = isLinked ? values.top : null;

    labels.forEach(labelData => {
        const inputContainer = document.createElement('div');
        inputContainer.className = 'relative';
        
        // Label
        const labelEl = document.createElement('label');
        labelEl.className = 'block text-xs text-gray-500 mb-0.5 text-center';
        labelEl.textContent = labelData.label;
        inputContainer.appendChild(labelEl);
        
        // Input
        const input = document.createElement('input');
        input.type = 'text'; // Use text to allow various units
        input.className = 'w-full h-8 px-2 text-sm text-center rounded-lg focus:outline-none bg-gray-50 text-gray-700';
        input.placeholder = labelData.placeholder || '0';
        input.value = initialValue !== null ? initialValue : (values[labelData.key] || '0');
        input.dataset.key = labelData.key;

        input.addEventListener('input', (e) => {
            const key = e.target.dataset.key;
            const value = e.target.value;
            values[key] = value; // Update the specific value in the passed object

            if (isLinked) {
                // Update all other inputs and their values in the object
                Object.keys(inputs).forEach(k => {
                    if (k !== key) {
                        inputs[k].value = value;
                        values[k] = value;
                    }
                });
            }
            changeCallback(); // Notify core about the change
        });

        inputContainer.appendChild(input);
        inputs[labelData.key] = input; // Store input reference
        container.appendChild(inputContainer);
    });

    if (linkable) {
        const linkButton = document.createElement('button');
        linkButton.type = 'button';
        linkButton.className = 'flex items-center justify-center h-8 mt-auto text-gray-400 hover:text-primary-500 focus:outline-none';
        linkButton.innerHTML = `<i class="ri-link"></i>`;
        linkButton.title = 'קשר/נתק ערכים';

        const updateLinkVisual = () => {
            linkButton.innerHTML = `<i class="${isLinked ? 'ri-link' : 'ri-link-unlink'}"></i>`;
            linkButton.classList.toggle('text-primary-600', isLinked);
        };

        linkButton.addEventListener('click', () => {
            isLinked = !isLinked;
            updateLinkVisual();
            // If linking now, apply the first input's value to all
            if (isLinked) {
                const firstValue = inputs[labels[0].key].value;
                Object.keys(inputs).forEach(k => {
                    inputs[k].value = firstValue;
                    values[k] = firstValue; // Update state object
                });
                changeCallback(); // Update visuals
            }
        });
        container.appendChild(linkButton);
        updateLinkVisual(); // Set initial state
    }

    return container;
}

// Helper function for creating a modern slider with value display
export function createSlider(label, value, min, max, step, changeCallback, displayUnit = '%') {
    const container = document.createElement('div');
    container.className = 'mb-2';
    
    if (label) {
        const labelRow = document.createElement('div');
        labelRow.className = 'flex items-center justify-between mb-1';
        
        const labelEl = document.createElement('span');
        labelEl.className = 'text-sm text-gray-600';
        labelEl.textContent = label;
        
        const valueDisplay = document.createElement('span');
        valueDisplay.className = 'text-sm font-medium text-gray-800';
        valueDisplay.textContent = `${Math.round(value * (displayUnit === '%' ? 100 : 1))}${displayUnit}`;
        
        labelRow.appendChild(labelEl);
        labelRow.appendChild(valueDisplay);
        container.appendChild(labelRow);
    }
    
    const slider = document.createElement('input');
    slider.type = 'range';
    slider.className = 'w-full h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-primary-500';
    slider.min = min;
    slider.max = max;
    slider.step = step;
    slider.value = value;
    
    slider.addEventListener('input', (e) => {
        const newValue = parseFloat(e.target.value);
        if (label) {
            container.querySelector('span:last-child').textContent = 
                `${Math.round(newValue * (displayUnit === '%' ? 100 : 1))}${displayUnit}`;
        }
        changeCallback(newValue);
    });
    
    container.appendChild(slider);
    return container;
}

// Helper for creating a color input with modern styling
export function createColorInput(value, changeCallback, hasOpacity = false) {
    const container = document.createElement('div');
    container.className = 'relative';
    
    const colorPreview = document.createElement('div');
    colorPreview.className = 'w-full h-9 rounded-lg cursor-pointer flex items-center px-3 bg-gray-50';
    colorPreview.style.borderRight = '30px solid ' + value;
    
    const colorText = document.createElement('span');
    colorText.className = 'text-sm text-gray-700 uppercase';
    colorText.textContent = value.replace('#', '');
    colorPreview.appendChild(colorText);
    
    const colorInput = document.createElement('input');
    colorInput.type = 'color';
    colorInput.className = 'absolute inset-0 opacity-0 cursor-pointer';
    colorInput.value = value;
    
    colorInput.addEventListener('input', (e) => {
        const newColor = e.target.value;
        colorPreview.style.borderRight = '30px solid ' + newColor;
        colorText.textContent = newColor.replace('#', '');
        changeCallback(newColor);
    });
    
    container.appendChild(colorPreview);
    container.appendChild(colorInput);
    
    // If opacity control needed, this is where we'd add it
    if (hasOpacity) {
        // Additional opacity slider could be added here
    }
    
    return container;
}

// Helper function to convert rgba to hex (basic, ignores alpha)
export function rgbaToHex(rgba) {
    if (!rgba || typeof rgba !== 'string') return '#000000';
    if (rgba.startsWith('#')) return rgba;
    const parts = rgba.match(/\d+/g);
    if (!parts || parts.length < 3) return '#000000';
    const r = parseInt(parts[0]).toString(16).padStart(2, '0');
    const g = parseInt(parts[1]).toString(16).padStart(2, '0');
    const b = parseInt(parts[2]).toString(16).padStart(2, '0');
    return `#${r}${g}${b}`;
}

// --- Tab Population Functions ---

/**
 * Populates the Design tab content.
 * @param {HTMLElement} settingsPanel - The container element for the settings.
 * @param {object} widgetData - The data object for the specific widget instance.
 * @param {Function} updateCallback - The function to call when a setting changes.
 */
export function populateDesignTab(settingsPanel, widgetData, updateCallback) {
    settingsPanel.innerHTML = ''; // Clear previous
    console.log('Populating Design tab for:', widgetData);

    // Ensure config and styles objects exist
    if (!widgetData.config) widgetData.config = {};
    if (!widgetData.config.styles) widgetData.config.styles = {};
    if (!widgetData.config.styles.typography) widgetData.config.styles.typography = {};
    const config = widgetData.config;
    const styles = config.styles;
    const typo = styles.typography;

    // --- Typography Section (Restored) ---
    const { accordionItem: typoAccordion, contentDiv: typoContent } = createSettingsGroup('טיפוגרפיה', true);

    // Font Family
    const fontOptions = [
        { value: "'Noto Sans Hebrew', sans-serif", label: 'Noto Sans Hebrew' },
        // הוסף פונטים רלוונטיים אחרים כאן אם צריך
    ];
    typoContent.appendChild(createSelect(fontOptions, typo.fontFamily || fontOptions[0].value, (value) => { typo.fontFamily = value; updateCallback(); }));

    // Font Weight & Size (in a row)
    const weightSizeRow = document.createElement('div');
    weightSizeRow.className = 'grid grid-cols-2 gap-2 mt-3';
    const weightOptions = [
        { value: '300', label: 'דק (300)' },
        { value: '400', label: 'רגיל (400)' },
        { value: '500', label: 'בינוני (500)' },
        { value: '600', label: 'מודגש למחצה (600)' },
        { value: '700', label: 'מודגש (700)' },
        { value: '800', label: 'מודגש מאד (800)' }
    ];
    weightSizeRow.appendChild(createSelect(weightOptions, typo.fontWeight || '400', (value) => { typo.fontWeight = value; updateCallback(); }));
    // שימוש ב-config.fontSize (קלאס Tailwind) לגודל, לא typo.fontSize
    const fontSizes = [
        { value: 'text-xs', label: 'XS' }, { value: 'text-sm', label: 'S' }, { value: 'text-base', label: 'M' },
        { value: 'text-lg', label: 'L' }, { value: 'text-xl', label: 'XL' }, { value: 'text-2xl', label: '2XL' },
         { value: 'text-3xl', label: '3XL' }, { value: 'text-4xl', label: '4XL' },
    ];
    weightSizeRow.appendChild(createSelect(fontSizes, config.fontSize || 'text-base', (value) => { config.fontSize = value; updateCallback(); }));
    typoContent.appendChild(weightSizeRow);

    // Line Height & Letter Spacing (in a row)
    const heightSpacingRow = document.createElement('div');
    heightSpacingRow.className = 'grid grid-cols-2 gap-2 mt-3';
    heightSpacingRow.appendChild(createTextInput(typo.lineHeight || '1.5', '1.5', null, (value) => { typo.lineHeight = value || '1.5'; updateCallback(); })); // ללא יחידה, ברירת מחדל ל-1.5
    heightSpacingRow.appendChild(createTextInput(typo.letterSpacing || '0px', '0px', 'px', (value) => { typo.letterSpacing = value || '0px'; updateCallback(); }));
    typoContent.appendChild(heightSpacingRow);

    // Text Align
    const alignButtons = [
        { value: 'text-right', icon: '<i class="ri-align-right"></i>', title: 'ימין' },
        { value: 'text-center', icon: '<i class="ri-align-center"></i>', title: 'מרכז' },
        { value: 'text-left', icon: '<i class="ri-align-left"></i>', title: 'שמאל' }
        // { value: 'text-justify', icon: '<i class="ri-align-justify"></i>', title: 'יישור דו-צדדי' } // אפשר להוסיף
    ];
    // שימוש ב-config.textAlign (קלאס Tailwind)
    const alignGroup = createButtonGroup(alignButtons, config.textAlign || 'text-right', (value) => { config.textAlign = value; updateCallback(); });
    const alignContainer = document.createElement('div');
    alignContainer.className = 'mt-3';
    const alignLabel = document.createElement('label');
    alignLabel.className = 'block text-sm text-gray-600 mb-1';
    alignLabel.textContent = 'יישור';
    alignContainer.appendChild(alignLabel);
    alignContainer.appendChild(alignGroup);
    typoContent.appendChild(alignContainer);

    settingsPanel.appendChild(typoAccordion);

    // --- Text Color Section (Restored with helper) ---
    const { accordionItem: colorAccordion, contentDiv: colorContent } = createSettingsGroup('צבע טקסט');
    // שימוש ב-createColorInput
    colorContent.appendChild(createColorInput(styles.color || '#000000', (value) => {
        styles.color = value;
        updateCallback();
    }));
    settingsPanel.appendChild(colorAccordion);

    // --- Common Design Styles (Background, etc.) ---
    const { accordionItem: commonStyleGroup, contentDiv: commonStyleContent } = createSettingsGroup('רקע וכללי');

    // Background Color (Using helper)
    const bgColorLabel = document.createElement('label');
    bgColorLabel.className = 'block text-sm text-gray-600 mb-1';
    bgColorLabel.textContent = 'צבע רקע';
    commonStyleContent.appendChild(bgColorLabel);
    commonStyleContent.appendChild(createColorInput(styles.backgroundColor || '#ffffff', (value) => {
        styles.backgroundColor = value;
        updateCallback();
    }));

    settingsPanel.appendChild(commonStyleGroup);
}

/**
 * Populates the Advanced tab content.
 * @param {HTMLElement} settingsPanel - The container element for the settings.
 * @param {object} widgetData - The data object for the specific widget instance.
 * @param {Function} updateCallback - The function to call when a setting changes.
 */
export function populateAdvancedTab(settingsPanel, widgetData, updateCallback) {
    console.log('Populating Advanced tab for:', widgetData);
    settingsPanel.innerHTML = ''; // Clear previous

    if (!widgetData || !widgetData.config) {
        settingsPanel.innerHTML = '<p class="text-gray-500 text-sm p-4">בחר אלמנט לעריכה.</p>';
        return; // Exit if no valid element data
    }

    // Ensure styles structure exists
    if (!widgetData.config.styles) widgetData.config.styles = {};
    const styles = widgetData.config.styles;
    if (!styles.padding) styles.padding = { top: '0', right: '0', bottom: '0', left: '0' };
    if (!styles.border) styles.border = { width: '0', style: 'solid', color: '#000000' };
    if (!styles.boxShadow) styles.boxShadow = { type: 'none', x: '0', y: '0', blur: '0', spread: '0', color: 'rgba(0,0,0,0.1)'};

    // --- Padding Section (Accordion) ---
    const { accordionItem: paddingAccordion, contentDiv: paddingContent } = createSettingsGroup('ריפוד (Padding)', true);
    const paddingLabels = [
        { key: 'top', label: 'עליון', placeholder: '0' },
        { key: 'right', label: 'ימין', placeholder: '0' },
        { key: 'bottom', label: 'תחתון', placeholder: '0' },
        { key: 'left', label: 'שמאל', placeholder: '0' },
    ];
    paddingContent.appendChild(createLinkedInputs(paddingLabels, styles.padding, 'px', true, updateCallback));
    settingsPanel.appendChild(paddingAccordion);

    // --- Layer Section (Accordion - Opacity Only) ---
    const { accordionItem: layerAccordion, contentDiv: layerContent } = createSettingsGroup('שכבה (Layer)');
    
    // Blending mode dropdown
    const blendingRow = document.createElement('div');
    blendingRow.className = 'mb-3';
    
    const blendingLabel = document.createElement('div');
    blendingLabel.className = 'flex justify-between mb-1';
    
    const blendingLabelText = document.createElement('span');
    blendingLabelText.className = 'text-sm text-gray-600';
    blendingLabelText.textContent = 'מצב שילוב:';
    
    const blendingValue = document.createElement('span');
    blendingValue.className = 'text-sm text-gray-800';
    blendingValue.textContent = 'רגיל';
    
    blendingLabel.appendChild(blendingLabelText);
    blendingLabel.appendChild(blendingValue);
    blendingRow.appendChild(blendingLabel);
    
    const blendingSelect = createSelect([
        { value: 'normal', label: 'רגיל' },
        { value: 'multiply', label: 'כפל' },
        { value: 'screen', label: 'מסך' },
        { value: 'overlay', label: 'שכבה עליונה' }
    ], styles.mixBlendMode || 'normal', (value) => { 
        styles.mixBlendMode = value; 
        blendingValue.textContent = value === 'normal' ? 'רגיל' : value;
        updateCallback(); 
    });
    blendingRow.appendChild(blendingSelect);
    layerContent.appendChild(blendingRow);
    
    // Opacity slider
    const opacityValue = styles.opacity ?? 1;
    layerContent.appendChild(createSlider('אטימות', opacityValue, 0, 1, 0.01, (value) => {
        styles.opacity = value;
        updateCallback();
    }, '%'));
    
    settingsPanel.appendChild(layerAccordion);

    // --- Stroke Section (Accordion - Border) ---
    const { accordionItem: borderAccordion, contentDiv: borderContent } = createSettingsGroup('מסגרת (Border)');

    // Color
    borderContent.appendChild(createColorInput(styles.border.color || '#000000', (value) => {
        styles.border.color = value;
        updateCallback();
    }));

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

    widthWithLabel.appendChild(createNumberInput(null, parseInt(styles.border.width) || 0,
        (value) => { styles.border.width = `${parseInt(value) || 0}`; updateCallback(); }, 0, 50, 1));

    // Style with label
    const styleWithLabel = document.createElement('div');
    styleWithLabel.className = 'flex flex-col';

    const styleLabel = document.createElement('span');
    styleLabel.className = 'text-xs text-gray-500 mb-1';
    styleLabel.textContent = 'סגנון';
    styleWithLabel.appendChild(styleLabel);

    styleWithLabel.appendChild(createSelect(
        [{value: 'solid', label:'רציף'}, {value: 'dashed', label:'מקווקו'}, {value: 'dotted', label:'נקודות'}, {value: 'none', label: 'ללא'}],
        styles.border.style || 'solid',
        (value) => { styles.border.style = value; updateCallback(); }
    ));

    strokeControlsRow.appendChild(widthWithLabel);
    strokeControlsRow.appendChild(styleWithLabel);
    borderContent.appendChild(strokeControlsRow);

    settingsPanel.appendChild(borderAccordion);

    // --- Shadow Section (Accordion) ---
    const { accordionItem: shadowAccordion, contentDiv: shadowContent } = createSettingsGroup('צל (Shadow)');
    
    // Shadow Type
    const shadowTypeRow = document.createElement('div');
    shadowTypeRow.className = 'mb-3';
    
    const shadowTypeLabel = document.createElement('span');
    shadowTypeLabel.className = 'text-xs text-gray-500 mb-1 block';
    shadowTypeLabel.textContent = 'סוג צל';
    shadowTypeRow.appendChild(shadowTypeLabel);
    
    shadowTypeRow.appendChild(createSelect(
        [{value: 'none', label: 'ללא'}, {value: 'drop-shadow', label: 'Drop Shadow'}], 
        styles.boxShadow.type || 'none', 
        (value) => { styles.boxShadow.type = value; updateCallback(); }
    ));
    
    shadowContent.appendChild(shadowTypeRow);
    
    // Shadow controls (X, Y)
    const shadowXYRow = document.createElement('div');
    shadowXYRow.className = 'grid grid-cols-2 gap-2 mb-3';
    shadowXYRow.appendChild(createNumberInput('X', parseInt(styles.boxShadow.x) || 0, 
        (value) => { styles.boxShadow.x = `${parseInt(value) || 0}px`; updateCallback(); }));
    shadowXYRow.appendChild(createNumberInput('Y', parseInt(styles.boxShadow.y) || 0, 
        (value) => { styles.boxShadow.y = `${parseInt(value) || 0}px`; updateCallback(); }));
    shadowContent.appendChild(shadowXYRow);
    
    // Shadow controls (Blur, Spread)
    const shadowBlurSpreadRow = document.createElement('div');
    shadowBlurSpreadRow.className = 'grid grid-cols-2 gap-2 mb-3';
    shadowBlurSpreadRow.appendChild(createNumberInput('Blur', parseInt(styles.boxShadow.blur) || 0, 
        (value) => { styles.boxShadow.blur = `${parseInt(value) || 0}px`; updateCallback(); }, 0));
    shadowBlurSpreadRow.appendChild(createNumberInput('Spread', parseInt(styles.boxShadow.spread) || 0, 
        (value) => { styles.boxShadow.spread = `${parseInt(value) || 0}px`; updateCallback(); }));
    shadowContent.appendChild(shadowBlurSpreadRow);
    
    // Shadow Color
    shadowContent.appendChild(createColorInput(
        rgbaToHex(styles.boxShadow.color) || '#000000', 
        (value) => { styles.boxShadow.color = value; updateCallback(); },
        true // Allow opacity
    ));
    
    settingsPanel.appendChild(shadowAccordion);

    // --- Custom Identifiers Section (Accordion) ---
    const { accordionItem: idClassAccordion, contentDiv: idClassContent } = createSettingsGroup('מזהים וקלאסים');

    // Custom ID Input
    const idContainer = document.createElement('div');
    idContainer.className = 'mb-3';
    const idLabel = document.createElement('label');
    idLabel.className = 'block text-sm text-gray-600 mb-1';
    idLabel.htmlFor = `custom-id-${widgetData.id}`;
    idLabel.textContent = 'Custom ID';
    const idInput = document.createElement('input');
    idInput.type = 'text';
    idInput.id = `custom-id-${widgetData.id}`;
    idInput.className = 'w-full h-9 px-3 text-sm rounded-lg focus:outline-none bg-gray-50 text-gray-700';
    idInput.placeholder = 'my-unique-widget-id';
    idInput.value = widgetData.config.customId || '';
    idInput.addEventListener('input', (e) => {
        // Basic validation: allow letters, numbers, hyphens, underscores
        e.target.value = e.target.value.replace(/[^a-zA-Z0-9-_]/g, '');
        widgetData.config.customId = e.target.value;
        updateCallback(); // Update state, no immediate re-render needed for this
    });
    idContainer.appendChild(idLabel);
    idContainer.appendChild(idInput);
    idClassContent.appendChild(idContainer);

    // Custom Class Input
    const classContainer = document.createElement('div');
    classContainer.className = 'mb-3';
    const classLabel = document.createElement('label');
    classLabel.className = 'block text-sm text-gray-600 mb-1';
    classLabel.htmlFor = `custom-class-${widgetData.id}`;
    classLabel.textContent = 'Custom CSS Classes';
    const classInput = document.createElement('input');
    classInput.type = 'text';
    classInput.id = `custom-class-${widgetData.id}`;
    classInput.className = 'w-full h-9 px-3 text-sm rounded-lg focus:outline-none bg-gray-50 text-gray-700';
    classInput.placeholder = 'my-class another-class';
    classInput.value = widgetData.config.customClass || '';
    classInput.addEventListener('input', (e) => {
        // Basic validation: allow letters, numbers, hyphens, underscores, spaces
        e.target.value = e.target.value.replace(/[^a-zA-Z0-9-_\s]/g, '');
        widgetData.config.customClass = e.target.value.trim(); // Store trimmed value
        updateCallback(); // Update state, re-render needed to apply class
    });
    classContainer.appendChild(classLabel);
    classContainer.appendChild(classInput);
    idClassContent.appendChild(classContainer);

    settingsPanel.appendChild(idClassAccordion);
}