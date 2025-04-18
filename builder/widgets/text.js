// Text Widget Module

// --- הוספה: ייבוא פונקציות רספונסיביות ושמירה ---

// --- שינוי: ייבוא פקדים נוספים ---
import {
    createTextInput,
    createSelect,
    createSettingsGroup,
    createColorPicker,      // <<< שינוי ל-createColorPicker
    createButtonGroup,     // <<< הוספה
    createVisibilityControls, // <<< הוספה (בהנחה שקיים)
    createLinkedInputs // <<< החלפה
} from '../common-settings.js';

import { saveResponsiveSetting, getSettingOverrideStatus, getEffectiveConfig, getNestedValue, getCurrentBreakpoint } from '../render-responsive.js';

// ---------------------------------------------------

console.log('Text Widget module loaded');

export function getWidgetName() {
    return 'טקסט';
}

export function getDefaultConfig() {
    return {
        content: 'טקסט ברירת מחדל',
        htmlTag: 'p',
        styles: { // שינוי: העברת סגנונות לכאן
            color: '#000000', 
            // נוסיף בהמשך הגדרות טיפוגרפיה כמו פונט, גודל, משקל וכו' כאן
            typography: {
                 fontSize: 'text-base', // ברירת מחדל
                 textAlign: 'text-right' // ברירת מחדל
            },
            // --- הוספה: ברירות מחדל למרווחים ---
            margin: { top: '0', right: '0', bottom: '0', left: '0', unit: 'px' },
            padding: { top: '0', right: '0', bottom: '0', left: '0', unit: 'px' }
            // ------------------------------------
        },
        visibility: { desktop: true, tablet: true, mobile: true },
        responsiveOverrides: {}
    };
}

/**
 * פנימי: ממלא את טאב התוכן
 * @param {HTMLElement} panel 
 * @param {object} elementData 
 * @param {object} effectiveConfig 
 * @param {function} updateCallback 
 */
function _populateContentTab(panel, elementData, effectiveConfig, updateCallback) {
    panel.innerHTML = ''; 
    const config = effectiveConfig;

    const contentFormGroup = document.createElement('div');
    contentFormGroup.className = 'mb-4';

    const contentLabel = document.createElement('label');
    contentLabel.textContent = 'תוכן טקסט:';
    contentLabel.className = 'block text-sm font-medium text-gray-700 mb-1';
    contentLabel.htmlFor = `text-content-${elementData.id}`;

    const textarea = document.createElement('textarea');
    textarea.id = `text-content-${elementData.id}`;
    textarea.value = config.content || '';
    textarea.className = 'w-full h-32 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent shadow-sm';
    textarea.rows = 6;

    textarea.addEventListener('input', () => {
        saveResponsiveSetting(elementData, ['content'], textarea.value, updateCallback);
    });

    contentFormGroup.appendChild(contentLabel);
    contentFormGroup.appendChild(textarea);
    panel.appendChild(contentFormGroup);

    const tagFormGroup = document.createElement('div');
    tagFormGroup.className = 'mb-4';

    const tagLabel = document.createElement('label');
    tagLabel.textContent = 'תג HTML:';
    tagLabel.className = 'block text-sm font-medium text-gray-700 mb-1';
    tagLabel.htmlFor = `html-tag-${elementData.id}`;

    const select = document.createElement('select');
    select.id = `html-tag-${elementData.id}`;
    select.className = 'w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent shadow-sm';

    const tags = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span'];
    tags.forEach(tag => {
        const option = document.createElement('option');
        option.value = tag;
        option.textContent = `<${tag}>`;
        select.appendChild(option);
    });

    select.value = config.htmlTag || 'p';

    select.addEventListener('change', () => {
        saveResponsiveSetting(elementData, ['htmlTag'], select.value, updateCallback);
    });

    tagFormGroup.appendChild(tagLabel);
    tagFormGroup.appendChild(select);
    panel.appendChild(tagFormGroup);
}

/**
 * פנימי: ממלא את טאב העיצוב (דוגמה בסיסית)
 * @param {HTMLElement} panel 
 * @param {object} elementData 
 * @param {object} effectiveConfig 
 * @param {function} updateCallback 
 */
function _populateDesignTab(panel, elementData, effectiveConfig, updateCallback) {
    panel.innerHTML = ''; // Clear
    const config = effectiveConfig;
    const styles = config.styles || {};
    const typo = styles.typography || {};

    const { accordionItem: typographyAccordion, contentDiv: typographyContent } = createSettingsGroup('טיפוגרפיה', true);

    // Font Color
    const colorContainer = document.createElement('div');
    colorContainer.className = 'mb-4';
    colorContainer.appendChild(createColorPicker(styles.color || '#000000',
        (value) => { saveResponsiveSetting(elementData, ['styles', 'color'], value, updateCallback); }
    ));
    typographyContent.appendChild(colorContainer);
    
    // Font Size (Using Tailwind classes for example)
    const fontSizeContainer = document.createElement('div');
    fontSizeContainer.className = 'mb-4';
    fontSizeContainer.appendChild(createSelect([
        { value: 'text-xs', label: 'Extra Small' },
        { value: 'text-sm', label: 'Small' },
        { value: 'text-base', label: 'Normal' },
        { value: 'text-lg', label: 'Large' },
        { value: 'text-xl', label: 'XL' },
        { value: 'text-2xl', label: '2XL' },
        { value: 'text-3xl', label: '3XL' },
        { value: 'text-4xl', label: '4XL' },
    ], typo.fontSize || 'text-base',
        (value) => { saveResponsiveSetting(elementData, ['styles', 'typography', 'fontSize'], value, updateCallback); }
    ));
    typographyContent.appendChild(fontSizeContainer);
    
     // Text Align
    const textAlignContainer = document.createElement('div');
    textAlignContainer.className = 'mb-4';
    textAlignContainer.appendChild(createButtonGroup([
        { value: 'text-left', icon: '<i class="ri-align-left"></i>', title: 'Left' },
        { value: 'text-center', icon: '<i class="ri-align-center"></i>', title: 'Center' },
        { value: 'text-right', icon: '<i class="ri-align-right"></i>', title: 'Right' },
        { value: 'text-justify', icon: '<i class="ri-align-justify"></i>', title: 'Justify' }
    ], typo.textAlign || 'text-right',
        (value) => { saveResponsiveSetting(elementData, ['styles', 'typography', 'textAlign'], value, updateCallback); }
    ));
    typographyContent.appendChild(textAlignContainer);

    // Add more typography controls here (font family, weight, decoration, etc.)

    panel.appendChild(typographyAccordion);
}

/**
 * פנימי: ממלא את טאב מתקדם
 * @param {HTMLElement} panel
 * @param {object} elementData
 * @param {object} effectiveConfig
 * @param {function} updateCallback
 */
// In text.js, update the _populateAdvancedTab function to fix the CSS ID and Classes section:

function _populateAdvancedTab(panel, elementData, effectiveConfig, updateCallback) {
    panel.innerHTML = ''; // Clear
    const config = effectiveConfig;
    const styles = config.styles || {};
    const visibility = config.visibility || { desktop: true, tablet: true, mobile: true };

   // --- Margin --- (Responsive)
   const { accordionItem: marginAccordion, contentDiv: marginContent } = createSettingsGroup('Margin (שוליים חיצוניים)');
   const currentMargin = styles.margin || { top: '0', right: '0', bottom: '0', left: '0', unit: 'px' };
   
   marginContent.appendChild(
       createLinkedInputs(
           'שוליים', // labelText
           elementData, // elementData
           ['styles', 'margin'], // baseSettingPath
           ['px', '%', 'em', 'rem'], // unitOptions as array
           currentMargin.unit || 'px', // defaultUnit
           updateCallback // updateCallback
       )
   );
   panel.appendChild(marginAccordion);

   // --- Padding --- (Responsive)
   const { accordionItem: paddingAccordion, contentDiv: paddingContent } = createSettingsGroup('Padding (ריפוד פנימי)');
   const currentPadding = styles.padding || { top: '0', right: '0', bottom: '0', left: '0', unit: 'px' };
   
   paddingContent.appendChild(
       createLinkedInputs(
           'ריפוד', // labelText
           elementData, // elementData
           ['styles', 'padding'], // baseSettingPath
           ['px', '%', 'em', 'rem'], // unitOptions as array
           currentPadding.unit || 'px', // defaultUnit
           updateCallback // updateCallback
       )
   );
   panel.appendChild(paddingAccordion);

   // --- Visibility Section --- (Responsive)
   const { accordionItem: visibilityAccordion, contentDiv: visibilityContent } = createSettingsGroup('נראות (Visibility)');
   const currentVisibility = config.visibility || { desktop: true, tablet: true, mobile: true };
   visibilityContent.appendChild(
       createVisibilityControls(currentVisibility, (newVisibility) => {
           const currentBreakpoint = getCurrentBreakpoint();
           saveResponsiveSetting(elementData, ['visibility', currentBreakpoint], newVisibility[currentBreakpoint], currentBreakpoint, updateCallback);
       })
   );
   panel.appendChild(visibilityAccordion);

   // --- Custom Identifiers Section (Not responsive) ---
   const { accordionItem: idClassAccordion, contentDiv: idClassContent } = createSettingsGroup('מזהים וקלאסים');
   
   // --- FIX: CSS ID Input ---
   const idLabel = document.createElement('label');
   idLabel.textContent = 'CSS ID';
   idLabel.className = 'block text-sm font-medium text-gray-700 mb-1';
   
   // Create a manual input container instead of using createTextInput
   const idInputContainer = document.createElement('div');
   idInputContainer.className = 'mb-3';
   
   const idInput = document.createElement('input');
   idInput.type = 'text';
   idInput.className = 'w-full h-9 px-3 text-sm rounded-lg focus:outline-none bg-gray-50 text-gray-700';
   idInput.placeholder = 'my-unique-id';
   idInput.value = config.cssId || '';
   
   // Add input event listener
   idInput.addEventListener('input', (e) => {
       // Sanitize ID value (only allow letters, numbers, hyphens, underscores)
       const validValue = e.target.value.replace(/[^a-zA-Z0-9_-]/g, '');
       e.target.value = validValue;
       
       // Save to element data
       elementData.config.cssId = validValue;
       
       // Apply ID directly to element to see immediate change
       const widgetElement = document.querySelector(`[data-widget-id="${elementData.id}"]`);
       if (widgetElement) {
           if (validValue) {
               widgetElement.id = validValue;
           } else {
               widgetElement.removeAttribute('id');
           }
       }
       
       // Call update callback to save state
       if (typeof updateCallback === 'function') {
           updateCallback();
       }
   });
   
   idInputContainer.appendChild(idLabel);
   idInputContainer.appendChild(idInput);
   idClassContent.appendChild(idInputContainer);

   // --- FIX: CSS Classes Input ---
   const classLabel = document.createElement('label');
   classLabel.textContent = 'CSS Classes';
   classLabel.className = 'block text-sm font-medium text-gray-700 mt-3 mb-1';
   
   // Create a manual input container
   const classInputContainer = document.createElement('div');
   classInputContainer.className = 'mb-3';
   
   const classInput = document.createElement('input');
   classInput.type = 'text';
   classInput.className = 'w-full h-9 px-3 text-sm rounded-lg focus:outline-none bg-gray-50 text-gray-700';
   classInput.placeholder = 'class1 class2 class3';
   classInput.value = config.cssClasses || '';
   
   // Track previous classes to remove them when changing
   let previousClasses = config.cssClasses ? config.cssClasses.split(' ').filter(c => c.trim()) : [];
   
   // Add input event listener
   classInput.addEventListener('input', (e) => {
       // Sanitize class value (only allow letters, numbers, hyphens, underscores, spaces)
       const validValue = e.target.value.replace(/[^a-zA-Z0-9_\-\s]/g, '');
       e.target.value = validValue;
       
       // Save to element data
       elementData.config.cssClasses = validValue;
       
       // Apply classes directly to element to see immediate change
       const widgetElement = document.querySelector(`[data-widget-id="${elementData.id}"]`);
       if (widgetElement) {
           // Remove previous classes
           if (previousClasses.length > 0) {
               widgetElement.classList.remove(...previousClasses);
           }
           
           // Add new classes
           const newClasses = validValue.split(' ').filter(c => c.trim());
           if (newClasses.length > 0) {
               widgetElement.classList.add(...newClasses);
               previousClasses = newClasses;
           } else {
               previousClasses = [];
           }
       }
       
       // Call update callback to save state
       if (typeof updateCallback === 'function') {
           updateCallback();
       }
   });
   
   classInputContainer.appendChild(classLabel);
   classInputContainer.appendChild(classInput);
   idClassContent.appendChild(classInputContainer);

   panel.appendChild(idClassAccordion);
}

/**
 * יוצר את אובייקט הטאבים לפאנל ההגדרות של הווידג'ט.
 * @param {HTMLElement} panel - אלמנט ה-DOM הכללי של הטאבים (לא בשימוש ישיר כאן)
 * @param {object} elementData - נתוני הווידג'ט המקוריים
 * @param {object} effectiveConfig - הקונפיג האפקטיבי לנקודת השבירה הנוכחית
 * @param {function} updateCallback - הקולבק לעדכון
 * @returns {object} אובייקט עם פונקציות לכל טאב ('content', 'design', 'advanced')
 */
export function createSettingsTabs(panel, elementData, effectiveConfig, updateCallback) {
    return {
        content: () => { _populateContentTab(panel, elementData, effectiveConfig, updateCallback); },
        design: () => { _populateDesignTab(panel, elementData, effectiveConfig, updateCallback); }, 
        advanced: () => { _populateAdvancedTab(panel, elementData, effectiveConfig, updateCallback); } 
    };
}

/**
 * מרנדר את תוכן הווידג'ט עצמו (ללא ה-wrapper).
 * @param {object} widgetData נתוני הווידג'ט
 * @param {object} effectiveConfig הקונפיג האפקטיבי לנקודת השבירה הנוכחית
 * @returns {HTMLElement} אלמנט ה-DOM של תוכן הווידג'ט
 */
export function render(widgetData, effectiveConfig) { 
    const config = effectiveConfig;
    const typo = config.styles?.typography || {};
    
    // בדיקה אם האלמנט כבר קיים
    const existingElement = document.querySelector(`[data-widget-id="${widgetData.id}"] .widget-content`);
    if (existingElement) {
        // עדכון כל המאפיינים של האלמנט הקיים
        existingElement.textContent = config.content || '';
        
        // עדכון תג ה-HTML אם השתנה
        if (existingElement.tagName.toLowerCase() !== (config.htmlTag || 'p')) {
            const newElement = document.createElement(config.htmlTag || 'p');
            newElement.className = existingElement.className;
            newElement.textContent = existingElement.textContent;
            existingElement.parentNode.replaceChild(newElement, existingElement);
            return newElement;
        }
        
        // עדכון קלאסים של טיפוגרפיה
        existingElement.className = 'widget-content'; // קלאס בסיס
        const typographyClasses = [
            typo.fontSize,
            typo.textAlign,
        ].filter(Boolean);
        existingElement.classList.add(...typographyClasses);
        
        // עדכון קלאסים של ה-wrapper
        const wrapperClasses = [
            'widget-wrapper',
            'text-widget',
            ...typographyClasses
        ].filter(Boolean);
        existingElement.dataset.widgetClasses = wrapperClasses.join(' ');
        
        return existingElement;
    }
    
    const tagName = config.htmlTag || 'p';
    const widgetContentElement = document.createElement(tagName);
    
    // החלת קלאסים של טיפוגרפיה
    widgetContentElement.className = 'widget-content'; // קלאס בסיס
    const typographyClasses = [
        typo.fontSize,
        typo.textAlign,
    ].filter(Boolean);
    widgetContentElement.classList.add(...typographyClasses);

    widgetContentElement.textContent = config.content || '';
    
    // הוספת קלאסים גם ל-wrapper
    const wrapperClasses = [
        'widget-wrapper',
        'text-widget',
        ...typographyClasses
    ].filter(Boolean);
    
    widgetContentElement.dataset.widgetClasses = wrapperClasses.join(' ');
    
    return widgetContentElement;
}

/**
 * מחיל סגנונות inline על ה-wrapper של הווידג'ט.
 * @param {HTMLElement} widgetWrapper אלמנט ה-wrapper של הווידג'ט
 * @param {object} effectiveConfig הקונפיג האפקטיבי
 */
/**
 * מחיל סגנונות inline על ה-wrapper של הווידג'ט.
 * @param {HTMLElement} widgetWrapper אלמנט ה-wrapper של הווידג'ט
 * @param {object} effectiveConfig הקונפיג האפקטיבי
 */
export function applyStyles(widgetWrapper, effectiveConfig) {
    if (!widgetWrapper || !effectiveConfig) {
        console.warn('applyStyles: Missing widgetWrapper or effectiveConfig in text.js');
        return;
    }

    const styles = effectiveConfig.styles || {};
    const typo = styles.typography || {};
    const margin = styles.margin || { top: '0', right: '0', bottom: '0', left: '0', unit: 'px' };
    const padding = styles.padding || { top: '0', right: '0', bottom: '0', left: '0', unit: 'px' };

    // החלת קלאסים מה-dataset
    const widgetContent = widgetWrapper.querySelector('.widget-content');
    if (widgetContent && widgetContent.dataset.widgetClasses) {
        widgetWrapper.className = widgetContent.dataset.widgetClasses;
    }

    // Always ensure widget-wrapper class is present
    if (!widgetWrapper.classList.contains('widget-wrapper')) {
        widgetWrapper.classList.add('widget-wrapper');
    }

    // Apply text content from config
    if (widgetContent && effectiveConfig.content !== undefined) {
        widgetContent.textContent = effectiveConfig.content;
    }

    // החלת סגנונות inline עיקריים שלא דרך קלאסים
    widgetWrapper.style.color = styles.color || '';

    // Improved margin handling - make sure to append proper units
    const marginUnit = margin.unit || 'px';
    widgetWrapper.style.marginTop = margin.top ? `${margin.top}${marginUnit}` : '0';
    widgetWrapper.style.marginRight = margin.right ? `${margin.right}${marginUnit}` : '0';
    widgetWrapper.style.marginBottom = margin.bottom ? `${margin.bottom}${marginUnit}` : '0';
    widgetWrapper.style.marginLeft = margin.left ? `${margin.left}${marginUnit}` : '0';

    // Improved padding handling - make sure to append proper units
    const paddingUnit = padding.unit || 'px';
    widgetWrapper.style.paddingTop = padding.top ? `${padding.top}${paddingUnit}` : '0';
    widgetWrapper.style.paddingRight = padding.right ? `${padding.right}${paddingUnit}` : '0';
    widgetWrapper.style.paddingBottom = padding.bottom ? `${padding.bottom}${paddingUnit}` : '0';
    widgetWrapper.style.paddingLeft = padding.left ? `${padding.left}${paddingUnit}` : '0';

    // Background color if specified
    if (styles.backgroundColor) {
        widgetWrapper.style.backgroundColor = styles.backgroundColor;
    }

    // החלת נראות (visibility)
    const currentBreakpoint = getCurrentBreakpoint(); // Now this will work with the import
    const visibility = effectiveConfig.visibility || { desktop: true, tablet: true, mobile: true };
    
    if (visibility[currentBreakpoint] === false) {
        widgetWrapper.style.display = 'none';
    } else {
        widgetWrapper.style.display = '';
    }
    
    // Add visibility classes - alternative approach
    widgetWrapper.classList.remove('hidden-desktop', 'hidden-tablet', 'hidden-mobile');
    if (visibility.desktop === false) widgetWrapper.classList.add('hidden-desktop');
    if (visibility.tablet === false) widgetWrapper.classList.add('hidden-tablet');
    if (visibility.mobile === false) widgetWrapper.classList.add('hidden-mobile');
    
    // עדכון הסגנונות של האלמנט עצמו (widget-content)
    if (widgetContent) {
        widgetContent.style.color = styles.color || '';
        
        // Apply typography settings to content element
        if (typo.fontSize) {
            // Remove existing font size classes first
            widgetContent.classList.remove('text-xs', 'text-sm', 'text-base', 'text-lg', 'text-xl', 'text-2xl', 'text-3xl', 'text-4xl');
            widgetContent.classList.add(typo.fontSize);
        }
        
        if (typo.textAlign) {
            // Remove existing alignment classes first
            widgetContent.classList.remove('text-left', 'text-center', 'text-right', 'text-justify');
            widgetContent.classList.add(typo.textAlign);
        }
    }
    
    // Force a relayout/repaint
    void widgetWrapper.offsetHeight; // This triggers a reflow
}