<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

$config['custom_fields'] = [
    'input' => 'Input',
    'number' => 'Number',
    'textarea' => 'Textarea',
    'select' => 'Dropdown',
    'multiselect' => 'Multi Select',
    'checkbox' => 'Checkbox',
    'date_picker' => 'Date Picker',
    'date_picker_time' => 'Datetime Picker',
    'colorpicker' => 'Color Picker',
    'link' => 'Hyperlink',
];

$config['custom_field_table'] = [
    'students' => 'Student',
    'staff' => 'Staff',
    'transfer_certificate' => 'Transfer Certificate',
];
