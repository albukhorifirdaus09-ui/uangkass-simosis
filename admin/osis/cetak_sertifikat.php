<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
requireRole('admin');

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) exit('Data anggota tidak valid.');
$stmt = $pdo->prepare("SELECT s.nama_lengkap, c.nama_kelas, op.nama_jabatan, ay.tahun_ajaran, om.tanggal_mulai, om.tanggal_selesai FROM osis_members om INNER JOIN students s ON s.id = om.student_id LEFT JOIN classes c ON c.id = s.kelas_id INNER JOIN osis_positions op ON op.id = om.position_id INNER JOIN academic_years ay ON ay.id = om.academic_year_id WHERE om.id = ? AND om.status IN ('nonaktif', 'keluar', 'lulus') LIMIT 1");
$stmt->execute([$id]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$member) exit('Data riwayat anggota tidak ditemukan.');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sertifikat OSIS - <?= htmlspecialchars($member['nama_lengkap']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Montserrat:wght@400;600;700&family=Pinyon+Script&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            background: #f4f6f9;
            font-family: 'Montserrat', sans-serif;
            color: #333;
        }
        .toolbar {
            padding: 20px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
        }
        .toolbar button, .toolbar a.btn-back {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            color: #fff;
            background: #1e3a8a;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
            font-family: 'Montserrat', sans-serif;
            display: inline-block;
        }
        .toolbar button:hover, .toolbar a.btn-back:hover {
            background: #172554;
        }
        .certificate-wrapper {
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        
        /* Elegant Frame Design */
        .certificate {
            width: 297mm;
            height: 210mm;
            padding: 16mm;
            background: #fdfbf7;
            position: relative;
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            border: 14px solid #1e3a8a; /* Thick dark blue border */
        }
        .certificate::before {
            content: '';
            position: absolute;
            inset: 8px;
            border: 4px solid #b48c36; /* Gold inner border */
            pointer-events: none;
        }
        .certificate::after {
            content: '';
            position: absolute;
            inset: 18px;
            border: 1px solid #1e3a8a; /* Thin blue inner border */
            pointer-events: none;
        }
        
        .inner {
            height: 100%;
            padding: 30px 50px;
            text-align: center;
            position: relative;
            background: #fff;
            border: 1px solid #b48c36;
            outline: 6px solid #fff;
            box-shadow: inset 0 0 0 2px #b48c36; /* Inner gold frame */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Elegant Corner Ornaments */
        .corner {
            position: absolute;
            width: 50px;
            height: 50px;
            background: #fff;
            border: 4px solid #1e3a8a;
            z-index: 2;
        }
        .corner-tl { top: -6px; left: -6px; border-bottom: none; border-right: none; }
        .corner-tr { top: -6px; right: -6px; border-bottom: none; border-left: none; }
        .corner-bl { bottom: -6px; left: -6px; border-top: none; border-right: none; }
        .corner-br { bottom: -6px; right: -6px; border-top: none; border-left: none; }
        
        /* Inner detail for corners */
        .corner::after {
            content: '';
            position: absolute;
            width: 25px;
            height: 25px;
            border: 2px solid #b48c36;
        }
        .corner-tl::after { top: 8px; left: 8px; border-bottom: none; border-right: none; }
        .corner-tr::after { top: 8px; right: 8px; border-bottom: none; border-left: none; }
        .corner-bl::after { bottom: 8px; left: 8px; border-top: none; border-right: none; }
        .corner-br::after { bottom: 8px; right: 8px; border-top: none; border-left: none; }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .logo {
            width: 100px;
            height: 100px;
            object-fit: contain;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
            z-index: 10;
        }
        .school-info {
            flex-grow: 1;
        }
        .school-name {
            margin: 0;
            color: #1e3a8a;
            font-family: 'Cinzel', serif;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 2px;
        }
        .school-sub {
            margin: 5px 0 0;
            color: #b48c36;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
        }
        .title {
            margin: 10px 0 5px;
            color: #b48c36;
            font-family: 'Cinzel', serif;
            font-size: 48px;
            letter-spacing: 4px;
        }
        .subtitle {
            margin: 0 0 25px;
            color: #4b5563;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .recipient {
            margin: 0 0 5px;
            font-size: 16px;
            color: #6b7280;
        }
        .name {
            display: inline-block;
            margin: 0 0 10px;
            font-family: 'Pinyon Script', cursive;
            font-size: 64px;
            color: #1e3a8a;
            line-height: 1.2;
            border-bottom: 2px solid #e5e7eb;
            padding: 0 40px 10px;
            min-width: 600px;
        }
        .detail {
            margin: 0 auto;
            max-width: 800px;
            font-size: 18px;
            line-height: 1.7;
            color: #374151;
        }
        .role {
            color: #1e3a8a;
            font-weight: 700;
            font-size: 22px;
        }
        .footer {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-top: 15px;
            padding: 0 20px;
        }
        .stamp-container {
            position: relative;
            width: 140px;
            height: 140px;
            margin-left: 20px;
        }
        .stamp {
            position: absolute;
            inset: 0;
            border: 5px double #b48c36;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #b48c36;
            font-family: 'Cinzel', serif;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
            text-align: center;
            transform: rotate(-15deg);
            background: rgba(180, 140, 54, 0.03);
            opacity: 0.9;
        }
        .stamp::before {
            content: '';
            position: absolute;
            inset: 6px;
            border: 1px solid #b48c36;
            border-radius: 50%;
            border-style: dashed;
        }
        .signature {
            width: 240px;
            text-align: center;
            margin-right: 20px;
        }
        .signature-title {
            color: #4b5563;
            font-size: 15px;
            margin-bottom: 70px;
        }
        .signature-line {
            border-top: 1px solid #1e3a8a;
            padding-top: 8px;
            color: #1e3a8a;
            font-weight: 600;
            font-size: 15px;
        }
        @media print {
            body {
                background: #fff;
            }
            .toolbar {
                display: none;
            }
            .certificate-wrapper {
                padding: 0;
            }
            .certificate {
                box-shadow: none;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar" id="toolbar">
        <a href="riwayat.php" class="btn-back" style="background: #4b5563;">&#11013; Kembali</a>
        <button onclick="window.print()">&#128424; Cetak Sertifikat</button>
        <button onclick="downloadPDF()" style="background: #b48c36;">&#128190; Download PDF</button>
    </div>
    
    <div class="certificate-wrapper" id="pdf-content">
        <section class="certificate">
            <div class="inner">
                <!-- Ornaments -->
                <div class="corner corner-tl"></div>
                <div class="corner corner-tr"></div>
                <div class="corner corner-bl"></div>
                <div class="corner corner-br"></div>
                
                <header class="header">
                    <img class="logo" src="../../img/logosmk.png" alt="Logo SMK">
                    <div class="school-info">
                        <p class="school-name">SEKOLAH MENENGAH KEJURUAN</p>
                        <p class="school-sub">SISTEM INFORMASI MANAJEMEN ORGANISASI SISWA</p>
                    </div>
                    <img class="logo" src="../../img/logoosis.png" alt="Logo OSIS">
                </header>
                
                <div>
                    <h1 class="title">SERTIFIKAT PENGHARGAAN</h1>
                    <p class="subtitle">DIBERIKAN ATAS DEDIKASI DAN KONTRIBUSI DALAM ORGANISASI SISWA INTRA SEKOLAH</p>
                    
                    <p class="recipient">Dengan bangga diberikan kepada</p>
                    <div class="name"><?= htmlspecialchars($member['nama_lengkap']) ?></div>
                    
                    <p class="detail">
                        sebagai <span class="role"><?= htmlspecialchars($member['nama_jabatan']) ?></span><br>
                        pada kepengurusan OSIS tahun ajaran <strong><?= htmlspecialchars($member['tahun_ajaran']) ?></strong><br>
                        terhitung sejak <?= date('d F Y', strtotime($member['tanggal_mulai'])) ?><?= $member['tanggal_selesai'] ? ' sampai ' . date('d F Y', strtotime($member['tanggal_selesai'])) : '' ?>.
                    </p>
                </div>
                
                <footer class="footer">
                    <div class="stamp-container">
                        <div class="stamp">
                            <span>SERTIFIKAT</span>
                            <span style="font-size: 16px; margin: 4px 0;">OSIS</span>
                            <span>RESMI</span>
                        </div>
                    </div>
                    <div class="signature">
                        <div class="signature-title">Mengetahui,<br>Pembina OSIS</div>
                        <div class="signature-line">( ........................................ )</div>
                    </div>
                </footer>
            </div>
        </section>
    </div>

    <!-- html2pdf Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    
    <script>
        function downloadPDF() {
            // Get the element to be converted
            const element = document.querySelector('.certificate');
            
            // Set options for html2pdf
            const opt = {
                margin:       0,
                filename:     'Sertifikat_OSIS_<?= htmlspecialchars(addslashes($member['nama_lengkap'])) ?>.pdf',
                image:        { type: 'jpeg', quality: 1 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
            };
            
            // Change button text temporarily
            const btn = document.querySelector('button[onclick="downloadPDF()"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '&#8987; Memproses PDF...';
            btn.style.opacity = '0.7';
            btn.disabled = true;
            
            // Generate and download PDF
            html2pdf().set(opt).from(element).save().then(() => {
                // Restore button state
                btn.innerHTML = originalText;
                btn.style.opacity = '1';
                btn.disabled = false;
            }).catch(err => {
                console.error("Error generating PDF:", err);
                alert("Terjadi kesalahan saat membuat PDF.");
                btn.innerHTML = originalText;
                btn.style.opacity = '1';
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>
