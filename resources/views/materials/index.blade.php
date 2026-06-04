@extends('layouts.app')

@section('content')
<div class="card p-4">
    <h4 class="fw-bold mb-3">📚 Daftar Materi</h4>
    <p>Halaman ini menampilkan seluruh materi yang tersimpan di MongoDB.</p>
    <div id="materials-list">
        Memuat data...
    </div>
</div>
@endsection

@push('scripts')
<script>
async function loadMaterials() {
    try {
        const response = await fetch('/api/materials');
        const json = await response.json();
        const wrapper = document.getElementById('materials-list');
        wrapper.innerHTML = '';
        if (!json.data.length) {
            wrapper.innerHTML = '<div class="alert alert-info">Belum ada materi tersimpan.</div>';
            return;
        }
        json.data.forEach(item => {
            const div = document.createElement('div');
            div.className = 'border rounded-4 p-3 mb-3 bg-light';
            div.innerHTML = `<h5 class="fw-bold">${item.title}</h5><h6 class="text-muted">${item.subject}</h6><p>${item.content}</p>`;
            wrapper.appendChild(div);
        });
    } catch (e) {
        document.getElementById('materials-list').innerHTML = '<div class="alert alert-danger">Gagal memuat data.</div>';
    }
}
loadMaterials();
</script>
@endpush
