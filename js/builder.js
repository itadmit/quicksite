document.addEventListener('alpine:init', () => {
    Alpine.data('builder', () => ({
        elements: [],
        history: [],
        currentHistoryIndex: -1,
        draggedElement: null,
        hasUnsavedChanges: false,

        init() {
            try {
                this.elements = window.initialContent ? JSON.parse(JSON.stringify(window.initialContent)) : [];
                this.saveToHistory();
                this.initInteract();
            } catch (error) {
                console.error('Error initializing builder:', error);
            }
        },

        initInteract() {
            // הגדרת אלמנטים שניתנים לגרירה
            interact('.draggable-element')
                .draggable({
                    inertia: true,
                    modifiers: [
                        interact.modifiers.restrictRect({
                            restriction: 'parent',
                            endOnly: true
                        })
                    ],
                    autoScroll: true,
                    listeners: {
                        start: (event) => {
                            this.dragStart(event);
                        },
                        move: (event) => {
                            this.dragMove(event);
                        },
                        end: (event) => {
                            this.dragEnd(event);
                        }
                    }
                });

            // הגדרת אזורי שחרור
            interact('.drop-zone')
                .dropzone({
                    accept: '.draggable-element',
                    overlap: 0.5,
                    listeners: {
                        dropactivate: (event) => {
                            event.target.classList.add('drop-active');
                        },
                        dragenter: (event) => {
                            event.target.classList.add('drop-target');
                        },
                        dragleave: (event) => {
                            event.target.classList.remove('drop-target');
                        },
                        drop: (event) => {
                            this.dropElement(event);
                        },
                        dropdeactivate: (event) => {
                            event.target.classList.remove('drop-active', 'drop-target');
                        }
                    }
                });
        },

        saveToHistory() {
            if (this.currentHistoryIndex < this.history.length - 1) {
                this.history = this.history.slice(0, this.currentHistoryIndex + 1);
            }
            this.history.push(JSON.parse(JSON.stringify(this.elements)));
            this.currentHistoryIndex = this.history.length - 1;
            this.hasUnsavedChanges = true;
        },

        undo() {
            if (this.currentHistoryIndex > 0) {
                this.currentHistoryIndex--;
                this.elements = JSON.parse(JSON.stringify(this.history[this.currentHistoryIndex]));
                this.hasUnsavedChanges = true;
            }
        },

        redo() {
            if (this.currentHistoryIndex < this.history.length - 1) {
                this.currentHistoryIndex++;
                this.elements = JSON.parse(JSON.stringify(this.history[this.currentHistoryIndex]));
                this.hasUnsavedChanges = true;
            }
        },

        createElement(type) {
            const element = {
                type,
                settings: {},
                content: {}
            };

            switch (type) {
                case 'text':
                    element.content = { text: 'טקסט חדש' };
                    element.settings = {
                        margin: 0,
                        padding: 0,
                        width: 'auto',
                        widthUnit: '',
                        height: 'auto',
                        heightUnit: '',
                        backgroundColor: '',
                        borderWidth: 0,
                        borderStyle: 'solid',
                        borderColor: '',
                        borderRadius: 0
                    };
                    break;
                case 'button':
                    element.content = { text: 'כפתור' };
                    element.settings = {
                        margin: 0,
                        padding: 8,
                        width: 'auto',
                        widthUnit: '',
                        height: 'auto',
                        heightUnit: '',
                        backgroundColor: '#4F46E5',
                        borderWidth: 0,
                        borderStyle: 'solid',
                        borderColor: '',
                        borderRadius: 4
                    };
                    break;
                case 'image':
                    element.content = { src: '', alt: '' };
                    element.settings = {
                        margin: 0,
                        padding: 0,
                        width: '100%',
                        widthUnit: '',
                        height: 'auto',
                        heightUnit: '',
                        backgroundColor: '',
                        borderWidth: 0,
                        borderStyle: 'solid',
                        borderColor: '',
                        borderRadius: 0
                    };
                    break;
                case 'video':
                    element.content = { src: '', title: '' };
                    element.settings = {
                        margin: 0,
                        padding: 0,
                        width: '100%',
                        widthUnit: '',
                        height: 'auto',
                        heightUnit: '',
                        backgroundColor: '',
                        borderWidth: 0,
                        borderStyle: 'solid',
                        borderColor: '',
                        borderRadius: 0
                    };
                    break;
                case 'form':
                    element.content = { fields: [] };
                    element.settings = {
                        margin: 0,
                        padding: 16,
                        width: '100%',
                        widthUnit: '',
                        height: 'auto',
                        heightUnit: '',
                        backgroundColor: '#F3F4F6',
                        borderWidth: 1,
                        borderStyle: 'solid',
                        borderColor: '#E5E7EB',
                        borderRadius: 8
                    };
                    break;
            }

            return element;
        },

        renderElementContent(element) {
            if (!element) return '';
            
            const settings = element.settings || {};
            const content = element.content || {};
            let html = '';

            switch (element.type) {
                case 'text':
                    html = content.text || '';
                    break;
                case 'button':
                    html = `<button class="text-white px-4 py-2 rounded">${content.text || ''}</button>`;
                    break;
                case 'image':
                    html = `<img src="${content.src || ''}" alt="${content.alt || ''}" class="w-full h-auto">`;
                    break;
                case 'video':
                    html = `<video src="${content.src || ''}" controls class="w-full h-auto">${content.title || ''}</video>`;
                    break;
                case 'form':
                    html = `<form class="space-y-4">
                        ${(content.fields || []).map(field => `
                            <div class="space-y-2">
                                <label class="block text-sm font-medium">${field.label || ''}</label>
                                <input type="${field.type || 'text'}" class="w-full px-3 py-2 border rounded">
                            </div>
                        `).join('')}
                    </form>`;
                    break;
                case 'row':
                    html = `<div class="grid gap-${settings.gap || 4} p-${settings.padding || 4}">
                        ${(element.columns || []).map(column => `
                            <div class="col-span-${column.width || 12} p-${column.padding || 4}">
                                ${(column.elements || []).map((el, i) => this.renderElement(el, i)).join('')}
                            </div>
                        `).join('')}
                    </div>`;
                    break;
            }

            return `<div class="w-full">${html}</div>`;
        },

        renderElement(element, index) {
            if (!element) return '';
            
            const settings = element.settings || {};
            const style = `
                margin: ${settings.margin || 0}px;
                padding: ${settings.padding || 0}px;
                width: ${settings.width || 'auto'}${settings.widthUnit || ''};
                height: ${settings.height || 'auto'}${settings.heightUnit || ''};
                background-color: ${settings.backgroundColor || ''};
                border: ${settings.borderWidth || 0}px ${settings.borderStyle || 'solid'} ${settings.borderColor || ''};
                border-radius: ${settings.borderRadius || 0}px;
            `;
            
            return `
                <div class="element relative group draggable-element" 
                     style="${style}"
                     data-type="${element.type}"
                     data-content='${JSON.stringify(element.content)}'
                     data-settings='${JSON.stringify(element.settings)}'>
                    ${this.renderElementContent(element)}
                    <div class="absolute top-0 right-0 p-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click="removeElement(${index})" class="p-1 text-red-500 hover:text-red-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>`;
        },

        dragStart(event) {
            const element = event.target;
            this.draggedElement = {
                type: element.dataset.type,
                content: element.dataset.content ? JSON.parse(element.dataset.content) : {},
                settings: element.dataset.settings ? JSON.parse(element.dataset.settings) : {}
            };
            element.classList.add('dragging');
        },

        dragMove(event) {
            const element = event.target;
            const x = (parseFloat(element.getAttribute('data-x')) || 0) + event.dx;
            const y = (parseFloat(element.getAttribute('data-y')) || 0) + event.dy;
            
            element.style.transform = `translate(${x}px, ${y}px)`;
            element.setAttribute('data-x', x);
            element.setAttribute('data-y', y);
        },

        dragEnd(event) {
            const element = event.target;
            element.classList.remove('dragging');
            this.draggedElement = null;
        },

        dropElement(event) {
            if (this.draggedElement) {
                const dropZone = event.target;
                const position = dropZone.dataset.position;
                const rowIndex = parseInt(dropZone.dataset.rowIndex);
                const columnIndex = parseInt(dropZone.dataset.columnIndex);
                
                const newElement = JSON.parse(JSON.stringify(this.draggedElement));
                
                if (position === 'column' && !isNaN(rowIndex) && !isNaN(columnIndex)) {
                    if (!this.elements[rowIndex].columns[columnIndex].elements) {
                        this.elements[rowIndex].columns[columnIndex].elements = [];
                    }
                    this.elements[rowIndex].columns[columnIndex].elements.push(newElement);
                } else if (position === 'before' && !isNaN(rowIndex)) {
                    const newRow = {
                        type: 'row',
                        settings: {
                            gap: 4,
                            padding: 4
                        },
                        columns: [{
                            width: 12,
                            padding: 4,
                            elements: [newElement]
                        }]
                    };
                    this.elements.splice(rowIndex, 0, newRow);
                } else {
                    const newRow = {
                        type: 'row',
                        settings: {
                            gap: 4,
                            padding: 4
                        },
                        columns: [{
                            width: 12,
                            padding: 4,
                            elements: [newElement]
                        }]
                    };
                    this.elements.push(newRow);
                }
                
                this.saveToHistory();
            }
        },

        removeElement(index) {
            this.elements.splice(index, 1);
            this.saveToHistory();
        },

        addRow() {
            const newRow = {
                type: 'row',
                settings: {
                    gap: 4,
                    padding: 4
                },
                columns: [{
                    width: 12,
                    padding: 4,
                    elements: []
                }]
            };
            this.elements.push(newRow);
            this.saveToHistory();
        }
    }));
}); 