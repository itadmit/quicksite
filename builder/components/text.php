<?php
/**
 * רכיב טקסט
 */

/**
 * רישום הווידג'ט במערכת
 * @return array מידע על הווידג'ט
 */
function register_text_widget() {
    return [
        'id' => 'text',
        'title' => 'טקסט',
        'icon' => 'fas fa-font',
        'description' => 'רכיב להוספת טקסט',
        'render_callback' => 'render_text_widget',
        'scripts' => [],
        'styles' => []
    ];
}

/**
 * רינדור הטקסט בתצוגה מקדימה בבילדר
 * @param array $settings הגדרות הווידג'ט
 * @return string קוד HTML של הווידג'ט
 */
function render_text_widget($settings) {
    // ערכי ברירת מחדל
    $defaults = [
        'content' => 'טקסט לדוגמה',
        'color' => '#333333',
        'fontSize' => '16px',
        'fontWeight' => 'normal',
        'textAlign' => 'right',
        'lineHeight' => '1.5',
        'margin' => '0',
        'padding' => '0',
        'htmlTag' => 'p'
    ];
    
    // מיזוג הגדרות
    $settings = array_merge($defaults, $settings);
    
    // יצירת מאפייני סגנון
    $style = "color:{$settings['color']};";
    $style .= "font-size:{$settings['fontSize']};";
    $style .= "font-weight:{$settings['fontWeight']};";
    $style .= "text-align:{$settings['textAlign']};";
    $style .= "line-height:{$settings['lineHeight']};";
    $style .= "margin:{$settings['margin']};";
    $style .= "padding:{$settings['padding']};";
    
    // הוספת תמיכה בתגית HTML
    $tag = in_array($settings['htmlTag'], ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div']) 
        ? $settings['htmlTag'] 
        : 'p';
    
    // הוספת קוד HTML
    return "<{$tag} style=\"{$style}\">{$settings['content']}</{$tag}>";
}

/**
 * פונקציות JavaScript עבור הבילדר
 */
?>

<script>
/**
 * רינדור תוכן הווידג'ט בבילדר
 * @param {Object} settings - הגדרות הווידג'ט
 * @returns {string} - HTML של תוכן הווידג'ט
 */
function renderTextWidget(settings) {
    // ערכי ברירת מחדל
    const defaults = {
        content: 'טקסט לדוגמה',
        color: '#333333',
        fontSize: '16px',
        fontWeight: 'normal',
        textAlign: 'right',
        lineHeight: '1.5',
        margin: '0',
        padding: '0',
        htmlTag: 'p'
    };
    
    // מיזוג הגדרות
    const mergedSettings = {...defaults, ...settings};
    
    // יצירת מאפייני סגנון
    const style = `
        color: ${mergedSettings.color};
        font-size: ${mergedSettings.fontSize};
        font-weight: ${mergedSettings.fontWeight};
        text-align: ${mergedSettings.textAlign};
        line-height: ${mergedSettings.lineHeight};
        margin: ${mergedSettings.margin};
        padding: ${mergedSettings.padding};
    `;
    
    // הוספת תמיכה בתגית HTML
    const validTags = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div'];
    const tag = validTags.includes(mergedSettings.htmlTag) ? mergedSettings.htmlTag : 'p';
    
    // החזרת HTML
    return `<${tag} style="${style}">${mergedSettings.content}</${tag}>`;
}

/**
 * הצגת הגדרות ווידג'ט טקסט
 * @param {HTMLElement} container - מכל ההגדרות
 * @param {Object} widgetData - נתוני הווידג'ט
 */
function showTextWidgetSettings(container, widgetData) {
    const settings = widgetData.settings || {};
    
    container.innerHTML = `
        <div class="settings-group">
            <h3 class="settings-title">הגדרות טקסט</h3>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">תוכן</span>
                <textarea id="text-content" class="settings-input h-24">${settings.content || ''}</textarea>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">תגית HTML</span>
                <select id="text-htmlTag" class="settings-select">
                    <option value="p" ${(settings.htmlTag === 'p' || !settings.htmlTag) ? 'selected' : ''}>פסקה (p)</option>
                    <option value="h1" ${settings.htmlTag === 'h1' ? 'selected' : ''}>כותרת 1 (h1)</option>
                    <option value="h2" ${settings.htmlTag === 'h2' ? 'selected' : ''}>כותרת 2 (h2)</option>
                    <option value="h3" ${settings.htmlTag === 'h3' ? 'selected' : ''}>כותרת 3 (h3)</option>
                    <option value="h4" ${settings.htmlTag === 'h4' ? 'selected' : ''}>כותרת 4 (h4)</option>
                    <option value="h5" ${settings.htmlTag === 'h5' ? 'selected' : ''}>כותרת 5 (h5)</option>
                    <option value="h6" ${settings.htmlTag === 'h6' ? 'selected' : ''}>כותרת 6 (h6)</option>
                    <option value="div" ${settings.htmlTag === 'div' ? 'selected' : ''}>מיכל (div)</option>
                </select>
            </label>
        </div>
        
        <div class="settings-group">
            <h3 class="settings-title">עיצוב</h3>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">צבע טקסט</span>
                <div class="flex items-center">
                    <input type="color" id="text-color" class="h-8 w-8 ml-2 border" value="${settings.color || '#333333'}">
                    <input type="text" id="text-color-hex" class="settings-input" value="${settings.color || '#333333'}">
                </div>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">גודל טקסט</span>
                <select id="text-fontSize" class="settings-select">
                    <option value="12px" ${settings.fontSize === '12px' ? 'selected' : ''}>קטן (12px)</option>
                    <option value="16px" ${(settings.fontSize === '16px' || !settings.fontSize) ? 'selected' : ''}>רגיל (16px)</option>
                    <option value="20px" ${settings.fontSize === '20px' ? 'selected' : ''}>גדול (20px)</option>
                    <option value="24px" ${settings.fontSize === '24px' ? 'selected' : ''}>גדול מאוד (24px)</option>
                    <option value="32px" ${settings.fontSize === '32px' ? 'selected' : ''}>ענק (32px)</option>
                </select>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">משקל גופן</span>
                <select id="text-fontWeight" class="settings-select">
                    <option value="normal" ${(settings.fontWeight === 'normal' || !settings.fontWeight) ? 'selected' : ''}>רגיל</option>
                    <option value="bold" ${settings.fontWeight === 'bold' ? 'selected' : ''}>מודגש</option>
                    <option value="lighter" ${settings.fontWeight === 'lighter' ? 'selected' : ''}>דק</option>
                </select>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">יישור טקסט</span>
                <select id="text-textAlign" class="settings-select">
                    <option value="right" ${(settings.textAlign === 'right' || !settings.textAlign) ? 'selected' : ''}>ימין</option>
                    <option value="center" ${settings.textAlign === 'center' ? 'selected' : ''}>מרכז</option>
                    <option value="left" ${settings.textAlign === 'left' ? 'selected' : ''}>שמאל</option>
                    <option value="justify" ${settings.textAlign === 'justify' ? 'selected' : ''}>מיושר</option>
                </select>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">גובה שורה</span>
                <select id="text-lineHeight" class="settings-select">
                    <option value="1" ${settings.lineHeight === '1' ? 'selected' : ''}>צפוף (1)</option>
                    <option value="1.5" ${(settings.lineHeight === '1.5' || !settings.lineHeight) ? 'selected' : ''}>רגיל (1.5)</option>
                    <option value="2" ${settings.lineHeight === '2' ? 'selected' : ''}>מרווח (2)</option>
                </select>
            </label>
        </div>
        
        <div class="settings-group">
            <h3 class="settings-title">ריווח</h3>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">מרווח חיצוני (margin)</span>
                <input type="text" id="text-margin" class="settings-input" placeholder="0px 0px 0px 0px" value="${settings.margin || '0'}">
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">ריפוד פנימי (padding)</span>
                <input type="text" id="text-padding" class="settings-input" placeholder="0px 0px 0px 0px" value="${settings.padding || '0'}">
            </label>
        </div>
        
        <div class="mt-4">
            <button id="update-text-widget" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded text-sm">
                החל שינויים
            </button>
        </div>
    `;
    
    // אירועי שינוי צבע
    document.getElementById('text-color').addEventListener('input', function() {
        document.getElementById('text-color-hex').value = this.value;
    });
    
    document.getElementById('text-color-hex').addEventListener('input', function() {
        document.getElementById('text-color').value = this.value;
    });
    
    // כפתור עדכון
    document.getElementById('update-text-widget').addEventListener('click', function() {
        const newSettings = {
            content: document.getElementById('text-content').value,
            htmlTag: document.getElementById('text-htmlTag').value,
            color: document.getElementById('text-color').value,
            fontSize: document.getElementById('text-fontSize').value,
            fontWeight: document.getElementById('text-fontWeight').value,
            textAlign: document.getElementById('text-textAlign').value,
            lineHeight: document.getElementById('text-lineHeight').value,
            margin: document.getElementById('text-margin').value,
            padding: document.getElementById('text-padding').value
        };
        
        updateWidgetSettings(widgetData.id, newSettings);
    });
}
</script>