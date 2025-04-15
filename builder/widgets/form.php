<?php
/**
 * קומפוננטת טופס מלאה
 */
function register_form_widget() {
    return [
        'id' => 'form',
        'title' => 'טופס',
        'icon' => 'fas fa-file-alt',
        'description' => 'רכיב טופס ליצירת טפסים מותאמים אישית',
        'render_callback' => 'render_form_widget'
    ];
}

class FormComponent {
    /**
     * יצירת אובייקט הרכיב
     * @param array $content תוכן הטופס (שדות וכו')
     * @param array $style מערך סגנונות
     * @return array אובייקט הרכיב המוכן
     */
    public static function render($content = [], $style = []) {
        return [
            'type' => 'form',
            'content' => $content,
            'style' => array_merge([
                'padding' => '20px',
                'margin' => '10px 0',
                'backgroundColor' => '#ffffff',
                'borderRadius' => '8px',
                'boxShadow' => '0 1px 3px rgba(0,0,0,0.1)'
            ], $style ?? []),
            'fields' => [
                [
                    'type' => 'text',
                    'name' => 'name',
                    'label' => 'שם מלא',
                    'placeholder' => 'הזן שם מלא',
                    'required' => true
                ],
                [
                    'type' => 'email',
                    'name' => 'email',
                    'label' => 'אימייל',
                    'placeholder' => 'הזן כתובת אימייל',
                    'required' => true
                ],
                [
                    'type' => 'phone',
                    'name' => 'phone',
                    'label' => 'טלפון',
                    'placeholder' => 'הזן מספר טלפון',
                    'required' => false
                ]
            ],
            'settings' => [
                'submitText' => 'שלח',
                'submitButtonStyle' => 'primary',
                'successMessage' => 'תודה! הטופס נשלח בהצלחה',
                'errorMessage' => 'אירעה שגיאה בשליחת הטופס',
                'redirectUrl' => '',
                'sendToEmail' => '',
                'emailTemplate' => 'default',
                'storeSubmissions' => true
            ]
        ];
    }

    /**
     * הגדרות ברירת מחדל של הרכיב
     * @return array הגדרות הרכיב
     */
    public static function getDefaultProps() {
        return [
            'icon' => 'ri-file-list-line',
            'label' => 'טופס',
            'category' => 'טפסים',
            'settings' => [
                'fields' => [
                    'type' => 'fields',
                    'label' => 'שדות',
                    'addable' => true,
                    'fieldTypes' => [
                        'text' => 'טקסט',
                        'email' => 'אימייל',
                        'phone' => 'טלפון',
                        'textarea' => 'טקסט ארוך',
                        'select' => 'בחירה',
                        'checkbox' => 'תיבת סימון',
                        'radio' => 'כפתורי רדיו',
                        'date' => 'תאריך',
                        'number' => 'מספר',
                        'file' => 'העלאת קובץ'
                    ]
                ],
                'submitText' => [
                    'type' => 'text',
                    'label' => 'טקסט כפתור שליחה'
                ],
                'submitButtonStyle' => [
                    'type' => 'select',
                    'label' => 'סגנון כפתור',
                    'options' => [
                        'primary' => 'ראשי',
                        'secondary' => 'משני',
                        'outline' => 'מתאר'
                    ]
                ],
                'successMessage' => [
                    'type' => 'textarea',
                    'label' => 'הודעת הצלחה'
                ],
                'errorMessage' => [
                    'type' => 'textarea',
                    'label' => 'הודעת שגיאה'
                ],
                'redirectUrl' => [
                    'type' => 'text',
                    'label' => 'קישור להפניה לאחר שליחה'
                ],
                'sendToEmail' => [
                    'type' => 'text',
                    'label' => 'אימייל לקבלת הטופס'
                ],
                'emailTemplate' => [
                    'type' => 'select',
                    'label' => 'תבנית אימייל',
                    'options' => [
                        'default' => 'ברירת מחדל',
                        'minimal' => 'מינימלי',
                        'branded' => 'ממותג'
                    ]
                ],
                'storeSubmissions' => [
                    'type' => 'boolean',
                    'label' => 'שמור הגשות במערכת'
                ],
                'formLayout' => [
                    'type' => 'select',
                    'label' => 'פריסת טופס',
                    'options' => [
                        'vertical' => 'אנכי',
                        'horizontal' => 'אופקי',
                        'inline' => 'בשורה אחת'
                    ]
                ]
            ]
        ];
    }

    /**
     * רינדור HTML של הרכיב עבור תצוגה בדפדפן
     * @param array $data נתוני הרכיב
     * @return string קוד HTML
     */
    public static function renderHtml($data) {
        // ערכי ברירת מחדל
        $defaults = [
            'style' => [
                'padding' => '20px',
                'margin' => '10px 0',
                'backgroundColor' => '#ffffff',
                'borderRadius' => '8px',
                'boxShadow' => '0 1px 3px rgba(0,0,0,0.1)'
            ],
            'fields' => [
                [
                    'type' => 'text',
                    'name' => 'name',
                    'label' => 'שם מלא',
                    'placeholder' => 'הזן שם מלא',
                    'required' => true
                ],
                [
                    'type' => 'email',
                    'name' => 'email',
                    'label' => 'אימייל',
                    'placeholder' => 'הזן כתובת אימייל',
                    'required' => true
                ],
                [
                    'type' => 'phone',
                    'name' => 'phone',
                    'label' => 'טלפון',
                    'placeholder' => 'הזן מספר טלפון',
                    'required' => false
                ]
            ],
            'settings' => [
                'submitText' => 'שלח',
                'submitButtonStyle' => 'primary',
                'successMessage' => 'תודה! הטופס נשלח בהצלחה',
                'errorMessage' => 'אירעה שגיאה בשליחת הטופס',
                'redirectUrl' => '',
                'formLayout' => 'vertical'
            ]
        ];
        
        // מיזוג הנתונים
        $fields = $data['fields'] ?? $defaults['fields'];
        $style = isset($data['style']) ? array_merge($defaults['style'], $data['style']) : $defaults['style'];
        $settings = isset($data['settings']) ? array_merge($defaults['settings'], $data['settings']) : $defaults['settings'];
        
        // בניית סגנון CSS למכל
        $formStyleStr = '';
        foreach ($style as $key => $value) {
            $formStyleStr .= "{$key}: {$value}; ";
        }
        
        // ID ייחודי לטופס
        $formId = 'form_' . uniqid();
        
        // יצירת קוד HTML
        $html = '<div style="' . $formStyleStr . '">';
        $html .= '<form id="' . $formId . '" class="quicksite-form" data-form-id="' . $formId . '" method="post">';
        
        // שדות הטופס
        foreach ($fields as $field) {
            $html .= self::renderFormField($field, $settings['formLayout'] ?? 'vertical');
        }
        
        // כפתור שליחה
        $buttonClass = 'btn';
        switch ($settings['submitButtonStyle'] ?? 'primary') {
            case 'primary':
                $buttonClass .= ' btn-primary';
                break;
            case 'secondary':
                $buttonClass .= ' btn-secondary';
                break;
            case 'outline':
                $buttonClass .= ' btn-outline';
                break;
        }
        
        $html .= '<div class="form-group">';
        $html .= '<button type="submit" class="' . $buttonClass . '">' . htmlspecialchars($settings['submitText'] ?? 'שלח') . '</button>';
        $html .= '</div>';
        
        // הודעות
        $html .= '<div class="form-messages">';
        $html .= '<div class="success-message" style="display: none; color: green; margin-top: 10px;">' . htmlspecialchars($settings['successMessage'] ?? 'תודה! הטופס נשלח בהצלחה') . '</div>';
        $html .= '<div class="error-message" style="display: none; color: red; margin-top: 10px;">' . htmlspecialchars($settings['errorMessage'] ?? 'אירעה שגיאה בשליחת הטופס') . '</div>';
        $html .= '</div>';
        
        // הגדרות טופס נסתרות
        if (!empty($settings['redirectUrl'])) {
            $html .= '<input type="hidden" name="redirect_url" value="' . htmlspecialchars($settings['redirectUrl']) . '">';
        }
        if (!empty($settings['sendToEmail'])) {
            $html .= '<input type="hidden" name="send_to_email" value="' . htmlspecialchars($settings['sendToEmail']) . '">';
        }
        
        $html .= '<input type="hidden" name="form_id" value="' . $formId . '">';
        $html .= '</form>';
        $html .= '</div>';
        
        // JavaScript לטיפול בשליחת הטופס
        $html .= '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const form = document.getElementById("' . $formId . '");
                if (form) {
                    form.addEventListener("submit", function(e) {
                        e.preventDefault();
                        const formData = new FormData(form);
                        
                        // שליחת הטופס באמצעות AJAX
                        fetch("/submit_form.php", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            const successMsg = form.querySelector(".success-message");
                            const errorMsg = form.querySelector(".error-message");
                            
                            if (data.success) {
                                // הצלחה
                                successMsg.style.display = "block";
                                errorMsg.style.display = "none";
                                form.reset();
                                
                                // הפניה אם הוגדרה
                                if (data.redirect_url) {
                                    setTimeout(() => {
                                        window.location.href = data.redirect_url;
                                    }, 2000);
                                }
                            } else {
                                // שגיאה
                                successMsg.style.display = "none";
                                errorMsg.style.display = "block";
                                errorMsg.textContent = data.message || "' . htmlspecialchars($settings['errorMessage'] ?? 'אירעה שגיאה בשליחת הטופס') . '";
                            }
                        })
                        .catch(error => {
                            const errorMsg = form.querySelector(".error-message");
                            errorMsg.style.display = "block";
                            errorMsg.textContent = "שגיאת תקשורת: " + error.message;
                        });
                    });
                }
            });
        </script>';
        
        return $html;
    }
    
    /**
     * רינדור שדה בטופס
     * @param array $field מערך נתוני השדה
     * @param string $layout פריסת הטופס
     * @return string קוד HTML של השדה
     */
    private static function renderFormField($field, $layout = 'vertical') {
        $type = $field['type'] ?? 'text';
        $name = $field['name'] ?? '';
        $label = $field['label'] ?? '';
        $placeholder = $field['placeholder'] ?? '';
        $required = isset($field['required']) && $field['required'] ? 'required' : '';
        $options = $field['options'] ?? [];
        
        // חישוב מחלקות CSS בהתאם לפריסה
        $formGroupClass = 'form-group';
        $labelClass = 'form-label';
        
        if ($layout === 'horizontal') {
            $formGroupClass .= ' row';
            $labelClass .= ' col-sm-3';
        } elseif ($layout === 'inline') {
            $formGroupClass .= ' d-inline-block mx-2';
        }
        
        $html = '<div class="' . $formGroupClass . '">';
        
        // תווית
        if (!empty($label)) {
            $html .= '<label class="' . $labelClass . '" for="' . $name . '">' . htmlspecialchars($label);
            if ($required) {
                $html .= ' <span class="text-danger">*</span>';
            }
            $html .= '</label>';
        }
        
        // מכל שדה הקלט בהתאם לפריסה
        $inputContainerClass = ($layout === 'horizontal') ? 'col-sm-9' : '';
        if (!empty($inputContainerClass)) {
            $html .= '<div class="' . $inputContainerClass . '">';
        }
        
        // שדה הקלט
        switch ($type) {
            case 'textarea':
                $html .= '<textarea name="' . $name . '" id="' . $name . '" class="form-control" placeholder="' . htmlspecialchars($placeholder) . '" ' . $required . '></textarea>';
                break;
                
            case 'select':
                $html .= '<select name="' . $name . '" id="' . $name . '" class="form-select" ' . $required . '>';
                if (!empty($placeholder)) {
                    $html .= '<option value="">' . htmlspecialchars($placeholder) . '</option>';
                }
                foreach ($options as $value => $text) {
                    $html .= '<option value="' . htmlspecialchars($value) . '">' . htmlspecialchars($text) . '</option>';
                }
                $html .= '</select>';
                break;
                
            case 'checkbox':
                $html .= '<div class="form-check">';
                $html .= '<input type="checkbox" name="' . $name . '" id="' . $name . '" class="form-check-input" value="1" ' . $required . '>';
                $html .= '<label class="form-check-label" for="' . $name . '">' . htmlspecialchars($field['checkboxLabel'] ?? '') . '</label>';
                $html .= '</div>';
                break;
                
            case 'radio':
                foreach ($options as $value => $text) {
                    $html .= '<div class="form-check">';
                    $html .= '<input type="radio" name="' . $name . '" id="' . $name . '_' . $value . '" class="form-check-input" value="' . htmlspecialchars($value) . '" ' . $required . '>';
                    $html .= '<label class="form-check-label" for="' . $name . '_' . $value . '">' . htmlspecialchars($text) . '</label>';
                    $html .= '</div>';
                }
                break;
                
            case 'date':
                $html .= '<input type="date" name="' . $name . '" id="' . $name . '" class="form-control" ' . $required . '>';
                break;
                
            case 'file':
                $html .= '<input type="file" name="' . $name . '" id="' . $name . '" class="form-control" ' . $required . '>';
                break;
                
            default:
                $inputType = in_array($type, ['email', 'number', 'tel', 'url', 'password']) ? $type : 'text';
                $html .= '<input type="' . $inputType . '" name="' . $name . '" id="' . $name . '" class="form-control" placeholder="' . htmlspecialchars($placeholder) . '" ' . $required . '>';
                break;
        }
        
        if (!empty($inputContainerClass)) {
            $html .= '</div>'; // סגירת מכל שדה הקלט
        }
        
        $html .= '</div>'; // סגירת קבוצת הטופס
        
        return $html;
    }
    
    /**
     * פונקציית עזר להצגת שדות בממשק העריכה
     * @param array $widget הווידג'ט הנערך
     * @return string קוד HTML של ממשק העריכה
     */
    public static function getEditFields($widget) {
        $fields = $widget['fields'] ?? [];
        $settings = $widget['settings'] ?? [];
        $style = $widget['style'] ?? [];
        
        $html = '<div class="mb-4">';
        $html .= '<h3 class="text-lg font-medium text-gray-700 mb-2">שדות הטופס</h3>';
        
        // רשימת השדות
        $html .= '<div id="form-fields-list" class="border rounded p-3 mb-3">';
        
        if (empty($fields)) {
            $html .= '<p class="text-gray-500">אין שדות בטופס. לחץ על "הוסף שדה" להוספת שדה חדש.</p>';
        } else {
            foreach ($fields as $index => $field) {
                $html .= self::renderFieldEditItem($index, $field);
            }
        }
        
        $html .= '</div>';
        
        // כפתור הוספת שדה
        $html .= '<button type="button" id="add-form-field" class="btn btn-secondary"><i class="fas fa-plus ml-1"></i>הוסף שדה</button>';
        $html .= '</div>';
        
        // הגדרות כלליות
        $html .= '<div class="mb-4">';
        $html .= '<h3 class="text-lg font-medium text-gray-700 mb-2">הגדרות שליחה</h3>';
        
        $html .= '<div class="mb-3">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">טקסט כפתור שליחה</label>';
        $html .= '<input type="text" id="form-submitText" name="settings[submitText]" value="' . htmlspecialchars($settings['submitText'] ?? 'שלח') . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '<div class="mb-3">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">הודעת הצלחה</label>';
        $html .= '<textarea id="form-successMessage" name="settings[successMessage]" class="form-control w-full">' . htmlspecialchars($settings['successMessage'] ?? 'תודה! הטופס נשלח בהצלחה') . '</textarea>';
        $html .= '</div>';
        
        $html .= '<div class="mb-3">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">אימייל לקבלת הטופס</label>';
        $html .= '<input type="email" id="form-sendToEmail" name="settings[sendToEmail]" value="' . htmlspecialchars($settings['sendToEmail'] ?? '') . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '<div class="mb-3">';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">קישור להפניה לאחר שליחה</label>';
        $html .= '<input type="text" id="form-redirectUrl" name="settings[redirectUrl]" value="' . htmlspecialchars($settings['redirectUrl'] ?? '') . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '</div>';
        
        // קוד JavaScript לטיפול בעריכת השדות
        $html .= '<script>
            // אירוע הוספת שדה
            document.getElementById("add-form-field").addEventListener("click", function() {
                const fieldsContainer = document.getElementById("form-fields-list");
                const newIndex = fieldsContainer.querySelectorAll(".form-field-item").length;
                
                const newFieldHtml = `' . self::renderFieldEditItem('__INDEX__', [
                    'type' => 'text',
                    'name' => 'field_new',
                    'label' => 'שדה חדש',
                    'placeholder' => '',
                    'required' => false
                ]) . '`;
                
                fieldsContainer.insertAdjacentHTML("beforeend", newFieldHtml.replace(/__INDEX__/g, newIndex));
                
                // אתחול אירועים לשדה החדש
                setupFieldEvents(newIndex);
            });
            
            // פונקציה לאתחול אירועים לשדה
            function setupFieldEvents(index) {
                // אירוע שינוי סוג שדה
                const typeSelect = document.getElementById(`field-type-${index}`);
                if (typeSelect) {
                    typeSelect.addEventListener("change", function() {
                        const optionsContainer = document.getElementById(`field-options-container-${index}`);
                        if (optionsContainer) {
                            optionsContainer.style.display = ["select", "radio", "checkbox"].includes(this.value) ? "block" : "none";
                        }
                    });
                }
                
                // אירוע מחיקת שדה
                const deleteBtn = document.getElementById(`delete-field-${index}`);
                if (deleteBtn) {
                    deleteBtn.addEventListener("click", function() {
                        const fieldItem = document.getElementById(`form-field-item-${index}`);
                        if (fieldItem && confirm("האם אתה בטוח שברצונך למחוק שדה זה?")) {
                            fieldItem.remove();
                        }
                    });
                }
            }
            
            // אתחול אירועים לשדות קיימים
            document.querySelectorAll(".form-field-item").forEach((item, index) => {
                setupFieldEvents(index);
            });
        </script>';
        
        return $html;
    }
    
    /**
     * רינדור שדה בודד בממשק העריכה
     * @param string|int $index אינדקס השדה
     * @param array $field נתוני השדה
     * @return string קוד HTML של שדה בממשק העריכה
     */
    private static function renderFieldEditItem($index, $field) {
        $type = $field['type'] ?? 'text';
        $name = $field['name'] ?? '';
        $label = $field['label'] ?? '';
        $placeholder = $field['placeholder'] ?? '';
        $required = isset($field['required']) && $field['required'];
        $options = $field['options'] ?? [];
        
        $html = '<div id="form-field-item-' . $index . '" class="form-field-item border-b pb-3 mb-3 last:border-b-0 last:mb-0">';
        $html .= '<div class="flex justify-between items-center mb-2">';
        $html .= '<h4 class="font-medium">' . htmlspecialchars($label) . ' <span class="text-gray-500">(' . htmlspecialchars($type) . ')</span></h4>';
        $html .= '<button type="button" id="delete-field-' . $index . '" class="text-red-600"><i class="fas fa-trash"></i></button>';
        $html .= '</div>';
        
        // סוג שדה
        $html .= '<div class="mb-2 grid grid-cols-2 gap-2">';
        $html .= '<div>';
        $html .= '<label class="block text-sm text-gray-700 mb-1">סוג שדה</label>';
        $html .= '<select id="field-type-' . $index . '" name="fields[' . $index . '][type]" class="form-select w-full">';
        
        $fieldTypes = [
            'text' => 'טקסט',
            'email' => 'אימייל',
            'phone' => 'טלפון',
            'textarea' => 'טקסט ארוך',
            'select' => 'בחירה',
            'checkbox' => 'תיבת סימון',
            'radio' => 'כפתורי רדיו',
            'date' => 'תאריך',
            'number' => 'מספר',
            'file' => 'העלאת קובץ'
        ];
        
        foreach ($fieldTypes as $value => $text) {
            $selected = ($value === $type) ? 'selected' : '';
            $html .= '<option value="' . $value . '" ' . $selected . '>' . htmlspecialchars($text) . '</option>';
        }
        
        $html .= '</select>';
        $html .= '</div>';
        
        $html .= '<div>';
        $html .= '<label class="block text-sm text-gray-700 mb-1">שם שדה (name)</label>';
        $html .= '<input type="text" name="fields[' . $index . '][name]" value="' . htmlspecialchars($name) . '" class="form-control w-full">';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="mb-2 grid grid-cols-2 gap-2">';
        $html .= '<div>';
        $html .= '<label class="block text-sm text-gray-700 mb-1">כותרת (label)</label>';
        $html .= '<input type="text" name="fields[' . $index . '][label]" value="' . htmlspecialchars($label) . '" class="form-control w-full">';
        $html .= '</div>';
        
        $html .= '<div>';
        $html .= '<label class="block text-sm text-gray-700 mb-1">טקסט במקום (placeholder)</label>';
        $html .= '<input type="text" name="fields[' . $index . '][placeholder]" value="' . htmlspecialchars($placeholder) . '" class="form-control w-full">';
        $html .= '</div>';
        $html .= '</div>';
        
        // אפשרויות רק לשדות מסוימים
        $optionsDisplay = in_array($type, ['select', 'radio', 'checkbox']) ? 'block' : 'none';
        $html .= '<div id="field-options-container-' . $index . '" class="mb-2" style="display: ' . $optionsDisplay . ';">';
        $html .= '<label class="block text-sm text-gray-700 mb-1">אפשרויות (הזן כל אפשרות בשורה נפרדת, בפורמט ערך=תווית)</label>';
        
        $optionsStr = '';
        if (!empty($options)) {
            foreach ($options as $value => $text) {
                $optionsStr .= $value . '=' . $text . "\n";
            }
        }
        
        $html .= '<textarea name="fields[' . $index . '][options_text]" class="form-control w-full h-24">' . htmlspecialchars($optionsStr) . '</textarea>';
        $html .= '</div>';
        
        // שדה חובה
        $html .= '<div class="mb-2">';
        $html .= '<label class="inline-flex items-center">';
        $html .= '<input type="checkbox" name="fields[' . $index . '][required]" value="1" ' . ($required ? 'checked' : '') . ' class="form-checkbox">';
        $html .= '<span class="mr-2">שדה חובה</span>';
        $html .= '</label>';
        $html .= '</div>';
        
        $html .= '</div>'; // סגירת form-field-item
        
        return $html;
    }
}