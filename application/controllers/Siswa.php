<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Siswa extends CI_Controller {
  private $filename = "import_data"; // Kita tentukan nama filenya
  
  public function __construct(){
    parent::__construct();
    
    $this->load->model('SiswaModel');
  }
  
  public function index(){
    $data['siswa'] = $this->SiswaModel->view();
    $this->load->view('view', $data);
  }
  
  public function form(){
    $data = array(); // Buat variabel $data sebagai array
    
    if(isset($_POST['preview'])){ // Jika user menekan tombol Preview pada form
      // lakukan upload file dengan memanggil function upload yang ada di SiswaModel.php
      $upload = $this->SiswaModel->upload_file($this->filename);
      
      if($upload['result'] == "success"){ // Jika proses upload sukses
        // Load plugin PHPExcel nya
        include APPPATH.'third_party/PHPExcel/PHPExcel.php';
        
        $csvreader = PHPExcel_IOFactory::createReader('CSV');
        $loadcsv = $csvreader->load('csv/'.$this->filename.'.csv'); // Load file yang tadi diupload ke folder csv
        $sheet = $loadcsv->getActiveSheet()->getRowIterator();
        
        // Masukan variabel $sheet ke dalam array data yang nantinya akan di kirim ke file form.php
        // Variabel $sheet tersebut berisi data-data yang sudah diinput di dalam csv yang sudha di upload sebelumnya
        $data['sheet'] = $sheet; 
      }else{ // Jika proses upload gagal
        $data['upload_error'] = $upload['error']; // Ambil pesan error uploadnya untuk dikirim ke file form dan ditampilkan
      }
    }
    
    $this->load->view('form', $data);
  }
  
  public function import(){
    // Load plugin PHPExcel nya
    include APPPATH.'third_party/PHPExcel/PHPExcel.php';
    
    $csvreader = PHPExcel_IOFactory::createReader('CSV');
    $loadcsv = $csvreader->load('csv/'.$this->filename.'.csv'); // Load file yang tadi diupload ke folder csv
    $sheet = $loadcsv->getActiveSheet()->getRowIterator();
    
    // Buat sebuah variabel array untuk menampung array data yg akan kita insert ke database
    $data = array();
    
    $numrow = 1;
    foreach($sheet as $row){
      // Cek $numrow apakah lebih dari 1
      // Artinya karena baris pertama adalah nama-nama kolom
      // Jadi dilewat saja, tidak usah diimport
      if($numrow > 1){
        // START -->
        // Skrip untuk mengambil value nya
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
        
        $get = array(); // Valuenya akan di simpan kedalam array,dimulai dari index ke 0
        foreach ($cellIterator as $cell) {
          array_push($get, $cell->getValue()); // Menambahkan value ke variabel array $get
        }
        // <-- END
        
        // Ambil data value yang telah di ambil dan dimasukkan ke variabel $get
        $nis = $get[0]; // Ambil data NIS dari kolom A di csv
        $nama = $get[1]; // Ambil data nama dari kolom B di csv
        $jenis_kelamin = $get[2]; // Ambil data jenis kelamin dari kolom C di csv
        $alamat = $get[3]; // Ambil data alamat dari kolom D di csv
        
        // Kita push (add) array data ke variabel data
        array_push($data, array(
          'nis'=>$nis, // Insert data nis
          'nama'=>$nama, // Insert data nama
          'jenis_kelamin'=>$jenis_kelamin, // Insert data jenis kelamin
          'alamat'=>$alamat, // Insert data alamat
        ));
      }
      
      $numrow++; // Tambah 1 setiap kali looping
    }
    // Panggil fungsi insert_multiple yg telah kita buat sebelumnya di model
    $this->SiswaModel->insert_multiple($data);
    
    redirect("Siswa"); // Redirect ke halaman awal (ke controller siswa fungsi index)
  }
}





