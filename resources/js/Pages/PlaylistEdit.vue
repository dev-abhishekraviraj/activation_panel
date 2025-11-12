<template>
<ClientLayout>
    <div class="col-md-6">
        <Link :href="route('client-playlist-list')" class="btn btn-primary list_playlist mx-3 px-3 py-2">List Playlists</Link>
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
                    <div class="form-group">
                        <label>PLAYLIST NAME:</label>
                        <input type="text" id="playlist_name" :class="['form-control', { 'border-danger': form.errors.playlist_name }]" placeholder="Playlist Name" v-model="form.playlist_name">
                    </div>
                    <div class="form-group">
                        <label>PLAYLIST TYPE:</label>
                        <div class="d-flex">
                            <div class="form-check" v-if="form.type == 'file'">
                                <input type="radio" class="form-check-input" id="m3u_file" name="optradio" v-model="form.type" value="file" checked>
                                <label class="form-check-label" for="m3u_file">M3U FILE</label>
                            </div>
                            <div class="form-check" v-if="form.type == 'url'">
                                <input type="radio" class="form-check-input" id="m3u_url" name="optradio" v-model="form.type" value="url" checked>
                                <label class="form-check-label" for="m3u_url">M3U FILE URL</label>
                            </div>
                        </div>
                        <div v-if="record.type == 'file'">
                            <div class="form-group mt-3">
                                <label>FILE:</label>
                                <div class="d-flex">
                                    <input ref="fileInput" type="file" id="m3u_file_input" :class="['form-control', { 'border-danger': form.errors.m3u_file }]" @input="form.m3u_file = $event.target.files[0]" accept=".m3u">
                                </div>
                            </div>
                        </div>
                        <div v-if="record.type == 'url'">
                            <div class="form-group mt-3">
                                <label>URL:</label>
                                <div class="d-flex">
                                    <input type="text" id="m3u_url_input" :class="['form-control', { 'border-danger': form.errors.m3u_url }]" placeholder="M3U OR TXT playist URL" v-model="form.m3u_url">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="form-group mt-3">
                        <label for="">EPG:</label>
                        <div class="d-flex">
                            <input type="text" id="xmltv_url" class="form-control" placeholder="XMLTV EPG URL" v-model="form.xmltv_url">
                        </div>
                    </div> -->
                    <!-- -----country- -->
                    <!-- <div class="form-group">
                        <label for="">EPG COUNTRIES:</label>
                        <select name="" id="epg_country" class="form-control" v-model="form.epg_countries">
                            <option value="">SELECT EPG COUNTRY</option>
                            <option v-for="(name, code) in countries" :key="code" :value="code">
                                {{ name }}
                            </option>
                        </select>
                    </div> -->
                    <!-- logo -->
                    <!-- <div class="form-group ">
                        <label for="">LOGOS:</label>
                        <select name="" id="logo" class="form-control" v-model="form.logos">
                            <option value="">SELECT LOGO</option>
                            <option v-for="(name, code) in logos" :key="code" :value="code">
                                {{ name }}
                            </option>
                        </select>
                    </div> -->
                    <!-- third content -->
                    <div class="form-group ">
                        <label>Status:</label>
                        <select name="" id="status" :class="['form-control', { 'border-danger': form.errors.status }]" v-model="form.status">
                            <option value="">SELECT STATUS</option>
                            <option v-for="(title, value) in select_status" :key="value" :value="value">
                                {{ title }}
                            </option>
                        </select>
                    </div>
                    <!-- ----- -->
                    <div class="row">
                        <div class="form-group d-flex gap-4 flex-wrap align-items-center">
                            <div class="form-check ms-auto">
                                <input type="checkbox" id="is_protected" class="form-check-input" v-model="form.is_protected" @click="clearInput()" />
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
                    </div>
                    <div class="row" v-if="form.is_protected">
                        <div class="col-md-6">
                            <div class="form-group mt-3">
                                <label>PASSWORD:</label>
                                <input type="text" id="password" :class="['form-control', { 'border-danger': form.errors.password }]" placeholder="Enter password" v-model="form.password">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mt-3">
                                <label>CONFIRM PASSWORD:</label>
                                <input type="text" id="password_confirmation" :class="['form-control', { 'border-danger': form.errors.password }]" placeholder="Re-enter password" v-model="form.password_confirmation">
                            </div>
                        </div>
                    </div>
                    <div>
                        <button id="payment-button" type="submit" class="btn btn-primary">
                            <span id="payment-button-amount">Update</span>
                        </button>
                        <div id="progress-container">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="progress-bar" style="width:0%"></div>
                        </div>
                        <div class="m3u_file_processing_message mt-4"></div>
                        <div class="m3u_file_success_message"></div>
                    </div>
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
import axios from 'axios';
import {
    route
} from 'ziggy-js'

let props = defineProps({
    record: Object,
    devicedetails: Array
})

const form = useForm({
    id: props.record.id,
    playlist_name: props.record.playlist_name,
    m3u_file: '',
    m3u_url: props.record.stream_line,
    xmltv_url: props.record.epg,
    type: props.record.type,
    epg_countries: props.record.epg_countries,
    logos: props.record.logos,
    save_online: (props.record.save_online === 1) ? true : false,
    detect_epg: (props.record.detect_epg === 1) ? true : false,
    disable_groups: (props.record.disable_groups === 1) ? true : false,
    is_protected: (props.record.is_protected === 1) ? true : false,
    status: (props.record.status === 1) ? 'active' : 'inactive',
    password: '',
    password_confirmation: '',
    password_exists:props.record.password_exists

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

function clearInput() {
    if (!form.is_protected) {
        form.password = ''
        form.password_confirmation = ''
    }
}

function submit() {

    if(form.type == 'file' && document.getElementById('m3u_file_input').files[0] != undefined){
        manageChunk(form);
    }
    else{
        form.post(route('client-playlist-edit'), {
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
        })

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
    padding: 0 0 0 12px;
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
