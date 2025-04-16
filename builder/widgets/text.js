// Text Widget Module

console.log('Text Widget module loaded');

function getWidgetName() {
    return 'טקסט';
}

function getDefaultConfig() {
    return {
        content: 'טקסט ברירת מחדל',
        htmlTag: 'p',
        fontSize: 'text-base',
        textAlign: 'text-right',
        styles: {
            color: '#000000',
        }
    };
}

/**
 * Populates the content settings tab for the text widget.
 * @param {HTMLElement} settingsPanel - The container element for the settings.
 * @param {object} widgetData - The data object for the specific widget instance.
 * @param {Function} updateCallback - The function to call when a setting changes.
 */
function populateContentTab(settingsPanel, widgetData, updateCallback) {
    console.log('Populating Content tab for Text widget:', widgetData);
    settingsPanel.innerHTML = ''; // Clear previous

    const contentFormGroup = document.createElement('div');
    contentFormGroup.className = 'mb-4';

    const contentLabel = document.createElement('label');
    contentLabel.textContent = 'תוכן טקסט:';
    contentLabel.className = 'block text-sm font-medium text-gray-700 mb-1';
    contentLabel.htmlFor = `text-content-${widgetData.id}`;

    const textarea = document.createElement('textarea');
    textarea.id = `text-content-${widgetData.id}`;
    textarea.value = widgetData.config.content || '';
    textarea.className = 'w-full h-32 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent shadow-sm';
    textarea.rows = 6;

    textarea.addEventListener('input', () => {
        widgetData.config.content = textarea.value;
        console.log('Text content changed in state:', widgetData.config.content);
        if (typeof updateCallback === 'function') {
             updateCallback();
        } else {
             console.warn('Update callback function not provided to populateContentTab');
        }
    });

    contentFormGroup.appendChild(contentLabel);
    contentFormGroup.appendChild(textarea);
    settingsPanel.appendChild(contentFormGroup);

    const tagFormGroup = document.createElement('div');
    tagFormGroup.className = 'mb-4';

    const tagLabel = document.createElement('label');
    tagLabel.textContent = 'תג HTML:';
    tagLabel.className = 'block text-sm font-medium text-gray-700 mb-1';
    tagLabel.htmlFor = `html-tag-${widgetData.id}`;

    const select = document.createElement('select');
    select.id = `html-tag-${widgetData.id}`;
    select.className = 'w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent shadow-sm';

    const tags = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span'];
    tags.forEach(tag => {
        const option = document.createElement('option');
        option.value = tag;
        option.textContent = `<${tag}>`;
        select.appendChild(option);
    });

    select.value = widgetData.config.htmlTag || 'p';

    select.addEventListener('change', () => {
        widgetData.config.htmlTag = select.value;
        console.log('HTML tag changed in state:', widgetData.config.htmlTag);
        if (typeof updateCallback === 'function') {
             updateCallback();
        } else {
             console.warn('Update callback function not provided to populateContentTab');
        }
    });

    tagFormGroup.appendChild(tagLabel);
    tagFormGroup.appendChild(select);
    settingsPanel.appendChild(tagFormGroup);
}

// Export functions specific to this widget
export { getWidgetName, getDefaultConfig, populateContentTab };

/**
 * מרנדר וידג'ט טקסט
 * @param {Object} widgetData - אובייקט הנתונים של הוידג'ט
 * @returns {HTMLElement} אלמנט ה-DOM שמייצג את הוידג'ט
 */
export function renderWidget(widgetData) {
    const tagName = widgetData.config?.htmlTag || 'p';
    const widgetElement = document.createElement(tagName);
    widgetElement.className = 'widget p-3 min-h-[40px]';
    
    const textContent = widgetData.config?.content || 'טקסט לדוגמה';
    
    widgetElement.textContent = textContent;
    
    return widgetElement;
} 