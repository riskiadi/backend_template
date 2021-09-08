<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// $sql = "SELECT * FROM some_table WHERE id = ? AND status = ? AND author = ?";
// $this->db->query($sql, array(3, 'live', 'Rick'));

class Api extends CI_Controller {
    function __construct(){
        parent::__construct();

        date_default_timezone_set('Asia/Jakarta');
        error_reporting(E_ALL);
        ini_set('display_errors',1);
    }

    //Fungsi Register
    function registerCustomer(){
        $nama = $this->input->post('nama'); 
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        $hp = $this->input->post('hp');
        $tanggal = date('Y - m - d H : i : s');
        $level = 1;

        $this->db->where('user_email',$email);
        $this->db->or_where('user_hp',$hp);

        $q = $this->db->get('tb_user');
        if($q -> num_rows() > 0){
            $data['message'] = 'Email atau Hp sudah terdaftar, silahkan Sign In';
            $data['status'] = 404;
        } 
        else{

            $simpan['user_nama'] = $nama;
            $simpan['user_email'] = $email;
            $simpan['user_password'] = md5($password);
            $simpan['user_hp'] = $hp;
            $simpan['user_tanggal'] = $tanggal;
            $simpan['user_level'] = $level;

            $q = $this->db->insert('tb_user', $simpan);
            if($q){
                $data['message'] = 'success';
                $data['status'] = 200;
            } else {
                $data['message'] = 'error';
                $data['status'] = 404;        
            }
        }
        echo json_encode($data);
    }


    function loginCustomer(){
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        $this->db->

        $this->db->where('user_email',$email);
        $this->db->where('user_password',md5($password));
        $this->db->where('user_level',1);

        $q = $this->db->get('tb_user');

        if($q -> num_rows() > 0){
            $data['message'] = 'Login Success';
            $data['status'] = 200;
            $data['user'] = $q -> row();
        } else {
            $data['message'] = 'Email atau Password Salah';
            $data['status'] = 404;
        }
        echo json_encode($data);
    }
}