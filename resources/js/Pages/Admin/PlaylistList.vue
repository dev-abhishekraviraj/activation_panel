<template>
<AdminLayout>
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-2 mt-2 mx-1">
            <Link :href="route('admin-playlist-create')" class="btn btn-success">+&nbspAdd Playlist</Link>
        </div>
        <div class="card mb-10">
            <div class="card-body">
                <div class="alert alert-success" v-if="$page.props.flash.success">
                    <strong>Success!</strong>&nbsp{{ $page.props.flash.success }}
                </div>
                <table class="table table-hover">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th scope="col">Playlist</th>
                            <th scope="col">Type</th>
                            <th scope="col">Password Protected</th>
                            <th scope="col">Status</th>
                            <th scope="col">Created On</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="records.length === 0">
                            <tr>
                                <td colspan="7" class="text-center">No records found.</td>
                            </tr>
                        </template>
                        <template v-else>
                            <tr v-for="record in records" :key="record.id">
                                <td>{{ record.stream_line }}
                                    <span v-if="record.type == 'file'"><button class="btn btn-success btn-sm" @click="downloadFile(record.stream_line)" data-toggle="tooltip" title="Download"><i class="fa fa-download"></i></button></span>
                                    <span v-else><button class="btn btn-success btn-sm" @click="copyMessage(record.stream_line)" data-toggle="tooltip" title="Copy"><i class="fa fa-copy"></i></button></span>
                                </td>
                                <td>{{ record.type.toUpperCase() }}</td>
                                <td><span class="badge badge-success" v-if="record.is_protected == 1">Yes</span><span class="badge badge-danger" v-else>No</span></td>
                                <td v-if="record.status == 1">
                                    <span class="badge badge-success">Active</span>
                                </td>
                                <td v-else>
                                    <span class="badge badge-danger">Inactive</span>
                                </td>
                                <td>{{ record.created_at }}</td>
                                <td>
                                    <button class="btn btn-success" @click="(record.is_protected == 1)?openValidatePasswordModal(record.id,'edit'):router.visit(route('admin-playlist-edit', [record.id]))" data-toggle="tooltip" title="Edit"><i class="fa fa-edit"></i></button>
                                    <button class="btn btn-danger" @click="(record.is_protected == 1)?openValidatePasswordModal(record.id,'delete'):deleteRecord(record.id)" data-toggle="tooltip" title="Delete"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Validate Password Modal -->
    <button type="button" class="btn btn-secondary mb-1 validate_password_modal_open d-none" data-toggle="modal" data-target="#mediumModal"></button>
    <div class="modal fade" id="mediumModal" tabindex="-1" role="dialog" aria-labelledby="mediumModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <form @submit.prevent="validatePassword()">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="mediumModalLabel">This Playlist is Password Protected</h6>
                        <button type="button" class="close validate_password_modal_close" data-dismiss="modal" aria-label="Close" @click="removeErrors('validate_password_clean')">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="validate_password_errors"></div>
                    <div class="modal-body">
                        <div class="form-group">
                            <input type="text" class="form-control password_input" placeholder="Enter password" v-model="validatePasswordForm.password">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary validate_password_modal_close" data-dismiss="modal" @click="removeErrors('validate_password_clean')">Cancel</button>
                            <button type="submit" class="btn btn-primary">Confirm</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Validate Password Modal -->
    <div id="snackbar"></div>
</AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
    ref
} from 'vue'
import {
    router,
    useForm,
    Link
} from '@inertiajs/vue3'
import axios from 'axios'
import {
    route
} from 'ziggy-js'
defineProps({
    records: Array
})
const validatePasswordForm = useForm({
    id: '',
    password: '',
    action: ''
})

function openValidatePasswordModal(id, action) {
    validatePasswordForm.id = id;
    validatePasswordForm.action = action;
    document.querySelector('.validate_password_modal_open').click();
}

async function validatePassword() {

    if (validatePasswordForm.password.trim() == '') {
        if (!document.querySelector('.password_input').classList.contains('border-danger')) {
            document.querySelector('.password_input').classList.add('border-danger');
        }
        document.querySelector('.validate_password_errors').innerHTML = `<div class="text-danger mx-3">Password field is required.</div>`;
        return false
    }
    try {
        let response = await axios.post(route('admin-password-check'), {
            id: validatePasswordForm.id,
            password: validatePasswordForm.password
        })

        if (response.data.errors) {
            if (!document.querySelector('.password_input').classList.contains('border-danger')) {
                document.querySelector('.password_input').classList.add('border-danger');
            }
            document.querySelector('.validate_password_errors').innerHTML = `<div class="text-danger mx-3">${response.data.message}</div>`;
            return false;
        } else {
            if (document.querySelector('.password_input').classList.contains('border-danger')) {
                document.querySelector('.password_input').classList.remove('border-danger');
            }
            document.querySelector('.validate_password_errors').innerHTML = '';

            Swal.fire({
                position: "top-center",
                icon: "success",
                title: "Authenticated",
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                if (validatePasswordForm.action == 'edit') {
                    router.visit(route('admin-playlist-edit', [validatePasswordForm.id]))
                } else if (validatePasswordForm.action == 'delete') {
                    deleteRecord(validatePasswordForm.id)
                }
                document.querySelector('.validate_password_modal_close').click()
            });

        }
    } catch (error) {
        console.log(error.response.data)
    }

}

async function copyMessage(message) {

    try {
        let copied = ref(false);
        await navigator.clipboard.writeText(message);
        copied = true;
        var toast = document.getElementById("snackbar");
        toast.innerHTML = "Copied!"
        toast.className = "show";
        setTimeout(() => {
            copied = false;
            toast.className = toast.className.replace("show", "");
        }, 2000);
    } catch (e) {
        console.error('Copy failed', e);
    }
}

function downloadFile(fileName) {
    Swal.fire({
        title: "Are you sure you want to download?",
        text: "",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, download!"
    }).then((result) => {
        if (result.isConfirmed) {
            if (result.isConfirmed) {
               window.location.href = `/admin/file/download/${fileName}`
            }
        }
    });
}


function deleteRecord(id) {
    Swal.fire({
        title: "Are you sure want to delete?",
        text: "",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete!"
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('admin-playlist-delete'), {
                id: id
            })
        }
    });
}

function removeErrors(str) {
    if (str.trim() == 'validate_password_clean') {
        if (document.querySelector('.password_input').classList.contains('border-danger')) {
            document.querySelector('.password_input').classList.remove('border-danger');
        }
        document.querySelector('.validate_password_errors').innerHTML = '';
        validatePasswordForm.reset()
    }
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
