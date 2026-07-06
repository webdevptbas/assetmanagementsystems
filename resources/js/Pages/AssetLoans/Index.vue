<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, watch, computed } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'

const props = defineProps({
    loans: Object,
    employees: { type: Array, default: () => [] },
    largeAssets: { type: Array, default: () => [] },
    filters: Object,
})

const page = usePage()
const userRole = computed(() => page.props.auth.user?.roles?.[0]?.name ?? '')

const search = ref(props.filters?.search ?? '')
const filterStatus = ref(props.filters?.status ?? '')
const filterBulan = ref(props.filters?.bulan ?? '')
const filterTahun = ref(props.filters?.tahun ?? '')

watch([search, filterStatus, filterBulan, filterTahun], () => {
    router.get(route('asset-loans.index'), {
        search: search.value,
        status: filterStatus.value,
        bulan: filterBulan.value,
        tahun: filterTahun.value,
    }, { preserveState: true, replace: true })
})

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'long', year: 'numeric'
    })
}

const isLate = (loan) => {
    if (!loan || !loan.tanggal_selesai) return false
    return loan.status === 'dipinjam' && new Date(loan.tanggal_selesai) < new Date()
}

const searchAsset = ref('')
const showAssetDropdown = ref(false)
const selectedAssetName = ref('')

const filteredAssets = computed(() =>
    props.largeAssets.filter(a =>
        a.nama_barang.toLowerCase().includes(searchAsset.value.toLowerCase()) ||
        (a.kode_barang ?? '').toLowerCase().includes(searchAsset.value.toLowerCase())
    )
)

const selectAsset = (asset) => {
    form.large_asset_id = asset.id
    selectedAssetName.value = asset.nama_barang
    showAssetDropdown.value = false
    searchAsset.value = ''
}

// =====================
// MODAL FORM
// =====================
const showModal = ref(false)
const isEdit = ref(false)
const selectedLoan = ref(null)

const form = useForm({
    employee_id: '',
    large_asset_id: '',
    jumlah: 1,
    kondisi_barang: '',
    tanggal_mulai: '',
    tanggal_selesai: '',
})

// Dropdown karyawan
const searchEmployee = ref('')
const showEmployeeDropdown = ref(false)
const selectedEmployeeName = ref('')

const filteredEmployees = computed(() =>
    props.employees.filter(e =>
        e.nama.toLowerCase().includes(searchEmployee.value.toLowerCase()) ||
        e.nik.toLowerCase().includes(searchEmployee.value.toLowerCase())
    )
)

const selectEmployee = (emp) => {
    form.employee_id = emp.id
    selectedEmployeeName.value = emp.nama
    showEmployeeDropdown.value = false
    searchEmployee.value = ''
}

const openCreate = () => {
    isEdit.value = false
    form.reset()
    selectedEmployeeName.value = ''
    selectedAssetName.value = ''
    showModal.value = false
    showModal.value = true
}

const openEdit = (loan) => {
    isEdit.value = true
    selectedLoan.value = loan
    form.employee_id     = loan.employee_id
    form.large_asset_id  = loan.large_asset_id
    form.jumlah          = loan.jumlah
    form.kondisi_barang  = loan.kondisi_barang
    form.tanggal_mulai   = loan.tanggal_mulai?.substring(0, 10)
    form.tanggal_selesai = loan.tanggal_selesai?.substring(0, 10) ?? ''
    selectedEmployeeName.value = loan.employee?.nama ?? ''
    selectedAssetName.value    = loan.large_asset?.nama_barang ?? ''
    showModal.value = true
}

const submit = () => {
    if (isEdit.value) {
        form.put(route('asset-loans.update', selectedLoan.value.id), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    } else {
        form.post(route('asset-loans.store'), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    }
}

const showKembalikanModal = ref(false)
const kembalikanForm = useForm({
    tanggal_selesai: '',
})

const openKembalikan = (loan) => {
    selectedLoan.value = loan
    kembalikanForm.tanggal_selesai = new Date().toISOString().substring(0, 10)
    showKembalikanModal.value = true
}

const submitKembalikan = () => {
    kembalikanForm.post(route('asset-loans.kembalikan', selectedLoan.value.id), {
        onSuccess: () => { showKembalikanModal.value = false; kembalikanForm.reset() },
    })
}

// =====================
// MODAL DETAIL
// =====================
const showDetailModal = ref(false)
const selectedDetail = ref(null)

const openDetail = (loan) => {
    selectedDetail.value = loan
    showDetailModal.value = true
}

// =====================
// MODAL HAPUS
// =====================
const showDeleteModal = ref(false)

const openDelete = (loan) => {
    selectedLoan.value = loan
    showDeleteModal.value = true
}

const confirmDelete = () => {
    router.delete(route('asset-loans.destroy', selectedLoan.value.id), {
        onSuccess: () => { showDeleteModal.value = false },
    })
}

// =====================
// APPROVAL & KEMBALIKAN
// =====================
const approve = (loan) => {
    router.post(route('asset-loans.approve', loan.id))
}

const kembalikan = (loan) => {
    router.post(route('asset-loans.kembalikan', loan.id))
}

const exportExcel = () => {
    const params = new URLSearchParams({
        search: search.value,
        status: filterStatus.value,
        bulan: filterBulan.value,
        tahun: filterTahun.value,
    }).toString()

    window.location.href = route('reports.export') + '?' + params
}
</script>

<template>
    <AppLayout>
        <template #title>Peminjaman Asset</template>

        <!-- Filter & Search -->
        <div class="flex flex-wrap items-center gap-3 mb-5">
            <input
                v-model="search"
                type="text"
                placeholder="Cari nama atau barang..."
                class="bg-white border border-zinc-200 rounded-lg px-4 py-2 text-sm text-zinc-700 focus:outline-none focus:border-zinc-400 w-56"
            />
            <select v-model="filterStatus"
                class="bg-white border border-zinc-200 rounded-lg px-3 py-2 text-sm text-zinc-700 focus:outline-none">
                <option value="">Semua Status</option>
                <option value="dipinjam">Dipinjam</option>
                <option value="dikembalikan">Dikembalikan</option>
            </select>
            <select v-model="filterBulan"
                class="bg-white border border-zinc-200 rounded-lg px-3 py-2 text-sm text-zinc-700 focus:outline-none">
                <option value="">Semua Bulan</option>
                <option v-for="n in 12" :key="n" :value="n">
                    {{ new Date(0, n-1).toLocaleString('id-ID', { month: 'long' }) }}
                </option>
            </select>
            <select v-model="filterTahun"
                class="bg-white border border-zinc-200 rounded-lg px-3 py-2 text-sm text-zinc-700 focus:outline-none">
                <option value="">Semua Tahun</option>
                <option v-for="y in [2024, 2025, 2026, 2027]" :key="y" :value="y">{{ y }}</option>
            </select>

            <div class="ml-auto flex items-center gap-2">
                <button
                    @click="exportExcel"
                    class="flex items-center gap-2 bg-[#1A1A1A] hover:bg-zinc-800 text-[#F5C518] text-sm font-semibold px-4 py-2 rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Export Excel
                </button>
                <button
                    v-if="userRole === 'admin'"
                    @click="openCreate"
                    class="bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] text-sm font-semibold px-4 py-2 rounded-lg transition-colors"
                >
                    + Tambah Peminjaman
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl border border-zinc-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-zinc-50 border-b border-zinc-200">
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Nama Peminjam</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Nama Barang</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Tgl Selesai</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Disetujui</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Diketahui</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Status</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loans.data.length === 0">
                        <td colspan="7" class="px-5 py-10 text-center text-zinc-400 text-xs">Belum ada data peminjaman</td>
                    </tr>
                    <tr
                        v-for="loan in loans.data"
                        :key="loan.id"
                        class="border-t border-zinc-100 hover:bg-zinc-50 transition-colors"
                    >
                        <td class="px-5 py-3 font-medium text-zinc-900">{{ loan.employee?.nama }}</td>
                        <td class="px-5 py-3 text-zinc-600">
                            {{ loan.large_asset?.nama_barang ?? loan.nama_barang }}
                        </td>
                        <td class="px-5 py-3 text-xs" :class="isLate(loan) ? 'text-red-500 font-medium' : 'text-zinc-400'">
                            {{ formatDate(loan.tanggal_selesai) }}
                        </td>
                        <!-- Diketahui -->
                        <td class="px-5 py-3">
                            <span v-if="loan.diketahui_oleh"
                                class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                {{ loan.diketahui_oleh.name }}
                            </span>
                            <button v-else-if="userRole === 'hrd'"
                                @click="approve(loan)"
                                class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors">
                                Setujui
                            </button>
                            <span v-else class="text-zinc-300 text-xs">Pending</span>
                        </td>
                        <!-- Disetujui -->
                        <td class="px-5 py-3">
                            <span v-if="loan.disetujui_oleh"
                                class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                {{ loan.disetujui_oleh.name }}
                            </span>
                            <button
                                v-else-if="userRole === 'manager' && loan.diketahui_oleh"
                                @click="approve(loan)"
                                class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors">
                                Setujui
                            </button>
                            <span v-else class="text-zinc-300 text-xs">Menunggu Approval HRD</span>
                        </td>
                        <td class="px-5 py-3">
                            <span
                                class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold"
                                :class="{
                                    'bg-emerald-50 text-emerald-700': loan.status === 'dikembalikan',
                                    'bg-red-50 text-red-600': isLate(loan),
                                    'bg-blue-50 text-blue-700': loan.status === 'dipinjam' && !isLate(loan),
                                }"
                            >
                                {{ loan.status === 'dikembalikan' ? 'Dikembalikan' : isLate(loan) ? 'Terlambat' : 'Dipinjam' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2 flex-wrap">
                                <button @click="openDetail(loan)" class="text-xs text-zinc-600 hover:text-zinc-800 font-medium">Detail</button>
                                <template v-if="userRole === 'admin'">
                                    <span class="text-zinc-300">|</span>
                                    <button @click="openEdit(loan)" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                                    <span class="text-zinc-300">|</span>
                                    <button @click="openDelete(loan)" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                                </template>
                                <template v-if="loan.status === 'dipinjam' && userRole === 'admin'">
                                    <span class="text-zinc-300">|</span>
                                    <button
                                        v-if="loan.diketahui_oleh && loan.disetujui_oleh"
                                        @click="openKembalikan(loan)"
                                        class="text-xs text-emerald-600 hover:text-emerald-800 font-medium"
                                    >
                                        Kembalikan
                                    </button>
                                    <span
                                        v-else
                                        class="text-xs text-zinc-300 cursor-not-allowed"
                                        title="Menunggu approval HRD dan Manager"
                                    >
                                        Kembalikan
                                    </span>
                                </template>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="px-5 py-3 border-t border-zinc-100 flex items-center justify-between">
                <p class="text-xs text-zinc-400">
                    Menampilkan {{ loans.from ?? 0 }}–{{ loans.to ?? 0 }} dari {{ loans.total }} data
                </p>
                <div class="flex gap-1">
                    <template v-for="link in loans.links" :key="link.label">
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
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-lg mx-4 p-6 max-h-[90vh] overflow-y-auto">
                <h2 class="text-base font-semibold text-zinc-900 mb-5">
                    {{ isEdit ? 'Edit Peminjaman' : 'Tambah Peminjaman' }}
                </h2>
                <div class="space-y-4">

                    <!-- Dropdown Karyawan -->
                    <div class="relative">
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Nama Peminjam</label>
                        <div @click="showEmployeeDropdown = !showEmployeeDropdown"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm cursor-pointer flex items-center justify-between"
                            :class="selectedEmployeeName ? 'text-zinc-800' : 'text-zinc-400'">
                            <span>{{ selectedEmployeeName || 'Pilih karyawan...' }}</span>
                            <svg class="w-4 h-4 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div v-if="showEmployeeDropdown" class="absolute z-10 w-full mt-1 bg-white border border-zinc-200 rounded-lg shadow-lg">
                            <div class="p-2 border-b border-zinc-100">
                                <input v-model="searchEmployee" type="text" placeholder="Cari nama atau NIK..."
                                    class="w-full text-sm px-2 py-1.5 border border-zinc-200 rounded-md focus:outline-none" @click.stop />
                            </div>
                            <div class="max-h-48 overflow-y-auto">
                                <div v-if="filteredEmployees.length === 0" class="px-3 py-2 text-xs text-zinc-400">Tidak ditemukan</div>
                                <div v-for="emp in filteredEmployees" :key="emp.id"
                                    @click="selectEmployee(emp)"
                                    class="px-3 py-2.5 hover:bg-zinc-50 cursor-pointer">
                                    <p class="text-sm text-zinc-800 font-medium">{{ emp.nama }}</p>
                                    <p class="text-xs text-zinc-400">{{ emp.nik }} · {{ emp.jabatan }} · {{ emp.divisi }}</p>
                                </div>
                            </div>
                        </div>
                        <p v-if="form.errors.employee_id" class="text-red-500 text-xs mt-1">{{ form.errors.employee_id }}</p>
                    </div>

                    <!-- Dropdown Asset -->
                    <div class="relative">
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Nama Barang</label>
                        <div @click="showAssetDropdown = !showAssetDropdown"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm cursor-pointer flex items-center justify-between"
                            :class="selectedAssetName ? 'text-zinc-800' : 'text-zinc-400'">
                            <span>{{ selectedAssetName || 'Pilih barang...' }}</span>
                            <svg class="w-4 h-4 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div v-if="showAssetDropdown" class="absolute z-10 w-full mt-1 bg-white border border-zinc-200 rounded-lg shadow-lg">
                            <div class="p-2 border-b border-zinc-100">
                                <input v-model="searchAsset" type="text" placeholder="Cari nama atau kode barang..."
                                    class="w-full text-sm px-2 py-1.5 border border-zinc-200 rounded-md focus:outline-none" @click.stop />
                            </div>
                            <div class="max-h-48 overflow-y-auto">
                                <div v-if="filteredAssets.length === 0" class="px-3 py-2 text-xs text-zinc-400">Tidak ditemukan</div>
                                <div v-for="asset in filteredAssets" :key="asset.id"
                                    @click="asset.is_available ? selectAsset(asset) : null"
                                    class="px-3 py-2.5 border-t border-zinc-50"
                                    :class="asset.is_available ? 'hover:bg-zinc-50 cursor-pointer' : 'opacity-50 cursor-not-allowed bg-zinc-50'">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-zinc-800">{{ asset.nama_barang }}</p>
                                            <p class="text-xs text-zinc-400">{{ asset.kode_barang }} · {{ asset.tipe }}</p>
                                        </div>
                                        <span
                                            class="text-[11px] font-semibold px-2 py-0.5 rounded-md"
                                            :class="asset.is_available ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'"
                                        >
                                            {{ asset.is_available ? 'Tersedia' : 'Dipinjam' }}
                                        </span>
                                    </div>
                                    <p v-if="!asset.is_available" class="text-xs text-red-400 mt-0.5">
                                        Dipinjam oleh: {{ asset.dipinjam_oleh }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <p v-if="form.errors.large_asset_id" class="text-red-500 text-xs mt-1">{{ form.errors.large_asset_id }}</p>
                    </div>

                    <!-- Jumlah -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Jumlah</label>
                        <input v-model="form.jumlah" type="number" min="1"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400" />
                        <p v-if="form.errors.jumlah" class="text-red-500 text-xs mt-1">{{ form.errors.jumlah }}</p>
                    </div>

                    <!-- Kondisi Barang -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Kondisi Barang</label>
                        <input v-model="form.kondisi_barang" type="text" placeholder="Contoh: Baik, Normal"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400" />
                        <p v-if="form.errors.kondisi_barang" class="text-red-500 text-xs mt-1">{{ form.errors.kondisi_barang }}</p>
                    </div>

                    <!-- Tanggal Mulai -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Tanggal Mulai</label>
                        <input v-model="form.tanggal_mulai" type="date"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400" />
                        <p v-if="form.errors.tanggal_mulai" class="text-red-500 text-xs mt-1">{{ form.errors.tanggal_mulai }}</p>
                    </div>

                    <!-- Tanggal Selesai -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">
                            Tanggal Selesai <span class="text-zinc-300">(opsional)</span>
                        </label>
                        <input v-model="form.tanggal_selesai" type="date"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400" />
                        <p v-if="form.errors.tanggal_selesai" class="text-red-500 text-xs mt-1">{{ form.errors.tanggal_selesai }}</p>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button @click="showModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Batal</button>
                    <button @click="submit" :disabled="form.processing"
                        class="px-4 py-2 text-sm font-semibold bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] rounded-lg disabled:opacity-60">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Detail -->
        <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-lg mx-4 p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-base font-semibold text-zinc-900">Detail Peminjaman</h2>
                    <button @click="showDetailModal = false" class="text-zinc-400 hover:text-zinc-600">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Nama Peminjam</p>
                            <p class="text-sm font-medium text-zinc-800">{{ selectedDetail?.employee?.nama }}</p>
                        </div>
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">NIK</p>
                            <p class="text-sm text-zinc-800">{{ selectedDetail?.employee?.nik }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Jabatan</p>
                            <p class="text-sm text-zinc-800">{{ selectedDetail?.employee?.jabatan }}</p>
                        </div>
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Divisi</p>
                            <p class="text-sm text-zinc-800">{{ selectedDetail?.employee?.divisi }}</p>
                        </div>
                    </div>
                    <div class="bg-zinc-50 rounded-lg p-3">
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Nama Barang</p>
                        <p class="text-sm text-zinc-800">{{ selectedDetail?.nama_barang }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Jumlah</p>
                            <p class="text-sm text-zinc-800">{{ selectedDetail?.jumlah }}</p>
                        </div>
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Kondisi</p>
                            <p class="text-sm text-zinc-800">{{ selectedDetail?.kondisi_barang }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Tgl Mulai</p>
                            <p class="text-sm text-zinc-800">{{ formatDate(selectedDetail?.tanggal_mulai) }}</p>
                        </div>
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Tgl Selesai</p>
                            <p class="text-sm" :class="isLate(selectedDetail) ? 'text-red-500 font-medium' : 'text-zinc-800'">
                                {{ selectedDetail?.tanggal_selesai ? formatDate(selectedDetail.tanggal_selesai) : '-' }}
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Disetujui Oleh</p>
                            <p class="text-sm text-zinc-800">{{ selectedDetail?.diketahui_oleh?.name ?? '-' }}</p>
                        </div>
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Diketahui Oleh</p>
                            <p class="text-sm text-zinc-800">{{ selectedDetail?.disetujui_oleh?.name ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="bg-zinc-50 rounded-lg p-3">
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Status</p>
                        <span
                            class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold"
                            :class="{
                                'bg-emerald-50 text-emerald-700': selectedDetail?.status === 'dikembalikan',
                                'bg-red-50 text-red-600': isLate(selectedDetail),
                                'bg-blue-50 text-blue-700': selectedDetail?.status === 'dipinjam' && !isLate(selectedDetail),
                            }"
                        >
                            {{ selectedDetail?.status === 'dikembalikan' ? 'Dikembalikan' : isLate(selectedDetail) ? 'Terlambat' : 'Dipinjam' }}
                        </span>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button @click="showDetailModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Tutup</button>
                </div>
            </div>
        </div>

        <!-- Modal Hapus -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-sm mx-4 p-6">
                <h2 class="text-base font-semibold text-zinc-900 mb-2">Hapus Peminjaman</h2>
                <p class="text-sm text-zinc-500 mb-6">
                    Yakin ingin menghapus peminjaman <span class="font-medium text-zinc-800">{{ selectedLoan?.nama_barang }}</span> oleh {{ selectedLoan?.employee?.nama }}?
                </p>
                <div class="flex justify-end gap-2">
                    <button @click="showDeleteModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Batal</button>
                    <button @click="confirmDelete" class="px-4 py-2 text-sm font-semibold bg-red-500 hover:bg-red-600 text-white rounded-lg">Hapus</button>
                </div>
            </div>
        </div>

        <div v-if="showKembalikanModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-sm mx-4 p-6">
                <h2 class="text-base font-semibold text-zinc-900 mb-2">Kembalikan Asset</h2>
                <p class="text-sm text-zinc-500 mb-4">
                    Konfirmasi pengembalian <span class="font-medium text-zinc-800">{{ selectedLoan?.large_asset?.nama_barang }}</span>
                    oleh <span class="font-medium text-zinc-800">{{ selectedLoan?.employee?.nama }}</span>
                </p>
                <div>
                    <label class="block text-xs font-medium text-zinc-500 mb-1.5">Tanggal Pengembalian</label>
                    <input
                        v-model="kembalikanForm.tanggal_selesai"
                        type="date"
                        class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400"
                    />
                    <p v-if="kembalikanForm.errors.tanggal_selesai" class="text-red-500 text-xs mt-1">
                        {{ kembalikanForm.errors.tanggal_selesai }}
                    </p>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button @click="showKembalikanModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Batal</button>
                    <button @click="submitKembalikan" :disabled="kembalikanForm.processing"
                        class="px-4 py-2 text-sm font-semibold bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] rounded-lg disabled:opacity-60">
                        {{ kembalikanForm.processing ? 'Menyimpan...' : 'Konfirmasi' }}
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>