// Builder Media Library Logic
console.log('Media Library module loaded');

// --- DOM Elements ---
const modal = document.getElementById('media-library-modal');
const closeButton = document.getElementById('media-library-close-btn');
const cancelButton = document.getElementById('media-library-cancel-btn');
const insertButton = document.getElementById('media-library-insert-btn');
const tabs = document.querySelectorAll('.media-tab-button');
const tabContents = document.querySelectorAll('.media-tab-content');
const uploadArea = document.getElementById('media-upload-area');
const fileInput = document.getElementById('media-file-input');
const uploadPreviewArea = document.getElementById('media-upload-preview-area');
const uploadPreviewImg = document.getElementById('media-upload-preview-img');
const uploadUrlText = document.getElementById('media-upload-url');
const uploadError = document.getElementById('media-upload-error');
const uploadProgress = document.getElementById('media-upload-progress');
const uploadProgressBar = uploadProgress ? uploadProgress.querySelector('div > div') : null; // Get the inner bar

const libraryGrid = document.getElementById('media-library-grid');
const libraryLoading = document.getElementById('media-library-loading');
const libraryError = document.getElementById('media-library-error');
const libraryEmpty = document.getElementById('media-library-empty');

// --- State ---
let mediaLibraryCallback = null; // Stores the function to call when an image is inserted
let selectedImageUrl = null; // Stores the URL of the currently selected image
let currentItemSlug = null; // לאחסון ה-slug שהועבר

// --- Functions ---

/**
 * Opens the Media Library modal.
 * @param {function} callback - Function to execute when an image is selected and inserted. 
 *                              Receives the selected image URL as an argument.
 * @param {string} slug - The slug of the current item (page/template).
 */
export function openMediaLibrary(callback, slug) {
    console.log(`Opening media library for slug: ${slug}...`);
    if (!slug || slug === 'null' || slug.startsWith('new-')) { // בדיקה משופרת
        console.error("Media Library opened without a valid saved item slug!");
        alert("שגיאה: יש לשמור את הפריט לפני העלאת מדיה או שימוש בספרייה."); // הודעה ברורה יותר
        return;
    }
    currentItemSlug = slug; // שמירת ה-slug שהתקבל
    mediaLibraryCallback = callback;
    selectedImageUrl = null; // Reset selection
    insertButton.disabled = true; // Disable insert button initially
    
    // Reset previews and errors
    uploadPreviewArea.classList.add('hidden');
    uploadPreviewImg.src = '';
    uploadUrlText.textContent = '';
    uploadError.classList.add('hidden');
    uploadError.textContent = '';
    uploadProgress.classList.add('hidden');
    if(uploadProgressBar) uploadProgressBar.style.width = '0%';
    fileInput.value = ''; // Clear file input selection
    
    // Reset library view
    libraryError.classList.add('hidden');
    libraryGrid.innerHTML = ''; // Clear previous items
    libraryEmpty.classList.add('hidden');

    // Deselect any selected items in the grid
    libraryGrid.querySelectorAll('.media-library-item.selected').forEach(item => {
        item.classList.remove('selected');
    });

    // Default to the library tab
    switchTab('library'); // נטען את הספרייה קודם
    
    modal.classList.remove('hidden');
}

/** Closes the Media Library modal */
function closeMediaLibrary() {
    console.log('Closing media library.');
    modal.classList.add('hidden');
    mediaLibraryCallback = null; // Clear callback
    selectedImageUrl = null;
    currentItemSlug = null; // איפוס ה-slug הנוכחי ביציאה
}

/**
 * Switches the active tab in the modal.
 * @param {string} tabId - The ID ('upload' or 'library') of the tab to switch to.
 */
function switchTab(tabId) {
    tabs.forEach(tab => {
        const isActive = tab.dataset.tab === tabId;
        tab.classList.toggle('bg-white', isActive);
        tab.classList.toggle('text-primary-600', isActive);
        tab.classList.toggle('text-gray-600', !isActive);
        tab.classList.toggle('hover:bg-gray-100', !isActive);
        tab.classList.toggle('bg-gray-50', !isActive); // Ensure non-active have bg
    });
    tabContents.forEach(content => {
        content.classList.toggle('hidden', content.id !== `media-tab-content-${tabId}`);
    });

    // Load library only when switching to it
    if (tabId === 'library') {
        loadMediaLibrary();
    }
    // --- שינוי: איפוס אזור ההעלאה כשחוזרים לטאב העלאה ---
    else if (tabId === 'upload') {
        uploadArea.classList.remove('hidden');
        uploadPreviewArea.classList.add('hidden');
        uploadPreviewImg.src = '';
        uploadUrlText.textContent = '';
        uploadError.classList.add('hidden');
        uploadError.textContent = '';
        uploadProgress.classList.add('hidden');
        if(uploadProgressBar) uploadProgressBar.style.width = '0%';
        fileInput.value = ''; // Clear file input selection
    }
    // -------------------------------------------------------
}

/** Function to load media library content from the server */
async function loadMediaLibrary() {
    libraryLoading.classList.remove('hidden');
    libraryGrid.classList.add('hidden'); // Hide grid while loading
    libraryEmpty.classList.add('hidden');
    libraryError.classList.add('hidden');
    libraryGrid.innerHTML = ''; // Clear previous items
    // --- שינוי: שימוש ב-currentItemSlug השמור --- 
    console.log(`Loading media library content from server for slug: ${currentItemSlug}...`);

    try {
        // --- הוספת בדיקה שה-slug תקין לפני שליחה --- 
        if (!currentItemSlug || currentItemSlug === 'null' || currentItemSlug.startsWith('new-')) {
            throw new Error("לא ניתן לטעון ספרייה עבור פריט שלא נשמר.");
        }
        // ---------------------------------------------
        const response = await fetch(`ajax_media_library_handler.php?slug=${encodeURIComponent(currentItemSlug)}`); 
        // --- שינוי: קריאת התגובה כטקסט קודם --- 
        const responseText = await response.text();
        // -------------------------------------

        if (!response.ok) {
            // --- שיפור: שימוש ב-responseText שקראנו ---
            let errorText = `שגיאת שרת (${response.status})`;
            console.error("Server response (non-ok status), Raw Text:", responseText);
            // נסה לחלץ הודעה אם אפשר 
            const match = responseText.match(/<b>.*?<\/b>:(.*?)<br/i);
            if(match && match[1]) errorText += ": " + match[1].trim();
             else if (responseText.includes('Undefined array key "slug"')) { errorText = 'שגיאת שרת: נראה שהמזהה (slug) לא נשלח כראוי.'; }
            throw new Error(errorText);
            // -------------------------------------------
        }

        // --- שינוי: נסה לפענח את הטקסט כ-JSON --- 
        try {
            const result = JSON.parse(responseText); // פענוח הטקסט שקראנו

            if (!result.success) {
                const debugInfo = result.debug ? JSON.stringify(result.debug) : 'No debug info';
                throw new Error(result.error || `שגיאה לא ידועה בקבלת רשימת התמונות. Debug: ${debugInfo}`);
            }
            
            // Process successful JSON result...
            const imageUrls = result.images || []; 
            const debugData = result.debug || {}; // קבלת מידע הדיבאג
            console.log("Media library loaded successfully. Debug Info:", debugData); // הדפסת הדיבאג

            if (imageUrls.length === 0) {
                libraryEmpty.classList.remove('hidden');
            } else {
                imageUrls.forEach(url => {
                    const item = document.createElement('div');
                    // --- שינוי: הוספת קלאסים לעיצוב הפריט והבחירה ---
                    item.className = 'media-library-item relative aspect-square overflow-hidden rounded-md cursor-pointer border border-gray-200 hover:border-primary-300 focus:outline-none group data-[selected=true]:ring-2 data-[selected=true]:ring-primary-500 data-[selected=true]:ring-offset-1';
                    // -------------------------------------------------
                    item.dataset.url = url;
                    item.dataset.selected = 'false'; // הוספת מאפיין למעקב אחר בחירה

                    const img = document.createElement('img');
                    img.src = url; 
                    img.alt = 'Media Library Image';
                    img.loading = 'lazy';
                    // --- שינוי: הוספת קלאסים לגודל אחיד ו-object-fit ---
                    img.className = 'block w-full h-full object-cover transition-transform duration-150 group-hover:scale-105';
                    // ---------------------------------------------------

                    item.appendChild(img);
                    item.addEventListener('click', () => selectLibraryImage(item));
                    libraryGrid.appendChild(item);
                });
                 libraryGrid.classList.remove('hidden'); 
            }
        } catch (jsonError) {
            // אם הפענוח נכשל
            console.error("Failed to parse response text as JSON:", jsonError);
            console.error("Raw server response text that failed JSON parsing:", responseText); // הדפס את הטקסט שגרם לכשל
            throw new Error(`שגיאה בפענוח תשובת השרת (לא JSON). Raw: ${responseText.substring(0, 200)}...`);
        }
        // ----------------------------------------

    } catch (error) {
        console.error("Error loading media library (Outer Catch):", error);
        libraryError.textContent = `שגיאה בטעינת הספרייה: ${error.message}`;
        libraryError.classList.remove('hidden');
    } finally {
        libraryLoading.classList.add('hidden');
    }
}

/** Handles selection of an image from the library grid */
function selectLibraryImage(selectedItem) {
    // Deselect previously selected item (if any)
    libraryGrid.querySelectorAll('.media-library-item[data-selected="true"]').forEach(item => {
        item.classList.remove('selected'); // שמירה על קלאס ישן אם משתמשים בו ב-CSS חיצוני
        item.dataset.selected = 'false'; // עדכון מאפיין נתונים
    });

    // Select the new item
    selectedItem.classList.add('selected'); // שמירה על קלאס ישן אם משתמשים בו ב-CSS חיצוני
    selectedItem.dataset.selected = 'true'; // עדכון מאפיין נתונים
    selectedImageUrl = selectedItem.dataset.url;
    insertButton.disabled = false; // Enable insert button
    console.log('Library image selected:', selectedImageUrl);
}


/** Handles file selection/drop for upload */
function handleFileSelect(file) {
    if (!file) return;
    // --- הוספת בדיקה שה-slug תקין לפני העלאה --- 
    if (!currentItemSlug || currentItemSlug === 'null' || currentItemSlug.startsWith('new-')) {
         console.error("Cannot upload file without a valid saved item slug!");
         uploadError.textContent = "שגיאה: יש לשמור את הפריט לפני העלאת קובץ.";
         uploadError.classList.remove('hidden');
         return;
     }
    // -------------------------------------------

    console.log('File selected:', file.name, file.type, file.size);
    uploadPreviewArea.classList.add('hidden'); // Hide previous preview
    uploadError.classList.add('hidden');
    uploadProgress.classList.add('hidden');
    insertButton.disabled = true; // Disable insert while uploading/processing
    selectedImageUrl = null; // Reset selection

    // Basic client-side validation (optional but good practice)
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        uploadError.textContent = 'סוג קובץ לא נתמך.';
        uploadError.classList.remove('hidden');
        return;
    }
    // You can add size validation here too if desired, though server validation is crucial

    // Show preview (optional)
    const reader = new FileReader();
    reader.onload = (e) => {
        uploadPreviewImg.src = e.target.result;
        // uploadPreviewArea.classList.remove('hidden'); // Show preview only after successful upload? Or immediately? Let's show after success.
    }
    reader.readAsDataURL(file);

    // --- Start Upload Process ---
    uploadFile(file);
}

/** Uploads the file to the server */
async function uploadFile(file) {
    const formData = new FormData();
    formData.append('image_upload', file); // קובץ התמונה
    // --- שינוי: שימוש ב-currentItemSlug השמור --- 
    formData.append('slug', currentItemSlug); 
    // ------------------------------------------

    uploadProgress.classList.remove('hidden');
    if(uploadProgressBar) uploadProgressBar.style.width = '0%'; 

    try {
        // *** ADJUST PATH to your PHP handler if needed ***
        const response = await fetch('ajax_upload_handler.php', { // Assuming it's in the same dir as index.php for now
            method: 'POST',
            body: formData,
            // If you need progress, you'd typically use XMLHttpRequest
            // For simplicity with fetch, we won't show granular progress here yet.
            // We'll just show "uploading" and then success/failure.
        });

        if (!response.ok) {
            // Handle HTTP errors (like 404, 500)
             let errorText = `שגיאת שרת: ${response.statusText}`;
             try { // Try to get more specific error from response body
                 const errorData = await response.json();
                 if (errorData && errorData.error) {
                     errorText = errorData.error;
                 }
             } catch (e) { /* Ignore if response is not JSON */ }
             throw new Error(errorText);
        }

        const result = await response.json();

        if (result.success && result.url) {
            console.log('Upload successful:', result.url);
            uploadPreviewImg.src = result.url; // Show the final WebP image URL
            uploadUrlText.textContent = `URL: ${result.url}`;
            uploadPreviewArea.classList.remove('hidden');
            selectedImageUrl = result.url; // Set as selected image
            insertButton.disabled = false; // Enable insert button
            if(uploadProgressBar) uploadProgressBar.style.width = '100%'; // Show complete
            
            // --- שיפור: הסתרת אזור גרירה ופס התקדמות --- 
            uploadArea.classList.add('hidden'); // הסתר את אזור הגרירה
            // הסתר את פס ההתקדמות אחרי שנייה כדי שיראו שהוא הגיע ל-100%
            setTimeout(() => uploadProgress.classList.add('hidden'), 1000);
            // -------------------------------------------------

             // Optionally: Refresh library view if on library tab?
             if (document.querySelector('.media-tab-button[data-tab="library"]').classList.contains('bg-white')) {
                loadMediaLibrary(); 
             }
        } else {
            throw new Error(result.error || 'שגיאה לא ידועה בהעלאה.');
        }

    } catch (error) {
        console.error('Upload error:', error);
        uploadError.textContent = error.message;
        uploadError.classList.remove('hidden');
        if(uploadProgressBar) uploadProgressBar.style.width = '0%'; // Reset progress on error

    } finally {
        // Maybe hide progress bar after a short delay on success/error?
        // setTimeout(() => uploadProgress.classList.add('hidden'), 1500); 
    }
}

// --- Event Listeners ---
// Defer adding listeners until the DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
    const modalElement = document.getElementById('media-library-modal');
    if (modalElement) {
        const closeBtn = document.getElementById('media-library-close-btn');
        const cancelBtn = document.getElementById('media-library-cancel-btn');
        const insertBtn = document.getElementById('media-library-insert-btn');
        const tabButtons = modalElement.querySelectorAll('.media-tab-button');
        const uploadAreaElement = document.getElementById('media-upload-area');
        const fileInputElement = document.getElementById('media-file-input');

        // Close button
        closeBtn?.addEventListener('click', closeMediaLibrary);
        cancelBtn?.addEventListener('click', closeMediaLibrary);

        // Insert button
        insertBtn?.addEventListener('click', () => {
            if (selectedImageUrl && mediaLibraryCallback) {
                mediaLibraryCallback(selectedImageUrl);
            }
            closeMediaLibrary();
        });

        // Tab switching
        tabButtons.forEach(tab => {
            tab.addEventListener('click', () => switchTab(tab.dataset.tab));
        });

        // --- Upload Area Listeners --- 
        if (uploadAreaElement) {
            // Click to open file input
            uploadAreaElement.addEventListener('click', () => fileInputElement.click());

            // Drag and Drop
            uploadAreaElement.addEventListener('dragover', (e) => {
                e.preventDefault(); // Prevent default behavior (opening file)
                uploadAreaElement.classList.add('border-primary-400', 'bg-primary-50'); // Highlight drop zone
            });
            uploadAreaElement.addEventListener('dragleave', () => {
                uploadAreaElement.classList.remove('border-primary-400', 'bg-primary-50');
            });
            uploadAreaElement.addEventListener('drop', (e) => {
                e.preventDefault(); // Prevent default behavior
                uploadAreaElement.classList.remove('border-primary-400', 'bg-primary-50');
                if (e.dataTransfer.files.length > 0) {
                    handleFileSelect(e.dataTransfer.files[0]); // Handle the first dropped file
                }
            });

            // File input change
            fileInputElement?.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleFileSelect(e.target.files[0]);
                } 
            });
        }

    } else {
        console.warn("Media Library Modal element not found after DOMContentLoaded.");
    }
}); // End DOMContentLoaded listener


// --- Make openMediaLibrary globally accessible (or export if using modules) ---
// Example of making it global (if not using ES modules for the builder's core JS)
// window.openMediaLibrary = openMediaLibrary; 
// If using ES modules, you would export it:
// export { openMediaLibrary }; // Removed duplicate export
// and import it where needed: import { openMediaLibrary } from './media-library.js'; 