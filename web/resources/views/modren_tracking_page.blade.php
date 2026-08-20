<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{$title}}</title>
    <link href="https://fonts.googleapis.com/css2?family=Sans+Serif&display=swap" rel="stylesheet">

    <script type="importmap">{ "imports": {
    "three": "//unpkg.com/three/build/three.module.js",
    "three/addons/": "//unpkg.com/three/examples/jsm/"
  }}</script>
    <script type="module">
        import * as THREE from 'three';
        window.THREE = THREE;
    </script>
    <script src="//unpkg.com/three-globe" defer></script>
    <style>
        html, body {
            font-family: 'Sans Serif', sans-serif;
        }

        #trackify-app {
            --bg_teritary: #ffffff;
            --bg_hover: rgba(0, 18, 49, 0.04);
            --bg_fill: rgba(0, 18, 49, 0.06);
            --primary: #0B1019;
            --secondary: rgba(11, 16, 25, 0.7);
            --teritary: rgba(11, 16, 25, 0.4);
            --border_secondary: rgba(0, 18, 49, 0.12);
            --shield_primary: rgba(11, 16, 25, 0.7);
        }

        #trackify-app {
            --trackify-all-radius: 12px;
            --trackify-big-radius: 8px;
            --trackify-small-radius: 4px;
        }

        #trackify-app {
            --white: #fff;
            --main-p2: rgba(6, 95, 245, .1);
            --text-p1: #0b1019;
            --text-p2: rgba(11, 16, 25, .65);
            --text-p3: rgba(11, 16, 25, .45);
            --text-p4: rgba(11, 16, 25, .25);
            --background-p1: #fff;
            --background-p2: #f1f2f6;
            --line-p1: rgba(2, 15, 64, .4);
            --line-p2: rgba(2, 15, 64, .08);
            --warning: #e70d0d;
            --main-p1: #2678ff;
            --consistent-white-primary: hsla(0, 0%, 100%, .95);
            --consistent-white-secondary: hsla(0, 0%, 100%, .55);
            --consistent-alert: #b42318;
        }

        .trackify_track_wrapper {
            height: 100%;
            width: 100%;
        }

        .trackify_map_iframe {
            border: none;
            height: calc(100vh + 150px);
            margin-top: -150px;
            opacity: 0;
            position: fixed;
            visibility: hidden;
            width: 100%;
            z-index: 1;
        }

        .trackify_map_show_iframe {
            animation: fadeIn 1.5s ease-in forwards;
            visibility: visible;
        }

        .trackify_globe_wrapper {
            background-color: #f1f1f1;
            border: none;
            height: 100vh;
            left: 0;
            opacity: 1;
            position: fixed;
            top: 0;
            width: 100vw;
            z-index: 3;
        }

        .trackify_globe_hidden_wrapper,
        .trackify_map_echarts_hidden_wrapper {
            animation: fadeOut 1.5s ease-in forwards;
            z-index: 1 !important;
        }

        .trackify_form_wrapper {
            /* display: none; */
            display: flex;
            flex-direction: column;
            height: 100vh;
            justify-content: center;
            left: 0;
            position: fixed;
            top: 0;
            z-index: 3;
        }
        .track_modern_loading {
            display: none;
            align-items: center;
            background: var(--bg_teritary);
            border-radius: var(--trackify-all-radius);
            box-shadow: 0 8px 16px 0 rgba(0, 0, 0, .16);
            /*display: flex;*/
            height: 248px;
            justify-content: center;
            margin-left: 13px;
            margin-top: -124px;
            position: fixed;
            top: 50%;
            width: 360px;
            z-index: 3;
        }

        .track_loading_status_content {
            display: flex;
            gap: 16px;
            overflow: hidden;
            width: 208px;
        }
        .track_loading_status_content svg {
            height: 40px;
            width: 40px;
        }
        .track_loading_status_content svg path {
            fill: rgba(11, 16, 25, .1);
        }
        .track_loading_status_content svg:first-child path {
            animation: highlight 2s 0s infinite;
        }
        .track_loading_status_content svg:nth-child(2) path {
            animation: highlight 2s .5s infinite;
        }
        .track_loading_status_content svg:nth-child(3) path {
            animation: highlight 2s 1s infinite;
        }
        .track_loading_status_content svg:nth-child(4) path {
            animation: highlight 2s 1.5s infinite;
        }
        @keyframes highlight {
            0% {
                fill: #E6E7E8; /* Start with the initial color */
            }
            50% {
                fill: #BABCBE; /* Highlight color */
            }
            100% {
                fill: #E6E7E8; /* End with the initial color */
            }
        }
        @media screen and (max-width: 800px) {
            .trackify_form_wrapper {
                bottom: 0;
                box-sizing: border-box;
                height: auto;
                justify-content: flex-end;
                left: 0;
                margin-top: 38px;
                position: fixed;
                top: unset;
                transform: unset;
                width: 100vw;
                z-index: 3;
            }
        }

        .trackify_form_container {
            background: var(--bg_teritary);
            border-radius: var(--trackify-all-radius);
            box-shadow: 0 4px 16px rgba(0, 0, 0, .16);
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-left: 20px;
            padding: 20px 20px 24px;
            position: relative;
            width: 360px;
        }

        @media screen and (max-width: 800px) {
            .trackify_form_container {
                border-end-end-radius: 0;
                border-end-start-radius: 0;
                margin-left: 0;
                width: 100%;
            }
        }

        .drawer {
            display: none
        }

        .trackify_tab_container {
            display: flex;
            width: 100%;
        }

        .trackify_tab_bar {
            background: transparent;
            border: none;
            border-bottom: 1px solid var(--line-p2);
            color: var(--teritary);
            cursor: pointer;
            display: block;
            font-size: 14px;
            font-weight: 700;
            overflow: hidden;
            padding: 10px;
            text-overflow: ellipsis;
            white-space: nowrap;
            width: 100%;
        }

        @media screen and (max-width: 800px) {
            .trackify_tab_bar {
                padding: 10px 5px;
                text-align: center;
                white-space: break-spaces;
                width: 100%;
            }
        }

        .trackify_tab_active_bar {
            border-bottom: 2px solid var(--primary);
            color: var(--primary);
        }

        .trackify_input_container {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .trackify_input_content {
            position: relative;
        }

        .trackify_form_field {
            background: var(--bg_fill);
            border: none;
            border-radius: var(--trackify-big-radius);
            color: var(--primary);
            font-size: 14px;
            height: 36px;
            padding: 8px 8px 8px 12px;
            width: 100%;
        }

        .trackify_input_clear_btn {
            cursor: pointer;
            padding: 7px;
            position: absolute;
            right: 2px;
            top: 4px;
        }

        .trackify_input_clear_btn path {
            fill: transparent;
        }

        .trackify_form_error {
            color: var(--consistent-alert);
            display: none;
            font-size: 12px;
            line-height: 16px;
            margin-top: 4px;
        }

        .trackify_form_button {
            align-items: center;
            background: {{$pageData->process_bar_color??"#313131"}};
            border: none;
            border-radius: var(--trackify-big-radius);
            color: var(--consistent-white-primary);
            cursor: pointer;
            display: flex;
            font-size: 16px;
            font-weight: 700;
            justify-content: center;
            line-height: 24px;
            padding: 8px 0;
            width: 100%;
        }



        /* --------------- ORDER WRAPPER --------------- */
        .trackify_order_wrapper {
            display: flex;
            flex-direction: column;
            font-size: 14px;
            gap: 12px;
            left: 20px;
            position: fixed;
            top: 50%;
            transform: translateY(-50%);
            z-index: 4;
        }

        @media screen and (max-width: 800px) {
            .trackify_order_wrapper {
                gap: 0;
                left: 0;
                top: 40%;
                transform: unset;
                transition: top .3s ease-out;
                width: 100%;
            }
        }

        .trackify_order_nav_tab_container {
            display: flex;
            gap: 8px;
            scroll-behavior: smooth;
            margin-bottom: 10px;
        }

        @media screen and (max-width: 800px) {
            .trackify_order_nav_tab_container {
                overflow: auto;
                padding: 0 12px;
                margin-bottom: 0px;
            }
        }

        .trackify_order_nav_item {
            align-items: center;
            background-color: var(--bg_teritary);
            border-radius: var(--trackify-big-radius);
            box-shadow: 0 8px 16px 0 rgba(0, 0, 0, .16);
            box-sizing: border-box;
            cursor: pointer;
            display: flex;
            font-weight: 700;
            gap: 4px;
            height: 36px;
            padding: 8px 16px 8px 12px;
        }

        @media screen and (max-width: 800px) {
            .trackify_order_nav_item {
                margin: 32px 0 12px;
            }
        }

        .trackify_order_nav_item .title {
            white-space: nowrap;
            margin: 0px;
            line-height: normal;
        }

        .trackify_order_detail_container {
            background-color: var(--bg_teritary);
            border-radius: var(--trackify-all-radius);
            box-shadow: 0 8px 16px 0 rgba(0, 0, 0, .16);
            color: var(--primary);
            font-size: 14px;
            height: calc(100vh - 88px);
            overflow: hidden;
            position: relative;
            width: 360px;
        }

        .trackify_order_detail_container,
        .trackify_order_detail_header {
            display: flex;
            flex-direction: column;
        }
        .trackify_order_detail_headerbg{
            /*background: #313131;*/
            background: rgb(6, 174, 212);

        }
        .trackify_order_nav_item.active{
            background: {{$pageData->process_bar_color??"#313131"}};
            color: #ffffff;
        }
        .trackify_order_nav_item svg path{
            fill: {{$pageData->process_bar_color??"#313131"}} !important;
        }
        .trackify_order_nav_item.active svg path{
            fill: #ffffff !important;
        }
        @media screen and (max-width: 800px) {
            .trackify_order_detail_container {
                border-end-end-radius: 0;
                border-end-start-radius: 0;
                height: calc(100vh - 80px);
                width: 100%;
            }
        }

        .trackify_order_detail_header .header {
            align-items: center;
            color: var(--consistent-white-secondary);
            display: flex;
            gap: 16px;
            justify-content: space-between;
            padding: 16px 16px 16px 20px;
        }

        .trackify_order_detail_header .header .order_name {
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 1;
            display: -webkit-box;
            flex: 1;
            font-size: 16px;
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
            word-break: break-all;
        }

        .trackify_order_detail_header .header .action_content {
            display: flex;
            gap: 8px;
        }

        .trackify_order_detail_header .header .action_content .btn_box {
            align-items: center;
            border-radius: var(--trackify-small-radius);
            cursor: pointer;
            display: flex;
            height: 28px;
            justify-content: center;
            width: 28px;
        }

        .trackify_order_detail_header .status_icon {
            align-items: center;
            display: flex;
            height: 120px;
            justify-content: center;
            position: absolute;
            right: 20px;
            top: 45px;
            width: 120px;
        }
        .trackify_order_detail_header .status_icon svg path{
            fill:#088AB2
        }

        .trackify_order_detail_header .status_and_product_content {
            height: 152px;
            padding: 16px 20px 0;
            position: relative;
        }

        .trackify_order_detail_header .status_name {
            text-transform: capitalize;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            color: var(--consistent-white-primary);
            display: -webkit-box;
            font-size: 28px;
            font-weight: 700;
            line-height: 40px;
            margin-bottom: 12px;
            overflow: hidden;
            text-overflow: ellipsis;
            word-break: break-all;
        }

        .trackify_order_items_container {
            background-color: var(--bg_teritary);
            border-radius: var(--trackify-all-radius);
            box-shadow: 0 8px 16px 0 rgba(0, 0, 0, .16);
            box-sizing: border-box;
            left: 12px;
            padding: 8px;
            position: absolute;
            top: 54px;
            width: calc(100% - 24px);
            z-index: 1;
        }

        .trackify_order_items_content {
            border-radius: var(--trackify-small-radius);
            box-sizing: border-box;
            cursor: pointer;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            padding: 12px;
        }

        .trackify_order_items_container .current-package-content,
        .trackify_order_packages_item .current-package-content {
            align-items: center;
            display: flex;
            gap: 16px;
        }

        .trackify_order_items_container .content,
        .trackify_order_packages_item .content {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .trackify_order_items_container .content .title,
        .trackify_order_packages_item .content .title {
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 1;
            color: var(--primary);
            display: -webkit-box;
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
            word-break: break-all;
            margin: 0px;
            line-height: normal;
        }

        .trackify_order_items_container .content .produc_num,
        .trackify_order_packages_item .content .produc_num {
            color: var(--teritary);
        }

        .order_items_list_content {
            display: flex;
            gap: 4px;
        }

        .trackify_order_items_container .order_items_list_content,
        .trackify_order_packages_item .order_items_list_content {
            cursor: pointer;
            display: flex;
            gap: 4px;
        }

        .trackify_order_items_container .product_image_content,
        .trackify_order_items_list_content .trackify_order_items .product_image_content,
        .trackify_order_packages_item .product_image_content {
            align-items: center;
            border: 1px solid var(--border_secondary);
            border-radius: var(--trackify-small-radius);
            display: flex;
            height: 40px;
            justify-content: center;
            overflow: hidden;
            width: 40px;
        }

        .order_items_list_content .product_image_content {
            align-items: center;
            border: 1px solid var(--border_secondary);
            border-radius: var(--trackify-small-radius);
            display: flex;
            height: 40px;
            justify-content: center;
            overflow: hidden;
            width: 40px;
        }

        .trackify_order_items_container .product_image_content img,
        .trackify_order_items_list_content .trackify_order_items .product_image_content img {
            max-height: 100%;
            max-width: 100%;
        }

        .order_items_list_content .product_image_content img {
            max-height: 100%;
            max-width: 100%;
        }

        .trackify_half_round_content {
            background-color: var(--bg_teritary);
            border-top-left-radius: var(--trackify-all-radius);
            border-top-right-radius: var(--trackify-all-radius);
            height: 26px;
            left: 0;
            position: absolute;
            top: 190px;
            width: 100%;
        }

        .trackify_order_detail_content {
            /* background-color: var(--bg_teritary); */
            border-radius: var(--trackify-all-radius);
            display: flex;
            flex: 1;
            flex-direction: column;
            gap: 20px;
            justify-content: space-between;
            overflow: auto;
            padding: 30px 20px 20px;
        }

        @media screen and (max-width: 800px) {
            .trackify_order_detail_content {
                overflow: hidden;
            }
        }

        .trackify_order_desc_wrapper {
            position: relative;
        }

        .trackify_order_desc_left_content {
            align-items: center;
            display: flex;
            flex-direction: column;
            height: 100%;
            position: absolute;
        }

        .trackify_track_date_icon_box {
            align-items: center;
            background-color: var(--bg_fill);
            border-radius: var(--trackify-small-radius);
            box-sizing: border-box;
            color: var(--primary);
            display: flex;
            flex-direction: column;
            font-size: 12px;
            height: 40px;
            justify-content: center;
            padding: 4px 8px;
            text-align: center;
            white-space: nowrap;
            width: 40px;
            word-break: break-all;
        }

        .trackify_track_date_icon_box.active{
            background: {{$pageData->process_bar_color??"#313131"}};
            color: #ffffff;
        }
        .trackify_track_date_text {
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
            line-height: normal;
        }

        .trackify_order_desc_left_content .trackify_date_line {
            border-left: 1.5pt dashed var(--border_secondary);
            flex: 1;
            width: 1px;
            display: block !important
        }

        .trackify_date_last_line {
            display: none;
        }

        .trackify_order_desc_container {
            display: flex;
            position: relative;
        }

        .trackify_order_desc_right_content {
            margin-bottom: 12px;
            margin-left: 56px;
        }

        .trackify_event_time {
            color: var(--secondary);
            display: flex;
            gap: 8px;
            margin-bottom: 4px;
        }

        .trackify_event_first_detail {
            color: var(--primary) !important;
            font-weight: 700 !important;
        }

        .trackify_event_detail {
            color: var(--primary);
        }

        .trackify_order_carrier_container {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .trackify_carrier_content {
            color: var(--secondary);
            display: flex;
            gap: 16px;
        }

        .trackify_carrier_content .logo_box {
            align-items: center;
            border: 1px solid var(--border_secondary);
            border-radius: var(--trackify-small-radius);
            box-sizing: border-box;
            display: flex;
            height: 40px;
            justify-content: center;
            overflow: auto;
            width: 40px;
        }

        .trackify_carrier_content .logo_box img {
            max-height: 100%;
            max-width: 100%;
        }

        .trackify_carrier_content .content {
            display: flex;
            flex-direction: column;
            gap: 4px;
            justify-content: center;
            line-height: normal;
        }

        .trackify_carrier_content .title {
            color: var(--primary);
            font-weight: 700;
            line-height: 20px;
            margin: 0px
        }


        /* ------------------ Product Recommendations ------------------ */

        .trackify_product_recommendations_wrapper {
            background-color: var(--bg_teritary);
            border-radius: var(--trackify-all-radius);
            box-shadow: 0 8px 16px 0 rgba(0, 0, 0, .16);
            color: var(--primary);
            display: flex;
            flex-direction: column;
            font-size: 14px;
            height: calc(100vh - 88px);
            overflow: hidden;
            position: relative;
            width: 360px;
        }

        .trackify_product_recommendations_header {
            align-items: center;
            display: flex;
            font-size: 16px;
            font-weight: 700;
            gap: 12px;
            justify-content: space-between;
            padding: 16px 16px 16px 20px;
        }

        .trackify_close_btn {
            align-items: center;
            border-radius: var(--trackify-small-radius);
            display: flex;
            height: 28px;
            justify-content: center;
            width: 28px;
        }

        .trackify_product_recommendations_header svg {
            cursor: pointer;
        }

        .trackify_product_recommendations_container {
            box-sizing: border-box;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            overflow: auto;
            padding: 8px 20px 20px;
        }

        .trackify_product_recommendations_item_content {
            cursor: pointer;
            width: calc(50% - 10px);
        }

        .trackify_product_recommendations_img_box {
            background-position: 50%;
            background-repeat: no-repeat;
            background-size: contain;
            border: 1px solid var(--border_secondary);
            border-radius: var(--trackify-big-radius);
            margin-bottom: 12px;
            overflow: hidden;
            padding-top: 100%;
            width: 100%;
        }

        .trackify_product_recommendations_item_content .content {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .trackify_product_title {
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            display: -webkit-box;
            overflow: hidden;
            text-overflow: ellipsis;
            word-break: break-all;
        }

        .trackify_product_price_container {
            align-items: baseline;
            display: flex;
        }

        .trackify_product_price {
            font-weight: 700;
        }

        .trackify_collection_recommendations_container {
            box-sizing: border-box;
            display: flex;
            flex: 1;
            flex-direction: column;
            gap: 4px;
            padding: 8px 20px 20px;
            display: block !important;
        }

        ::-webkit-scrollbar {
            display: none;
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
        }
        .rotate {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <style>
        {!! $custom_css !!}
    </style>
</head>

<body>



<div id="trackify-app">
    <div class="trackify_track_wrapper">
        <iframe id="googleIframe"
                src="https://www.google.com/maps/embed/v1/place?key=AIzaSyDf5VCcikU0XbIhQtJ2mOpz6JHHND2Yggk&q=US"
                frameborder="0" class="trackify_map_iframe trackify_map_show_iframe" style="z-index: 1;"></iframe>


        <div class="trackify_globe_wrapper">
            <div id="globeViz"></div>
        </div>
        <div class="track_modern_loading"><div class="track_loading_status_content"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13.2803 9.21967C13.5732 9.51256 13.5732 9.98744 13.2803 10.2803L9.78033 13.7803C9.48744 14.0732 9.01256 14.0732 8.71967 13.7803L6.96967 12.0303C6.67678 11.7374 6.67678 11.2626 6.96967 10.9697C7.26256 10.6768 7.73744 10.6768 8.03033 10.9697L9.25 12.1893L12.2197 9.21967C12.5126 8.92678 12.9874 8.92678 13.2803 9.21967Z" fill="#0B1019"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M6.51547 4.75C6.6385 3.76342 7.4801 3 8.5 3H11.5C12.5269 3 13.373 3.77394 13.487 4.77035C13.4862 4.76355 13.4854 4.75677 13.4845 4.75H13.75C14.9926 4.75 16 5.75736 16 7V14.75C16 15.9926 14.9926 17 13.75 17H6.25C5.00736 17 4 15.9926 4 14.75V7C4 5.75736 5.00736 4.75 6.25 4.75H6.51547ZM8.5 4.5H11.5C11.7761 4.5 12 4.72386 12 5V6C12 6.27614 11.7761 6.5 11.5 6.5H8.5C8.22386 6.5 8 6.27614 8 6V5C8 4.72386 8.22386 4.5 8.5 4.5ZM6.51304 6.22965C6.51382 6.23645 6.51463 6.24323 6.51547 6.25H6.25C5.83579 6.25 5.5 6.58579 5.5 7V14.75C5.5 15.1642 5.83579 15.5 6.25 15.5H13.75C14.1642 15.5 14.5 15.1642 14.5 14.75V7C14.5 6.58579 14.1642 6.25 13.75 6.25H13.4845C13.3615 7.23658 12.5199 8 11.5 8H8.5C7.4731 8 6.62695 7.22606 6.51304 6.22965Z" fill="#0B1019"></path></svg> <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M4 5.25C4 4.83579 4.33579 4.5 4.75 4.5H11.7414C12.9692 4.5 14.0483 5.31394 14.3856 6.49452L14.8125 7.98862C14.837 8.07452 14.9055 8.1408 14.9922 8.16247L16.6744 8.58303C17.4535 8.77779 18 9.47776 18 10.2808V11.5C18 12.2108 17.5763 12.8226 16.9676 13.0966C16.9889 13.2279 17 13.3627 17 13.5C17 14.8807 15.8807 16 14.5 16C13.1193 16 12 14.8807 12 13.5C12 13.4156 12.0042 13.3322 12.0123 13.25H8.98766C8.99582 13.3322 9 13.4156 9 13.5C9 14.8807 7.88071 16 6.5 16C5.11929 16 4 14.8807 4 13.5C4 13.1444 4.07422 12.8062 4.20802 12.5H3.75C3.33579 12.5 3 12.1642 3 11.75C3 11.3358 3.33579 11 3.75 11H6.25C6.27988 11 6.30935 11.0017 6.33831 11.0051C6.39177 11.0017 6.44568 11 6.5 11C7.19935 11 7.83163 11.2872 8.28536 11.75H12.7146C13.1684 11.2872 13.8007 11 14.5 11C15.1982 11 15.8296 11.2863 16.2832 11.7478C16.4056 11.7316 16.5 11.6268 16.5 11.5V10.2808C16.5 10.1661 16.4219 10.0661 16.3106 10.0382L14.6284 9.61769C14.0217 9.466 13.542 9.00205 13.3702 8.4007L12.9433 6.9066C12.79 6.36997 12.2995 6 11.7414 6H4.75C4.33579 6 4 5.66421 4 5.25ZM6.5 14.5C7.05228 14.5 7.5 14.0523 7.5 13.5C7.5 12.9477 7.05228 12.5 6.5 12.5C5.94772 12.5 5.5 12.9477 5.5 13.5C5.5 14.0523 5.94772 14.5 6.5 14.5ZM14.5 14.5C15.0523 14.5 15.5 14.0523 15.5 13.5C15.5 12.9477 15.0523 12.5 14.5 12.5C13.9477 12.5 13.5 12.9477 13.5 13.5C13.5 14.0523 13.9477 14.5 14.5 14.5Z" fill="#0B1019"></path> <path d="M3.25 8C2.83579 8 2.5 8.33579 2.5 8.75C2.5 9.16421 2.83579 9.5 3.25 9.5H8.25C8.66421 9.5 9 9.16421 9 8.75C9 8.33579 8.66421 8 8.25 8H3.25Z" fill="#0B1019"></path></svg> <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M7 9C6.44772 9 6 9.44771 6 10V13C6 13.5523 6.44771 14 7 14H11C11.5523 14 12 13.5523 12 13V10C12 9.44772 11.5523 9 11 9H7ZM7.5 12.5V10.5H10.5V12.5H7.5Z" fill="#0B1019"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M5.31463 4.44946C5.73645 3.85394 6.4209 3.5 7.15068 3.5H12.9472C13.6995 3.5 14.402 3.87598 14.8193 4.50192L16.0381 6.33013C16.3393 6.78186 16.5 7.31263 16.5 7.85555V14.75C16.5 15.7165 15.7165 16.5 14.75 16.5H5.25C4.2835 16.5 3.5 15.7165 3.5 14.75V7.88657C3.5 7.31712 3.67678 6.76171 4.00593 6.29703L5.31463 4.44946ZM7.15068 5C6.90742 5 6.67927 5.11798 6.53866 5.31649L5.70034 6.5H9.25V5H7.15068ZM10.75 6.5H14.3486L13.5713 5.33397C13.4322 5.12533 13.198 5 12.9472 5H10.75V6.5ZM15 8H5V14.75C5 14.8881 5.11193 15 5.25 15H14.75C14.8881 15 15 14.8881 15 14.75V8Z" fill="#0B1019"></path></svg> <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M15.7803 5.96967C16.0732 6.26256 16.0732 6.73744 15.7803 7.03033L9.28033 13.5303C8.98744 13.8232 8.51256 13.8232 8.21967 13.5303L4.96967 10.2803C4.67678 9.98744 4.67678 9.51256 4.96967 9.21967C5.26256 8.92678 5.73744 8.92678 6.03033 9.21967L8.75 11.9393L14.7197 5.96967C15.0126 5.67678 15.4874 5.67678 15.7803 5.96967Z" fill="#0B1019"></path></svg></div></div>
        <div class="trackify_form_wrapper">
            <div class="trackify_form_container" style="min-height: 250px; @if(!$trackingPage)     justify-content: center;align-items: center; @endif">
                @if($trackingPage)

                <div class="trackify_tab_container">
                    @if($pageData->search_order_with_email==1)
                    <button class="trackify_tab_bar trackify_tab_active_bar" data-tab="order-number">{{$translation->order_number}}</button>
                    @endif
                    @if($pageData->search_tracking_number==1)
                    <button class="trackify_tab_bar @if($pageData->search_order_with_email==0) trackify_tab_active_bar @endif " data-tab="tracking-number">{{$translation->tracking_number}}</button>
                    @endif
                </div>
                <div class="trackify_input_container">
                    @if($pageData->search_order_with_email==1)
                    <div class="trackify_input_content" data-field="order-number">
                        <input placeholder="{{$translation->order_number}}" class="trackify_form_field" data-type="order-number">
                        <div class="trackify_input_clear_btn">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                        d="M7 14C3.13401 14 0 10.866 0 7C0 3.13401 3.13401 0 7 0C10.866 0 14 3.13401 14 7C14 10.866 10.866 14 7 14ZM7 6.01006L5.51507 4.52513C5.2417 4.25176 4.79849 4.25176 4.52513 4.52513C4.25176 4.79849 4.25176 5.24171 4.52513 5.51507L6.01006 7L4.52513 8.48491C4.25176 8.75827 4.25176 9.20149 4.52513 9.47485C4.79849 9.74822 5.2417 9.74822 5.51507 9.47486L7 7.98994L8.48491 9.47485C8.75827 9.74821 9.20148 9.74821 9.47485 9.47485C9.74821 9.20148 9.74821 8.75827 9.47485 8.48491L7.98994 7L9.47486 5.51507C9.74822 5.2417 9.74822 4.79849 9.47485 4.52513C9.20149 4.25176 8.75827 4.25176 8.48491 4.52513L7 6.01006Z"
                                        fill="#0B1019" fill-opacity="0.7"></path>
                            </svg>
                        </div>
                        <div class="trackify_form_error">{{$translation->order_number_error}}</div>
                    </div>
                    <div class="trackify_input_content" data-field="email-phone">
                        <input placeholder="{{$translation->email_phone_number}}" class="trackify_form_field" data-type="email-phone">
                        <div class="trackify_input_clear_btn">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                        d="M7 14C3.13401 14 0 10.866 0 7C0 3.13401 3.13401 0 7 0C10.866 0 14 3.13401 14 7C14 10.866 10.866 14 7 14ZM7 6.01006L5.51507 4.52513C5.2417 4.25176 4.79849 4.25176 4.52513 4.52513C4.25176 4.79849 4.25176 5.24171 4.52513 5.51507L6.01006 7L4.52513 8.48491C4.25176 8.75827 4.25176 9.20149 4.52513 9.47485C4.79849 9.74822 5.2417 9.74822 5.51507 9.47486L7 7.98994L8.48491 9.47485C8.75827 9.74821 9.20148 9.74821 9.47485 9.47485C9.74821 9.20148 9.74821 8.75827 9.47485 8.48491L7.98994 7L9.47486 5.51507C9.74822 5.2417 9.74822 4.79849 9.47485 4.52513C9.20149 4.25176 8.75827 4.25176 8.48491 4.52513L7 6.01006Z"
                                        fill="#0B1019" fill-opacity="0.7"></path>
                            </svg>
                        </div>
                        <div class="trackify_form_error">{{$translation->email_phone_number_error}}</div>
                    </div>
                    @endif
                    @if($pageData->search_tracking_number==1)

                    <div class="trackify_input_content" data-field="tracking-number" style=" @if($pageData->search_order_with_email==1) display: none; @endif ">
                        <input placeholder="{{$translation->tracking_number}}" id="tracking-number" class="trackify_form_field" data-type="tracking-number">
                        <div class="trackify_input_clear_btn">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                        d="M7 14C3.13401 14 0 10.866 0 7C0 3.13401 3.13401 0 7 0C10.866 0 14 3.13401 14 7C14 10.866 10.866 14 7 14ZM7 6.01006L5.51507 4.52513C5.2417 4.25176 4.79849 4.25176 4.52513 4.52513C4.25176 4.79849 4.25176 5.24171 4.52513 5.51507L6.01006 7L4.52513 8.48491C4.25176 8.75827 4.25176 9.20149 4.52513 9.47485C4.79849 9.74822 5.2417 9.74822 5.51507 9.47486L7 7.98994L8.48491 9.47485C8.75827 9.74821 9.20148 9.74821 9.47485 9.47485C9.74821 9.20148 9.74821 8.75827 9.47485 8.48491L7.98994 7L9.47486 5.51507C9.74822 5.2417 9.74822 4.79849 9.47485 4.52513C9.20149 4.25176 8.75827 4.25176 8.48491 4.52513L7 6.01006Z"
                                        fill="#0B1019" fill-opacity="0.7"></path>
                            </svg>
                        </div>
                        <div class="trackify_form_error">{{$translation->tracking_number_error}}</div>
                    </div>
                    @endif
                    <div class="trackify_form_error" id="apiResError"></div>

                </div>
                <div>
                    <button class="trackify_form_button" >
                        <div>{{$translation->track_btn}}</div>
                    </button>
                </div>
                @else
                    <div>{{$translation->page_not_publish}}</div>
                @endif
            </div>
        </div>
        <div id="orderWrapper" class="trackify_order_wrapper" style="display: none">
            <div class="trackify_order_nav_tab_container">
                <div class="trackify_order_nav_item active"  id="orderTrackingTab" data-target="orderDetail">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#fff" fill-rule="evenodd" clip-rule="evenodd"
                              d="M9.50131 10.6436C8.50723 11.3983 7.26746 11.8462 5.92308 11.8462C2.65185 11.8462 0 9.1943 0 5.92308C0 2.65185 2.65185 0 5.92308 0C9.1943 0 11.8462 2.65185 11.8462 5.92308C11.8462 7.26746 11.3983 8.50723 10.6436 9.50131L13.7634 12.6212C14.0789 12.9366 14.0789 13.448 13.7634 13.7634C13.448 14.0789 12.9366 14.0789 12.6212 13.7634L9.50131 10.6436ZM10.2308 5.92308C10.2308 8.30215 8.30215 10.2308 5.92308 10.2308C3.544 10.2308 1.61538 8.30215 1.61538 5.92308C1.61538 3.544 3.544 1.61538 5.92308 1.61538C8.30215 1.61538 10.2308 3.544 10.2308 5.92308Z">
                        </path>
                    </svg>
                    <div class="title">Track</div>
                </div>
                <div class="trackify_order_nav_item" id="productRecommendationTab" data-target="productRecommendations">
                    <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M6.79123 1.70641C7.2764 0.68966 8.72367 0.689662 9.20884 1.70641L10.6977 4.82663L14.1253 5.27845C15.2422 5.42568 15.6895 6.80212 14.8724 7.57773L12.365 9.95795L12.9945 13.3574C13.1996 14.4652 12.0287 15.3158 11.0386 14.7784L8.00004 13.1293L4.96148 14.7784C3.97134 15.3158 2.80047 14.4652 3.00559 13.3574L3.63507 9.95795L1.12766 7.57773C0.310599 6.80212 0.757832 5.42568 1.87474 5.27845L5.30234 4.82663L6.79123 1.70641Z"
                              fill="#0B1019"></path>
                    </svg>
                    <div class="title">Recommendation</div>
                </div>
            </div>
            <div id="orderDetail" class="trackify_order_detail_container"
                 style="touch-action: pan-x; user-select: none; -webkit-user-drag: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0);">
                <div class="trackify_order_detail_header trackify_order_detail_headerbg">
                    <div class="header">
                        <div class="order_name" >#123</div>
                        <div class="action_content">
                            <div class="btn_box" id="reloadBtnTrack">
                                <svg id="reloadBtnSvg" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                            d="M7.25 0C10.9779 0 14 3.02208 14 6.75C14 7.16421 13.6642 7.5 13.25 7.5C12.8358 7.5 12.5 7.16421 12.5 6.75C12.5 3.85051 10.1495 1.5 7.25 1.5C5.84765 1.5 4.54003 2.05353 3.57506 3H5.25C5.6297 3 5.94349 3.28215 5.99315 3.64823L6 3.75C6 4.1297 5.71785 4.44349 5.35177 4.49315L5.25 4.5H2.25C1.8703 4.5 1.55651 4.21785 1.50685 3.85177L1.5 3.75V3.44647C1.4998 3.43657 1.4998 3.42667 1.5 3.41677V0.75C1.5 0.335786 1.83579 0 2.25 0C2.6297 0 2.94349 0.282154 2.99315 0.648229L3 0.75V1.50499C4.17922 0.548578 5.66829 0 7.25 0Z"
                                            fill="white" fill-opacity="0.95"></path>
                                    <path
                                            d="M1.25 6C1.66421 6 2 6.33579 2 6.75C2 9.6495 4.35051 12 7.25 12C8.65203 12 9.95963 11.4468 10.9249 10.5H9.25C8.8703 10.5 8.55651 10.2178 8.50685 9.85177L8.5 9.75C8.5 9.3703 8.78215 9.05651 9.14823 9.00685L9.25 9H12.25C12.6297 9 12.9435 9.28215 12.9932 9.64823L13 9.75V12.75C13 13.1642 12.6642 13.5 12.25 13.5C11.8703 13.5 11.5565 13.2178 11.5068 12.8518L11.5 12.75V11.995C10.3205 12.9517 8.8314 13.5 7.25 13.5C3.52208 13.5 0.5 10.4779 0.5 6.75C0.5 6.33579 0.835786 6 1.25 6Z"
                                            fill="white" fill-opacity="0.95"></path>
                                </svg>
                            </div>
                            <div class="btn_box " id="cancelBtnTrack">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                            d="M10.5368 11.7489C10.8715 12.0837 11.4142 12.0837 11.7489 11.7489C12.0837 11.4142 12.0837 10.8715 11.7489 10.5368L7.21218 6L11.749 1.46323C12.0837 1.1285 12.0837 0.585787 11.749 0.251052C11.4142 -0.0836833 10.8715 -0.0836833 10.5368 0.251052L6 4.78782L1.46323 0.251051C1.1285 -0.0836837 0.585787 -0.0836838 0.251051 0.251051C-0.0836838 0.585786 -0.0836839 1.1285 0.251051 1.46323L4.78782 6L0.251052 10.5368C-0.0836832 10.8715 -0.0836832 11.4142 0.251052 11.7489C0.585787 12.0837 1.1285 12.0837 1.46323 11.7489L6 7.21218L10.5368 11.7489Z"
                                            fill="white" fill-opacity="0.95"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="status_icon">
                        <svg width="68" height="49" viewBox="0 0 68 49" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path  fill-rule="evenodd" clip-rule="evenodd"
                                  d="M66.682 1.81802C68.4393 3.57538 68.4393 6.42462 66.682 8.18198L27.682 47.182C25.9246 48.9393 23.0754 48.9393 21.318 47.182L1.81802 27.682C0.0606602 25.9246 0.0606602 23.0754 1.81802 21.318C3.57538 19.5607 6.42462 19.5607 8.18198 21.318L24.5 37.636L60.318 1.81802C62.0754 0.0606602 64.9246 0.0606602 66.682 1.81802Z"
                                  ></path>
                        </svg>
                    </div>
                    <div class="status_and_product_content" style="height: 138px;">
                        <div class="status_name">Delivered</div>
                        <div class="trackify_order_items_container" style="top: 68px;">
                            <div class="trackify_order_items_content">
                                <div class="current-package-content">
                                    <div class="content">
                                        <div class="title">{{$translation->package_content}}</div>
                                        <div class="produc_num">0 item(s)</div>
                                    </div>
                                </div>
                                <div class="order_items_list_content">
                                    <div class="product_image_content">
                                        <img
                                                src="https://cdn.shopify.com/s/files/1/0829/5805/7771/files/Frame3316_b08ac2a9-8dfa-45a9-b1c5-350a5986f1ac.jpg?v=1718938320">
                                    </div>
                                    <div class="product_image_content">
                                        <img
                                                src="https://cdn.shopify.com/s/files/1/0829/5805/7771/files/Image_a66efb82-7967-4b67-b85b-9a13725515f3.jpg?v=1718936566">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="trackify_half_round_content" style="top: 188px;"></div>
                <div id="trackingDetail" class="trackify_order_detail_content" style="
            padding-top: 30px; padding-bottom: 20px; overflow: auto;

          ">
                    <div class="trackify_order_tracking_details_content">
                        {{--<div class="trackify_order_desc_wrapper">
                            <div class="trackify_order_desc_left_content">
                                <div class="trackify_track_date_icon_box" style="color: rgb(255, 255, 255); background: #616161;">
                                    <div class="trackify_track_date_text">16</div>
                                    <div class="trackify_track_date_text">Aug</div>
                                </div>
                                <div class="trackify_date_line"></div>
                            </div>
                            <div class="trackify_order_desc_container">
                                <div class="trackify_order_desc_right_content">
                                    <div class="trackify_event_time">
                                        <div>11:51 am</div>
                                    </div>
                                    <div class="trackify_event_detail trackify_event_first_detail">
                                        Evanston, IL, Evanston, IL, Delivered, Left at front door. Signature Service not requested.
                                    </div>
                                </div>
                            </div>
                            <div class="trackify_order_desc_container">
                                <div class="trackify_order_desc_right_content">
                                    <div class="trackify_event_time">
                                        <div>04:44 am</div>
                                    </div>
                                    <div class="trackify_event_detail">NILES, THE, NILES, IL, On FedEx vehicle for delivery</div>
                                </div>
                            </div>
                            <div class="trackify_order_desc_container">
                                <div class="trackify_order_desc_right_content">
                                    <div class="trackify_event_time">
                                        <div>04:40 am</div>
                                    </div>
                                    <div class="trackify_event_detail">NILES, THE, NILES, IL, Arrived at FedEx location</div>
                                </div>
                            </div>
                            <div class="trackify_order_desc_container">
                                <div class="trackify_order_desc_right_content">
                                    <div class="trackify_event_time">
                                        <div>04:38 am</div>
                                    </div>
                                    <div class="trackify_event_detail">NILES, THE, NILES, IL, At local FedEx facility</div>
                                </div>
                            </div>
                        </div>--}}
                    </div>
                    <div class="trackify_order_carrier_container">
                        <div class="trackify_carrier_content">
                           {{-- <div class="logo_box">
                                <img class="shipmentLogo"
                                        src="data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAMAAABHPGVmAAAAk1BMVEUAAABHL5FHL5FHL5FHL5FHL5FHL5H////4spCjl8j84tX+9fHz8vjzeD1HL5FSO5f72MjRy+Po5fFpVaX7z7r6xayXisH1lWb0i1mMfLrc2Orp5vL0gUt0YqzxZSL97OP2nnRdSJ7ybi91Y62Ab7OupM+BcLTFvtyYisH60b72n3SdXXX5u566sdb1kF/lzMyMfbpQ1ZH/AAAAB3RSTlMA0BAw4HDw7PHCHgAAAhRJREFUeF7t2tlO6zAQgOG0tIyz72v3Hc7+/k93nJkipw1qilpbR0fzXyDDBZ+GYCSUsWTj6SjQ1Gg6trCXSaCxyQsar4HWXqUyngSam4ytaaC9qTXSj4ysDrCzn9iuoyjkOIOnNjv2kTd4em/XyBE0dLxCZjqQ2SWyAy3tLhBbD2IzwggjjDwWI4wwwggjjMTE3UYXgez7UhX9DwgjaoRBRAD1KNLETRdInobUcVsDcNojuo8R8OpQfpLGCllH2Dt+dEDmRthqGKEWHhJY0c4kCSwkRPWNDplE1niau3cji45YkEFdIxF9661zNnw0hhBVaMc0bqLIPgIlKeID+xpyAoBDi6QIpB5O1EPcOSrKGEYWtqymY5uay5PmSSGVwFbQVbK77wl6vfbQFnx2T5zt2cjhQcTuIaqcjAq+jGzsjzZ3TqLglRD5EJLQQ7gcupHGQSF+hTkALhrdn1e29X+U6wEEUrqR4NU4EyLhz/hw87dLPXk/9/1M+rcQBJEJaYab94SM+bpzT6pouXRENIBAcfHIk8EbP3ehVIocQ4hyaBIalDpIs9kQEKbXyC8aQF0X3wXIt8LxK/gE8eK2BM4lRSqFtKAveLZkwtprYtnvSPWOh9XVX+GsrITL/zr8owgjjDDCCCOMMMIII0ZeMJt4VW7kpb+B9YU/RhcxjKyUGFmOMbLmY2RhycTqlZklMiPrcH8B/beOPcPt7jMAAAAASUVORK5CYII="
                                        alt="fedex">
                            </div>--}}
                            <div class="content">
                                <div class="title shipmentTitle">AutoTrack</div>
                                <div class="shipmentNumber">0000000</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="productRecommendations" class="trackify_product_recommendations_wrapper"
                 style="display: none; touch-action: pan-x; user-select: none; -webkit-user-drag: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0);">
                <div class="trackify_product_recommendations_header">
                    Recommendation
                    <div class="trackify_close_btn">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                    d="M10.5368 11.7489C10.8715 12.0837 11.4142 12.0837 11.7489 11.7489C12.0837 11.4142 12.0837 10.8715 11.7489 10.5368L7.21218 6L11.749 1.46323C12.0837 1.1285 12.0837 0.585787 11.749 0.251052C11.4142 -0.0836833 10.8715 -0.0836833 10.5368 0.251052L6 4.78782L1.46323 0.251051C1.1285 -0.0836837 0.585787 -0.0836838 0.251051 0.251051C-0.0836838 0.585786 -0.0836839 1.1285 0.251051 1.46323L4.78782 6L0.251052 10.5368C-0.0836832 10.8715 -0.0836832 11.4142 0.251052 11.7489C0.585787 12.0837 1.1285 12.0837 1.46323 11.7489L6 7.21218L10.5368 11.7489Z"
                                    fill="#0B1019" fill-opacity="0.7"></path>
                        </svg>
                    </div>
                </div>
                <div id="productRecommendationsDetail" class="trackify_product_recommendations_container">
                    <div class="trackify_product_recommendations_item_content">
                        <div class="trackify_product_recommendations_img_box"
                             style="background-image: url(https://cdn.shopify.com/s/files/1/0829/5805/7771/files/Frame3315_60bf2520-0cba-4453-8b72-dcaa80fb1f07.jpg?v=1718936354); display: block">
                        </div>
                        <div class="content">
                            <div class="trackify_product_title">PRINTED MINI DRESS</div>

                            <div class="trackify_product_price_container">
                                <div class="trackify_product_price">$121.00</div>
                            </div>
                        </div>
                    </div>
                    <div class="trackify_product_recommendations_item_content">
                        <div class="trackify_product_recommendations_img_box"
                             style="background-image: url(https://cdn.shopify.com/s/files/1/0829/5805/7771/files/Frame3315_60bf2520-0cba-4453-8b72-dcaa80fb1f07.jpg?v=1718936354); display: block">
                        </div>
                        <div class="content">
                            <div class="trackify_product_title">SOFT SHORT JACKET</div>

                            <div class="trackify_product_price_container">
                                <div class="trackify_product_price">$72.00</div>
                            </div>
                        </div>
                    </div>
                    <div class="trackify_product_recommendations_item_content">
                        <div class="trackify_product_recommendations_img_box"
                             style="background-image: url(https://cdn.shopify.com/s/files/1/0829/5805/7771/files/Frame3315_60bf2520-0cba-4453-8b72-dcaa80fb1f07.jpg?v=1718936354); display: block">
                        </div>
                        <div class="content">
                            <div class="trackify_product_title">DOUBLE-BREASTED CROPPED BLAZER</div>

                            <div class="trackify_product_price_container">
                                <div class="trackify_product_price">$137.00</div>
                            </div>
                        </div>
                    </div>
                    <div class="trackify_product_recommendations_item_content">
                        <div class="trackify_product_recommendations_img_box"
                             style="background-image: url(https://cdn.shopify.com/s/files/1/0829/5805/7771/files/Frame3315_60bf2520-0cba-4453-8b72-dcaa80fb1f07.jpg?v=1718936354); display: block">
                        </div>
                        <div class="content">
                            <div class="trackify_product_title">BASIC SATIN SHIRT</div>

                            <div class="trackify_product_price_container">
                                <div class="trackify_product_price">$15.00</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="trackify_collection_recommendations_container"></div>
            </div>
        </div>
    </div>
</div>
<script>
    const  shopdomain="{{$shop_name}}";
    const appUrl="{{env('APP_URL')}}";
    function product_recommendations_function(handle) {

        var productUrl=`https://${shopdomain}/products/${handle}`
        // alert(productUrl);
        // Making a POST API call
        const link = document.createElement('a');
        link.href = productUrl;
        link.target = '_blank';
        link.rel = 'noopener noreferrer'; // for security best practices
        link.click();

        fetch(`${appUrl}/api/save-recommended-click`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                // Include additional headers here if needed, like authorization tokens
            },
            body: JSON.stringify({ shop: shopdomain }) // Send `handle` as `id` in the request body
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json(); // Assuming JSON response
            })
            .then(data => {
                // console.log("API call success:", data); // Log or handle the API response as needed

                // Open product URL in the parent window
                // window.open(productUrl, '_parent');

            })
            .catch(error => {
                // console.error('There was a problem with the API call:', error);
            });
    }
</script>
<script type="module" defer>


    import { TrackballControls } from 'three/addons/controls/TrackballControls.js';
    import { CSS2DRenderer } from 'three/addons/renderers/CSS2DRenderer.js';

    setInputValuesFromUrl();
    // Get URL parameters
    function getUrlParams() {
        const params = new URLSearchParams(window.location.search);
        return {

            trackingNumber: params.get('tracking_number') || ''
        };
    }

    // Set input values from URL parameters
    function setInputValuesFromUrl() {
        const {  trackingNumber } = getUrlParams();

            // console.log('trackingNumber',trackingNumber);


        if(trackingNumber){
            document.getElementById('tracking-number').value = trackingNumber;
            let tf_tracking_form_tab= document.querySelector('.trackify_tab_bar')
            tf_tracking_form_tab.classList.remove('trackify_tab_active_bar');
            document.querySelector('[data-tab="tracking-number"]').classList.add('trackify_tab_active_bar');
            const fields = document.querySelectorAll('.trackify_input_content');
            fields.forEach(field => field.style.display = 'none');
            document.querySelector('[data-field="tracking-number"]').style.display = 'block';

        }
    }
   /* if (typeof Shopify !== 'undefined' && Shopify.shop) {
        shopdomain = Shopify.shop; // Use Shopify object if available
    } else {
        // If Shopify object is not available, check URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const shopurl = urlParams.get('shop'); // Get 'shop' parameter from the URL
        if (shopurl) {
            shopdomain = shopurl; // Use the 'shop' parameter if available
        }
    }*/
    // Function to send the iframe height to the parent window
    function sendHeightToParent() {
        var height = document.documentElement.scrollHeight;
        window.parent.postMessage({ type: 'resizeIframe', height: height }, '*');
    }

    // Function to initialize MutationObserver
    function initMutationObserver() {
        var observer = new MutationObserver(function() {
            sendHeightToParent();
        });

        // Start observing the document for changes
        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true
        });
    }

    // Call the function initially and set up the observer
    sendHeightToParent();
    initMutationObserver();
    // const appUrl="https://phpstack-1329250-4863091.cloudwaysapps.com";

    // console.log("shopdomain",shopdomain)
    const markerSvg = `<svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g filter="url(#filter0_f_2975_5101)">
                                    <ellipse cx="14" cy="24" rx="6" ry="2" fill="#0B1019" fill-opacity="0.3"></ellipse>
                                    </g>
                                    <mask id="path-2-outside-1_2975_5101" maskUnits="userSpaceOnUse" x="5" y="1" width="18" height="23" fill="black">
                                    <rect fill="white" x="5" y="1" width="18" height="23"></rect>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M14 21.7998C14.35 21.7998 21 16.3002 21 10C21 6.13401 17.866 3 14 3C10.134 3 7 6.13401 7 10C7 16.3002 13.65 21.7998 14 21.7998ZM14.0002 12.8002C15.5466 12.8002 16.8002 11.5466 16.8002 10.0002C16.8002 8.4538 15.5466 7.2002 14.0002 7.2002C12.4538 7.2002 11.2002 8.4538 11.2002 10.0002C11.2002 11.5466 12.4538 12.8002 14.0002 12.8002Z"></path>
                                    </mask>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M14 21.7998C14.35 21.7998 21 16.3002 21 10C21 6.13401 17.866 3 14 3C10.134 3 7 6.13401 7 10C7 16.3002 13.65 21.7998 14 21.7998ZM14.0002 12.8002C15.5466 12.8002 16.8002 11.5466 16.8002 10.0002C16.8002 8.4538 15.5466 7.2002 14.0002 7.2002C12.4538 7.2002 11.2002 8.4538 11.2002 10.0002C11.2002 11.5466 12.4538 12.8002 14.0002 12.8002Z" fill="{{$pageData->process_bar_color??"#313131"}}"></path>
                                    <path d="M19.6875 10C19.6875 12.7132 18.2377 15.375 16.6009 17.4495C15.7969 18.4684 14.9828 19.301 14.3565 19.8727C14.0427 20.1592 13.7858 20.3712 13.6101 20.504C13.5199 20.5721 13.4671 20.6072 13.4482 20.6188C13.4369 20.6258 13.4567 20.6126 13.4986 20.5932C13.5196 20.5835 13.5649 20.5633 13.6278 20.5436C13.6767 20.5282 13.8146 20.4873 14 20.4873V23.1123C14.3268 23.1123 14.5766 22.9868 14.6032 22.9745C14.6908 22.9338 14.7677 22.889 14.8243 22.8542C14.9414 22.7822 15.0674 22.6929 15.1925 22.5984C15.4474 22.4058 15.7675 22.1389 16.1263 21.8114C16.8454 21.155 17.7594 20.2189 18.6616 19.0755C20.4373 16.8251 22.3125 13.5869 22.3125 10H19.6875ZM14 4.3125C17.1411 4.3125 19.6875 6.85888 19.6875 10H22.3125C22.3125 5.40913 18.5909 1.6875 14 1.6875V4.3125ZM8.3125 10C8.3125 6.85888 10.8589 4.3125 14 4.3125V1.6875C9.40913 1.6875 5.6875 5.40913 5.6875 10H8.3125ZM14 20.4873C14.1854 20.4873 14.3233 20.5282 14.3722 20.5436C14.4351 20.5633 14.4804 20.5835 14.5014 20.5932C14.5433 20.6126 14.5631 20.6258 14.5518 20.6188C14.5329 20.6072 14.4801 20.5721 14.3899 20.504C14.2142 20.3712 13.9573 20.1592 13.6435 19.8727C13.0172 19.301 12.2031 18.4684 11.3991 17.4495C9.76229 15.375 8.3125 12.7132 8.3125 10H5.6875C5.6875 13.5869 7.56271 16.8251 9.33838 19.0755C10.2406 20.2189 11.1546 21.155 11.8737 21.8114C12.2325 22.1389 12.5526 22.4058 12.8075 22.5984C12.9326 22.6929 13.0586 22.7822 13.1757 22.8542C13.2323 22.889 13.3092 22.9338 13.3968 22.9745C13.4234 22.9868 13.6732 23.1123 14 23.1123V20.4873ZM15.4877 10.0002C15.4877 10.8217 14.8217 11.4877 14.0002 11.4877V14.1127C16.2715 14.1127 18.1127 12.2715 18.1127 10.0002H15.4877ZM14.0002 8.5127C14.8217 8.5127 15.4877 9.17867 15.4877 10.0002H18.1127C18.1127 7.72892 16.2715 5.8877 14.0002 5.8877V8.5127ZM12.5127 10.0002C12.5127 9.17867 13.1787 8.5127 14.0002 8.5127V5.8877C11.7289 5.8877 9.8877 7.72892 9.8877 10.0002H12.5127ZM14.0002 11.4877C13.1787 11.4877 12.5127 10.8217 12.5127 10.0002H9.8877C9.8877 12.2715 11.7289 14.1127 14.0002 14.1127V11.4877Z" fill="white" mask="url(#path-2-outside-1_2975_5101)"></path>
                                    <defs>
                                    <filter id="filter0_f_2975_5101" x="6" y="20" width="16" height="8" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                    <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                                    <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"></feBlend>
                                    <feGaussianBlur stdDeviation="1" result="effect1_foregroundBlur_2975_5101"></feGaussianBlur>
                                    </filter>
                                    </defs>
                                </svg>`;
    let cruntzoom = 260;
    let finalzoom = 150;
    let step = 5;
    let delay = 90; // Delay between each zoom step (milliseconds)
    const storeLocations = @json($storeLocations);

    let Globe,camera;
    fetch(`${appUrl}/js/countries_json.geojson`).then(res => res.json()).then(countries => {

        Globe = new ThreeGlobe()
            .globeImageUrl(`${appUrl}/images/globe_background.png`)
            .atmosphereColor('rgba(255,255,255,0.7)')
            .atmosphereAltitude(0.5) // Increase atmosphere range
            .hexPolygonsData(countries.features)
            .hexPolygonResolution(3)
            .hexPolygonMargin(0.3)
            .hexPolygonUseDots(false)
            .hexPolygonColor(() => '#4eafa5');

// Check if there are store locations before adding htmlElementsData
        if (storeLocations.length > 0) {
            Globe.htmlElementsData(storeLocations)
                .htmlElement(d => {
                    const el = document.createElement('div');
                    el.style.position = 'absolute';
                    el.style.transform = 'translate(-50%, -100%)'; // Adjust the position to match the exact lat/lng location
                    el.style.pointerEvents = 'none'; // Avoid interfering with globe interactions

                    // Create marker element
                    const marker = document.createElement('div');
                    marker.innerHTML = markerSvg;
                    marker.style.color = 'black';
                    marker.style.width = '20px';
                    marker.style.height = '20px';
                    el.appendChild(marker);

                    // Create store name element
                    const nameEl = document.createElement('div');
                    nameEl.textContent = d.name;
                    nameEl.style.fontSize = '12px';
                    nameEl.style.textAlign = 'center';
                    nameEl.style.marginTop = '8px';
                    nameEl.style.background = '#ffffff';
                    nameEl.style.padding = '5px';
                    nameEl.style.borderRadius = '5px';
                    nameEl.style.width = '100%';

                    el.appendChild(nameEl);

                    return el;
                });
        }


// .hexPolygonGeoJsonGeometry("geometry")
// .hexPolygonColor(() => `#${Math.round(Math.random() * Math.pow(2, 24)).toString(16).padStart(6, '0')}`);

// Setup renderer
        const renderers = [new THREE.WebGLRenderer({
            antialias: true,
            alpha: true
        }), new CSS2DRenderer()];
        renderers.forEach((r, idx) => {
            r.setSize(window.innerWidth, window.innerHeight);
// r.setPixelRatio(window.devicePixelRation)
            if (idx > 0) {
// overlay additional on top of main renderer
                r.domElement.style.position = 'absolute';
                r.domElement.style.top = '0px';
                r.domElement.style.pointerEvents = 'none';
            }
            document.getElementById('globeViz').appendChild(r.domElement);
        });
        /*const renderer = new THREE.WebGLRenderer({
        antialias: true,
        alpha: true
        });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(window.devicePixelRation)
        document.getElementById('globeViz').appendChild(renderer.domElement);*/

// Setup scene
        const scene = new THREE.Scene();
// Change background color here
        scene.background = new THREE.Color(0xD6DFE7); // Black background
        scene.add(Globe);
        scene.add(new THREE.AmbientLight(0xd7d9f1,  4));
        const directionalLight = new THREE.DirectionalLight(0xeeeeee, 1.5); // Increased intensity
        directionalLight.position.set(-10, 100, 50).normalize(); // Position the light
        scene.add(directionalLight);

// Setup camera
        camera = new THREE.PerspectiveCamera();
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        camera.position.z = cruntzoom;


// Add camera controls
        const tbControls = new TrackballControls(camera, renderers[0].domElement);
        tbControls.minDistance = 101;
        tbControls.maxDistance = 300;
        tbControls.minPolarAngle = 0; // Limit vertical rotation
        tbControls.maxPolarAngle = 180; // Limit vertical rotation
        tbControls.rotateSpeed = 1;
        tbControls.zoomSpeed = 0.1;

// // Create a map of store locations to globe points
// const storePoints = storeLocations.map(store => {
//     const position = Globe.getCoords(store.lat, store.lng);
//     return {
//         ...store,
//         position: new THREE.Vector3(position.x, position.y, position.z)
//     };
// });
// Update pov when camera moves
        Globe.setPointOfView(camera.position, Globe.position);
        tbControls.addEventListener('change', () => Globe.setPointOfView(camera.position, Globe.position));

// Kick-off renderer
        (function animate() { // IIFE
// Frame cycle
            tbControls.update();

// Add rotation to the Globe
            Globe.rotation.y += 0.0005; // Adjust this value to change rotation speed
            renderers.forEach(r => r.render(scene, camera));
// renderer.render(scene, camera);
            requestAnimationFrame(animate);
        })();
    });




    document.querySelectorAll('.trackify_order_nav_item').forEach(item => {
        item.addEventListener('click', function () {
// Remove active class and reset styles from all tabs
            document.querySelectorAll('.trackify_order_nav_item').forEach(tab => {
                tab.classList.remove('active');
                // tab.style.color = '';
                // tab.style.background = '';
                // tab.querySelector('svg path').style.fill = '#0B1019'; // Reset SVG fill color
            });

// Add active class and styles to the clicked tab
            this.classList.add('active');
            // this.style.color = 'rgb(255, 255, 255)';
            // this.style.background = '#313131';
            // this.querySelector('svg path').style.fill = '#fff'; // Change SVG fill color to white

// Hide all content containers
            document.querySelectorAll('.trackify_order_detail_container, .trackify_product_recommendations_wrapper').forEach(content => {
                content.style.display = 'none';
            });

// Show the content container corresponding to the clicked tab
            const targetId = this.getAttribute('data-target');
            document.getElementById(targetId).style.display = 'flex';
        });
    });

    document.querySelector('.trackify_close_btn').addEventListener('click', function () {
// Simulate a click on the "Track" tab
        document.querySelector('.trackify_order_nav_item[data-target="orderDetail"]').click();
    });


    // document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.trackify_tab_bar');
    const fields = document.querySelectorAll('.trackify_input_content');
    const trackButton = document.querySelector('.trackify_form_button');
    const apiResError = document.getElementById('apiResError');
    const reloadBtnSvg = document.querySelector('#reloadBtnSvg');
    const track_modern_loading = document.querySelector('.track_modern_loading');
    const orderWrapper = document.getElementById('orderWrapper');
    const formWrapper = document.querySelector('.trackify_form_wrapper');

    const mapIframe = document.querySelector('.trackify_map_iframe');
    const globeWrapper = document.querySelector('.trackify_globe_wrapper');
    document.querySelector('#cancelBtnTrack').addEventListener('click', function () {
// Simulate a click on the "Track" tab
        formWrapper.style.display = "flex";

        orderWrapper.style.display = 'none';
        mapIframe.style.zIndex = '0';
        globeWrapper.classList.remove('trackify_globe_hidden_wrapper');
// location.reload();
    });
    document.querySelector('#reloadBtnTrack').addEventListener('click', function () {
// Simulate a click on the "Track" tab
        orderWrapper.style.display = 'none';
        mapIframe.style.zIndex = '0';
        globeWrapper.classList.remove('trackify_globe_hidden_wrapper');
        reloadBtnSvg.classList.add('rotate');
        trackButton.click()
    });
    // Tab click event listener
    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            apiResError.style.display = 'none';
// Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('trackify_tab_active_bar'));

// Add active class to the clicked tab
            this.classList.add('trackify_tab_active_bar');

// Hide all fields
            fields.forEach(field => field.style.display = 'none');

// Show fields related to the active tab
            const activeTab = this.getAttribute('data-tab');
            if (activeTab === 'order-number') {
                document.querySelector('[data-field="order-number"]').style.display = 'block';
                document.querySelector('[data-field="email-phone"]').style.display = 'block';
            } else if (activeTab === 'tracking-number') {
                document.querySelector('[data-field="tracking-number"]').style.display = 'block';
            }
        });
    });

    // Track button click event listener
    trackButton.addEventListener('click', function (event) {
        event.preventDefault(); // Prevent form submission for demo purposes
        apiResError.style.display = 'none';
        apiResError.innerHTML="";
        let allValid = true; // Flag to check overall form validity

        fields.forEach(field => {
            const input = field.querySelector('.trackify_form_field');
            const errorMessage = field.querySelector('.trackify_form_error');

            if (field.style.display !== 'none') { // Validate only visible fields
                if (!input.value.trim()) {
                    errorMessage.style.display = 'block'; // Show error message
                    allValid = false;
                } else {
                    errorMessage.style.display = 'none'; // Hide error message
                }
            }
        });

        if (allValid) {
// Form is valid, proceed with tracking logic
//             console.log('Tracking...');

// Add loading state to the button
            trackButton.disabled = true;
            trackButton.innerHTML = 'Tracking...';

// Determine active tab and set track_type
            const activeTabElement = document.querySelector('.trackify_tab_active_bar');
            const trackType = activeTabElement.getAttribute('data-tab');
            track_modern_loading.style.display = 'flex';
            formWrapper.style.display = "none";
// Prepare the API URL based on active tab (track_type)

            let url;
            if (trackType === 'order-number') {
                const orderNumber = document.querySelector('[data-field="order-number"] .trackify_form_field[data-type="order-number"]');
                const orderEmail = document.querySelector('[data-field="email-phone"] .trackify_form_field[data-type="email-phone"]');
                url = `${ appUrl }/api/search-tracking-number?shop=${shopdomain}&track_type=${encodeURIComponent(trackType)}&email=${encodeURIComponent(orderEmail.value.trim())}&order_number=${encodeURIComponent(orderNumber.value.trim())}`;
            } else if (trackType === 'tracking-number') {
                const trackingNumber = document.querySelector('[data-field="tracking-number"] .trackify_form_field[data-type="tracking-number"]');
                url = `${ appUrl }/api/search-tracking-number?shop=${shopdomain}&track_type=${encodeURIComponent(trackType)}&tracking_number=${encodeURIComponent(trackingNumber.value.trim())}`;
            }

// Make the API call
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    // console.log('Order form is valid. API response:', data);
                    if(data.status=="error"){
                        var errorMsg=data.message;
                        apiResError.style.display = 'block';
                        apiResError.innerHTML=errorMsg;
                        formWrapper.style.display = "flex";
                        return;
                    }

                    let Recommendation=data.recomendation;
                    let Fullfilment=data.fulfillments[0];
                    let status_name= document.querySelector('.status_name');
                    let productRecommendationTab= document.querySelector('#productRecommendationTab');
                    let orderTrackingTab= document.querySelector('#orderTrackingTab');
                    let productRecommendationsDetail= document.querySelector('#productRecommendationsDetail');
                    let order_name=document.querySelector('.order_name');
                    let produc_num=document.querySelector('.produc_num');
                    let order_items_list_content=document.querySelector('.order_items_list_content');
                    let trackify_order_tracking_details_content=document.querySelector('.trackify_order_tracking_details_content');
                    let shipmentTitle=document.querySelector('.shipmentTitle');
                    let shipmentLogo=document.querySelector('.shipmentLogo');
                    let shipmentNumber=document.querySelector('.shipmentNumber');
                    let line_items=JSON.parse(Fullfilment.line_items);
                    let oder_line_items=Fullfilment.order.lineitems;
                    let track_info=JSON.parse(Fullfilment.track_info);
                    const details = track_info?track_info[0]?.location:"";
                    const original_country=Fullfilment?.country;

                    productRecommendationsDetail.innerHTML='';
                    // console.log('track_info',track_info);

                    if (details) {
                        const encodedDetails = encodeURIComponent(details);
// setTimeout(() => {
                        mapIframe.src = `https://www.google.com/maps/embed/v1/place?key=AIzaSyCMG-OWhqs5GTIELSzqQCwyC0dLQWMu81s&q=${encodedDetails}`;
// }, 500); // Small delay before setting the src
                    }  else if(original_country) {
                        mapIframe.src = `https://www.google.com/maps/embed/v1/place?zoom=6&key=AIzaSyCMG-OWhqs5GTIELSzqQCwyC0dLQWMu81s&q=${encodeURIComponent(original_country)}`;
                    } else {
                        mapIframe.src = "https://www.google.com/maps/embed/v1/view?zoom=3&center=20,0&key=AIzaSyCMG-OWhqs5GTIELSzqQCwyC0dLQWMu81s";
                    }
// Use a for loop with setTimeout to simulate gradual zoom
                    for (let i = 0; i <= Math.abs(cruntzoom - finalzoom) / step; i++) {
                        setTimeout(() => {
                            if (cruntzoom > finalzoom) {
                                cruntzoom -= step;  // Decrease the zoom
                            } else if (cruntzoom < finalzoom) {
                                cruntzoom += step;  // Increase the zoom
                            }

// Update the camera's position
                            camera.position.z = cruntzoom;
                            if(cruntzoom==finalzoom) {
                                // if (details) {
                                    globeWrapper.classList.add('trackify_globe_hidden_wrapper');
                                    mapIframe.style.zIndex = '3';
                                // }
                                cruntzoom=260
                                camera.position.z = 260;
                            }
                        }, i * delay);  // Delay each step by `i * delay` milliseconds
                    }
                    orderWrapper.style.display = "block";
                    status_name.innerHTML = track_info[0]?.checkpoint_delivery_status || "Pending";
                    order_name.innerHTML=Fullfilment.tracking_number;
                    shipmentNumber.innerHTML=Fullfilment.tracking_company;
                    shipmentTitle.innerHTML=Fullfilment.tracking_number;
                    // shipmentLogo.src = Fullfilment.carrier_name_base?.picture || Fullfilment.carrier_code_base?.picture || '';
                    if(Recommendation.length>0){
                        // console.log('Recommendation',Recommendation);
                        productRecommendationTab.style.display = 'flex';
                        orderTrackingTab.style.display = 'flex';
                        let recomendedproducts="";
                        Recommendation.forEach((r_product)=>{
                            recomendedproducts+=`<div class="trackify_product_recommendations_item_content" data-handle="${r_product.handle}" onclick="product_recommendations_function('${r_product.handle}');">
    <div class="trackify_product_recommendations_img_box" style="background-image: url(${r_product.image}); display: block">
    </div>
    <div class="content">
        <div class="trackify_product_title">${r_product.title}</div>

        <div class="trackify_product_price_container">
            <div class="trackify_product_price">
                ${r_product?.price ? r_product?.price  : ''}

            </div>
        </div>
    </div>
</div>`;
                        })
                        productRecommendationsDetail.innerHTML=recomendedproducts;
                    }else{
                        productRecommendationTab.style.display = 'none';
                        orderTrackingTab.style.display = 'none';
                    }
                    // console.log('track_info',track_info);

                    let count = 0;
                    let product_images="";
                    let tracking_details_content="";
                    line_items.forEach((item) => {
                        oder_line_items.forEach((odritem) => {
                            if(item.product_id==odritem.product_id){
                                product_images+='<div class="product_image_content">' +
                                    '<img src="'+odritem.image+'">' +
                                    '</div>';
                            }
                        });
                        // console.log("Title:", item.title);
                        count++;
                    });
                    order_items_list_content.innerHTML=product_images;
                    produc_num.innerHTML=count + "item(s)";


// Function to group the tracking info by date
                    const groupByDate = (data) => {
                        return data.reduce((acc, item) => {
                            const d=item.checkpoint_date;

                            const date = new Date(d.split('T')[0]).toLocaleDateString('en-US', {
                                day: '2-digit', month: 'short'
                            });
                            if (!acc[date]) {
                                acc[date] = [];
                            }
                            acc[date].push(item);
                            return acc;
                        }, {});
                    };

// Function to extract time
                    const getTime = (dateStr) => {
                        // Extract the time part (e.g., '12:56:00' from '2024-05-23T12:56:00-05:00')
                        const timePart = dateStr.split('T')[1].split('-')[0]; // '12:56:00'
                        const [hours, minutes] = timePart.split(':');

                        // Convert 24-hour format to 12-hour format with AM/PM
                        let hour = parseInt(hours);
                        const ampm = hour >= 12 ? 'PM' : 'AM';
                        hour = hour % 12 || 12; // Convert hour to 12-hour format
                        return  `${hour}:${minutes} ${ampm}`;
                    };
                    const groupedData = groupByDate(track_info);

                    /*track_info.forEach((trackInfo,index) => {
                    // Adjust the following code according to the actual properties of trackInfo
                    const dateObj = new Date(trackInfo.Date); // Create a Date object from the string
                    const date = dateObj.getDate(); // Get the day of the month (1-31)
                    const month = dateObj.toLocaleString('default', { month: 'short' }); // Get the abbreviated month name (e.g., 'Mar')
                    const time = dateObj.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }); // Get time in 'hh:mm am/pm' format

                    const location = trackInfo.Details;
                    const detail = trackInfo.StatusDescription; // Example, replace with actual trackInfo.detail
                    const firstDetailClass = index === 0 ? 'trackify_event_first_detail' : ''; // Add class only to the first record

                    tracking_details_content += `
                    <div class="trackify_order_desc_wrapper">
                        <div class="trackify_order_desc_left_content">
                            <div class="trackify_track_date_icon_box" style="color: rgb(255, 255, 255); background: #616161;">
                                <div class="trackify_track_date_text">${date}</div>
                                <div class="trackify_track_date_text">${month}</div>
                            </div>
                            <div class="trackify_date_line"></div>
                        </div>
                        <div class="trackify_order_desc_container">
                            <div class="trackify_order_desc_right_content">
                                <div class="trackify_event_time">
                                    <div>${time}</div>
                                </div>
                                <div class="trackify_event_detail ${firstDetailClass}">
                                    ${location}, ${detail}
                                </div>
                            </div>
                        </div>
                    </div>
                    `;
                    });*/
                    Object.keys(groupedData).forEach((date, dateIndex) => {
                        const dateClass =  dateIndex === 0 ? 'active' : ''; // Apply class to the first item on the first date only

                        tracking_details_content += `
<div class="trackify_order_desc_wrapper">
    <div class="trackify_order_desc_left_content">
        <div class="trackify_track_date_icon_box ${dateClass}" >
            <div class="trackify_track_date_text">${date.split(' ')[0]}</div>
            <div class="trackify_track_date_text">${date.split(' ')[1]}</div>
        </div>
        <div class="trackify_date_line"></div>
    </div>
    `;

                        groupedData[date].forEach((trackInfo, index) => {
                            const time = getTime(trackInfo.checkpoint_date);
                            const detailClass = index === 0 && dateIndex === 0 ? 'trackify_event_first_detail' : ''; // Apply class to the first item on the first date only

                            tracking_details_content += `
    <div class="trackify_order_desc_container">
        <div class="trackify_order_desc_right_content">
            <div class="trackify_event_time">
                <div>${time}</div>
            </div>
            <div class="trackify_event_detail ${detailClass}">
                ${trackInfo.location ? trackInfo.location + ', ' : ''} ${trackInfo.tracking_detail}
            </div>
        </div>
    </div>
    `;
                        });

                        tracking_details_content += '</div>'; // Closing the wrapper div for each date
                    });
                    trackify_order_tracking_details_content.innerHTML=tracking_details_content;
// Handle the API response here
                })
                .catch(error => {
                    console.error('API call error:', error);
                })
                .finally(() => {
// Reset loading state after API call is complete
                    trackButton.disabled = false;
                    trackButton.innerHTML = 'Track';
                    reloadBtnSvg.classList.remove('rotate');
                    track_modern_loading.style.display = 'none';

                });


            // console.log('Order form is valid.');
        }
    });

    // Input change event listener to remove error message
    fields.forEach(field => {
        const input = field.querySelector('.trackify_form_field');
        const errorMessage = field.querySelector('.trackify_form_error');

        input.addEventListener('input', function () {
            if (input.value.trim()) {
                errorMessage.style.display = 'none'; // Hide error message on input change
            }
        });
    });
</script>
</body>
</html>


