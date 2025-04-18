/**
 * Video Editor Widget
 * 
 * A lightweight editor for video items in the customizer
 */

class VideoEditor {
    constructor(options) {
        this.data = options.data || {
            title: '',
            description: '',
            url: '',
            embed_code: '',
            thumbnail: ''
        };
        
        this.onSave = options.onSave || function() {};
        this.onCancel = options.onCancel || function() {};
        
        this.element = this.createEditor();
        this.setupEventListeners();
    }
    
    createEditor() {
        const editor = document.createElement('div');
        editor.className = 'widget-editor video-editor';
        
        editor.innerHTML = `
            <div class="editor-header">
                <h3>עריכת סרטון</h3>
                <button type="button" class="close-editor">&times;</button>
            </div>
            
            <div class="editor-body">
                <div class="space-y-5">
                    <!-- Video Title -->
                    <div class="form-group">
                        <label for="video-title" class="block mb-2 text-sm font-medium text-gray-700">כותרת הסרטון</label>
                        <input type="text" id="video-title" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary" value="${this.data.title || ''}">
                    </div>
                    
                    <!-- Video Description -->
                    <div class="form-group">
                        <label for="video-description" class="block mb-2 text-sm font-medium text-gray-700">תיאור הסרטון</label>
                        <textarea id="video-description" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary" rows="3">${this.data.description || ''}</textarea>
                    </div>
                    
                    <!-- Video Source Selector -->
                    <div class="form-group">
                        <label class="block mb-2 text-sm font-medium text-gray-700">סוג הסרטון</label>
                        <div class="flex border border-gray-300 rounded-md overflow-hidden">
                            <button type="button" class="source-selector flex-1 py-2 px-3 text-center ${!this.data.embed_code ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700'}" data-source="url">כתובת URL</button>
                            <button type="button" class="source-selector flex-1 py-2 px-3 text-center ${this.data.embed_code ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700'}" data-source="embed">קוד הטמעה</button>
                        </div>
                    </div>
                    
                    <!-- Video URL Field (Default) -->
                    <div class="form-group video-url-group ${this.data.embed_code ? 'hidden' : ''}">
                        <label for="video-url" class="block mb-2 text-sm font-medium text-gray-700">כתובת URL של הסרטון</label>
                        <input type="text" id="video-url" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary" placeholder="https://www.youtube.com/watch?v=..." value="${this.data.url || ''}">
                        <p class="help-text text-xs text-gray-500 mt-1">תומך ב-YouTube, Vimeo ועוד</p>
                    </div>
                    
                    <!-- Video Embed Code Field (Hidden by default) -->
                    <div class="form-group video-embed-group ${!this.data.embed_code ? 'hidden' : ''}">
                        <label for="video-embed" class="block mb-2 text-sm font-medium text-gray-700">קוד הטמעה</label>
                        <textarea id="video-embed" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary" rows="4" placeholder="<iframe src=...">${this.data.embed_code || ''}</textarea>
                        <p class="help-text text-xs text-gray-500 mt-1">הדבק את קוד ההטמעה מ-YouTube, Vimeo או כל שירות וידאו אחר</p>
                    </div>
                    
                    <!-- Video Thumbnail -->
                    <div class="form-group">
                        <label class="block mb-2 text-sm font-medium text-gray-700">תמונה ממוזערת</label>
                        <div class="image-selector">
                            <div class="preview-image" style="height: 150px;">
                                <img src="${this.data.thumbnail || '/customizer/assets/video-placeholder.jpg'}" alt="Preview" id="video-thumbnail-preview" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div class="image-actions flex flex-col gap-2 mt-3">
                                <input type="text" id="video-thumbnail" class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary" placeholder="כתובת URL של התמונה הממוזערת" value="${this.data.thumbnail || ''}">
                                <button type="button" class="select-image-btn flex items-center justify-center">
                                    <i class="ri-folder-image-line mr-1"></i> בחר תמונה מהמדיה
                                </button>
                                <p class="help-text text-xs text-gray-500">מומלץ להשתמש בתמונה ביחס 16:9</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="editor-footer">
                <button type="button" class="cancel-btn px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">ביטול</button>
                <button type="button" class="save-btn px-4 py-2 rounded-md text-white bg-primary hover:bg-primary-dark transition">שמור</button>
            </div>
        `;
        
        return editor;
    }
    
    setupEventListeners() {
        // Close button
        const closeBtn = this.element.querySelector('.close-editor');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                this.onCancel();
            });
        }
        
        // Cancel button
        const cancelBtn = this.element.querySelector('.cancel-btn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                this.onCancel();
            });
        }
        
        // Save button
        const saveBtn = this.element.querySelector('.save-btn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => {
                const titleInput = this.element.querySelector('#video-title');
                const descriptionInput = this.element.querySelector('#video-description');
                const urlInput = this.element.querySelector('#video-url');
                const embedInput = this.element.querySelector('#video-embed');
                const thumbnailInput = this.element.querySelector('#video-thumbnail');
                
                const urlSourceActive = !this.element.querySelector('.video-url-group').classList.contains('hidden');
                
                const updatedData = {
                    title: titleInput.value,
                    description: descriptionInput.value,
                    url: urlSourceActive ? urlInput.value : '',
                    embed_code: !urlSourceActive ? embedInput.value : '',
                    thumbnail: thumbnailInput.value
                };
                
                this.onSave(updatedData);
            });
        }
        
        // Source selector buttons
        const sourceSelectors = this.element.querySelectorAll('.source-selector');
        sourceSelectors.forEach(selector => {
            selector.addEventListener('click', () => {
                const source = selector.getAttribute('data-source');
                
                // Update UI for source selectors
                sourceSelectors.forEach(btn => {
                    if (btn.getAttribute('data-source') === source) {
                        btn.classList.add('bg-primary', 'text-white');
                        btn.classList.remove('bg-gray-100', 'text-gray-700');
                    } else {
                        btn.classList.remove('bg-primary', 'text-white');
                        btn.classList.add('bg-gray-100', 'text-gray-700');
                    }
                });
                
                // Show/hide appropriate fields
                if (source === 'url') {
                    this.element.querySelector('.video-url-group').classList.remove('hidden');
                    this.element.querySelector('.video-embed-group').classList.add('hidden');
                } else {
                    this.element.querySelector('.video-url-group').classList.add('hidden');
                    this.element.querySelector('.video-embed-group').classList.remove('hidden');
                }
            });
        });
        
        // Image URL input
        const thumbnailInput = this.element.querySelector('#video-thumbnail');
        const thumbnailPreview = this.element.querySelector('#video-thumbnail-preview');
        
        if (thumbnailInput && thumbnailPreview) {
            thumbnailInput.addEventListener('input', () => {
                if (!thumbnailInput.value) {
                    thumbnailPreview.src = '/customizer/assets/video-placeholder.jpg';
                } else {
                    thumbnailPreview.src = thumbnailInput.value;
                }
            });
        }
        
        // Handle image preview errors
        if (thumbnailPreview) {
            thumbnailPreview.onerror = function() {
                this.src = '/customizer/assets/video-placeholder.jpg';
            };
        }
    }
} 