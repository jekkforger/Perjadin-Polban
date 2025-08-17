<!-- Langkah 2: Data Personel -->
<div id="data-section" class="form-step">
  <h3>Data Personel</h3>
  <p>*Centang untuk memilih pegawai/mahasiswa yang akan ditugaskan!</p>

  <!-- Dropdown Pilih Data dan Pencarian -->
  <div class="mb-3 d-flex justify-content-between align-items-center">
    <div>
      <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="data-selection-dropdown" data-bs-toggle="dropdown">
          Pilih Data
        </button>
        <ul class="dropdown-menu" aria-labelledby="data-selection-dropdown">
          <li><a class="dropdown-item" href="#" data-value="data-pegawai">Data Pegawai</a></li>
          <li><a class="dropdown-item" href="#" data-value="data-mahasiswa">Data Mahasiswa</a></li>
        </ul>
      </div>
    </div>
    <div class="d-flex align-items-center">
      <input type="text" class="form-control me-2" id="search-input" placeholder="Search" style="max-width: 200px;">
      <button class="btn btn-primary" id="search-button">Search</button>
    </div>
  </div>

  <!-- Tabel Data Pegawai -->
  <div class="table-responsive" id="data-pegawai-table">
    <table class="table table-striped" id="pegawaiTable">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all-pegawai"></th>
          <th>No</th>
          <th>Nama</th>
          <th>NIP</th>
          <th>Pangkat</th>
          <th>Golongan</th>
          <th>Jabatan</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($pegawais as $index => $pegawai)
          <tr>
            <td>
              <input type="checkbox" name="pegawai_ids[]" value="{{ $pegawai->id }}"
                class="personel-checkbox"
                data-id="{{ $pegawai->id }}"
                data-type="pegawai"
                data-nama="{{ $pegawai->nama }}"
                data-nip="{{ $pegawai->nip ?? '-' }}"
                data-pangkat="{{ $pegawai->pangkat ?? '-' }}"
                data-golongan="{{ $pegawai->golongan ?? '-' }}"
                data-jabatan="{{ $pegawai->jabatan ?? '-' }}"
                data-jurusan=""
                data-prodi=""
                onchange="updateSelectedPersonel(this)">
            </td>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $pegawai->nama }}</td>
            <td>{{ $pegawai->nip ?? '-' }}</td>
            <td>{{ $pegawai->pangkat ?? '-' }}</td>
            <td>{{ $pegawai->golongan ?? '-' }}</td>
            <td>{{ $pegawai->jabatan ?? '-' }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center">Tidak ada data pegawai.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Tabel Data Mahasiswa -->
  <div class="table-responsive" id="data-mahasiswa-table" style="display: none;">
    <table class="table table-striped" id="mahasiswaTable">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all-mahasiswa"></th>
          <th>No</th>
          <th>Nama</th>
          <th>NIM</th>
          <th>Jurusan</th>
          <th>Prodi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($mahasiswa as $index => $mhs)
          <tr>
            <td>
              <input type="checkbox" name="mahasiswa_ids[]" value="{{ $mhs->id }}"
                class="personel-checkbox"
                data-id="{{ $mhs->id }}"
                data-type="mahasiswa"
                data-nama="{{ $mhs->nama }}"
                data-nip=""
                data-pangkat=""
                data-golongan=""
                data-jabatan=""
                data-nim="{{ $mhs->nim ?? '-' }}"
                data-jurusan="{{ $mhs->jurusan ?? '-' }}"
                data-prodi="{{ $mhs->prodi ?? '-' }}"
                onchange="updateSelectedPersonel(this)">
            </td>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $mhs->nama }}</td>
            <td>{{ $mhs->nim ?? '-' }}</td>
            <td>{{ $mhs->jurusan ?? '-' }}</td>
            <td>{{ $mhs->prodi ?? '-' }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center">Tidak ada data mahasiswa.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Tabel Personel Terpilih -->
  <div class="mt-4" id="selectedPersonelContainer" style="display: none;">
    <h5 class="mb-3">Personel Terpilih:</h5>
    <div class="table-responsive">
      <table class="table table-bordered" id="selectedPersonelTable">
        <thead class="table-primary">
          <tr>
            <th>No</th>
            <th>Nama</th>
            <th>NIP/NIM</th>
            <th>Jabatan/Jurusan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="selectedPersonelList">
          <!-- Diisi melalui JavaScript -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Tombol Aksi -->
  {{-- <div class="button-next mt-3 d-flex gap-2">
    <button type="button" class="btn btn-secondary" id="back">Kembali</button>
    <button type="button" class="btn btn-success" id="create-task">Buat Surat Tugas</button>
    <button type="button" class="btn btn-warning" id="save-draft">Simpan Draft</button>
  </div> --}}
</div>
