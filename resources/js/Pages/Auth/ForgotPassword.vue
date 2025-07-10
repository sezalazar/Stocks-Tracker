<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import { Button } from '@/Components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Mail } from 'lucide-vue-next'

defineProps({
    status: String,
})

const form = useForm({
    email: '',
})

const submit = () => {
    form.post(route('password.email'))
}
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <Card class="mx-auto max-w-sm bg-card border-border">
            <CardHeader>
                <CardTitle class="text-2xl text-primary">Forgot Password</CardTitle>
                <CardDescription class="text-muted-foreground pt-2">
                    No problem. Enter your email and we'll send you a reset link.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="status" class="mb-4 text-sm font-medium text-green-500">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <div class="relative">
                            <Mail class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                id="email"
                                type="email"
                                class="pl-9"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="name@example.com"
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <Button type="submit" class="w-full" :disabled="form.processing">Email Password Reset Link</Button>
                </form>
                <div class="mt-4 text-center text-sm">
                    <Link :href="route('login')" class="text-muted-foreground underline hover:text-primary">Back to login</Link>
                </div>
            </CardContent>
        </Card>
    </GuestLayout>
</template>
