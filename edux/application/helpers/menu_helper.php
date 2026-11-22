<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (! function_exists('active_link')) {

    function activate_menu($controller, $action)
    {
        $CI = get_instance();
        $method = $CI->router->fetch_method();
        $class = $CI->router->fetch_class();

        return ($method == $action && $controller == $class) ? 'active' : '';
    }

    function set_Topmenu($top_menu_name)
    {
        $CI = get_instance();
        $session_top_menu = $CI->session->userdata('top_menu');
        if ($session_top_menu == $top_menu_name) {
            return 'active';
        }

        return '';
    }

    function set_Submenu($sub_menu_name)
    {
        $CI = get_instance();
        $session_sub_menu = $CI->session->userdata('sub_menu');
        if ($session_sub_menu == $sub_menu_name) {
            return 'active';
        }

        return '';
    }

    function set_SubSubmenu($sub_menu_name)
    {
        $CI = get_instance();
        $session_sub_menu = $CI->session->userdata('subsub_menu');
        if ($session_sub_menu == $sub_menu_name) {
            return 'active';
        }

        return '';
    }

}

function access_denied()
{
    redirect('admin/unauthorized');
}

function update_config_installed()
{
    $CI = &get_instance();
    $config_path = APPPATH.'config/config.php';
    $CI->load->helper('file');
    @chmod($config_path, FILE_WRITE_MODE);
    $config_file = read_file($config_path);
    $config_file = trim($config_file);
    $config_file = str_replace("\$config['installed'] = false;", "\$config['installed'] = true;", $config_file);
    $config_file = str_replace("\$config['base_url'] = '';", "\$config['base_url'] = '".site_url()."';", $config_file);
    if (! $fp = fopen($config_path, FOPEN_WRITE_CREATE_DESTRUCTIVE)) {
        return false;
    }
    flock($fp, LOCK_EX);
    fwrite($fp, $config_file, strlen($config_file));
    flock($fp, LOCK_UN);
    fclose($fp);
    @chmod($config_path, FILE_READ_MODE);

    return true;
}

function update_autoload_installed()
{
    $CI = &get_instance();
    $autoload_path = APPPATH.'config/autoload.php';
    $CI->load->helper('file');
    @chmod($autoload_path, FILE_WRITE_MODE);
    $autoload_file = read_file($autoload_path);
    $autoload_file = trim($autoload_file);
    $autoload_file = str_replace("\$autoload['libraries'] = array('database', 'session', 'form_validation')", "\$autoload['libraries'] = array('email','session', 'form_validation', 'upload', 'pagination','Customlib')", $autoload_file);
    if (! $fp = fopen($autoload_path, FOPEN_WRITE_CREATE_DESTRUCTIVE)) {
        return false;
    }
    flock($fp, LOCK_EX);
    fwrite($fp, $autoload_file, strlen($autoload_file));
    flock($fp, LOCK_UN);
    fclose($fp);
    @chmod($config_path, FILE_READ_MODE);

    return true;
}

function delete_dir($dirPath)
{
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath.'*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            delete_dir($file);
        } else {
            unlink($file);
        }
    }
    if (rmdir($dirPath)) {
        return true;
    }

    return false;
}

function admin_url($url = '')
{
    if ($url == '') {
        return site_url().'site/login';
    } else {
        return site_url().'site/login';
    }
}

if (! function_exists('main_menu_array')) {

    function main_menu_array($find_array)
    {
        $array = [

            'front_office' => [
                'enquiry' => ['index'],
                'visitors' => ['index'],
                'generalcall' => ['index', 'edit'],
                'dispatch' => ['index', 'editdispatch'],
                'receive' => ['index', 'editreceive'],
                'complaint' => ['index', 'edit'],
                'visitorspurpose' => ['index', 'edit'],
            ],

            'student_information' => [
                'student' => ['search', 'create', 'import', 'disablestudentslist', 'multiclass', 'bulkdelete', 'view', 'edit'],
                'onlinestudent' => ['index', 'edit'],
                'category' => ['index', 'edit'],
                'schoolhouse' => ['index', 'edit'],
                'disable_reason' => ['index', 'edit'],
            ],

            'fees_collection' => [
                'studentfee' => ['index', 'addfee', 'searchpayment', 'feesearch'],
                'feemaster' => ['index', 'assign', 'edit'],
                'feegroup' => ['index', 'edit'],
                'feetype' => ['index', 'edit'],
                'feediscount' => ['index', 'edit', 'assign'],
                'feesforward' => ['index'],
                'feereminder' => ['setting'],
                'offlinepayment' => ['index'],
                'customfeesmaster' => ['index'],
            ],

            'income' => [
                'income' => ['index', 'edit', 'incomesearch'],
                'incomehead' => ['index', 'edit'],
            ],

            'expense' => [
                'expense' => ['index', 'edit', 'expensesearch'],
                'expensehead' => ['index', 'edit'],
            ],

            'examinations' => [
                'examgroup' => ['index', 'edit', 'addexam'],
                'exam_schedule' => ['index'],
                'examresult' => ['index', 'admitcard', 'marksheet'],
                'admitcard' => ['index', 'edit'],
                'marksheet' => ['index', 'edit'],
                'grade' => ['index', 'edit'],
                'marksdivision' => ['index', 'edit'],
            ],

            'attendance' => [
                'approve_leave' => ['index'],
                'stuattendence' => ['index', 'edit', 'attendencereport'],
                'subjectattendence' => ['index', 'reportbydate'],

            ],

            'online_examinations' => [
                'onlineexam' => ['index', 'evalution', 'assign'],
                'question' => ['index', 'read'],
            ],

            'lesson_plan' => [
                'syllabus' => ['index', 'status'],
                'lessonplan' => ['lesson', 'topic', 'copylesson', 'edittopic', 'editlesson'],
            ],

            'academics' => [
                'timetable' => ['classreport', 'mytimetable', 'create'],
                'teacher' => ['assign_class_teacher', 'update_class_teacher'],
                'stdtransfer' => ['index'],
                'subjectgroup' => ['index', 'edit'],
                'subject' => ['index', 'edit'],
                'classes' => ['index', 'edit'],
                'sections' => ['index', 'edit'],
            ],

            'human_resource' => [
                'staff' => ['index', 'profile', 'edit', 'leaverequest', 'rating', 'disablestafflist', 'create'],
                'staffattendance' => ['index'],
                'payroll' => ['index', 'edit', 'create'],
                'leaverequest' => ['leaverequest'],
                'leavetypes' => ['index', 'leaveedit', 'createleavetype'],
                'department' => ['department', 'departmentedit'],
                'designation' => ['designation', 'designationedit'],
            ],

            'communicate' => [
                'notification' => ['index', 'edit', 'add'],
                'mailsms' => ['compose', 'compose_sms', 'index', 'schedule', 'email_template', 'sms_template', 'edit_schedule'],
                'student' => ['bulkmail'],
            ],

            'download_center' => [
                'contenttype' => ['index', 'edit'],
                'content' => ['list', 'upload'],
                'video_tutorial' => ['index'],
            ],

            'homework' => [
                'homework' => ['index', 'dailyassignment'],
            ],

            'library' => [
                'book' => ['getall', 'edit', 'index', 'import'],
                'member' => ['index', 'issue', 'student', 'teacher'],
            ],

            'inventory' => [
                'issueitem' => ['index', 'create'],
                'itemstock' => ['index', 'edit'],
                'item' => ['index', 'edit'],
                'itemcategory' => ['index', 'edit'],
                'itemstore' => ['index', 'edit', 'create'],
                'itemsupplier' => ['index', 'edit', 'create'],
            ],

            'transport' => [
                'transport' => ['feemaster'],
                'pickuppoint' => ['index', 'assign', 'student_fees'],
                'route' => ['index', 'edit'],
                'vehicle' => ['index'],
                'vehroute' => ['index', 'edit'],
            ],

            'hostel' => [
                'hostelroom' => ['index', 'edit'],
                'roomtype' => ['index', 'edit'],
                'hostel' => ['index', 'edit'],
            ],

            'certificate' => [
                'certificate' => ['index', 'edit'],
                'generatecertificate' => ['index', 'search'],
                'studentidcard' => ['index', 'edit'],
                'generateidcard' => ['search'],
                'staffidcard' => ['index', 'edit'],
                'generatestaffidcard' => ['index', 'search'],
                'transfercertificate' => ['download', 'verify_tc', 'prepare_tc', 'index', 'edit_custom_field', 'edit_header'],

            ],

            'front_cms' => [
                'events' => ['index', 'edit', 'create'],
                'gallery' => ['index', 'edit', 'create'],
                'notice' => ['index', 'edit', 'create'],
                'media' => ['index'],
                'page' => ['index', 'edit', 'create'],
                'menus' => ['index', 'additem'],
                'banner' => ['index'],
            ],

            'alumni' => [
                'alumni' => ['alumnilist', 'events'],
            ],

            'reports' => [
                'report' => ['alumnireport', 'inventory', 'issueinventory', 'additem', 'inventorystock', 'library', 'studentbookissuereport', 'bookduereport', 'bookinventory', 'human_resource', 'staff_report', 'lesson_plan', 'teachersyllabusstatus', 'onlineexamrank', 'onlineexamattend', 'onlineexams', 'attendance', 'studentinformation', 'studentreport', 'online_admission_report', 'student_teacher_ratio', 'boys_girls_ratio', 'student_profile', 'sibling_report', 'admission_report', 'class_subject', 'classsectionreport', 'guardianreport', 'admissionreport', 'logindetailreport', 'parentlogindetailreport', 'leaverequestreport', 'myleaverequestreport'],
                'attendencereports' => ['attendance', 'classattendencereport', 'attendancereport', 'daily_attendance_report', 'staffattendancereport', 'biometric_attlog', 'reportbymonthstudent', 'reportbymonth', 'staffdaywiseattendancereport', 'daywiseattendancereport'],
                'payroll' => ['payrollreport'],
                'onlineexam' => ['report'],
                'examresult' => ['rankreport', 'examinations'],
                'book' => ['issue_returnreport'],
                'route' => ['studenttransportdetails'],
                'hostelroom' => ['studenthosteldetails'],
                'userlog' => ['index'],
                'audit' => ['index'],
                'financereports' => ['finance', 'reportduefees', 'reportdailycollection', 'reportbyname', 'studentacademicreport', 'collection_report', 'onlinefees_report', 'duefeesremark', 'income', 'expense', 'payroll', 'incomegroup', 'expensegroup', 'onlineadmission', 'incomeexpensebalancereport'],
                'homework' => ['homeworkordailyassignmentreport', 'homeworkreport', 'evaluation_report', 'dailyassignmentreport', 'homework_marksreport'],
            ],

            'system_settings' => [
                'schsettings' => ['index', 'logo', 'miscellaneous', 'backendtheme', 'mobileapp', 'studentguardianpanel', 'fees', 'idautogeneration', 'attendancetype', 'maintenance', 'whatsappsettings'],
                'sessions' => ['index', 'edit'],
                'notification' => ['setting'],
                'smsconfig' => ['index'],
                'emailconfig' => ['index'],
                'paymentsettings' => ['index'],
                'print_headerfooter' => ['index'],
                'frontcms' => ['index'],
                'roles' => ['index', 'permission'],
                'admin' => ['backup', 'filetype'],
                'language' => ['index', 'create'],
                'currency' => ['index'],
                'users' => ['index'],
                'module' => ['index'],
                'customfield' => ['index', 'edit'],
                'captcha' => ['index'],
                'systemfield' => ['index'],
                'student' => ['profilesetting'],
                'onlineadmission' => ['admissionsetting'],
                'updater' => ['index'],
                'sidemenu' => ['index'],
                'addons' => ['index'],
                'thermalprint' => ['index'],
                'whatsappconfig' => ['index'],
            ],

            'gmeet_live_classes' => [
                'gmeet' => ['timetable', 'meeting', 'class_report', 'meeting_report', 'index'],
            ],

            'zoom_live_classes' => [
                'conference' => ['timetable', 'meeting', 'class_report', 'meeting_report', 'index'],
            ],

            'behaviour_records' => [
                'studentincidents' => ['index'],
                'incidents' => ['index'],
                'report' => ['behaviourreport', 'studentincidentreport', 'studentbehaviorsrankreport', 'classwiserankreport', 'classsectionwiserank', 'housewiserank', 'incidentwisereport'],
                'behavioursetting' => ['index'],
            ],

            'multi_branch' => [
                'branch' => ['overview', 'index'],
                'finance' => ['dailycollectionreport', 'payroll', 'incomelist', 'expenselist', 'incomereport', 'expensereport', 'userlogreport', 'index'],
            ],

            'two_factor_authentication' => [
                'admin' => ['setup', 'index'],
            ],

            'online_course' => [
                'course' => ['index', 'setting'],
                'coursecategory' => ['categoryadd', 'categoryedit'],
                'coursereport' => ['report', 'coursepurchase', 'coursesellreport', 'trendingreport', 'completereport', 'courseratingreport', 'guestlist', 'quizperformance', 'course_assignment_report', 'course_exam_result_report', 'course_exam_report', 'course_exam_attempt_report', 'result_report'],
                'offlinepayment' => ['payment'],
                'courseexamquestion' => ['index'],
                'coursetag' => ['index'],
                'coursecertificate' => ['createcertificate', 'templatelist', 'addcertificate'],
            ],

            'cbse_exam' => [
                'exam' => ['index', 'examtimetable', 'examwiserank', 'templatewiserank'],
                'result' => ['marksheet'],
                'grade' => ['gradelist'],
                'observation' => ['index', 'assign'],
                'observationparameter' => ['index', 'edit'],
                'assessment' => ['index'],
                'term' => ['index'],
                'template' => ['index', 'templatewiserank'],
                'report' => ['index', 'templatewise', 'examsubject'],
                'setting' => ['index'],
                'cbsecategory' => ['index', 'edit'],
                'cbseadmitcard' => ['index', 'admitcard', 'edit'],
            ],

            'qr_code_attendance' => [
                'attendance' => ['index', 'scannergun'],
                'qrsetting' => ['index'],
            ],

            'holiday' => [
                'holiday' => ['index', 'holidaytype', 'editholidaytype'],
            ],

            'student_cv' => [
                'resume' => ['index', 'download', 'resume_setting', 'student_resume_details'],
            ],

        ];
        if (array_key_exists($find_array, $array)) {
            return $array[$find_array];
        }

        return false;
    }

}

if (! function_exists('activate_main_menu')) {

    function activate_main_menu($menu, $class_active = 'active')
    {
        $CI = get_instance();
        $class = $CI->router->fetch_class();
        $method = $CI->router->fetch_method();

        $return_array = main_menu_array($menu);
        if ($return_array) {
            if (array_key_exists($class, $return_array)) {
                $a = $return_array[$class];

                if (! empty($a)) {
                    foreach ($a as $method_key => $method_value) {
                        if ($method_value == $method) {
                            return $class_active;
                            break;
                        }
                    }
                }
            }
        }
    }
}

if (! function_exists('activate_submenu')) {

    function activate_submenu($arg_class = '', $arg_methods = [], $class_active = 'active')
    {
        $CI = get_instance();

        // Getting router class to active.
        $class = $CI->router->fetch_class();
        $method = $CI->router->fetch_method();
        if (is_array($arg_methods)) {
            foreach ($arg_methods as $arg_methods_key => $arg_methods_value) {
                if ($method == $arg_methods_value && $class == $arg_class) {
                    return $class_active;
                    break;
                }
            }
        }
    }

}

function side_menu_list($list = -1)
{

    $CI = &get_instance();
    $CI->load->model('sidebarmenu_model');
    $result = $CI->sidebarmenu_model->getMenuwithSubmenus($list);

    return $result;

}

function access_permission_sidebar_remove_pipe($access_permissions)
{
    // remove pipe sign ||
    $module_permission = array_map('trim', explode('||', preg_replace('/\(\'|\'|\)/', '', $access_permissions)));

    return $module_permission;
}

function access_permission_remove_comma($m_permission_value)
{
    // remove pipe sign ||
    $module_permission_seprated = array_map('trim', explode(',', preg_replace('/\s+/', '', $m_permission_value)));

    return $module_permission_seprated;
}
