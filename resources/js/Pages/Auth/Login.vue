<script setup>
import { useForm } from "@inertiajs/vue3";

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post("/", {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <div
        class="min-h-screen bg-[#1A1A1A] flex items-center justify-center px-4"
    >
        <div class="w-full max-w-sm">
            <!-- Logo & Title -->
            <div class="text-center mb-8">
                <img :src="'/Images/logo-bmc.png'" alt="BMC Logo" class="h-16 mx-auto mb-4" />
                <p class="text-zinc-500 text-sm mt-1">Asset Management System</p>
            </div>

            <!-- Card -->
            <div class="bg-[#242424] rounded-xl border border-zinc-800 p-6">
                <h2 class="text-white text-base font-medium mb-5">
                    Masuk ke akun Anda
                </h2>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Email -->
                    <div>
                        <label
                            class="block text-zinc-400 text-xs font-medium mb-1.5"
                        >
                            Email
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            placeholder="admin@bas.com"
                            class="w-full bg-[#1A1A1A] border border-zinc-700 rounded-lg px-3 py-2.5 text-white text-sm placeholder-zinc-600 focus:outline-none focus:border-[#F5C518] transition-colors"
                        />
                        <p
                            v-if="form.errors.email"
                            class="text-red-400 text-xs mt-1"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label
                            class="block text-zinc-400 text-xs font-medium mb-1.5"
                        >
                            Password
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full bg-[#1A1A1A] border border-zinc-700 rounded-lg px-3 py-2.5 text-white text-sm placeholder-zinc-600 focus:outline-none focus:border-[#F5C518] transition-colors"
                        />
                        <p
                            v-if="form.errors.password"
                            class="text-red-400 text-xs mt-1"
                        >
                            {{ form.errors.password }}
                        </p>
                    </div>


                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] font-semibold text-sm py-2.5 rounded-lg transition-colors disabled:opacity-60 disabled:cursor-not-allowed mt-2"
                    >
                        {{ form.processing ? "Memproses..." : "Masuk" }}
                    </button>
                </form>
            </div>

            <p class="text-center text-zinc-600 text-xs mt-6">
                BMC Asset © {{ new Date().getFullYear() }}
            </p>
        </div>
    </div>
</template>
