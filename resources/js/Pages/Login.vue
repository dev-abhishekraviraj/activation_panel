<template>
<!-- Header -->
<div class="header">
    <div>
        <img :src="Logo" alt="Logo" />
    </div>
</div>
<!-- Main Section -->
<div class="container">
    <div class="login-content">
        <div class="login-logo">
            <a href="index.html">
                <!-- <img class="align-content" src="images/logo.png" alt=""> -->
            </a>
        </div>
        <div class="login-form">
            <div class="alert alert-success" v-if="$page.props.flash.success">
                <strong>Success!</strong>&nbsp{{ $page.props.flash.success }}
            </div>
            <div class="alert alert-danger" v-if="$page.props.flash.auth_error">
                <strong>Error!</strong>&nbsp{{ $page.props.flash.auth_error }}
            </div>
            <div v-if="$page.props.errors.auth_error" class="alert alert-danger"><strong>Error!</strong>&nbsp{{ $page.props.errors.auth_error }}</div>
            <form @submit.prevent="submit">
                <div class="form-group">
                    <label>Mac Address</label>
                    <input type="text" :class="['form-control',{'border-danger':$page.props.errors.mac_address}]" v-model="form.mac_address" placeholder="Mac Address">
                    <div v-if="$page.props.errors.mac_address" class="text-danger">{{ $page.props.errors.mac_address }}</div>
                </div>

                <div class="form-group">
                    <label>Device Key</label>
                    <input type="password" :class="['form-control',{'border-danger':$page.props.errors.password}]" v-model="form.password" placeholder="Device key">
                    <div v-if="$page.props.errors.password" class="text-danger">{{ $page.props.errors.password }}</div>
                </div>
                <button type="submit" class="btn btn-login text-white">ADD DEVICE</button>
            </form>
        </div>
    </div>
    <div class="main-footer">
        <div class="col-md-12">
            <div class="container-fluid pt-0 ht-100p text-center">
                While using this application I accept <a href="https://smarterspro.com/terms-conditions/" target="_blank"><b>terms and conditions.</b></a>
            </div>
        </div>
    </div>
</div>
</template>

<script setup>
import Logo from "../../../public/images/SmarterProLogo.png"
import {
    ref,
    reactive
} from 'vue'
import {
    useForm
} from '@inertiajs/vue3'

const form = useForm({
    mac_address: '',
    password: ''
})

function submit() {
    form.post('/', {
        onSuccess: () => {
            form.reset()
        },
        onError: (errors) => {
            console.error('Validation Errors:', errors)
        }
    })
}
</script>

<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #f1f2f6;
}

.header {
    background-color: #0d1a3c;
    color: white;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header h1 span {
    color: #5c6eff;
}

.contact-btn {
    background-color: #6c63ff;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
}

.main-section {
    background-color: #0d1a3c;
    color: white;
    padding: 3rem 2rem;
    position: relative;
}

.main-section h2 span {
    color: #5c6eff;
}

.main-section ul {
    list-style: none;
    padding-left: 1rem;
}

.main-section ul li::before {
    content: "★";
    color: #6c63ff;
    padding-right: 0.5rem;
}

.video-box {
    position: absolute;
    right: 2rem;
    top: 2rem;
    max-width: 400px;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
}

.form-section {
    background-color: white;
    margin: 3rem auto;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
    max-width: 800px;
}

.form-check-label {
    margin-left: 0.25rem;
    margin-right: 1rem;
}

.btn-login {
    background: #0d1a3c;
}
</style>
