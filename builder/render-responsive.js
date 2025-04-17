// builder/render-responsive.js
// מכיל לוגיקה לטיפול בהגדרות רספונסיביות וחישוב הגדרות אפקטיביות

console.log('Render Responsive module loaded');

/**
 * מחשב את אובייקט הקונפיגורציה האפקטיבי עבור אלמנט בנקודת שבירה נתונה.
 * משלב את הגדרות הבסיס עם דריסות רספונסיביות.
 * סדר הדריסה: mobile > tablet > desktop.
 *
 * @param {object} elementData נתוני האלמנט (למשל, columnData, rowData, widgetData)
 * @param {string} breakpoint נקודת השבירה הנוכחית ('desktop', 'tablet', 'mobile')
 * @returns {object} אובייקט הקונפיגורציה האפקטיבי המלא
 */
export function getEffectiveConfig(elementData, breakpoint) {
    if (!elementData || !elementData.config) {
        console.warn('getEffectiveConfig: Missing elementData or config');
        return {};
    }

    const baseConfig = deepClone(elementData.config); 
    const overrides = elementData.config.responsiveOverrides || {};

    delete baseConfig.responsiveOverrides;
    
    let mergedConfig = deepClone(baseConfig); // התחל מהבסיס

    // --- שינוי: החל את הדריסות בסדר הנכון --- 
    // 1. החל דריסת Desktop תמיד (כדי ש-Tablet/Mobile יוכלו לרשת ממנה)
    if (overrides.desktop) {
        mergedConfig = deepMerge(mergedConfig, deepClone(overrides.desktop));
    }
    
    // 2. החל דריסת Tablet אם אנחנו ב-Tablet או Mobile
    if (breakpoint === 'tablet' || breakpoint === 'mobile') {
        if (overrides.tablet) {
            mergedConfig = deepMerge(mergedConfig, deepClone(overrides.tablet));
        }
    }

    // 3. החל דריסת Mobile אם אנחנו ב-Mobile
    if (breakpoint === 'mobile') {
        if (overrides.mobile) {
            mergedConfig = deepMerge(mergedConfig, deepClone(overrides.mobile));
        }
    }
    // --- סוף שינוי ---

    // console.log(`getEffectiveConfig for ${elementData.id} at ${breakpoint}:`, mergedConfig);
    return mergedConfig;
}

/**
 * פונקציית עזר לקבלת נקודת השבירה הנוכחית מה-DOM.
 * @returns {string} 'desktop', 'tablet', or 'mobile'
 */
export function getCurrentBreakpoint() {
    const activeControl = document.querySelector('#responsive-controls .responsive-button[data-active="true"]');
    return activeControl ? activeControl.dataset.view : 'desktop'; // ברירת מחדל לדסקטופ
}

// --- הוספה: פונקציה לבדיקת קיום דריסות להגדרה ספציפית --- 

/**
 * בודק באילו נקודות שבירה קיימת דריסה (override) עבור נתיב הגדרה ספציפי.
 * @param {object} elementData נתוני האלמנט המקוריים.
 * @param {string[]} settingPath הנתיב להגדרה (e.g., ['styles', 'padding']).
 * @returns {object} אובייקט עם סטטוס הדריסה לכל נקודת שבירה, e.g., { desktop: true, tablet: false, mobile: true }.
 */
export function getSettingOverrideStatus(elementData, settingPath) {
    const status = { desktop: false, tablet: false, mobile: false };
    if (!elementData?.config?.responsiveOverrides) {
        return status; // אין דריסות כלל
    }

    const overrides = elementData.config.responsiveOverrides;

    // בדוק אם הנתיב קיים תחת כל נקודת שבירה
    if (getNestedValue(overrides, ['desktop', ...settingPath]) !== undefined) {
        status.desktop = true;
    }
    if (getNestedValue(overrides, ['tablet', ...settingPath]) !== undefined) {
        status.tablet = true;
    }
    if (getNestedValue(overrides, ['mobile', ...settingPath]) !== undefined) {
        status.mobile = true;
    }

    return status;
}
// -----------------------------------------------------------

// --- הוספה: פונקציה חדשה לשמירת הגדרות רספונסיביות ---

/**
 * שומר הגדרה תוך התחשבות בנקודת השבירה הנתונה.
 * מעדכן את ה-responsiveOverrides.
 *
 * @param {object} elementData נתוני האלמנט המקוריים מה-state
 * @param {string[]} settingPath מערך המייצג את הנתיב להגדרה
 * @param {*} newValue הערך החדש לשמירה
 * @param {string} breakpoint נקודת השבירה שלגביה לשמור את הערך ('desktop', 'tablet', 'mobile')
 * @param {function} updateCallback הפונקציה מה-core לעדכון ה-state והרינדור
 */
export function saveResponsiveSetting(elementData, settingPath, newValue, breakpoint, updateCallback) {
    if (!elementData || !elementData.config) {
        console.error('saveResponsiveSetting: Missing elementData or config');
        return;
    }

    console.log(`Saving setting [${settingPath.join('.')}] = ${JSON.stringify(newValue)} for explicit breakpoint: ${breakpoint}`);

    // Ensure responsiveOverrides exists
    if (!elementData.config.responsiveOverrides) {
        elementData.config.responsiveOverrides = {};
    }
    if (!elementData.config.responsiveOverrides[breakpoint]) {
        elementData.config.responsiveOverrides[breakpoint] = {};
    }

    let comparisonValue;
    let comparisonSourceBreakpoint;

    if (breakpoint === 'desktop') {
        comparisonValue = getNestedValue(elementData.config, settingPath);
        comparisonSourceBreakpoint = 'base';
    } else {
        comparisonSourceBreakpoint = (breakpoint === 'mobile') ? 'tablet' : 'desktop';
        const parentEffectiveConfig = getEffectiveConfig(elementData, comparisonSourceBreakpoint);
        comparisonValue = getNestedValue(parentEffectiveConfig, settingPath);
    }

    const valuesAreEqual = JSON.stringify(newValue) === JSON.stringify(comparisonValue);
    const overridePath = [breakpoint, ...settingPath];

    if (!valuesAreEqual) {
        setNestedValue(elementData.config.responsiveOverrides, overridePath, newValue);
        console.log(` -> Saved override at ${breakpoint} because value differs from ${comparisonSourceBreakpoint} (${JSON.stringify(comparisonValue)})`);
    } else {
        deleteNestedValue(elementData.config.responsiveOverrides, overridePath);
        console.log(` -> Removed override at ${breakpoint} because value matches ${comparisonSourceBreakpoint} (${JSON.stringify(comparisonValue)})`);
    }

    if (elementData.config.responsiveOverrides[breakpoint] && Object.keys(elementData.config.responsiveOverrides[breakpoint]).length === 0) {
        delete elementData.config.responsiveOverrides[breakpoint];
    }
    if (breakpoint === 'desktop') {
        if (elementData.config.responsiveOverrides.tablet && Object.keys(elementData.config.responsiveOverrides.tablet).length === 0) {
            delete elementData.config.responsiveOverrides.tablet;
        }
        if (elementData.config.responsiveOverrides.mobile && Object.keys(elementData.config.responsiveOverrides.mobile).length === 0) {
            delete elementData.config.responsiveOverrides.mobile;
        }
    }

    updateCallback();
}


// --- פונקציות עזר לגישה/שינוי נתיבים מקוננים --- 

/**
 * קובע ערך בנתיב מקונן באובייקט.
 * יוצר אובייקטים בנתיב אם הם לא קיימים.
 * @param {object} obj האובייקט לעדכון
 * @param {string[]} path המערך של מפתחות הנתיב
 * @param {*} value הערך לקבוע
 */
export function setNestedValue(obj, path, value) {
    let current = obj;
    for (let i = 0; i < path.length - 1; i++) {
        const key = path[i];
        if (current[key] === undefined || typeof current[key] !== 'object' || current[key] === null) {
            current[key] = {};
        }
        current = current[key];
    }
    current[path[path.length - 1]] = value;
}

/**
 * קורא ערך מנתיב מקונן באובייקט.
 * @param {object} obj האובייקט לקריאה
 * @param {string[]} path המערך של מפתחות הנתיב
 * @returns {*} הערך שנמצא או undefined
 */
export function getNestedValue(obj, path) {
    let current = obj;
    for (const key of path) {
        if (current === undefined || current === null || typeof current !== 'object') {
            return undefined;
        }
        current = current[key];
    }
    return current;
}

/**
 * מוחק ערך/מפתח מנתיב מקונן באובייקט.
 * @param {object} obj האובייקט לעדכון
 * @param {string[]} path המערך של מפתחות הנתיב
 * @returns {boolean} True אם המחיקה הצליחה (או שהמפתח לא היה קיים), False אחרת
 */
export function deleteNestedValue(obj, path) {
    let current = obj;
    for (let i = 0; i < path.length - 1; i++) {
        const key = path[i];
        if (current === undefined || current === null || typeof current !== 'object') {
            return true;
        }
        current = current[key];
    }

    if (current && typeof current === 'object' && path.length > 0) {
        const finalKey = path[path.length - 1];
        const deleted = delete current[finalKey];
        return deleted;
    }
    return true;
}

// --- פונקציות עזר למיזוג עמוק ושכפול עמוק ---

/**
 * יוצר שכפול עמוק של אובייקט (באמצעות JSON)
 * @param {object} obj האובייקט לשכפול
 * @returns {object} העתק עמוק של האובייקט
 */
function deepClone(obj) {
    if (typeof obj !== 'object' || obj === null) {
        return obj; // לא אובייקט או null
    }
    try {
        return JSON.parse(JSON.stringify(obj));
    } catch (e) {
        console.error("Deep clone failed:", e);
        return {}; // החזר אובייקט ריק במקרה של שגיאה
    }
}

/**
 * ממזג באופן עמוק שני אובייקטים. משנה את targetObject.
 * @param {object} targetObject אובייקט היעד (ישתנה)
 * @param {object} sourceObject אובייקט המקור
 * @returns {object} אובייקט היעד הממוזג
 */
function deepMerge(targetObject, sourceObject) {
    if (!sourceObject) return targetObject;

    Object.keys(sourceObject).forEach(key => {
        const targetValue = targetObject[key];
        const sourceValue = sourceObject[key];

        if (typeof targetValue === 'object' && targetValue !== null && typeof sourceValue === 'object' && sourceValue !== null) {
            // אם שני הערכים הם אובייקטים, מזג אותם רקורסיבית
            // ודא שאתה לא מנסה למזג מערך עם אובייקט או להיפך
            if (!Array.isArray(targetValue) && !Array.isArray(sourceValue)) {
                 deepMerge(targetValue, sourceValue);
            } else {
                 // אם אחד מהם מערך, או שניהם, החלף עם ערך המקור
                 targetObject[key] = deepClone(sourceValue); 
            }
        } else {
            // אחרת, החלף את ערך היעד בערך המקור (או העתק עמוק שלו)
            targetObject[key] = deepClone(sourceValue);
        }
    });

    return targetObject;
}
