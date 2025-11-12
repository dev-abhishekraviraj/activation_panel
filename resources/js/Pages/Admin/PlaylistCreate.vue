<template>
<AdminLayout>
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-2 mt-2 mx-1">
            <Link :href="route('admin-playlist-list')" class="btn btn-success">List Playlists</Link>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="alert alert-success" v-if="$page.props.flash.success">
                    <strong>Success!</strong>&nbsp{{ $page.props.flash.success }}
                </div>
                <div class="error_messages"></div>
                <form @submit.prevent="submit">
                    <div class="form-group">
                        <label for="">PLAYLIST NAME:</label>
                        <input type="text" id="playlist_name" :class="['form-control', { 'border-danger': form.errors.playlist_name }]" placeholder="Playlist Name" v-model="form.playlist_name">
                    </div>
                    <div class="form-group">
                        <select name="" id="mac_address" :class="['form-control', { 'border-danger': form.errors.mac_id }]" v-model="form.mac_id">
                            <option value="">Select Mac Address:</option>
                            <option v-for="(mac_address) in devices" :key="mac_address.id" :value="mac_address.id">
                                {{ mac_address.mac_address }}
                            </option>
                        </select>
                    </div>
                    <div class="row">
                        <label for="" class="mx-15">PLAYLIST TYPE:</label>
                        <div class="col-md-12 d-flex">
                            <div class="form-check">
                                <input type="radio" class="form-check-input" id="m3u_file" name="optradio" v-model="form.type" value="file" checked>
                                <label class="form-check-label" for="m3u_file">M3U FILE</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" class="form-check-input" id="m3u_url" name="optradio" v-model="form.type" value="url">
                                <label class="form-check-label" for="m3u_url">M3U FILE URL</label>
                            </div>
                        </div>
                        <div class="col-md-12" v-if="form.type == 'file'">
                            <div class="form-group mt-3">
                                <label for="">FILE:</label>
                                <div class="d-flex">
                                    &nbsp<input ref="fileInput" id="m3u_file_input" type="file" :class="['form-control', { 'border-danger': form.errors.m3u_file }]" @input="form.m3u_file = $event.target.files[0]">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12" v-if="form.type == 'url'">
                            <div class="form-group mt-3">
                                <label for="">URL:</label>
                                <div class="d-flex">
                                    &nbsp<input type="text" :class="['form-control', { 'border-danger': form.errors.m3u_url }]" placeholder="M3U OR TXT playist URL" v-model="form.m3u_url">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="form-group mt-3">
                        <label for="">EPG:</label>
                        <div class="d-flex">
                            &nbsp<input type="text" class="form-control" placeholder="XMLTV EPG URL" v-model="form.xmltv_url">
                        </div>
                    </div>
                    <div class="form-group">
                        <select name="" id="" class="form-control" v-model="form.epg_countries">
                            <option value="">EPG Countries</option>
                            <option v-for="(name,code) in countries" :key="code" :value="code">
                                {{ name }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group mt-3">
                        <select name="" id="" class="form-control" v-model="form.logos">
                            <option value="">LOGOS</option>
                            <option v-for="(name,code) in logos" :key="code" :value="code">
                                {{ name }}
                            </option>
                        </select>
                    </div> -->
                    <div class="form-group mt-3">
                        <select name="" id="" :class="['form-control', { 'border-danger': form.errors.status }]" v-model="form.status">
                            <option value="">Select status</option>
                            <option v-for="(title,value) in select_status" :key="value" :value="value">
                                {{ title }}
                            </option>
                        </select>
                    </div>
                    <div class="row">
                        <!-- <div class="col-lg-3">
                            <div class="form-group mt-3">
                                <input type="checkbox" id="save_online" v-model="form.save_online" value="">&nbsp
                                <label for="save_online">Save online</label><br>
                            </div>
                        </div> -->
                        <!-- <div class="col-lg-3">
                            <div class="form-group mt-3">
                                <input type="checkbox" id="detect_epg" v-model="form.detect_epg" value="">&nbsp
                                <label for="detect_epg">Detect EPG</label><br>
                            </div>
                        </div> -->
                        <!-- <div class="col-lg-3">
                            <div class="form-group mt-3">
                                <input type="checkbox" id="disable_groups" v-model="form.disable_groups" value="">&nbsp
                                <label for="disable_groups">Disable Groups</label><br>
                            </div>
                        </div> -->
                        <div class="col-lg-3">
                            <div class="form-group mt-3">
                                <input type="checkbox" id="is_protected" v-model="form.is_protected" @click="clearInput()">&nbsp
                                <label for="is_protected">Password Protected</label><br>
                                <p class="protected_desc">Protected playlists will not be viewed or modified without entering password.</p>
                            </div>
                        </div>
                    </div>
                    <div class="row" v-if="form.is_protected">
                        <div class="col-md-6">
                            <div class="form-group mt-3">
                                <label for="">PASSWORD:</label>
                                <input type="text" id="password" :class="['form-control', { 'border-danger': form.errors.password }]" placeholder="Enter password" v-model="form.password">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mt-3">
                                <label for="">CONFIRM PASSWORD:</label>
                                <input type="text" id="password_confirmation" :class="['form-control', { 'border-danger': form.errors.password }]" placeholder="Re-enter password" v-model="form.password_confirmation">
                            </div>
                        </div>
                    </div>
                    <div>
                        <button id="payment-button" type="submit" class="btn btn-primary">
                            <span id="payment-button-amount">Send</span>
                        </button>
                    </div>
                    <div id="progress-container">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" id="progress-bar" style="width:0%"></div>
                    </div>
                    <div class="m3u_file_processing_message mt-4"></div>
                    <div class="m3u_file_success_message"></div>
                </form>
            </div>
        </div>
    </div>
</AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import manageChunk from '@/Chunk/chunk.js';

import {
    useForm,
    Link
} from '@inertiajs/vue3'
import {
    route
} from 'ziggy-js'
let props = defineProps({
    playlist_count: Number,
    devices: Array
})

const form = useForm({
    id: '',
    playlist_name: '',
    mac_id: '',
    type:'file',
    m3u_file: '',
    m3u_url: '',
    xmltv_url: '',
    epg_countries: '',
    logos: '',
    save_online: '',
    detect_epg: '',
    disable_groups: '',
    is_protected: false,
    status: '',
    password: '',
    password_confirmation: ''
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
    "disable plist logos": "Disable plist logos",
    "override app logos": "Override app logos"
}

const select_status = {
    "active": "Active",
    "inactive": "Inactive",
}

function clearInput() {
    if(!form.is_protected) {
        form.password = ''
        form.password_confirmation = ''
    } 
}

function submit() {

    if(form.type == 'file'){
      manageChunk(form,'admin');
    }else{
        form.post(route('admin-playlist-create'), {
            forceFormData: true,
            onSuccess: () => {
                form.reset()
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
<style>
 .protected_desc{
    white-space: nowrap;
    color:violet;
 }
 .mx-15{
    margin-left:15px;
 }
</style>