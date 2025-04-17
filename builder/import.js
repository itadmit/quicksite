// builder/import.js
// פונקציות לייבוא וייצוא של מצב העמוד (תבניות)

console.log('Import/Export module loaded');

/**
 * ממיר מצב עמוד לפורמט JSON
 * @param {Object} state מבנה הנתונים המייצג את מצב העמוד
 * @returns {string} מחרוזת JSON מפורמטת
 */
export function stateToJson(state) {
    try {
        const jsonString = JSON.stringify(state, null, 2);
        console.log('State converted to JSON successfully');
        return jsonString;
    } catch (error) {
        console.error('Error converting state to JSON:', error);
        alert('שגיאה בהמרת נתוני העמוד לפורמט JSON.');
        return null;
    }
}

/**
 * ממיר מחרוזת JSON למבנה נתונים של מצב העמוד
 * @param {string} jsonString מחרוזת JSON המייצגת מצב עמוד
 * @returns {Object|null} מבנה נתונים של העמוד או null במקרה של שגיאה
 */
export function jsonToState(jsonString) {
    try {
        const state = JSON.parse(jsonString);
        
        // בדיקת תקינות בסיסית של המבנה
        if (!Array.isArray(state)) {
            throw new Error('מבנה הנתונים אינו מערך');
        }
        
        // בדיקה בסיסית של כל שורה
        state.forEach((row, index) => {
            if (!row.id) {
                throw new Error(`חסר מזהה בשורה ${index + 1}`);
            }
            if (!Array.isArray(row.columns)) {
                throw new Error(`חסר מערך עמודות בשורה ${index + 1}`);
            }
            
            // בדיקת עמודות
            row.columns.forEach((column, colIndex) => {
                if (!column.id) {
                    throw new Error(`חסר מזהה בעמודה ${colIndex + 1} בשורה ${index + 1}`);
                }
                if (!Array.isArray(column.widgets)) {
                    throw new Error(`חסר מערך ווידג'טים בעמודה ${colIndex + 1} בשורה ${index + 1}`);
                }
                
                // בדיקת ווידג'טים
                column.widgets.forEach((widget, widgetIndex) => {
                    if (!widget.id) {
                        throw new Error(`חסר מזהה בווידג'ט ${widgetIndex + 1} בעמודה ${colIndex + 1} בשורה ${index + 1}`);
                    }
                    if (!widget.type) {
                        throw new Error(`חסר סוג בווידג'ט ${widgetIndex + 1} בעמודה ${colIndex + 1} בשורה ${index + 1}`);
                    }
                });
            });
        });
        
        console.log('JSON parsed to state successfully');
        return state;
    } catch (error) {
        console.error('Error parsing JSON to state:', error);
        alert(`שגיאה בקריאת קובץ התבנית: ${error.message}`);
        return null;
    }
}

/**
 * מוריד קובץ למחשב המשתמש
 * @param {string} content תוכן הקובץ
 * @param {string} fileName שם הקובץ
 * @param {string} contentType סוג MIME של הקובץ
 */
export function downloadFile(content, fileName, contentType) {
    const blob = new Blob([content], { type: contentType });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = fileName;
    document.body.appendChild(a);
    a.click();
    setTimeout(() => {
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }, 100);
}

/**
 * טוען תוכן מקובץ
 * @param {File} file אובייקט File
 * @returns {Promise<string>} הבטחה המחזירה את תוכן הקובץ כמחרוזת
 */
export function readFileContent(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = (event) => resolve(event.target.result);
        reader.onerror = (error) => reject(error);
        reader.readAsText(file);
    });
} 