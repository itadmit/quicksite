<?php
class RowComponent {
    public static function render($content = [], $style = []) {
        return [
            'type' => 'row',
            'content' => $content,
            'style' => array_merge([
                'display' => 'grid',
                'gridTemplateColumns' => 'repeat(12, 1fr)',
                'gap' => '20px',
                'padding' => '20',
                'margin' => '0',
                'backgroundColor' => 'transparent',
                'minHeight' => '50px'
            ], $style ?? []),
            'columns' => []
        ];
    }

    public static function getDefaultProps() {
        return [
            'icon' => 'ri-layout-row-line',
            'label' => 'שורה',
            'category' => 'מבנה',
            'settings' => [
                'columns' => [
                    'type' => 'columns',
                    'label' => 'עמודות',
                    'max' => 6,
                    'options' => [
                        1 => '100%',
                        2 => '50% / 50%',
                        3 => '33% / 33% / 33%',
                        4 => '25% / 25% / 25% / 25%',
                        6 => '16.6% x 6'
                    ]
                ],
                'verticalAlign' => [
                    'type' => 'select',
                    'label' => 'יישור אנכי',
                    'options' => [
                        'start' => 'למעלה',
                        'center' => 'מרכז',
                        'end' => 'למטה'
                    ]
                ],
                'backgroundColor' => [
                    'type' => 'color',
                    'label' => 'צבע רקע'
                ],
                'padding' => [
                    'type' => 'spacing',
                    'label' => 'מרווח פנימי'
                ]
            ],
            'droppable' => true // ניתן לגרור אליו אלמנטים
        ];
    }

    public static function updateColumns($row, $numColumns) {
        $columns = [];
        $colWidth = 12 / $numColumns;
        
        for ($i = 0; $i < $numColumns; $i++) {
            $columns[] = [
                'width' => $colWidth,
                'content' => []
            ];
        }

        $row['columns'] = $columns;
        return $row;
    }
} 