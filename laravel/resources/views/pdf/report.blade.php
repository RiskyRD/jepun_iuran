<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bulanan</title>
    <style>
      @import url("https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap");
      body {
        margin: 0;
        font-family: "Montserrat", sans-serif;
        box-sizing: border-box;
        position: relative;
      }
      h1 {
        font-size: 26pt;
        line-height: 20pt;
      }
      h2 {
        font-size: 20pt;
      }
      h3 {
        font-size: 15pt;
      }
      p {
        font-size: 9pt;
      }
      header {
        padding: 0.8cm 1cm;
        height: 2cm;
        margin-bottom: 0.5cm;
        color: white;
        background-color: #343a40;
      }
      .header-logo img {
        height: 2cm;
      }
      footer {
        width: 100%;
        position: absolute;
        bottom: 0;
        display: flex;
        justify-content: flex-end;
      }
      footer h3 {
        text-align: right;
        padding: 1cm 2cm;
        border-top: 1px solid black;
      }
      h1,
      h2,
      h3,
      p {
        margin: 0;
      }

      main {
        padding: 0 1cm;
      }
      section {
        margin-bottom: 0.2cm;
      }
      .section-title {
        padding: 0.2cm 0.5cm;
        color: white;
        background-color: #343a40;
        margin-bottom: 0.1cm;
      }
      section > div {
        padding: 0 0.5cm;
      }
      .top-section {
        margin-bottom: 0.5cm;
      }
      .section-table {
        width: 100%;
        border-collapse: collapse;
      }
      .section-table td {
        border-top: 1px solid #b6b6b6;
      }
      th {
        text-align: left;
      }
      .section-table th,
      .section-table td {
        padding: 0.2cm;
      }
      .table-align-right {
        text-align: right;
      }
      tfoot td {
        font-weight: bold;
      }
      .total-each {
        margin-top: 0.5cm;
        color: white;
      }
      .total-each-con {
        height: 2.2cm;
        color: white;
      }
      .total-each-con > table {
        height: 100%;
        width: 100%;
        border: none;
      }
      .total-each-con td {
        padding: 0.5cm 0.8cm;
        background-color: #343a40;
      }
      .total-each-con h1 {
        line-height: 30pt !important;
      }
      .laba {
        background-color: black !important;
      }

    </style>
</head>
<body>
    <header>
        <table style="width: 100%;">
          <tr>
            <td class="header-logo">
              <!-- Logo -->
              <img src="{{ public_path('igj_full_colors_white.png') }}" alt="Logo">
            </td>
            <td class="header-desc">
              <h3>Iuran Gang Jepun</h3>
              <p><b>Batubulan Kangin, Sukawati, Gianyar</b></p>
              <p><b>Tanggal: </b>{{ \Carbon\Carbon::now()->format('d.m.Y') }}</p>
            </td>
          </tr>
        </table>
    </header>

    <main>
        <section class="top-section">
            <table style="width: 100%;">
              <tr>
                <td>
                  <table>
                    <tr>
                        <td>Dari</td>
                        <td>:</td>
                        <td>{{ \Carbon\Carbon::parse($startDate)->format('d.m.Y') }}</td>
                    </tr>
                    <tr>
                        <td>Sampai</td>
                        <td>:</td>
                        <td>{{ \Carbon\Carbon::parse($endDate)->format('d.m.Y') }}</td>
                    </tr>
                  </table>
                </td>
                <td>
                  <div>
                    <b>Kas Awal:</b>
                    <h2>{{ number_format($previousBalance, 0, ',', '.') }}</h2>
                  </div>
                </td>
              </tr>
            </table>
        </section>

        <!-- Pemasukan -->
        <section>
            <h3 class="section-title">Pemasukan</h3>
            <table class="section-table">
                <thead>
                    <th>Kategori</th>
                    <th class="table-align-right">Banyak</th>
                    <th class="table-align-right">Jumlah</th>
                </thead>
                <tbody>
                  @foreach ($incomes as $income)
                      <tr>
                          <td>{{ $income['category'] }}</td> <!-- Access array elements -->
                          <td class="table-align-right">{{ $income['quantity'] }}</td>
                          <td class="table-align-right">{{ number_format($income['amount'], 0, ',', '.') }}</td>
                      </tr>
                  @endforeach
              </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td class="table-align-right">{{ $totalIncomesQuantity }}</td>
                        <td class="table-align-right">{{ number_format($totalIncomesAmount, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <!-- Pengeluaran -->
        <section>
            <h3 class="section-title">Pengeluaran</h3>
            <table class="section-table">
                <thead>
                    <th>Kategori</th>
                    <th class="table-align-right">Banyak</th>
                    <th class="table-align-right">Jumlah</th>
                </thead>
                <tbody>
                  @foreach ($expenses as $expense)
                      <tr>
                          <td>{{ $expense['category'] }}</td> <!-- Access array elements -->
                          <td class="table-align-right">{{ $expense['quantity'] }}</td>
                          <td class="table-align-right">{{ number_format($expense['amount'], 0, ',', '.') }}</td>
                      </tr>
                  @endforeach
              </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td class="table-align-right">{{ $totalExpensesQuantity }}</td>
                        <td class="table-align-right">{{ number_format($totalExpensesAmount, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <!-- Tunggakan -->
        <section>
            <h3 class="section-title">Tunggakan</h3>
            <table class="section-table">
                <thead>
                    <th>Kategori</th>
                    <th class="table-align-right">Banyak</th>
                    <th class="table-align-right">Jumlah</th>
                </thead>
                <tbody>
                    <tr>
                        <td>Wajib</td>
                        <td class="table-align-right">{{ $arrearWajib }}</td>
                        <td class="table-align-right">{{ number_format($arrearWajib * 5000, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                      <td>Sampah</td>
                      <td class="table-align-right">{{ $arrearSampah }}</td>
                      <td class="table-align-right">{{ number_format($arrearSampah * 20000, 0, ',', '.') }}</td>
                  </tr>
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        <div class="total-each-con">
          <table>
            <tr>
              <td>
                <p>Total Tunggakan</p>
                <h2>
                  {{ number_format($totalArrears, 0, ',', '.') }}
                </h2>
              </td>
              <td class="laba">
                <p>Kas Terbaru</p>
                <h1>{{ number_format($latestBalance, 0, ',', '.') }}</h1>
              </td>
            </tr>
          </table>
        </div>
    </footer>
</body>
</html>
