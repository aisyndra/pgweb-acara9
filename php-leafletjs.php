<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Sesuaikan dengan setting MySQL
$servername = "localhost";
$username = "root";
$password = ""; // Ganti dengan password MySQL root jika ada
$dbname = "pgweb_acara7b";

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query untuk mengambil semua data dari tabel penduduk
$sql = "SELECT * FROM penduduk";
$result = $conn->query($sql);

// Inisialisasi peta
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta dan Tabel Data Penduduk Wilayah Yogyakarta</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Layout Grid untuk membagi peta dan tabel */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        header {
            text-align: center;
            padding: 20px;
            height: 10%;
            
            /* Anda bisa menyesuaikan tinggi sesuai kebutuhan */
            width: 100%;
            /* Membuat header memenuhi lebar penuh */
            background-color: #BED2E5;
        }

        .container {
            display: flex;
            /* Menggunakan Flexbox untuk menempatkan item secara horizontal */
            justify-content: center;
            /* Memposisikan konten di tengah */
            align-items: flex-start;
            /* Memposisikan konten di bagian atas */
            height: 90%;
            /* Sisa tinggi dari viewport */
        }

        #map {
            height: 60%;
            /* Atur tinggi peta sesuai kebutuhan */
            width: 100%;
            /* Atur lebar peta sesuai kebutuhan */
            margin-right: 20px;
            /* Memberi jarak antara peta dan tabel */
            background-color: #e0e0e0;
            /* Background untuk peta, jika perlu */
        }

        #tabel-data {
            padding: 20px;
            max-height: 250px;
            overflow-y: auto;
            background-color: #fff;
            border: 1px solid #ddd;
        }

        table {
            width: 100%;
            /* Mengatur tabel untuk memenuhi lebar kontainer */
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color:#BED2E5;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #D3EDC5;
        }

        tr:nth-child(odd) {
            background-color: #ffffff;
        }

        tr:hover {
            background-color: #BED2E5;
        }

        h2 {
            text-align: center;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }
    </style>
</head>

<body>
    <header>
        <h1>Peta dan Tabel Data Penduduk Wilayah Yogyakarta</h1>
        <p>Halaman ini menampilkan data penduduk beserta lokasi geografis masing-masing kecamatan.</p>
    </header>

    <div id="map"></div>

    <!-- Modal Bootstrap -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="edit-id">
                        <div class="mb-3">
                            <label for="kecamatan" class="form-label">Kecamatan</label>
                            <input type="text" class="form-control" id="kecamatan" name="kecamatan" required>
                        </div>
                        <div class="mb-3">
                            <label for="latitude" class="form-label">Latitude</label>
                            <input type="text" class="form-control" id="latitude" name="latitude" required>
                        </div>
                        <div class="mb-3">
                            <label for="longitude" class="form-label">Longitude</label>
                            <input type="text" class="form-control" id="longitude" name="longitude" required>
                        </div>
                        <div class="mb-3">
                            <label for="luas" class="form-label">Luas</label>
                            <input type="text" class="form-control" id="luas" name="luas" required>
                        </div>
                        <div class="mb-3">
                            <label for="jumlahPenduduk" class="form-label">Jumlah Penduduk</label>
                            <input type="number" class="form-control" id="jumlahPenduduk" name="jumlahPenduduk" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveChanges()">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <div id="tabel-data">
        <h2>Data Penduduk</h2>
        <?php
        if ($result->num_rows > 0) {
            echo "<table>
                    <tr>
                        <th>ID</th>
                        <th>Kecamatan</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Luas</th>
                        <th>Jumlah Penduduk</th>
                        <th>Aksi</th>
                    </tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . $row["id"] . "</td>
                        <td>" . $row["kecamatan"] . "</td>
                        <td>" . $row["latitude"] . "</td>
                        <td>" . $row["longitude"] . "</td>
                        <td>" . $row["luas"] . "</td>
                        <td>" . $row["jumlah_penduduk"] . "</td>
                        <td class='action-buttons'>
                            <button onclick=\"editData(" . $row['id'] . ")\">Edit</button>
                            <button onclick=\"deleteData(" . $row['id'] . ")\">Hapus</button>
                        </td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "Tidak ada data yang tersedia.";
        }
        ?>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script>
        // Inisialisasi peta
        var map = L.map("map").setView([-7.7706217711188295, 110.35620626294589], 11);

        // Tile Layer Base Map
        var osm = L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        });
        osm.addTo(map);

        <?php
        // Loop untuk menambahkan marker ke peta
        if ($result->num_rows > 0) {
            $result->data_seek(0);
            while ($row = $result->fetch_assoc()) {
                $kecamatan = $row['kecamatan'];
                $latitude = $row['latitude'];
                $longitude = $row['longitude'];
                $luas = $row['luas'];
                $jumlah_penduduk = $row['jumlah_penduduk'];

                echo "L.marker([$latitude, $longitude]).addTo(map)
                    .bindPopup('<b>Kecamatan: $kecamatan</b><br>Luas: $luas km²<br>Jumlah Penduduk: $jumlah_penduduk');";
            }
        }
        ?>

        function editData(id) {
            fetch(`http://localhost/pgweb/acara9/api.php?id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('edit-id').value = data.id; // Simpan ID di hidden input
                    document.getElementById('kecamatan').value = data.kecamatan;
                    document.getElementById('latitude').value = data.latitude;
                    document.getElementById('longitude').value = data.longitude;
                    document.getElementById('luas').value = data.luas;
                    document.getElementById('jumlahPenduduk').value = data.jumlah_penduduk;

                    // Tampilkan modal menggunakan Bootstrap
                    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
                    editModal.show();
                })
                .catch(error => console.error('Error fetching data:', error));
        }

        function saveChanges() {
            const id = document.getElementById('edit-id').value;
            // Validasi ID
            if (!id || isNaN(id)) {
                alert('Invalid ID. It must be a number.');
                return;
            }

            const updatedData = {
                'edit-id': id, // Pastikan ini cocok dengan nama yang digunakan di api.php
                kecamatan: document.getElementById('kecamatan').value,
                latitude: document.getElementById('latitude').value,
                longitude: document.getElementById('longitude').value,
                luas: document.getElementById('luas').value,
                jumlahPenduduk: document.getElementById('jumlahPenduduk').value,
            };

            // Mengirim request PUT
            fetch(`http://localhost/pgweb/acara9/api.php`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(updatedData), // Mengonversi objek menjadi URL-encoded string
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response from server:', data); // Untuk debugging
                    // Tindakan setelah berhasil memperbarui data, misalnya menutup modal
                    if (data.success) {
                        alert(data.success);
                        // Jika Anda ingin menutup modal, lakukan di sini
                        const editModal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                        editModal.hide();
                        // Juga bisa memanggil fungsi untuk memperbarui tampilan data
                    } else if (data.error) {
                        alert(data.error);
                    }
                })
                .catch(error => console.error('Error updating data:', error));
        }

        function deleteData(id) {
            if (confirm("Apakah Anda yakin ingin menghapus data ini?")) {
                fetch(`http://localhost/pgweb/acara9/api.php?id=${id}`, {
                        method: 'DELETE',
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        location.reload(); // Reload data setelah menghapus
                    })
                    .catch(error => console.error('Error deleting data:', error));
            }
        }
    </script>
</body>

</html>