/**
 * Testimonial Editor Widget
 * 
 * A lightweight editor for testimonial items in the customizer
 */

class TestimonialEditor {
    constructor(options) {
        this.data = options.data || {
            author_name: '',
            author_role: '',
            quote: '',
            author_image: ''
        };
        
        this.onSave = options.onSave || function() {};
        this.onCancel = options.onCancel || function() {};
        
        this.element = this.createEditor();
        this.setupEventListeners();
    }
    
    createEditor() {
        const editor = document.createElement('div');
        editor.className = 'widget-editor testimonial-editor';
        
        editor.innerHTML = `
            <div class="editor-header">
                <h3>עריכת המלצה</h3>
                <button type="button" class="close-editor">&times;</button>
            </div>
            
            <div class="editor-body">
                <div class="space-y-5">
                    <!-- Author Name -->
                    <div class="form-group">
                        <label for="testimonial-author" class="block mb-2 text-sm font-medium text-gray-700">שם הממליץ</label>
                        <input type="text" id="testimonial-author" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary" value="${this.data.author_name || ''}">
                    </div>
                    
                    <!-- Author Role -->
                    <div class="form-group">
                        <label for="testimonial-role" class="block mb-2 text-sm font-medium text-gray-700">תפקיד</label>
                        <input type="text" id="testimonial-role" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary" value="${this.data.author_role || ''}">
                    </div>
                    
                    <!-- Quote -->
                    <div class="form-group">
                        <label for="testimonial-quote" class="block mb-2 text-sm font-medium text-gray-700">תוכן ההמלצה</label>
                        <textarea id="testimonial-quote" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary" rows="4">${this.data.quote || ''}</textarea>
                    </div>
                    
                    <!-- Author Image -->
                    <div class="form-group">
                        <label class="block mb-2 text-sm font-medium text-gray-700">תמונת הממליץ</label>
                        <div class="image-selector">
                            <div class="preview-image">
                                <img src="${this.data.author_image || '/customizer/assets/default-avatar.jpg'}" alt="Preview" id="testimonial-image-preview">
                            </div>
                            <div class="image-actions flex flex-col gap-2">
                                <input type="text" id="testimonial-image" class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary" placeholder="כתובת URL של התמונה" value="${this.data.author_image || ''}">
                                <button type="button" class="select-image-btn flex items-center justify-center">
                                    <i class="ri-folder-image-line mr-1"></i> בחר תמונה מהמדיה
                                </button>
                                <p class="help-text text-xs text-gray-500">מומלץ להשתמש בתמונה בגודל 200x200 פיקסלים</p>
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
                const authorInput = this.element.querySelector('#testimonial-author');
                const roleInput = this.element.querySelector('#testimonial-role');
                const quoteInput = this.element.querySelector('#testimonial-quote');
                const imageInput = this.element.querySelector('#testimonial-image');
                
                const updatedData = {
                    author_name: authorInput.value,
                    author_role: roleInput.value,
                    quote: quoteInput.value,
                    author_image: imageInput.value
                };
                
                this.onSave(updatedData);
            });
        }
        
        // Image URL input
        const imageInput = this.element.querySelector('#testimonial-image');
        const imagePreview = this.element.querySelector('#testimonial-image-preview');
        
        if (imageInput && imagePreview) {
            imageInput.addEventListener('input', () => {
                if (imageInput.value) {
                    imagePreview.src = imageInput.value;
                } else {
                    imagePreview.src = '/customizer/assets/default-avatar.jpg';
                }
            });
        }
        
        // Handle image preview errors
        if (imagePreview) {
            imagePreview.onerror = function() {
                this.src = '/customizer/assets/default-avatar.jpg';
            };
        }
    }
} 