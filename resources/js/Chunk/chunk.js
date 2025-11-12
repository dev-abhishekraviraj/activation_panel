import axios from 'axios';
import { router } from '@inertiajs/vue3';

async function manageChunk(form,role) {

  let file = document.getElementById('m3u_file_input').files[0];
  let errors = false;
  document.querySelector('.error_messages').innerHTML = '';
  document.getElementById('playlist_name').style.borderColor = '';
  document.getElementById('mac_address').style.borderColor = '';
  document.getElementById('m3u_file_input').style.borderColor = '';
  if(form.is_protected){
    document.getElementById('password').style.borderColor = '';
    document.getElementById('password_confirmation').style.borderColor = '';
  }
  let errorMessages = `<div class="alert alert-danger">`;
  if(form.playlist_name.trim() == ''){
    document.getElementById('playlist_name').style.borderColor = 'red';
    errorMessages += 'Playlist name field is required.<br>';
    errors = true;
  }
  if(role == 'admin'){
    if(form.mac_id == ''){
      document.getElementById('mac_address').style.borderColor = 'red';
      errorMessages += 'Mac address field is required.<br>';
      errors = true;
    }
  }
  if(!file){
    document.getElementById('m3u_file_input').style.borderColor = 'red';
    errorMessages += 'Please choose a file or enter a valid url.<br>';
    errors = true;
  }
  if(file && file.size == 0){
     document.getElementById('m3u_file_input').style.borderColor = 'red';
     errorMessages += 'File size is zero.<br>';
     errors = true;
  }

  if(form.is_protected){
    let password = form.password;
    let password_confirmation = form.password_confirmation;
    if(form.id == ''){
        if(password == ''){
          document.getElementById('password').style.borderColor = 'red';
          errorMessages += 'Password field is required.<br>';
          errors = true;
        }
        if(password_confirmation == ''){
          document.getElementById('password_confirmation').style.borderColor = 'red';
          errorMessages += 'Password confirmation field is required.<br>';
          errors = true;
        }
        if(password != '' && password_confirmation != ''){
          document.getElementById('password').style.borderColor = 'red';
          document.getElementById('password_confirmation').style.borderColor = 'red';
          errorMessages += 'Password and password confirmation field is must be same.<br>';
          errors = true;
        }
    }
    else{
        if(!form.password_exists){
            if(password == ''){
              document.getElementById('password').style.borderColor = 'red';
              errorMessages += 'Password field is required.<br>';
              errors = true;
            }
            if(password_confirmation == ''){
              document.getElementById('password_confirmation').style.borderColor = 'red';
              errorMessages += 'Password confirmation field is required.<br>';
              errors = true;
            }
            if(password != '' && password_confirmation != ''){
               if(password != password_confirmation){
                  document.getElementById('password').style.borderColor = 'red';
                  document.getElementById('password_confirmation').style.borderColor = 'red';
                  errorMessages += 'Password and confirm password field must be same.<br>';
                  errors = true;
               }
            }
        }
    }
  }
  
  if(errors){
    errorMessages += `</div>`;
    document.querySelector('.error_messages').innerHTML = errorMessages;
    return false;
  }
  
  const chunkSize = 1024 * 1024; 
  const totalChunks = Math.ceil(file.size / chunkSize);
  document.getElementById('payment-button').style.display = 'none';
  document.getElementById('progress-container').style.display = 'block';
  const progressBar = document.getElementById('progress-bar');
  document.querySelector('.m3u_file_processing_message').innerText = `Please don't refresh the page upload is in progress...`;

  let upload_url = (role == 'admin')?'/admin/file/upload':'/file/upload';
  for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
        const start = chunkIndex * chunkSize;
        const end = Math.min(start + chunkSize, file.size);
        const chunk = file.slice(start, end);

        const formData = new FormData();
        formData.append("chunk", chunk);
        formData.append("fileName", file.name);
        formData.append("chunkIndex", chunkIndex);
        formData.append("totalChunks", totalChunks);
        if(chunkIndex == (totalChunks-1)){
            if(form.id != ''){
                formData.append('id',form.id);
            }
            formData.append("playlist_name", form.playlist_name);
            if(role == 'admin'){
              formData.append("mac_id", form.mac_id);
            }
            formData.append('xmltv_url',form.xmltv_url);
            formData.append('epg_country',form.epg_countries);
            formData.append('logo',form.logos);
            formData.append("status",form.status);
            if(form.is_protected){
              formData.append("is_protected", 1);
              formData.append("password", form.password);
            }
        }

        try{
            let r = await axios.post(upload_url, formData, {

            });
            
            if(r.data.errors){
              if(r.data.message.chunk != undefined){
                  console.log(r.data.message)
                  r.data.message=r.data.message.chunk[0];
                  document.getElementById('m3u_file_input').style.borderColor = 'red';
              }
              progressBar.style.width = 0 + '%';
              document.getElementById('progress-container').style.display = 'none';
              document.querySelector('.error_messages').innerHTML = `<div class="alert alert-danger">${r.data.message}</div>`;
              document.getElementById('payment-button').style.display = 'block';
              document.querySelector('.m3u_file_processing_message').innerText = '';
              return false;
            }
    
            //Update progress
            const percent = Math.round(((chunkIndex + 1) / totalChunks) * 100);
            progressBar.style.width = percent + '%';
            progressBar.textContent = percent + '%';  
        }catch(e){
            progressBar.style.width = 0 + '%';
            document.getElementById('progress-container').style.display = 'none';
            document.querySelector('.error_messages').innerHTML = `<div class="alert alert-danger">${e.message}</div>`;
            document.getElementById('payment-button').style.display = 'block';
            document.querySelector('.m3u_file_processing_message').innerText = '';
            return false;
        }

  }

  let msg = (form.id == '')?'saved':'updated';
  progressBar.style.width = 0 + '%';
  document.getElementById('progress-container').style.display = 'none';
  document.querySelector('.m3u_file_processing_message').innerText = '';
  let t = 3;
  document.querySelector('.m3u_file_success_message').innerHTML = `<div class="alert alert-success">Playlist ${msg} successfully now redirecting to listing page in <span id="r_time">${t}</span> seconds.</div>`;
  setInterval(()=>{
    if(t >= 1){
      document.querySelector('#r_time').innerHTML = `${t}`;
      t--;
    }
         
  },1000);
  
  let redirect_url = (role == 'admin')?'/admin/playlist/list':'/playlist/list';
  setTimeout(()=>{
    router.visit(redirect_url);
  },3000);
  
}

export default manageChunk;