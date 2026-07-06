<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, watch, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    transfers: Object,
    employees: { type: Array, default: () => [] },
    divisions: { type: Array, default: () => [] },
    positions: { type: Array, default: () => [] },
    filters: Object,
})

const search = ref(props.filters?.search ?? '')

watch(search, (val) => {
    router.get(route('employee-transfers.index'), { search: val }, {
        preserveState: true,
        replace: true,
    })
})

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'long', year: 'numeric'
    })
}

const showModal = ref(false)
const showDeleteModal = ref(false)
const selectedTransfer = ref(null)
const showHistoryModal = ref(false)
const selectedEmployee = ref(null)
const employeeHistory = ref([])

const form = useForm({
    employee_id: '',
    jabatan_baru: '',
    divisi_baru: '',
    tanggal_efektif: '',
    keterangan: '',
    attachment: null,
})

// Dropdown karyawan
const searchEmployee = ref('')
const showEmployeeDropdown = ref(false)
const selectedEmployeeName = ref('')
const selectedEmployeeData = ref(null)

const filteredEmployees = computed(() =>
    props.employees.filter(e =>
        e.nama.toLowerCase().includes(searchEmployee.value.toLowerCase()) ||
        e.nik.toLowerCase().includes(searchEmployee.value.toLowerCase())
    )
)

// Dropdown Jabatan Baru
const searchJabatanBaru = ref('')
const showJabatanBaruDropdown = ref(false)
const filteredPositionsBaru = computed(() =>
    props.positions.filter(p =>
        p.nama.toLowerCase().includes(searchJabatanBaru.value.toLowerCase())
    )
)
const selectJabatanBaru = (val) => {
    form.jabatan_baru = val
    showJabatanBaruDropdown.value = false
    searchJabatanBaru.value = ''
}

// Dropdown Divisi Baru
const searchDivisiBaru = ref('')
const showDivisiBaruDropdown = ref(false)
const filteredDivisionsBaru = computed(() =>
    props.divisions.filter(d =>
        d.nama.toLowerCase().includes(searchDivisiBaru.value.toLowerCase())
    )
)
const selectDivisiBaru = (val) => {
    form.divisi_baru = val
    showDivisiBaruDropdown.value = false
    searchDivisiBaru.value = ''
}

const selectEmployee = (emp) => {
    form.employee_id = emp.id
    selectedEmployeeName.value = emp.nama
    selectedEmployeeData.value = emp
    showEmployeeDropdown.value = false
    searchEmployee.value = ''
}

const openCreate = () => {
    form.reset()
    selectedEmployeeName.value = ''
    selectedEmployeeData.value = null
    showModal.value = true
}

const showDetailModal = ref(false)
const selectedDetail = ref(null)

const openDetail = (transfer) => {
    selectedDetail.value = transfer
    showDetailModal.value = true
}

const onAttachmentChange = (e) => {
    form.attachment = e.target.files[0] ?? null
}

const openDelete = (transfer) => {
    selectedTransfer.value = transfer
    showDeleteModal.value = true
}

const submit = () => {
    form.post(route('employee-transfers.store'), {
        forceFormData: true,
        onSuccess: () => {
            showModal.value = false
            form.reset()
            selectedEmployeeName.value = ''
            selectedEmployeeData.value = null
        },
    })
}

const confirmDelete = () => {
    router.delete(route('employee-transfers.destroy', selectedTransfer.value.id), {
        onSuccess: () => { showDeleteModal.value = false },
    })
}
</script>

<template>
    <AppLayout>
        <template #title>Transfer Karyawan</template>

        <!-- Header -->
        <div class="flex items-center justify-between mb-5">
            <input
                v-model="search"
                type="text"
                placeholder="Cari nama karyawan..."
                class="bg-white border border-zinc-200 rounded-lg px-4 py-2 text-sm text-zinc-700 focus:outline-none focus:border-zinc-400 w-64"
            />
            <button
                @click="openCreate"
                class="bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] text-sm font-semibold px-4 py-2 rounded-lg transition-colors"
            >
                + Tambah Transfer
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl border border-zinc-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-zinc-50 border-b border-zinc-200">
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">No</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Nama Karyawan</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Jabatan Lama</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Jabatan Baru</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Divisi Lama</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Divisi Baru</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Tgl Efektif</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="transfers.data.length === 0">
                        <td colspan="8" class="px-5 py-10 text-center text-zinc-400 text-xs">Belum ada data transfer</td>
                    </tr>
                    <tr
                        v-for="(transfer, index) in transfers.data"
                        :key="transfer.id"
                        class="border-t border-zinc-100 hover:bg-zinc-50 transition-colors"
                    >
                        <td class="px-5 py-3 text-zinc-400 text-xs">
                            {{ (transfers.current_page - 1) * transfers.per_page + index + 1 }}
                        </td>
                        <td class="px-5 py-3">
                            <p class="font-medium text-zinc-900">{{ transfer.employee?.nama }}</p>
                            <p class="text-xs text-zinc-400">{{ transfer.employee?.nik }}</p>
                        </td>
                        <td class="px-5 py-3 text-zinc-600">{{ transfer.jabatan_lama }}</td>
                        <td class="px-5 py-3">
                            <span class="text-emerald-700 font-medium">{{ transfer.jabatan_baru }}</span>
                        </td>
                        <td class="px-5 py-3 text-zinc-600">{{ transfer.divisi_lama }}</td>
                        <td class="px-5 py-3">
                            <span class="text-emerald-700 font-medium">{{ transfer.divisi_baru }}</span>
                        </td>
                        <td class="px-5 py-3 text-zinc-400 text-xs">{{ formatDate(transfer.tanggal_efektif) }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <button @click="openDetail(transfer)" class="text-xs text-zinc-600 hover:text-zinc-800 font-medium">Detail</button>
                                <span class="text-zinc-300">|</span>
                                <button @click="openDelete(transfer)" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="px-5 py-3 border-t border-zinc-100 flex items-center justify-between">
                <p class="text-xs text-zinc-400">
                    Menampilkan {{ transfers.from ?? 0 }}–{{ transfers.to ?? 0 }} dari {{ transfers.total }} data
                </p>
                <div class="flex gap-1">
                    <template v-for="link in transfers.links" :key="link.label">
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
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-lg mx-4 p-6">
                <h2 class="text-base font-semibold text-zinc-900 mb-5">Tambah Transfer Karyawan</h2>
                <div class="space-y-4">

                    <!-- Dropdown Karyawan -->
                    <div class="relative">
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Nama Karyawan</label>
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

                    <!-- Info Jabatan & Divisi Saat Ini -->
                    <div v-if="selectedEmployeeData" class="bg-zinc-50 rounded-lg p-3">
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-2">Posisi Saat Ini</p>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <p class="text-xs text-zinc-400">Jabatan</p>
                                <p class="text-sm font-medium text-zinc-800">{{ selectedEmployeeData.jabatan }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-zinc-400">Divisi</p>
                                <p class="text-sm font-medium text-zinc-800">{{ selectedEmployeeData.divisi }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Jabatan Baru -->
                        <div class="relative">
                            <label class="block text-xs font-medium text-zinc-500 mb-1.5">Jabatan Baru</label>
                            <div @click="showJabatanBaruDropdown = !showJabatanBaruDropdown"
                                class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm cursor-pointer flex items-center justify-between"
                                :class="form.jabatan_baru ? 'text-zinc-800' : 'text-zinc-400'">
                                <span>{{ form.jabatan_baru || 'Pilih jabatan...' }}</span>
                                <svg class="w-4 h-4 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                            </div>
                            <div v-if="showJabatanBaruDropdown" class="absolute z-10 w-full mt-1 bg-white border border-zinc-200 rounded-lg shadow-lg">
                                <div class="p-2 border-b border-zinc-100">
                                    <input v-model="searchJabatanBaru" type="text" placeholder="Cari jabatan..."
                                        class="w-full text-sm px-2 py-1.5 border border-zinc-200 rounded-md focus:outline-none" @click.stop />
                                </div>
                                <div class="max-h-40 overflow-y-auto">
                                    <div v-if="filteredPositionsBaru.length === 0" class="px-3 py-2 text-xs text-zinc-400">Tidak ditemukan</div>
                                    <div v-for="pos in filteredPositionsBaru" :key="pos.id"
                                        @click="selectJabatanBaru(pos.nama)"
                                        class="px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 cursor-pointer">
                                        {{ pos.nama }}
                                    </div>
                                </div>
                            </div>
                            <p v-if="form.errors.jabatan_baru" class="text-red-500 text-xs mt-1">{{ form.errors.jabatan_baru }}</p>
                        </div>

                        <!-- Divisi Baru -->
                        <div class="relative">
                            <label class="block text-xs font-medium text-zinc-500 mb-1.5">Divisi Baru</label>
                            <div @click="showDivisiBaruDropdown = !showDivisiBaruDropdown"
                                class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm cursor-pointer flex items-center justify-between"
                                :class="form.divisi_baru ? 'text-zinc-800' : 'text-zinc-400'">
                                <span>{{ form.divisi_baru || 'Pilih divisi...' }}</span>
                                <svg class="w-4 h-4 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                            </div>
                            <div v-if="showDivisiBaruDropdown" class="absolute z-10 w-full mt-1 bg-white border border-zinc-200 rounded-lg shadow-lg">
                                <div class="p-2 border-b border-zinc-100">
                                    <input v-model="searchDivisiBaru" type="text" placeholder="Cari divisi..."
                                        class="w-full text-sm px-2 py-1.5 border border-zinc-200 rounded-md focus:outline-none" @click.stop />
                                </div>
                                <div class="max-h-40 overflow-y-auto">
                                    <div v-if="filteredDivisionsBaru.length === 0" class="px-3 py-2 text-xs text-zinc-400">Tidak ditemukan</div>
                                    <div v-for="div in filteredDivisionsBaru" :key="div.id"
                                        @click="selectDivisiBaru(div.nama)"
                                        class="px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 cursor-pointer">
                                        {{ div.nama }}
                                    </div>
                                </div>
                            </div>
                            <p v-if="form.errors.divisi_baru" class="text-red-500 text-xs mt-1">{{ form.errors.divisi_baru }}</p>
                        </div>
                    </div>

                    <!-- Tanggal Efektif -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Tanggal Efektif</label>
                        <input v-model="form.tanggal_efektif" type="date"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400" />
                        <p v-if="form.errors.tanggal_efektif" class="text-red-500 text-xs mt-1">{{ form.errors.tanggal_efektif }}</p>
                    </div>

                    <!-- Keterangan -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Keterangan <span class="text-zinc-300">(opsional)</span></label>
                        <textarea v-model="form.keterangan" rows="2" placeholder="Alasan transfer, mutasi, promosi, dll"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400 resize-none" />
                    </div>
                </div>

                <!-- Attachment -->
                <div class="mt-3">
                    <label class="block text-xs font-medium text-zinc-500 mb-1.5">
                        Surat Keterangan Mutasi <span class="text-zinc-300">(opsional, PDF maks. 5MB)</span>
                    </label>
                    <input
                        type="file"
                        accept="application/pdf"
                        @change="onAttachmentChange"
                        class="w-full text-sm text-zinc-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200"
                    />
                    <p v-if="form.errors.attachment" class="text-red-500 text-xs mt-1">{{ form.errors.attachment }}</p>
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

        <!-- Modal Hapus -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-sm mx-4 p-6">
                <h2 class="text-base font-semibold text-zinc-900 mb-2">Hapus History Transfer</h2>
                <p class="text-sm text-zinc-500 mb-6">
                    Yakin ingin menghapus history transfer <span class="font-medium text-zinc-800">{{ selectedTransfer?.employee?.nama }}</span>?
                </p>
                <div class="flex justify-end gap-2">
                    <button @click="showDeleteModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Batal</button>
                    <button @click="confirmDelete" class="px-4 py-2 text-sm font-semibold bg-red-500 hover:bg-red-600 text-white rounded-lg">Hapus</button>
                </div>
            </div>
        </div>

        <!-- Modal Detail Transfer -->
        <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-lg mx-4 p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-base font-semibold text-zinc-900">Detail Transfer Karyawan</h2>
                    <button @click="showDetailModal = false" class="text-zinc-400 hover:text-zinc-600">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-3">
                    <!-- Info Karyawan -->
                    <div class="bg-zinc-50 rounded-lg p-3">
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Karyawan</p>
                        <p class="text-sm font-medium text-zinc-900">{{ selectedDetail?.employee?.nama }}</p>
                        <p class="text-xs text-zinc-400">{{ selectedDetail?.employee?.nik }}</p>
                    </div>

                    <!-- Perubahan Posisi -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Jabatan Lama</p>
                            <p class="text-sm text-zinc-800">{{ selectedDetail?.jabatan_lama }}</p>
                        </div>
                        <div class="bg-emerald-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wide mb-1">Jabatan Baru</p>
                            <p class="text-sm font-medium text-emerald-800">{{ selectedDetail?.jabatan_baru }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Divisi Lama</p>
                            <p class="text-sm text-zinc-800">{{ selectedDetail?.divisi_lama }}</p>
                        </div>
                        <div class="bg-emerald-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wide mb-1">Divisi Baru</p>
                            <p class="text-sm font-medium text-emerald-800">{{ selectedDetail?.divisi_baru }}</p>
                        </div>
                    </div>

                    <div class="bg-zinc-50 rounded-lg p-3">
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Tanggal Efektif</p>
                        <p class="text-sm text-zinc-800">{{ formatDate(selectedDetail?.tanggal_efektif) }}</p>
                    </div>

                    <div class="bg-zinc-50 rounded-lg p-3">
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Keterangan</p>
                        <p class="text-sm text-zinc-800 whitespace-pre-wrap">{{ selectedDetail?.keterangan ?? '-' }}</p>
                    </div>

                    <!-- Attachment -->
                    <div class="bg-zinc-50 rounded-lg p-3">
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-2">Surat Keterangan Mutasi</p>
                        <div v-if="selectedDetail?.attachment">
    
                             <a   :href="`/storage/${selectedDetail.attachment}`"
                                target="_blank"
                                :download="selectedDetail.attachment_name ?? selectedDetail.attachment.split('/').pop()"
                                class="inline-flex items-center gap-2 bg-[#1A1A1A] hover:bg-zinc-800 text-[#F5C518] text-xs font-semibold px-3 py-2 rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="7 10 12 15 17 10"/>
                                    <line x1="12" y1="15" x2="12" y2="3"/>
                                </svg>
                                Download PDF
                            </a>
                        </div>
                        <p v-else class="text-sm text-zinc-400">Tidak ada file terlampir</p>
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