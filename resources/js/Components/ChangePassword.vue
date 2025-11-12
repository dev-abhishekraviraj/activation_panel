<template>
<div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="staticModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="removeErrors();">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form @submit.prevent="changePassword();">
                <div class="modal-body">
                    <div class="form-group">
                        <input type="text" class="form-control password" placeholder="Password">
                        <span class="password_error text-danger"></span>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control confirm_password" placeholder="Confirm Password">
                        <span class="confirm_password_error text-danger"></span>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary change_cancel_btn" data-dismiss="modal" @click="removeErrors();">Cancel</button>
                    <button type="submit" class="btn btn-primary change_confirm_btn">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>
</template>

<script setup>
import axios from 'axios';
async function changePassword() {
    removeErrors();
    document.querySelector('.change_cancel_btn').setAttribute('disabled',true);
    document.querySelector('.change_confirm_btn').setAttribute('disabled',true);
    document.querySelector('.change_confirm_btn').innerHTML = 'Please wait...';
    let password = document.querySelector('.password').value;
    let password_confirmation = document.querySelector('.confirm_password').value;
    let r = await axios.post('/password/change', {
        password: password,
        password_confirmation: password_confirmation
    });

    if (r.data.errors) {
        document.querySelector('.change_cancel_btn').removeAttribute('disabled');
        document.querySelector('.change_confirm_btn').removeAttribute('disabled');
        document.querySelector('.change_confirm_btn').innerHTML = 'Confirm';

        if (r.data.message.password != undefined) {
            document.querySelector('.password').classList.add('is-invalid');
            document.querySelector('.password_error').textContent = r.data.message.password[0];
        }
        if (r.data.message.password_confirmation != undefined) {
            document.querySelector('.confirm_password').classList.add('is-invalid');
            document.querySelector('.confirm_password_error').textContent = r.data.message.password_confirmation[0];
        }
    } else {
        document.querySelector('.change_cancel_btn').removeAttribute('disabled');
        document.querySelector('.change_confirm_btn').removeAttribute('disabled');
        document.querySelector('.change_confirm_btn').innerHTML = 'Confirm';

        document.querySelector('.close').click();
        document.querySelector('.password').value = '';
        document.querySelector('.confirm_password').value = '';
        document.querySelector('#device_key_span').textContent = password;
        Swal.fire({
            icon: "success",
            title: r.data.message,
            showConfirmButton: false,
            timer: 1500
        });
    }
}

function removeErrors() {
    document.querySelector('.password').classList.remove('is-invalid');
    document.querySelector('.confirm_password').classList.remove('is-invalid');
    document.querySelector('.password_error').textContent = '';
    document.querySelector('.confirm_password_error').textContent = '';
}
</script>
