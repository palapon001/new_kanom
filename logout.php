<?php
session_start();

// ล้างข้อมูล Session ทั้งหมด
session_unset();
session_destroy();

// เด้งกลับไปหน้า Login
header("Location: login.php");
exit();
?>