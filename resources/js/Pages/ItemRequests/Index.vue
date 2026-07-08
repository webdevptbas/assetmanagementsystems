<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, watch, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    requests: Object,
    employees: { type: Array, default: () => [] },
    items: { type: Array, default: () => [] },
    filters: Object,
})

const search = ref(props.filters?.search ?? '')

watch(search, (val) => {
    router.get(route('item-requests.index'), { search: val }, {
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

// =====================
// MODAL FORM
// =====================
const showModal = ref(false)
const showDeleteModal = ref(false)
const selectedRequest = ref(null)

const form = useForm({
    employee_id: '',
    inventory_item_id: '',
    jumlah: 1,
})

// Dropdown Karyawan
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

// Dropdown Barang
const searchItem = ref('')
const showItemDropdown = ref(false)
const selectedItemName = ref('')
const selectedItemStok = ref(0)

const filteredItems = computed(() =>
    props.items.filter(i =>
        i.nama_barang.toLowerCase().includes(searchItem.value.toLowerCase()) ||
        i.kode_barang.toLowerCase().includes(searchItem.value.toLowerCase())
    )
)

const selectItem = (item) => {
    form.inventory_item_id = item.id
    selectedItemName.value = item.nama_barang
    selectedItemStok.value = item.stok
    showItemDropdown.value = false
    searchItem.value = ''
}

const openCreate = () => {
    form.reset()
    selectedEmployeeName.value = ''
    selectedItemName.value = ''
    selectedItemStok.value = 0
    showModal.value = true
}

const openDelete = (req) => {
    selectedRequest.value = req
    showDeleteModal.value = true
}

const submit = () => {
    form.post(route('item-requests.store'), {
        onSuccess: () => { showModal.value = false; form.reset() },
    })
}

const confirmDelete = () => {
    router.delete(route('item-requests.destroy', selectedRequest.value.id), {
        onSuccess: () => { showDeleteModal.value = false },
    })
}

const exportExcel = () => {
    const params = new URLSearchParams({ search: search.value }).toString()
    window.location.href = route('item-requests.export') + '?' + params
}
</script>

<template>
    <AppLayout>
        <template #title>Permintaan Barang</template>

        <!-- Header -->
        
        <div class="flex items-center justify-between mb-5">
            <input
                v-model="search"
                type="text"
                placeholder="Cari nama atau barang..."
                class="bg-white border border-zinc-200 rounded-lg px-4 py-2 text-sm text-zinc-700 focus:outline-none focus:border-zinc-400 w-64"
            />
                <div class="flex items-center gap-2">
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
                        @click="openCreate"
                        class="bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] text-sm font-semibold px-4 py-2 rounded-lg transition-colors"
                    >
                        + Tambah Permintaan
                    </button>
                </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl border border-zinc-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-zinc-50 border-b border-zinc-200">
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">No</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Nama Peminta</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Barang</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Jumlah</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Sisa Stok</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Tanggal</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="requests.data.length === 0">
                        <td colspan="7" class="px-5 py-10 text-center text-zinc-400 text-xs">Belum ada data permintaan</td>
                    </tr>
                    <tr
                        v-for="(req, index) in requests.data"
                        :key="req.id"
                        class="border-t border-zinc-100 hover:bg-zinc-50 transition-colors"
                    >
                        <td class="px-5 py-3 text-zinc-400 text-xs">
                            {{ (requests.current_page - 1) * requests.per_page + index + 1 }}
                        </td>
                        <td class="px-5 py-3">
                            <p class="font-medium text-zinc-900">{{ req.employee?.nama }}</p>
                            <p class="text-xs text-zinc-400">{{ req.employee?.divisi }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <p class="text-zinc-800">{{ req.item?.nama_barang }}</p>
                            <p class="text-xs font-mono text-zinc-400">{{ req.item?.kode_barang }}</p>
                        </td>
                        <td class="px-5 py-3 text-zinc-700 font-medium">{{ req.jumlah }}</td>
                        <td class="px-5 py-3">
                            <span
                                class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold"
                                :class="req.item?.stok < 10 ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-700'"
                            >
                                {{ req.item?.stok }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-zinc-400 text-xs">{{ formatDate(req.created_at) }}</td>
                        <td class="px-5 py-3">
                            <button @click="openDelete(req)" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="px-5 py-3 border-t border-zinc-100 flex items-center justify-between">
                <p class="text-xs text-zinc-400">
                    Menampilkan {{ requests.from ?? 0 }}–{{ requests.to ?? 0 }} dari {{ requests.total }} data
                </p>
                <div class="flex gap-1">
                    <template v-for="link in requests.links" :key="link.label">
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
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-md mx-4 p-6">
                <h2 class="text-base font-semibold text-zinc-900 mb-5">Tambah Permintaan Barang</h2>
                <div class="space-y-4">

                    <!-- Dropdown Karyawan -->
                    <div class="relative">
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Nama Peminta</label>
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
                                    <p class="text-xs text-zinc-400">{{ emp.nik }} · {{ emp.divisi }}</p>
                                </div>
                            </div>
                        </div>
                        <p v-if="form.errors.employee_id" class="text-red-500 text-xs mt-1">{{ form.errors.employee_id }}</p>
                    </div>

                    <!-- Dropdown Barang -->
                    <div class="relative">
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Barang</label>
                        <div @click="showItemDropdown = !showItemDropdown"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm cursor-pointer flex items-center justify-between"
                            :class="selectedItemName ? 'text-zinc-800' : 'text-zinc-400'">
                            <span>{{ selectedItemName || 'Pilih barang...' }}</span>
                            <svg class="w-4 h-4 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div v-if="showItemDropdown" class="absolute z-10 w-full mt-1 bg-white border border-zinc-200 rounded-lg shadow-lg">
                            <div class="p-2 border-b border-zinc-100">
                                <input v-model="searchItem" type="text" placeholder="Cari barang..."
                                    class="w-full text-sm px-2 py-1.5 border border-zinc-200 rounded-md focus:outline-none" @click.stop />
                            </div>
                            <div class="max-h-48 overflow-y-auto">
                                <div v-if="filteredItems.length === 0" class="px-3 py-2 text-xs text-zinc-400">Tidak ditemukan</div>
                                <div v-for="item in filteredItems" :key="item.id"
                                    @click="selectItem(item)"
                                    class="px-3 py-2.5 hover:bg-zinc-50 cursor-pointer border-t border-zinc-50">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-zinc-800">{{ item.nama_barang }}</p>
                                            <p class="text-xs text-zinc-400 font-mono">{{ item.kode_barang }}</p>
                                        </div>
                                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md"
                                            :class="item.stok < 10 ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-700'">
                                            {{ item.satuan === 'box' && item.pcs_per_box
                                                ? `${Math.floor(item.stok / item.pcs_per_box)} box (${item.stok} pcs)`
                                                : `Stok: ${item.stok}` }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p v-if="form.errors.inventory_item_id" class="text-red-500 text-xs mt-1">{{ form.errors.inventory_item_id }}</p>
                    </div>

                    <!-- Jumlah -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">
                            Jumlah
                            <span v-if="selectedItemStok > 0" class="text-zinc-300 font-normal">
                                (maks. {{ selectedItemStok }})
                            </span>
                        </label>
                        <input v-model="form.jumlah" type="number" min="1" :max="selectedItemStok"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400" />
                        <p v-if="form.errors.jumlah" class="text-red-500 text-xs mt-1">{{ form.errors.jumlah }}</p>
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

        <!-- Modal Hapus -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-sm mx-4 p-6">
                <h2 class="text-base font-semibold text-zinc-900 mb-2">Hapus Permintaan</h2>
                <p class="text-sm text-zinc-500 mb-6">
                    Yakin ingin menghapus permintaan <span class="font-medium text-zinc-800">{{ selectedRequest?.item?.nama_barang }}</span>
                    oleh <span class="font-medium text-zinc-800">{{ selectedRequest?.employee?.nama }}</span>?
                    Stok akan dikembalikan.
                </p>
                <div class="flex justify-end gap-2">
                    <button @click="showDeleteModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Batal</button>
                    <button @click="confirmDelete" class="px-4 py-2 text-sm font-semibold bg-red-500 hover:bg-red-600 text-white rounded-lg">Hapus</button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>