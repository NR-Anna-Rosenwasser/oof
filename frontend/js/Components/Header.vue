<script setup>
const propps = defineProps({
    title: String,
    description: String,
})
</script>
<template>
    <h1 class="text-4xl font-bold mb-8">{{ title }}</h1>
    <p class="text-lg">{{ description }}</p>
</template>
