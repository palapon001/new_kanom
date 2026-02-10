<?php
// -------------------------------------------------------------------------
// 1. Path Management Logic
// -------------------------------------------------------------------------
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$sub_folders = ['shop', 'admin', 'supplier', 'users'];
$path_prefix = in_array($current_dir, $sub_folders) ? '../' : '';

// หมายเหตุ: มั่นใจว่าไฟล์ index.php หรือไฟล์หลัก ได้ require 'config.php' มาแล้ว
// และตัวแปร $config พร้อมใช้งานในหน้านี้
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title><?= $config['app']['title'] ?? 'KanomMuangPhet Platform' ?></title>

    <meta name="description" content="<?= $config['app']['desc'] ?? '' ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="<?= $path_prefix ?>assets/css/style.css" rel="stylesheet">

    <?php if (isset($config['theme']['colors']['primary'])): ?>
        <style>
            :root {
                --nia-magenta: <?= $config['theme']['colors']['primary'] ?>;
                --nia-purple:  <?= $config['theme']['colors']['secondary'] ?>;
                --nia-gold:    <?= $config['theme']['colors']['accent'] ?>;
                --nia-bg:      <?= $config['theme']['colors']['background'] ?>;
                --text-dark:   <?= $config['theme']['colors']['text_main'] ?>;
            }

            body {
                font-family: 'Kanit', sans-serif;
                background-color: var(--nia-bg);
                color: var(--text-dark);
            }

            h1, h2, h3, h4, h5, h6,
            .navbar-brand, .btn-nia, .nav-link {
                font-family: 'Kanit', sans-serif;
                font-weight: 600;
                letter-spacing: 0.5px;
            }

            .swal2-confirm {
                background-color: var(--nia-magenta) !important;
                border-radius: var(--nia-radius, 16px) !important;
            }
        </style>
    <?php endif; ?>

</head>

<body class="d-flex flex-column min-vh-100">

<?php if (isset($_SESSION['success'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ!',
                    text: '<?= $_SESSION['success']; ?>',
                    confirmButtonColor: '<?= $config['theme']['colors']['primary'] ?? '#E6007E' ?>',
                    confirmButtonText: 'ตกลง'
                });
            });
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด!',
                    text: '<?= $_SESSION['error']; ?>',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'ลองใหม่'
                });
            });
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>