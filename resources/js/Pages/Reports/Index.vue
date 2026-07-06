<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    loans: Object,
    filters: Object,
})

const search = ref(props.filters?.search ?? '')
const filterStatus = ref(props.filters?.status ?? '')
const filterBulan = ref(props.filters?.bulan ?? '')
const filterTahun = ref(props.filters?.tahun ?? '')

watch([search, filterStatus, filterBulan, filterTahun], () => {
    router.get(route('reports.index'), {
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
        <template #title>Laporan Peminjaman</template>

        <!-- Filter & Actions -->
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

            <div class="ml-auto">
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
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl border border-zinc-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-zinc-50 border-b border-zinc-200">
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">No</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Nama Peminjam</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Nama Barang</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Tgl Mulai</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Tgl Selesai</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Diketahui</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Disetujui</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loans.data.length === 0">
                        <td colspan="8" class="px-5 py-10 text-center text-zinc-400 text-xs">Belum ada data laporan</td>
                    </tr>
                    <tr
                        v-for="(loan, index) in loans.data"
                        :key="loan.id"
                        class="border-t border-zinc-100 hover:bg-zinc-50 transition-colors"
                    >
                        <td class="px-5 py-3 text-zinc-400 text-xs">
                            {{ (loans.current_page - 1) * loans.per_page + index + 1 }}
                        </td>
                        <td class="px-5 py-3">
                            <p class="font-medium text-zinc-900">{{ loan.employee?.nama }}</p>
                            <p class="text-xs text-zinc-400">{{ loan.employee?.divisi }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <p class="text-zinc-800">{{ loan.large_asset?.nama_barang ?? loan.nama_barang }}</p>
                            <p class="text-xs font-mono text-zinc-400">{{ loan.large_asset?.kode_barang }}</p>
                        </td>
                        <td class="px-5 py-3 text-zinc-400 text-xs">{{ formatDate(loan.tanggal_mulai) }}</td>
                        <td class="px-5 py-3 text-xs" :class="isLate(loan) ? 'text-red-500 font-medium' : 'text-zinc-400'">
                            {{ formatDate(loan.tanggal_selesai) }}
                        </td>
                        <td class="px-5 py-3">
                            <span v-if="loan.diketahui_oleh"
                                class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                {{ loan.diketahui_oleh.name }}
                            </span>
                            <span v-else class="text-zinc-300 text-xs">Pending</span>
                        </td>
                        <td class="px-5 py-3">
                            <span v-if="loan.disetujui_oleh"
                                class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                {{ loan.disetujui_oleh.name }}
                            </span>
                            <span v-else class="text-zinc-300 text-xs">Pending</span>
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

    </AppLayout>
</template>