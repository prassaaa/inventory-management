<div class="form-group mb-3">
    <label for="name" class="form-label">Nama Satuan <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $unit->name ?? '') }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group mb-3">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="is_base_unit" name="is_base_unit" value="1" 
               {{ old('is_base_unit', $unit->is_base_unit ?? false) ? 'checked' : '' }}
               onchange="toggleBaseUnitFields()">
        <label class="form-check-label" for="is_base_unit">
            Ini adalah satuan dasar
        </label>
    </div>
    <small class="form-text text-muted">Centang jika satuan ini adalah satuan dasar untuk konversi (contoh: gram, meter)</small>
    @error('is_base_unit')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>

<div id="derived_unit_fields" class="card mb-4 border-0 bg-light p-3 rounded" style="{{ old('is_base_unit', $unit->is_base_unit ?? false) ? 'display:none' : '' }}">
    <h6 class="fw-bold text-primary mb-3">Pengaturan Satuan Turunan</h6>
    
    <div class="form-group mb-3">
        <label for="base_unit_id" class="form-label">Satuan Dasar <span class="text-danger">*</span></label>
        <select class="form-select @error('base_unit_id') is-invalid @enderror" id="base_unit_id" name="base_unit_id">
            <option value="">Pilih Satuan Dasar</option>
            @foreach($baseUnits as $baseUnit)
                <option value="{{ $baseUnit->id }}" {{ old('base_unit_id', $unit->base_unit_id ?? '') == $baseUnit->id ? 'selected' : '' }}>
                    {{ $baseUnit->name }}
                </option>
            @endforeach
        </select>
        <small class="form-text text-muted">Satuan dasar yang akan digunakan untuk konversi</small>
        @error('base_unit_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group mb-3">
        <label for="conversion_factor" class="form-label">Faktor Konversi <span class="text-danger">*</span></label>
        <input type="number" step="0.0001" class="form-control @error('conversion_factor') is-invalid @enderror" id="conversion_factor" name="conversion_factor" value="{{ old('conversion_factor', $unit->conversion_factor ?? '1') }}">
        <small class="form-text text-muted">Berapa banyak satuan dasar yang setara dengan satuan ini? (contoh: 1000 untuk kilogram jika gram adalah satuan dasar)</small>
        @error('conversion_factor')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> {{ $submitButtonText }}
            </button>
            <a href="{{ route('units.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-times me-1"></i> Batal
            </a>
        </div>
    </div>
</div>

@section('scripts')
@parent
<script>
    function toggleBaseUnitFields() {
        if (document.getElementById('is_base_unit').checked) {
            document.getElementById('derived_unit_fields').style.display = 'none';
            
            // Reset derived unit fields
            document.getElementById('base_unit_id').value = '';
            document.getElementById('conversion_factor').value = '1';
        } else {
            document.getElementById('derived_unit_fields').style.display = 'block';
        }
    }
    
    $(document).ready(function() {
        // Initialize Select2 for dropdowns
        if ($.fn.select2) {
            $('#base_unit_id').select2({
                theme: "bootstrap-5",
                width: '100%'
            });
        }
    });
</script>
@endsection