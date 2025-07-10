<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Checkbox } from '@/Components/ui/checkbox';
import { Mail, Lock } from 'lucide-vue-next';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <Card class="mx-auto max-w-sm bg-card border-border">
            <CardHeader>
                <CardTitle class="text-2xl text-primary">
                    Login
                </CardTitle>
                <CardDescription class="text-muted-foreground">
                    Enter your credentials to access your dashboard.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
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
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center">
                            <Label for="password">Password</Label>
                             <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="form-subtle-link"
                            >
                                Forgot your password?
                            </Link>
                        </div>
                        <div class="relative">
                            <Lock class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                id="password"
                                type="password"
                                class="pl-9"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                            />
                        </div>
                        <InputError :message="form.errors.password" />
                    </div>
                    
                    <div class="flex items-center space-x-2 pt-2">
                        <Checkbox id="remember" :checked="form.remember" @update:checked="val => form.remember = val" />
                        <Label for="remember" class="text-sm font-normal text-muted-foreground">Remember me</Label>
                    </div>

                    <Button type="submit" class="w-full" :disabled="form.processing">
                        Log in
                    </Button>
                </form>
            </CardContent>
        </Card>

        <div class="form-footer-prompt">
            <span class="prompt-text">
                Don't have an account?
            </span>
            <Link
                :href="route('register')"
                class="prompt-link"
            >
                Sign up
            </Link>
        </div>
    </GuestLayout>
</template>
