<template>
    <div class="fgi-card">
        <div class="relative w-80 h-60 overflow-visible">
            <svg viewBox="0 0 200 100" class="absolute inset-0 w-full h-full">
                <defs>
                    <template v-for="(seg, i) in segmentsWithAngles" :key="i">
                        <path :id="`segPath${i}`" :d="describeArc(0, 0, R, seg.startAngle, seg.endAngle)" fill="none" />
                    </template>
                </defs>

                <g transform="translate(100,100)">
                    <template v-for="(seg, i) in segmentsWithAngles" :key="i">
                        <path
                            :d="describeArc(0, 0, R, seg.startAngle, seg.endAngle)"
                            :stroke="seg.color"
                            :stroke-width="STROKE"
                            fill="none"
                        />
                    </template>

                    <template v-for="p of tickPercents" :key="p">
                        <line
                            :x1="tickPos(p).inner.x"
                            :y1="tickPos(p).inner.y"
                            :x2="tickPos(p).outer.x"
                            :y2="tickPos(p).outer.y"
                            class="stroke-[hsl(var(--border))]"
                            stroke-width="2"
                        />
                        <text
                            :x="tickPos(p).label.x"
                            :y="tickPos(p).label.y"
                            text-anchor="middle"
                            dominant-baseline="middle"
                            class="fgi-gauge-tick-label"
                        >
                            {{ p }}
                        </text>
                    </template>

                    <template v-for="(seg, i) in segmentsWithAngles" :key="i">
                        <text text-anchor="middle" dominant-baseline="middle" class="fgi-gauge-segment-label">
                            <textPath :href="`#segPath${i}`" startOffset="50%">
                                <template v-if="seg.label.includes(' ')">
                                    <tspan x="0" dy="-6">{{ seg.label.split(' ')[0] }}</tspan>
                                    <tspan x="0" dy="8">{{ seg.label.split(' ')[1] }}</tspan>
                                </template>
                                <template v-else>
                                    {{ seg.label }}
                                </template>
                            </textPath>
                        </text>
                    </template>

                    <line x1="0" y1="0" :x2="needle.x" :y2="needle.y" class="fgi-gauge-needle" stroke-width="2" />
                    <circle cx="0" cy="0" r="4" class="fgi-gauge-needle-center" />
                </g>
            </svg>

            <div class="absolute inset-x-0 bottom-0 pb-2 text-center">
                <div class="fgi-gauge-value">{{ currentValue }}</div>
                <div class="fgi-gauge-text">{{ currentText }}</div>
            </div>
        </div>

        <ul class="fgi-list">
            <li class="fgi-list-item">
                <span class="fgi-list-item-value">Previous close:</span>
                {{ data.fgi.previousClose.valueText }}
                <span
                    class="ml-2 inline-block px-2 py-0.5 text-xs font-medium text-destructive-foreground rounded-full"
                    :class="badgeClass(data.fgi.previousClose.value)"
                >
                    {{ data.fgi.previousClose.value }}
                </span>
            </li>
            <li class="fgi-list-item">
                <span class="fgi-list-item-value">1 week ago:</span>
                {{ data.fgi.oneWeekAgo.valueText }}
                <span
                    class="ml-2 inline-block px-2 py-0.5 text-xs font-medium text-destructive-foreground rounded-full"
                    :class="badgeClass(data.fgi.oneWeekAgo.value)"
                >
                    {{ data.fgi.oneWeekAgo.value }}
                </span>
            </li>
            <li class="fgi-list-item">
                <span class="fgi-list-item-value">1 month ago:</span>
                {{ data.fgi.oneMonthAgo.valueText }}
                <span
                    class="ml-2 inline-block px-2 py-0.5 text-xs font-medium text-destructive-foreground rounded-full"
                    :class="badgeClass(data.fgi.oneMonthAgo.value)"
                >
                    {{ data.fgi.oneMonthAgo.value }}
                </span>
            </li>
            <li class="fgi-list-item">
                <span class="fgi-list-item-value">1 year ago:</span>
                {{ data.fgi.oneYearAgo.valueText }}
                <span
                    class="ml-2 inline-block px-2 py-0.5 text-xs font-medium text-destructive-foreground rounded-full"
                    :class="badgeClass(data.fgi.oneYearAgo.value)"
                >
                    {{ data.fgi.oneYearAgo.value }}
                </span>
            </li>
        </ul>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ data: Object })

const R = 90
const STROKE = 24

const currentValue = computed(() => props.data.fgi.now.value)
const currentText = computed(() => props.data.fgi.now.valueText)

const segments = [
    { label: 'Extreme Fear', color: 'hsl(var(--destructive))', start: 0, end: 20 },
    { label: 'Fear', color: '#F97316', start: 20, end: 40 },
    { label: 'Neutral', color: 'hsl(var(--muted))', start: 40, end: 60 },
    { label: 'Greed', color: 'hsl(var(--success))', start: 60, end: 80 },
    { label: 'Extreme Greed', color: '#059669', start: 80, end: 100 },
]

// SVG helpers
function pctToAngle(p) {
    return -90 + (p / 100) * 180
}
function polarToCartesian(cx, cy, r, angleDeg) {
    const rad = ((angleDeg - 90) * Math.PI) / 180
    return { x: cx + r * Math.cos(rad), y: cy + r * Math.sin(rad) }
}
function describeArc(cx, cy, r, startAngle, endAngle) {
    const s = polarToCartesian(cx, cy, r, startAngle)
    const e = polarToCartesian(cx, cy, r, endAngle)
    const largeArc = endAngle - startAngle <= 180 ? '0' : '1'
    return `M ${s.x} ${s.y} A ${r} ${r} 0 ${largeArc} 1 ${e.x} ${e.y}`
}

const segmentsWithAngles = computed(() =>
    segments.map((s) => ({
        ...s,
        startAngle: pctToAngle(s.start),
        endAngle: pctToAngle(s.end),
    }))
)

const needle = computed(() => polarToCartesian(0, 0, R, pctToAngle(currentValue.value)))

const tickPercents = [0, 20, 40, 60, 80, 100]
function tickPos(p) {
    const ang = pctToAngle(p)
    return {
        inner: polarToCartesian(0, 0, R - STROKE / 2 - 4, ang),
        outer: polarToCartesian(0, 0, R + STROKE / 2 + 4, ang),
        label: polarToCartesian(0, 0, R + STROKE / 2 + 12, ang),
    }
}

function badgeClass(val) {
    if (val <= 20) return 'bg-destructive'
    if (val <= 40) return 'bg-orange-500'
    if (val <= 60) return 'bg-muted'
    if (val <= 80) return 'bg-[hsl(var(--success))]'
    return 'bg-emerald-600'
}

const data = props.data
</script>

<style scoped>
textPath {
    letter-spacing: 0.5px;
}
</style>
