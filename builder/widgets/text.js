// Text Widget Module

// --- הוספה: ייבוא פונקציות רספונסיביות ושמירה ---
import { saveResponsiveSetting } from '../render-responsive.js';
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
function _populateAdvancedTab(panel, elementData, effectiveConfig, updateCallback) {
     panel.innerHTML = ''; // Clear
     const config = effectiveConfig;
     const styles = config.styles || {};
     const visibility = config.visibility || { desktop: true, tablet: true, mobile: true };

    // Labels for spacing inputs
    const spacingLabels = [
        { label: 'למעלה', key: 'top' },
        { label: 'ימין', key: 'right' },
        { label: 'למטה', key: 'bottom' },
        { label: 'שמאל', key: 'left' },
    ];

    // --- Margin --- (Responsive)
    const { accordionItem: marginAccordion, contentDiv: marginContent } = createSettingsGroup('Margin (שוליים חיצוניים)');
    const currentMargin = styles.margin || { top: '0', right: '0', bottom: '0', left: '0', unit: 'px' };
    marginContent.appendChild(
        createLinkedInputs(spacingLabels, currentMargin, currentMargin.unit || 'px', true, () => {
             saveResponsiveSetting(elementData, ['styles', 'margin'], currentMargin, updateCallback);
        })
    );
    panel.appendChild(marginAccordion);

    // --- Padding --- (Responsive)
    const { accordionItem: paddingAccordion, contentDiv: paddingContent } = createSettingsGroup('Padding (ריפוד פנימי)');
    const currentPadding = styles.padding || { top: '0', right: '0', bottom: '0', left: '0', unit: 'px' };
    paddingContent.appendChild(
        createLinkedInputs(spacingLabels, currentPadding, currentPadding.unit || 'px', true, () => {
            saveResponsiveSetting(elementData, ['styles', 'padding'], currentPadding, updateCallback);
        })
    );
    panel.appendChild(paddingAccordion);

    // --- Visibility Section --- (Responsive)
    const { accordionItem: visibilityAccordion, contentDiv: visibilityContent } = createSettingsGroup('נראות (Visibility)');
    const currentVisibility = config.visibility || { desktop: true, tablet: true, mobile: true };
    visibilityContent.appendChild(
        createVisibilityControls(currentVisibility, (newVisibility) => {
            const currentBreakpoint = window.currentBreakpoint || 'desktop';
            saveResponsiveSetting(elementData, ['visibility', currentBreakpoint], newVisibility[currentBreakpoint], updateCallback);
        })
    );
    panel.appendChild(visibilityAccordion);

    // --- Custom Identifiers Section (Not responsive) ---
    const { accordionItem: idClassAccordion, contentDiv: idClassContent } = createSettingsGroup('מזהים וקלאסים');
    const idLabel = document.createElement('label');
    idLabel.textContent = 'CSS ID';
    idLabel.className = 'block text-sm font-medium text-gray-700 mb-1';
    const idInput = createTextInput(elementData.config.cssId || '', (value) => {
        elementData.config.cssId = value;
        updateCallback(false);
    });
    idClassContent.appendChild(idLabel);
    idClassContent.appendChild(idInput);

    const classLabel = document.createElement('label');
    classLabel.textContent = 'CSS Classes';
    classLabel.className = 'block text-sm font-medium text-gray-700 mt-3 mb-1';
    const classInput = createTextInput(elementData.config.cssClasses || '', (value) => {
        elementData.config.cssClasses = value;
        updateCallback(false);
    });
    idClassContent.appendChild(classLabel);
    idClassContent.appendChild(classInput);

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
    
    const tagName = config.htmlTag || 'p';
    const widgetContentElement = document.createElement(tagName);
    
    // החלת קלאסים של טיפוגרפיה
    widgetContentElement.className = 'widget-content'; // קלאס בסיס
    const typographyClasses = [
        typo.fontSize,
        typo.textAlign,
        // להוסיף כאן קלאסים נוספים מ-Tailwind אם רוצים (למשקל, עיטור וכו')
    ].filter(Boolean); // סינון ערכים ריקים
    widgetContentElement.classList.add(...typographyClasses);

    widgetContentElement.textContent = config.content || '';
    
    return widgetContentElement;
}

/**
 * מחיל סגנונות inline על ה-wrapper של הווידג'ט.
 * @param {HTMLElement} widgetWrapper אלמנט ה-wrapper של הווידג'ט
 * @param {object} effectiveConfig הקונפיג האפקטיבי
 */
export function applyStyles(widgetWrapper, effectiveConfig) {
     const styles = effectiveConfig.styles || {};
     const typo = styles.typography || {};
     const margin = styles.margin || { top: '0', right: '0', bottom: '0', left: '0', unit: 'px' };
     const padding = styles.padding || { top: '0', right: '0', bottom: '0', left: '0', unit: 'px' };

    // החלת סגנונות inline עיקריים שלא דרך קלאסים
    widgetWrapper.style.color = styles.color || null;

    // הוספה: החלת מרווחים
    const marginUnit = margin.unit || 'px';
    const paddingUnit = padding.unit || 'px';
    widgetWrapper.style.marginTop = margin.top ? `${margin.top}${marginUnit}` : null;
    widgetWrapper.style.marginRight = margin.right ? `${margin.right}${marginUnit}` : null;
    widgetWrapper.style.marginBottom = margin.bottom ? `${margin.bottom}${marginUnit}` : null;
    widgetWrapper.style.marginLeft = margin.left ? `${margin.left}${marginUnit}` : null;

    widgetWrapper.style.paddingTop = padding.top ? `${padding.top}${paddingUnit}` : null;
    widgetWrapper.style.paddingRight = padding.right ? `${padding.right}${paddingUnit}` : null;
    widgetWrapper.style.paddingBottom = padding.bottom ? `${padding.bottom}${paddingUnit}` : null;
    widgetWrapper.style.paddingLeft = padding.left ? `${padding.left}${paddingUnit}` : null;

    // החלת נראות (visibility)
    const visibility = effectiveConfig.visibility || { desktop: true, tablet: true, mobile: true };
    const currentBreakpoint = window.currentBreakpoint || 'desktop';
    if (visibility[currentBreakpoint]) {
        widgetWrapper.style.display = '';
    } else {
        widgetWrapper.style.display = 'none';
    }

    // אולי נוסיף כאן עוד סגנונות inline אם נצטרך
    // widgetWrapper.style.fontFamily = typo.fontFamily || null;
    // widgetWrapper.style.fontWeight = typo.fontWeight || null;
}

// הסרת ייצוא ישיר של populateContentTab
// export { getWidgetName, getDefaultConfig, populateContentTab }; 