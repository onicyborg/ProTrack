@extends('layouts.master')

@section('page_title', 'Data Klien')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">Data Klien</h3>
        <button class="btn btn-primary" id="btnAddClient" data-bs-toggle="modal" data-bs-target="#clientModal">
            <i class="bi bi-plus-lg me-2"></i>Tambah Klien
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="client_table" class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>Nama Klien</th>
                            <th>Kontak</th>
                            <th>Alamat</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($clients as $i => $client)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $client->client_name }}</td>
                                <td>{{ $client->contact ?? '-' }}</td>
                                <td>{{ $client->address ?? '-' }}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-light-primary btnEditClient"
                                        data-id="{{ $client->id }}"
                                        data-client_name="{{ $client->client_name }}"
                                        data-contact="{{ $client->contact }}"
                                        data-address="{{ $client->address }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light-danger btnDeleteClient"
                                        data-id="{{ $client->id }}"
                                        data-name="{{ $client->client_name }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Create/Edit --}}
    <div class="modal fade" id="clientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="clientForm" method="POST" action="{{ route('admin.clients.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="clientFormMethod" value="POST">

                    <div class="modal-header">
                        <h5 class="modal-title" id="clientModalTitle">Tambah Klien</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label required">Nama Klien</label>
                            <input type="text" name="client_name" id="client_name" class="form-control" placeholder="Nama klien" required />
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Kontak</label>
                            <input type="text" name="contact" id="client_contact" class="form-control" placeholder="No. telepon / email" />
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" id="client_address" class="form-control" rows="3" placeholder="Alamat klien"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveClient">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Confirm Delete --}}
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Hapus Klien</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Yakin ingin menghapus klien <strong id="delete_name">-</strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Init DataTable
        var dt = jQuery('#client_table').DataTable({
            pageLength: 10,
            ordering: true,
        });

        // Refs
        var clientForm       = document.getElementById('clientForm');
        var clientFormMethod  = document.getElementById('clientFormMethod');
        var clientModalTitle  = document.getElementById('clientModalTitle');
        var clientModal       = document.getElementById('clientModal');
        var storeUrl          = "{{ route('admin.clients.store') }}";
        var baseUrl           = "{{ url('admin/clients') }}";

        // Tombol Tambah — reset form ke mode Create
        document.getElementById('btnAddClient').addEventListener('click', function () {
            clientForm.action          = storeUrl;
            clientFormMethod.value     = 'POST';
            clientModalTitle.textContent = 'Tambah Klien';
            clientForm.reset();
        });

        // Delegated: Tombol Edit
        jQuery('#client_table').on('click', '.btnEditClient', function () {
            var btn = this;
            clientForm.action          = baseUrl + '/' + btn.dataset.id;
            clientFormMethod.value     = 'PUT';
            clientModalTitle.textContent = 'Edit Klien';

            document.getElementById('client_name').value    = btn.dataset.client_name || '';
            document.getElementById('client_contact').value = btn.dataset.contact || '';
            document.getElementById('client_address').value = btn.dataset.address || '';

            var modal = bootstrap.Modal.getOrCreateInstance(clientModal);
            modal.show();
        });

        // Delegated: Tombol Delete
        jQuery('#client_table').on('click', '.btnDeleteClient', function () {
            var btn = this;
            document.getElementById('deleteForm').action       = baseUrl + '/' + btn.dataset.id;
            document.getElementById('delete_name').textContent = btn.dataset.name;

            var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmDeleteModal'));
            modal.show();
        });
    });
</script>
@endpush
