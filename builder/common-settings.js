// Logic for common settings (Design & Advanced tabs)

console.log('Common Settings module loaded');

// --- הוספה: ייבוא חסר ---
import { getCurrentBreakpoint, getEffectiveConfig, saveResponsiveSetting, getNestedValue } from './render-responsive.js';
// ------------------------

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

// --- הוספה: פונקציה ליצירת כותרת סעיף --- 
/**
 * Creates a simple section title element.
 * @param {string} titleText The text for the title.
 * @returns {HTMLElement} The title element (e.g., h3).
 */
export function createSectionTitle(titleText) {
    const titleElement = document.createElement('h3');
    titleElement.className = 'text-sm font-medium text-gray-800 mb-3 pt-2 border-t border-gray-200'; // Example styling
    titleElement.textContent = titleText;
    return titleElement;
}
// -----------------------------------------------

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

/**
 * יוצר קבוצת שדות קלט מקושרים (כמו פדינג/מרג'ין) עם חיווי רספונסיבי.
 * @param {array} labels - מערך של מצבים עבור הקלטים.
 * @param {object} initialValues - אובייקט עם ערכים ההתחלתיים של הקלטים.
 * @param {string} unit - יחידת המידה של הקלטים.
 * @param {boolean} linkable - האם יש לקשר את הקלטים בין עצמם.
 * @param {function} changeCallback - פונקציה שתקרא בכל שינוי, מקבלת את הערכים המעודכנים ואת ההקשר המקומי.
 * @param {boolean} isResponsive - האם יש להציג אייקונים רספונסיביים.
 * @param {object} overrideStatus - אובייקט עם מצב הנראות לאייקונים רספונסיביים.
 * @param {function} [fetchValueCallback=null] - קולבק לקבלת הערך האפקטיבי לברייקפוינט נתון.
 * @param {string[]} [settingPath=[]] - נתיב ההגדרה (נחוץ עבור fetchValueCallback).
 * @returns {HTMLElement} - אלמנט ה-DOM של קבוצת הקלטים.
 */
export function createLinkedInputs(labelText, elementData, baseSettingPath, unitOptions = ['px', '%', 'em', 'rem', 'vh', 'vw'], defaultUnit = 'px', updateCallback) {
    const container = document.createElement('div');
    container.className = 'mb-4'; // Add some bottom margin

    // --- Row 1: Unit Selector (Left) + Responsive Icons (Right) ---
    const topControlsRow = document.createElement('div');
    topControlsRow.className = 'flex items-center justify-between mb-2';

    // Unit Selector (Aligned Left)
    const unitSelectorWrapper = document.createElement('div');
    unitSelectorWrapper.className = 'relative flex-shrink-0'; // Wrapper for select + arrow
    const unitSelector = document.createElement('select');
    unitSelector.className = 'h-8 pl-2 pr-5 text-xs font-medium text-gray-600 focus:outline-none appearance-none bg-transparent border-none'; // No background/border
    unitOptions.forEach(unit => {
        const option = document.createElement('option');
        option.value = unit;
        option.textContent = unit;
        if (unit === defaultUnit) option.selected = true;
        unitSelector.appendChild(option);
    });
    unitSelector.addEventListener('change', () => {
        updateInputValuesAndState(true);
    });
    unitSelectorWrapper.appendChild(unitSelector);
    const arrowIcon = document.createElement('i'); // Custom arrow
    arrowIcon.className = 'ri-arrow-down-s-line text-xs text-gray-400 absolute right-1 top-1/2 transform -translate-y-1/2 pointer-events-none';
    unitSelectorWrapper.appendChild(arrowIcon);
    topControlsRow.appendChild(unitSelectorWrapper); // Add unit selector first

    // Responsive Icons Container (Aligned Right)
    const responsiveControlsContainer = document.createElement('div');
    // --- שינוי: הסרת מסגרת ורקע מהקונטיינר ---
    responsiveControlsContainer.className = 'flex items-center space-x-1 rtl:space-x-reverse'; // No border/bg on container
    // -----------------------------------------
    createResponsiveControls(responsiveControlsContainer, elementData, baseSettingPath, updateCallback, { size: 'small' });
    topControlsRow.appendChild(responsiveControlsContainer); // Add responsive icons

    container.appendChild(topControlsRow);

    // --- Row 2: Inputs + Link Button ---
    const inputsRow = document.createElement('div');
    inputsRow.className = 'flex items-end space-x-2 rtl:space-x-reverse'; // Flex row for inputs + link button

    // Input Elements Holder
    const inputElements = [];

    // Link Button (will be added at the end)
    const linkButton = document.createElement('button');
    linkButton.type = 'button';
    linkButton.className = 'flex items-center justify-center h-8 w-8 text-gray-400 hover:text-primary-500 focus:outline-none rounded-md hover:bg-gray-100 flex-shrink-0'; // Placed after inputs
    linkButton.title = 'קשר/נתק ערכים';
    linkButton.dataset.linked = 'true';

    // Function to update link button visual
    const updateLinkVisual = () => {
        const isLinked = linkButton.dataset.linked === 'true';
        linkButton.innerHTML = `<i class="ri-${isLinked ? 'link' : 'link-unlink'} text-base"></i>`;
        linkButton.classList.toggle('text-primary-500', isLinked);
        linkButton.classList.toggle('text-gray-400', !isLinked);
    };

    // Link button click handler
    linkButton.addEventListener('click', () => {
        const isCurrentlyLinked = linkButton.dataset.linked === 'true';
        const justLinked = !isCurrentlyLinked; // האם המשתמש בדיוק נעל?
        linkButton.dataset.linked = isCurrentlyLinked ? 'false' : 'true';
        updateLinkVisual(); // עדכן מראה מיידי
        updateInputValuesAndState(false, justLinked); // העבר את הדגל החדש
    });

    // Create the 4 input fields and add to inputsRow
    ['top', 'right', 'bottom', 'left'].forEach((side) => {
        const inputWrapper = document.createElement('div');
        inputWrapper.className = 'flex flex-col items-center flex-grow'; // Inputs take remaining space

        const inputLabel = document.createElement('label');
        inputLabel.className = 'block text-xs text-gray-500 mb-0.5';
        const sideLabels = { top: 'מעלה', right: 'ימין', bottom: 'מטה', left: 'שמאל' };
        inputLabel.textContent = sideLabels[side];
        inputWrapper.appendChild(inputLabel);

        const inputElement = document.createElement('input');
        inputElement.type = 'text';
        inputElement.className = 'w-full h-8 px-1 text-sm text-center rounded-lg focus:outline-none bg-gray-50 text-gray-700 border border-gray-200 focus:border-primary-300 focus:ring-1 focus:ring-primary-300';
        inputElement.placeholder = '0';
        inputElement.dataset.side = side;
        inputElement.addEventListener('input', (e) => {
             const value = e.target.value;
             const validatedValue = value.match(/^-?\d*\.?\d*/)?.[0] || '';
             if (validatedValue !== value) {
                e.target.value = validatedValue;
             }
             const finalValue = validatedValue;
             const unit = unitSelector.value;
             const fullValue = (finalValue === '' || finalValue === null || finalValue === undefined) ? '' : `${finalValue}${unit}`;
             const sidePath = [...baseSettingPath, side];
             const currentBreakpoint = getCurrentBreakpoint();

             console.log(`Input change for ${side}: value=${finalValue}, unit=${unit}, fullValue=${fullValue}, bp=${currentBreakpoint}`);

             if (linkButton.dataset.linked === 'true') {
                 console.log('Linked state: updating all sides');
                 inputElements.forEach(input => { if (input !== inputElement) input.value = finalValue; });
                 ['top', 'right', 'bottom', 'left'].forEach(s => {
                     saveResponsiveSetting(elementData, [...baseSettingPath, s], fullValue, currentBreakpoint, updateCallback);
                 });
             } else {
                 console.log(`Unlinked state: updating only ${side}`);
                 saveResponsiveSetting(elementData, sidePath, fullValue, currentBreakpoint, updateCallback);
             }
        });

        inputWrapper.appendChild(inputElement);
        inputsRow.appendChild(inputWrapper);
        inputElements.push(inputElement);
    });

    // Add Link Button to the end of the inputs row
    inputsRow.appendChild(linkButton);

    // --- הוספה: קביעת מצב נעילה התחלתי לפי הנתונים --- 
    const determineInitialLinkState = () => {
        const currentBreakpoint = getCurrentBreakpoint();
        let initialValues = [];
        let allSame = true;
        for (let i = 0; i < 4; i++) {
            const side = inputElements[i].dataset.side;
            const sidePath = [...baseSettingPath, side];
            let currentValueRaw = getNestedValue(elementData.config, ['responsiveOverrides', currentBreakpoint, ...sidePath]);
            let desktopValueRaw = getNestedValue(elementData.config, ['responsiveOverrides', 'desktop', ...sidePath]) ?? getNestedValue(elementData.config, sidePath);
            const effectiveValue = currentValueRaw ?? desktopValueRaw ?? ''; // קח את הערך האפקטיבי
            initialValues.push(effectiveValue);
            if (i > 0 && effectiveValue !== initialValues[0]) {
                allSame = false;
                // אין צורך להמשיך לבדוק אם מצאנו חוסר אחידות
                // break; // אפשר לצאת מהלולאה כאן, אבל נמשיך כדי לקבל את כל הערכים אם נרצה בעתיד
            }
        }
        console.log(`Initial values for ${baseSettingPath.join('.')} at ${currentBreakpoint}:`, initialValues, `All same? ${allSame}`);
        linkButton.dataset.linked = allSame ? 'true' : 'false';
    };
    determineInitialLinkState();
    // ------------------------------------------------------

    let firstValuePlaceholder = '';

    // Function to update inputs based on state, link status, and breakpoint
    const updateInputValuesAndState = (forceSaveWithNewUnit = false, justLinked = false) => {
        const currentBreakpoint = getCurrentBreakpoint();
        // --- שינוי: קריאת המצב הנוכחי של הכפתור --- 
        const isNowLinked = linkButton.dataset.linked === 'true';
        console.log(`Updating linked inputs for ${baseSettingPath.join('.')} at ${currentBreakpoint}. Is now linked: ${isNowLinked}, Just linked: ${justLinked}`);

        let firstValue = null;
        let firstUnit = defaultUnit;
        let firstValuePlaceholder = ''; 
        let firstFullValueRaw = null; // לשמור את הערך המלא של הראשון עבור שמירה
        const isInitialRun = !container.hasAttribute('data-initialized');
        if (isInitialRun) {
            container.setAttribute('data-initialized', 'true');
        }

        let allValuesAreIdentical = true;
        let firstActualValue = undefined; 

        inputElements.forEach((input, index) => {
            const side = input.dataset.side;
            const sidePath = [...baseSettingPath, side];
            let currentValueRaw = getNestedValue(elementData.config, ['responsiveOverrides', currentBreakpoint, ...sidePath]);
            let desktopValueRaw = getNestedValue(elementData.config, ['responsiveOverrides', 'desktop', ...sidePath]) ?? getNestedValue(elementData.config, sidePath);

            // console.log(` -> Side: ${side}, Current BP Raw: ${currentValueRaw}, Desktop/Base Raw: ${desktopValueRaw}`);

            let valueToDisplay = '';
            let placeholderToDisplay = '';
            let unitToUse = defaultUnit;

            const parseValue = (rawValue) => {
                if (rawValue === undefined || rawValue === null || rawValue === '') return { value: '', unit: defaultUnit };
                const match = String(rawValue).match(/^(-?\d*\.?\d+)(.*)$/);
                const value = match ? match[1] : '';
                const unit = match && match[2] && unitOptions.includes(match[2]) ? match[2] : defaultUnit;
                return { value, unit };
            };

            const currentParsed = parseValue(currentValueRaw);
            const desktopParsed = parseValue(desktopValueRaw);

            if (currentValueRaw !== undefined && currentValueRaw !== null && currentValueRaw !== '') {
                valueToDisplay = currentParsed.value;
                unitToUse = currentParsed.unit;
            } else if (desktopValueRaw !== undefined && desktopValueRaw !== null && desktopValueRaw !== '') {
                placeholderToDisplay = desktopParsed.value;
                unitToUse = desktopParsed.unit;
                valueToDisplay = '';
            } else {
                placeholderToDisplay = '0';
                valueToDisplay = '';
                unitToUse = defaultUnit;
            }

            let actualValueDisplayed = valueToDisplay || placeholderToDisplay || '';
            if (index === 0) {
                firstActualValue = actualValueDisplayed;
                firstValue = valueToDisplay;
                firstValuePlaceholder = placeholderToDisplay;
                firstUnit = unitToUse;
                firstFullValueRaw = currentValueRaw || desktopValueRaw || `0${defaultUnit}`;
                if (isInitialRun) {
                    unitSelector.value = firstUnit;
                }
            } else if (actualValueDisplayed !== firstActualValue) {
                allValuesAreIdentical = false;
            }
            
            // שימוש ב-isNowLinked לשליטה בהשבתה/הפעלה
            if (isNowLinked && index > 0) { 
                input.value = firstValue;
                input.placeholder = firstValuePlaceholder;
                input.disabled = true;
                input.classList.add('opacity-50');
            } else {
                input.value = valueToDisplay;
                input.placeholder = placeholderToDisplay;
                input.disabled = false;
                input.classList.remove('opacity-50');
            }

            input.classList.toggle('placeholder-gray-400', !!input.placeholder && !input.value);
            input.classList.toggle('italic', !!input.placeholder && !input.value);

            // שמירה רק אם מחליפים יחידה
            if (forceSaveWithNewUnit) {
                 const currentUnit = unitSelector.value; 
                 let numericValueToSave = '';
                 // השתמש ב-isNowLinked גם כאן לקביעת הערך לשמירה
                 if (isNowLinked) { 
                    numericValueToSave = firstValue || firstValuePlaceholder; 
                 } else {
                    numericValueToSave = valueToDisplay || placeholderToDisplay;
                 }
                 if (!numericValueToSave) numericValueToSave = ''; 
                 const valueToSave = (numericValueToSave === '' || numericValueToSave === null || numericValueToSave === undefined) ? '' : `${numericValueToSave}${currentUnit}`;
                 saveResponsiveSetting(elementData, sidePath, valueToSave, currentBreakpoint, updateCallback);
                 console.log(` -> Force saving ${sidePath.join('.')} with new unit ${currentUnit}: ${valueToSave} (numeric: ${numericValueToSave})`); 
            }
        }); // סוף הלולאה

        // שמירת הערך הראשון אם המשתמש בדיוק נעל
        if (justLinked && firstFullValueRaw !== null) {
            console.log(`User just linked inputs. Saving first value (${firstFullValueRaw}) to all sides.`);
            const valueToSave = firstFullValueRaw; // הערך המלא כולל יחידה
             ['top', 'right', 'bottom', 'left'].forEach(s => {
                 saveResponsiveSetting(elementData, [...baseSettingPath, s], valueToSave, currentBreakpoint, updateCallback);
             });
        }
        // ------------------------------------------------------

        // עדכון סופי של מראה הכפתור לפי המצב העדכני שלו
        updateLinkVisual(); 
    };

    // --- שינוי: קריאה ראשונית אחרי קביעת מצב הנעילה --- 
    updateLinkVisual(); // עדכן מראה לפי המצב שנקבע
    updateInputValuesAndState(); // מלא את השדות לפי המצב שנקבע
    // ---------------------------------------------------

    window.addEventListener('global-breakpoint-changed', (event) => {
         console.log(`Linked inputs for ${baseSettingPath.join('.')} detected global-breakpoint-changed to ${event.detail.breakpoint}`);
         // --- שינוי: צריך לקבוע מחדש את מצב הנעילה גם כאן? --- 
         // כן, כי ייתכן שב-breakpoint החדש הערכים כן/לא זהים
         determineInitialLinkState(); // חשוב לקבוע מחדש את המצב
         updateInputValuesAndState();
    });

    container.appendChild(inputsRow); // Append the inputs row
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
export function createColorPicker(initialValue, changeCallback, options = {}) {
    // const { hasOpacity = false } = options; // אפשר להוסיף בעתיד
    const container = document.createElement('div');
    container.className = 'relative mb-2';

    // יצירת תצוגה מקדימה של הצבע (דומה ל-createColorInput)
    const previewWrapper = document.createElement('div');
    previewWrapper.className = 'w-full h-9 rounded-lg cursor-pointer flex items-center px-3 bg-gray-50 border border-gray-200';

    const colorPreviewBox = document.createElement('div');
    colorPreviewBox.className = 'w-5 h-5 rounded-sm mr-2 shadow-inner';
    colorPreviewBox.style.backgroundColor = initialValue;

    const colorHexText = document.createElement('span');
    colorHexText.className = 'text-xs text-gray-500 uppercase flex-grow text-left'; // יישור לשמאל
    colorHexText.textContent = initialValue.startsWith('#') ? initialValue : rgbaToHex(initialValue);

    previewWrapper.appendChild(colorPreviewBox);
    previewWrapper.appendChild(colorHexText);

    // יצירת בורר הצבעים המוסתר
    const colorInput = document.createElement('input');
    colorInput.type = 'color';
    colorInput.className = 'absolute inset-0 w-full h-full opacity-0 cursor-pointer';
    colorInput.value = initialValue.startsWith('#') ? initialValue : rgbaToHex(initialValue);

    colorInput.addEventListener('input', (e) => {
        const newColor = e.target.value;
        colorPreviewBox.style.backgroundColor = newColor;
        colorHexText.textContent = newColor;
        changeCallback(newColor);
    });

    // הוספת אלמנטים לקונטיינר
    container.appendChild(previewWrapper);
    container.appendChild(colorInput);

    // (נוכל להוסיף כאן לוגיקה של opacity אם נצטרך)

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

// --- הוספה: הגדרה חדשה ונכונה של createResponsiveControls ---
// (ההגדרה הזו נדרשת על ידי createLinkedInputs)
export function createResponsiveControls(parentElement, elementData, settingPath, controlUpdateCallback, options = {}) {
    const { size = 'normal' } = options; // Default to normal size
    const currentBreakpoint = getCurrentBreakpoint();
    // Clear existing content if any
    parentElement.innerHTML = ''; 
    // Add base classes from parent call
    // parentElement.className += ' flex items-center space-x-1 rtl:space-x-reverse';

    const breakpoints = ['desktop', 'tablet', 'mobile'];
    const icons = {
        desktop: 'ri-computer-line',
        tablet: 'ri-tablet-line',
        mobile: 'ri-smartphone-line'
    };

    // Adjust padding and icon size based on the option
    const buttonPadding = size === 'small' ? 'p-0.5' : 'p-1';
    const iconSizeClass = size === 'small' ? 'text-xs' : 'text-sm'; // Example: adjust icon size class

    // Function to update active button state and trigger global change
    const setActiveBreakpoint = (bp) => {
        // Update visual state of buttons in this group
        parentElement.querySelectorAll('.responsive-icon-button').forEach(b => {
            const isActive = b.dataset.breakpoint === bp;
            // Base classes + conditional active/inactive classes
            b.className = `responsive-icon-button ${buttonPadding} rounded transition-colors duration-150 ${isActive ? 'text-primary-500 bg-primary-50' : 'text-gray-500 hover:text-primary-500 hover:bg-gray-100'}`;
        });
        if (getCurrentBreakpoint() === bp) return;
        console.log(`Dispatching change-global-breakpoint event for: ${bp}`);
        const event = new CustomEvent('change-global-breakpoint', { detail: { breakpoint: bp }, bubbles: true, composed: true });
        parentElement.dispatchEvent(event);
    };

    breakpoints.forEach(bp => {
        const btn = document.createElement('button');
        btn.type = 'button';
        const isActive = bp === currentBreakpoint;
        btn.className = `responsive-icon-button ${buttonPadding} rounded transition-colors duration-150 ${isActive ? 'text-primary-500 bg-primary-50' : 'text-gray-500 hover:text-primary-500 hover:bg-gray-100'}`;
        btn.dataset.breakpoint = bp;
        btn.innerHTML = `<i class="${icons[bp]} ${iconSizeClass}"></i>`; // Apply size class to icon

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            setActiveBreakpoint(bp);
        });
        parentElement.appendChild(btn);
    });

    // Listen for global changes to update local icons
    const handleGlobalChange = (event) => {
        const newGlobalBreakpoint = event.detail.breakpoint;
        console.log(`Responsive control group for ${Array.isArray(settingPath) ? settingPath.join('.') : 'unknown'} detected global change to ${newGlobalBreakpoint}`);
        parentElement.querySelectorAll('.responsive-icon-button').forEach(b => {
            const isActive = b.dataset.breakpoint === newGlobalBreakpoint;
             // Re-apply classes based on active state
            b.className = `responsive-icon-button ${buttonPadding} rounded transition-colors duration-150 ${isActive ? 'text-primary-500 bg-primary-50' : 'text-gray-500 hover:text-primary-500 hover:bg-gray-100'}`;
        });
    };
    window.addEventListener('global-breakpoint-changed', handleGlobalChange);

    // Note: Need a way to remove this listener when the control is destroyed
}
// --- סוף הגדרה חדשה createResponsiveControls ---

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
    // שימוש ב-createColorPicker
    colorContent.appendChild(createColorPicker(styles.color || '#000000', (value) => {
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
    commonStyleContent.appendChild(createColorPicker(styles.backgroundColor || '#ffffff', (value) => {
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
    paddingContent.appendChild(createLinkedInputs(
        'ריפוד', // labelText
        widgetData, // elementData
        ['styles', 'padding'], // baseSettingPath for saving
        ['px', '%', 'em', 'rem', 'vh', 'vw'], // unitOptions
        'px', // defaultUnit
        updateCallback // updateCallback
    ));
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
    borderContent.appendChild(createColorPicker(styles.border.color || '#000000', (value) => {
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
    shadowContent.appendChild(createColorPicker(
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

// --- הוספה: פונקציה ליצירת פקדי נראות רספונסיביים ---

/**
 * יוצר קבוצת כפתורים לשליטה בנראות עבור נקודות שבירה שונות.
 * @param {object} initialVisibility - אובייקט עם מצב הנראות ההתחלתי, למשל { desktop: true, tablet: true, mobile: true }.
 * @param {function} changeCallback - פונקציה שתקרא בכל שינוי, מקבלת את אובייקט ה-visibility המעודכן.
 * @returns {HTMLElement} - אלמנט ה-DOM של קבוצת הכפתורים.
 */
export function createVisibilityControls(initialVisibility, changeCallback) {
    const container = document.createElement('div');
    container.className = 'flex items-center justify-center space-x-2 rtl:space-x-reverse'; // Center align buttons

    const breakpoints = [
        { key: 'desktop', icon: 'ri-computer-line', title: 'הצג/הסתר בדסקטופ' },
        { key: 'tablet', icon: 'ri-tablet-line', title: 'הצג/הסתר בטאבלט' },
        { key: 'mobile', icon: 'ri-smartphone-line', title: 'הצג/הסתר במובייל' },
    ];

    // Internal state to track visibility
    let currentVisibility = { ...initialVisibility };

    breakpoints.forEach(bp => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'p-2 rounded-lg transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-primary-400';
        button.title = bp.title;
        button.dataset.breakpoint = bp.key;

        const icon = document.createElement('i');
        icon.className = bp.icon + ' text-xl'; // Larger icons
        button.appendChild(icon);

        // Function to update button appearance based on visibility state
        const updateButtonVisual = (isVisible) => {
            button.classList.toggle('bg-primary-500', isVisible);
            button.classList.toggle('text-white', isVisible);
            button.classList.toggle('bg-gray-100', !isVisible);
            button.classList.toggle('text-gray-500', !isVisible);
            button.classList.toggle('hover:bg-primary-600', isVisible);
            button.classList.toggle('hover:bg-gray-200', !isVisible);
        };

        button.addEventListener('click', () => {
            // Toggle the visibility for the specific breakpoint
            currentVisibility[bp.key] = !currentVisibility[bp.key];
            // Update the button's visual state
            updateButtonVisual(currentVisibility[bp.key]);
            // Call the callback with the updated visibility object
            changeCallback({ ...currentVisibility });
        });

        // Set initial visual state
        updateButtonVisual(currentVisibility[bp.key]);

        container.appendChild(button);
    });

    return container;
}

// --- NEW: Function to create checkbox ---

/**
 * Creates a styled checkbox input with a label.
 * @param {string} labelText The text for the checkbox label.
 * @param {boolean} isChecked Initial checked state.
 * @param {function} changeCallback Function to call when the state changes. Receives the new boolean state.
 * @returns {HTMLElement} The container div for the checkbox and label.
 */
export function createCheckbox(labelText, isChecked, changeCallback) {
    const container = document.createElement('div');
    container.className = 'flex items-center justify-between my-2'; // Use justify-between to push label and checkbox apart

    const label = document.createElement('label');
    label.className = 'text-sm text-gray-600 cursor-pointer';
    label.textContent = labelText;

    const checkboxWrapper = document.createElement('div');
    checkboxWrapper.className = 'relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in'; // Tailwind toggle switch base

    const checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.className = 'toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer focus:outline-none';
    checkbox.checked = isChecked;
    checkbox.style.right = isChecked ? '0' : 'auto'; // Initial position based on state
    checkbox.style.left = isChecked ? 'auto' : '0';
    checkbox.style.borderColor = isChecked ? '#0ea5e9' : '#e5e7eb'; // primary-500 or gray-200

    const toggleLabel = document.createElement('label'); // The background track
    toggleLabel.className = 'toggle-label block overflow-hidden h-6 rounded-full cursor-pointer';
    toggleLabel.style.backgroundColor = isChecked ? '#0ea5e9' : '#e5e7eb'; // primary-500 or gray-200

    // Link label click to checkbox click
    label.addEventListener('click', () => checkbox.click()); 
    toggleLabel.addEventListener('click', () => checkbox.click());
    
    checkbox.addEventListener('change', (e) => {
        const newState = e.target.checked;
        checkbox.style.right = newState ? '0' : 'auto';
        checkbox.style.left = newState ? 'auto' : '0';
        checkbox.style.borderColor = newState ? '#0ea5e9' : '#e5e7eb';
        toggleLabel.style.backgroundColor = newState ? '#0ea5e9' : '#e5e7eb';
        changeCallback(newState);
    });

    checkboxWrapper.appendChild(checkbox);
    checkboxWrapper.appendChild(toggleLabel);

    container.appendChild(label); // Label on the left
    container.appendChild(checkboxWrapper); // Toggle switch on the right

    return container;
}

// --- פונקציות עזר פנימיות ---
// (ייתכן שצריך להעביר את updateInputValuesAndState לכאן או למצוא דרך טובה יותר לשתף אותה)

// ... existing code ...