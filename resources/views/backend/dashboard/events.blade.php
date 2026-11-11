@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto w-full">

    <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-800 text-left">
                <i class="fa-solid fa-layer-group text-indigo-600 mr-2"></i> Manajemen Event & Form Dinamis
            </h1>
            <p class="text-sm text-gray-500 mt-1">Atur nama event dan data apa saja yang wajib diisi pengguna pada halaman depan.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-bold transition whitespace-nowrap">
            <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- FORM TAMBAH EVENT BARU --}}
        <div class="lg:col-span-5 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4">
                <i class="fa-solid fa-plus-circle text-indigo-600 mr-2"></i> Buat Event Baru
            </h2>

            <form action="{{ route('admin.events.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Event *</label>
                    <input type="text" name="name" required placeholder="Cth: Daftar Ulang Siswa Baru 2026" class="w-full border border-gray-300 p-2.5 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-400 font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Subjudul / Nama Instansi</label>
                    <input type="text" name="subtitle" value="SMK Negeri 1 Adiwerna" placeholder="Cth: SMK Negeri 1 Adiwerna" class="w-full border border-gray-300 p-2.5 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Deskripsi Singkat (Tampil di Halaman Depan)</label>
                    <textarea name="description" rows="2" placeholder="Petunjuk singkat untuk pendaftar..." class="w-full border border-gray-300 p-2.5 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3 bg-indigo-50/70 p-3 rounded-xl border border-indigo-100">
                    <div>
                        <label class="block text-[11px] font-bold text-indigo-800 mb-1">Label ID Unik *</label>
                        <input type="text" name="identifier_label" value="NISN" required placeholder="NISN / NIK / No. HP" class="w-full border border-indigo-200 bg-white p-2 rounded-lg text-xs font-bold outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-indigo-800 mb-1">Label Nama Peserta *</label>
                        <input type="text" name="name_label" value="Nama Lengkap" required placeholder="Nama Lengkap" class="w-full border border-indigo-200 bg-white p-2 rounded-lg text-xs font-bold outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[11px] font-bold text-indigo-800 mb-1">Placeholder ID Unik</label>
                        <input type="text" name="identifier_placeholder" placeholder="Cth: Ketik 10 Digit NISN / 16 Digit NIK..." class="w-full border border-indigo-200 bg-white p-2 rounded-lg text-xs outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>

                {{-- BUILDER FORM FIELD DINAMIS --}}
                <div class="border border-gray-200 rounded-xl p-3 bg-gray-50">
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-xs font-black text-gray-700 uppercase tracking-wider">
                            <i class="fa-solid fa-list-check text-indigo-600 mr-1"></i> Field Isian Pengguna
                        </label>
                        <button type="button" onclick="addFieldRow('createFieldsContainer')" class="bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold px-2.5 py-1.5 rounded-lg transition">
                            <i class="fa-solid fa-plus"></i> Tambah Field
                        </button>
                    </div>
                    <p class="text-[10px] text-gray-500 mb-3">Field ID Unik & Nama di atas selalu ada otomatis. Tambahkan data lain yang harus diisi pendaftar:</p>
                    <div id="createFieldsContainer" class="space-y-2.5 max-h-[340px] overflow-y-auto pr-1"></div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Checklist Ketentuan di e-Tiket PDF <span class="text-gray-400 font-normal">(1 baris = 1 poin, opsional)</span></label>
                    <textarea name="checklist_text" rows="2" placeholder="Membawa fotokopi KK&#10;Membawa bukti pendaftaran" class="w-full border border-gray-300 p-2 rounded-lg text-xs outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Teks Kotak Konfirmasi Sebelum Submit <span class="text-gray-400 font-normal">(opsional)</span></label>
                    <input type="text" name="confirmation_text" placeholder="Kosongkan jika tidak perlu checkbox persetujuan" class="w-full border border-gray-300 p-2 rounded-lg text-xs outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                <div class="space-y-2 bg-gray-50 p-3 rounded-xl border border-gray-200">
                    <label class="flex items-start gap-2 cursor-pointer text-xs font-bold text-gray-700">
                        <input type="checkbox" name="requires_checkin" value="1" class="w-4 h-4 mt-0.5 text-indigo-600 rounded">
                        <span>
                            Wajibkan Menu Check-In (Scanner Gerbang)
                            <span class="block text-[10px] font-normal text-gray-500">Jika tidak dicentang, peserta yang mendaftar langsung masuk antrian panggilan loket tanpa scan Check-In.</span>
                        </span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-700">
                        <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-indigo-600 rounded">
                        Aktifkan Event Ini di Halaman Depan
                    </label>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl shadow-md transition">
                    <i class="fa-solid fa-save mr-1"></i> Simpan Event
                </button>
            </form>
        </div>

        {{-- DAFTAR EVENT --}}
        <div class="lg:col-span-7 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b pb-3 mb-4 gap-3">
                <h2 class="text-lg font-bold text-gray-800">Daftar Event</h2>
                <form method="GET" action="{{ route('admin.manage_events') }}" class="flex gap-2 w-full sm:w-auto">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari event..." class="border border-gray-300 rounded-lg px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-indigo-400 w-full sm:w-52">
                    <button type="submit" class="bg-gray-800 text-white px-3 py-1.5 rounded-lg text-xs font-bold">Cari</button>
                </form>
            </div>

            <div class="space-y-4">
                @forelse($events as $ev)
                    <div class="border rounded-2xl p-4 transition {{ $ev->is_active ? 'border-indigo-200 bg-indigo-50/30' : 'border-gray-200 bg-gray-50/70 opacity-85' }}">
                        <div class="flex flex-col sm:flex-row justify-between items-start gap-3">
                            <div>
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $ev->is_active ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-gray-200 text-gray-600' }}">
                                        {{ $ev->is_active ? 'Aktif' : 'Arsip / Nonaktif' }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ev->requires_checkin ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                        <i class="fa-solid {{ $ev->requires_checkin ? 'fa-qrcode' : 'fa-forward' }} mr-1"></i>
                                        {{ $ev->requires_checkin ? 'Dengan Check-In' : 'Tanpa Check-In' }}
                                    </span>
                                    <span class="text-[11px] font-mono text-gray-500 bg-white px-2 py-0.5 rounded border">/e/{{ $ev->slug }}</span>
                                    @if($ev->is_active)
                                        <button type="button" onclick="copyEventLink('{{ route('ticket.event', $ev->slug) }}')" class="text-[11px] font-bold text-indigo-600 hover:underline">
                                            <i class="fa-regular fa-copy"></i> Salin Link
                                        </button>
                                    @endif
                                </div>

                                <h3 class="text-base font-black text-gray-800">{{ $ev->name }}</h3>
                                @if($ev->subtitle)
                                    <p class="text-xs font-semibold text-gray-500">{{ $ev->subtitle }}</p>
                                @endif

                                <div class="flex flex-wrap gap-3 mt-2 text-[11px] font-bold text-gray-600">
                                    <span><i class="fa-solid fa-id-badge text-indigo-500 mr-1"></i> ID: {{ $ev->identifier_label }}</span>
                                    <span><i class="fa-solid fa-list text-blue-500 mr-1"></i> {{ count($ev->form_fields ?? []) }} Field Tambahan</span>
                                    <span><i class="fa-solid fa-calendar text-orange-500 mr-1"></i> {{ $ev->service_dates_count }} Sesi</span>
                                    <span><i class="fa-solid fa-ticket text-green-500 mr-1"></i> {{ $ev->tickets_count }} Tiket</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <form action="{{ route('admin.events.toggle', $ev->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-white hover:bg-gray-100 text-gray-700 border border-gray-300 px-2.5 py-1.5 rounded-lg text-xs font-bold transition" title="{{ $ev->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="fa-solid fa-power-off"></i>
                                    </button>
                                </form>

                                <button type="button" onclick='openEditEventModal(@json($ev))' class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-2.5 py-1.5 rounded-lg text-xs font-bold transition" title="Edit Event & Form">
                                    <i class="fa-solid fa-edit"></i> Edit
                                </button>

                                <form action="{{ route('admin.events.destroy', $ev->id) }}" method="POST" class="delete-form" data-msg="Hapus event '{{ $ev->name }}' beserta seluruh sesi dan tiket di dalamnya?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="bg-red-100 hover:bg-red-200 text-red-700 px-2.5 py-1.5 rounded-lg text-xs font-bold transition" title="Hapus Event">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center p-12 text-gray-400 italic">Belum ada event. Silakan buat event baru di samping.</div>
                @endforelse
            </div>

            <div class="mt-6 border-t pt-4">
                {{ $events->links() }}
            </div>
        </div>
    </div>
</div>

{{-- MODAL EDIT EVENT --}}
<div id="editEventModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white p-6 rounded-2xl w-full max-w-2xl max-h-[92vh] overflow-y-auto shadow-2xl">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 class="text-xl font-black text-gray-800"><i class="fa-solid fa-pen-to-square text-blue-600 mr-2"></i> Edit Event & Form</h3>
            <button type="button" onclick="closeEditEventModal()" class="text-gray-400 hover:text-red-500 text-xl"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form id="editEventForm" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Event *</label>
                    <input type="text" name="name" id="ee_name" required class="w-full border border-gray-300 p-2.5 rounded-lg text-sm font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Subjudul / Instansi</label>
                    <input type="text" name="subtitle" id="ee_subtitle" class="w-full border border-gray-300 p-2.5 rounded-lg text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Deskripsi Singkat</label>
                <textarea name="description" id="ee_description" rows="2" class="w-full border border-gray-300 p-2.5 rounded-lg text-sm"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-blue-50/70 p-3 rounded-xl border border-blue-100">
                <div>
                    <label class="block text-[11px] font-bold text-blue-800 mb-1">Label ID Unik *</label>
                    <input type="text" name="identifier_label" id="ee_identifier_label" required class="w-full border border-blue-200 bg-white p-2 rounded-lg text-xs font-bold">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-blue-800 mb-1">Placeholder ID Unik</label>
                    <input type="text" name="identifier_placeholder" id="ee_identifier_placeholder" class="w-full border border-blue-200 bg-white p-2 rounded-lg text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-blue-800 mb-1">Label Nama Peserta *</label>
                    <input type="text" name="name_label" id="ee_name_label" required class="w-full border border-blue-200 bg-white p-2 rounded-lg text-xs font-bold">
                </div>
            </div>

            <div class="border border-gray-200 rounded-xl p-3 bg-gray-50">
                <div class="flex justify-between items-center mb-2">
                    <label class="text-xs font-black text-gray-700 uppercase tracking-wider">Field Isian Pengguna</label>
                    <button type="button" onclick="addFieldRow('editFieldsContainer')" class="bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold px-2.5 py-1.5 rounded-lg transition">
                        <i class="fa-solid fa-plus"></i> Tambah Field
                    </button>
                </div>
                <div id="editFieldsContainer" class="space-y-2.5 max-h-[280px] overflow-y-auto pr-1"></div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Checklist Ketentuan di e-Tiket PDF (1 baris = 1 poin)</label>
                <textarea name="checklist_text" id="ee_checklist_text" rows="2" class="w-full border border-gray-300 p-2 rounded-lg text-xs"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Teks Kotak Konfirmasi Sebelum Submit</label>
                <input type="text" name="confirmation_text" id="ee_confirmation_text" class="w-full border border-gray-300 p-2 rounded-lg text-xs">
            </div>

            <div class="space-y-2 bg-gray-50 p-3 rounded-xl border border-gray-200">
                <label class="flex items-start gap-2 cursor-pointer text-xs font-bold text-gray-700">
                    <input type="checkbox" name="requires_checkin" id="ee_requires_checkin" value="1" class="w-4 h-4 mt-0.5 text-blue-600 rounded">
                    <span>
                        Wajibkan Menu Check-In (Scanner Gerbang)
                        <span class="block text-[10px] font-normal text-gray-500">Jika dimatikan, pendaftar baru langsung masuk antrian panggilan loket.</span>
                    </span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-700">
                    <input type="checkbox" name="is_active" id="ee_is_active" value="1" class="w-4 h-4 text-blue-600 rounded">
                    Event Aktif
                </label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeEditEventModal()" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 py-2.5 rounded-xl font-bold">Batal</button>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl font-bold">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @if(session('error')) Swal.fire('Gagal', '{!! session('error') !!}', 'error'); @endif
    @if(session('success')) Swal.fire('Berhasil!', '{!! session('success') !!}', 'success'); @endif

    let fieldUid = 0;
    const starterFields = @json(\App\Models\Event::defaultFormFields());

    function escapeAttr(val) {
        return String(val ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function addFieldRow(containerId, data = {}) {
        const container = document.getElementById(containerId);
        const idx = fieldUid++;
        const type = data.type || 'text';
        const showOptions = (type === 'select' || type === 'checkbox');
        const optionsText = Array.isArray(data.options) ? data.options.join(', ') : (data.options || '');
        const isChecked = data.required !== false;

        const row = document.createElement('div');
        row.className = 'field-row bg-white border border-gray-200 rounded-xl p-3 space-y-2 shadow-xs';
        row.innerHTML = `
            <div class="grid grid-cols-12 gap-2 items-center">
                <div class="col-span-6">
                    <input type="text" name="fields[${idx}][label]" value="${escapeAttr(data.label || '')}" required placeholder="Label Field (cth: Asal Sekolah)" class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs font-bold outline-none focus:ring-1 focus:ring-indigo-400">
                    <input type="hidden" name="fields[${idx}][key]" value="${escapeAttr(data.key || '')}">
                </div>
                <div class="col-span-4">
                    <select name="fields[${idx}][type]" onchange="toggleFieldOptions(this)" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs font-bold bg-gray-50 outline-none">
                        <option value="text" ${type === 'text' ? 'selected' : ''}>Teks Singkat</option>
                        <option value="number" ${type === 'number' ? 'selected' : ''}>Angka</option>
                        <option value="email" ${type === 'email' ? 'selected' : ''}>Email</option>
                        <option value="textarea" ${type === 'textarea' ? 'selected' : ''}>Paragraf / Alamat</option>
                        <option value="select" ${type === 'select' ? 'selected' : ''}>Pilihan Dropdown</option>
                        <option value="checkbox" ${type === 'checkbox' ? 'selected' : ''}>Pilihan Checkbox</option>
                    </select>
                </div>
                <div class="col-span-2 flex justify-end gap-1">
                    <button type="button" onclick="moveFieldRow(this, -1)" class="text-gray-400 hover:text-gray-700 p-1 text-xs" title="Naik"><i class="fa-solid fa-arrow-up"></i></button>
                    <button type="button" onclick="moveFieldRow(this, 1)" class="text-gray-400 hover:text-gray-700 p-1 text-xs" title="Turun"><i class="fa-solid fa-arrow-down"></i></button>
                    <button type="button" onclick="this.closest('.field-row').remove()" class="text-red-500 hover:text-red-700 p-1 text-xs" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                </div>
            </div>
            <div class="grid grid-cols-12 gap-2 items-center">
                <div class="col-span-8 placeholder-box ${showOptions ? 'hidden' : ''}">
                    <input type="text" name="fields[${idx}][placeholder]" value="${escapeAttr(data.placeholder || '')}" placeholder="Contoh teks bantuan (opsional)" class="w-full border border-gray-200 rounded px-2.5 py-1 text-[11px] bg-gray-50 outline-none">
                </div>
                <div class="col-span-8 options-box ${showOptions ? '' : 'hidden'}">
                    <input type="text" name="fields[${idx}][options]" value="${escapeAttr(optionsText)}" placeholder="Opsi pisahkan koma (cth: Opsi A, Opsi B)" class="w-full border border-indigo-200 rounded px-2.5 py-1 text-[11px] bg-indigo-50/40 outline-none">
                </div>
                <div class="col-span-4 flex justify-end">
                    <label class="flex items-center gap-1.5 text-[11px] font-bold text-gray-600 cursor-pointer">
                        <input type="checkbox" name="fields[${idx}][required]" value="1" ${isChecked ? 'checked' : ''} class="w-3.5 h-3.5 text-indigo-600 rounded">
                        Wajib diisi
                    </label>
                </div>
            </div>
        `;
        container.appendChild(row);
    }

    function toggleFieldOptions(selectEl) {
        const row = selectEl.closest('.field-row');
        const showOptions = (selectEl.value === 'select' || selectEl.value === 'checkbox');
        row.querySelector('.options-box').classList.toggle('hidden', !showOptions);
        row.querySelector('.placeholder-box').classList.toggle('hidden', showOptions);
    }

    function moveFieldRow(btn, dir) {
        const row = btn.closest('.field-row');
        const parent = row.parentNode;
        if (dir === -1 && row.previousElementSibling) {
            parent.insertBefore(row, row.previousElementSibling);
        } else if (dir === 1 && row.nextElementSibling) {
            parent.insertBefore(row.nextElementSibling, row);
        }
    }

    function openEditEventModal(ev) {
        document.getElementById('editEventForm').action = '/admin/events/' + ev.id;
        document.getElementById('ee_name').value = ev.name || '';
        document.getElementById('ee_subtitle').value = ev.subtitle || '';
        document.getElementById('ee_description').value = ev.description || '';
        document.getElementById('ee_identifier_label').value = ev.identifier_label || 'NISN';
        document.getElementById('ee_identifier_placeholder').value = ev.identifier_placeholder || '';
        document.getElementById('ee_name_label').value = ev.name_label || 'Nama Lengkap';
        document.getElementById('ee_confirmation_text').value = ev.confirmation_text || '';
        document.getElementById('ee_checklist_text').value = Array.isArray(ev.checklist_items) ? ev.checklist_items.join('\n') : '';
        document.getElementById('ee_requires_checkin').checked = !!ev.requires_checkin;
        document.getElementById('ee_is_active').checked = !!ev.is_active;

        const container = document.getElementById('editFieldsContainer');
        container.innerHTML = '';
        (ev.form_fields || []).forEach(f => addFieldRow('editFieldsContainer', f));

        document.getElementById('editEventModal').classList.remove('hidden');
    }

    function closeEditEventModal() {
        document.getElementById('editEventModal').classList.add('hidden');
    }

    function copyEventLink(url) {
        navigator.clipboard.writeText(url).then(() => {
            Swal.fire({ icon: 'success', title: 'Link Disalin!', text: url, timer: 1800, showConfirmButton: false });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        starterFields.forEach(f => addFieldRow('createFieldsContainer', f));

        document.body.addEventListener('submit', function (e) {
            if (e.target && e.target.classList.contains('delete-form')) {
                e.preventDefault();
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: e.target.getAttribute('data-msg'),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e3342f',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((res) => { if (res.isConfirmed) e.target.submit(); });
            }
        });
    });
</script>
@endpush
