<?php
ob_start();
session_start();

date_default_timezone_set('Asia/Jakarta');

include_once __DIR__ . "/../model/m_koneksi.php";

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Notifikasi
$notif = $_SESSION['notif_kendaraan_masuk'] ?? null;
unset($_SESSION['notif_kendaraan_masuk']);

// Kendaraan yang belum sedang parkir
$kendaraan = mysqli_query($conn, "
    SELECT k.*
    FROM tb_kendaraan k
    WHERE NOT EXISTS (
        SELECT 1
        FROM tb_transaksi t
        WHERE t.id_kendaraan = k.id_kendaraan
        AND t.status = 'masuk'
    )
    ORDER BY k.id_kendaraan DESC
");

// Data area parkir
$area = mysqli_query($conn, "
    SELECT *
    FROM tb_area_parkir
    ORDER BY id_area DESC
");

// Data kendaraan yang sedang parkir
$sedang_parkir = mysqli_query($conn, "
    SELECT
        t.id_parkir,
        t.waktu_masuk,
        k.plat_nomer,
        k.jenis_kendaraan,
        a.nama_area
    FROM tb_transaksi t
    INNER JOIN tb_kendaraan k
        ON t.id_kendaraan = k.id_kendaraan
    LEFT JOIN tb_area_parkir a
        ON t.id_area = a.id_area
    WHERE t.status = 'masuk'
    ORDER BY t.waktu_masuk DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kendaraan Masuk dan Keluar</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            padding: 25px;
            background: linear-gradient(135deg, #d1fae5, #f0fdf4);
        }

        .container {
            max-width: 1050px;
            margin: 25px auto;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.10);
            margin-bottom: 25px;
        }

        h2 {
            color: #047857;
            margin-bottom: 10px;
        }

        .deskripsi {
            color: #6b7280;
            margin-bottom: 25px;
            font-size: 14px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        label {
            font-weight: bold;
            color: #374151;
            margin-top: 10px;
        }

        select,
        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #d1d5db;
            border-radius: 9px;
            font-size: 14px;
            background: #f9fafb;
        }

        select:focus,
        input:focus {
            outline: none;
            border-color: #10b981;
        }

        #waktu_masuk {
            background: #f3f4f6;
            cursor: not-allowed;
        }

        button {
            padding: 13px 18px;
            border: none;
            border-radius: 9px;
            background: #059669;
            color: white;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            margin-top: 15px;
        }

        button:hover {
            background: #047857;
        }

        .notifikasi {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: bold;
        }

        .notifikasi.success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .notifikasi.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .table-wrapper {
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        th {
            background: #059669;
            color: white;
            padding: 14px;
            text-align: left;
            font-size: 13px;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
            font-size: 13px;
        }

        tr:hover td {
            background: #f0fdf4;
        }

        .btn-keluar {
            display: inline-block;
            padding: 9px 13px;
            background: #dc2626;
            color: white;
            text-decoration: none;
            border-radius: 7px;
            font-weight: bold;
            white-space: nowrap;
        }

        .btn-keluar:hover {
            background: #b91c1c;
        }

        .kosong {
            text-align: center;
            color: #9ca3af;
            padding: 25px;
        }

        .status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            background: #dcfce7;
            color: #166534;
            font-weight: bold;
            font-size: 11px;
        }

        @media (max-width: 600px) {
            body {
                padding: 12px;
            }

            .card {
                padding: 20px 15px;
            }

            h2 {
                font-size: 21px;
            }
          .btn-kembali {
    display: inline-block;
    padding: 10px 18px;
    background: #6b7280;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 14px;
    font-weight: bold;
}

.btn-kembali:hover {
    background: #4b5563;
}
          .header-halaman {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.header-halaman h2 {
    margin: 0;
}

.btn-kembali {
    display: inline-block;
    padding: 10px 18px;
    background: #6b7280;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: bold;
}

.btn-kembali:hover {
    background: #4b5563;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <?php if ($notif) { ?>
        <div class="notifikasi <?= htmlspecialchars($notif['jenis']); ?>">
            <?= htmlspecialchars($notif['pesan']); ?>
        </div>
    <?php } ?>

    <!-- FORM KENDARAAN MASUK -->
    <div class="card">

        <div class="header-halaman">
    <h2>Kendaraan Masuk</h2>

    <a href="dashboard_petugas.php" class="btn-kembali">
        ← Kembali
    </a>
</div>

        <p class="deskripsi">
            Silakan isi data kendaraan yang akan masuk ke area parkir.
        </p>

        <form action="../controller/c_data.php" method="POST">

            <label>Kendaraan</label>

            <select name="id_kendaraan" required>
                <option value="">-- Pilih Kendaraan --</option>

                <?php while ($data = mysqli_fetch_assoc($kendaraan)) { ?>
                    <option value="<?= $data['id_kendaraan']; ?>">
                        <?= htmlspecialchars($data['plat_nomer']); ?>
                        -
                        <?= htmlspecialchars($data['jenis_kendaraan']); ?>
                    </option>
                <?php } ?>

            </select>

            <label>Area Parkir</label>

            <select name="id_area" required>
                <option value="">-- Pilih Area --</option>

                <?php while ($data = mysqli_fetch_assoc($area)) { ?>
                    <option value="<?= $data['id_area']; ?>">
                        <?= htmlspecialchars($data['nama_area']); ?>
                    </option>
                <?php } ?>

            </select>

            <label>Waktu Masuk</label>

            <input
                type="datetime-local"
                id="waktu_masuk"
                name="waktu_masuk"
                readonly
                required
            >

            <button type="submit" name="kendaraan_masuk">
                + Simpan Kendaraan Masuk
            </button>

        </form>

    </div>


    <!-- DATA KENDARAAN SEDANG PARKIR -->
    <div class="card">

        <h2>Kendaraan Sedang Parkir</h2>

        <p class="deskripsi">
            Daftar kendaraan yang belum melakukan proses keluar.
        </p>

        <div class="table-wrapper">

            <table>

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Plat Nomor</th>
                        <th>Jenis Kendaraan</th>
                        <th>Area Parkir</th>
                        <th>Waktu Masuk</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                <?php
                if ($sedang_parkir && mysqli_num_rows($sedang_parkir) > 0) {

                    $no = 1;

                    while ($data = mysqli_fetch_assoc($sedang_parkir)) {
                ?>

                    <tr>
                        <td><?= $no++; ?></td>

                        <td>
                            <strong>
                                <?= htmlspecialchars($data['plat_nomer']); ?>
                            </strong>
                        </td>

                        <td>
                            <?= htmlspecialchars($data['jenis_kendaraan']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($data['nama_area'] ?? '-'); ?>
                        </td>

                        <td>
                            <?= date('d-m-Y H:i', strtotime($data['waktu_masuk'])); ?>
                        </td>

                        <td>
                            <span class="status">Sedang Parkir</span>
                        </td>

                        <td>
                            <a
                                href="../controller/c_data.php?kendaraan_keluar=<?= $data['id_parkir']; ?>"
                                class="btn-keluar"
                                onclick="return confirm('Yakin ingin memproses kendaraan ini keluar?')"
                            >
                                Kendaraan Keluar
                            </a>
                        </td>
                    </tr>

                <?php
                    }

                } else {
                ?>

                    <tr>
                        <td colspan="7" class="kosong">
                            Belum ada kendaraan yang sedang parkir.
                        </td>
                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<script>
function updateWaktu() {
    const sekarang = new Date();

    const tahun = sekarang.getFullYear();
    const bulan = String(sekarang.getMonth() + 1).padStart(2, '0');
    const tanggal = String(sekarang.getDate()).padStart(2, '0');
    const jam = String(sekarang.getHours()).padStart(2, '0');
    const menit = String(sekarang.getMinutes()).padStart(2, '0');

    document.getElementById('waktu_masuk').value =
        `${tahun}-${bulan}-${tanggal}T${jam}:${menit}`;
}

updateWaktu();
setInterval(updateWaktu, 1000);
</script>

</body>
</html>
