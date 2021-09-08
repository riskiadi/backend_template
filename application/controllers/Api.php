<?php
defined('BASEPATH') or exit('No direct script access allowed');


//Setting Currennt Date untuk lokasi di indonesia
class Api extends CI_Controller
{
  function __construct()
  {
    parent::__construct();
    $this->db->conn_id->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);
    date_default_timezone_set('Asia/Jakarta');
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
  }


  //Upload Gambar
  function updateImage()
  {
    $idUser = $this->input->post('iduser'); //iduser merupakan sebuah penamaan saja (value)
    $config['upload_path'] = './image_growback/'; // tipe string dan file
    $config['allowed_types'] = 'gif|jpg|png|jpeg';


    $this->load->library('upload', $config); // ini proses untuk memasukkan ke dalam libraries
    $this->db->where('id_user', $idUser); // id_user merupakan nama field pada databasenya



    if (!$this->upload->do_upload('image')) {
      $error = array('error' => $this->upload->display_errors());
      $data1 = array(
        'message' => $error,
        'status' => 404,
      );
    } else {
      //upload to folder
      $data = array('upload_data' => $this->upload->data()); //proses upload

      //upload to database
      $save['photo_user'] = $data['upload_data']['file_name']; //ini proses untuk menyimpan ke databasenya
      $query = $this->db->update('oc_customer', $save);

      //Output Request
      $data1 = array(
        'message' => "Successfully Upload News",
        'status' => 200,
        'data' => $data['upload_data']['file_name'],
      );
    }
    echo json_encode($data1);
  }



  function changeBackgroundImg()
  {
    $iduser = $this->input->post('id');
    $namafile = "";
    if (!empty($_FILES['userfile'])) {
      $hasil = $this->upload_photo('image_growback');

      if ($hasil['result'] == 'false') {
        $data['result'] = 'false';
        $data['msg'] = $hasil['msg'];

        echo json_encode($data);
        return;
      } else {
        $namafile = $hasil['namafile'];
      }
    } else {
      echo "kosong";
    }

    $this->db->where('user_id', $iduser);

    $update['user_image'] = $namafile;

    $q = $this->db->update('tb_user', $update);
    if ($q) {
      $data['message'] = 'success';
      $data['status'] = 200;
    } else {
      $data['message'] = 'error';
      $data['status'] = 404;
    }

    echo json_encode($data);
  }



  function addShippingAddress()
  {
    $req = [
      "user_id" => $this->input->post("user_id"),
      "title" => $this->input->post("title"),
      "city" => $this->input->post("city"),
      "province" => $this->input->post("province"),
      "address" => $this->input->post("address"),
      "zip_code" => $this->input->post("zip_code")
    ];

    $isSuccess = $this->db->insert("shipping_addresses", $req);

    if ($isSuccess) {
      $data['message'] = $isSuccess;
      $data['satus'] = 200;
    } else {
      $data['message'] = $isSuccess;
      $data['satus'] = 404;
    }

    echo json_encode($data);
  }

  function updateShippingAddress()
  {
    $req = array(
      'id' => $this->input->post('id'),
      "user_id" => $this->input->post("user_id"),
      "title" => $this->input->post("title"),
      "city" => $this->input->post("city"),
      "province" => $this->input->post("province"),
      "address" => $this->input->post("address"),
      "zip_code" => $this->input->post("zip_code")
    );

    $this->db->where('id', $req['id']);
    $isSuccess = $this->db->update("shipping_addresses", $req);

    if ($isSuccess) {
      $data['message'] = $isSuccess;
      $data['satus'] = 200;
    } else {
      $data['message'] = $isSuccess;
      $data['satus'] = 404;
    }

    echo json_encode($data);
  }


  //   // Update order_status
  //   function updateOrderStatus(){
  //     $idOrder = $this->input->post('idOrder');
  //     // $orderUser = $this->input->post('orderUser');

  //     $this->db->where('order_id',$idOrder);


  //     $simpan['order_status'] = 1;
  //     // $simpan['order_user'] = $orderUser;
  //     $simpan['order_tanggal'] = date('Y-m-d H:i:s'); 
  //     $q = $this->db->update('tb_order',$simpan);

  //     if($q){
  //         $data['message'] = 'success';
  //         $data['satus'] = 200;
  //     } else {
  //         $data['message'] = 'error';
  //         $data['satus'] = 404;
  //     }

  //     echo json_encode($data);
  // }


  function getHistory()
  {
    $arr = array(
      'detail_status' => 1,
      'detail_user' => $this->input->post('iduser'),
    );

    // $this->db->select('detail_id,detail_order,detail_qty,detail_harga,produk_nama,produk_id,produk_harga, deskripsi_produk, produk_gambar,order_id,order_user,order_tanggal,order_alamatUser,order_status,order_total');
    $this->db->select('*');
    $this->db->from('tb_detailOrder');
    $this->db->join('tb_produk', 'tb_produk.produk_id = tb_detailOrder.detail_produk');
    $this->db->join('tb_order', 'tb_order.order_user = tb_detailOrder.detail_user AND tb_order.idcheckout = tb_detailOrder.detail_order');
    $this->db->where($arr);
    $q = $this->db->get();
    if ($q->num_rows() > 0) {
      $data = array(
        'message' => "Successfully Get Data",
        'status' => true,
        'dataHistory' => $q->result(),
      );
    } else {
      $data = array(
        'message' => "Failed Get Data",
        'status' => false,
      );
    }
    echo json_encode($data);
  }




  //Get keranjang
  function getKeranjang()
  {
    $arr = array(
      'detail_user' => $this->input->post('iduser'),
      'detail_status' => 0,
    );
    header("Content-Type: application/json");
    $this->db->select('*');
    $this->db->from('tb_detailOrder');
    $this->db->join('tb_produk', 'tb_produk.produk_id = tb_detailOrder.detail_produk');
    $this->db->where($arr);
    $q = $this->db->get();
    // $q = $this->db->get('tb_detailOrder');

    if ($q->num_rows() > 0) {
      $data = array(
        'message' => "Successfully getKeranjang",
        'status' => true,
        'dataKeranjang' => $q->result(),
      );
    } else {
      $data = array(
        'message' => "Failed getKeranjang",
        'status' => false,
      );
    }
    echo json_encode($data);
  }



  //Delete Item Cart
  function deleteItem()
  {
    $detailid = $this->input->post('detailid');
    $this->db->where('detail_id', $detailid);

    $status = $this->db->delete('tb_detailOrder');
    if ($status == true) {
      $response['pesan'] = 'hapus berhasil';
      $response['status'] = 200;
    } else {
      $response['pesan'] = 'hapus error';
      $response['status'] = 404;
    }
    echo json_encode($response);
  }


  //Update QTY TOTAL Cart 
  function updateQty()
  {
    $arr = array(
      'detail_qty' => $this->input->post('qty'),
      'detail_total' => $this->input->post('total'),
      'detail_id' => $this->input->post('detailid'),
    );
    $this->db->where('detail_id', $arr['detail_id']);
    $q = $this->db->update('tb_detailOrder', $arr);

    if ($q) {
      $data['message'] = 'success';
      $data['satus'] = 200;
    } else {
      $data['message'] = 'error';
      $data['satus'] = 404;
    }

    echo json_encode($data);
  }


  // Fungsi Add Cart
  function addCart()
  {

    $qty = $this->input->post('qty');
    $price = $this->input->post('price');
    $idproduct = $this->input->post('idproduct');
    $code = $this->input->post('code');
    $iduser = $this->input->post('iduser');

    $checkArr = array(
      'detail_user' => $iduser,
      'detail_status' => 0,
      'detail_produk' => $idproduct
    );

    $query = $this->db->get_where('tb_detailOrder', $checkArr);


    if ($query->num_rows() > 0) {
      $row = $query->row();
      if (isset($row)) {

        $arr = array(
          'detail_qty' => $row->detail_qty + 1,
          'detail_total' => (int)$row->detail_harga * ($row->detail_qty + 1),
          'detail_id' => $row->detail_id,
        );
        $this->db->where('detail_id', $row->detail_id);
        $query = $this->db->update('tb_detailOrder', $arr);
        if ($query) {
          $data['message'] = 'Successfully Add to cart';
          $data['satus'] = true;
        } else {
          $data['message'] = 'Failed Add to cart';
          $data['satus'] = false;
        }
      }
    } else {

      $saveArr = array(
        'detail_order' => $code,
        'detail_produk' => $idproduct,
        'detail_qty' => $qty,
        'detail_harga' => $price,
        'detail_total' => (int)$qty * (int)$price,
        'detail_user' => $iduser,
      );

      $query = $this->db->insert('tb_detailOrder', $saveArr);

      if ($query) {
        $data = array(
          'message' => "Successfully Add to cart",
          'status' => true,
        );
      } else {
        $data = array(
          'message' => "Failed Add to cart",
          'status' => false,
        );
      }
    }

    echo json_encode($data);
  }

  function checkoutOrder()
  {
    $req = array(
      'detail_order' => $this->input->post('code'),
      'detail_status' => 1,
    );

    $reqOrder = array(
      'order_tanggal' => date('Y-m-d H:i:s'),
      'order_user' => $this->input->post('iduser'),
      'order_alamatUser' => $this->input->post('alamat'),
      'order_total' => $this->input->post('total'),
      'order_status' => 1,
      'idcheckout' => $this->input->post('code'),
    );

    $this->db->where('detail_order', $req['detail_order']);
    $result =  $this->db->get('tb_detailOrder');
    $isSuccess = false;

    if ($result->num_rows() > 0) {
      $this->db->where('detail_order', $req['detail_order']);
      $isSuccess = $this->db->update('tb_detailOrder', $req);
    }

    if ($isSuccess == true) {
      $resInsert = $this->db->insert('tb_order', $reqOrder);
      if ($resInsert) {
        $data = array(
          'message' => "Successfully Checkout Order",
          'status' => $isSuccess,
        );
      } else {
        $data = array(
          'message' => "Failed Checkout Order",
          'status' => $isSuccess,
        );
      }
    } else {
      $data = array(
        'message' => "Failed Checkout Order",
        'status' => $isSuccess,
      );
    }

    echo json_encode($data);
  }






  //Fungsi Promosi
  function promosi()
  {
    $this->db->where('is_promote', true);
    $q = $this->db->get('tb_produk');

    if ($q->num_rows() > 0) {
      $data['message'] = 'success';
      $data['status'] = 200;
      $data['data'] = $q->result();
    } else {
      $data['message'] = 'error';
      $data['status'] = 404;
    }
    echo json_encode($data);
  }

  //Fungsi Popular
  function populer()
  {
    $this->db->where_in('produk_rating', array(4, 5));
    $q = $this->db->get('tb_produk');

    if ($q->num_rows() > 0) {
      $data['message'] = 'success';
      $data['status'] = 200;
      $data['data'] = $q->result();
    } else {
      $data['message'] = 'error';
      $data['status'] = 404;
    }
    echo json_encode($data);
  }


  //Fungsi Produk Kategori
  function produkPerKategori()
  {
    $this->db->where('produk_kategori', $this->input->post('id'));
    $q = $this->db->get('tb_produk');

    if ($q->num_rows() > 0) {
      $func = function ($value) {
        $value->is_promote = false;
        if ($value->is_promote != 1) {
          $value->is_promote = true;
        }
        return $value->is_promote;
      };
      $tempArr = $q->result();
      array_map($func, $tempArr);
      $data['message'] = 'success';
      $data['status'] = 200;
      $data['dataProduct'] = $tempArr;
    } else {
      $data['message'] = 'error';
      $data['status'] = 404;
    }
    echo json_encode($data);
  }



  //Fungsi Get Kategori
  function getKategori()
  {
    $q = $this->db->get('tb_kategori');

    if ($q->num_rows() > 0) {
      $data['message'] = 'success';
      $data['status'] = 200;
      $data['data'] = $q->result();
    } else {
      $data['message'] = 'error';
      $data['status'] = 404;
    }
    echo json_encode($data);
  }


  //Search Produk
  function searchProduk()
  {
    $keyword = $this->input->post('keyword');
    $this->db->like('produk_nama', $keyword);

    $q = $this->db->get('tb_produk');

    if ($q->num_rows() > 0) {
      $data['message'] = 'success';
      $data['status'] = 200;
      $data['data'] = $q->result();
    } else {
      $data['message'] = 'tidak ditemukan';
      $data['status'] = 404;
    }

    echo json_encode($data);
  }



  //Fungsi Get Produk
  function getProduk()
  {
    $q = $this->db->get('tb_produk');
    if ($q->num_rows() > 0) {
      $func = function ($value) {
        $value->is_promote = false;
        if ($value->is_promote != 1) {
          $value->is_promote = true;
        }
        return $value->is_promote;
      };
      $tempArr = $q->result();
      array_map($func, $tempArr);
      $data['message'] = 'success';
      $data['status'] = 200;
      $data['dataProduct'] = $tempArr;
    } else {
      $data['message'] = 'error';
      $data['status'] = 404;
    }
    echo json_encode($data);
  }



  //Fungsi Update HP User (Verify Phone)
  function updateHpUser()
  {
    $idUser = $this->input->post('idUser');
    $hp = $this->input->post('hp');

    $this->db->where('user_id', $idUser);

    $simpan['user_hp'] = $hp;

    $q = $this->db->update('tb_user', $simpan);

    if ($q) {
      $data['message'] = 'success';
      $data['satus'] = 200;
    } else {
      $data['message'] = 'error';
      $data['satus'] = 404;
    }

    echo json_encode($data);
  }


  //Fungsi Login Customer
  function loginCustomer()
  {
    $email = $this->input->post('email');
    $password = $this->input->post('password');

    $this->db->where('user_email', $email);
    $this->db->where('user_password', md5($password));
    $this->db->where('user_level', 1);

    $q = $this->db->get('tb_user');

    if ($q->num_rows() > 0) {
      $data['message'] = 'Login Success';
      $data['status'] = 200;
      $data['user'] = $q->row();
    } else {
      $data['message'] = 'Email atau Password Salah';
      $data['status'] = 404;
    }
    echo json_encode($data);
  }



  //Fungsi Register Customer
  function registerCustomer()
  {
    $nama = $this->input->post('nama');
    $email = $this->input->post('email');
    $password = $this->input->post('password');
    $hp = $this->input->post('hp');
    $level = 1;

    $this->db->where('user_email', $email);
    $this->db->or_where('user_hp', $hp);

    $q = $this->db->get('tb_user');
    if ($q->num_rows() > 0) {
      $data['message'] = 'Email atau Hp sudah terdaftar, silahkan Sign In';
      $data['status'] = 404;
    } else {

      $simpan['user_nama'] = $nama;
      $simpan['user_email'] = $email;
      $simpan['user_password'] = md5($password);
      $simpan['user_hp'] = $hp;
      $simpan['user_tanggal'] = date('Y-m-d H:i:s');
      $simpan['user_level'] = $level;

      $q = $this->db->insert('tb_user', $simpan);
      if ($q) {
        $data['message'] = 'success';
        $data['status'] = 200;
      } else {
        $data['message'] = 'error';
        $data['status'] = 404;
      }
    }
    echo json_encode($data);
  }

  //function get provinsi
  function getProvince()
  {
    $q = $this->db->get('system_local_province');
    if ($q->num_rows() > 0) {
      $data = $q->result();
    } else {

      $data['message'] = 'Failed to Get Data Province';
      $data['status'] = 404;
    }
    echo json_encode($data);
  }

  // function get City
  function getCity()
  {
    $id = $this->input->post('id');

    $this->db->where('province_id', $id);

    $q = $this->db->get('system_local_city');

    if ($q->num_rows() > 0) {
      $data = $q->result();
    } else {
      $data['message'] = 'tidak ditemukan';
      $data['status'] = 404;
    }

    echo json_encode($data);
  }

  //function get kecamatan
  function getKecamatan()
  {
    $id = $this->input->post('id');

    $this->db->where('city_id', $id);

    $q = $this->db->get('system_local_kecamatan');

    if ($q->num_rows() > 0) {
      $data = $q->result();
    } else {
      $data['message'] = 'tidak ditemukan';
      $data['status'] = 404;
    }

    echo json_encode($data);
  }

  function getDataShipping()
  {
    $this->db->where('user_id', $this->input->post('iduser'));

    $q = $this->db->get('shipping_addresses');

    if ($q->num_rows() > 0) {
      $data['message'] = 'success';
      $data['status'] = 200;
      $data['shipping'] = $q->result();
    } else {
      $data['message'] = 'error';
      $data['status'] = 404;
    }

    echo json_encode($data);
  }
  
  function getInvoice()
  {
    $idUser = $this->input->post('iduser');
    $orderCode = $this->input->post('ordercode');
    $data = [];
    $this->db->select('tb_detailOrder.*, tb_produk.produk_nama, tb_produk.produk_gambar');
    $this->db->from('tb_detailOrder');
    $this->db->join('tb_produk', 'tb_produk.produk_id = tb_detailOrder.detail_produk');
    $this->db->where('detail_user', $idUser);
    $this->db->where('detail_order', $orderCode);
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
      $data['message'] = 'success';
      $data['status'] = 200;
      $data["invoice"] = $query->result();
    } else {
      $data['message'] = 'error';
      $data['status'] = 404;
    }

    echo json_encode($data);
  }
  
}