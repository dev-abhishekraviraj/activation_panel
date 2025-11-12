<template>
<ClientLayout>
    <div class="col-md-6">
        <Link :href="route('client-playlist-register')" class="btn btn-primary add_playlist px-3 py-2">
        + Add Playlist
        </Link>
    </div>
    <div class="col-md-6">
        <table class="table table-borderless">
            <thead>
                <th>NAME</th>
                <th>MAC ADDRESS</th>
                <th>DEVICE KEY</th>
            </thead>
            <tbody>
                <tr>
                    <td>
                        {{ JSON.parse(devicedetails.device_info).name?JSON.parse(devicedetails.device_info).name.toUpperCase():'NA' }}
                    </td>
                    <td>
                        {{ devicedetails.mac_address }}
                    </td>
                    <td>
                        <span id="device_key_span">{{ devicedetails.device_key }}</span>&nbsp&nbsp<a data-toggle="modal" data-target="#changePasswordModal"><i class="fa fa-edit"></i></a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- </div> -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="alert alert-success" v-if="$page.props.flash.success">
                    <strong>Success!</strong> {{ $page.props.flash.success }}
                </div>

                <div class="table table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="tableBackground text-white">
                            <tr>
                                <th>Playlist</th>
                                <th>Type</th>
                                <th>Protected</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="records.length === 0">
                                <tr>
                                    <td colspan="6" class="text-center py-4">No records found.</td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr v-for="record in records" :key="record.id">
                                    <td class="text-break">
                                        {{ record.stream_line }}
                                        <span v-if="record.type === 'file'">
                                            <button class="btn download btn-sm ms-2" @click="downloadFile(record.stream_line)" title="Download">
                                                <i class="fa fa-download"></i>
                                            </button>
                                        </span>
                                        <span v-else-if="record.type === 'url'">
                                            <button class="btn copy btn-sm ms-2" @click="copyMessage(record.stream_line)" title="Copy">
                                                <i class="fa fa-copy"></i>
                                            </button>
                                            <span v-if="copied && record.id == idToBeCopied">Copied!</span>
                                        </span>
                                    </td>
                                    <td>{{ record.type.toUpperCase() }}</td>
                                    <td>
                                        <span :class="['badge text-white', record.is_protected ? 'bg-success' : 'bg-danger']">
                                            {{ record.is_protected ? 'Yes':'No' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span :class="['badge text-white', record.status ? 'bg-success' : 'bg-danger']">
                                            {{ record.status ? 'Active':'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ record.created_at }}</td>
                                    <div class="two_btn align-items-center d-flex justify-content-center p-2 " v-if="record.is_protected == 1">
                                        <a data-toggle="modal" data-target="#validatePasswordModal" @click="getId(record.id,'edit')"><i class="fa fa-edit"></i></a> 
                                        &nbsp<a data-toggle="modal" data-target="#validatePasswordModal"  @click="getId(record.id,'delete')"><i class="fa fa-trash"></i></a>                                     
                                    </div>
                                    <div class="two_btn align-items-center d-flex justify-content-center p-2 " v-else>
                                        <Link :href="route('client-playlist-edit',record.id)"><i class="fa fa-edit"></i></Link>
                                        &nbsp<a @click="deleteRecord(record.id);"><i class="fa fa-trash"></i></a>                                     
                                    </div>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
   
    <!-- Change Password Modal -->
    <ChangePassword></ChangePassword>
    <!-- Change Password Modal -->
    <!-- Validate Password Modal -->
    <ValidatePassword></ValidatePassword>
    <!-- Validate Password Modal -->
    <div id="snackbar"></div>
</ClientLayout>
</template>

<script setup>
import axios from 'axios';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import ChangePassword from '@/Components/ChangePassword.vue';
import ValidatePassword from '@/Components/ValidatePassword.vue';

import {
    onMounted,
    ref
} from 'vue';
import {
    router,
    useForm,
    Link
} from '@inertiajs/vue3';
import {
    route
} from 'ziggy-js';
const props = defineProps({
    records: Array,
    devicedetails: Array
});

async function copyMessage(message) {
    try {
        let copied = false;
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
        title: "Are you sure want to download?",
        text: "",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, download!"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `/file/download/${fileName}`
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
            router.post(route('client-playlist-delete'), {
                id: id
            })
        }
    });
}

function getId(id,action){
   document.querySelector('#record_id').value = id;
   document.querySelector('#record_action').value = action;
}

onMounted(async () => {
    try {
            const response = await axios.post('/login_qr', {
        })
        
    } catch (error) {
        console.error('Error posting data:', error)
    }
});
</script>

<style scoped>
.btn-sm {
    padding: 0.35rem 0.6rem;
    font-size: 0.8rem;
}

.btn {
    border-radius: 10px;
}

.btn-primary {
    background: #002153;
}

.fa fa-edit {
    color: white !important;
}

.download {
    background-color: white;
    color: #002153;
}

.edit {
    background-color: white;
}

.fa-edit {
    color: #002153 !important;
}

.delete {
    background-color: white;
    color: red;
}

.copy {
    background-color: white;
    color: #002153;
}

.box_details {
    border-radius: 0.5rem !important;
}

.badge {
    font-size: 0.75rem;
    padding: 0.35em 0.6em;
    border-radius: 0.4rem;
}

.tableBackground {
    background-color: #002153;
}

.add_playlist {
    margin-top: 50px;
}
</style>
