<template>
    <div class="bg-white rounded-2xl shadow-md p-6 flex items-center space-x-8">
      <div class="relative w-80 h-60 overflow-visible">
        <svg viewBox="0 0 200 100" class="absolute inset-0 w-full h-full">
          <defs>
            <template v-for="(seg, i) in segmentsWithAngles" :key="i">
              <path
                :id="`segPath${i}`"
                :d="describeArc(0,0,R, seg.startAngle, seg.endAngle)"
                fill="none"
              />
            </template>
          </defs>
  
          <g transform="translate(100,100)">
            <template v-for="(seg, i) in segmentsWithAngles" :key="i">
              <path
                :d="describeArc(0,0,R, seg.startAngle, seg.endAngle)"
                :stroke="seg.color"
                :stroke-width="STROKE"
                fill="none"
              />
            </template>
  
            <template v-for="p of tickPercents" :key="p">
              <line
                :x1="tickPos(p).inner.x" :y1="tickPos(p).inner.y"
                :x2="tickPos(p).outer.x" :y2="tickPos(p).outer.y"
                stroke="#fff" stroke-width="2"
              />
              <text
                :x="tickPos(p).label.x" :y="tickPos(p).label.y"
                text-anchor="middle" dominant-baseline="middle"
                class="text-[8px] fill-gray-700"
              >{{ p }}</text>
            </template>
  
            <template v-for="(seg, i) in segmentsWithAngles" :key="i">
              <text
                text-anchor="middle"
                dominant-baseline="middle"
                class="fill-gray-700 text-[9px] font-medium"
              >
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
  
            <line
              x1="0" y1="0"
              :x2="needle.x" :y2="needle.y"
              stroke="#111827"
              stroke-width="2"
            />
            <circle cx="0" cy="0" r="4" fill="#111827" />
          </g>
        </svg>
  
        <div class="absolute inset-x-0 bottom-0 pb-2 text-center">
          <div class="text-3xl font-bold">{{ currentValue }}</div>
          <div class="text-sm text-gray-500">{{ currentText }}</div>
        </div>
      </div>
  
      <ul class="space-y-2 text-sm">
        <li>
          <span class="font-semibold">Previous close:</span>
          {{ data.fgi.previousClose.valueText }}
          <span
            class="ml-2 inline-block px-2 py-0.5 text-xs font-medium text-white rounded-full"
            :class="badgeClass(data.fgi.previousClose.value)"
          >{{ data.fgi.previousClose.value }}</span>
        </li>
        <li>
          <span class="font-semibold">1 week ago:</span>
          {{ data.fgi.oneWeekAgo.valueText }}
          <span
            class="ml-2 inline-block px-2 py-0.5 text-xs font-medium text-white rounded-full"
            :class="badgeClass(data.fgi.oneWeekAgo.value)"
          >{{ data.fgi.oneWeekAgo.value }}</span>
        </li>
        <li>
          <span class="font-semibold">1 month ago:</span>
          {{ data.fgi.oneMonthAgo.valueText }}
          <span
            class="ml-2 inline-block px-2 py-0.5 text-xs font-medium text-white rounded-full"
            :class="badgeClass(data.fgi.oneMonthAgo.value)"
          >{{ data.fgi.oneMonthAgo.value }}</span>
        </li>
        <li>
          <span class="font-semibold">1 year ago:</span>
          {{ data.fgi.oneYearAgo.valueText }}
          <span
            class="ml-2 inline-block px-2 py-0.5 text-xs font-medium text-white rounded-full"
            :class="badgeClass(data.fgi.oneYearAgo.value)"
          >{{ data.fgi.oneYearAgo.value }}</span>
        </li>
      </ul>
    </div>
  </template>
  
  <script setup lang="ts">
  import { computed } from 'vue'
  
  interface FgiResponse {
    fgi: {
      now:           { value: number; valueText: string }
      previousClose: { value: number; valueText: string }
      oneWeekAgo:    { value: number; valueText: string }
      oneMonthAgo:   { value: number; valueText: string }
      oneYearAgo:    { value: number; valueText: string }
    }
  }
  
  const props = defineProps<{ data: FgiResponse }>()
  
  const R = 90
  const STROKE = 24
  
  const currentValue = computed(() => props.data.fgi.now.value)
  const currentText  = computed(() => props.data.fgi.now.valueText)
  
  const segments = [
    { label: 'Extreme Fear',  color: '#DC2626', start:   0,  end: 20 },
    { label: 'Fear',          color: '#F97316', start:  20, end: 40 },
    { label: 'Neutral',       color: '#6B7280', start:  40, end: 60 },
    { label: 'Greed',         color: '#10B981', start:  60, end: 80 },
    { label: 'Extreme Greed', color: '#059669', start:  80, end:100 },
  ]
  
  // SVG helpers
  function pctToAngle(p: number): number {
    return -90 + (p / 100) * 180
  }
  function polarToCartesian(cx: number, cy: number, r: number, angleDeg: number) {
    const rad = (angleDeg - 90) * Math.PI / 180
    return { x: cx + r * Math.cos(rad), y: cy + r * Math.sin(rad) }
  }
  function describeArc(cx: number, cy: number, r: number, startAngle: number, endAngle: number): string {
    const s = polarToCartesian(cx, cy, r, startAngle)
    const e = polarToCartesian(cx, cy, r, endAngle)
    const largeArc = endAngle - startAngle <= 180 ? '0' : '1'
    return `M ${s.x} ${s.y} A ${r} ${r} 0 ${largeArc} 1 ${e.x} ${e.y}`
  }
  
  const segmentsWithAngles = computed(() =>
    segments.map(s => ({
      ...s,
      startAngle: pctToAngle(s.start),
      endAngle:   pctToAngle(s.end),
    }))
  )
  
  const needle = computed(() => polarToCartesian(0,0,R, pctToAngle(currentValue.value)))
  
  const tickPercents = [0,20,40,60,80,100]
  function tickPos(p: number) {
    const ang = pctToAngle(p)
    return {
      inner: polarToCartesian(0,0,R - STROKE/2 - 4, ang),
      outer: polarToCartesian(0,0,R + STROKE/2 + 4, ang),
      label: polarToCartesian(0,0,R + STROKE/2 + 12, ang),
    }
  }
  
  function badgeClass(val: number) {
    if (val <= 20) return 'bg-red-600'
    if (val <= 40) return 'bg-orange-500'
    if (val <= 60) return 'bg-gray-500'
    if (val <= 80) return 'bg-green-600'
    return 'bg-emerald-600'
  }
  
  const data = props.data
  </script>
  
  <style scoped>
  textPath {
    letter-spacing: 0.5px;
  }
  </style>
  