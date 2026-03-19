<script setup>
import Base from '../../Layouts/Base.vue';
import Header from '../../Components/Header.vue';
import { useForm, Form } from '@inertiajs/vue3';
import { ref } from 'vue';

const { step = 1} = defineProps({
    errors: Object,
    step: Number,
    filePath: String,
    headers: Array,
    tempUrl: String,
    expiresAt: String
})

const step1 = useForm({
    file: null,
    step: 1,
});

const step2 = useForm({
    filePath: "",
    mapping: {},
    note: "",
    step: 2,
});

const uploadFile = () => {
    step1.post('/contact-creator', {
        wantsJson: true,
        onSuccess: (data) => {
            step2.filePath = data.props.filePath
        }
    });
};

const submitMapping = () => {
    step2.post('/contact-creator', {
        wantsJson: true,
        onSuccess: (data) => {
            console.log(data);
        }
    });
};
</script>
<template>
    <Base>
        <Header title="Contact Creator" description="Convert csv to vcard files" />
        <Form class="oof-form" v-if="step === 1" @submit.prevent="uploadFile">
            <div class="mb-4 oof-form--group">
                <label for="file">CSV File</label>
                <input type="file" id="file" name="file" @change="e => step1.file = e.target.files[0]" accept="text/csv" required>
                <div v-if="errors.file" class="text-red-500">{{ errors.file }}</div>
            </div>
            <button type="submit">Upload</button>
        </Form>
        <Form class="oof-form" v-else-if="step === 2" @submit.prevent="submitMapping">
            <div class="mb-4 oof-form--group">
                <p class="text-red-700" v-if="errors.filePath">{{ errors.filePath }}</p>
                <p class="text-red-700" v-if="errors.mapping">{{ errors.mapping }}</p>
                <label for="mapping">Column Mapping</label>
                <div v-for="header in headers" :key="header" class="mb-2 flex items-center space-x-4">
                    <span class="flex-1">{{ header }}</span>
                    <select class="flex-1" :value="header" @change="e => step2.mapping[header] = e.target.value">
                        <option value="ignore">Ignore</option>
                        <option value="fname">First Name</option>
                        <option value="lname">Last Name</option>
                        <option value="email">Email</option>
                        <option value="phone">Phone</option>
                        <option value="pronouns">Pronouns</option>
                    </select>
                </div>
            </div>
            <button type="submit">Generate Contacts</button>
        </Form>
        <div v-if="tempUrl" class="mt-6">
            <a :href="tempUrl" target="_blank" class="text-blue-500 underline">Download Result</a>
            <p class="text-sm text-gray-500">This link will expire at {{ expiresAt }}</p>
        </div>
    </Base>
</template>
