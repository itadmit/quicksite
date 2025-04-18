/**
 * Customizer JavaScript
 * Main functionality for the template customizer
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    const sectionsList = document.getElementById('available-sections');
    const previewIframe = document.getElementById('preview-iframe');
    const sectionSettings = document.getElementById('section-settings');
    const saveButton = document.getElementById('save-template');
    const previewButton = document.getElementById('preview-template');
    const scrollToSelectedBtn = document.getElementById('scroll-to-selected');
    const activeSectionName = document.getElementById('active-section-name');
    const settingsSectionName = document.getElementById('settings-section-name');
    const templatePreview = document.getElementById('template-preview');
    
    // Current page data
    let pageData = {
        pageId: getPageId(),
        sections: {},
        activeSectionId: null
    };
    
    // Get page ID from URL
    function getPageId() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('page_id') || 0;
    }
    
    // Load initial template data
    loadTemplateData();
    
    // Event handlers
    if (sectionsList) {
        sectionsList.addEventListener('click', handleSectionSelect);
    }
    
    if (saveButton) {
        saveButton.addEventListener('click', saveTemplate);
    }
    
    if (previewButton) {
        previewButton.addEventListener('click', previewTemplate);
    }
    
    if (scrollToSelectedBtn) {
        scrollToSelectedBtn.addEventListener('click', scrollToSelectedSection);
    }
    
    /**
     * Load template data from the server
     */
    function loadTemplateData() {
        // Fetch data from server
        fetch(`../api/get_template_data.php?page_id=${pageData.pageId}`)
            .then(response => response.json())
            .then(data => {
                pageData.sections = data.sections || {};
                updateSectionList();
            })
            .catch(error => {
                console.error('שגיאה בטעינת התבנית:', error);
                showNotification('שגיאה בטעינת הנתונים. נסה שוב מאוחר יותר.', 'error');
            });
    }
    
    /**
     * Render the template in the preview area
     */
    function renderTemplate() {
        // רענון התצוגה המקדימה ב-iframe
        if (previewIframe && previewIframe.contentWindow) {
            previewIframe.contentWindow.location.reload();
        }
        
        // עדכון רשימת הסקשנים
        updateSectionList();
    }
    
    /**
     * Render a single section based on its type
     */
    function renderSection(sectionId, sectionData) {
        // Get template for this section type
        return `
            <div class="template-section rounded shadow-md overflow-hidden mb-4 border" data-section-id="${sectionId}">
                <div class="section-preview ${sectionId}-section p-4">
                    <h3 class="text-lg font-bold pb-3 mb-3 border-b">${getSectionTitle(sectionId)}</h3>
                    <div class="section-content">
                        ${getSectionPreviewContent(sectionId, sectionData)}
                    </div>
                </div>
                <div class="section-controls bg-light p-3 border-t flex justify-end">
                    <button class="edit-section button bg-primary text-white rounded px-4 py-2" data-section-id="${sectionId}">ערוך</button>
                </div>
            </div>
        `;
    }
    
    /**
     * Get a user-friendly title for each section type
     */
    function getSectionTitle(sectionId) {
        const titles = {
            'header': 'כותרת עליונה',
            'hero': 'באנר ראשי',
            'features': 'תכונות',
            'testimonials': 'המלצות',
            'gallery': 'גלריה',
            'videos': 'סרטונים',
            'contact': 'צור קשר',
            'footer': 'כותרת תחתונה'
        };
        
        return titles[sectionId] || 'סקשן';
    }
    
    /**
     * Get preview content for a section based on its data
     */
    function getSectionPreviewContent(sectionId, sectionData) {
        // Return placeholder content based on section type
        switch(sectionId) {
            case 'header':
                return '<div class="preview-header p-3 bg-light rounded">תצוגה מקדימה של כותרת עליונה</div>';
            case 'hero':
                return '<div class="preview-hero p-3 bg-light rounded">תצוגה מקדימה של באנר ראשי</div>';
            case 'testimonials':
                return renderTestimonialsPreview(sectionData);
            case 'videos':
                return renderVideosPreview(sectionData);
            // Add cases for other section types
            default:
                return `<div class="preview-placeholder p-3 bg-light rounded">תצוגה מקדימה של ${getSectionTitle(sectionId)}</div>`;
        }
    }
    
    /**
     * Render testimonials preview
     */
    function renderTestimonialsPreview(sectionData) {
        const testimonials = sectionData.items || [];
        let html = '<div class="testimonials-preview grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">';
        
        if (testimonials.length > 0) {
            testimonials.forEach((testimonial, index) => {
                const imageSrc = testimonial.image || '../assets/img/placeholder.jpg';
                // Fix image path if needed
                const fixedImageSrc = imageSrc.startsWith('/') ? imageSrc.substring(1) : imageSrc;
                
                html += `
                    <div class="testimonial-item rounded shadow border overflow-hidden">
                        <div class="flex flex-col sm:flex-row">
                            <div class="testimonial-image" style="max-width: 120px; height: 120px;">
                                <img src="${fixedImageSrc}" alt="תמונת ממליץ" class="w-full h-full object-cover">
                            </div>
                            <div class="testimonial-content p-3 flex-1">
                                <p class="quote text-gray-600 italic mb-2">${testimonial.quote || 'תוכן ההמלצה יופיע כאן'}</p>
                                <p class="author font-bold mb-1">${testimonial.author || 'שם הממליץ'}</p>
                                <p class="role text-sm text-gray-600">${testimonial.role || 'תפקיד'}</p>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            html += '<div class="empty-testimonials bg-light p-4 rounded border text-center text-gray-600 w-full">אין המלצות להצגה. הוסף המלצות בהגדרות הסקשן.</div>';
        }
        
        html += '</div>';
        return html;
    }
    
    /**
     * Render videos preview
     */
    function renderVideosPreview(sectionData) {
        const videos = sectionData.items || [];
        let html = '<div class="videos-preview grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">';
        
        if (videos.length > 0) {
            videos.forEach((video, index) => {
                const thumbnailSrc = video.thumbnail || '../assets/img/video-placeholder.jpg';
                // Fix image path if needed
                const fixedThumbnailSrc = thumbnailSrc.startsWith('/') ? thumbnailSrc.substring(1) : thumbnailSrc;
                
                html += `
                    <div class="video-item rounded shadow border overflow-hidden">
                        <div class="video-thumbnail relative" style="height: 180px;">
                            <img src="${fixedThumbnailSrc}" alt="תמונת וידאו" class="w-full h-full object-cover">
                            <div class="play-button absolute flex items-center justify-center" style="top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,0.2);">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22ZM12 20C16.4183 20 20 16.4183 20 12C20 7.58172 16.4183 4 12 4C7.58172 4 4 7.58172 4 12C4 16.4183 7.58172 20 12 20Z" fill="white"/>
                                    <path d="M10 16.5V7.5L16 12L10 16.5Z" fill="white"/>
                                </svg>
                            </div>
                        </div>
                        <div class="video-info p-3">
                            <h4 class="font-bold mb-2">${video.title || 'כותרת הוידאו'}</h4>
                            <p class="text-sm text-gray-600">${video.description || 'תיאור הוידאו יופיע כאן'}</p>
                        </div>
                    </div>
                `;
            });
        } else {
            html += '<div class="empty-videos bg-light p-4 rounded border text-center text-gray-600 w-full">אין סרטונים להצגה. הוסף סרטונים בהגדרות הסקשן.</div>';
        }
        
        html += '</div>';
        return html;
    }
    
    /**
     * Show loading indicator
     */
    function showLoading(element) {
        if (element) { // בדיקה שהאלמנט קיים לפני הנסיון לעדכן אותו
            element.innerHTML = '<div class="loading-placeholder flex items-center justify-center bg-light rounded p-5 h-64 text-gray-600">טוען...</div>';
        }
    }
    
    /**
     * Handle section selection
     */
    function handleSectionSelect(event) {
        const sectionItem = event.target.closest('[data-section]');
        if (!sectionItem) return;

        const statusDot = event.target.closest('.status-dot');
        if (statusDot) {
            // אם לחצו על נקודת הסטטוס, נטפל בהפעלה/כיבוי של הסקשן
            event.stopPropagation(); // מניעת בחירת הסקשן
            const sectionId = sectionItem.getAttribute('data-section');
            const isCurrentlyEnabled = !statusDot.classList.contains('bg-gray-300');
            toggleSectionStatus(sectionId, !isCurrentlyEnabled);
            return;
        }

        // אם לא לחצו על נקודת הסטטוס, נבחר את הסקשן
        const sectionId = sectionItem.getAttribute('data-section');
        selectSection(sectionId);
        
        // עדכון שם הסקשן הנבחר
        if (activeSectionName) {
            activeSectionName.textContent = getSectionTitle(sectionId);
        }
        
        if (settingsSectionName) {
            settingsSectionName.textContent = getSectionTitle(sectionId);
        }
    }
    
    /**
     * Select a section and load its settings
     */
    function selectSection(sectionId) {
        // Update active section
        pageData.activeSectionId = sectionId;
        
        // Update visual selection in sidebar
        const items = sectionsList.querySelectorAll('li');
        items.forEach(item => {
            item.classList.remove('active');
            if (item.getAttribute('data-section') === sectionId) {
                item.classList.add('active');
            }
        });
        
        // Show settings for this section
        loadSectionSettings(sectionId);
    }
    
    /**
     * Load settings for a specific section
     */
    function loadSectionSettings(sectionId) {
        showLoading(sectionSettings);
        
        // Create or get section data
        if (!pageData.sections[sectionId]) {
            pageData.sections[sectionId] = {
                hidden: false,
                items: []
            };
        }
        
        const sectionData = pageData.sections[sectionId];
        
        // Render settings UI based on section type
        let settingsHtml = `
            <div class="settings-panel bg-white rounded shadow overflow-hidden">
                <div class="panel-header bg-light p-3 border-b font-bold">
                    ${getSectionTitle(sectionId)}
                </div>
                <div class="panel-body p-4">
                    <div class="form-group mb-4">
                        <label class="toggle-label flex items-center justify-between">
                            <span class="font-medium">הצג סקשן</span>
                            <label class="toggle-switch">
                                <input type="checkbox" class="section-toggle" data-section-id="${sectionId}" ${sectionData.hidden ? '' : 'checked'}>
                                <span class="slider"></span>
                            </label>
                        </label>
                    </div>
                    
                    ${getSpecificSettings(sectionId, sectionData)}
                </div>
            </div>
        `;
        
        sectionSettings.innerHTML = settingsHtml;
        
        // Add event listeners to new elements
        addSettingsEventListeners(sectionId);
    }
    
    /**
     * Get specific settings UI for each section type
     */
    function getSpecificSettings(sectionId, sectionData) {
        switch(sectionId) {
            case 'testimonials':
                return getTestimonialsSettings(sectionData);
            case 'videos':
                return getVideosSettings(sectionData);
            // Add cases for other section types
            default:
                return '<p class="text-center text-gray-600 p-3">הגדרות נוספות יופיעו כאן בהתאם לסוג הסקשן</p>';
        }
    }
    
    /**
     * Get settings UI for testimonials section
     */
    function getTestimonialsSettings(sectionData) {
        const testimonials = sectionData.items || [];
        let html = `
            <div class="form-group mb-4">
                <label class="block mb-2 font-medium">כותרת הסקשן</label>
                <input type="text" class="form-control section-title w-full p-2 border rounded" value="${sectionData.title || 'ההמלצות שלנו'}" placeholder="הזן כותרת">
            </div>
            
            <div class="items-list testimonials-list mt-4">
                <h4 class="font-bold mb-3">המלצות</h4>
        `;
        
        // Display existing testimonials
        testimonials.forEach((testimonial, index) => {
            html += `
                <div class="item-row flex justify-between items-center p-3 mb-2 bg-white border rounded" data-index="${index}">
                    <div class="item-preview">
                        <strong>${testimonial.author || 'ממליץ'}</strong>
                    </div>
                    <div class="item-actions flex gap-2">
                        <button class="edit-item px-2 py-1 bg-light border rounded" data-type="testimonial" data-index="${index}">ערוך</button>
                        <button class="delete-item px-2 py-1 bg-light border rounded" data-type="testimonial" data-index="${index}">מחק</button>
                    </div>
                </div>
            `;
        });
        
        // Add button
        html += `
                <button class="add-item w-full p-2 mt-3 bg-light border-dashed border rounded text-center cursor-pointer" data-type="testimonial">הוסף המלצה חדשה</button>
            </div>
        `;
        
        return html;
    }
    
    /**
     * Get settings UI for videos section
     */
    function getVideosSettings(sectionData) {
        const videos = sectionData.items || [];
        let html = `
            <div class="form-group mb-4">
                <label class="block mb-2 font-medium">כותרת הסקשן</label>
                <input type="text" class="form-control section-title w-full p-2 border rounded" value="${sectionData.title || 'הסרטונים שלנו'}" placeholder="הזן כותרת">
            </div>
            
            <div class="items-list videos-list mt-4">
                <h4 class="font-bold mb-3">סרטונים</h4>
        `;
        
        // Display existing videos
        videos.forEach((video, index) => {
            html += `
                <div class="item-row flex justify-between items-center p-3 mb-2 bg-white border rounded" data-index="${index}">
                    <div class="item-preview">
                        <strong>${video.title || 'סרטון'}</strong>
                    </div>
                    <div class="item-actions flex gap-2">
                        <button class="edit-item px-2 py-1 bg-light border rounded" data-type="video" data-index="${index}">ערוך</button>
                        <button class="delete-item px-2 py-1 bg-light border rounded" data-type="video" data-index="${index}">מחק</button>
                    </div>
                </div>
            `;
        });
        
        // Add button
        html += `
                <button class="add-item w-full p-2 mt-3 bg-light border-dashed border rounded text-center cursor-pointer" data-type="video">הוסף סרטון חדש</button>
            </div>
        `;
        
        return html;
    }
    
    /**
     * Add event listeners to the settings UI elements
     */
    function addSettingsEventListeners(sectionId) {
        // Toggle section visibility
        const toggle = document.querySelector(`.section-toggle[data-section-id="${sectionId}"]`);
        if (toggle) {
            toggle.addEventListener('change', function() {
                toggleSectionStatus(sectionId, this.checked);
            });
        }
        
        // Section title change
        const titleInput = document.querySelector('.section-title');
        if (titleInput) {
            titleInput.addEventListener('input', function() {
                if (!pageData.sections[sectionId]) {
                    pageData.sections[sectionId] = {};
                }
                pageData.sections[sectionId].title = this.value;
                
                clearTimeout(this._updateTimer);
                this._updateTimer = setTimeout(() => {
                    // עדכון התצוגה המקדימה ב-iframe
                    if (previewIframe && previewIframe.contentWindow) {
                        previewIframe.contentWindow.location.reload();
                    }
                    
                    // הודעה למשתמש
                    showNotification("כותרת הסקשן עודכנה. לחץ על 'שמור שינויים' כדי לשמור", 'info');
                }, 500); // השהייה של חצי שנייה אחרי הקלדה
            });
        }
        
        // Add item buttons
        const addButtons = document.querySelectorAll('.add-item');
        addButtons.forEach(button => {
            button.addEventListener('click', function() {
                const itemType = this.getAttribute('data-type');
                addNewItem(sectionId, itemType);
            });
        });
        
        // Edit item buttons
        const editButtons = document.querySelectorAll('.edit-item');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const itemType = this.getAttribute('data-type');
                const index = parseInt(this.getAttribute('data-index'));
                editItem(sectionId, itemType, index);
            });
        });
        
        // Delete item buttons
        const deleteButtons = document.querySelectorAll('.delete-item');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const itemType = this.getAttribute('data-type');
                const index = parseInt(this.getAttribute('data-index'));
                deleteItem(sectionId, itemType, index);
            });
        });
    }
    
    // פונקציה לשמירה אוטומטית
    let autoSaveTimer = null;
    function startAutoSave() {
        // מבטל טיימר קודם אם קיים
        if (autoSaveTimer) clearTimeout(autoSaveTimer);
        
        // מגדיר טיימר חדש
        autoSaveTimer = setTimeout(() => {
            saveTemplate();
        }, 2000); // שמירה אוטומטית לאחר 2 שניות של חוסר פעילות
    }
    
    /**
     * Add a new item to a section
     */
    function addNewItem(sectionId, itemType) {
        if (!pageData.sections[sectionId].items) {
            pageData.sections[sectionId].items = [];
        }
        
        let newItem = {};
        
        // Create default item based on type
        if (itemType === 'testimonial') {
            newItem = {
                author: 'שם הממליץ',
                role: 'תפקיד',
                quote: 'תוכן ההמלצה',
                image: '../assets/img/placeholder.jpg'
            };
            
            // Open the editor for the new item
            const testimonialsCount = pageData.sections[sectionId].items.length;
            pageData.sections[sectionId].items.push(newItem);
            
            openTestimonialEditor(sectionId, testimonialsCount);
        } else if (itemType === 'video') {
            newItem = {
                title: 'כותרת הסרטון',
                description: 'תיאור הסרטון',
                url: '',
                thumbnail: '../assets/img/video-placeholder.jpg'
            };
            
            // Open the editor for the new item
            const videosCount = pageData.sections[sectionId].items.length;
            pageData.sections[sectionId].items.push(newItem);
            
            openVideoEditor(sectionId, videosCount);
        }
    }
    
    /**
     * Edit an existing item
     */
    function editItem(sectionId, itemType, index) {
        if (itemType === 'testimonial') {
            openTestimonialEditor(sectionId, index);
        } else if (itemType === 'video') {
            openVideoEditor(sectionId, index);
        }
    }
    
    /**
     * Delete an item
     */
    function deleteItem(sectionId, itemType, index) {
        if (confirm('האם אתה בטוח שברצונך למחוק פריט זה?')) {
            pageData.sections[sectionId].items.splice(index, 1);
            loadSectionSettings(sectionId);
            renderTemplate();
            
            // עדכון התצוגה המקדימה ב-iframe
            if (previewIframe && previewIframe.contentWindow) {
                previewIframe.contentWindow.location.reload();
            }
            
            // הודעה למשתמש
            let itemTypeText = '';
            if (itemType === 'testimonial') {
                itemTypeText = 'המלצה';
            } else if (itemType === 'video') {
                itemTypeText = 'סרטון';
            }
            
            showNotification(`${itemTypeText} נמחקה. לחץ על 'שמור שינויים' כדי לשמור באופן קבוע`, 'info');
        }
    }
    
    /**
     * Open testimonial editor
     */
    function openTestimonialEditor(sectionId, itemIndex) {
        const testimonialData = pageData.sections[sectionId].items[itemIndex];
        
        // Create modal background
        const modalBackground = document.createElement('div');
        modalBackground.className = 'modal-background';
        modalBackground.style.position = 'fixed';
        modalBackground.style.top = '0';
        modalBackground.style.right = '0';
        modalBackground.style.bottom = '0';
        modalBackground.style.left = '0';
        modalBackground.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
        modalBackground.style.zIndex = '999';
        document.body.appendChild(modalBackground);
        
        // Create editor
        const editor = new TestimonialEditor({
            data: testimonialData,
            onSave: (data) => {
                // Update data
                pageData.sections[sectionId].items[itemIndex] = data;
                
                // Refresh UI
                loadSectionSettings(sectionId);
                renderTemplate();
                
                // הודעה למשתמש
                showNotification("המלצה נשמרה. לחץ על 'שמור שינויים' כדי לשמור באופן קבוע", 'success');
                
                // עדכון התצוגה המקדימה ב-iframe באופן מיידי
                if (previewIframe && previewIframe.contentWindow) {
                    previewIframe.contentWindow.location.reload();
                }
                
                // Remove modal background and editor
                if (document.body.contains(modalBackground)) {
                    document.body.removeChild(modalBackground);
                }
                if (document.body.contains(editor.element)) {
                    document.body.removeChild(editor.element);
                }
            },
            onCancel: () => {
                // Remove modal background and editor
                if (document.body.contains(modalBackground)) {
                    document.body.removeChild(modalBackground);
                }
                if (document.body.contains(editor.element)) {
                    document.body.removeChild(editor.element);
                }
            }
        });
        
        // Append to body
        document.body.appendChild(editor.element);
    }
    
    /**
     * Open video editor
     */
    function openVideoEditor(sectionId, itemIndex) {
        const videoData = pageData.sections[sectionId].items[itemIndex];
        
        // Create modal background
        const modalBackground = document.createElement('div');
        modalBackground.className = 'modal-background';
        modalBackground.style.position = 'fixed';
        modalBackground.style.top = '0';
        modalBackground.style.right = '0';
        modalBackground.style.bottom = '0';
        modalBackground.style.left = '0';
        modalBackground.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
        modalBackground.style.zIndex = '999';
        document.body.appendChild(modalBackground);
        
        // Create editor
        const editor = new VideoEditor({
            data: videoData,
            onSave: (data) => {
                // Update data
                pageData.sections[sectionId].items[itemIndex] = data;
                
                // Refresh UI
                loadSectionSettings(sectionId);
                renderTemplate();
                
                // הודעה למשתמש
                showNotification("סרטון נשמר. לחץ על 'שמור שינויים' כדי לשמור באופן קבוע", 'success');
                
                // עדכון התצוגה המקדימה ב-iframe באופן מיידי
                if (previewIframe && previewIframe.contentWindow) {
                    previewIframe.contentWindow.location.reload();
                }
                
                // Remove modal background and editor
                if (document.body.contains(modalBackground)) {
                    document.body.removeChild(modalBackground);
                }
                if (document.body.contains(editor.element)) {
                    document.body.removeChild(editor.element);
                }
            },
            onCancel: () => {
                // Remove modal background and editor
                if (document.body.contains(modalBackground)) {
                    document.body.removeChild(modalBackground);
                }
                if (document.body.contains(editor.element)) {
                    document.body.removeChild(editor.element);
                }
            }
        });
        
        // Append to body
        document.body.appendChild(editor.element);
    }
    
    /**
     * Update the sections list UI
     */
    function updateSectionList() {
        const sectionsList = document.getElementById('available-sections');
        if (!sectionsList) return;
        
        // עדכון הסטטוס של כל סקשן ברשימה
        const sectionItems = sectionsList.querySelectorAll('[data-section]');
        sectionItems.forEach(item => {
            const sectionId = item.getAttribute('data-section');
            const section = pageData.sections[sectionId] || {};
            const statusDot = item.querySelector('.status-dot');
            
            if (statusDot) {
                if (section.hidden) {
                    statusDot.classList.remove('bg-green-500');
                    statusDot.classList.add('bg-gray-300');
                    statusDot.setAttribute('title', 'לא פעיל - לחץ כדי להפעיל');
                } else {
                    statusDot.classList.remove('bg-gray-300');
                    statusDot.classList.add('bg-green-500');
                    statusDot.setAttribute('title', 'פעיל - לחץ כדי לכבות');
                }
            }
            
            // עדכון הסטטוס הנוכחי של הסקשן
            if (pageData.activeSectionId === sectionId) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }
    
    /**
     * Toggle section visibility status
     */
    function toggleSectionStatus(sectionId, isEnabled) {
        if (!pageData.sections[sectionId]) {
            pageData.sections[sectionId] = {};
        }
        
        pageData.sections[sectionId].hidden = !isEnabled;
        
        // עדכון התצוגה המקדימה
        if (previewIframe && previewIframe.contentWindow) {
            previewIframe.contentWindow.location.reload();
        }
        
        // עדכון רשימת הסקשנים
        updateSectionList();
        
        // הודעה למשתמש
        showNotification(
            isEnabled ? 
            `הסקשן "${getSectionTitle(sectionId)}" הופעל` : 
            `הסקשן "${getSectionTitle(sectionId)}" כובה`, 
            'info'
        );
    }
    
    /**
     * Save the template
     */
    function saveTemplate() {
        const saveBtn = document.getElementById('save-template');
        if (!saveBtn) return;

        // שמירת הטקסט המקורי של הכפתור
        const originalText = saveBtn.innerHTML;
        
        // עדכון הכפתור למצב טעינה
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="ri-loader-4-line animate-spin"></i> שומר...';

        // הכנת הנתונים לשמירה
        const templateData = {
            page_id: pageData.pageId,
            sections: pageData.sections
        };
        
        // שליחת הנתונים לשרת
        fetch('../api/save_template.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(templateData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // הצגת הודעה ברורה
                showNotification('השינויים נשמרו בהצלחה!', 'success');
                
                // רענון עמוד התצוגה המקדימה
                if (previewIframe && previewIframe.contentWindow) {
                    previewIframe.contentWindow.location.reload();
                }
            } else {
                showNotification('שגיאה בשמירת התבנית: ' + data.message, 'error');
                console.error('שגיאה בשמירת התבנית:', data.message);
            }
        })
        .catch(error => {
            console.error('שגיאה בשמירת התבנית:', error);
            showNotification('שגיאה בשמירת התבנית. נסה שוב.', 'error');
        })
        .finally(() => {
            // החזרת כפתור השמירה למצב רגיל
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        });
    }
    
    /**
     * הצגת הודעה קטנה למשתמש (לשמירה אוטומטית)
     */
    function showMiniNotification(message, type = 'info') {
        // יצירת אלמנט ההודעה
        const notification = document.createElement('div');
        notification.className = `mini-notification ${type} fixed bottom-5 left-5 px-3 py-2 rounded-lg shadow-lg z-50 text-sm opacity-90`;
        
        // קביעת הצבע לפי סוג ההודעה
        if (type === 'success') {
            notification.classList.add('bg-green-500', 'text-white');
        } else if (type === 'error') {
            notification.classList.add('bg-red-500', 'text-white');
        } else {
            notification.classList.add('bg-blue-500', 'text-white');
        }
        
        // הוספת הטקסט
        notification.textContent = message;
        
        // הוספה לדף
        document.body.appendChild(notification);
        
        // הסרה אוטומטית אחרי 2 שניות
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 2000);
    }
    
    /**
     * הצגת הודעה למשתמש
     */
    function showNotification(message, type = 'info') {
        // יצירת אלמנט ההודעה
        const notification = document.createElement('div');
        notification.className = `notification ${type} fixed top-20 left-1/2 transform -translate-x-1/2 px-4 py-3 rounded-lg shadow-lg z-50`;
        
        // קביעת הצבע לפי סוג ההודעה
        if (type === 'success') {
            notification.classList.add('bg-green-500', 'text-white');
        } else if (type === 'error') {
            notification.classList.add('bg-red-500', 'text-white');
        } else {
            notification.classList.add('bg-blue-500', 'text-white');
        }
        
        // הוספת הטקסט
        notification.textContent = message;
        
        // הוספה לדף
        document.body.appendChild(notification);
        
        // הסרה אוטומטית אחרי 3 שניות
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 3000);
    }
    
    /**
     * Preview the template in a new tab
     */
    function previewTemplate() {
        // Open preview in a new tab
        window.open(`../preview.php?page_id=${pageData.pageId}`, '_blank');
    }
    
    /**
     * גלילה לסקשן הנבחר בתצוגת התצוגה המקדימה
     */
    function scrollToSelectedSection() {
        if (!pageData.activeSectionId) return;
        
        const previewIframe = document.getElementById('preview-iframe');
        if (!previewIframe || !previewIframe.contentWindow) return;
        
        // שליחת הודעה לתוך ה-iframe כדי לגלול לסקשן הנבחר
        previewIframe.contentWindow.postMessage({
            action: 'scrollToSection',
            sectionId: pageData.activeSectionId
        }, '*');
    }
}); 