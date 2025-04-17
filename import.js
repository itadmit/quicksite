// import.js
// מכיל לוגיקה לשמירה וטעינה של מצב הבילדר מ/אל JSON

console.log('Import/Export module loaded');

/**
 * ממיר את מצב הבילדר הנוכחי למחרוזת JSON.
 * @param {Array} pageState - מערך השורות והאלמנטים הנוכחי.
 * @returns {string} - מחרוזת JSON המייצגת את המצב.
 */
export function stateToJson(pageState) {
    try {
        // אולי נרצה להוסיף כאן עיבוד נוסף לפני השמירה בעתיד
        return JSON.stringify(pageState, null, 2); // Formatting for readability
    } catch (error) {
        console.error("Error converting state to JSON:", error);
        return null;
    }
}

/**
 * טוען מצב בילדר ממחרוזת JSON.
 * @param {string} jsonString - מחרוזת ה-JSON לטעינה.
 * @returns {Array|null} - מערך המצב החדש או null במקרה של שגיאה.
 */
export function jsonToState(jsonString) {
    try {
        const parsedState = JSON.parse(jsonString);
        // כאן נוכל להוסיף ולידציות כדי לוודא שהמבנה תקין
        if (!Array.isArray(parsedState)) {
            throw new Error("Invalid template format: root is not an array.");
        }
        // אפשר להוסיף בדיקות נוספות למבנה הפנימי
        return parsedState;
    } catch (error) {
        console.error("Error parsing JSON to state:", error);
        alert(`שגיאה בטעינת התבנית: ${error.message}`); // הודעה למשתמש
        return null;
    }
}

/**
 * מפעיל הורדה של מחרוזת כקובץ למחשב המשתמש.
 * @param {string} content - תוכן הקובץ.
 * @param {string} filename - שם הקובץ להורדה.
 * @param {string} contentType - סוג התוכן (e.g., 'application/json').
 */
export function downloadFile(content, filename, contentType) {
    const blob = new Blob([content], { type: contentType });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link); // Required for Firefox
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
} 