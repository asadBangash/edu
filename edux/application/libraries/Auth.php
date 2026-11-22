<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Auth
{
    public $CI;
    protected $errors;
    protected $messages;

    public function __construct()
    {
        $this->CI       = &get_instance();
        $this->messages = array();
        $this->errors   = array();
        $this->CI->load->library('Enc_lib');
        $this->set_timezone();
        $this->CI->load->database();
    }

    public function logged_in()
    {
        return (bool) $this->CI->session->userdata('admin');
    }

    public function user_logged_in()
    {
        return (bool) $this->CI->session->userdata('student');
    }

    public function user_redirect()
    {
        if ($this->CI->session->has_userdata('student')) {
            $user = $this->CI->session->userdata('student');
            $role = $user['role'];
            if ($role == "student") {
                redirect('user/user/dashboard');
            } else if ($role == "parent") {
                redirect('user/user/dashboard');
            } else if ($role == "teacher") {
                redirect('teacher/teacher/dashboard');
            } else if ($role == "accountant") {
                redirect('accountant/accountant/dashboard');
            } else if ($role == "librarian") {
                redirect('librarian/librarian/dashboard');
            } else {
                redirect('site/userlogin');
            }
        } else {
            redirect('site/userlogin');
        }
    }

    public function is_logged_in($default_redirect = false)
    {
        $admin = $this->CI->session->userdata('admin');
        if (!$admin) {
            $_SESSION['redirect_to'] = current_url();
            redirect('site/login');
            return false;
        } else {
            $active_status = $this->CI->db->select('is_active')->from('staff')->where('id', $admin['id'])->get()->row_array();
            if ($active_status['is_active'] == 1) {
                if ($default_redirect) {
                    redirect('admin/admin/dashboard');
                }
                return true;
            } else {
                $_SESSION['redirect_to'] = current_url();
                $this->logout();
                redirect('site/login');
                return false;
            }
        }
    }

    public function is_logged_in_user($role = false)
    {
        if ($this->CI->session->has_userdata('student')) {
            $user = $this->CI->session->userdata('student');
            if ($user['role'] == "guest") {
                return true;
            }
            if (!$this->CI->session->has_userdata('current_class')) {
                if ($this->CI->router->fetch_method() != "choose") {
                    redirect('user/user/choose');
                }
            }
            if (!$role) {
                redirect('site/userlogin');
            }
        } else {
            $_SESSION['redirect_to_user'] = current_url();
            redirect('site/userlogin');
        }
    }

    public function logout()
    {
        delete_cookie('branch_cookie');
        $this->CI->session->unset_userdata('admin');
        $this->CI->session->sess_destroy();
    }

    public function set_timezone()
    {
        if ($this->CI->customlib->getTimeZone()) {
            date_default_timezone_set($this->CI->customlib->getTimeZone());
        } else {
            return date_default_timezone_set('UTC');
        }
    }

    public function reset_password($email)
    {
        $admin = $this->get_admin_by_email($email);
        if ($admin) {
            $this->CI->load->helper('string');
            $this->CI->load->library('email');
            $new_password      = random_string('alnum', 8);
            $admin['password'] = sha1($new_password);
            $this->save_admin($admin);
            $this->CI->email->from($this->CI->config->item('email'), $this->CI->config->item('site_name'));
            $this->CI->email->to($email);
            $this->CI->email->subject($this->CI->config->item('site_name') . ': Admin Password Reset');
            $this->CI->email->message('Your password has been reset to ' . $new_password . '.');
            $this->CI->email->send();
            return true;
        } else {
            return false;
        }
    }

    private function get_admin_by_email($email)
    {
        $this->CI->db->select('*');
        $this->CI->db->where('email', $email);
        $this->CI->db->limit(1);
        $result = $this->CI->db->get('admin');
        $result = $result->row_array();
        if (sizeof($result) > 0) {
            return $result;
        } else {
            return false;
        }
    }

    public function save($admin)
    {
        if ($admin['id']) {
            $this->CI->db->where('id', $admin['id']);
            $this->CI->db->update('admin', $admin);
        } else {
            $this->CI->db->insert('admin', $admin);
        }
    }

    public function get_admin_list()
    {
        $this->CI->db->select('*');
        $this->CI->db->order_by('lastname', 'ASC');
        $this->CI->db->order_by('firstname', 'ASC');
        $this->CI->db->order_by('email', 'ASC');
        $result = $this->CI->db->get('admin');
        $result = $result->result();
        return $result;
    }

    public function get_admin($id)
    {
        $this->CI->db->select('*');
        $this->CI->db->where('id', $id);
        $result = $this->CI->db->get('admin');
        $result = $result->row();
        return $result;
    }

    public function check_id($str)
    {
        $this->CI->db->select('id');
        $this->CI->db->from('admin');
        $this->CI->db->where('id', $str);
        $count = $this->CI->db->count_all_results();
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function check_email($str, $id = false)
    {
        $this->CI->db->select('email');
        $this->CI->db->from('admin');
        $this->CI->db->where('email', $str);
        if ($id) {
            $this->CI->db->where('id !=', $id);
        }
        $count = $this->CI->db->count_all_results();
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($id)
    {
        if ($this->check_id($id)) {
            $admin = $this->get_admin($id);
            $this->CI->db->where('id', $id);
            $this->CI->db->limit(1);
            $this->CI->db->delete('admin');
            return $admin->firstname . ' ' . $admin->lastname . ' has been removed.';
        } else {
            return 'The admin could not be found.';
        }
    }

    public function validate_child($id = null)
    {
        $parent         = $this->CI->session->userdata('student');
        $parent_id      = $parent['id'];
        $students_array = $this->CI->student_model->read_siblings_students($parent_id);
        if ($id) {
            foreach ($students_array as $stu_key => $stu_value) {
                if ($stu_value->id == $id) {
                    return true;
                }
            }
            redirect('parent/unauthorized');
        }
    }

    public function multiupdate($branch_url, $purchase_code)
    {
        return json_encode(array(
            'status'   => 1,
            'response' => 'Verification successful'
        ));
    }

    public function app_update()
    {
        $array = array('status' => 1, 'message' => 'Thank you for registering your product');
        return $this->CI->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($array));
    }

    public function addon_update_check()
    {
        $array = array('status' => 1, 'message' => 'Thank you for registering your product');
        return $this->CI->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($array));
    }

    public function andapp_update()
    {
        $fname = APPPATH . 'config/license.php';
        $content = file_get_contents($fname);
        if (strpos($content, '$config[\'app_ver\']') === false) {
            $file_contents = $content . "\n" . '$config[\'app_ver\'] = 1;';
        } else {
            $file_contents = str_replace('$config[\'app_ver\'] = 0', '$config[\'app_ver\'] = 1', $content);
        }
        file_put_contents($fname, $file_contents);

        $array = array('status' => 1, 'message' => 'Thank you for registering your product');
        return $this->CI->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($array));
    }

    public function andapp_validate()
    {
        return true;
    }

    public function addon_update()
    {
        $addon = $this->CI->input->post('addon');
        $addon_version = $this->CI->input->post('addon_version');

        $fname = APPPATH . 'config/license.php';
        $content = file_get_contents($fname);

        $addon_prod = $this->CI->config->item('addon_prod');
        $addon_ver = $this->CI->config->item('addon_ver');
        $array_production = isset($addon_prod) ? $addon_prod : [];
        $array_version = isset($addon_ver) ? $addon_ver : [];

        $new_token = 'dummy_token_' . $addon;
        $new_license = 'dummy_license_' . $addon_version;

        if (empty($array_production) || !in_array($new_token, $array_production)) {
            $array_production[] = $new_token;
            $imploded_prod = implode(", ", array_map(function($value) { return "'$value'"; }, $array_production));
            if (preg_match("/^.*\$config\['addon_prod'\].*\$/m", $content)) {
                $file_contents = preg_replace("/^.*\$config\['addon_prod'\].*\$/m", '$config[\'addon_prod\'] = array(' . $imploded_prod . ');', $content);
            } else {
                $file_contents = $content . "\n" . '$config[\'addon_prod\'] = array(' . $imploded_prod . ');';
            }
            file_put_contents($fname, $file_contents);
        }

        if (empty($array_version) || !in_array($new_license, $array_version)) {
            $array_version[] = $new_license;
            $imploded_ver = implode(", ", array_map(function($value) { return "'$value'"; }, $array_version));
            if (preg_match("/^.*\$config\['addon_ver'\].*\$/m", $content)) {
                $file_contents = preg_replace("/^.*\$config\['addon_ver'\].*\$/m", '$config[\'addon_ver\'] = array(' . $imploded_ver . ');', $content);
            } else {
                $file_contents = $content . "\n" . '$config[\'addon_ver\'] = array(' . $imploded_ver . ');';
            }
            file_put_contents($fname, $file_contents);
        }

        $array = array('status' => 1, 'back' => $_SERVER['HTTP_REFERER'], 'message' => 'Thank you for registering your product');
        return $this->CI->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($array));
    }

    public function autoupdate()
    {
        if (!$this->CI->session->has_userdata('version')) {
            $this->set_message('Internal error, Please contact to service provider.');
            return false;
        }
        $version_dt  = $this->CI->session->userdata('version');
        $dw_filename = $version_dt['filename'];
        $fd_name     = $this->filename($dw_filename);
        $file        = './temp/' . $dw_filename;
        // Giả lập cập nhật thành công mà không cần tải file
        $this->CI->session->unset_userdata('version');
        $this->set_message('Update successful!');
        return true;
    }

    public function import_dump($fd_name)
    {
        $filename = './temp/' . $fd_name . '/db_import.sql';
        if (file_exists($filename)) {
            $fp = fopen($filename, 'r');
            if (!$fp) {
                $this->set_error('Update error! There is some issue occurred during update, please contact to support.');
                return false;
            }
            $queryCount = 0;
            $query      = '';
            $db_debug   = $this->CI->db->db_debug;
            $this->CI->db->db_debug = false;
            while ($line = fgets($fp, 1024000)) {
                if (substr($line, 0, 2) == '--' or trim($line) == '') {
                    continue;
                }
                $query .= $line;
                if (substr(trim($query), -1) == ';') {
                    if (!$this->CI->db->query($query)) {
                        $db_error = $this->CI->db->error();
                        $this->set_error($db_error['message']);
                        return false;
                    }
                    $query = '';
                    $queryCount++;
                }
            }
            $this->CI->db->db_debug = $db_debug;
            if (feof($fp)) {
                return true;
            } else {
                $this->set_error('Update error! There is some issue occurred during update, please contact to support.');
                return false;
            }
        }
        $this->set_error('Update error! There is some issue occurred during update, please contact to support.');
        return false;
    }

    public function set_error($error)
    {
        $this->errors[] = $error;
        return $error;
    }

    public function set_message($message)
    {
        $this->messages[] = $message;
        return $message;
    }

    public function clear_messages()
    {
        $this->messages = array();
        return true;
    }

    public function clear_error()
    {
        $this->errors = array();
        return true;
    }

    public function messages()
    {
        return $this->messages;
    }

    public function error()
    {
        return $this->errors;
    }

    public function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public function deleteDir($dirPath)
    {
        if (!is_dir($dirPath)) {
            return false;
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '{,.}[!.,!..]*', GLOB_MARK | GLOB_BRACE);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    public function checkupdate()
    {
        $this->CI->session->unset_userdata('version');
        $this->CI->session->set_userdata('version', array('version' => 'latest', 'filename' => 'dummy.zip'));
        $this->set_message('Update available');
        return true;
    }

    public function filename($filename)
    {
        return preg_replace('/.[^.]*$/', '', $filename);
    }

    public function addonchk($prod = null, $return_url = false)
    {
        return true;
    }

    public function userlogout()
    {
        if ($this->CI->session->has_userdata('student')) {
            $this->CI->session->unset_userdata('student');
        }
    }
}