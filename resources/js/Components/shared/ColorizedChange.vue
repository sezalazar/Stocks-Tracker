<script setup>
import { computed } from 'vue'

const props = defineProps({
    value: {
        type: Number,
        default: null,
    },
    isPercentage: {
        type: Boolean,
        default: false,
    },
})

const textColorClass = computed(() => {
    if (props.value === null || props.value === undefined) return ''
    if (props.value > 0) return 'text-[hsl(var(--success))]'
    if (props.value < 0) return 'text-destructive'
    return ''
})

const formattedValue = computed(() => {
    if (props.value === null || props.value === undefined) return '-'
    const sign = props.value > 0 ? '+' : ''
    const number = props.value.toFixed(2)
    const suffix = props.isPercentage ? '%' : ''
    return `${sign}${number}${suffix}`
})
</script>

<template>
    <span :class="textColorClass">{{ formattedValue }}</span>
</template>
