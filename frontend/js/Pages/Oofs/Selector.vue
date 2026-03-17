<script setup>
import Base from '../../Layouts/Base.vue';
import { useForm, Form } from '@inertiajs/vue3';

const form = useForm({
    file: null,
    numberOfRows: 30,
});

defineProps({
    errors: Object,
    downloadUrl: String,
    expiresAt: String,
})

const submitForm = () => {
    form.post('/selector', {
        wantsJson: true,
        onSuccess: (data) => {
            console.log(data);
        }
    });
};

</script>
<template>
    <Base>
        <h1 class="text-4xl font-bold mb-8">Randomized Selector</h1>
        <p class="text-lg">Select a random number of rows from your dataset.</p>
        <Form @submit.prevent="submitForm" class="oof-form">
            <div class="mb-4 oof-form--group">
                <label for="file">CSV File</label>
                <input type="file" id="file" name="file" @change="e => form.file = e.target.files[0]" accept="text/csv" required>
                <div v-if="errors.file" class="text-red-500">{{ errors.file }}</div>
            </div>
            <div class="mb-4 oof-form--group">
                <label for="numberOfRows">Number of Rows</label>
                <input type="number" id="numberOfRows" name="numberOfRows" v-model="form.numberOfRows" required>
                <div v-if="errors.numberOfRows" class="text-red-500">{{ errors.numberOfRows }}</div>
            </div>
            <button type="submit">Generate</button>
        </Form>

        <div v-if="downloadUrl" class="mt-6">
            <a :href="downloadUrl" target="_blank" class="text-blue-500 underline">Download Result</a>
            <p class="text-sm text-gray-500">This link will expire at {{ expiresAt }}</p>
        </div>
    </Base>
</template>
