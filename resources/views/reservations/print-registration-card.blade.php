<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Registration Card — {{ $reservation->reservation_number }}</title>
<script>
  window.onload = function() {
    window.print();
  };
</script>
<style>
  @page {
    size: A4 portrait;
    margin: 6mm 8mm 6mm 8mm;
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: Arial, sans-serif;
    font-size: 11pt;
    color: #000;
    background: #fff;
    width: 190mm;
    margin: 0 auto;
  }

  .header {
    text-align: center;
    margin-bottom: 6px;
  }
  .hotel-logo {
    max-height: 70px;
    max-width: 250px;
    object-fit: contain;
  }
  .header h1 {
    font-size: 18pt;
    font-weight: bold;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin-bottom: 2px;
  }
  .header .hotel-address {
    font-size: 11pt;
    color: #333;
    margin: 2px 0;
  }
  .header-rule-top { border-top: 2px solid #000; margin: 4px 0 2px; }
  .header-rule-bot { border-top: 1.5px solid #000; margin: 2px 0 5px; }
  .header h2 {
    font-size: 15pt;
    font-weight: bold;
    letter-spacing: 2px;
    margin: 2px 0;
  }

  table {
    width: 100%;
    border-collapse: collapse;
  }
  tr {
    page-break-inside: avoid;
  }
  td {
    border: 1px solid #000;
    padding: 5px 7px;
    vertical-align: top;
    font-size: 11pt;
  }

  .lbl {
    font-size: 9pt;
    font-weight: bold;
    display: block;
    line-height: 1.3;
  }
  .lbl-id {
    font-size: 8pt;
    font-weight: normal;
    display: block;
    color: #333;
    line-height: 1.2;
  }
  .field-value {
    display: block;
    font-size: 11pt;
    min-height: 16px;
    margin-top: 3px;
    padding: 0 2px;
  }
  .wline {
    display: block;
    border-bottom: 1px solid #666;
    min-height: 16px;
    margin-top: 3px;
  }

  .pay-row {
    display: flex;
    flex-wrap: wrap;
    gap: 3px 12px;
    margin-top: 3px;
  }
  .pay-item {
    display: flex;
    align-items: center;
    gap: 3px;
    font-size: 10pt;
    white-space: nowrap;
  }
  .pay-item input[type="checkbox"] {
    width: 12px;
    height: 12px;
    margin: 0;
    flex-shrink: 0;
  }

  .terms-cell { font-size: 9pt; line-height: 1.5; }
  .terms-cell p { margin-bottom: 4px; }
  .t-id { font-style: italic; color: #222; }

  .hotel-use-hd {
    background: #000;
    color: #fff;
    font-weight: bold;
    font-size: 11pt;
    text-align: center;
    padding: 5px 7px;
  }

  .sign-cell {
    height: 80px;
    vertical-align: bottom;
  }
  .sign-cell-tall {
    height: 85px;
    vertical-align: bottom;
    text-align: center;
  }
  .sign-label {
    font-size: 10pt;
    font-weight: bold;
    text-align: center;
    display: block;
    margin-bottom: 4px;
  }
  .sign-line {
    border-top: 1px solid #000;
  }
  .sign-text {
    font-size: 8pt;
    font-style: italic;
    color: #333;
    line-height: 1.4;
    margin-bottom: 8px;
  }
  .sign-disclaimer {
    font-size: 7pt;
    font-style: italic;
    color: #444;
    line-height: 1.3;
  }

  @media print {
    body { width: 100%; }
  }
</style>
</head>
<body>

@php $hotel = \App\Models\HotelSetting::first(); @endphp

<!-- HEADER -->
<div class="header">
  @if($hotel->logo_path)
    <img src="{{ asset('storage/' . $hotel->logo_path) }}" alt="Logo" class="hotel-logo">
    <div class="hotel-address">{{ $hotel->address }}</div>
  @endif
  @if($hotel->phone || $hotel->email)
    <div class="hotel-address">
      @if($hotel->phone)Telp: {{ $hotel->phone }}@endif
      @if($hotel->phone && $hotel->email) | @endif
      @if($hotel->email){{ $hotel->email }}@endif
    </div>
  @endif
  <div class="header-rule-top"></div>
  <h2>REGISTRATION FORM &nbsp;/&nbsp; FORMULIR PENDAFTARAN</h2>
  <div class="header-rule-bot"></div>
</div>

<table>

  <!-- ROW 1: Arrival / ETA / Departure / ETD -->
  <tr>
    <td style="width:26%">
      <span class="lbl">Arrival Date / Tgl. Kedatangan</span>
      <span class="field-value">{{ $reservation->check_in ? $reservation->check_in->format('d/m/Y') : '' }}</span>
    </td>
    <td style="width:24%">
      <span class="lbl">Flight / ETA / Penerbangan</span>
      <span class="wline"></span>
    </td>
    <td style="width:26%">
      <span class="lbl">Departure Date / Tgl. Keberangkatan</span>
      <span class="field-value">{{ $reservation->check_out ? $reservation->check_out->format('d/m/Y') : '' }}</span>
    </td>
    <td style="width:24%">
      <span class="lbl">Flight / ETD / Penerbangan</span>
      <span class="wline"></span>
    </td>
  </tr>

  <!-- ROW 2: No. of Guest / Room Type / Room Rate / Room No / Conf. No -->
  <tr>
    <td style="width:14%">
      <span class="lbl">No. of Guest</span>
      <span class="lbl-id">Jumlah Tamu</span>
      <span class="wline"></span>
    </td>
    <td style="width:22%">
      <span class="lbl">Room Type / Jenis Kamar</span>
      <span class="field-value">{{ $reservation->room->room_type_name ?? '' }}</span>
    </td>
    <td style="width:20%">
      <span class="lbl">Room Rate / Tarif Kamar</span>
      <span class="field-value">{{ $reservation->custom_room_rate ? 'Rp '.number_format($reservation->custom_room_rate, 0, ',', '.') : ($reservation->total_amount ? 'Rp '.number_format($reservation->total_amount, 0, ',', '.') : '') }}</span>
    </td>
    <td style="width:14%">
      <span class="lbl">Room No.</span>
      <span class="lbl-id">No. Kamar</span>
      <span class="field-value">{{ $reservation->room->room_number ?? '' }}</span>
    </td>
    <td style="width:30%">
      <span class="lbl">Conf. No. / No. Konfirmasi</span>
      <span class="field-value">{{ $reservation->reservation_number }}</span>
    </td>
  </tr>

  <!-- ROW 3: First Name / Name | Folio Number -->
  <tr>
    <td colspan="3">
      <span class="lbl">First Name / Nama Depan &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Name / Nama</span>
      <span class="field-value">{{ $reservation->guest->guest_name ?? '' }}</span>
    </td>
    <td colspan="2">
      <span class="lbl">Folio Number / No. Folio</span>
      <span class="field-value">{{ $reservation->reservation_number }}</span>
    </td>
  </tr>

  <!-- ROW 4: Address (rowspan 3) | Passport No -->
  <tr>
    <td colspan="3" rowspan="3" style="vertical-align:top; padding-top:3px;">
      <span class="lbl">Address / Residential / Rumah Alamat</span>
      <span class="field-value" style="min-height:30px;">{{ $reservation->guest->address ?? '' }}</span>
    </td>
    <td colspan="2">
      <span class="lbl">Passport No. / ID No.</span>
      <span class="lbl-id">No. Passport / No. KTP / SIM</span>
      <span class="field-value">{{ $reservation->guest->id_number ?? '' }}</span>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <span class="lbl">Nationality / Kebangsaan</span>
      <span class="wline"></span>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <span class="lbl">No. Membership Card / No. Keanggotaan</span>
      <span class="wline"></span>
    </td>
  </tr>

  <!-- ROW: Date of Birth / Vehicle No -->
  <tr>
    <td colspan="3">
      <span class="lbl">Date of Birth / Tanggal Lahir</span>
      <span class="wline"></span>
    </td>
    <td colspan="2">
      <span class="lbl">No. of Vehicle / No. Kendaraan</span>
      <span class="wline"></span>
    </td>
  </tr>

  <!-- ROW: City / State-Province / Company -->
  <tr>
    <td style="width:22%">
      <span class="lbl">City / Kota</span>
      <span class="wline"></span>
    </td>
    <td style="width:22%">
      <span class="lbl">State / Province / Provinsi</span>
      <span class="wline"></span>
    </td>
    <td style="width:22%">
      <span class="lbl">Company / Perusahaan</span>
      <span class="wline"></span>
    </td>
    <td colspan="2">
      <span class="lbl">Postal Code / Kode Pos</span>
      <span class="wline"></span>
    </td>
  </tr>

  <!-- ROW: Country / Email -->
  <tr>
    <td colspan="2">
      <span class="lbl">Country / Negara</span>
      <span class="wline"></span>
    </td>
    <td colspan="3">
      <span class="lbl">E-mail / Surel</span>
      <span class="field-value">{{ $reservation->guest->email ?? '' }}</span>
    </td>
  </tr>

  <!-- ROW: Telephone / Mobile -->
  <tr>
    <td>
      <span class="lbl">Telephone / Telepon</span>
      <span class="field-value">{{ $reservation->guest->phone ?? '' }}</span>
    </td>
    <td>
      <span class="lbl">Mobile / Handphone</span>
      <span class="wline"></span>
    </td>
    <td colspan="3">
      <span class="lbl">E-mail / Surel</span>
      <span class="field-value">{{ $reservation->guest->email ?? '' }}</span>
    </td>
  </tr>

  <!-- ROW: Payment Method -->
  <tr>
    <td colspan="5">
      <span class="lbl">Method of Payment / Metode Pembayaran</span>
      <div class="pay-row">
        <label class="pay-item"><input type="checkbox"> Cash / Tunai</label>
        <label class="pay-item"><input type="checkbox"> Visa Card</label>
        <label class="pay-item"><input type="checkbox"> Master Card</label>
        <label class="pay-item"><input type="checkbox"> American Express</label>
        <label class="pay-item"><input type="checkbox"> BCA Card</label>
        <label class="pay-item"><input type="checkbox"> JCB Card</label>
        <label class="pay-item"><input type="checkbox"> Traveller's Cheque</label>
        <label class="pay-item"><input type="checkbox"> Voucher</label>
        <label class="pay-item"><input type="checkbox"> Company Acct. / Perusahaan</label>
        <span class="pay-item" style="gap:3px;">
          <input type="checkbox" style="width:9px;height:9px;"> Others / Lain-lain :
          <span style="display:inline-block; border-bottom:1px solid #666; min-width:60px; margin-left:2px;">&nbsp;</span>
        </span>
      </div>
    </td>
  </tr>

  <!-- ROW: Terms & Conditions -->
  <tr>
    <td colspan="5" class="terms-cell">
      <p><strong>Dear Guest, Please note the following terms and conditions :<br>
      Tamu kami terhormat, harap perhatikan syarat dan ketentuan di bawah ini :</strong></p>
      <p>
        &#9658; Check-In time starts at 2pm and Check-Out time is 12noon.<br>
        <span class="t-id">(Waktu masuk hotel mulai jam 2 siang dan waktu keluar hotel jam 12 siang).</span>
      </p>
      <p>
        &#9658; Room rates are subject to 21% service charge and prevailing government tax.<br>
        <span class="t-id">(Tarif kamar belum termasuk 21% biaya pelayanan dan pajak pemerintah).</span>
      </p>
      <p>
        &#9658; Smoking is prohibited in all non-smoking floors; penalty of IDR 1,000,000 will be applied on your room folio if smoking evident found.<br>
        <span class="t-id">(Merokok di dalam kamar bebas-rokok tidak diperkenakan; denda sebesar Rp 1.000.000 akan dibebankan ke dalam tagihan kamar Anda apabila ditemukan).</span>
      </p>
      <p>
        &#9658; You agree to forfeit your deposit if smoking in non-smoking room, some damages found, there are some missing room items and/or Hotel will holdback deposit until at a later time after check-out.<br>
        <span class="t-id">(Anda telah setuju untuk pengurangan dari deposit dan/atau menunda pengembalian deposit setelah registrasi keluar apabila ditemukan merokok di kamar bebas-rokok, ada kerusakan dan ada barang kamar yang hilang).</span>
      </p>
      <p>
        &#9658; Hotel cannot be sued legally for accidents/injury caused by guest's negligence. The hotel will provide assistance in accordance with the SOP in force at the hotel.<br>
        <span class="t-id">(Hotel tidak dapat dituntut secara hukum untuk kecelakaan/cidera yang bukan disebabkan oleh kesalahan pihak hotel. Pihak hotel akan berikan asistensi sesuai SOP yang berlaku di hotel).</span>
      </p>
      <p>
        &#9658; Hotel is guaranteed for a noise-free from Hotel's activities in your room, otherwise, Hotel will inform Guest in advance.<br>
        <span class="t-id">(Hotel dijamin bebas kebisingan dari semua kegiatan hotel, apabila ada kegiatan, Hotel akan memberikan informasi ke tamu terlebih dahulu).</span>
      </p>
      <p>
        &#9658; Cash Guest Deposit can only be collected at check-out and can only be requested by registered Guest(s); NO exceptions.<br>
        <span class="t-id">(Uang deposit hanya dapat diambil pada saat pendaftaran keluar dan hanya tamu terdaftar yang berhak mengambil. Tidak ada pengecualian).</span>
      </p>
      <p>
        &#9658; I agree to receive e-mails from {{ strtoupper($hotel->hotel_name ?? 'PT SANGKAN PARK') }} regarding my stay experience and exclusive benefits.<br>
        <span class="t-id">(Saya bersedia/setuju menerima surat elektronik dari {{ $hotel->hotel_name ?? 'PT SANGKAN PARK' }} mengenai pengalaman selama menginap dan termasuk keuntungannya).</span>
      </p>
      <p>
        &#9658; My signature is an authorization for the hotel to use a non-cash method for the payment of my account.<br>
        <span class="t-id">(Tanda tangan saya adalah otorisasi bagi hotel pada saat pembayaran tagihan dengan menggunakan metode pembayaran non-tunai).</span>
      </p>
    </td>
  </tr>

  <!-- ROW: Signatures -->
  <tr>
    <td colspan="2" class="sign-cell">
      <span class="sign-label">Front Office / Petugas Hotel</span>
      <div class="sign-line"></div>
    </td>
    <td style="height:55px; vertical-align:top;">
      <span class="lbl">Remark / Keterangan</span>
    </td>
    <td colspan="2" class="sign-cell-tall">
      <div class="sign-text">
        Guest Signature for registration<br>
        Tanda Tangan Tamu Pendaftaran<br><br>
        I Agree Rp. 1.000.000 for smoking / durian / pets penalty<br>
        <em>Saya setuju denda Rp. 1.000.000 apabila merokok / membawa durian &amp; hewan</em>
      </div>
      <div class="sign-line" style="margin-bottom:2px;"></div>
      <div class="sign-disclaimer">
        Regardless of charge instruction, I hereby acknowledge to be personally responsible for the payment of account.<br>
        <em>Dengan memahami instruksi penagihan yang ada, saya mengetahui bahwa saya pribadi akan bertanggung jawab atas seluruh pembayaran.</em>
      </div>
    </td>
  </tr>

  <!-- HOTEL USE ONLY -->
  <tr>
    <td colspan="5" class="hotel-use-hd">
      Hotel Use Only / Hanya diisi oleh petugas Hotel
    </td>
  </tr>
  <tr>
    <td colspan="5" style="height:20px;">&nbsp;</td>
  </tr>

</table>

</body>
</html>
