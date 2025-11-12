<template>
<AdminLayout>
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-2 mx-4">
            <label class="switch switch-3d switch-primary mr-3">
                <input type="checkbox" class="switch-input" @change="manage_status(status)" :checked="status === 1"> 
                <span class="switch-label"></span>
                <span class="switch-handle"></span>
            </label>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="alert alert-success" v-if="$page.props.flash.success">
                    <strong>Success!</strong>&nbsp{{ $page.props.flash.success }}
                </div>
                <table class="table table-hover">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th scope="col">Id</th>
                            <th scope="col">Action</th>
                            <th scope="col">Request Data</th>
                            <th scope="col">Response Data</th>
                            <th scope="col">Created By</th>
                            <th scope="col">Created On</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="status === 1">
                            <template v-if="records.length === 0">
                                <tr>
                                    <td colspan="7" class="text-center">No records found.</td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr v-for="(record) in records" :key="record.id">
                                    <td>{{ record.id }}</td>
                                    <td>{{ record.action }}</td>
                                    <td><textarea name="" id="" cols="30" rows="50">{{ record.request_data }}</textarea></td>
                                    <td>{{ record.response_data }}</td>
                                    <td><span v-if="record.role ==0">You({{ record.by }})</span><span v-else>Client({{ record.by }})</span></td>
                                    <td>{{ record.created_at }}</td>
                                    <td>
                                        <button class="btn btn-danger" @click="deleteRecord(record.id)"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                            </template>
                        </template>
                        <template v-else>
                            <tr>
                                <td colspan="7" class="text-center">Logs are disabled.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
    route
} from 'ziggy-js'

import {
    router,
    Link
} from '@inertiajs/vue3'


defineProps({
    records: Array,
    status: Number
})

function deleteRecord(id) {
    Swal.fire({
        title: "Are you sure want to delete this log?",
        text: "",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete!"
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('admin-log-delete'), {
                id: id
            })
        }
    });
}

function manage_status(status) {
    let message = (status == 1) ? 'disable' : 'enable'
    Swal.fire({
        title: "Are you sure want to " + message + " the logs?",
        text: "",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, " + message + "!"
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('admin-log-status'), {
                status: status
            })
        }else{
           (status == 1)?document.querySelector('.switch-input').checked = true:document.querySelector('.switch-input').checked = false;
        }
    });
}
</script>

<style>
row {
    color: #fff;
}
textarea{
    height:200px;
    width:300px;
}
</style>
