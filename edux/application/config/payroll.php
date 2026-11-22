<?php

$config['staffattendance'] = [
    'present' => 1,
    'half_day' => 4,
    'late' => 2,
    'absent' => 3,
    'holiday' => 5,
    'half_day_second_shift' => 6,
];

$config['contracttype'] = [
    'permanent' => lang('permanent'),
    'probation' => lang('probation'),
];

$config['status'] = [
    'approve' => lang('approved'),
    'disapprove' => lang('disapproved'),
    'pending' => lang('pending'),
];

$config['marital_status'] = [
    'Single' => lang('single'),
    'Married' => lang('married'),
    'Widowed' => lang('widowed'),
    'Seperated' => lang('separated'),
    'Not Specified' => lang('not_specified'),
];

$config['payroll_status'] = [
    'generated' => lang('generated'),
    'paid' => lang('paid'),
    'unpaid' => lang('unpaid'),
    'not_generate' => lang('not_generated'),
];
$config['payment_mode'] = [
    'cash' => lang('cash'),
    'cheque' => lang('cheque'),
    'online' => lang('transfer_to_bank_account'),
];
$config['enquiry_status'] = [
    'active' => lang('active'),
    'passive' => lang('passive'),
    'dead' => lang('dead'),
    'won' => lang('won'),
    'lost' => lang('lost'),
];
$config['search_type'] = [
    'today' => lang('today'),
    'this_week' => lang('this_week'),
    'last_week' => lang('last_week'),
    'this_month' => lang('this_month'),
    'last_month' => lang('last_month'),
    'last_3_month' => lang('last_3_month'),
    'last_6_month' => lang('last_6_month'),
    'last_12_month' => lang('last_12_month'),
    'this_year' => lang('this_year'),
    'last_year' => lang('last_year'),
    'period' => lang('period'),
];
