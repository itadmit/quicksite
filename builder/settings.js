// builder/settings.js
// Handles the settings panel logic (tabs, loading content)

// Import necessary functions from other modules if needed
import { renderPageContent } from './render.js';
import { findElementData, findRowContainingElement } from './utils.js';
import { getCurrentBreakpoint, getEffectiveConfig } from './render-responsive.js';
import { populateRowContentTab, populateRowDesignTab, populateRowAdvancedTab } from './row-settings.js';
import { populateColumnContentTab, populateColumnDesignTab, populateColumnAdvancedTab } from './column-settings.js';
import * as TextWidget from './widgets/text.js'; // לייבוא הגדרות של ווידג'ט טקסט

console.log('Settings module loaded');

// --- שינוי: הסרנו את settingsPanelContainer --- 
// let settingsPanelContainer = null; 
let settingsTabsContainer = null;
let settingsPanelElement = null; // This will now be the main container
let settingsTabsElements = null;
let selectedElementForPanel = null; // Keep track of the element whose settings are loaded
let settingsPlaceholderElement = null; // To show/hide the placeholder
let tabContentAreaElement = null; // To show/hide the content area
let pageState = null; // הוספת משתנה לשמירת מצב העמוד

export function initSettingsPanel(currentPageState) {
    // שמירת מצב העמוד מהפרמטר
    pageState = currentPageState;
    
    let foundAll = true;
    // --- שינוי: settingsPanelElement הוא הקונטיינר הראשי --- 
    settingsPanelElement = document.getElementById('settings-panel'); 
    // -------------------------------------------------------
    settingsTabsContainer = document.getElementById('settings-tabs-container');
    settingsPlaceholderElement = document.getElementById('settings-panel-placeholder');
    tabContentAreaElement = document.getElementById('tab-content-area');
    
    // --- שינוי: הבדיקה מתבססת על settingsPanelElement --- 
    if (!settingsPanelElement) {
        console.error('Settings panel ERROR: Main container with ID "settings-panel" not found!');
        foundAll = false;
    } else {
        // Find other elements relative to the main panel if found
        if (!settingsTabsContainer) {
            console.error('Settings panel ERROR: Tabs container with ID "settings-tabs-container" not found inside #settings-panel!');
            foundAll = false;
        }
        if (!settingsPlaceholderElement) {
            console.error('Settings panel ERROR: Placeholder element with ID "settings-panel-placeholder" not found inside #settings-panel!');
            foundAll = false;
        }
        if (!tabContentAreaElement) {
            console.error('Settings panel ERROR: Tab content area with ID "tab-content-area" not found inside #settings-panel!');
            foundAll = false;
        }
    }
    // ------------------------------------------------------
    
    // Get tab buttons only if container exists
    settingsTabsElements = settingsTabsContainer ? settingsTabsContainer.querySelectorAll('#settings-tabs button') : [];
    if (settingsTabsContainer && settingsTabsElements.length === 0) {
        console.warn('Settings panel WARNING: Found "settings-tabs-container", but no button elements inside #settings-tabs.');
        // This might not be critical if tabs are generated dynamically later, but good to know.
    }

    if (!foundAll) {
        console.error('Settings panel initialization failed due to missing elements.');
        return; // Stop initialization if essential elements are missing
    }

    // Continue with initialization only if all essential elements were found
    console.log('Settings panel elements found. Initializing tabs...');
    settingsTabsElements.forEach(tab => {
        tab.addEventListener('click', handleSettingsTabClick);
    });
    
    // Initially reset/hide the panel
    resetSettingsPanel(); 
}

function handleSettingsTabClick(event) {
    const button = event.target.closest('button');
    if (!button || button.dataset.active === 'true') return;

    const tabName = button.dataset.tab;
    console.log('Settings tab clicked:', tabName);

    // Update active state visually
    settingsTabsElements.forEach(btn => {
        btn.dataset.active = (btn === button) ? 'true' : 'false';
    });

    // Load content for the selected tab (if an element is selected)
    if (selectedElementForPanel) {
        loadSettingsForElement(selectedElementForPanel.id, tabName);
    } else {
        console.warn("Tab clicked but no element selected for settings.");
         // Ensure all content panels are hidden
         // --- שינוי: שימוש ב-tabContentAreaElement --- 
        const panelContentDivs = tabContentAreaElement ? tabContentAreaElement.querySelectorAll('.tab-panel') : [];
        panelContentDivs.forEach(div => div.style.display = 'none');
        // -----------------------------------------
    }
}

/**
 * עדכון מצב העמוד - לשימוש בעת שינוי מצב העמוד
 * @param {Array} newPageState מצב העמוד המעודכן
 */
export function updatePageState(newPageState) {
    pageState = newPageState;
    console.log('Settings module: Page state updated');
}

/**
 * Load settings for a specific element into the settings panel
 * @param {string} elementId 
 * @param {string} [tabToLoad='content'] - The tab to activate and load.
 * @param {Function} updateCallback - The callback function from core.js.
 * @param {Array} pageState - The current page state array
 */
export function loadSettingsForElement(elementId, tabToLoad = 'content', updateCallback, pageState = []) {
    console.log(`Attempting to load settings for element ${elementId}, tab: ${tabToLoad}`);
    
    const elementData = findElementData(pageState, elementId);
    selectedElementForPanel = elementData; // Update the selected element state for this module
    
    if (!elementData) {
        console.error(`Element data not found for ID: ${elementId}`);
        resetSettingsPanel();
        return;
    }
    
    // Ensure panel is visible
    if(settingsTabsContainer) {
        settingsTabsContainer.classList.remove('hidden');
        // Re-query the tabs elements to ensure we have the latest
        settingsTabsElements = settingsTabsContainer.querySelectorAll('#settings-tabs button');
    }
    if(tabContentAreaElement) tabContentAreaElement.classList.remove('hidden');
    if(settingsPlaceholderElement) settingsPlaceholderElement.classList.add('hidden');

    // Get effective config for the current breakpoint
    const currentBreakpoint = getCurrentBreakpoint();
    const effectiveConfig = getEffectiveConfig(elementData, currentBreakpoint);

    // Get tab elements (children of tabContentAreaElement)
    const contentTabPanel = document.getElementById('tab-content-content');
    const designTabPanel = document.getElementById('tab-content-design');
    const advancedTabPanel = document.getElementById('tab-content-advanced');
    
    if(!contentTabPanel || !designTabPanel || !advancedTabPanel) {
        console.error("Settings tab content panels not found inside #tab-content-area!");
        return;
    }

    // Hide all panels first
    contentTabPanel.style.display = 'none'; contentTabPanel.innerHTML = '';
    designTabPanel.style.display = 'none'; designTabPanel.innerHTML = '';
    advancedTabPanel.style.display = 'none'; advancedTabPanel.innerHTML = '';

    // Activate the correct tab button visually
    if (settingsTabsElements) {
        settingsTabsElements.forEach(btn => {
            btn.dataset.active = (btn.dataset.tab === tabToLoad) ? 'true' : 'false';
        });
    }

    // Select the panel to show
    let activeTabPanel = null;
    switch (tabToLoad) {
        case 'content': activeTabPanel = contentTabPanel; break;
        case 'design': activeTabPanel = designTabPanel; break;
        case 'advanced': activeTabPanel = advancedTabPanel; break;
        default: activeTabPanel = contentTabPanel;
    }

    // Populate the active panel
    activeTabPanel.style.display = 'block';
    
    if (typeof updateCallback !== 'function') {
         console.error('Crucial update callback from core.js was not passed correctly to loadSettingsForElement!');
         updateCallback = () => console.error("Settings panel cannot trigger updates!");
    }
    populatePanelContent(activeTabPanel, elementData, effectiveConfig, tabToLoad, updateCallback, pageState);
}

/**
 * Resets the settings panel to its initial state (no element selected).
 */
export function resetSettingsPanel() {
    console.log("Resetting settings panel (settings.js).");
    selectedElementForPanel = null;
    
    // --- שינוי: הסרת settingsPanelContainer, הצגת פלייסהולדר והסתרת תוכן ---
    // if(settingsPanelContainer) settingsPanelContainer.classList.add('hidden');
    if(settingsTabsContainer) settingsTabsContainer.classList.add('hidden');
    if(tabContentAreaElement) tabContentAreaElement.classList.add('hidden');
    if(settingsPlaceholderElement) settingsPlaceholderElement.classList.remove('hidden');
    // ---------------------------------------------------------------------
    
    // Clear content panels
    const contentTabPanel = document.getElementById('tab-content-content');
    const designTabPanel = document.getElementById('tab-content-design');
    const advancedTabPanel = document.getElementById('tab-content-advanced');
    if (contentTabPanel) { contentTabPanel.innerHTML = ''; contentTabPanel.style.display = 'none'; }
    if (designTabPanel) { designTabPanel.innerHTML = ''; designTabPanel.style.display = 'none'; }
    if (advancedTabPanel) { advancedTabPanel.innerHTML = ''; advancedTabPanel.style.display = 'none'; }

    // Deactivate tab buttons visually
    // --- שינוי: בדיקה אם settingsTabsElements קיים --- 
    if (settingsTabsElements) {
        settingsTabsElements.forEach(btn => btn.dataset.active = 'false');
    }
    // --------------------------------------------
}


/**
 * Helper function to populate the content of the active tab panel.
 * @param {HTMLElement} panel 
 * @param {object} elementData 
 * @param {object} effectiveConfig 
 * @param {string} tabName 
 * @param {Function} updateCallbackFromLoad - The correctly passed callback.
 * @param {Array} pageState - מצב הדף הנוכחי
 */
function populatePanelContent(panel, elementData, effectiveConfig, tabName, updateCallbackFromLoad, pageState) {
    // --- שינוי: שימוש בקולבק שהועבר --- 
    const updateCallback = updateCallbackFromLoad;
    // -------------------------------------
    
    // זיהוי סוג האלמנט
    let elementType;
    if (elementData.type === 'text') {
        elementType = 'widget';
    } else if (elementData.columns) {
        elementType = 'row';
    } else if (elementData.widgets !== undefined) {
        elementType = 'column';
    } else {
        elementType = 'unknown';
    }
    
    console.log(`Populating ${tabName} panel for ${elementType} element ${elementData.id}`);

    try {
        switch (elementType) {
            case 'row':
                // קריאה לפונקציות המיובאות מקובץ row-settings.js
                if (tabName === 'content') populateRowContentTab(panel, elementData, effectiveConfig, updateCallback, pageState);
                else if (tabName === 'design') populateRowDesignTab(panel, elementData, effectiveConfig, updateCallback, pageState);
                else if (tabName === 'advanced') populateRowAdvancedTab(panel, elementData, effectiveConfig, updateCallback, pageState);
                else panel.innerHTML = `<p>No settings for tab: ${tabName}</p>`;
                break;
                
            case 'column':
                // קריאה לפונקציות המיובאות מקובץ column-settings.js
                if (!pageState) {
                    console.error('pageState is not defined in populatePanelContent');
                    return;
                }
                const parentRowData = findRowContainingElement(pageState, elementData.id);
                if (tabName === 'content') populateColumnContentTab(panel, elementData, effectiveConfig, updateCallback, parentRowData, pageState);
                else if (tabName === 'design') populateColumnDesignTab(panel, elementData, effectiveConfig, updateCallback, pageState);
                else if (tabName === 'advanced') populateColumnAdvancedTab(panel, elementData, effectiveConfig, updateCallback, pageState);
                else panel.innerHTML = `<p>No settings for tab: ${tabName}</p>`;
                break;
                
            case 'widget':
                // טיפול בווידג'טים בהתאם לסוג
                if (elementData.type === 'text') {
                    const widgetTabs = TextWidget.createSettingsTabs(panel, elementData, effectiveConfig, updateCallback, pageState);
                    if (widgetTabs && widgetTabs[tabName]) {
                        widgetTabs[tabName]();
                    } else {
                        panel.innerHTML = `<p>Widget 'text' does not have a '${tabName}' tab.</p>`;
                    }
                } else {
                    panel.innerHTML = `<p>Settings not available for widget type: ${elementData.type || 'unknown'}</p>`;
                }
                break;
                
            default:
                panel.innerHTML = `<p>Unknown element type: ${elementType}</p>`;
        }
    } catch (error) {
        console.error(`Error populating settings panel for ${elementType}:`, error);
        panel.innerHTML = `<p>Error loading settings: ${error.message}</p>
                         <pre class="text-red-500 text-xs mt-2">${error.stack}</pre>`;
    }
}

// --- הסרת Placeholders Globals --- 