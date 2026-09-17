<?php
require_once '../../config/auth.php'; require_once '../../config/database.php'; requireRole('admin');
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location: index.php');exit;}$id=(int)($_POST['id']??0);$classId=(int)($_POST['class_id']??0);$yearId=(int)($_POST['academic_year_id']??0);$start=$_POST['tanggal_mulai']??'';$end=$_POST['tanggal_selesai']??'';$status=$_POST['status']??'';
if(!$id||!$classId||!$yearId||!$start||!in_array($status,['aktif','nonaktif','menunggu'],true)){header('Location: edit.php?id='.$id.'&error='.urlencode('Data tidak valid.'));exit;}
try{$stmt=$pdo->prepare('UPDATE class_officers SET class_id=?,academic_year_id=?,tanggal_mulai=?,tanggal_selesai=?,status=? WHERE id=?');$stmt->execute([$classId,$yearId,$start,$end?:null,$status,$id]);header('Location: index.php?success=edit');}catch(PDOException $e){header('Location: edit.php?id='.$id.'&error='.urlencode('Gagal memperbarui data pengurus.'));}exit;
