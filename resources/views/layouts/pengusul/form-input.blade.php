<!-- Langkah 1: Informasi Dasar Kegiatan -->
  <div id="initial-form" class="form-step">
    <div class="row">
      <!-- Bagian Kiri -->
      <div class="col-md-6">
        <!-- Nama Kegiatan -->
        <div class="mb-3 mt-4">
          <label for="nama_kegiatan" class="form-label">Nama Kegiatan *</label>
          <textarea class="form-control" id="nama_kegiatan" name="nama_kegiatan" placeholder="Nama Kegiatan" rows="3" required>{{ old('nama_kegiatan') }}</textarea>
        </div>

        <!-- Tempat Kegiatan -->
        {{-- <div class="mb-3">
          <label for="tempat_kegiatan" class="form-label">Tempat Kegiatan *</label>
          <textarea class="form-control" id="tempat_kegiatan" name="tempat_kegiatan" placeholder="Tempat Kegiatan Kegiatan" rows="3" required>{{ old('tempat_kegiatan') }}</textarea>
        </div> --}}

        <!-- Diusulkan Kepada -->
        <div class="form-section mb-4">
          <label for="diusulkan_kepada" class="form-label">Diusulkan Kepada *</label>
          <div class="d-flex align-items-end gap-2">
            <input type="text" class="form-control" id="diusulkan_kepada" name="diusulkan_kepada" placeholder="Diusulkan Kepada" readonly required value="{{ old('diusulkan_kepada') }}">
            <div class="dropdown">
              <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Pilih</button>
              <ul class="dropdown-menu">
                @foreach (['I','II','III','IV'] as $i)
                  <li>
                    <a class="dropdown-item pilih-option" data-target="diusulkan_kepada" data-value="Wakil Direktur {{ $i }}" href="#">
                      Wakil Direktur {{ $i }}
                    </a>
                  </li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>

        <!-- Surat Undangan -->
        <div class="form-section mb-4">
          <label for="surat_undangan" class="form-label">Surat Undangan (Jika ada)</label>
          <input type="file" class="form-control" name="surat_undangan" id="surat_undangan" accept=".pdf,.jpg,.png,.doc,.docx">
        </div>

        <!-- Pembiayaan -->
        <div class="form-section mb-4">
          <label class="form-label">Pembiayaan *</label>
          <input type="hidden" name="pembiayaan" id="pembiayaan_value" value="{{ old('pembiayaan', 'Polban') }}">
          @foreach (['Polban', 'Penyelenggara', 'Polban dan Penyelenggara'] as $option)
            <div class="form-check">
              <input type="radio" class="form-check-input" name="pembiayaan_option" id="pembiayaan_{{ Str::slug($option) }}" value="{{ $option }}" {{ old('pembiayaan', 'Polban') === $option ? 'checked' : '' }}>
              <label class="form-check-label" for="pembiayaan_{{ Str::slug($option) }}">{{ $option }}</label>
            </div>
          @endforeach
        </div>
      </div>

      <!-- Bagian Kanan -->
      <div class="col-md-6">
        <!-- Nama Penyelenggara -->
        <div class="mb-3 mt-4">
          <label for="nama_penyelenggara" class="form-label">Nama Penyelenggara Kegiatan</label>
          <input type="text" class="form-control" id="nama_penyelenggara" name="nama_penyelenggara" placeholder="Nama Penyelenggara Kegiatan" value="{{ old('nama_penyelenggara') }}">
        </div>

        <!-- Tanggal Pelaksanaan -->
        <div class="mb-3">
          <label for="tanggal_pelaksanaan" class="form-label">Tanggal Pelaksanaan *</label>
          <input type="text" id="tanggal_pelaksanaan" name="tanggal_pelaksanaan" placeholder="Tanggal Pelaksanaan" class="form-control" readonly required value="{{ old('tanggal_pelaksanaan') }}">
        </div>

        <!-- Pagu Desentralisasi -->
        <div class="mb-3">
          <label class="form-label">Pagu</label>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="pagu_desentralisasi_checkbox" name="pagu_desentralisasi" {{ old('pagu_desentralisasi') ? 'checked' : '' }}>
            <label class="form-check-label" for="pagu_desentralisasi_checkbox">Desentralisasi</label>
          </div>
        </div>

        <!-- Nominal Pagu -->
        <div class="mb-3" id="pagu_nominal_input_group" style="{{ old('pagu_desentralisasi') ? '' : 'display:none;' }}">
          <label for="pagu_nominal" class="form-label">Nominal Pagu</label>
          <input type="number" class="form-control" id="pagu_nominal" name="pagu_nominal" placeholder="Contoh: 1500000" value="{{ old('pagu_nominal') }}">
        </div>

          <!-- Alamat Kegiatan -->
          {{-- <div class="mb-3">
              <label for="alamat_kegiatan" class="form-label">Alamat Kegiatan *</label>
              <div class="input-group">
                  <textarea class="form-control" id="alamat_kegiatan" name="alamat_kegiatan" placeholder="Alamat Kegiatan" rows="3" required>{{ old('alamat_kegiatan') }}</textarea>
              </div>
          </div> --}}

        <div class="mb-3">
          <label class="form-label">Tempat & Alamat Kegiatan *</label>
          <div id="lokasi-wrapper">
              {{-- Lokasi pertama (wajib ada) --}}
              <div class="input-group mb-2 lokasi-entry">
                  <span class="input-group-text">1.</span>
                  <input type="text" name="lokasi[0][tempat]" class="form-control" placeholder="Tempat Kegiatan" required>
                  <input type="text" name="lokasi[0][alamat]" class="form-control" placeholder="Alamat" required>
                  {{-- Tombol hapus tidak ada untuk yang pertama --}}
              </div>
          </div>
          <button type="button" id="btn-tambah-lokasi" class="btn btn-sm btn-outline-success mt-2">
              <i class="fas fa-plus"></i> Tambah Lokasi
          </button>
      </div>

        <!-- Provinsi -->
        <div class="form-section mb-4">
          <label for="provinsi" class="form-label">Provinsi *</label>
          <div class="d-flex align-items-end gap-2">
            <input type="text" class="form-control" id="provinsi" name="provinsi" placeholder="Provinsi" readonly required value="{{ old('provinsi') }}">
            <input type="hidden" id="provinsi_id" name="provinsi_id" value="{{ old('provinsi_id') }}">
            <div class="dropdown">
              <button class="btn btn-secondary dropdown-toggle" type="button" id="btn-provinsi-dropdown" data-bs-toggle="dropdown">
                Pilih
              </button>
              <ul class="dropdown-menu" id="provinsi-dropdown-menu">
                <li class="px-2 py-1">
                  <input type="text" class="form-control form-control-sm" id="search-provinsi-input" placeholder="Cari provinsi...">
                </li>
                <li>
                  <div id="provinsi-scroll-container" style="max-height: 200px; overflow-y: auto;"></div>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Nomor Surat Usulan -->
        @if($errors->has('nomor_surat_usulan'))
          <div class="alert alert-danger d-flex align-items-center mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>{{ $errors->first('nomor_surat_usulan') }}</div>
          </div>
        @endif

        <div class="mb-3">
          <label class="form-label">Nomor Surat Usulan *</label>
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <input type="text" name="nomor_urutan_surat" class="form-control @error('nomor_urutan_surat') is-invalid @enderror" placeholder="Nomor Urut" required style="width: 80px;" value="{{ old('nomor_urutan_surat') }}">
            /
            <input type="text" class="form-control" value="{{ Auth::user()->kode_pengusul }}" readonly style="width: 60px;">
            <input type="hidden" name="kode_pengusul" value="{{ Auth::user()->kode_pengusul }}">
            /
            <input type="text" class="form-control" value="{{ $kodePerihal ?? 'RT.01.00' }}" readonly style="width: 90px; background:#eee; font-weight:500;">
            <input type="hidden" name="kode_perihal" value="{{ $kodePerihal ?? 'RT.01.00' }}">
            /
            <select name="tahun_nomor_surat" class="form-select @error('tahun_nomor_surat') is-invalid @enderror" required style="width: 90px;">
              @foreach($tahunList as $tahun)
                <option value="{{ $tahun }}" {{ old('tahun_nomor_surat') == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
              @endforeach
            </select>
          </div>

          
          <!-- Elemen untuk menampilkan feedback real-time -->
          <div id="nomor-surat-feedback" class="mt-2" style="font-size: 0.9em;"></div>

          <!-- Tombol dan daftar nomor terpakai -->
          <div class="mt-2">
              <a class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" href="#collapseNomorTerpakai" role="button" aria-expanded="false" aria-controls="collapseNomorTerpakai">
                  Lihat Nomor Terpakai (30 Hari Terakhir)
              </a>
              <div class="collapse mt-2" id="collapseNomorTerpakai">
                  <div class="card card-body" style="max-height: 150px; overflow-y: auto;">
                      <ul id="nomor-surat-list-ul">
                          <li class="text-muted">Memuat...</li>
                      </ul>
                  </div>
              </div>
          </div>

          <div id="nomor_surat_error_container" class="invalid-feedback d-block mt-2"></div>
          @error('nomor_surat_usulan_jurusan')
                  <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
              @enderror
          </div>
          {{-- Area untuk feedback validasi ketersediaan nomor surat --}}
          <!-- <div id="nomor-surat-feedback" class="mt-2" style="font-size: 0.875em;"></div>
                <div id="used-nomor-surat-list" class="mt-3" style="font-size: 0.85em;">
                    <p class="mb-1">Nomor Surat Terpakai (30 Hari Terakhir):</p>
                    <ul class="list-unstyled" id="nomor-surat-list-ul">
                    </ul>
                </div>
                
                @error('nomor_urutan_surat') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                @error('tahun_nomor_surat') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                @error('kode_pengusul') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div> -->
        </div>
      </div>
    </div>

    <!-- Tombol Lanjut -->
    {{-- <div class="button-next mt-3">
      <button type="button" class="btn btn-primary" id="next-to-personel">Selanjutnya</button>
    </div> --}}
  </div>
</form>

