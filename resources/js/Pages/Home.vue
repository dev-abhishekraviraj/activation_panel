<template>
<ClientLayout>
    <div class="Heading m-auto mt-4">
        <p>Upload playlist Files or external playlist URLs with auto-update</p>
    </div>
    <div class="col-md-6">
        <Link :href="route('client-playlist-list')" class="btn btn-primary list_playlist mx-3 text-white px-3 py-2">List Playlists</Link>
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
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="error_messages"></div>
                <form @submit.prevent="submit">
                    <!-- Playlist Name -->
                    <div class="form-group">
                        <label>PLAYLIST NAME:</label>
                        <input id="playlist_name" type="text" :class="['form-control', { 'border-danger': form.errors.playlist_name }]" placeholder="Playlist Name" v-model="form.playlist_name" />
                    </div>
                    <!-- Playlist Type -->
                    <div class="form-group">
                        <label>PLAYLIST TYPE:</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input type="radio" class="form-check-input" id="m3u_file" name="playlist_type" value="file" v-model="form.type" />
                                <label class="form-check-label" for="m3u_file">M3U FILE</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" class="form-check-input" id="m3u_url" name="playlist_type" value="url" v-model="form.type" />
                                <label class="form-check-label" for="m3u_url">M3U FILE URL</label>
                            </div>
                        </div>
                    </div>
                    <!-- File Input -->
                    <div class="form-group" v-if="form.type === 'file'">
                        <label>FILE:</label>
                        <div class="d-flex align-items-center gap-2">
                            <input ref="fileInput" id="m3u_file_input" type="file" @change="form.m3u_file = $event.target.files[0]" :class="['form-control',{ 'border-danger': form.errors.m3u_file }]"  accept=".m3u"/>
                        </div>
                    </div>
                    <!-- URL Input -->
                    <div class="form-group" v-if="form.type === 'url'">
                        <label>URL:</label>
                        <div class="d-flex align-items-center gap-2">
                            <input id="m3u_url_input" type="text" placeholder="M3U OR TXT playlist" v-model="form.m3u_url" :class="['form-control',{ 'border-danger': form.errors.m3u_url }]" />
                        </div>
                    </div>
                    <!-- EPG Inputs -->
                    <!-- <div class="form-group">
                        <label for="xmltv_url">EPG:</label>
                        <input id="xmltv_url" type="text" class="form-control" placeholder="XMLTV EPG URL" v-model="form.xmltv_url" />
                    </div>
                    <div class="form-group ">
                        <label>EPG COUNTRIES:</label>
                        <select class="form-control" id="epg_country" v-model="form.epg_countries">
                            <option value="">SELECT EPG COUNTRY</option>
                            <option v-for="(name, code) in countries" :value="code" :key="code">
                                {{ name }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group ">
                        <label for="">LOGOS:</label>
                        <select class="form-control" id="logo" v-model="form.logos">
                            <option value="">SELECT LOGO</option>
                            <option v-for="(name, code) in logos" :value="code" :key="code">
                                {{ name }}
                            </option>
                        </select>
                    </div> -->
                    <div class="form-group ">
                        <label>STATUS:</label>
                        <select name="" id="status" :class="['form-control', { 'border-danger': form.errors.status }]" v-model="form.status">
                            <option value="">SELECT STATUS</option>
                            <option v-for="(title, value) in select_status" :key="value" :value="value">
                                {{ title }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group d-flex gap-4 flex-wrap align-items-center">
                        <div class="form-check ms-auto">
                            <input type="checkbox" id="is_protected" class="form-check-input" v-model="form.is_protected" @click="clearInput($event)" />
                            <label for="is_protected" class="form-check-label">Password Protected</label>
                            <div class="container">
                                <div class="row ">
                                    <p class="mt-2 mb-0 text-body-secondary small">
                                        Protected playlists will not be viewed or modified without entering a
                                        password.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Password Fields if Protected -->
                    <div class="row" v-if="form.is_protected">
                        <div class="col-md-6">
                            <div class="form-group mt-3">
                                <label>PASSWORD:</label>
                                <input id="password" type="password" :class="['form-control',{ 'border-danger': form.errors.password }]" placeholder="Enter password" v-model="form.password" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mt-3">
                                <label>CONFIRM PASSWORD:</label>
                                <input id="password_confirmation" type="password" :class="['form-control',{ 'border-danger': form.errors.password }]" placeholder="Re-enter password" v-model="form.password_confirmation" />
                            </div>
                        </div>
                    </div>
                    <button id="payment-button" type="submit" class="btn btn-primary mt-4 py-2">
                        <span id="payment-button-amount">Save</span>
                    </button>
                    <div id="progress-container">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" id="progress-bar" style="width:0%"></div>
                    </div>
                    <div class="m3u_file_processing_message mt-4"></div>
                    <div class="m3u_file_success_message"></div>
                </form>
            </div>
        </div>
    </div>
    <!-- Change Password Modal -->
    <ChangePassword></ChangePassword>
    <!-- Change Password Modal -->
</ClientLayout>
</template>

<script setup>

import ClientLayout from '@/Layouts/ClientLayout.vue';
import ChangePassword from '@/Components/ChangePassword.vue';
import manageChunk from '@/Chunk/chunk.js';
import {
    useForm,
    Link
} from '@inertiajs/vue3';
import {
    route
} from 'ziggy-js';

let props = defineProps({
    playlist_count: Number,
    devicedetails: Array
});

const form = useForm({
    id:'',
    playlist_name: '',
    type: 'file',
    m3u_file: '',
    m3u_url: '',
    xmltv_url: '',
    epg_countries: '',
    logos: '',
    save_online: '',
    detect_epg: '',
    disable_groups: '',
    is_protected:false,
    status: '',
    password: '',
    password_confirmation: '',
    hidden_password:''
})

const countries = {
    "AF": "Afghanistan",
    "AL": "Albania",
    "DZ": "Algeria",
    "AS": "American Samoa",
    "AD": "Andorra",
    "AO": "Angola",
    "AI": "Anguilla",
    "AQ": "Antarctica",
    "AG": "Antigua and Barbuda",
    "AR": "Argentina",
    "AM": "Armenia",
    "AW": "Aruba",
    "AU": "Australia",
    "AT": "Austria",
    "AZ": "Azerbaijan",
    "BS": "Bahamas",
    "BH": "Bahrain",
    "BD": "Bangladesh",
    "BB": "Barbados",
    "BY": "Belarus",
    "BE": "Belgium",
    "BZ": "Belize",
    "BJ": "Benin",
    "BM": "Bermuda",
    "BT": "Bhutan",
    "BO": "Bolivia",
    "BA": "Bosnia and Herzegovina",
    "BW": "Botswana",
    "BV": "Bouvet Island",
    "BR": "Brazil",
    "IO": "British Indian Ocean Territory",
    "BN": "Brunei Darussalam",
    "BG": "Bulgaria",
    "BF": "Burkina Faso",
    "BI": "Burundi",
    "KH": "Cambodia",
    "CM": "Cameroon",
    "CA": "Canada",
    "CV": "Cape Verde",
    "KY": "Cayman Islands",
    "CF": "Central African Republic",
    "TD": "Chad",
    "CL": "Chile",
    "CN": "China",
    "CX": "Christmas Island",
    "CC": "Cocos (Keeling) Islands",
    "CO": "Colombia",
    "KM": "Comoros",
    "CG": "Congo",
    "CD": "Congo, the Democratic Republic of the",
    "CK": "Cook Islands",
    "CR": "Costa Rica",
    "CI": "Cote D'Ivoire",
    "HR": "Croatia",
    "CU": "Cuba",
    "CY": "Cyprus",
    "CZ": "Czech Republic",
    "DK": "Denmark",
    "DJ": "Djibouti",
    "DM": "Dominica",
    "DO": "Dominican Republic",
    "EC": "Ecuador",
    "EG": "Egypt",
    "SV": "El Salvador",
    "GQ": "Equatorial Guinea",
    "ER": "Eritrea",
    "EE": "Estonia",
    "ET": "Ethiopia",
    "FK": "Falkland Islands (Malvinas)",
    "FO": "Faroe Islands",
    "FJ": "Fiji",
    "FI": "Finland",
    "FR": "France",
    "GF": "French Guiana",
    "PF": "French Polynesia",
    "TF": "French Southern Territories",
    "GA": "Gabon",
    "GM": "Gambia",
    "GE": "Georgia",
    "DE": "Germany",
    "GH": "Ghana",
    "GI": "Gibraltar",
    "GR": "Greece",
    "GL": "Greenland",
    "GD": "Grenada",
    "GP": "Guadeloupe",
    "GU": "Guam",
    "GT": "Guatemala",
    "GN": "Guinea",
    "GW": "Guinea-Bissau",
    "GY": "Guyana",
    "HT": "Haiti",
    "HM": "Heard Island and Mcdonald Islands",
    "VA": "Holy See (Vatican City State)",
    "HN": "Honduras",
    "HK": "Hong Kong",
    "HU": "Hungary",
    "IS": "Iceland",
    "IN": "India",
    "ID": "Indonesia",
    "IR": "Iran, Islamic Republic of",
    "IQ": "Iraq",
    "IE": "Ireland",
    "IL": "Israel",
    "IT": "Italy",
    "JM": "Jamaica",
    "JP": "Japan",
    "JO": "Jordan",
    "KZ": "Kazakhstan",
    "KE": "Kenya",
    "KI": "Kiribati",
    "KP": "Korea, Democratic People's Republic of",
    "KR": "Korea, Republic of",
    "KW": "Kuwait",
    "KG": "Kyrgyzstan",
    "LA": "Lao People's Democratic Republic",
    "LV": "Latvia",
    "LB": "Lebanon",
    "LS": "Lesotho",
    "LR": "Liberia",
    "LY": "Libyan Arab Jamahiriya",
    "LI": "Liechtenstein",
    "LT": "Lithuania",
    "LU": "Luxembourg",
    "MO": "Macao",
    "MK": "Macedonia, the Former Yugoslav Republic of",
    "MG": "Madagascar",
    "MW": "Malawi",
    "MY": "Malaysia",
    "MV": "Maldives",
    "ML": "Mali",
    "MT": "Malta",
    "MH": "Marshall Islands",
    "MQ": "Martinique",
    "MR": "Mauritania",
    "MU": "Mauritius",
    "YT": "Mayotte",
    "MX": "Mexico",
    "FM": "Micronesia, Federated States of",
    "MD": "Moldova, Republic of",
    "MC": "Monaco",
    "MN": "Mongolia",
    "MS": "Montserrat",
    "MA": "Morocco",
    "MZ": "Mozambique",
    "MM": "Myanmar",
    "NA": "Namibia",
    "NR": "Nauru",
    "NP": "Nepal",
    "NL": "Netherlands",
    "AN": "Netherlands Antilles",
    "NC": "New Caledonia",
    "NZ": "New Zealand",
    "NI": "Nicaragua",
    "NE": "Niger",
    "NG": "Nigeria",
    "NU": "Niue",
    "NF": "Norfolk Island",
    "MP": "Northern Mariana Islands",
    "NO": "Norway",
    "OM": "Oman",
    "PK": "Pakistan",
    "PW": "Palau",
    "PS": "Palestinian Territory, Occupied",
    "PA": "Panama",
    "PG": "Papua New Guinea",
    "PY": "Paraguay",
    "PE": "Peru",
    "PH": "Philippines",
    "PN": "Pitcairn",
    "PL": "Poland",
    "PT": "Portugal",
    "PR": "Puerto Rico",
    "QA": "Qatar",
    "RE": "Reunion",
    "RO": "Romania",
    "RU": "Russian Federation",
    "RW": "Rwanda",
    "SH": "Saint Helena",
    "KN": "Saint Kitts and Nevis",
    "LC": "Saint Lucia",
    "PM": "Saint Pierre and Miquelon",
    "VC": "Saint Vincent and the Grenadines",
    "WS": "Samoa",
    "SM": "San Marino",
    "ST": "Sao Tome and Principe",
    "SA": "Saudi Arabia",
    "SN": "Senegal",
    "CS": "Serbia and Montenegro",
    "SC": "Seychelles",
    "SL": "Sierra Leone",
    "SG": "Singapore",
    "SK": "Slovakia",
    "SI": "Slovenia",
    "SB": "Solomon Islands",
    "SO": "Somalia",
    "ZA": "South Africa",
    "GS": "South Georgia and the South Sandwich Islands",
    "ES": "Spain",
    "LK": "Sri Lanka",
    "SD": "Sudan",
    "SR": "Suriname",
    "SJ": "Svalbard and Jan Mayen",
    "SZ": "Swaziland",
    "SE": "Sweden",
    "CH": "Switzerland",
    "SY": "Syrian Arab Republic",
    "TW": "Taiwan, Province of China",
    "TJ": "Tajikistan",
    "TZ": "Tanzania, United Republic of",
    "TH": "Thailand",
    "TL": "Timor-Leste",
    "TG": "Togo",
    "TK": "Tokelau",
    "TO": "Tonga",
    "TT": "Trinidad and Tobago",
    "TN": "Tunisia",
    "TR": "Turkey",
    "TM": "Turkmenistan",
    "TC": "Turks and Caicos Islands",
    "TV": "Tuvalu",
    "UG": "Uganda",
    "UA": "Ukraine",
    "AE": "United Arab Emirates",
    "GB": "United Kingdom",
    "US": "United States",
    "UM": "United States Minor Outlying Islands",
    "UY": "Uruguay",
    "UZ": "Uzbekistan",
    "VU": "Vanuatu",
    "VE": "Venezuela",
    "VN": "Viet Nam",
    "VG": "Virgin Islands, British",
    "VI": "Virgin Islands, U.s.",
    "WF": "Wallis and Futuna",
    "EH": "Western Sahara",
    "YE": "Yemen",
    "ZM": "Zambia",
    "ZW": "Zimbabwe"
};

const logos = {
    "DISABLE PLIST LOGOS": "DISABLE PLIST LOGOS",
    "OVERRIDE APP LOGOS": "OVERRIDE APP LOGOS"
}

const select_status = {
    "active": "ACTIVE",
    "inactive": "INACTIVE",
}

function clearInput(event){
    if(!event.target.checked){
        form.password='';
        form.password_confirmation='';
    }
}

function submit() {
    if(form.type == 'file'){
        manageChunk(form);
    }else{
        form.post(route('client-playlist-register'), {
            forceFormData: true,
            onSuccess: () => {
                form.reset();
            },
            onError: (errors) => {

                let errorMessages = `<div class="alert alert-danger">`;
                for(let key in errors){
                    errorMessages += errors[key] + '<br>';
                }
                errorMessages += `</div>`;
                document.querySelector('.error_messages').innerHTML = errorMessages;

            }
        });
    }
   
}

</script>

<style scoped>
/* ----------------------------------------------- */

body {
    background-color: #F1F2F7 !important;
    overflow-x: hidden !important;
}

.card {
    border: none !important;
    background: none !important;
}

.Heading p {
    font-size: 30px;
    color: #002A3B;
    text-align: center;
}

select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' fill='%23ffffff' viewBox='0 0 24 24'><path d='M7 10l5 5 5-5z'/></svg>");
    background-repeat: no-repeat;
    background-position: right 10px center;
}

form {
    background-color: #FFFFFF;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 0 12px rgba(0, 0, 0, 0.06);
}

.btn {
    border-radius: 10px;
}

.btn-primary {
    background: #002153;
}

/* Labels */
form label {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 6px;
    text-transform: uppercase;
    color: #333;
}

/* Form Inputs */
form .form-control {
    border-radius: 8px;
    height: 42px;
    font-size: 14px;
    background-color: #F1F2F7;
}

form .form-control:focus {
    border-color: #615DFC;
    border: 1px solid #615DFC;
    box-shadow: none;
}

input {
    background-color: #F1F2F7;
}

/* Radio & Checkbox Label */
.form-check-label {
    font-weight: 500;
    margin-left: 4px;
    font-size: 13px;
}

.d-flex.gap-4 {
    gap: 20px;
    flex-wrap: wrap;
}

/* File/URL Button */
.btn-outline-secondary {
    height: 40px;
    width: 40px;
    padding: 0;
    font-size: 18px;
    border-radius: 8px;
    margin-left: 10px;
}

/* Danger Border for Validation */
.border-danger {
    border-color: #dc3545 !important;
}

/* Small tweaks */
.form-group {
    margin-bottom: 20px;
}

select.form-control {
    height: 42px;
}

.custom-select-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background-color: #F1F2F7;
    border-radius: 12px;
    height: 45px;
    width: 100%;
}

.custom-select-wrapper select {
    flex: 1;
    background: transparent;
    height: 100%;
    padding-right: 10px;
    font-size: 14px;
    color: #5f6475;
    outline: none;
    cursor: pointer;
}

.select-arrow {
    background-color: #615DFC;
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
    height: 100%;
    width: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}

.protected_desc {
    color: violet;
}

/* --------------------------------------------- */

.header {
    background-color: #0d1a3c;
    color: white;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.list_playlist {
    margin-top: 50px;
}

/* Chunk functionality css */
#progress-container {
    width: 100%;
    background: #eee;
    margin-top: 10px;
    display:none;
}
/* #progress-bar {
    width: 0%;
    height: 20px;
    background: green;
    color: white;
    text-align: center;
} */
  
</style>
