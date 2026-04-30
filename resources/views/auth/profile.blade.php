@extends('layouts.master')

@section('page_title', 'Profil Saya')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold mb-0">Profil Pengguna</h3>
            <span class="text-muted">Perbarui informasi dasar, foto, dan password akun Anda.</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-5">
        <div class="col-lg-4">
            <div class="card card-flush h-100">
                <div class="card-body text-center">
                    @php
                        $fallbackAvatar = asset('assets/media/avatars/blank.png');
                        $avatarPath = trim((string) ($user->avatar_path ?? ''));
                        $avatarPath = ltrim($avatarPath, '/');
                        $avatarPath = preg_replace('/^storage\//', '', $avatarPath);
                        $avatarUrl = !empty($avatarPath) ? url('storage/' . $avatarPath) : null;
                    @endphp
                    <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url('{{ $fallbackAvatar }}')">
                        <div class="image-input-wrapper w-150px h-150px" style="background-image: url('{{ $avatarUrl ?? $fallbackAvatar }}');"></div>
                        <label class="btn btn-icon btn-circle btn-active-color-primary w-35px h-35px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Ganti foto">
                            <i class="bi bi-pencil-fill fs-7"></i>
                            <input type="file" name="avatar" accept=".png, .jpg, .jpeg, .webp" form="profile_form" />
                            <input type="hidden" name="avatar_remove" />
                        </label>
                        <span class="btn btn-icon btn-circle btn-active-color-primary w-35px h-35px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Batalkan">
                            <i class="bi bi-x fs-2"></i>
                        </span>
                    </div>
                    <h4 class="fw-bold mt-4 mb-1">{{ $employee?->employee_name ?? $user->username }}</h4>
                    <div class="text-muted mb-4 text-uppercase">{{ $user->role ?? '-' }}</div>
                    <div class="border-top pt-4 text-start">
                        <div class="mb-3">
                            <span class="text-muted d-block">Username</span>
                            <span class="fw-semibold">{{ $user->username }}</span>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted d-block">Email</span>
                            <span class="fw-semibold">{{ $user->email ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-muted d-block">Terakhir diperbarui</span>
                            <span class="fw-semibold">{{ optional($user->updated_at)->format('d M Y H:i') ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-flush h-100">
                <div class="card-header">
                    <div class="card-title">
                        <h4 class="fw-bold mb-0">Informasi Akun</h4>
                    </div>
                </div>
                <div class="card-body">
                    <form id="profile_form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-5">
                            <div class="col-md-6">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" value="{{ old('username', $user->username) }}" class="form-control @error('username') is-invalid @enderror" required>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-5 mt-1">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="employee_name" value="{{ old('employee_name', $employee?->employee_name ?? '') }}" class="form-control @error('employee_name') is-invalid @enderror" placeholder="Nama lengkap">
                                @error('employee_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jabatan</label>
                                <input type="text" name="position" value="{{ old('position', $employee?->position ?? '') }}" class="form-control @error('position') is-invalid @enderror" placeholder="Contoh: Project Manager">
                                @error('position')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-5 mt-1">
                            <div class="col-md-6">
                                <label class="form-label">Nomor Telepon</label>
                                <input type="text" name="phone_number" value="{{ old('phone_number', $employee?->phone_number ?? '') }}" class="form-control @error('phone_number') is-invalid @enderror" placeholder="Contoh: 0812xxx">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NIK</label>
                                <input type="text" name="nik" value="{{ old('nik', $employee?->nik ?? '') }}" class="form-control @error('nik') is-invalid @enderror" placeholder="Nomor Induk Kependudukan">
                                @error('nik')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-5 mt-1">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="date" name="birth_date" value="{{ old('birth_date', $employee?->birth_date ?? '') }}" class="form-control @error('birth_date') is-invalid @enderror">
                                @error('birth_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jenis Kelamin</label>
                                <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                    <option value="" {{ old('gender', $employee?->gender ?? '') === '' ? 'selected' : '' }}>-</option>
                                    <option value="L" {{ old('gender', $employee?->gender ?? '') === 'L' ? 'selected' : '' }}>L</option>
                                    <option value="P" {{ old('gender', $employee?->gender ?? '') === 'P' ? 'selected' : '' }}>P</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-5 mt-1">
                            <div class="col-12">
                                <label class="form-label">Alamat</label>
                                <textarea name="address" rows="3" class="form-control @error('address') is-invalid @enderror" placeholder="Alamat lengkap">{{ old('address', $employee?->address ?? '') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-5 mt-1">
                            <div class="col-md-6">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Kosongkan jika tidak diubah">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-5">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
