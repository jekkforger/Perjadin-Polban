<div class="document-container">

    <!-- =========== HEADER =========== -->
    <div class="header">
      <img src="{{ asset('img/polban.png') }}" alt="POLBAN Logo" />
      <div class="header-text">
        <h1>KEMENTERIAN PENDIDIKAN TINGGI, SAINS,<br>DAN TEKNOLOGI</h1>
        <h2>POLITEKNIK NEGERI BANDUNG</h2>
        <p>Jalan Gegerkalong Hilir, Desa Ciwaruga, Bandung 40012, Kotak Pos 1234,</p>
        <p>Telepon: (022) 2013789, Faksimile: (022) 2013889</p>
        <p>Laman: <a href="https://www.polban.ac.id" target="_blank">www.polban.ac.id</a>,
           Pos Elektronik: polban@polban.ac.id</p>
      </div>
    </div>
    <hr class="header-line" />

    <!-- =========== ISI UTAMA HALAMAN =========== -->
    <div class="content">
      <div style="width: fit-content; margin-left: auto; margin-right: auto; transform: translateX(30px); text-align: left;">
        <h4 class="surattugas" style="text-decoration: underline; margin-bottom: 4px;">SURAT TUGAS</h4>
        <h4 class="nomor" style="margin-top: 0;">Nomor: {{ $suratTugas->nomor_surat_tugas_resmi ?? '[Nomor Belum Diberikan]' }}</h4>
      </div>

      <p style="margin-bottom: 10px;">
        Direktur memberi tugas kepada:
      </p>

      {{-- Daftar Personel yang Ditugaskan --}}
      <div id="daftar_personel_surat_tugas" style="margin-bottom: 15px;">
        @forelse ($suratTugas->detailPelaksanaTugas as $detail)
          @php
            $personel = $detail->personable;
            $isPegawai = $detail->personable_type === \App\Models\Pegawai::class;
          @endphp

          <table class="table table-borderless table-sm mb-3" style="width: 100%;">
            <tbody>
              <tr>
                <td style="width: 30%;">Nama</td>
                <td style="width: 5%;">:</td>
                <td>{{ $personel->nama ?? '-' }}</td>
              </tr>
              @if ($isPegawai)
                <tr>
                  <td>NIP</td>
                  <td>:</td>
                  <td>{{ $personel->nip ?? '-' }}</td>
                </tr>
                <tr>
                  <td>Pangkat / Golongan</td>
                  <td>:</td>
                  <td>{{ ($personel->pangkat ?? '-') . ' / ' . ($personel->golongan ?? '-') }}</td>
                </tr>
                <tr>
                  <td>Jabatan</td>
                  <td>:</td>
                  <td>{{ $personel->jabatan ?? '-' }}</td>
                </tr>
              @else
                <tr>
                  <td>NIM</td>
                  <td>:</td>
                  <td>{{ $personel->nim ?? '-' }}</td>
                </tr>
                <tr>
                  <td>Jurusan</td>
                  <td>:</td>
                  <td>{{ $personel->jurusan ?? '-' }}</td>
                </tr>
                <tr>
                  <td>Program Studi</td>
                  <td>:</td>
                  <td>{{ $personel->prodi ?? '-' }}</td>
                </tr>
              @endif
            </tbody>
          </table>
        @empty
          <p class="text-muted fst-italic">Tidak ada personel yang ditugaskan.</p>
        @endforelse
      </div>

      <p style="margin-top: 20px; margin-bottom: 10px;">
        Untuk mengikuti kegiatan <span class="fw-bold">{{ $suratTugas->perihal_tugas }}</span>, diselenggarakan oleh
        <span class="fw-bold">{{ $suratTugas->sumber_dana }}</span> pada:
      </p>

      <!-- Detail Kegiatan -->
      <table class="table table-borderless table-sm" style="width: 100%;">
        <tbody>
          <tr>
            <td style="width: 30%;">Hari / Tandsdadggal</td>
            <td style="width: 5%;">:</td>
            <td>
              @if ($suratTugas->tanggal_berangkat->isSameDay($suratTugas->tanggal_kembali))
                {{ $suratTugas->tanggal_berangkat->translatedFormat('l, j F Y') }}
              @else
                {{ $suratTugas->tanggal_berangkat->translatedFormat('l, j F Y') }} →
                {{ $suratTugas->tanggal_kembali->translatedFormat('l, j F Y') }}
              @endif
            </td>
          </tr>
          <tr>
            <td>Tempat</td>
            <td>:</td>
            <td>
              {{ $suratTugas->tempat_kegiatan }}<br>
              {!! nl2br(e($suratTugas->alamat_kegiatan)) !!}
            </td>
          </tr>
        </tbody>
      </table>

      {{-- Jika ada surat undangan --}}
      @if ($suratTugas->path_file_surat_usulan)
        <p style="margin-top: 20px;">
          <a href="{{ Storage::url($suratTugas->path_file_surat_usulan) }}" target="_blank" class="btn btn-sm btn-outline-info">
            <i class="fas fa-file-alt"></i> Unduh Surat Undangan
          </a>
        </p>
      @endif

      <p style="margin-top: 20px;">
        Surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.
      </p>

      <!-- =========== FOOTER GRID (Tembusan + Signature) =========== -->
      <table class="table table-borderless table-sm mt-5" style="width: 100%;">
        <tr valign="top">
          <!-- Kolom Tembusan -->
          <td style="width: 50%; vertical-align: bottom;">
            <div>
              <p class="fw mb-1">Tembusan:</p>
              <ol class="mb-0">
                @forelse ($suratSettings->tembusan_default ?? [] as $tembusan)
                  <li>{{ $tembusan }}</li>
                @empty
                  <li>-</li>
                @endforelse
              </ol>
            </div>
          </td>

          <!-- Kolom Tanda Tangan Direktur -->
          <td style="width: 50%; text-align: center; vertical-align: bottom;">
            <p class="mb-1">Bandung,
              <!-- {{ $suratTugas->tanggal_paraf_wadir ? $suratTugas->tanggal_paraf_wadir->translatedFormat('j F Y') : \Carbon\Carbon::now()->translatedFormat('j F Y') }} -->
            </p>
            <p class="mb-1">Direktur,</p>

            {{-- Signature Image --}}
            @if($suratTugas->direktur_signature_data)
              @php
                $position = $suratTugas->direktur_signature_position ?? ['x' => 0, 'y' => -10, 'width' => 100, 'height' => 60];
              @endphp
              <div style="position: relative; height: {{ $position['height'] + 5 }}px; margin-bottom: 5px;">
                <img src="{{ $suratTugas->direktur_signature_data }}" alt="Tanda Tangan Direktur" style="position: absolute; 
                  left: {{ $position['x'] }}px; 
                  top: {{ $position['y'] }}px; 
                  width: {{ $position['width'] }}px; 
                  height: {{ $position['height'] }}px; 
                  object-fit: contain;">
              </div>
            @else
              <div style="height: 60px;"></div>
            @endif

            {{-- Nama Direktur & NIP --}}
            <p class="fw mb-0" style="margin-top: -5px;">{{ $suratSettings->nama_direktur ?? '' }}</p>
            <p class="mb-0">NIP {{ $suratSettings->nip_direktur ?? '' }}</p>
          </td>
        </tr>
      </table>
    </div>
    <!-- =========== AKHIR ISI UTAMA =========== -->

</div>