<?php
class FormComponent {
    public static function render($content = [], $style = []) {
        return [
            'type' => 'form',
            'content' => $content,
            'style' => array_merge([
                'padding' => '20px',
                'margin' => '10px',
                'backgroundColor' => '#ffffff',
                'borderRadius' => '8px',
                'boxShadow' => '0 1px 3px rgba(0,0,0,0.1)'
            ], $style ?? []),
            'fields' => [
                [
                    'type' => 'text',
                    'name' => 'name',
                    'label' => 'שם מלא',
                    'required' => true
                ],
                [
                    'type' => 'email',
                    'name' => 'email',
                    'label' => 'אימייל',
                    'required' => true
                ],
                [
                    'type' => 'phone',
                    'name' => 'phone',
                    'label' => 'טלפון',
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
                'emailTemplate' => 'default'
            ]
        ];
    }

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
                        'radio' => 'כפתורי רדיו'
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
                    'type' => 'text',
                    'label' => 'הודעת הצלחה'
                ],
                'errorMessage' => [
                    'type' => 'text',
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
                ]
            ]
        ];
    }
}
