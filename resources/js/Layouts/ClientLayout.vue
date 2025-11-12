<template>
<div class="MainContainer">
    <div class="headersection">
        <!-- Logo -->
        <div class=" container d-flex justify-content-between align-items-center py-3">
            <div>
                <img :src="Logo" alt="Logo" />
            </div>

            <!-- Logout -->
            <div class="d-flex">
                <p class="text-white">Do you want to add another device?</p>&nbsp<a href="#" @click="logout">+&nbspADD NEW DEVICE</a>
            </div>
        </div>
    </div>

    <div class="heroSection d-flex align-items-center py-5 px-4">
        <div class="container">
            <div class="row align-items-center">
                <!-- Left Content -->
                <div class="col-lg-6 mb-4 mb-lg-0 hero-text">
                    <h2 class="mb-4" style="font-size: 36px;">
                        Playlist Upload To <span class="textColor" style="font-size: 64px;">Smarters Pro</span>
                        <span style="font-size: 24px;"> Smart TVs</span>
                    </h2>
                    <ul class="ps-3 no-bullets">
                        <li>
                            <img :src="Star" alt="Star Icon" />
                            Select proper EPG country to correctly match channel electronic programming language.
                        </li>
                        <li>
                            <img :src="Star" alt="Star Icon" />
                            Use Disable plist logos to disable playlist logos or Override app logos (to logo) to
                            only use playlist logos.
                        </li>
                        <li>
                            <img :src="Star" alt="Star Icon" />
                            Use Save online ONLY if you have problems loading playlist on your TV due to low memory.
                        </li>
                        <li>
                            <img :src="Star" alt="Star Icon" />
                            Use Detect EPG to automatically detect EPG URL included in your playlist.
                        </li>
                        <li>
                            <img :src="Star" alt="Star Icon" />
                            Use Disable Groups to disable playlist groups when uploading multiple playlists.
                        </li>
                        <li>
                            <img :src="Star" alt="Star Icon" />
                            Lock your MAC/ID in App’s settings to avoid your playlist being reset by third party.
                        </li>
                    </ul>
                </div>
                <!-- Right Video -->
                <div class="rightsidevedio col-sm-12 col-lg-6  video-wrapper">
                    <video width="100%" controls poster="https://via.placeholder.com/400x250">
                        <source src="" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container">
    <div class="row">
        <slot></slot>
        <div class="col-md-12">
            <div class="container-fluid pt-0 ht-100p text-center">
                While using this application I accept <a href="https://smarterspro.com/terms-conditions/" target="_blank"><b>terms and conditions.</b></a>
            </div>
        </div>
    </div>
</div>
</template>

<script setup>
import Logo from "../../../public/images/SmarterProLogo.png"
import Star from "../../../public/images/Star.png"
import HeroVector from "../../../public/images/HeroVector.png"

import {
    route
} from 'ziggy-js'
import {
    Link,
    router
} from '@inertiajs/vue3';

function logout() {
    router.post(route('client-logout'));
}
</script>

<style scoped>
body {
    font-family: 'Segoe UI', sans-serif;
    /* background-color: #f1f2f6; */
    width: 100%;
    height: 100%;
}

.card {
    border: none !important;
}

.no-bullets {
    list-style-type: none;
    padding-left: 0;
    margin-left: 0;
}

.no-bullets li {
    display: flex;
    align-items: center;
}

.no-bullets li img {
    flex-shrink: 0;
}

.headersection {
    background-color: #002153;
    padding-left: 40px;
    padding-right: 40px;
}

.headersection img {
    height: 40px;
}

.headersection a {
    color: #007bff;
    font-weight: 500;
}

.headersection a:hover {
    text-decoration: underline;
}

.MainContainer {
    width: 100%;
    height: 100%;
}

.heroSection {
    width: 100%;
    min-height: 400px;
    background: url('/images/HeroVector.png') center center no-repeat;
    background-size: 100% 100%;
    color: #fff;
    position: relative;
    z-index: 1;
}

.hero-text h2 .textColor {
    color: #00b8ff;
    font-weight: 700;
}

.video-wrapper video {
    border-radius: 12px;
    width: 80%;
    height: auto;
    border: 2px solid #ffffff;
}

.rightsidevedio {
    display: flex;
    justify-content: flex-end;

    padding-left: 30px;
}

.SmarterPRo {
    font-size: 64px !important;
}

ul.no-bullets {
    list-style-type: none;
    padding-left: 0;
    margin-left: 0;
    color: #ffffff;
    font-family: 'Segoe UI', sans-serif;
    font-size: 15px;
    line-height: 1.6;
}

.no-bullets li {
    display: flex;
    align-items: flex-start;
    margin-bottom: 10px;
}

.no-bullets li img {
    width: 16px;
    height: 16px;
    margin-right: 10px;
    margin-top: 4px;
    flex-shrink: 0;
}

@media (max-width: 768px) {
    .header-padding {
        padding-left: 5px !important;
        padding-right: 5px !important;
    }

    .heroSection {
        background-size: cover;
        background-position: center top;
    }

}

@media (max-width:1000px) {
    .video-wrapper video {
        border-radius: 12px;
        width: 100%;
        height: auto;
        border: 2px solid #ffffff;
    }

    .rightsidevedio {
        display: flex;
        max-height: 300px;
        padding-left: 0px;
    }
}

#snackbar {
    visibility: hidden;
    min-width: 250px;
    margin-left: -125px;
    background-color: #333;
    color: #fff;
    text-align: center;
    border-radius: 2px;
    padding: 16px;
    position: fixed;
    z-index: 1;
    left: 50%;
    bottom: 30px;
    font-size: 17px;
}

#snackbar.show {
    visibility: visible;
    -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
    animation: fadein 0.5s, fadeout 0.5s 2.5s;
}

@-webkit-keyframes fadein {
    from {
        bottom: 0;
        opacity: 0;
    }

    to {
        bottom: 30px;
        opacity: 1;
    }
}

@keyframes fadein {
    from {
        bottom: 0;
        opacity: 0;
    }

    to {
        bottom: 30px;
        opacity: 1;
    }
}

@-webkit-keyframes fadeout {
    from {
        bottom: 30px;
        opacity: 1;
    }

    to {
        bottom: 0;
        opacity: 0;
    }
}

@keyframes fadeout {
    from {
        bottom: 30px;
        opacity: 1;
    }

    to {
        bottom: 0;
        opacity: 0;
    }
}

.btn {
    border-radius: 10px;
}
</style>
