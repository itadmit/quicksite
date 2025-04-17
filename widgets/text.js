import {
    createSettingsGroup,
    createSelect,
    createTextInput,
    createButtonGroup,
    createSlider,
    createColorPicker,
    createLinkedInputs,
    createCheckbox,
    createNumberInput,
    rgbaToHex
} from '../common-settings.js';

import { saveResponsiveSetting, getEffectiveConfig, getNestedValue, getCurrentBreakpoint } from '../render-responsive.js';

function _populateDesignTab(panel, widgetData, effectiveConfig, updateCallback) {
    panel.innerHTML = '';
    console.log("Populating Text Design Tab for:", widgetData.id);

    const config = effectiveConfig;
    const styles = config.styles || {};
    const typo = styles.typography || {};

    const { accordionItem: typoAccordion, contentDiv: typoContent } = createSettingsGroup('טיפוגרפיה', true);

    const fontOptions = [
        { value: "'Noto Sans Hebrew', sans-serif", label: 'Noto Sans Hebrew' },
    ];
    typoContent.appendChild(createSelect(fontOptions, typo.fontFamily || fontOptions[0].value, (value) => {
        saveResponsiveSetting(widgetData, ['styles', 'typography', 'fontFamily'], value, getCurrentBreakpoint(), updateCallback);
    }));

    const weightSizeRow = document.createElement('div');
    weightSizeRow.className = 'grid grid-cols-2 gap-2 mt-3';
    const weightOptions = [
        { value: '300', label: 'דק (300)' }, { value: '400', label: 'רגיל (400)' }, { value: '500', label: 'בינוני (500)' },
        { value: '600', label: 'מודגש למחצה (600)' }, { value: '700', label: 'מודגש (700)' }, { value: '800', label: 'מודגש מאד (800)' }
    ];
    weightSizeRow.appendChild(createSelect(weightOptions, typo.fontWeight || '400', (value) => {
        saveResponsiveSetting(widgetData, ['styles', 'typography', 'fontWeight'], value, getCurrentBreakpoint(), updateCallback);
    }));
    const fontSizes = [
        { value: 'text-xs', label: 'XS' }, { value: 'text-sm', label: 'S' }, { value: 'text-base', label: 'M' },
        { value: 'text-lg', label: 'L' }, { value: 'text-xl', label: 'XL' }, { value: 'text-2xl', label: '2XL' },
        { value: 'text-3xl', label: '3XL' }, { value: 'text-4xl', label: '4XL' },
    ];
    weightSizeRow.appendChild(createSelect(fontSizes, config.fontSize || 'text-base', (value) => {
         saveResponsiveSetting(widgetData, ['fontSize'], value, getCurrentBreakpoint(), updateCallback);
    }));
    typoContent.appendChild(weightSizeRow);

    const heightSpacingRow = document.createElement('div');
    heightSpacingRow.className = 'grid grid-cols-2 gap-2 mt-3';
    heightSpacingRow.appendChild(createTextInput(typo.lineHeight || '1.5', '1.5', null, (value) => {
        saveResponsiveSetting(widgetData, ['styles', 'typography', 'lineHeight'], value || '1.5', getCurrentBreakpoint(), updateCallback);
    }));
    heightSpacingRow.appendChild(createTextInput(typo.letterSpacing || '0px', '0px', 'px', (value) => {
        saveResponsiveSetting(widgetData, ['styles', 'typography', 'letterSpacing'], value || '0px', getCurrentBreakpoint(), updateCallback);
    }));
    typoContent.appendChild(heightSpacingRow);

    const alignButtons = [
        { value: 'text-right', icon: '<i class="ri-align-right"></i>', title: 'ימין' },
        { value: 'text-center', icon: '<i class="ri-align-center"></i>', title: 'מרכז' },
        { value: 'text-left', icon: '<i class="ri-align-left"></i>', title: 'שמאל' }
    ];
    const alignGroup = createButtonGroup(alignButtons, config.textAlign || 'text-right', (value) => {
        saveResponsiveSetting(widgetData, ['textAlign'], value, getCurrentBreakpoint(), updateCallback);
    });
    const alignContainer = document.createElement('div');
    alignContainer.className = 'mt-3';
    const alignLabel = document.createElement('label');
    alignLabel.className = 'block text-sm text-gray-600 mb-1';
    alignLabel.textContent = 'יישור';
    alignContainer.appendChild(alignLabel);
    alignContainer.appendChild(alignGroup);
    typoContent.appendChild(alignContainer);

    panel.appendChild(typoAccordion);

    const { accordionItem: colorAccordion, contentDiv: colorContent } = createSettingsGroup('צבע טקסט');
    colorContent.appendChild(createColorPicker(styles.color || '#000000', (value) => {
        saveResponsiveSetting(widgetData, ['styles', 'color'], value, getCurrentBreakpoint(), updateCallback);
    }));
    panel.appendChild(colorAccordion);

    // TODO: Add text shadow controls?
} 