<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/register';

defineOptions({
    layout: {
        title: 'Daftar Toko Baru',
        description: 'Buat akun toko sendiri dan mulai jualan dalam hitungan menit.',
    },
});
</script>

<template>
    <Head title="Daftar Toko Baru" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="space-y-5"
    >
        <div class="grid gap-5">
            <div class="grid gap-2">
                <Label for="nama_toko">Nama toko</Label>
                <Input
                    id="nama_toko"
                    type="text"
                    name="nama_toko"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="organization"
                    placeholder="Mis. Warung Berkah Jaya"
                />
                <InputError :message="errors.nama_toko" />
            </div>

            <div class="grid gap-2">
                <Label for="nama_pemilik">Nama Anda</Label>
                <Input
                    id="nama_pemilik"
                    type="text"
                    name="nama_pemilik"
                    required
                    :tabindex="2"
                    autocomplete="name"
                    placeholder="Nama pemilik toko"
                />
                <InputError :message="errors.nama_pemilik" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    :tabindex="3"
                    autocomplete="email"
                    placeholder="email@contoh.com"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="whatsapp">Nomor WhatsApp toko</Label>
                <Input
                    id="whatsapp"
                    type="tel"
                    name="whatsapp"
                    required
                    :tabindex="4"
                    autocomplete="tel"
                    placeholder="08xxxxxxxxxx"
                />
                <InputError :message="errors.whatsapp" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Kata sandi</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="5"
                    autocomplete="new-password"
                    placeholder="Buat kata sandi"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Konfirmasi kata sandi</Label>
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    :tabindex="6"
                    autocomplete="new-password"
                    placeholder="Ulangi kata sandi"
                />
                <InputError :message="errors.password_confirmation" />
            </div>
        </div>

        <Button
            type="submit"
            class="w-full"
            :tabindex="7"
            :disabled="processing"
            data-test="register-button"
        >
            <span class="inline-flex items-center justify-center gap-2">
                <Spinner v-if="processing" />
                {{ processing ? 'Membuat toko...' : 'Daftar & Mulai Jualan' }}
            </span>
        </Button>
    </Form>
</template>
