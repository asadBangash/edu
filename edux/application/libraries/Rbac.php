<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Rbac
{
    private $CI;

    protected array $permissions = [];

    public $perm_category;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->permissions = [];
        $this->CI->config->load('mailsms');
        $this->perm_category = $this->CI->config->item('perm_category');
    }

    public function hasPrivilege($category = null, $permission = null)
    {
        $roleJson = $this->CI->customlib->getStaffRole();
        if ($roleJson) {
            $logged = json_decode($roleJson);
            if ($logged && isset($logged->name) && $logged->name === 'Super Admin') {
                return true;
            }
            if ($logged && isset($logged->id) && (int) $logged->id === 7) {
                return true;
            }
        }

        $admin = $this->CI->session->userdata('admin');
        if (! $admin || ! isset($admin['roles']) || ! is_array($admin['roles']) || $admin['roles'] === []) {
            return false;
        }

        $roles = $admin['roles'];
        $role_key = key($roles);
        if ($role_key === null) {
            return false;
        }
        $role_id = $roles[$role_key];

        $role_perm = $this->CI->rolepermission_model->getPermissionByRoleandCategory($role_id, trim($category));

        if ($role_perm) {
            if (array_key_exists($permission, $role_perm)) {
                return $role_perm[$permission];
            }
        }

        return false;
    }

    public function module_permission($module_name)
    {
        $module_perm = $this->CI->Module_model->getPermissionByModulename($module_name);

        return $module_perm;
    }

    public function unautherized()
    {
        $this->CI->load->view('layout/header');
        $this->CI->load->view('unauthorized');
        $this->CI->load->view('layout/footer');
    }
}
