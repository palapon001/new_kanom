<?php
// 1. จัดการ Path
$path_prefix = in_array(basename(dirname($_SERVER['PHP_SELF'])), ['shop', 'admin', 'supplier', 'users']) ? '../' : '';

// 2. เตรียม Font URL
$font_config = $config['theme']['fonts']['main'] ?? "'Kanit', sans-serif";
$font_clean  = urlencode(trim(str_replace(["'", ", sans-serif"], "", $font_config)));
$font_url    = "https://fonts.googleapis.com/css2?family={$font_clean}:wght@300;400;500;600;700&display=swap";
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $config['app']['title'] ?? 'KanomMuangPhet' ?></title>
    <meta name="description" content="<?= $config['app']['desc'] ?? '' ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="<?= $font_url ?>" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?= $path_prefix ?>assets/css/style.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            /* Theme Colors */
            --nia-primary:   <?= $config['theme']['colors']['primary']   ?? '#E6007E' ?>;
            --nia-secondary: <?= $config['theme']['colors']['secondary'] ?? '#2D1F57' ?>;
            --nia-accent:    <?= $config['theme']['colors']['accent']    ?? '#FDB913' ?>;
            --nia-success:   <?= $config['theme']['colors']['success']   ?? '#00C853' ?>;
            
            /* Navbar */
            --nia-navbar-bg:   <?= $config['theme']['colors']['secondary']   ?? '#2D1F57' ?>;
            --nia-navbar-text: <?= $config['theme']['colors']['navbar_text'] ?? '#ffffff' ?>;

            /* Body */
            --nia-body-bg:   <?= $config['theme']['colors']['background'] ?? '#F4F6F9' ?>;
            --nia-body-text: <?= $config['theme']['colors']['text_main']  ?? '#333333' ?>;
            
            /* Footer */
            --nia-footer-bg:   <?= $config['theme']['colors']['footer_bg']   ?? '#343a40' ?>;
            --nia-footer-text: <?= $config['theme']['colors']['footer_text'] ?? '#ffffff' ?>;

            /* Typography & UI */
            --nia-font-main: <?= $config['theme']['fonts']['main'] ?>;
            --nia-font-size: <?= $config['theme']['fonts']['size'] ?? '16px' ?>;
            --nia-radius:    <?= $config['theme']['ui']['radius']  ?? '16px' ?>;
        }

        /* 🟢 เพิ่มส่วนนี้: สั่งให้ Body ใช้ค่าตัวแปรที่ประกาศไว้ */
        body {
            background-color: var(--nia-body-bg) !important;
            color: var(--nia-body-text) !important;
            font-family: var(--nia-font-main) !important;
            font-size: var(--nia-font-size);
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php if (isset($_SESSION['success']) || isset($_SESSION['error']) || isset($_SESSION['demo_popup'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                <?php if (isset($_SESSION['success'])): ?>
                    Swal.fire({
                        icon: 'success', title: 'สำเร็จ!', text: '<?= $_SESSION['success'] ?>',
                        confirmButtonColor: 'var(--nia-secondary)', timer: 2000, timerProgressBar: true
                    });
                    <?php unset($_SESSION['success']); ?>
                
                <?php elseif (isset($_SESSION['error'])): ?>
                    Swal.fire({
                        icon: 'error', title: 'พบข้อผิดพลาด', text: '<?= $_SESSION['error'] ?>',
                        confirmButtonColor: '#dc3545'
                    });
                    <?php unset($_SESSION['error']); ?>

                <?php elseif (isset($_SESSION['demo_popup'])): ?>
                    Swal.fire({
                        title: '🎉 Demo Accounts', html: `<?= $_SESSION['demo_popup'] ?>`, icon: 'info',
                        confirmButtonText: 'รับทราบ', confirmButtonColor: 'var(--nia-secondary)',
                        footer: '<span class="text-muted small">ระบบทดสอบเท่านั้น</span>', backdrop: `rgba(0,0,123,0.4)`
                    });
                    <?php unset($_SESSION['demo_popup']); ?>
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>