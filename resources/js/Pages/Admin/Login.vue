<template>
<div class="sufee-login d-flex align-content-center flex-wrap">
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
                <form @submit.prevent="login">
                    <div class="form-group">
                        <label>Email address</label>
                        <input type="text" :class="['form-control',{'border-danger':$page.props.errors.email}]" v-model="form.email" placeholder="Email">
                        <div v-if="$page.props.errors.email" class="text-danger">{{ $page.props.errors.email }}</div>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" :class="['form-control',{'border-danger':$page.props.errors.password}]" v-model="form.password" placeholder="Password">
                         <i class="fa fa-eye" style="position:absolute;right:455px; top:191px;"></i>
                        <div v-if="$page.props.errors.password" class="text-danger">{{ $page.props.errors.password }}</div>
                    </div>
                    <!-- <div class="checkbox">
                        <label>
                            <input type="checkbox"> Remember Me
                        </label>
                        <label class="pull-right">
                            <a href="#">Forgotten Password?</a>
                        </label>

                    </div> -->
                    <button type="submit" class="btn btn-success btn-flat m-b-30 m-t-30">Sign in</button>
                </form>
            </div>
        </div>
    </div>
</div>
</template>

<script setup>
import {
    router,
    useForm
} from '@inertiajs/vue3';

let form = useForm({
    email: '',
    password: ''
})

function login() {
    form.post("admin")
}
</script>

<style scoped>
body{
    background-color:#343a40;
}
</style>
