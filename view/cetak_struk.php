<?php
ob_start();
session_start();

include_once __DIR__ . "/../model/m_koneksi.php";

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$query = mysqli_query($conn, "
    SELECT
        t.id_parkir,
        t.waktu_masuk,
        t.waktu_keluar,
        t.durasi_jam,
        t.biaya_total,
        t.status,
        k.plat_nomer,
        k.jenis_kendaraan,
        k.penilik
    FROM tb_transaksi t
    INNER JOIN tb_kendaraan k
        ON t.id_kendaraan = k.id_kendaraan
    WHERE LOWER(TRIM(t.status)) = 'keluar'
      AND t.waktu_keluar IS NOT NULL
    ORDER BY t.id_parkir DESC
");

if (!$query) {
    die("Gagal mengambil data transaksi: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Cetak Struk - Parkir</title>

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, sans-serif;
    background: #f0fdf4;
    padding: 30px;
}

.container {
    max-width: 1100px;
    margin: auto;
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
}

h2 {
    color: #047857;
    margin-bottom: 8px;
}

.subtitle {
    color: #6b7280;
    margin-bottom: 25px;
    font-size: 14px;
}

.table-box {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
}

th {
    background: #047857;
    color: white;
    padding: 14px;
    text-align: left;
    font-size: 13px;
}

td {
    padding: 14px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 13px;
}

tr:hover td {
    background: #ecfdf5;
}

.plat {
    color: #047857;
    font-weight: bold;
}

.btn-cetak {
    display: inline-block;
    padding: 9px 14px;
    background: #059669;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-size: 12px;
    font-weight: bold;
}

.btn-cetak:hover {
    background: #065f46;
}

.btn-kembali {
    display: inline-block;
    margin-bottom: 20px;
    padding: 10px 15px;
    background: #e5e7eb;
    color: #374151;
    text-decoration: none;
    border-radius: 8px;
    font-size: 13px;
}

.kosong {
    text-align: center;
    padding: 35px;
    color: #6b7280;
}

@media (max-width: 600px) {
    body {
        padding: 12px;
    }

    .container {
        padding: 18px;
    }
}
</style>
</head>

<body>

<div class="container">

    <a href="dashboard_petugas.php" class="btn-kembali">
        ← Kembali ke Dashboard
    </a>

    <h2>🧾 Cetak Struk</h2>

    <p class="subtitle">
        Pilih transaksi kendaraan yang sudah keluar untuk mencetak struk.
    </p>

    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Plat Nomor</th>
                    <th>Jenis Kendaraan</th>
                    <th>Pemilik</th>
                    <th>Waktu Masuk</th>
                    <th>Waktu Keluar</th>
                    <th>Biaya</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php
            $no = 1;

            if (mysqli_num_rows($query) > 0) {
                while ($data = mysqli_fetch_assoc($query)) {
            ?>
                <tr>
                    <td><?= $no++; ?></td>

                    <td>
                        <span class="plat">
                            <?= htmlspecialchars($data['plat_nomer']); ?>
                        </span>
                    </td>

                    <td>
                        <?= htmlspecialchars($data['jenis_kendaraan']); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($data['penilik']); ?>
                    </td>

                    <td>
                        <?= !empty($data['waktu_masuk'])
                            ? date('d-m-Y H:i', strtotime($data['waktu_masuk']))
                            : '-'; ?>
                    </td>

                    <td>
                        <?= !empty($data['waktu_keluar'])
                            ? date('d-m-Y H:i', strtotime($data['waktu_keluar']))
                            : '-'; ?>
                    </td>

                    <td>
                        Rp <?= number_format(
                            $data['biaya_total'] ?? 0,
                            0,
                            ',',
                            '.'
                        ); ?>
                    </td>

                    <td>
                        <span style="
                            padding:5px 9px;
                            border-radius:20px;
                            font-size:10px;
                            font-weight:bold;
                            background:#dcfce7;
                            color:#166534;
                        ">
                            KELUAR
                        </span>
                    </td>

                    <td>
                        <a
                            href="struk.php?id_parkir=<?= (int) $data['id_parkir']; ?>"
                            target="_blank"
                            class="btn-cetak"
                        >
                            🧾 Cetak
                        </a>
                    </td>
                </tr>
            <?php
                }
            } else {
            ?>
                <tr>
                    <td colspan="9" class="kosong">
                        Belum ada transaksi kendaraan yang sudah keluar.
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
