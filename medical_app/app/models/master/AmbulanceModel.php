<?php
class AmbulanceModel {
  public static function getAll($conn) {
    $result = $conn->query("SELECT * FROM ambulance ORDER BY id DESC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }

  public static function insert($tipe, $asal, $conn) {
    $stmt = $conn->prepare("INSERT INTO ambulance (tipe, asal_ambulance) VALUES (?, ?)");
    if (!$stmt) return false;
    $stmt->bind_param("ss", $tipe, $asal);
    return $stmt->execute();
  }

  public static function find($id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM ambulance WHERE id = ?");
    if (!$stmt) return null;
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  public static function update($id, $tipe, $asal, $conn) {
    $stmt = $conn->prepare("UPDATE ambulance SET tipe = ?, asal_ambulance = ? WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("ssi", $tipe, $asal, $id);
    return $stmt->execute();
  }

  public static function delete($id, $conn) {
    $stmt = $conn->prepare("DELETE FROM ambulance WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("i", $id);
    return $stmt->execute();
  }

  public static function getTarif($id, $conn) {
    $stmt = $conn->prepare("SELECT tarif FROM ambulance WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['tarif'] ?? 0;
  }

  public static function getNamaById($id, $conn) {
    if (!$id) return '-';
    $stmt = $conn->prepare("SELECT asal_ambulance FROM ambulance WHERE id = ?");
    if (!$stmt) return '-';
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if (!$result) return '-';
    return $result['asal_ambulance'];
  }
  
}