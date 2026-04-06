<?php
class Profil_model extends CI_Model {

  // Ambil data gabungan dari kasir, users, dan jabatan
  public function getProfil($user_id) {
    $this->db->select('u.username, k.nama_petugas, k.nip, j.nama_jabatan');
    $this->db->from('users u');
    $this->db->join('kasir k', 'k.user_id = u.id');
    $this->db->join('jabatan j', 'j.id = k.jabatan_id', 'left');
    $this->db->where('u.id', $user_id);
    return $this->db->get()->row_array();
  }

  // Update data petugas
  public function updatePetugas($user_id, $data) {
    $this->db->where('user_id', $user_id);
    $this->db->update('kasir', $data);
  }

  // Update password login
  public function updatePassword($user_id, $hashed_password) {
    $this->db->where('id', $user_id);
    $this->db->update('users', ['password' => $hashed_password]);
  }
}
