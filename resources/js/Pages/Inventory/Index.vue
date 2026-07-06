<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, watch, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    tab: String,
    largeAssets: Object,
    smallAssets: Object,
    assetTypes: { type: Array, default: () => [] },
    smallAssetTypes: { type: Array, default: () => [] },
    filters: Object,
})

const activeTab = ref(props.tab ?? 'large')

const switchTab = (tab) => {
    activeTab.value = tab
    router.get(route('inventory.index'), { tab }, {
        preserveState: true,
        replace: true,
    })
}

const search = ref(props.filters?.search ?? '')
watch(search, (val) => {
    router.get(route('inventory.index'), { tab: activeTab.value, search: val }, {
        preserveState: true,
        replace: true,
    })
})

const showDetailModal = ref(false)
const selectedDetail = ref(null)

const openDetailLarge = (asset) => {
    selectedDetail.value = asset
    showDetailModal.value = true
}

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    })
}

// =====================
// TIPE ASSET BESAR
// =====================
const showAssetTypeModal = ref(false)
const isEditAssetType = ref(false)
const selectedAssetType = ref(null)
const showAssetTypeDeleteModal = ref(false)

const assetTypeForm = useForm({ nama: '' })

const openCreateAssetType = () => {
    isEditAssetType.value = false
    assetTypeForm.reset()
    showAssetTypeModal.value = true
}

const openEditAssetType = (type) => {
    isEditAssetType.value = true
    selectedAssetType.value = type
    assetTypeForm.nama = type.nama
    showAssetTypeModal.value = true
}

const openDeleteAssetType = (type) => {
    selectedAssetType.value = type
    showAssetTypeDeleteModal.value = true
}

const submitAssetType = () => {
    if (isEditAssetType.value) {
        assetTypeForm.put(route('asset-types.update', selectedAssetType.value.id), {
            onSuccess: () => { showAssetTypeModal.value = false; assetTypeForm.reset() },
        })
    } else {
        assetTypeForm.post(route('asset-types.store'), {
            onSuccess: () => { showAssetTypeModal.value = false; assetTypeForm.reset() },
        })
    }
}

const confirmDeleteAssetType = () => {
    router.delete(route('asset-types.destroy', selectedAssetType.value.id), {
        onSuccess: () => { showAssetTypeDeleteModal.value = false },
    })
}

const exportLarge = () => {
    window.location.href = route('inventory.export-large')
}

const paginationPages = (paginator) => {
    const current = paginator.current_page
    const last = paginator.last_page
    const pages = []

    if (last <= 7) {
        for (let i = 1; i <= last; i++) pages.push(i)
        return pages
    }

    pages.push(1)

    if (current > 4) pages.push('...')

    const start = Math.max(2, current - 1)
    const end = Math.min(last - 1, current + 1)

    for (let i = start; i <= end; i++) pages.push(i)

    if (current < last - 3) pages.push('...')

    pages.push(last)

    return pages
}

// =====================
// TIPE ASSET KECIL
// =====================
const showSmallAssetTypeModal = ref(false)
const isEditSmallAssetType = ref(false)
const selectedSmallAssetType = ref(null)
const showSmallAssetTypeDeleteModal = ref(false)

const smallAssetTypeForm = useForm({ nama: '' })

const openCreateSmallAssetType = () => {
    isEditSmallAssetType.value = false
    smallAssetTypeForm.reset()
    showSmallAssetTypeModal.value = true
}

const openEditSmallAssetType = (type) => {
    isEditSmallAssetType.value = true
    selectedSmallAssetType.value = type
    smallAssetTypeForm.nama = type.nama
    showSmallAssetTypeModal.value = true
}

const openDeleteSmallAssetType = (type) => {
    selectedSmallAssetType.value = type
    showSmallAssetTypeDeleteModal.value = true
}

const submitSmallAssetType = () => {
    if (isEditSmallAssetType.value) {
        smallAssetTypeForm.put(route('small-asset-types.update', selectedSmallAssetType.value.id), {
            onSuccess: () => { showSmallAssetTypeModal.value = false; smallAssetTypeForm.reset() },
        })
    } else {
        smallAssetTypeForm.post(route('small-asset-types.store'), {
            onSuccess: () => { showSmallAssetTypeModal.value = false; smallAssetTypeForm.reset() },
        })
    }
}

const confirmDeleteSmallAssetType = () => {
    router.delete(route('small-asset-types.destroy', selectedSmallAssetType.value.id), {
        onSuccess: () => { showSmallAssetTypeDeleteModal.value = false },
    })
}

const exportSmall = () => {
    window.location.href = route('inventory.export-small')
}

// =====================
// ASSET BESAR
// =====================
const showLargeModal = ref(false)
const isEditLarge = ref(false)
const selectedLarge = ref(null)
const showLargeDeleteModal = ref(false)

const largeForm = useForm({
    nama_barang: '',
    asset_type_id: '',
    tanggal_pembelian: '',
    garansi: false,
    warranty_end_date: '',
    serial_number: '',
    note: '',
    plat_nomor: '',
})

// Dropdown tipe asset besar
const searchAssetType = ref('')
const showAssetTypeDropdown = ref(false)
const filteredAssetTypes = computed(() =>
    props.assetTypes.filter(t =>
        t.nama.toLowerCase().includes(searchAssetType.value.toLowerCase())
    )
)
const selectAssetType = (type) => {
    largeForm.asset_type_id = type.id
    selectedAssetTypeName.value = type.nama
    showAssetTypeDropdown.value = false
    searchAssetType.value = ''
}
const selectedAssetTypeName = ref('')

const isVehicle = computed(() => {
    return ['mobil', 'motor'].includes(selectedAssetTypeName.value.toLowerCase())
})

const openCreateLarge = () => {
    isEditLarge.value = false
    largeForm.reset()
    selectedAssetTypeName.value = ''
    showLargeModal.value = true
}

const openEditLarge = (asset) => {
    isEditLarge.value = true
    selectedLarge.value = asset
    largeForm.nama_barang      = asset.nama_barang
    largeForm.asset_type_id    = asset.asset_type_id
    largeForm.tanggal_pembelian = asset.tanggal_pembelian
    largeForm.garansi          = asset.garansi
    largeForm.warranty_end_date = asset.warranty_end_date ?? ''
    largeForm.serial_number    = asset.serial_number ?? ''
    largeForm.note             = asset.note
    largeForm.plat_nomor       = asset.plat_nomor ?? ''
    selectedAssetTypeName.value = asset.asset_type?.nama ?? ''
    showLargeModal.value = true
}

const openDeleteLarge = (asset) => {
    selectedLarge.value = asset
    showLargeDeleteModal.value = true
}

const submitLarge = () => {
    if (isEditLarge.value) {
        largeForm.put(route('large-assets.update', selectedLarge.value.id), {
            onSuccess: () => { showLargeModal.value = false; largeForm.reset() },
        })
    } else {
        largeForm.post(route('large-assets.store'), {
            onSuccess: () => { showLargeModal.value = false; largeForm.reset() },
        })
    }
}

const confirmDeleteLarge = () => {
    router.delete(route('large-assets.destroy', selectedLarge.value.id), {
        onSuccess: () => { showLargeDeleteModal.value = false },
    })
}

// =====================
// ASSET KECIL
// =====================
const showSmallModal = ref(false)
const isEditSmall = ref(false)
const selectedSmall = ref(null)
const showSmallDeleteModal = ref(false)
const showRestockModal = ref(false)

const smallForm = useForm({
    nama_barang: '',
    small_asset_type_id: '',
    stok: 0,
    satuan: '',
    pcs_per_box: '',
    keterangan: '',
})

const restockForm = useForm({ jumlah: 1 })

// Dropdown tipe asset kecil
const searchSmallType = ref('')
const showSmallTypeDropdown = ref(false)
const filteredSmallTypes = computed(() =>
    props.smallAssetTypes.filter(t =>
        t.nama.toLowerCase().includes(searchSmallType.value.toLowerCase())
    )
)
const selectedSmallTypeName = ref('')
const selectSmallType = (type) => {
    smallForm.small_asset_type_id = type.id
    selectedSmallTypeName.value = type.nama
    showSmallTypeDropdown.value = false
    searchSmallType.value = ''
}

const openCreateSmall = () => {
    isEditSmall.value = false
    smallForm.reset()
    selectedSmallTypeName.value = ''
    showSmallModal.value = true
}

const openEditSmall = (asset) => {
    isEditSmall.value = true
    selectedSmall.value = asset
    smallForm.nama_barang         = asset.nama_barang
    smallForm.small_asset_type_id = asset.small_asset_type_id
    smallForm.stok                = asset.stok
    smallForm.satuan              = asset.satuan ?? ''
    smallForm.pcs_per_box         = asset.pcs_per_box ?? ''
    smallForm.keterangan          = asset.keterangan ?? ''
    selectedSmallTypeName.value   = asset.asset_type?.nama ?? ''
    showSmallModal.value = true
}

const openDeleteSmall = (asset) => {
    selectedSmall.value = asset
    showSmallDeleteModal.value = true
}

const openRestock = (asset) => {
    selectedSmall.value = asset
    restockForm.reset()
    showRestockModal.value = true
}

const openMoreId = ref(null)

const toggleMore = (id) => {
    openMoreId.value = openMoreId.value === id ? null : id
}


const showSmallDetailModal = ref(false)
const selectedSmallDetail = ref(null)

const openDetailSmall = (asset) => {
    selectedSmallDetail.value = asset
    showSmallDetailModal.value = true
}

const submitSmall = () => {
    if (isEditSmall.value) {
        smallForm.put(route('small-assets.update', selectedSmall.value.id), {
            onSuccess: () => { showSmallModal.value = false; smallForm.reset() },
        })
    } else {
        smallForm.post(route('small-assets.store'), {
            onSuccess: () => { showSmallModal.value = false; smallForm.reset() },
        })
    }
}

const confirmDeleteSmall = () => {
    router.delete(route('small-assets.destroy', selectedSmall.value.id), {
        onSuccess: () => { showSmallDeleteModal.value = false },
    })
}

const submitRestock = () => {
    restockForm.post(route('small-assets.restock', selectedSmall.value.id), {
        onSuccess: () => { showRestockModal.value = false; restockForm.reset() },
    })
}
</script>

<template>
    <AppLayout>
        <template #title>Inventaris Barang</template>

        <!-- Tabs -->
        <div class="flex gap-1 bg-zinc-100 p-1 rounded-xl w-fit mb-6">
            <button
                v-for="tab in [
                    { key: 'large', label: 'Asset Besar' },
                    { key: 'small', label: 'Asset Kecil' },
                    { key: 'types', label: 'Tipe Asset' },
                ]"
                :key="tab.key"
                @click="switchTab(tab.key)"
                class="px-5 py-2 text-sm font-medium rounded-lg transition-all duration-150"
                :class="activeTab === tab.key
                    ? 'bg-[#1A1A1A] text-[#F5C518] shadow-sm'
                    : 'text-zinc-500 hover:text-zinc-800'"
            >
                {{ tab.label }}
            </button>
        </div>

        <!-- =================== TAB ASSET BESAR =================== -->
        <div v-if="activeTab === 'large'">
            <div class="flex items-center justify-between mb-5">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama atau kode barang..."
                    class="bg-white border border-zinc-200 rounded-lg px-4 py-2 text-sm text-zinc-700 focus:outline-none focus:border-zinc-400 w-64"
                />
                <div class="flex items-center gap-2">
                    <button
                        @click="exportLarge"
                        class="flex items-center gap-2 bg-[#1A1A1A] hover:bg-zinc-800 text-[#F5C518] text-sm font-semibold px-4 py-2 rounded-lg transition-colors"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Export Excel
                    </button>
                    <button @click="openCreateLarge" class="bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] text-sm font-semibold px-4 py-2 rounded-lg transition-colors whitespace-nowrap">
                        + Tambah Asset
                    </button>   
                </div>
            </div>

            <div class="bg-white rounded-xl border border-zinc-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-zinc-50 border-b border-zinc-200">
                            <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Kode</th>
                            <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Nama Barang</th>
                            <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Tipe</th>
                            <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Tgl Pembelian</th>
                            <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Note</th>
                            <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="largeAssets.data.length === 0">
                            <td colspan="6" class="px-5 py-10 text-center text-zinc-400 text-xs">Belum ada data asset besar</td>
                        </tr>
                        <tr
                            v-for="asset in largeAssets.data"
                            :key="asset.id"
                            class="border-t border-zinc-100 hover:bg-zinc-50 transition-colors"
                        >
                            <td class="px-5 py-3 text-xs font-mono text-zinc-500">
                                {{ asset.kode_barang ?? asset.plat_nomor ?? '-' }}
                            </td>
                            <td class="px-5 py-3 text-zinc-600">{{ asset.nama_barang }}</td>
                            <td class="px-5 py-3 text-zinc-600">{{ asset.asset_type?.nama }}</td>
                            <td class="px-5 py-3 text-zinc-500 text-xs">{{ formatDate(asset.tanggal_pembelian) }}</td>
                            <td class="px-5 py-3 text-zinc-500 text-xs max-w-[200px] truncate">{{ asset.note }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <button @click="openDetailLarge(asset)" class="text-xs text-zinc-600 hover:text-zinc-800 font-medium">Detail</button>
                                    <span class="text-zinc-300">|</span>
                                    <button @click="openEditLarge(asset)" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                                    <span class="text-zinc-300">|</span>
                                    <button @click="openDeleteLarge(asset)" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="px-5 py-3 border-t border-zinc-100 flex items-center justify-between">
                    <p class="text-xs text-zinc-400">
                        Menampilkan {{ largeAssets.from ?? 0 }}–{{ largeAssets.to ?? 0 }} dari {{ largeAssets.total }} data
                    </p>
                    <div class="flex gap-1">
                        <button
                            :disabled="!largeAssets.prev_page_url"
                            @click="router.get(route('inventory.index'), { tab: 'large', page: largeAssets.current_page - 1, search: search })"
                            class="px-3 py-1 text-xs rounded-md border transition-colors bg-white text-zinc-500 border-zinc-200 hover:bg-zinc-50 disabled:opacity-40 disabled:cursor-not-allowed"
                        >&laquo;</button>

                        <template v-for="page in paginationPages(largeAssets)" :key="page">
                            <span v-if="page === '...'" class="px-3 py-1 text-xs text-zinc-400">...</span>
                            <button v-else
                                @click="router.get(route('inventory.index'), { tab: 'large', page: page, search: search })"
                                class="px-3 py-1 text-xs rounded-md border transition-colors"
                                :class="page === largeAssets.current_page
                                    ? 'bg-[#1A1A1A] text-[#F5C518] border-[#1A1A1A]'
                                    : 'bg-white text-zinc-500 border-zinc-200 hover:bg-zinc-50'"
                            >{{ page }}</button>
                        </template>

                        <button
                            :disabled="!largeAssets.next_page_url"
                            @click="router.get(route('inventory.index'), { tab: 'large', page: largeAssets.current_page + 1, search: search })"
                            class="px-3 py-1 text-xs rounded-md border transition-colors bg-white text-zinc-500 border-zinc-200 hover:bg-zinc-50 disabled:opacity-40 disabled:cursor-not-allowed"
                        >&raquo;</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- =================== TAB ASSET KECIL =================== -->
        <div v-if="activeTab === 'small'">
            <div class="flex items-center justify-between mb-5">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama atau kode barang..."
                    class="bg-white border border-zinc-200 rounded-lg px-4 py-2 text-sm text-zinc-700 focus:outline-none focus:border-zinc-400 w-64"
                />
                <div class="flex items-center gap-2">
                    <button
                        @click="exportSmall"
                        class="flex items-center gap-2 bg-[#1A1A1A] hover:bg-zinc-800 text-[#F5C518] text-sm font-semibold px-4 py-2 rounded-lg transition-colors"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Export Excel
                    </button>
                    <button @click="openCreateSmall" class="bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] text-sm font-semibold px-4 py-2 rounded-lg transition-colors whitespace-nowrap">
                        + Tambah Asset
                    </button>   
                </div>
            </div>

            <div class="bg-white rounded-xl border border-zinc-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-zinc-50 border-b border-zinc-200">
                            <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Kode</th>
                            <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Nama Barang</th>
                            <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Tipe</th>
                            <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Stok</th>
                            <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="smallAssets.data.length === 0">
                            <td colspan="5" class="px-5 py-10 text-center text-zinc-400 text-xs">Belum ada data asset kecil</td>
                        </tr>
                        <tr
                            v-for="asset in smallAssets.data"
                            :key="asset.id"
                            class="border-t border-zinc-100 hover:bg-zinc-50 transition-colors"
                        >
                            <td class="px-5 py-3 text-xs font-mono text-zinc-500">{{ asset.kode_barang }}</td>
                            <td class="px-5 py-3 text-zinc-600">{{ asset.nama_barang }}</td>
                            <td class="px-5 py-3 text-zinc-600">{{ asset.asset_type?.nama }}</td>
                            <td class="px-5 py-3">
                                <span
                                    class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold"
                                    :class="asset.stok < 10 ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-700'"
                                >
                                    {{ asset.stok }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="relative">
                                    <button
                                        @click="toggleMore(asset.id)"
                                        class="flex items-center gap-1 text-xs text-zinc-600 hover:text-zinc-800 font-medium border border-zinc-200 px-2.5 py-1.5 rounded-lg hover:bg-zinc-50 transition-colors"
                                    >
                                        More
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="6 9 12 15 18 9"/>
                                        </svg>
                                    </button>
                                    <div v-if="openMoreId === asset.id"
                                        class="absolute right-0 z-10 mt-1 w-36 bg-white border border-zinc-200 rounded-lg shadow-lg overflow-hidden">
                                        <button @click="openDetailSmall(asset); openMoreId = null"
                                            class="w-full text-left px-3 py-2 text-xs text-zinc-700 hover:bg-zinc-50 transition-colors">
                                            Detail
                                        </button>
                                        <button @click="openRestock(asset); openMoreId = null"
                                            class="w-full text-left px-3 py-2 text-xs text-emerald-600 hover:bg-zinc-50 transition-colors">
                                            Restock
                                        </button>
                                        <button @click="openEditSmall(asset); openMoreId = null"
                                            class="w-full text-left px-3 py-2 text-xs text-blue-600 hover:bg-zinc-50 transition-colors">
                                            Edit
                                        </button>
                                        <button @click="openDeleteSmall(asset); openMoreId = null"
                                            class="w-full text-left px-3 py-2 text-xs text-red-500 hover:bg-zinc-50 transition-colors">
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="px-5 py-3 border-t border-zinc-100 flex items-center justify-between">
                    <p class="text-xs text-zinc-400">
                        Menampilkan {{ smallAssets.from ?? 0 }}–{{ smallAssets.to ?? 0 }} dari {{ smallAssets.total }} data
                    </p>
                    <div class="flex gap-1">
                        <button
                            :disabled="!smallAssets.prev_page_url"
                            @click="router.get(route('inventory.index'), { tab: 'small', page: smallAssets.current_page - 1, search: search })"
                            class="px-3 py-1 text-xs rounded-md border transition-colors bg-white text-zinc-500 border-zinc-200 hover:bg-zinc-50 disabled:opacity-40 disabled:cursor-not-allowed"
                        >&laquo;</button>

                        <template v-for="page in paginationPages(smallAssets)" :key="page">
                            <span v-if="page === '...'" class="px-3 py-1 text-xs text-zinc-400">...</span>
                            <button v-else
                                @click="router.get(route('inventory.index'), { tab: 'small', page: page, search: search })"
                                class="px-3 py-1 text-xs rounded-md border transition-colors"
                                :class="page === smallAssets.current_page
                                    ? 'bg-[#1A1A1A] text-[#F5C518] border-[#1A1A1A]'
                                    : 'bg-white text-zinc-500 border-zinc-200 hover:bg-zinc-50'"
                            >{{ page }}</button>
                        </template>

                        <button
                            :disabled="!smallAssets.next_page_url"
                            @click="router.get(route('inventory.index'), { tab: 'small', page: smallAssets.current_page + 1, search: search })"
                            class="px-3 py-1 text-xs rounded-md border transition-colors bg-white text-zinc-500 border-zinc-200 hover:bg-zinc-50 disabled:opacity-40 disabled:cursor-not-allowed"
                        >&raquo;</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- =================== TAB TIPE ASSET =================== -->
        <div v-if="activeTab === 'types'">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Tipe Asset Besar -->
                <div class="bg-white rounded-xl border border-zinc-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between">
                        <p class="text-sm font-semibold text-zinc-900">Tipe Asset Besar</p>
                        <button @click="openCreateAssetType" class="bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] text-xs font-semibold px-3 py-1.5 rounded-lg">+ Tambah</button>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-zinc-50 border-b border-zinc-100">
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">No</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Nama Tipe</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Jumlah Asset</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="assetTypes.length === 0">
                                <td colspan="4" class="px-5 py-8 text-center text-zinc-400 text-xs">Belum ada tipe asset</td>
                            </tr>
                            <tr v-for="(type, index) in assetTypes" :key="type.id" class="border-t border-zinc-100 hover:bg-zinc-50">
                                <td class="px-5 py-3 text-zinc-400 text-xs">{{ index + 1 }}</td>
                                <td class="px-5 py-3 text-zinc-600">{{ type.nama }}</td>
                                <td class="px-5 py-3">
                                    <span class="bg-blue-50 text-blue-700 text-[11px] font-semibold px-2.5 py-1 rounded-md">
                                        {{ type.large_assets_count }} item
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <button @click="openEditAssetType(type)" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                                        <span class="text-zinc-300">|</span>
                                        <button @click="openDeleteAssetType(type)" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Tipe Asset Kecil -->
                <div class="bg-white rounded-xl border border-zinc-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between">
                        <p class="text-sm font-semibold text-zinc-900">Tipe Asset Kecil</p>
                        <button @click="openCreateSmallAssetType" class="bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] text-xs font-semibold px-3 py-1.5 rounded-lg">+ Tambah</button>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-zinc-50 border-b border-zinc-100">
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">No</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Nama Tipe</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="smallAssetTypes.length === 0">
                                <td colspan="3" class="px-5 py-8 text-center text-zinc-400 text-xs">Belum ada tipe asset kecil</td>
                            </tr>
                            <tr v-for="(type, index) in smallAssetTypes" :key="type.id" class="border-t border-zinc-100 hover:bg-zinc-50">
                                <td class="px-5 py-3 text-zinc-400 text-xs">{{ index + 1 }}</td>
                                <td class="px-5 py-3 text-zinc-600">{{ type.nama }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <button @click="openEditSmallAssetType(type)" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                                        <span class="text-zinc-300">|</span>
                                        <button @click="openDeleteSmallAssetType(type)" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- =================== MODALS ASSET BESAR =================== -->
        <div v-if="showLargeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-lg mx-4 p-6 max-h-[90vh] overflow-y-auto">
                <h2 class="text-base font-semibold text-zinc-900 mb-5">
                    {{ isEditLarge ? 'Edit Asset Besar' : 'Tambah Asset Besar' }}
                </h2>
                <div class="space-y-4">

                    <!-- Nama Barang -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Nama Barang</label>
                        <input v-model="largeForm.nama_barang" type="text" placeholder="Nama barang"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400" />
                        <p v-if="largeForm.errors.nama_barang" class="text-red-500 text-xs mt-1">{{ largeForm.errors.nama_barang }}</p>
                    </div>

                    <!-- Tipe Asset Dropdown -->
                    <div class="relative">
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Tipe Asset</label>
                        <div @click="showAssetTypeDropdown = !showAssetTypeDropdown"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm cursor-pointer flex items-center justify-between"
                            :class="selectedAssetTypeName ? 'text-zinc-800' : 'text-zinc-400'">
                            <span>{{ selectedAssetTypeName || 'Pilih tipe asset...' }}</span>
                            <svg class="w-4 h-4 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div v-if="showAssetTypeDropdown" class="absolute z-10 w-full mt-1 bg-white border border-zinc-200 rounded-lg shadow-lg">
                            <div class="p-2 border-b border-zinc-100">
                                <input v-model="searchAssetType" type="text" placeholder="Cari tipe asset..."
                                    class="w-full text-sm px-2 py-1.5 border border-zinc-200 rounded-md focus:outline-none" @click.stop />
                            </div>
                            <div class="max-h-40 overflow-y-auto">
                                <div v-if="filteredAssetTypes.length === 0" class="px-3 py-2 text-xs text-zinc-400">Tidak ditemukan</div>
                                <div v-for="type in filteredAssetTypes" :key="type.id"
                                    @click="selectAssetType(type)"
                                    class="px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 cursor-pointer">
                                    {{ type.nama }}
                                </div>
                            </div>
                        </div>
                        <p v-if="largeForm.errors.asset_type_id" class="text-red-500 text-xs mt-1">{{ largeForm.errors.asset_type_id }}</p>
                    </div>

                    <!-- Kode / Plat Nomor -->
                    <div v-if="isVehicle">
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Plat Nomor</label>
                        <input v-model="largeForm.plat_nomor" type="text" placeholder="Contoh: B 1234 ABC"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400" />
                    </div>
                    <div v-else-if="!isEditLarge">
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Kode Barang</label>
                        <div class="w-full border border-zinc-100 bg-zinc-50 rounded-lg px-3 py-2.5 text-sm text-zinc-400">
                            Auto-generate saat disimpan
                        </div>
                    </div>

                    <!-- Tanggal Pembelian -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Tanggal Pembelian</label>
                        <input v-model="largeForm.tanggal_pembelian" type="date"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400" />
                        <p v-if="largeForm.errors.tanggal_pembelian" class="text-red-500 text-xs mt-1">{{ largeForm.errors.tanggal_pembelian }}</p>
                    </div>

                    <!-- Garansi -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-2">Garansi</label>
                        <div class="flex gap-3">
                            <button type="button" @click="largeForm.garansi = true"
                                class="px-4 py-2 text-sm rounded-lg border transition-colors"
                                :class="largeForm.garansi ? 'bg-[#1A1A1A] text-[#F5C518] border-[#1A1A1A]' : 'bg-white text-zinc-500 border-zinc-200'">
                                Ya
                            </button>
                            <button type="button" @click="largeForm.garansi = false; largeForm.warranty_end_date = ''"
                                class="px-4 py-2 text-sm rounded-lg border transition-colors"
                                :class="!largeForm.garansi ? 'bg-[#1A1A1A] text-[#F5C518] border-[#1A1A1A]' : 'bg-white text-zinc-500 border-zinc-200'">
                                Tidak
                            </button>
                        </div>
                    </div>

                    <!-- Warranty End Date -->
                    <div v-if="largeForm.garansi">
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Tanggal Akhir Garansi</label>
                        <input v-model="largeForm.warranty_end_date" type="date"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400" />
                        <p v-if="largeForm.errors.warranty_end_date" class="text-red-500 text-xs mt-1">{{ largeForm.errors.warranty_end_date }}</p>
                    </div>

                    <!-- Serial Number -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Serial Number <span class="text-zinc-300">(opsional)</span></label>
                        <input
                            v-model="largeForm.serial_number"
                            type="text"
                            inputmode="numeric"
                            placeholder="Serial number"
                            @input="largeForm.serial_number = largeForm.serial_number.replace(/\D/g, '')"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400"
                        />
                    </div>

                    <!-- Note -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Note</label>
                        <textarea v-model="largeForm.note" rows="3" placeholder="Catatan tambahan"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400 resize-none" />
                        <p v-if="largeForm.errors.note" class="text-red-500 text-xs mt-1">{{ largeForm.errors.note }}</p>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button @click="showLargeModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Batal</button>
                    <button @click="submitLarge" :disabled="largeForm.processing"
                        class="px-4 py-2 text-sm font-semibold bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] rounded-lg disabled:opacity-60">
                        {{ largeForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Hapus Asset Besar -->
        <div v-if="showLargeDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-sm mx-4 p-6">
                <h2 class="text-base font-semibold text-zinc-900 mb-2">Hapus Asset</h2>
                <p class="text-sm text-zinc-500 mb-6">Yakin ingin menghapus <span class="font-medium text-zinc-800">{{ selectedLarge?.nama_barang }}</span>?</p>
                <div class="flex justify-end gap-2">
                    <button @click="showLargeDeleteModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Batal</button>
                    <button @click="confirmDeleteLarge" class="px-4 py-2 text-sm font-semibold bg-red-500 hover:bg-red-600 text-white rounded-lg">Hapus</button>
                </div>
            </div>
        </div>

        <!-- =================== MODALS ASSET KECIL =================== -->
        <div v-if="showSmallModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-2xl mx-4 p-6 max-h-[90vh] overflow-y-auto">
                <h2 class="text-base font-semibold text-zinc-900 mb-5">
                    {{ isEditSmall ? 'Edit Asset Kecil' : 'Tambah Asset Kecil' }}
                </h2>
                <div class="grid grid-cols-2 gap-4">
                    <!-- Nama Barang -->
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Nama Barang</label>
                        <input v-model="smallForm.nama_barang" type="text" placeholder="Nama barang"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400" />
                        <p v-if="smallForm.errors.nama_barang" class="text-red-500 text-xs mt-1">{{ smallForm.errors.nama_barang }}</p>
                    </div>

                    <!-- Tipe Asset Kecil -->
                    <div class="relative">
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Tipe Asset</label>
                        <div @click="showSmallTypeDropdown = !showSmallTypeDropdown"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm cursor-pointer flex items-center justify-between"
                            :class="selectedSmallTypeName ? 'text-zinc-800' : 'text-zinc-400'">
                            <span>{{ selectedSmallTypeName || 'Pilih tipe asset...' }}</span>
                            <svg class="w-4 h-4 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div v-if="showSmallTypeDropdown" class="absolute z-10 w-full mt-1 bg-white border border-zinc-200 rounded-lg shadow-lg">
                            <div class="p-2 border-b border-zinc-100">
                                <input v-model="searchSmallType" type="text" placeholder="Cari tipe..."
                                    class="w-full text-sm px-2 py-1.5 border border-zinc-200 rounded-md focus:outline-none" @click.stop />
                            </div>
                            <div class="max-h-40 overflow-y-auto">
                                <div v-if="filteredSmallTypes.length === 0" class="px-3 py-2 text-xs text-zinc-400">Tidak ditemukan</div>
                                <div v-for="type in filteredSmallTypes" :key="type.id"
                                    @click="selectSmallType(type)"
                                    class="px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 cursor-pointer">
                                    {{ type.nama }}
                                </div>
                            </div>
                        </div>
                        <p v-if="smallForm.errors.small_asset_type_id" class="text-red-500 text-xs mt-1">{{ smallForm.errors.small_asset_type_id }}</p>
                    </div>

                    <!-- Stok -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Stok Awal</label>
                        <input v-model="smallForm.stok" type="number" min="0" placeholder="0"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400" />
                        <p v-if="smallForm.errors.stok" class="text-red-500 text-xs mt-1">{{ smallForm.errors.stok }}</p>
                    </div>

                    <!-- Satuan -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Satuan</label>
                        <select v-model="smallForm.satuan"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400">
                            <option value="" disabled>Pilih satuan...</option>
                            <option value="pcs">Pcs</option>
                            <option value="box">Box</option>
                            <option value="meter">Meter</option>
                            <option value="cm">Cm</option>
                            <option value="lusin">Lusin</option>
                            <option value="rim">Rim</option>
                            <option value="lembar">Lembar</option>
                            <option value="kg">Kg</option>
                            <option value="liter">Liter</option>
                        </select>
                        <p v-if="smallForm.errors.satuan" class="text-red-500 text-xs mt-1">{{ smallForm.errors.satuan }}</p>
                    </div>

                    <!-- Pcs Per Box -->
                    <div v-if="smallForm.satuan === 'box'" class="col-span-2">
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">
                            Jumlah Pcs per Box
                            <span class="text-zinc-400 font-normal">(1 box = berapa pcs?)</span>
                        </label>
                        <input v-model="smallForm.pcs_per_box" type="number" min="1" placeholder="Contoh: 12"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400" />
                        <p v-if="smallForm.errors.pcs_per_box" class="text-red-500 text-xs mt-1">{{ smallForm.errors.pcs_per_box }}</p>
                    </div>

                    <!-- Kode Barang -->
                    <div v-if="!isEditSmall" class="col-span-2">
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Kode Barang</label>
                        <div class="w-full border border-zinc-100 bg-zinc-50 rounded-lg px-3 py-2.5 text-sm text-zinc-400">
                            Auto-generate saat disimpan
                        </div>
                    </div>

                    <!-- Keterangan -->
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Keterangan <span class="text-zinc-300">(opsional)</span></label>
                        <textarea v-model="smallForm.keterangan" rows="2" placeholder="Keterangan tambahan..."
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400 resize-none" />
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button @click="showSmallModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Batal</button>
                    <button @click="submitSmall" :disabled="smallForm.processing"
                        class="px-4 py-2 text-sm font-semibold bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] rounded-lg disabled:opacity-60">
                        {{ smallForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Hapus Asset Kecil -->
        <div v-if="showSmallDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-sm mx-4 p-6">
                <h2 class="text-base font-semibold text-zinc-900 mb-2">Hapus Asset</h2>
                <p class="text-sm text-zinc-500 mb-6">Yakin ingin menghapus <span class="font-medium text-zinc-800">{{ selectedSmall?.nama_barang }}</span>?</p>
                <div class="flex justify-end gap-2">
                    <button @click="showSmallDeleteModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Batal</button>
                    <button @click="confirmDeleteSmall" class="px-4 py-2 text-sm font-semibold bg-red-500 hover:bg-red-600 text-white rounded-lg">Hapus</button>
                </div>
            </div>
        </div>

        <!-- Modal Restock -->
        <div v-if="showRestockModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-sm mx-4 p-6">
                <h2 class="text-base font-semibold text-zinc-900 mb-2">Restock Barang</h2>
                <p class="text-sm text-zinc-500 mb-4">
                    Tambah stok untuk <span class="font-medium text-zinc-800">{{ selectedSmall?.nama_barang }}</span>
                    (stok saat ini: <span class="font-medium">{{ selectedSmall?.stok }}</span>)
                </p>
                <div>
                    <label class="block text-xs font-medium text-zinc-500 mb-1.5">Jumlah Tambahan</label>
                    <input v-model="restockForm.jumlah" type="number" min="1"
                        class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400" />
                    <p v-if="restockForm.errors.jumlah" class="text-red-500 text-xs mt-1">{{ restockForm.errors.jumlah }}</p>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button @click="showRestockModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Batal</button>
                    <button @click="submitRestock" :disabled="restockForm.processing"
                        class="px-4 py-2 text-sm font-semibold bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] rounded-lg disabled:opacity-60">
                        {{ restockForm.processing ? 'Menyimpan...' : 'Tambah Stok' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Tipe Asset Besar -->
        <div v-if="showAssetTypeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-sm mx-4 p-6">
                <h2 class="text-base font-semibold text-zinc-900 mb-5">{{ isEditAssetType ? 'Edit Tipe Asset' : 'Tambah Tipe Asset' }}</h2>
                <div>
                    <label class="block text-xs font-medium text-zinc-500 mb-1.5">Nama Tipe</label>
                    <input v-model="assetTypeForm.nama" type="text" placeholder="Contoh: Laptop, Mobil, HP"
                        class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400" />
                    <p v-if="assetTypeForm.errors.nama" class="text-red-500 text-xs mt-1">{{ assetTypeForm.errors.nama }}</p>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button @click="showAssetTypeModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Batal</button>
                    <button @click="submitAssetType" :disabled="assetTypeForm.processing"
                        class="px-4 py-2 text-sm font-semibold bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] rounded-lg disabled:opacity-60">
                        {{ assetTypeForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Hapus Tipe Asset Besar -->
        <div v-if="showAssetTypeDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-sm mx-4 p-6">
                <h2 class="text-base font-semibold text-zinc-900 mb-2">Hapus Tipe Asset</h2>
                <p class="text-sm text-zinc-500 mb-6">Yakin ingin menghapus tipe <span class="font-medium text-zinc-800">{{ selectedAssetType?.nama }}</span>?</p>
                <div class="flex justify-end gap-2">
                    <button @click="showAssetTypeDeleteModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Batal</button>
                    <button @click="confirmDeleteAssetType" class="px-4 py-2 text-sm font-semibold bg-red-500 hover:bg-red-600 text-white rounded-lg">Hapus</button>
                </div>
            </div>
        </div>

        <!-- Modal Tipe Asset Kecil -->
        <div v-if="showSmallAssetTypeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-sm mx-4 p-6">
                <h2 class="text-base font-semibold text-zinc-900 mb-5">{{ isEditSmallAssetType ? 'Edit Tipe Asset Kecil' : 'Tambah Tipe Asset Kecil' }}</h2>
                <div>
                    <label class="block text-xs font-medium text-zinc-500 mb-1.5">Nama Tipe</label>
                    <input v-model="smallAssetTypeForm.nama" type="text" placeholder="Contoh: ATK, Elektronik"
                        class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400" />
                    <p v-if="smallAssetTypeForm.errors.nama" class="text-red-500 text-xs mt-1">{{ smallAssetTypeForm.errors.nama }}</p>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button @click="showSmallAssetTypeModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Batal</button>
                    <button @click="submitSmallAssetType" :disabled="smallAssetTypeForm.processing"
                        class="px-4 py-2 text-sm font-semibold bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] rounded-lg disabled:opacity-60">
                        {{ smallAssetTypeForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Hapus Tipe Asset Kecil -->
        <div v-if="showSmallAssetTypeDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-sm mx-4 p-6">
                <h2 class="text-base font-semibold text-zinc-900 mb-2">Hapus Tipe Asset Kecil</h2>
                <p class="text-sm text-zinc-500 mb-6">Yakin ingin menghapus tipe <span class="font-medium text-zinc-800">{{ selectedSmallAssetType?.nama }}</span>?</p>
                <div class="flex justify-end gap-2">
                    <button @click="showSmallAssetTypeDeleteModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Batal</button>
                    <button @click="confirmDeleteSmallAssetType" class="px-4 py-2 text-sm font-semibold bg-red-500 hover:bg-red-600 text-white rounded-lg">Hapus</button>
                </div>
            </div>
        </div>

        <!-- Modal Detail Asset Besar -->
        <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-lg mx-4 p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-base font-semibold text-zinc-900">Detail Asset</h2>
                    <button @click="showDetailModal = false" class="text-zinc-400 hover:text-zinc-600">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Kode Barang</p>
                            <p class="text-sm font-mono text-zinc-800">{{ selectedDetail?.kode_barang ?? selectedDetail?.plat_nomor ?? '-' }}</p>
                        </div>
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Tipe Asset</p>
                            <p class="text-sm text-zinc-800">{{ selectedDetail?.asset_type?.nama ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="bg-zinc-50 rounded-lg p-3">
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Nama Barang</p>
                        <p class="text-sm text-zinc-800">{{ selectedDetail?.nama_barang }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Tanggal Pembelian</p>
                            <p class="text-sm text-zinc-800">{{ formatDate(selectedDetail?.tanggal_pembelian) }}</p>
                        </div>
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Serial Number</p>
                            <p class="text-sm text-zinc-800">{{ selectedDetail?.serial_number ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Garansi</p>
                            <span
                                class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold"
                                :class="selectedDetail?.garansi ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-500'"
                            >
                                {{ selectedDetail?.garansi ? 'Ya' : 'Tidak' }}
                            </span>
                        </div>
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Akhir Garansi</p>
                            <p class="text-sm text-zinc-800">{{ selectedDetail?.warranty_end_date ? formatDate(selectedDetail.warranty_end_date) : '-' }}</p>
                        </div>
                    </div>

                    <div class="bg-zinc-50 rounded-lg p-3">
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Note</p>
                        <p class="text-sm text-zinc-800 whitespace-pre-wrap">{{ selectedDetail?.note ?? '-' }}</p>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button @click="showDetailModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Detail Asset Kecil -->
        <div v-if="showSmallDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-md mx-4 p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-base font-semibold text-zinc-900">Detail Asset Kecil</h2>
                    <button @click="showSmallDetailModal = false" class="text-zinc-400 hover:text-zinc-600">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Kode Barang</p>
                            <p class="text-sm font-mono text-zinc-800">{{ selectedSmallDetail?.kode_barang }}</p>
                        </div>
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Tipe Asset</p>
                            <p class="text-sm text-zinc-800">{{ selectedSmallDetail?.asset_type?.nama ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="bg-zinc-50 rounded-lg p-3">
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Nama Barang</p>
                        <p class="text-sm text-zinc-800">{{ selectedSmallDetail?.nama_barang }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Stok</p>
                            <span class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold"
                                :class="selectedSmallDetail?.stok < 10 ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-700'">
                                {{ selectedSmallDetail?.stok }}
                            </span>
                        </div>
                        <div class="bg-zinc-50 rounded-lg p-3">
                            <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Satuan</p>
                            <p class="text-sm text-zinc-800 capitalize">{{ selectedSmallDetail?.satuan ?? '-' }}</p>
                        </div>
                    </div>
                    <div v-if="selectedSmallDetail?.satuan === 'box'" class="bg-zinc-50 rounded-lg p-3">
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Pcs per Box</p>
                        <p class="text-sm text-zinc-800">{{ selectedSmallDetail?.pcs_per_box ?? '-' }} pcs</p>
                    </div>
                    <div class="bg-zinc-50 rounded-lg p-3">
                        <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-1">Keterangan</p>
                        <p class="text-sm text-zinc-800 whitespace-pre-wrap">{{ selectedSmallDetail?.keterangan ?? '-' }}</p>
                    </div>
                </div>
                <div class="flex justify-end mt-6">
                    <button @click="showSmallDetailModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Tutup</button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>