<template>
<div class="modal fade" id="validatePasswordModal" tabindex="-1" role="dialog" aria-labelledby="staticModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <b>This playlist is password protected.</b>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="removeErrors();">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form @submit.prevent="validatePassword();">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="">Enter password</label>
                        <input type="text" class="form-control password_to_be_validated" placeholder="Password">
                        <input type="hidden" id="record_id">
                        <input type="hidden" id="record_action">
                        <span class="password_to_be_validated_error text-danger"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary validate_cancel_btn" data-dismiss="modal" @click="removeErrors();">Cancel</button>
                    <button type="submit" class="btn btn-primary validate_confirm_btn">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>
</template>

<script setup>

import axios from 'axios';
import {
    router,
} from '@inertiajs/vue3';

async function validatePassword() {
    removeErrors();
    let password = document.querySelector('.password_to_be_validated').value;
    let id = document.querySelector('#record_id').value;
    let action = document.querySelector('#record_action').value.trim();
    if(password.trim() == ''){
        document.querySelector('.password_to_be_validated').classList.add('is-invalid');
        document.querySelector('.password_to_be_validated_error').textContent = 'Password field is required.';
        return false;
    }

    document.querySelector('.validate_cancel_btn').setAttribute('disabled',true);
    document.querySelector('.validate_confirm_btn').setAttribute('disabled',true);
    document.querySelector('.validate_confirm_btn').innerHTML = 'Please wait...';

    let r = await axios.post('/password/validate', {
        id: id,
        password: password,
    });
    console.log(r);
    if (r.data.errors) {
        document.querySelector('.validate_cancel_btn').removeAttribute('disabled');
        document.querySelector('.validate_confirm_btn').removeAttribute('disabled');
        document.querySelector('.validate_confirm_btn').innerHTML = 'Confirm';
        if (r.data.message != undefined) {
            document.querySelector('.password_to_be_validated').classList.add('is-invalid');
            document.querySelector('.password_to_be_validated_error').textContent = r.data.message;
        }
    } else {
        document.querySelector('.validate_cancel_btn').removeAttribute('disabled');
        document.querySelector('.validate_confirm_btn').removeAttribute('disabled');
        document.querySelector('.validate_confirm_btn').innerHTML = 'Confirm';
        
        jQuery('#validatePasswordModal').modal('hide');
        document.querySelector('.password_to_be_validated').value = '';
        Swal.fire({
            icon: "success",
            title: r.data.message,
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            if (action == 'edit') {
                 router.visit(route('client-playlist-edit',id));

            } else {
                deleteRecord(id);
            }
        });
    }
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

function removeErrors() {
    document.querySelector('.password_to_be_validated').classList.remove('is-invalid');
    document.querySelector('.password_to_be_validated_error').textContent = '';
}
</script>
