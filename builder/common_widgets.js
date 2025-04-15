// builder/common_widgets.js
// פונקציות שירות לרינדור ווידג'טים בצד לקוח

/**
 * רינדור ווידג'ט כפתור
 * @param {Object} settings - הגדרות הווידג'ט
 * @returns {string} - HTML של הווידג'ט
 */
function renderButtonWidget(settings) {
    // ערכי ברירת מחדל
    const defaults = {
        text: 'לחץ כאן',
        color: '#ffffff',
        backgroundColor: '#0ea5e9',
        textAlign: 'center',
        url: '#',
        size: 'medium',
        borderRadius: '4px',
        fullWidth: false,
        margin: '0',
        padding: '10px 20px'
    };
    
    // מיזוג הגדרות
    const mergedSettings = {...defaults, ...settings};
    
    // בחירת גודל
    let sizeClass = 'text-base';
    switch (mergedSettings.size) {
        case 'small':
            sizeClass = 'text-sm';
            break;
        case 'large':
            sizeClass = 'text-lg';
            break;
    }
    
    // יצירת מאפייני סגנון
    const style = `
        color: ${mergedSettings.color};
        background-color: ${mergedSettings.backgroundColor};
        text-align: ${mergedSettings.textAlign};
        border-radius: ${mergedSettings.borderRadius};
        width: ${mergedSettings.fullWidth ? '100%' : 'auto'};
        margin: ${mergedSettings.margin};
        padding: ${mergedSettings.padding};
        display: inline-block;
        text-decoration: none;
        font-weight: 600;
        cursor: pointer;
    `;
    
    // החזרת HTML
    return `<a href="${mergedSettings.url}" class="${sizeClass}" style="${style}">${mergedSettings.text}</a>`;
}

/**
 * הצגת הגדרות ווידג'ט כפתור
 * @param {HTMLElement} container - מכל ההגדרות
 * @param {Object} widgetData - נתוני הווידג'ט
 */
function showButtonWidgetSettings(container, widgetData) {
    const settings = widgetData.settings || {};
    
    container.innerHTML = `
        <div class="settings-group">
            <h3 class="settings-title">הגדרות כפתור</h3>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">טקסט</span>
                <input type="text" id="button-text" class="settings-input" value="${settings.text || 'לחץ כאן'}">
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">כתובת URL</span>
                <input type="text" id="button-url" class="settings-input" value="${settings.url || '#'}">
            </label>
        </div>
        
        <div class="settings-group">
            <h3 class="settings-title">עיצוב</h3>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">צבע טקסט</span>
                <div class="flex items-center">
                    <input type="color" id="button-color" class="h-8 w-8 ml-2 border" value="${settings.color || '#ffffff'}">
                    <input type="text" id="button-color-hex" class="settings-input" value="${settings.color || '#ffffff'}">
                </div>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">צבע רקע</span>
                <div class="flex items-center">
                    <input type="color" id="button-backgroundColor" class="h-8 w-8 ml-2 border" value="${settings.backgroundColor || '#0ea5e9'}">
                    <input type="text" id="button-backgroundColor-hex" class="settings-input" value="${settings.backgroundColor || '#0ea5e9'}">
                </div>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">גודל</span>
                <select id="button-size" class="settings-select">
                    <option value="small" ${settings.size === 'small' ? 'selected' : ''}>קטן</option>
                    <option value="medium" ${(settings.size === 'medium' || !settings.size) ? 'selected' : ''}>בינוני</option>
                    <option value="large" ${settings.size === 'large' ? 'selected' : ''}>גדול</option>
                </select>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">רדיוס פינות</span>
                <input type="text" id="button-borderRadius" class="settings-input" value="${settings.borderRadius || '4px'}">
            </label>
            
            <label class="block mb-2 flex items-center">
                <input type="checkbox" id="button-fullWidth" class="ml-2" ${settings.fullWidth ? 'checked' : ''}>
                <span class="text-sm text-gray-700">רוחב מלא</span>
            </label>
        </div>
        
        <div class="settings-group">
            <h3 class="settings-title">ריווח</h3>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">מרווח חיצוני (margin)</span>
                <input type="text" id="button-margin" class="settings-input" value="${settings.margin || '0'}">
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">ריפוד פנימי (padding)</span>
                <input type="text" id="button-padding" class="settings-input" value="${settings.padding || '10px 20px'}">
            </label>
        </div>
        
        <div class="mt-4">
            <button id="update-button-widget" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded text-sm">
                החל שינויים
            </button>
        </div>
    `;
    
    // אירועי שינוי צבע
    document.getElementById('button-color').addEventListener('input', function() {
        document.getElementById('button-color-hex').value = this.value;
    });
    
    document.getElementById('button-color-hex').addEventListener('input', function() {
        document.getElementById('button-color').value = this.value;
    });
    
    document.getElementById('button-backgroundColor').addEventListener('input', function() {
        document.getElementById('button-backgroundColor-hex').value = this.value;
    });
    
    document.getElementById('button-backgroundColor-hex').addEventListener('input', function() {
        document.getElementById('button-backgroundColor').value = this.value;
    });
    
    // כפתור עדכון
    document.getElementById('update-button-widget').addEventListener('click', function() {
        const newSettings = {
            text: document.getElementById('button-text').value,
            url: document.getElementById('button-url').value,
            color: document.getElementById('button-color').value,
            backgroundColor: document.getElementById('button-backgroundColor').value,
            size: document.getElementById('button-size').value,
            borderRadius: document.getElementById('button-borderRadius').value,
            fullWidth: document.getElementById('button-fullWidth').checked,
            margin: document.getElementById('button-margin').value,
            padding: document.getElementById('button-padding').value
        };
        
        updateWidgetSettings(widgetData.id, newSettings);
    });
}

/**
 * רינדור ווידג'ט תמונה
 * @param {Object} settings - הגדרות הווידג'ט
 * @returns {string} - HTML של הווידג'ט
 */
function renderImageWidget(settings) {
    // ערכי ברירת מחדל
    const defaults = {
        src: '/assets/placeholder.jpg',
        alt: 'תמונה',
        width: '100%',
        height: 'auto',
        alignment: 'center',
        borderRadius: '0',
        margin: '0'
    };
    
    // מיזוג הגדרות
    const mergedSettings = {...defaults, ...settings};
    
    // יצירת מאפייני סגנון למכל
    const containerStyle = `
        text-align: ${mergedSettings.alignment};
        margin: ${mergedSettings.margin};
    `;
    
    // יצירת מאפייני סגנון לתמונה
    const imageStyle = `
        width: ${mergedSettings.width};
        height: ${mergedSettings.height};
        border-radius: ${mergedSettings.borderRadius};
        display: inline-block;
    `;
    
    // החזרת HTML
    return `
        <div style="${containerStyle}">
            <img src="${mergedSettings.src}" alt="${mergedSettings.alt}" style="${imageStyle}">
        </div>
    `;
}

/**
 * הצגת הגדרות ווידג'ט תמונה
 * @param {HTMLElement} container - מכל ההגדרות
 * @param {Object} widgetData - נתוני הווידג'ט
 */
function showImageWidgetSettings(container, widgetData) {
    const settings = widgetData.settings || {};
    
    container.innerHTML = `
        <div class="settings-group">
            <h3 class="settings-title">הגדרות תמונה</h3>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">כתובת התמונה</span>
                <input type="text" id="image-src" class="settings-input" value="${settings.src || '/assets/placeholder.jpg'}">
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">טקסט חלופי</span>
                <input type="text" id="image-alt" class="settings-input" value="${settings.alt || 'תמונה'}">
            </label>
            
            <div class="mt-2 mb-4">
                <button id="upload-image" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm">
                    <i class="fas fa-upload ml-1"></i>העלאת תמונה
                </button>
            </div>
        </div>
        
        <div class="settings-group">
            <h3 class="settings-title">עיצוב</h3>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">רוחב</span>
                <input type="text" id="image-width" class="settings-input" value="${settings.width || '100%'}">
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">גובה</span>
                <input type="text" id="image-height" class="settings-input" value="${settings.height || 'auto'}">
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">יישור</span>
                <select id="image-alignment" class="settings-select">
                    <option value="right" ${settings.alignment === 'right' ? 'selected' : ''}>ימין</option>
                    <option value="center" ${(settings.alignment === 'center' || !settings.alignment) ? 'selected' : ''}>מרכז</option>
                    <option value="left" ${settings.alignment === 'left' ? 'selected' : ''}>שמאל</option>
                </select>
            </label>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">רדיוס פינות</span>
                <input type="text" id="image-borderRadius" class="settings-input" value="${settings.borderRadius || '0'}">
            </label>
        </div>
        
        <div class="settings-group">
            <h3 class="settings-title">ריווח</h3>
            
            <label class="block mb-2">
                <span class="text-sm text-gray-700">מרווח חיצוני (margin)</span>
                <input type="text" id="image-margin" class="settings-input" value="${settings.margin || '0'}">
            </label>
        </div>
        
        <div class="mt-4">
            <button id="update-image-widget" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded text-sm">
                החל שינויים
            </button>
        </div>
    `;
    
    // אירוע העלאת תמונה
    document.getElementById('upload-image').addEventListener('click', function() {
        // פתיחת דיאלוג העלאת תמונה או גלריה
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        
        input.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                // העלאת הקובץ לשרת
                uploadImage(this.files[0], function(response) {
                    if (response.success && response.url) {
                        document.getElementById('image-src').value = response.url;
                    } else {
                        showMessage('שגיאה בהעלאת התמונה: ' + (response.message || 'אירעה שגיאה לא ידועה'), 'error');
                    }
                });
            }
        });
        
        input.click();
    });
    
    // כפתור עדכון
    document.getElementById('update-image-widget').addEventListener('click', function() {
        const newSettings = {
            src: document.getElementById('image-src').value,
            alt: document.getElementById('image-alt').value,
            width: document.getElementById('image-width').value,
            height: document.getElementById('image-height').value,
            alignment: document.getElementById('image-alignment').value,
            borderRadius: document.getElementById('image-borderRadius').value,
            margin: document.getElementById('image-margin').value
        };
        
        updateWidgetSettings(widgetData.id, newSettings);
    });
}

/**
 * פונקציה להעלאת תמונה לשרת
 * @param {File} file - קובץ התמונה
 * @param {Function} callback - פונקצית callback לאחר ההעלאה
 */
function uploadImage(file, callback) {
    const formData = new FormData();
    formData.append('image', file);
    
    fetch('upload_image.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        callback(data);
    })
    .catch(error => {
        console.error('Error uploading image:', error);
        callback({success: false, message: error.message});
    });
}

/**
 * רינדור ווידג'ט וידאו
 * @param {Object} settings - הגדרות הווידג'ט
 * @returns {string} - HTML של הווידג'ט
 */
function renderVideoWidget(settings) {
    // ערכי ברירת מחדל
    const defaults = {
        src: '',
        width: '100%',
        controls: true,
        autoplay: false,
        loop: false,
        muted: false,
        videoType: 'file', // 'file', 'youtube', 'vimeo'
        youtubeId: '',
        vimeoId: ''
    };
    
    // מיזוג הגדרות
    const mergedSettings = {...defaults, ...settings};
    
    // HTML בהתאם לסוג הוידאו
    switch (mergedSettings.videoType) {
        case 'youtube':
            return `
                <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; width: ${mergedSettings.width};">
                    <iframe 
                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" 
                        src="https://www.youtube.com/embed/${mergedSettings.youtubeId}" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </div>
            `;
        
        case 'vimeo':
            return `
                <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; width: ${mergedSettings.width};">
                    <iframe 
                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" 
                        src="https://player.vimeo.com/video/${mergedSettings.vimeoId}" 
                        frameborder="0" 
                        allow="autoplay; fullscreen; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </div>
            `;
        
        case 'file':
        default:
            return `
                <video 
                    src="${mergedSettings.src}" 
                    width="${mergedSettings.width}" 
                    ${mergedSettings.controls ? 'controls' : ''} 
                    ${mergedSettings.autoplay ? 'autoplay' : ''} 
                    ${mergedSettings.loop ? 'loop' : ''} 
                    ${mergedSettings.muted ? 'muted' : ''}>
                    הדפדפן שלך אינו תומך בתג וידאו.
                </video>
            `;
    }
}