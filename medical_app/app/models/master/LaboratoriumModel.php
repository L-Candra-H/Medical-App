<?php
class LaboratoriumModel {
  public static function getAll($conn) {
    $result = $conn->query("SELECT * FROM laboratorium ORDER BY id DESC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }

  public static function insert($tipe, $asal, $conn) {
    $stmt = $conn->prepare("INSERT INTO laboratorium (tipe, asal_laboratorium) VALUES (?, ?)");
    if (!$stmt) return false;
    $stmt->bind_param("ss", $tipe, $asal);
    return $stmt->execute();
  }

  public static function find($id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM laboratorium WHERE id = ?");
    if (!$stmt) return null;
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  public static function update($id, $tipe, $asal, $conn) {
    $stmt = $conn->prepare("UPDATE laboratorium SET tipe = ?, asal_laboratorium = ? WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("ssi", $tipe, $asal, $id);
    return $stmt->execute();
  }

  public static function delete($id, $conn) {
    $stmt = $conn->prepare("DELETE FROM laboratorium WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("i", $id);
    return $stmt->execute();
  }

  public static function getNamaById($id, $conn) {
    if (!$id) return '-';
    $stmt = $conn->prepare("SELECT asal_laboratorium FROM laboratorium WHERE id = ?");
    if (!$stmt) return '-';
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if (!$result) return '-';
    return $result['asal_laboratorium'];
  }  

}