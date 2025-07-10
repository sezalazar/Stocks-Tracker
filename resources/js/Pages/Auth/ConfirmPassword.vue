<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import { Button } from '@/Components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Lock } from 'lucide-vue-next'

const form = useForm({
    password: '',
})

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset()
        },
    })
}
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password" />

        <Card class="mx-auto max-w-sm bg-card border-border">
            <CardHeader>
                <CardTitle class="text-2xl text-primary">Confirm Password</CardTitle>
                <CardDescription class="text-muted-foreground pt-2">
                    This is a secure area of the application. Please confirm your password before continuing.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="password">Password</Label>
                        <div class="relative">
                            <Lock class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                id="password"
                                type="password"
                                class="pl-9"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                autofocus
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <Button type="submit" class="w-full" :disabled="form.processing">Confirm</Button>
                </form>
            </CardContent>
        </Card>
    </GuestLayout>
</template>
