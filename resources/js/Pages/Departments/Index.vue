<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    divisions: Array,
    positions: Array,
})

// ---- Divisions ----
const showDivisionModal = ref(false)
const showDivisionDeleteModal = ref(false)
const isEditDivision = ref(false)
const selectedDivision = ref(null)

const divisionForm = useForm({ nama: '' })

const openCreateDivision = () => {
    isEditDivision.value = false
    divisionForm.reset()
    showDivisionModal.value = true
}

const openEditDivision = (div) => {
    isEditDivision.value = true
    selectedDivision.value = div
    divisionForm.nama = div.nama
    showDivisionModal.value = true
}

const openDeleteDivision = (div) => {
    selectedDivision.value = div
    showDivisionDeleteModal.value = true
}

const submitDivision = () => {
    if (isEditDivision.value) {
        divisionForm.put(route('divisions.update', selectedDivision.value.id), {
            onSuccess: () => { showDivisionModal.value = false; divisionForm.reset() },
        })
    } else {
        divisionForm.post(route('divisions.store'), {
            onSuccess: () => { showDivisionModal.value = false; divisionForm.reset() },
        })
    }
}

const confirmDeleteDivision = () => {
    router.delete(route('divisions.destroy', selectedDivision.value.id), {
        onSuccess: () => { showDivisionDeleteModal.value = false },
    })
}

// ---- Positions ----
const showPositionModal = ref(false)
const showPositionDeleteModal = ref(false)
const isEditPosition = ref(false)
const selectedPosition = ref(null)

const positionForm = useForm({ nama: '' })

const openCreatePosition = () => {
    isEditPosition.value = false
    positionForm.reset()
    showPositionModal.value = true
}

const openEditPosition = (pos) => {
    isEditPosition.value = true
    selectedPosition.value = pos
    positionForm.nama = pos.nama
    showPositionModal.value = true
}

const openDeletePosition = (pos) => {
    selectedPosition.value = pos
    showPositionDeleteModal.value = true
}

const submitPosition = () => {
    if (isEditPosition.value) {
        positionForm.put(route('positions.update', selectedPosition.value.id), {
            onSuccess: () => { showPositionModal.value = false; positionForm.reset() },
        })
    } else {
        positionForm.post(route('positions.store'), {
            onSuccess: () => { showPositionModal.value = false; positionForm.reset() },
        })
    }
}

const confirmDeletePosition = () => {
    router.delete(route('positions.destroy', selectedPosition.value.id), {
        onSuccess: () => { showPositionDeleteModal.value = false },
    })
}
</script>

<template>
    <AppLayout>
        <template #title>Departemen</template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Divisi -->
            <div class="bg-white rounded-xl border border-zinc-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between">
                    <p class="text-sm font-semibold text-zinc-900">Divisi</p>
                    <button
                        @click="openCreateDivision"
                        class="bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors"
                    >
                        + Tambah
                    </button>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-zinc-50 border-b border-zinc-100">
                            <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">No</th>
                            <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Nama Divisi</th>
                            <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="divisions.length === 0">
                            <td colspan="3" class="px-5 py-8 text-center text-zinc-400 text-xs">Belum ada data divisi</td>
                        </tr>
                        <tr
                            v-for="(div, index) in divisions"
                            :key="div.id"
                            class="border-t border-zinc-100 hover:bg-zinc-50 transition-colors"
                        >
                            <td class="px-5 py-3 text-zinc-400 text-xs">{{ index + 1 }}</td>
                            <td class="px-5 py-3 text-zinc-600">{{ div.nama }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <button @click="openEditDivision(div)" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                                    <span class="text-zinc-300">|</span>
                                    <button @click="openDeleteDivision(div)" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Jabatan -->
            <div class="bg-white rounded-xl border border-zinc-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between">
                    <p class="text-sm font-semibold text-zinc-900">Jabatan</p>
                    <button
                        @click="openCreatePosition"
                        class="bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors"
                    >
                        + Tambah
                    </button>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-zinc-50 border-b border-zinc-100">
                            <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">No</th>
                            <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Nama Jabatan</th>
                            <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="positions.length === 0">
                            <td colspan="3" class="px-5 py-8 text-center text-zinc-400 text-xs">Belum ada data jabatan</td>
                        </tr>
                        <tr
                            v-for="(pos, index) in positions"
                            :key="pos.id"
                            class="border-t border-zinc-100 hover:bg-zinc-50 transition-colors"
                        >
                            <td class="px-5 py-3 text-zinc-400 text-xs">{{ index + 1 }}</td>
                            <td class="px-5 py-3 text-zinc-600">{{ pos.nama }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <button @click="openEditPosition(pos)" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                                    <span class="text-zinc-300">|</span>
                                    <button @click="openDeletePosition(pos)" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Divisi -->
        <div v-if="showDivisionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-sm mx-4 p-6">
                <h2 class="text-base font-semibold text-zinc-900 mb-5">
                    {{ isEditDivision ? 'Edit Divisi' : 'Tambah Divisi' }}
                </h2>
                <div>
                    <label class="block text-xs font-medium text-zinc-500 mb-1.5">Nama Divisi</label>
                    <input
                        v-model="divisionForm.nama"
                        type="text"
                        placeholder="Contoh: Finance, IT, HRD"
                        class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-900 focus:outline-none focus:border-zinc-400"
                    />
                    <p v-if="divisionForm.errors.nama" class="text-red-500 text-xs mt-1">{{ divisionForm.errors.nama }}</p>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button @click="showDivisionModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Batal</button>
                    <button @click="submitDivision" :disabled="divisionForm.processing" class="px-4 py-2 text-sm font-semibold bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] rounded-lg disabled:opacity-60">
                        {{ divisionForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Hapus Divisi -->
        <div v-if="showDivisionDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-sm mx-4 p-6">
                <h2 class="text-base font-semibold text-zinc-900 mb-2">Hapus Divisi</h2>
                <p class="text-sm text-zinc-500 mb-6">Yakin ingin menghapus <span class="font-medium text-zinc-800">{{ selectedDivision?.nama }}</span>?</p>
                <div class="flex justify-end gap-2">
                    <button @click="showDivisionDeleteModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Batal</button>
                    <button @click="confirmDeleteDivision" class="px-4 py-2 text-sm font-semibold bg-red-500 hover:bg-red-600 text-white rounded-lg">Hapus</button>
                </div>
            </div>
        </div>

        <!-- Modal Jabatan -->
        <div v-if="showPositionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-sm mx-4 p-6">
                <h2 class="text-base font-semibold text-zinc-900 mb-5">
                    {{ isEditPosition ? 'Edit Jabatan' : 'Tambah Jabatan' }}
                </h2>
                <div>
                    <label class="block text-xs font-medium text-zinc-500 mb-1.5">Nama Jabatan</label>
                    <input
                        v-model="positionForm.nama"
                        type="text"
                        placeholder="Contoh: Staff, Manager, Supervisor"
                        class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400"
                    />
                    <p v-if="positionForm.errors.nama" class="text-red-500 text-xs mt-1">{{ positionForm.errors.nama }}</p>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button @click="showPositionModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Batal</button>
                    <button @click="submitPosition" :disabled="positionForm.processing" class="px-4 py-2 text-sm font-semibold bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] rounded-lg disabled:opacity-60">
                        {{ positionForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Hapus Jabatan -->
        <div v-if="showPositionDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-sm mx-4 p-6">
                <h2 class="text-base font-semibold text-zinc-900 mb-2">Hapus Jabatan</h2>
                <p class="text-sm text-zinc-500 mb-6">Yakin ingin menghapus <span class="font-medium text-zinc-800">{{ selectedPosition?.nama }}</span>?</p>
                <div class="flex justify-end gap-2">
                    <button @click="showPositionDeleteModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Batal</button>
                    <button @click="confirmDeletePosition" class="px-4 py-2 text-sm font-semibold bg-red-500 hover:bg-red-600 text-white rounded-lg">Hapus</button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>