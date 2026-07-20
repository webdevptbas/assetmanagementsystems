<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, watch, computed, onMounted, onUnmounted } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'

const page = usePage()
const flashError = computed(() => page.props.flash?.error ?? null)
const deleteError = ref(null)

const props = defineProps({
    employees: Object,
    filters: Object,
    divisions: { type: Array, default: () => [] },
    positions: { type: Array, default: () => [] },
    transferred_ids: { type: Array, default: () => [] },
})

const search = ref(props.filters?.search ?? '')

watch(search, (val) => {
    router.get(route('employees.index'), { search: val }, {
        preserveState: true,
        replace: true,
    })
})

const showModal = ref(false)
const isEdit = ref(false)
const showDeleteModal = ref(false)
const selectedEmployee = ref(null)

const form = useForm({
    nama: '',
    nik: '',
    nip: '',
    jabatan: '',
    divisi: '',
    alamat: '',
    no_bpjs_ketenagakerjaan: '',
    no_bpjs_kesehatan: '',
    jenis_kelamin: '',
    foto: null,
    kontak_darurat_nama: '',
})

// Dropdown Jabatan
const searchJabatan = ref('')
const showJabatanDropdown = ref(false)
const filteredPositions = computed(() =>
    props.positions.filter(p =>
        p.nama.toLowerCase().includes(searchJabatan.value.toLowerCase())
    )
)
const selectJabatan = (val) => {
    form.jabatan = val
    showJabatanDropdown.value = false
    searchJabatan.value = ''
}

// Dropdown Divisi
const searchDivisi = ref('')
const showDivisiDropdown = ref(false)
const filteredDivisions = computed(() =>
    props.divisions.filter(d =>
        d.nama.toLowerCase().includes(searchDivisi.value.toLowerCase())
    )
)
const selectDivisi = (val) => {
    form.divisi = val
    showDivisiDropdown.value = false
    searchDivisi.value = ''
}

const openCreate = () => {
    isEdit.value = false
    form.reset()
    form._method = ''
    fotoPreview.value = null
    showModal.value = true
}

const openEdit = (employee) => {
    isEdit.value = true
    selectedEmployee.value = employee
    form.nama    = employee.nama
    form.nik     = employee.nik
    form.jabatan = employee.jabatan
    form.divisi  = employee.divisi
    form.alamat  = employee.alamat ?? ''
    form.no_bpjs_ketenagakerjaan = employee.no_bpjs_ketenagakerjaan ?? ''
    form.no_bpjs_kesehatan = employee.no_bpjs_kesehatan ?? ''
    form.jenis_kelamin = employee.jenis_kelamin ?? ''
    form.foto = null
    form.nip = employee.nip ?? ''
    form.kontak_darurat_nama = employee.kontak_darurat_nama ?? ''
    fotoPreview.value = employee.foto ? '/storage/${employee.foto}' : null
    form._method = 'PUT'
    showModal.value = true
}

const isTransferred = computed(() => {
    if (!selectedEmployee.value) return false
    return props.transferred_ids.includes(selectedEmployee.value.id)
})

const openDelete = (employee) => {
    selectedEmployee.value = employee
    showDeleteModal.value = true
}

const showDetailModal = ref(false)
const selectedDetail = ref(null)

const openDetail = (employee) => {
    selectedDetail.value = employee
    showDetailModal.value = true
}

const submit = () => {
    if (isEdit.value) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(route('employees.update', selectedEmployee.value.id), {
            forceFormData: true,
            onSuccess: () => { showModal.value = false; form.reset(); fotoPreview.value = null },
        }) 
    } else {
        form.post(route('employees.store'), {
            forceFormData: true,
            onSuccess: () => { showModal.value = false; form.reset(); fotoPreview.value = null },
        })
    }
}

const confirmDelete = () => {
    router.delete(route('employees.destroy', selectedEmployee.value.id), {
        preserveScroll: true,
        onSuccess: () => { showDeleteModal.value = false },
        onError: (errors) => {
            showDeleteModal.value = false
            deleteError.value = errors.delete_error ?? null
            setTimeout(() => { deleteError.value = null }, 4000)
        },
    })
}

const fotoPreview = ref(null)
const onFotoChange = (e) => {
    const file = e.target.files[0]
    if (file){
        form.foto = file
        fotoPreview.value = URL.createObjectURL(file)
    }
}
</script>

<template>
    <AppLayout>
        
        <template #title>Data Karyawan</template>

        <div v-if="deleteError"
            class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ deleteError }}
            </div>
            <button @click="deleteError = null" class="text-red-400 hover:text-red-600">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between mb-5">
            <input
                v-model="search"
                type="text"
                placeholder="Cari nama atau NIK..."
                class="bg-white border border-zinc-200 rounded-lg px-4 py-2 text-sm text-zinc-700 focus:outline-none focus:border-zinc-400 w-64"
            />
            <button
                @click="openCreate"
                class="bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] text-sm font-semibold px-4 py-2 rounded-lg transition-colors"
            >
                + Tambah Karyawan
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl border border-zinc-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-zinc-50 border-b border-zinc-200">
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">No</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Nama</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">NIK</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Jabatan</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Divisi</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="employees.data.length === 0">
                        <td colspan="6" class="px-5 py-10 text-center text-zinc-400 text-xs">
                            Belum ada data karyawan
                        </td>
                    </tr>
                    <tr
                        v-for="(employee, index) in employees.data"
                        :key="employee.id"
                        class="border-t border-zinc-100 hover:bg-zinc-50 transition-colors"
                    >
                        <td class="px-5 py-3 text-zinc-400 text-xs">
                            {{ (employees.current_page - 1) * employees.per_page + index + 1 }}
                        </td>
                        <td class="px-5 py-3 text-zinc-600">{{ employee.nama }}</td>
                        <td class="px-5 py-3 text-zinc-600">{{ employee.nik }}</td>
                        <td class="px-5 py-3 text-zinc-600">{{ employee.jabatan }}</td>
                        <td class="px-5 py-3 text-zinc-600">{{ employee.divisi }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <button @click="openDetail(employee)" class="text-xs text-zinc-600 hover:text-zinc-800 font-medium">Detail</button>
                                <span class="text-zinc-300">|</span>
                                <button @click="openEdit(employee)" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                                <span class="text-zinc-300">|</span>
                                <button @click="openDelete(employee)" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="px-5 py-3 border-t border-zinc-100 flex items-center justify-between">
                <p class="text-xs text-zinc-400">
                    Menampilkan {{ employees.from ?? 0 }}–{{ employees.to ?? 0 }} dari {{ employees.total }} data
                </p>
                <div class="flex gap-1">
                    <template v-for="link in employees.links" :key="link.label">
                        <button
                            v-html="link.label"
                            :disabled="!link.url"
                            @click="link.url && router.get(link.url)"
                            class="px-3 py-1 text-xs rounded-md border transition-colors"
                            :class="link.active
                                ? 'bg-[#1A1A1A] text-[#F5C518] border-[#1A1A1A]'
                                : 'bg-white text-zinc-500 border-zinc-200 hover:bg-zinc-50 disabled:opacity-40 disabled:cursor-not-allowed'"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Modal Form -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-2xl mx-4 p-6 max-h-[90vh] overflow-y-auto">
                <h2 class="text-base font-semibold text-zinc-900 mb-5">
                    {{ isEdit ? 'Edit Karyawan' : 'Tambah Karyawan' }}
                </h2>

                <div class="space-y-4 overflow-visible">
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Nama</label>
                        <input
                            v-model="form.nama"
                            type="text"
                            placeholder="Nama lengkap"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400"
                        />
                        <p v-if="form.errors.nama" class="text-red-500 text-xs mt-1">{{ form.errors.nama }}</p>
                    </div>

                    <!-- NIP -->
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">NIP</label>
                        <input
                            v-model="form.nip"
                            type="text"
                            inputmode="numeric"
                            placeholder="Nomor Induk Pegawai"
                            @input="form.nip = form.nip.replace(/\D/g, '')"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">NIK</label>
                        <input
                            v-model="form.nik"
                            type="text"
                            inputmode="numeric"
                            placeholder="Nomor Induk Karyawan"
                            @input="form.nik = form.nik.replace(/\D/g, '')"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400"
                        />
                        <p v-if="form.errors.nik" class="text-red-500 text-xs mt-1">{{ form.errors.nik }}</p>
                    </div>

                    <!-- Jabatan -->
                    <div class="relative">
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">
                            Jabatan
                            <span v-if="isEdit && isTransferred" class="text-amber-500 font-normal">(ubah via Transfer Karyawan)</span>
                        </label>
                        <div
                            @click="!isTransferred && (showJabatanDropdown = !showJabatanDropdown)"
                            class="w-full border rounded-lg px-3 py-2.5 text-sm flex items-center justify-between"
                            :class="[
                                form.jabatan ? 'text-zinc-800' : 'text-zinc-400',
                                isEdit && isTransferred
                                    ? 'border-zinc-100 bg-zinc-50 cursor-not-allowed'
                                    : 'border-zinc-200 cursor-pointer'
                            ]"
                        >
                            <span>{{ form.jabatan || 'Pilih jabatan...' }}</span>
                            <svg v-if="!(isEdit && isTransferred)" class="w-4 h-4 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                            <svg v-else class="w-4 h-4 text-zinc-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <div v-if="showJabatanDropdown && !(isEdit && isTransferred)" class="absolute z-10 w-full mt-1 bg-white border border-zinc-200 rounded-lg shadow-lg">
                            <div class="p-2 border-b border-zinc-100">
                                <input v-model="searchJabatan" type="text" placeholder="Cari jabatan..."
                                    class="w-full text-sm px-2 py-1.5 border border-zinc-200 rounded-md focus:outline-none" @click.stop />
                            </div>
                            <div class="max-h-40 overflow-y-auto">
                                <div v-if="filteredPositions.length === 0" class="px-3 py-2 text-xs text-zinc-400">Tidak ditemukan</div>
                                <div v-for="pos in filteredPositions" :key="pos.id" @click="selectJabatan(pos.nama)"
                                    class="px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 cursor-pointer">{{ pos.nama }}</div>
                            </div>
                        </div>
                        <p v-if="form.errors.jabatan" class="text-red-500 text-xs mt-1">{{ form.errors.jabatan }}</p>
                    </div>

                    <!-- Divisi -->
                    <div class="relative">
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">
                            Divisi
                            <span v-if="isEdit && isTransferred" class="text-amber-500 font-normal">(ubah via Transfer Karyawan)</span>
                        </label>
                        <div
                            @click="!isTransferred && (showDivisiDropdown = !showDivisiDropdown)"
                            class="w-full border rounded-lg px-3 py-2.5 text-sm flex items-center justify-between"
                            :class="[
                                form.divisi ? 'text-zinc-800' : 'text-zinc-400',
                                isEdit && isTransferred
                                    ? 'border-zinc-100 bg-zinc-50 cursor-not-allowed'
                                    : 'border-zinc-200 cursor-pointer'
                            ]"
                        >
                            <span>{{ form.divisi || 'Pilih divisi...' }}</span>
                            <svg v-if="!(isEdit && isTransferred)" class="w-4 h-4 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                            <svg v-else class="w-4 h-4 text-zinc-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <div v-if="showDivisiDropdown && !(isEdit && isTransferred)" class="absolute z-10 w-full mt-1 bg-white border border-zinc-200 rounded-lg shadow-lg">
                            <div class="p-2 border-b border-zinc-100">
                                <input v-model="searchDivisi" type="text" placeholder="Cari divisi..."
                                    class="w-full text-sm px-2 py-1.5 border border-zinc-200 rounded-md focus:outline-none" @click.stop />
                            </div>
                            <div class="max-h-40 overflow-y-auto">
                                <div v-if="filteredDivisions.length === 0" class="px-3 py-2 text-xs text-zinc-400">Tidak ditemukan</div>
                                <div v-for="div in filteredDivisions" :key="div.id" @click="selectDivisi(div.nama)"
                                    class="px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 cursor-pointer">{{ div.nama }}</div>
                            </div>
                        </div>
                        <p v-if="form.errors.divisi" class="text-red-500 text-xs mt-1">{{ form.errors.divisi }}</p>
                    </div>
                    <!-- Alamat -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Alamat</label>
                        <textarea
                            v-model="form.alamat"
                            rows="2"
                            placeholder="Alamat lengkap"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400 resize-none"
                        />
                    </div>

                    <!-- Kontak Darurat -->
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Kontak Darurat <span class="text-zinc-300">(opsional - format: Nama / No. HP)</span></label>
                        <input
                            v-model="form.kontak_darurat_nama"
                            type="text"
                            placeholder="Contoh: Budi / 08123456789"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400"
                        />
                    </div>

                    <!-- Jenis Kelamin -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Jenis Kelamin</label>
                        <div class="flex gap-3">
                            <button type="button" @click="form.jenis_kelamin = 'Laki-laki'"
                                class="px-4 py-2 text-sm rounded-lg border transition-colors"
                                :class="form.jenis_kelamin === 'Laki-laki' ? 'bg-[#1A1A1A] text-[#F5C518] border-[#1A1A1A]' : 'bg-white text-zinc-500 border-zinc-200'">
                                Laki-laki
                            </button>
                            <button type="button" @click="form.jenis_kelamin = 'Perempuan'"
                                class="px-4 py-2 text-sm rounded-lg border transition-colors"
                                :class="form.jenis_kelamin === 'Perempuan' ? 'bg-[#1A1A1A] text-[#F5C518] border-[#1A1A1A]' : 'bg-white text-zinc-500 border-zinc-200'">
                                Perempuan
                            </button>
                        </div>
                        <p v-if="form.errors.jenis_kelamin" class="text-red-500 text-xs mt-1">{{ form.errors.jenis_kelamin }}</p>
                    </div>

                    <!-- No BPJS Ketenagakerjaan -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">No. BPJS Ketenagakerjaan <span class="text-zinc-300">(opsional)</span></label>
                        <input
                            v-model="form.no_bpjs_ketenagakerjaan"
                            type="text"
                            placeholder="Nomor BPJS Ketenagakerjaan"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400"
                        />
                    </div>

                    <!-- No BPJS Kesehatan -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">No. BPJS Kesehatan <span class="text-zinc-300">(opsional)</span></label>
                        <input
                            v-model="form.no_bpjs_kesehatan"
                            type="text"
                            placeholder="Nomor BPJS Kesehatan"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400"
                        />
                    </div>

                    <!-- Foto -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Foto <span class="text-zinc-300">(opsional)</span></label>
                        <div v-if="fotoPreview" class="mb-2">
                            <img :src="fotoPreview" class="w-16 h-16 rounded-lg object-cover border border-zinc-200" />
                        </div>
                        <input
                            type="file"
                            accept="image/jpg,image/jpeg,image/png"
                            @change="onFotoChange"
                            class="w-full text-sm text-zinc-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200"
                        />
                        <p v-if="form.errors.foto" class="text-red-500 text-xs mt-1">{{ form.errors.foto }}</p>
                    </div>
                </div>
                

                <div class="flex justify-end gap-2 mt-6">
                    <button
                        @click="showModal = false"
                        class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50"
                    >
                        Batal
                    </button>
                    <button
                        @click="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 text-sm font-semibold bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] rounded-lg disabled:opacity-60"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Konfirmasi Hapus -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-sm mx-4 p-6">
                <h2 class="text-base font-semibold text-zinc-900 mb-2">Hapus Karyawan</h2>
                <p class="text-sm text-zinc-500 mb-6">
                    Yakin ingin menghapus <span class="font-medium text-zinc-800">{{ selectedEmployee?.nama }}</span>? Data tidak bisa dikembalikan.
                </p>
                <div class="flex justify-end gap-2">
                    <button
                        @click="showDeleteModal = false"
                        class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50"
                    >
                        Batal
                    </button>
                    <button
                        @click="confirmDelete"
                        class="px-4 py-2 text-sm font-semibold bg-red-500 hover:bg-red-600 text-white rounded-lg"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        </div>
        <!-- Modal Detail Karyawan -->
        <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-2xl mx-4 p-6 max-h-[90vh] overflow-y-auto">

                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-base font-semibold text-zinc-900">Detail Karyawan</h2>
                    <button @click="showDetailModal = false" class="text-zinc-400 hover:text-zinc-600">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                <!-- Foto Profil + Info Utama -->
                <div class="flex items-center gap-6 mb-6 pb-6 border-b border-zinc-100">
                    <div class="w-24 h-24 rounded-full overflow-hidden border-2 border-zinc-200 flex-shrink-0 bg-zinc-100 flex items-center justify-center">
                        <img
                            v-if="selectedDetail?.foto"
                            :src="`/storage/${selectedDetail.foto}`"
                            class="w-full h-full object-cover"
                        />
                        <svg v-else class="w-10 h-10 text-zinc-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-zinc-900">{{ selectedDetail?.nama }}</p>
                        <p class="text-sm text-zinc-500 mt-0.5">{{ selectedDetail?.jabatan }} · {{ selectedDetail?.divisi }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold bg-zinc-100 text-zinc-600">
                                NIP: {{ selectedDetail?.nip ?? '-' }}
                            </span>
                            <span
                                v-if="selectedDetail?.jenis_kelamin"
                                class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold"
                                :class="selectedDetail?.jenis_kelamin === 'Laki-laki' ? 'bg-blue-50 text-blue-700' : 'bg-pink-50 text-pink-700'"
                            >
                                {{ selectedDetail?.jenis_kelamin }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Detail Info -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-zinc-50 rounded-lg p-3">
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Jabatan</p>
                        <p class="text-sm text-zinc-800">{{ selectedDetail?.jabatan ?? '-' }}</p>
                    </div>
                    <div class="bg-zinc-50 rounded-lg p-3">
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Divisi</p>
                        <p class="text-sm text-zinc-800">{{ selectedDetail?.divisi ?? '-' }}</p>
                    </div>
                    <div class="bg-zinc-50 rounded-lg p-3">
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">No. BPJS Ketenagakerjaan</p>
                        <p class="text-sm text-zinc-800">{{ selectedDetail?.no_bpjs_ketenagakerjaan ?? '-' }}</p>
                    </div>
                    <div class="bg-zinc-50 rounded-lg p-3">
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">No. BPJS Kesehatan</p>
                        <p class="text-sm text-zinc-800">{{ selectedDetail?.no_bpjs_kesehatan ?? '-' }}</p>
                    </div>
                    <div class="bg-zinc-50 rounded-lg p-3 col-span-2">
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Alamat</p>
                        <p class="text-sm text-zinc-800 whitespace-pre-wrap">{{ selectedDetail?.alamat ?? '-' }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Nama Kontak Darurat</p>
                            <p class="text-sm text-zinc-800">{{ selectedDetail?.kontak_darurat_nama ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button @click="showDetailModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>