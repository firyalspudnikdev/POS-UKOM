<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Submission</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>

<body>

    <div class="hidden-text" id="status-text"></div>

    <form id="myForm">
        <div>
            <label for="id_pelanggan">Nama Pelanggan :</label>
            <select id="id_pelanggan" name="id_pelanggan" required>
                <option value="">Pilih Nama Anda</option>
                @foreach ($pelanggans as $pelanggan)
                    <option value="{{ $pelanggan->id_pelanggan }}">{{ $pelanggan->nama_pelanggan }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="jumlah">Jumlah Barang :</label>
            <input type="number" id="jumlah" name="jumlah" required>
        </div>

        <div>
            <label for="id_barang">Barang :</label>
            <select id="id_barang" name="id_barang" required>
                <option value="">Pilih Barang</option>
                @foreach ($barangs as $barang)
                    <option value="{{ $barang->id_barang }}">{{ $barang->nama_barang }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <input type="button" value="Submit" id="button">
        </div>
    </form>


    <table id="transaksiTable">
        <thead>
            <tr>
                <th>ID Transaksi</th>
                <th>ID Pelanggan</th>
                <th>ID Barang</th>
                <th>Jumlah</th>
                <th>Tanggal Transaksi</th>
            </tr>
        </thead>
        <tbody>
            <!-- Data transaksi akan ditampilkan di sini -->
        </tbody>
    </table>


    <script>
        $(document).ready(function() {
            // Memuat data transaksi saat halaman dimuat
            loadTransaksi();

            $("#button").click(function() {
                var formData = $("#myForm").serialize();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "POST",
                    url: "{{ route('transaksi.store') }}",
                    data: formData,
                    success: function(response) {
                        displayTransaksi(response.data);
                    },
                    error: function(xhr, status, error) {
                        displayMessage("Error: " + xhr.status);
                    }
                });
            });
        });

        // Fungsi untuk memuat data transaksi dari server
        function loadTransaksi() {
            var filterPelanggan = $("#filter_pelanggan").val();
            $.ajax({
                type: "GET",
                url: "{{ route('transaksi.index') }}",
                data: {
                    pelanggan: filterPelanggan
                },
                success: function(response) {
                    $("#transaksiTable tbody").empty();
                    response.data.forEach(function(data) {
                        console.log(data);
                        displayTransaksi(data);
                    });
                },
                error: function(xhr, status, error) {
                    displayMessage("Error: " + xhr.status);
                }
            });
        }

        function displayTransaksi(data) {
            var transaksiTable = $("#transaksiTable tbody");
            var newRow = $("<tr>");

            newRow.append("<td>" + data.id_transaksi + "</td>");
            newRow.append("<td>" + data.nama_pelanggan + "</td>");
            newRow.append("<td>" + data.nama_barang + "</td>");
            newRow.append("<td>" + data.jumlah + "</td>");
            var createdAt = new Date(data.created_at);
            var formattedDate = formatDate(createdAt);
            newRow.append("<td>" + formattedDate + "</td>");

            transaksiTable.append(newRow);
        }

        function displayMessage(message) {
            console.log(message)
            var statusText = $("#status-text");
            statusText.html(message);
            statusText.show();
            setTimeout(function() {
                statusText.hide();
            }, 60000);
        }

        // Function to format date
        function formatDate(date) {
            var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October",
                "November", "December"
            ];
            var day = date.getDate();
            var monthIndex = date.getMonth();
            var year = date.getFullYear();
            var hours = date.getHours();
            var minutes = date.getMinutes();
            var seconds = date.getSeconds();

            // Add leading zero if needed
            if (hours < 10) {
                hours = "0" + hours;
            }
            if (minutes < 10) {
                minutes = "0" + minutes;
            }
            if (seconds < 10) {
                seconds = "0" + seconds;
            }

            return day + ' ' + months[monthIndex] + ' ' + year + ' ' + hours + ':' + minutes + ':' + seconds;
        }
    </script>
</body>

</html>