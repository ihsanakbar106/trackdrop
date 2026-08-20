
<style>
    #MainContent {
        background: #fff;
    }

    .tf_tracking_content {
        --heading-h1: #000;
        /* Custom property for heading color */
        --tracking-button-bg-color: {{ $section->settings->theme_color }};
        /* Custom property for button background color */
        --tracking-button-text-color: #fff;
        /* Custom property for button text color */
    }

    /* Combined selectors for applying color */
    .tf_tracking_content,
    .tf_tracking_content h1 {
        color: var(--heading-h1);
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_title {
        font-size: 26px;
        font-style: normal;
        font-weight: 400;
        line-height: 32px;
        margin-bottom: 48px;
        margin-top: 0px
    }

    .tf_tracking_content .tf_tracking_form_div .tf_tracking_form_in_tabs h1.tf_tracking_title {
        text-align: center;
    }

    .tf_tracking_form_tabs {
        width: 100%;
        position: relative;
        cursor: pointer;
        white-space: normal;
        overflow-wrap: break-word;
        word-break: break-word;
        margin-bottom: 24px;
    }

    .tf_tracking_above {
        text-align: center;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_above {
        padding: 20px 0;
        white-space: pre-line;
        word-break: break-word;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs {
        border: 1px solid rgba(0, 0, 0, 0.12);
        border-radius: 12px;
        clear: both;
        padding: 40px 20px;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }

    @media screen and (max-width: 810px) {
        .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs {
            border: none
        }
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_form_order_tabs {
        width: 100%;
        -webkit-box-sizing: border-box;
    }

    .tf_tracking_form {
        text-align: center !important;
        display: block !important;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_form_order_tabs>.tf_tracking_form>.tf_tracking_parent {
        margin-bottom: 24px;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_form_order_tabs>.tf_tracking_form>.tf_tracking_parent>.tf_tracking_input {
        width: 100%;
        position: relative;
    }

    .tr_Dawn_input {
        padding: .8rem 1.5rem !important;
    }

    .tf_tracking_form input {
        display: inline-block !important;
    }

    .tf_tracking_input input {
        border: 1px solid rgba(0, 0, 0, 0.16);
        color: rgba(0, 0, 0, 1);
    }

    .tf_tracking_input input:focus {
        outline: none;
        box-shadow: none;
        border: 1px solid rgba(30, 30, 30, 1);
        background-color: #ffffff;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_form_order_tabs>.tf_tracking_form>.tf_tracking_parent>.tf_tracking_input input {
        width: 100%;
        height: 52px;
        font-size: 14px;
        font-weight: 400;
        line-height: 20px;
        padding: 16px !important;
        border-radius: 8px;
        font-style: normal;
        margin-top: 4px;
        background: #ffffff;
        box-sizing: border-box;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_form_order_tabs>.tf_tracking_form>.tf_tracking_parent>.tf_tracking_input .tf_tracking_alert {
        color: rgba(142, 31, 11, 1);
        display: none;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_form_order_tabs>.tf_tracking_form>.tf_tracking_parent {
        margin-bottom: 24px;
    }

    .tf_tracking_button button {
        background-color: var(--tracking-button-bg-color);
        color: var(--tracking-button-text-color);
        font-size: 14px;
        font-style: normal;
        font-weight: 400;
        line-height: 20px;
        border-radius: 8px;
        padding: 16px;
        box-shadow: 0px -1px 0px 0px rgba(0, 0, 0, 0.20) inset, 0px 1px 0px 0px rgba(0, 0, 0, 0.08);
        border: 0;
        cursor: pointer;
    }

    .tf_tracking_button button::before,
    .tf_tracking_button button::after {
        content: unset;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_line_center {
        display: inline-block;
        text-align: center;
        vertical-align: top;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_line_center {
        width: 6%;
        padding: 20px 12px 53px;
        -webkit-box-sizing: border-box;
    }

    @media screen and (max-width: 810px) {
        .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_line_center {
            display: block;
            width: 100%;
            padding: 20px 12px;
        }
    }

    @media screen and (max-width: 810px) {
        .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_line_center {
            margin-top: 1rem;
            display: block;
            vertical-align: middle;
        }
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_line_center>.tf_tracking_line {
        /* height: 4.5rem; */
        overflow: hidden;
    }

    @media screen and (max-width: 810px) {
        .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_line_center>.tf_tracking_line {
            background: repeating-linear-gradient(90deg, white 0em, #999 0.35em, white 0, white 0.5em);
        }
    }

    @media screen and (max-width: 810px) {
        .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_line_center>.tf_tracking_line {
            display: none;
        }
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_line_center>.tf_tracking_word {
        padding: 6px 0;
    }

    @media screen and (max-width: 810px) {
        .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_line_center>.tf_tracking_word {
            display: block;
            text-align: center;
        }
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_form_number_tabs {
        width: 100%;
        -webkit-box-sizing: border-box;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_form_number_tabs>.tf_tracking_form>.tf_tracking_parent {
        margin-bottom: 24px;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_form_number_tabs>.tf_tracking_form>.tf_tracking_parent>.tf_tracking_input {
        width: 100%;
        position: relative;
        margin-top: 4px;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_form_number_tabs>.tf_tracking_form>.tf_tracking_parent>.tf_tracking_input input {
        width: 100%;
        height: 52px;
        font-size: 14px;
        font-weight: 400;
        line-height: 20px;
        padding: 16px !important;
        border-radius: 8px;
        font-style: normal;
        margin-top: 4px;
        background: #ffffff;
        box-sizing: border-box;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in_tabs>.tf_tracking_form_number_tabs>.tf_tracking_form>.tf_tracking_parent>.tf_tracking_input .tf_tracking_alert {
        color: rgba(142, 31, 11, 1);
        display: none;
    }

    .tf_track_copy_right {
        display: flex;
        flex-direction: row;
        justify-content: flex-end;
        padding: 0 !important;
    }

    .tf_track_copy_right div {
        padding: 10px 0;
    }

    .tf_track_copy_right div a {
        color: #cdcdcd !important;
        text-decoration: none;
        font-size: 12px;
    }

    .tf_tracking_form_tab {
        width: 50%;
        height: 52px;
        display: flex;
        justify-content: center;
        align-items: center;
        color: rgba(30, 30, 30, 0.6);
        text-align: center;
        word-wrap: break-word;
        padding: 0;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
    }

    .tf_tracking_form_tab:first-child {
        padding-right: 2px;
    }

    .tf_tracking_form_tab.tf_tracking_form_tab_active {
        color: rgba(0, 0, 0, 1);
        font-weight: 600;
        border-bottom: 2px solid rgba(0, 0, 0, 1);
    }



    /* TRACKING SECOND FORM */
    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_title {
        font-size: 26px;
        font-style: normal;
        font-weight: 400;
        line-height: 32px;
    }

    .tf_tracking_content .tf_tracking_form_div h1.tf_tracking_title {
        text-align: center;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in {
        border: 1px solid rgba(0, 0, 0, 0.12);
        border-radius: 12px;
        clear: both;
        padding: 30px 20px;
        display: flex;
        flex-wrap: wrap;
    }

    @media screen and (max-width: 810px) {
        .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in {
            border: none
        }
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_form_order {
        width: 47%;
        padding: 20px 100px;
        -webkit-box-sizing: border-box;
    }

    @media screen and (max-width: 1024px) {
        .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_form_order {
            padding: 20px 80px;
        }
    }

    @media screen and (max-width: 810px) {
        .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_form_order {
            display: block;
            width: 100%;
            padding: 0;
        }
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_form_order>.tf_tracking_form>.tf_tracking_parent {
        margin-bottom: 24px;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_form_order>.tf_tracking_form>.tf_tracking_parent>.tf_tracking_input {
        width: 100%;
        position: relative;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_form_order>.tf_tracking_form>.tf_tracking_parent>.tf_tracking_input input {
        width: 100%;
        height: 52px;
        font-size: 14px;
        font-weight: 400;
        line-height: 20px;
        padding: 16px !important;
        border-radius: 8px;
        font-style: normal;
        margin-top: 4px;
        background: #ffffff;
        box-sizing: border-box;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_form_order>.tf_tracking_form>.tf_tracking_parent>.tf_tracking_input .tf_tracking_alert {
        color: rgba(142, 31, 11, 1);
        display: none;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_form_order>.tf_tracking_form>.tf_tracking_parent {
        margin-bottom: 24px;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_line_center {
        display: inline-block;
        text-align: center;
        vertical-align: top;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_line_center {
        width: 6%;
        padding: 20px 12px 53px;
        -webkit-box-sizing: border-box;
    }

    @media screen and (max-width: 810px) {
        .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_line_center {
            display: block;
            width: 100%;
            padding: 0;
            vertical-align: middle;
        }
    }


    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_line_center>.tf_tracking_line {
        /* height: 4.5rem; */
        overflow: hidden;
    }

    @media screen and (max-width: 810px) {
        .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_line_center>.tf_tracking_line {
            background: repeating-linear-gradient(90deg, white 0em, #999 0.35em, white 0, white 0.5em);
        }
    }

    @media screen and (max-width: 810px) {
        .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_line_center>.tf_tracking_line {
            display: none;
        }
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_line_center>.tf_tracking_word {
        padding: 6px 0;
    }

    @media screen and (max-width: 810px) {
        .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_line_center>.tf_tracking_word {
            display: block;
            text-align: center;
        }
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_form_number {
        width: 47%;
        padding: 20px 100px 96px;
        -webkit-box-sizing: border-box;
    }

    @media screen and (max-width: 1024px) {
        .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_form_number {
            padding: 20px 80px 96px;
        }
    }

    @media screen and (max-width: 810px) {
        .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_form_number {
            display: block;
            width: 100%;
            padding: 0;
        }
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_form_number>.tf_tracking_form>.tf_tracking_parent {
        margin-bottom: 24px;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_form_number>.tf_tracking_form>.tf_tracking_parent>.tf_tracking_input {
        width: 100%;
        position: relative;
        margin-top: 4px;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_form_number>.tf_tracking_form>.tf_tracking_parent>.tf_tracking_input input {
        width: 100%;
        height: 52px;
        font-size: 14px;
        font-weight: 400;
        line-height: 20px;
        padding: 16px !important;
        border-radius: 8px;
        font-style: normal;
        margin-top: 4px;
        background: #ffffff;
        box-sizing: border-box;
    }

    .tf_tracking_content>.tf_tracking_form_div>.tf_tracking_form_in>.tf_tracking_form_number>.tf_tracking_form>.tf_tracking_parent>.tf_tracking_input .tf_tracking_alert {
        color: rgba(142, 31, 11, 1);
        display: none;
    }


    .tf_track_copy_right {
        display: flex;
        flex-direction: row;
        justify-content: flex-end;
    }

    @media screen and (max-width: 810px) {
        .tf_track_copy_right {
            padding: 0 20px !important
        }
    }

    .tf_track_copy_right div {
        padding: 10px 0;
    }

    .tf_track_copy_right div a {
        color: #cdcdcd !important;
        text-decoration: none;
        font-size: 12px;
    }


    .tf_tracking_content>.result_loading_frame {
        min-height: 100px;
        display: flex;
        flex-direction: column;
    }

    @media screen and (max-width: 600px) {
        .tf_tracking_content>.result_loading_frame {
            padding: 0 15px;
        }
    }

    .tf_tracking_result {
        padding: 0px 80px;
    }
    .tf_tracking_result_title {
        font-size: 24px !important;
        font-weight: 600;
        line-height: 32px;
        color: rgba(0, 0, 0, 1);
        text-align: center
    }

    .tf_tracking_content>.result_loading_frame>.tf_tracking_result {
        margin-bottom: 20px;
        float: left;
        width: 100%;
        padding: 0 15px;
    }

    @media screen and (max-width: 600px) {
        .tf_tracking_content>.result_loading_frame>.tf_tracking_result {
            padding: 0;
        }
    }

    .tf_tracking_content .progress-bar-style {
        width: 100%;
        height: 50px;
        position: relative;
        margin: 70px auto 50px auto;
    }

    @media screen and (max-width: 810px) {
        .tf_tracking_content .progress-bar-style {
            display: none;
        }
    }

    .tf_tracking_content .progress-bar-style div {
        width: 100%;
        height: 8px;
        position: absolute;
        top: 50%;
        left: 0;
        margin-top: -20px;
        background: #C6C6C6;
    }

    .tf_tracking_content .progress-bar-style div span {
        position: absolute;
        display: inline-block;
        height: 8px;
        width: 100%;
        left: 0;
    }

    .tf_tracking_content .progress-bar-style>.progress-bar-node {
        position: absolute;
        top: 0;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        margin-left: -1px;
        background: #C6C6C6;
    }
    .tf_tracking_content .progress-bar-style .progress-bar-range{
        background: {{ $section->settings->theme_color }};
    }
    .tf_tracking_content .progress-bar-style>.progress-bar-node.active {
        background: {{ $section->settings->theme_color }};
    }

    .tf_tracking_content .progress-bar-style>.progress-bar-node>svg {
        position: absolute;
        top: -50px;
        left: -12px;
        width: 40px;
        height: 40px;
        fill: #c6c6c6;
    }
    .tf_tracking_content .progress-bar-style>.progress-bar-node>svg.svg_active {
        fill: {{ $section->settings->theme_color }};
    }

    .tf_tracking_content .progress-bar-style>.progress-bar-node>svg {
        stroke: none;
    }

    .tf_tracking_content .progress-bar-style .progress-bar-node:nth-of-type(1)>svg {
        left: 0;
    }

    .tf_tracking_content .progress-bar-style>.progress-bar-node>span {
        display: table;
        position: absolute;
        top: 24px;
        left: 50%;
        transform: translateX(-50%);
        text-align: center;
        white-space: nowrap;
    }

    .tf_tracking_content .progress-bar-style .progress-bar-node:nth-of-type(1)>span {
        text-align: left;
        transform: none;
        left: 0;
    }

    .tf_tracking_content .progress-bar-style>.progress-bar-node>span>* {
        word-break: normal !important;
        display: block;
        overflow-wrap: normal;
    }

    .tf_tracking_content .progress-bar-style>.progress-bar-node>span>b {
        font-size: 16px;
    }

    .tf_tracking_content .progress-bar-style .progress-bar-node:last-child {
        text-align: right;
        margin-left: -16px;
    }

    .tf_tracking_content .progress-bar-style .progress-bar-node:last-child>svg {
        right: 0;
        left: -18px;
    }

    .tf_tracking_content .progress-bar-style .progress-bar-node:last-child>span {
        text-align: right;
        transform: none;
        left: initial;
        right: 0;
    }


    /* MOBILE PROGRESS BAR */
    .tf_tracking_content .progress-bar-mobile-style {
        min-height: 400px;
        position: relative;
        float: left;
        display: none;
    }

    @media screen and (max-width: 810px) {
        .tf_tracking_content .progress-bar-mobile-style {
            display: block;
        }
    }

    .tf_tracking_content .progress-bar-mobile-left {
        background: #C6C6C6;
        height: calc(100% - 72px);
        position: absolute;
        width: 8px;
        left: 5px;
        top: 35px;
    }

    .tf_tracking_content .progress-bar-mobile-left>span {
        float: left;
        width: 8px;
    }

    .tf_tracking_content .progress-bar-mobile-style>.progress-bar-mobile-list {
        width: 100%;
        float: left;
        margin: 12px 0;
    }

    .tf_tracking_content .progress-bar-mobile-style>.progress-bar-mobile-list>.progress-bar-mobile-node {
        height: 18px;
        width: 18px;
        float: left;
        border-radius: 50%;
        position: relative;
        top: 22px;
        background: #C6C6C6;
    }

    .tf_tracking_content .progress-bar-mobile-style>.progress-bar-mobile-list svg {
        width: 40px;
        height: 40px;
        fill: #4a4949;
    }

    .tf_tracking_content .progress-bar-mobile-style>.progress-bar-mobile-list>svg {
        margin: 12px 0 12px 25px;
        float: left;
    }

    .tf_tracking_content .progress-bar-mobile-style>.progress-bar-mobile-list>svg {
        stroke: none;
    }

    .tf_tracking_content .progress-bar-mobile-style>.progress-bar-mobile-list>.progress-bar-mobile-content {
        float: left;
        padding-left: 25px;
        padding-top: 7px;
    }

    .tf_tracking_content .progress-bar-mobile-style>.progress-bar-mobile-list>.progress-bar-mobile-content>* {
        display: block;
    }

    .tf_tracking_content .progress-bar-mobile-style>.progress-bar-mobile-list>.progress-bar-mobile-content>b {
        line-height: 30px;
        font-size: 16px;
    }

    .tf_tracking_content .progress-bar-mobile-style>.progress-bar-mobile-list>.progress-bar-mobile-content>span {
        font-size: 80%;
    }

    .tf_tracking_content .progress-bar-mobile-style>.progress-bar-mobile-list.progress-bar-mobile-disabled>svg {
        fill: #C6C6C6;
    }

    .tf_tracking_content .progress-bar-mobile-style>.progress-bar-mobile-list.progress-bar-mobile-disabled>.progress-bar-mobile-content>b {
        line-height: 54px;
        color: #C6C6C6;
    }
    .PP-GoogleMap{
        height: 100%;
        position: relative;
        width: 100%;
    }
    .PP-GoogleMap-PlaceCard[data-v-6cd44d7e] {
        word-wrap: break-word;
        background-color: #fff;
        border-radius: 2px;
        box-shadow: 0 1px 4px -1px rgba(0, 0, 0, .3);
        left: 0;
        margin: 10px;
        min-height: 122px;
        overflow: hidden;
        padding: 16px 12px;
        position: absolute;
        text-overflow: ellipsis;
        top: 0;
        width: 302px;
    }

    .PP-GoogleMap-PlaceCard__LocationType[data-v-6cd44d7e] {
        color: #000;
        font-size: 16px;
        margin: 0;
    }

    .PP-GoogleMap-PlaceCard__LocationAddress[data-v-6cd44d7e] {
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
        color: #5b5b5b;
        display: -webkit-box;
        font-size: 16px;
        margin-bottom: 0;
        margin-top: 12px;
        overflow: hidden;
        text-overflow: ellipsis;
    }






    .tf_tracking_content .tf_tracking_left {
        /*float: left;*/
        width: 68%;
        box-sizing: border-box;
        padding: 0 16px 0px 0px;
        border-radius: 8px;
    }

    @media screen and (max-width: 810px) {
        .tf_tracking_content .tf_tracking_left {
            width: 100%;
        }
    }

    .tf_tracking_result_title,
    .tf_tracking_left>h2 {
        font-size: 25px;
        margin-block-start: 16px;
    }

    .tf_timeline {
        margin: 0;
        padding: 0;
        position: relative;
        list-style: none;
    }

    .tf_timeline:before {
        content: '';
        position: absolute;
        display: block;
        width: 4px;
        background: #f5f6fa;
        top: 0;
        bottom: 0;
        margin-left: 24px;
    }

    .tf_timeline .timeline-item {
        margin: 0;
        padding: 0;
        display: flex;
         flex-shrink: 1;
        position: relative;
        margin-right: -8px;
        margin-left: 0px;
        gap:10px;
    }

    .tf_timeline .timeline-badge {
        /*float: left;*/
        position: relative;
        width: 35px;
        height: 35px;
        top: 22px;
        left: 8px;
        flex-grow: 0;
    }

    .tf_timeline .timeline-badge-userpic {
        width: 35px;
        height: 35px;
        border: 4px #f5f6fa solid;
        background: #f5f6fa;
        -webkit-border-radius: 50% !important;
        -moz-border-radius: 50% !important;
        border-radius: 50% !important;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .tf_timeline .timeline-badge-userpic svg {
        fill: {{ $section->settings->theme_color }};
    }
    .tf_timeline .timeline-badge-userpic.active {
        border: 4px {{ $section->settings->theme_color }} solid;
        background: {{ $section->settings->theme_color }};
    }
    .tf_timeline .timeline-badge-userpic.active svg {
        fill: #ffffff;
    }
    .tf_timeline .timeline-body {
        position: relative;
        padding: 0 12px;
        margin: 10px;
        /*margin-left: 65px;*/
        background-color: #f5f6fa;
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        -ms-border-radius: 4px;
        -o-border-radius: 4px;
        border-radius: 4px;
        /*flex-grow: 1;*/
    }

    .tf_timeline .timeline-body {
        background-color: #ffffff;
    }

    .tf_timeline .timeline-body:before,
    .tf_timeline .timeline-body:after {
        content: " ";
        display: table;
    }

    .tf_timeline .timeline-body-arrow {
        display: none;
        position: absolute;
        top: 25px;
        left: -14px;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 14px 14px 14px 0;
        border-color: transparent #f5f6fa transparent transparent;
    }

    .tf_timeline .timeline-body-arrow {
        border-color: transparent #ffffff transparent transparent;
    }

    .tf_timeline .timeline-body-head {
        /*float: left;*/
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .tf_timeline .timeline-body-head-caption {
        /*float: left;*/
        line-height: 44px;
        color: #aaa;
        font-size: 16px;
    }

    .tf_timeline .timeline-body-head-caption {
        color: #000000;
    }

    .tf_timeline .timeline-body-head-actions {
        /*float: right;*/
    }

    .tf_timeline .timeline-body-content {
        font-size: 16px;
        /*float: left;*/
        width: 100%;
        margin-bottom: 12px;
        line-height: 32px;
        word-break: break-word;
    }

    .tf_timeline .timeline-body:after {
        clear: both;
    }


    .tf_tracking_content>.tf_tracking_right {
        float: right;
        width: 30%;
    }

    @media screen and (max-width: 810px) {
        .tf_tracking_content .tf_tracking_right {
            width: 100%;
        }
    }

    .tf_tracking_content .tf_tracking_right .tf_tracking_info_parent_ {
        border-radius: 8px;
        /*border: 1px solid #D9D9D9;*/
        border: none;
    }

    .tf_tracking_content .tf_tracking_right .tf_tracking_info_parent_ .tf_tracking_info_parent {
        margin: 0;
        display: flex;
        flex-direction: column;
        padding: 20px 0 0 0;
    }

    .tf_tracking_content .tf_tracking_right .tf_tracking_info_parent_ .tf_tracking_info_parent li {
        margin-bottom: 20px;
        /* float: left; */
        width: 100%;
        list-style: none;
    }

    .tf_tracking_content .tf_tracking_info_title {
        color: #000 !important;
        font-weight: 600;
    }

    .tf_tracking_content .tf_tracking_right .tf_tracking_info_parent_ .tf_tracking_info_parent li .tf_tracking_info_title {
        font-size: 16px;
        line-height: 40px;
        color: #000 !important;
        font-weight: 600;
    }

    .tf_tracking_content .tf_tracking_right .tf_tracking_info_parent_ .tf_tracking_info_parent li .tf_tracking_info {
        /*float: left;*/
        line-height: 30px;
        font-size: 16px;
        font-weight: 400;
        word-break: break-word;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .tf_tracking_content .tf_tracking_right .tf_tracking_info_parent_ .tf_tracking_info_parent li .tf_tracking_info .tf_tracking_carrier_img{
        cursor: pointer;
        height: 80px;
        width: 80px;
        background: #ddd;
        background-size: 100% 100%;
        border-radius: 4px;
    }
    .tf_tracking_content .tf_tracking_right .tf_tracking_info_parent_ .tf_tracking_info_parent li .tf_tracking_info.tf_tracking_product_info {
        display: flex;
        flex-direction: column;
        align-items: unset;
    }
    .tf_tracking_product_info .tf_tracking_product_show {
        display: flex;
        gap: 10px;
    }
    .tf_tracking_product_info .tf_tracking_product_show .tf_tracking_info_img{
        position: relative;
        /* float: left; */
        width: 60px;
        padding: 2px 0;
        max-height: 75px;
    }
    .tf_tracking_product_info .tf_tracking_product_show .tf_tracking_info_img span{
        display: inline-block;
        position: absolute;
        top: -10%;
        right: -10%;
        padding: 2px;
        background-color: #808080;
        color: #fff;
        border-radius: 50%;
        font-size: 12px;
        line-height: 12px;
        padding: 3px 6px;
    }
    .tf_tracking_product_info .tf_tracking_product_show .tf_tracking_info_img_span{

    }



    /* FULFILLMENTS TABS -------------------- */

    .tf_tracking_fulfillments_tabs {
        width: 100%;
        position: relative;
        cursor: pointer;
        white-space: normal;
        overflow-wrap: break-word;
        word-break: break-word;
        margin-bottom: 24px;
    }

    .tf_tracking_fulfillments_tab {
        width: 50%;
        height: 52px;
        display: flex;
        justify-content: center;
        align-items: center;
        color: rgba(30, 30, 30, 0.6);
        text-align: center;
        word-wrap: break-word;
        padding: 0;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
    }

    .tf_tracking_fulfillments_tab:first-child {
        padding-right: 2px;
    }

    .tf_tracking_fulfillments_tab.active {
        color: rgba(0, 0, 0, 1);
        font-weight: 600;
        border-bottom: 2px solid rgba(0, 0, 0, 1);
    }
    .button-enter {
        position: relative;
        display: inline-block;
    }
    .button-enter .spinner {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        border: 4px solid transparent;
        border-top: 4px solid #fff;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        animation: spin 1s linear infinite;
        display: none; /* Initially hidden */
    }

    .button-enter.loading .spinner {
        display: inline-block; /* Show spinner when loading */
    }

    .button-enter.loading .button-text {
        visibility: hidden; /* Hide button text when loading */
    }

    /* Spinner animation */
    @keyframes spin {
        0% {
            transform: translate(-50%, -50%) rotate(0deg);
        }
        100% {
            transform: translate(-50%, -50%) rotate(360deg);
        }
    }
</style>

<div class="tf_tracking_content" style="max-width: 656.594px; margin: 54px auto; width: 100%;">
    <div class="tf_tracking_form_div">
        <div class="tf_tracking_form_in_tabs">
            <h1 class="tf_tracking_title">TRACK YOUR ORDER</h1>
            <div class="tf_tracking_form_tabs" style="display: flex;">
                <div id="ONtab" class="yq-body-16-400 tf_tracking_form_tab tf_tracking_form_tab_active"
                     style="color: rgba(0,0,0,1); border-color: rgb(35, 35, 35); font-size: 16px!important; line-height: 22px;"
                     data-tab="order">
                    Order Number
                </div>
                <div id="TNtab" class="yq-body-16-400 tf_tracking_form_tab"
                     style="color: rgba(0,0,0,1); border-color: rgba(0,0,0,1); font-size: 16px!important; line-height: 22px;"
                     data-tab="tracking">
                    Tracking Number
                </div>
            </div>

            <div class="tf_tracking_form_order_tabs">
                <form id="orderForm" action="" method="get" class="tf_tracking_form">
                    <div class="tf_tracking_parent">
                        <div class="tf_tracking_span"><label for="tfs_order_number_tabs">Order number</label></div>
                        <div class="tf_tracking_input">
                            <input id="tfs_order_number_tabs" class="field__input tr_Dawn_input" type="text" name="order"
                                   placeholder="Enter your order number">
                            <span class="tf_tracking_alert" style="display: none;">Please enter order number.</span>
                        </div>
                    </div>
                    <div class="tf_tracking_parent">
                        <div class="tf_tracking_span"><label for="tfs_order_email_tabs">Email or Phone number</label></div>
                        <div class="tf_tracking_input">
                            <input id="tfs_order_email_tabs" class="field__input tr_Dawn_input" type="text" name="email"
                                   placeholder="Enter your email or phone number">
                            <span class="tf_tracking_alert" style="display: none;">Please enter your email or phone number</span>
                        </div>
                    </div>
                    <div class="tf_tracking_button">
                        <button class="button-enter btn button styled-submit" type="button" style="width: 100%">

                            <span class="button-text">TRACK</span>
                            <span class="spinner"></span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="tf_tracking_form_number_tabs" style="display: none;">
                <form id="trackingForm" action="" method="get" class="tf_tracking_form">
                    <div class="tf_tracking_parent">
                        <div class="tf_tracking_span"><label for="tfs_track_number_tabs">Tracking Number</label></div>
                        <div class="tf_tracking_input">
                            <input id="tfs_track_number_tabs" class="field__input tr_Dawn_input" type="text" name="nums"
                                   placeholder="Enter your tracking number">
                            <span class="tf_tracking_alert" style="display: none;">Please enter tracking number</span>
                        </div>
                    </div>
                    <div class="tf_tracking_button">
                        <button class="button-enter btn button styled-submit" type="button" style="width: 100%">
                            <span class="button-text">TRACK</span>
                            <span class="spinner"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="tf_track_copy_right">
            <div>
                <a target="_blank" href="#">Powered
                    by AutoTrack</a>
            </div>
        </div>
    </div>
</div>


<div class="tf_tracking_content" style="max-width: 1100px; margin: 54px auto; width: 100%;">
    <div class="result_loading_frame"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const appUrl="https://track-drop.test";
        const shop_name='elias-project.myshopify.com';
        // const shop_name=Shopify.shop;
        // const appUrl="https://app.theautotrack.com";
        // const appUrl="https://phpstack-1329250-4863091.cloudwaysapps.com";

        const tabs = document.querySelectorAll('.tf_tracking_form_tab');
        const orderForm = document.querySelector('.tf_tracking_form_order_tabs');
        const trackingForm = document.querySelector('.tf_tracking_form_number_tabs');
        const orderTrackButton = orderForm.querySelector('.button-enter');
        const trackingTrackButton = trackingForm.querySelector('.button-enter');

        // Get URL parameters
        function getUrlParams() {
            const params = new URLSearchParams(window.location.search);
            return {
                orderNumber: params.get('order_number') || '',
                orderEmail: params.get('email') || '',
                trackingNumber: params.get('tracking_number') || ''
            };
        }

        // Set input values from URL parameters
        function setInputValuesFromUrl() {
            const { orderNumber, orderEmail, trackingNumber } = getUrlParams();

            document.getElementById('tfs_order_number_tabs').value = orderNumber;
            document.getElementById('tfs_order_email_tabs').value = orderEmail;
            document.getElementById('tfs_track_number_tabs').value = trackingNumber;
            let tf_tracking_form_tab= document.querySelector('.tf_tracking_form_tab')
            tf_tracking_form_tab.classList.remove('tf_tracking_form_tab_active');
            if(trackingNumber){
                document.querySelector('.tf_tracking_form_order_tabs').style.display = 'none';
                document.querySelector('.tf_tracking_form_number_tabs').style.display = 'block';
                document.getElementById('TNtab').classList.add('tf_tracking_form_tab_active');
            }else{
                document.querySelector('.tf_tracking_form_order_tabs').style.display = 'block';
                document.querySelector('.tf_tracking_form_number_tabs').style.display = 'none';
                document.getElementById('ONtab').classList.add('tf_tracking_form_tab_active');
            }
        }

        // Update URL with form input values
        function updateUrlParams(params, removeKeys = []) {
            const url = new URL(window.location.href);
            Object.keys(params).forEach(key => url.searchParams.set(key, params[key]));

            // Remove specified keys from the URL
            removeKeys.forEach(key => url.searchParams.delete(key));

            window.history.replaceState({}, '', url);
        }

        // Call this function to set input values on page load
        setInputValuesFromUrl();

        // Tab switching logic
        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                tabs.forEach(tab => tab.classList.remove('tf_tracking_form_tab_active'));
                this.classList.add('tf_tracking_form_tab_active');

                const target = this.getAttribute('data-tab');

                if (target === 'order') {
                    orderForm.style.display = 'block';
                    trackingForm.style.display = 'none';
                } else if (target === 'tracking') {
                    orderForm.style.display = 'none';
                    trackingForm.style.display = 'block';
                }
            });
        });

        // Add loading state to the button
        function setLoading(button, isLoading) {
            if (isLoading) {
                button.disabled = true;
                button.classList.add('loading');
                // button.innerHTML = 'Loading...'; // You can customize this to show a spinner or different text
            } else {
                button.disabled = false;
                button.classList.remove('loading');
                // button.innerHTML = 'Track'; // Restore the original button text
            }
        }
        function formatDate(dateString) {
            if (!dateString) return "";
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }
        function getValues(data) {

                setTimeout(function() {
                    // Get the current scroll position
                    const currentScroll = window.scrollY || document.documentElement.scrollTop;

                    // Smoothly scroll a bit further (300px down)
                    window.scrollTo({
                        top: currentScroll + 500,  // Scroll 300px more
                        behavior: 'smooth'         // Smooth scroll behavior
                    });
                }, 500);
            // Define the progress stages
            const progressArray = ['ordered', 'order_ready', 'in_transit', 'out_for_delivery', 'delivered'];
            const singleValue = 100 / progressArray.length;


            // Extract shipment status
            const status = data?.shipment_status;

            // Calculate progress value based on status
            const progressValue = (progressArray?.indexOf(status) + 1) * singleValue;

            // Select the result frame element
            const resultFrame = document.querySelector('.result_loading_frame');

            // Update the result frame with shipment status and progress
            resultFrame.innerHTML = data.length ? `
      <div class="tf_tracking_fulfillments_tabs" style="display: flex;">
      ${data.map((item, index) => `<div
          class="yq-body-16-400 tf_tracking_fulfillments_tab  ${index === 0 ? 'active' : ''}"
          style="color: rgba(0,0,0,1); border-color: rgb(35, 35, 35); font-size: 16px!important; line-height: 22px;"
          data-tab="${index}"
        >
          ${item.name}
        </div>`).join('')}
      </div>
          <div class="tf_tracking_form_contents">
      ${data.map((item, index) => {
                let line_items=JSON.parse(item.line_items);
                let oder_line_items=item.order.lineitems;
                let track_info=JSON.parse(item.track_info);
                let track_info_complete=JSON.parse(item.tracking_complete_info);
                const milestone_date = track_info_complete?track_info_complete[0]?.origin_info?.milestone_date:null;
                const details = track_info?track_info[0]?.location:"";
                const original_country=item?.country;
                const s_status=item?.shipment_status;
                let progress_percent=0;
                if(s_status=="transit"){
                    progress_percent=30;
                }else if(s_status=="out for delivery"){
                    progress_percent=60;
                }else if(s_status=="delivered"){
                    progress_percent=100;
                }
                // console.log('progress_percent',progress_percent);

                let orderdDate=null;
                let transitDate=null;
                let outDeliveryDate=null;
                let deliveredDate=null;
                if(milestone_date){
                    orderdDate=milestone_date?.inforeceived_date;
                    transitDate=milestone_date?.pickup_date;
                    outDeliveryDate=milestone_date?.outfordelivery_date;
                    deliveredDate=milestone_date?.delivery_date;
                }
                // console.log('original_country',original_country);
                // console.log('details',details);
                let mapIframeSrc = "";
                if (details) {
                    mapIframeSrc = `https://www.google.com/maps/embed/v1/place?zoom=6&key=AIzaSyCMG-OWhqs5GTIELSzqQCwyC0dLQWMu81s&q=${encodeURIComponent(details)}`;
                } else if(original_country) {
                    mapIframeSrc = `https://www.google.com/maps/embed/v1/place?zoom=6&key=AIzaSyCMG-OWhqs5GTIELSzqQCwyC0dLQWMu81s&q=${encodeURIComponent(original_country)}`;
                } else {
                    mapIframeSrc = "https://www.google.com/maps/embed/v1/view?zoom=3&center=20,0&key=AIzaSyCMG-OWhqs5GTIELSzqQCwyC0dLQWMu81s";
                }
                return `
        <div
          class="tf_tracking_form_content"
          data-content="${index}"
          style="display: ${index === 0 ? 'block' : 'none'};"
        >
        <h1 class="tf_tracking_result_title">Your order is ${item?.shipment_status_t || item?.shipment_status}</h1>
        <div class="tf_tracking_result">
      <div class="progress-bar-style">
        <div>
          <span class="progress-bar-range" style="width:${progress_percent}%"></span>
        </div>
        <span class="progress-bar-node ${(progress_percent<=100 && progress_percent>=0 )?"active":""}" style="left:0%;font-size:80%;">
          <svg class="${(progress_percent<=100 && progress_percent>=0 )?"svg_active":""}" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512">
            <circle cx="7" cy="22" r="2"/><circle cx="17" cy="22" r="2"/><path d="M23,3H21V1a1,1,0,0,0-2,0V3H17a1,1,0,0,0,0,2h2V7a1,1,0,0,0,2,0V5h2a1,1,0,0,0,0-2Z"/><path d="M21.771,9.726a.994.994,0,0,0-1.162.806A3,3,0,0,1,17.657,13H5.418l-.94-8H13a1,1,0,0,0,0-2H4.242L4.2,2.648A3,3,0,0,0,1.222,0H1A1,1,0,0,0,1,2h.222a1,1,0,0,1,.993.883l1.376,11.7A5,5,0,0,0,8.557,19H19a1,1,0,0,0,0-2H8.557a3,3,0,0,1-2.829-2H17.657a5,5,0,0,0,4.921-4.112A1,1,0,0,0,21.771,9.726Z"/>
          </svg>
          <span><b>Ordered</b> <span>${formatDate(orderdDate)}</span></span>
        </span>
        <span class="progress-bar-node ${(progress_percent<=100 && progress_percent>=30 )?"active":""}" style="left:30%;font-size:80%;">
          <svg class="${(progress_percent<=100 && progress_percent>=30 )?"svg_active":""}" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
            <path d="m19,5h-2.101c-.465-2.279-2.484-4-4.899-4h-7C2.243,1,0,3.243,0,6v9c0,1.881,1.309,3.452,3.061,3.877-.038.204-.061.412-.061.623,0,1.93,1.57,3.5,3.5,3.5s3.5-1.57,3.5-3.5c0-.169-.017-.335-.041-.5h4.082c-.024.165-.041.331-.041.5,0,1.93,1.57,3.5,3.5,3.5s3.5-1.57,3.5-3.5c0-.211-.023-.419-.061-.623,1.752-.425,3.061-1.996,3.061-3.877v-5c0-2.757-2.243-5-5-5Zm3,5v1h-5v-4h2c1.654,0,3,1.346,3,3ZM2,15V6c0-1.654,1.346-3,3-3h7c1.654,0,3,1.346,3,3v11H4c-1.103,0-2-.897-2-2Zm6,4.5c0,.827-.673,1.5-1.5,1.5s-1.5-.673-1.5-1.5c0-.19.039-.356.093-.5h2.814c.054.144.093.31.093.5Zm9.5,1.5c-.827,0-1.5-.673-1.5-1.5,0-.19.039-.356.093-.5h2.814c.054.144.093.31.093.5,0,.827-.673,1.5-1.5,1.5Zm2.5-4h-3v-4h5v2c0,1.103-.897,2-2,2Zm-15.707-6.192c-.391-.391-.391-1.023,0-1.414s1.023-.391,1.414,0l1.402,1.402c.346.346.91.346,1.256,0l2.919-2.995c.386-.395,1.021-.402,1.414-.018.396.386.403,1.019.018,1.415l-2.928,3.003c-.568.568-1.312.852-2.054.852s-1.478-.281-2.039-.843l-1.402-1.402Z"/>
          </svg>
          <span><b>In&nbsp;Transit</b> <span>${formatDate(transitDate)}</span></span>
        </span>
        <span class="progress-bar-node ${(progress_percent<=100 && progress_percent>=60 )?"active":""}" style="left:60%;font-size:80%;">
          <svg class="${(progress_percent<=100 && progress_percent>=60 )?"svg_active":""}" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512">
            <path d="M12,6a4,4,0,1,0,4,4A4,4,0,0,0,12,6Zm0,6a2,2,0,1,1,2-2A2,2,0,0,1,12,12Z"/><path d="M12,24a5.271,5.271,0,0,1-4.311-2.2c-3.811-5.257-5.744-9.209-5.744-11.747a10.055,10.055,0,0,1,20.11,0c0,2.538-1.933,6.49-5.744,11.747A5.271,5.271,0,0,1,12,24ZM12,2.181a7.883,7.883,0,0,0-7.874,7.874c0,2.01,1.893,5.727,5.329,10.466a3.145,3.145,0,0,0,5.09,0c3.436-4.739,5.329-8.456,5.329-10.466A7.883,7.883,0,0,0,12,2.181Z"/>
          </svg>
          <span><b>Out&nbsp;for&nbsp;Delivery</b> <span>${formatDate(outDeliveryDate)}</span></span>
        </span>
        <span class="progress-bar-node ${(progress_percent==100 )?"active":""}" style="left:100%;font-size:80%;">
          <svg
           class="${(progress_percent==100 )?"svg_active":""}"
            xmlns="http://www.w3.org/2000/svg"
            id="Layer_1"
            data-name="Layer 1"
            viewBox="0 0 24 24"
            width="512"
            height="512"
          >
            <path d="m18.214,9.098c.387.394.381,1.027-.014,1.414l-4.426,4.345c-.783.768-1.791,1.151-2.8,1.151-.998,0-1.996-.376-2.776-1.129l-1.899-1.867c-.394-.387-.399-1.02-.012-1.414.386-.395,1.021-.4,1.414-.012l1.893,1.861c.776.75,2.001.746,2.781-.018l4.425-4.344c.393-.388,1.024-.381,1.414.013Zm5.786,2.902c0,6.617-5.383,12-12,12S0,18.617,0,12,5.383,0,12,0s12,5.383,12,12Zm-2,0c0-5.514-4.486-10-10-10S2,6.486,2,12s4.486,10,10,10,10-4.486,10-10Z"/>
          </svg>
          <span><b>Delivered</b><span>${formatDate(deliveredDate)}</span></span>
        </span>
      </div>
    </div>
        <div style="display: flex;  margin-top: 20px">
            <div
                    class="tf_tracking_left"
            >
                <ul class="tf_tracking_result_parent tf_timeline">
                ${track_info.map((trackInfo, index )=> {
                    const d=trackInfo.checkpoint_date;

                    const dateObj = new Date(d.split('T')[0]);
                    const date = dateObj.getDate();
                    const month = dateObj.toLocaleString('default', { month: 'short' });
                    return `
                    <li>
                      <div class="timeline-item">
                        <div class="timeline-badge">
<div class="timeline-badge-userpic ${index === 0 ? 'active' : ''}">
                        <svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512">
            <path d="M12,6a4,4,0,1,0,4,4A4,4,0,0,0,12,6Zm0,6a2,2,0,1,1,2-2A2,2,0,0,1,12,12Z"></path><path d="M12,24a5.271,5.271,0,0,1-4.311-2.2c-3.811-5.257-5.744-9.209-5.744-11.747a10.055,10.055,0,0,1,20.11,0c0,2.538-1.933,6.49-5.744,11.747A5.271,5.271,0,0,1,12,24ZM12,2.181a7.883,7.883,0,0,0-7.874,7.874c0,2.01,1.893,5.727,5.329,10.466a3.145,3.145,0,0,0,5.09,0c3.436-4.739,5.329-8.456,5.329-10.466A7.883,7.883,0,0,0,12,2.181Z"></path>
          </svg>
</div>
<!--                          <img class="timeline-badge-userpic" loading="lazy" src="https://phpstack-1329250-4863091.cloudwaysapps.com/images/delivery_image.png">-->
                        </div>
                        <div class="timeline-body">
                          <div class="timeline-body-arrow"></div>
                          <div class="timeline-body-head">
                            <div class="timeline-body-head-caption">
                              <span>${date} ${month}</span>
                            </div>
                            <div class="timeline-body-head-actions"></div>
                          </div>
                          <div class="timeline-body-content">
                            <span class="font-grey-cascade"> ${trackInfo.location ? trackInfo.location + ', ' : ''} ${trackInfo.tracking_detail}</span>
                          </div>
                        </div>
                      </div>
                    </li>`;
                }).join('')}
                </ul>
            </div>
            <div
                    class="tf_tracking_right"
            >
                <div class="tf_tracking_info_parent_" >
                    <div data-v-c939869c="" style="height: 400px;">
                        <div
                                class="PP-GoogleMap"
                                data-v-6cd44d7e=""
                                data-v-c939869c="">
                            <iframe
                                    src="${mapIframeSrc}"
                                    width="100%"
                                    height="100%"
                                    style="border:0;"
                                    allowfullscreen="true"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    data-v-6cd44d7e
                            ></iframe>

                        </div>
                    </div>
                    <ul class="tf_tracking_info_parent">
                        <li>
                            <div
                                    class="tf_tracking_info_title">
                                <span>Carrier</span>
                            </div>
                            <div
                                    class="tf_tracking_info">

                                <div
                                        class="tf_tracking_carrier_info">
                                    <div class="tf_tracking_carrier_top" style="cursor: pointer;"><span>${item.tracking_company}</span></div>
                                    <div class="tf_tracking_carrier_bottom">
                                        <span>${item.tracking_number}</span>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="tf_tracking_info_title" >
                                <span>Product (s)</span>
                            </div>
                            <div
                                    class="tf_tracking_info tf_tracking_product_info">
                            ${line_items.map(lineItem =>{
                    var product_image="";
                    oder_line_items.forEach((odritem) => {
                        if(lineItem.product_id==odritem.product_id){
                            product_image=odritem.image;
                        }
                    });

                    return `
                                <div class="tf_tracking_product_show">
                                    <div
                                            class="tf_tracking_info_img">
                  <span>${lineItem.quantity}</span
                  ><img
                                                width="60"
                                                alt="product"
                                                style="max-height: 75px;"
                                                loading="lazy"
                                                src="${product_image}"
                                        >
                                    </div>
                                    <div
                                            class="tf_tracking_info_img_span">
                  <span
                          title="x1 Wedding Bride Bouquet"
                          style="
                      overflow: hidden;
                      display: -webkit-box;
                      -webkit-box-orient: vertical;
                      -webkit-line-clamp: 3;
                      line-height: 24px;
                    "
                  >${lineItem.name}</span
                  >
                                    </div>
                                </div>
                                `}).join('')}
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        </div>`}).join('')}
    </div>
` : `<h1 class="tf_tracking_result_title">Could Not Find Order</h1>`;


            // Add click event listeners for tabs
            document.querySelectorAll('.tf_tracking_fulfillments_tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    const tabIndex = tab.getAttribute('data-tab');
                    document.querySelectorAll('.tf_tracking_fulfillments_tab').forEach(tab => {
                        tab.classList.remove('active');
                    });
                    tab.classList.add('active');
                    document.querySelectorAll('.tf_tracking_form_content').forEach(content => {
                        content.style.display = content.getAttribute('data-content') === tabIndex ? 'block' : 'none';
                    });
                });
            });
        }

        // Function to get SVG based on status
        function getStatusSVG(status) {
            switch (status) {
                case 'ordered':
                    return `<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512">
                        <circle cx="7" cy="22" r="2" />
                        <circle cx="17" cy="22" r="2" />
                        <path d="M23,3H21V1a1,1,0,0,0-2,0V3H17a1,1,0,0,0,0,2h2V7a1,1,0,0,0,2,0V5h2a1,1,0,0,0,0-2Z" />
                        <path d="M21.771,9.726a.994.994,0,0,0-1.162.806A3,3,0,0,1,17.657,13H5.418l-.94-8H13a1,1,0,0,0,0-2H4.242L4.2,2.648A3,3,0,0,0,1.222,0H1A1,1,0,0,0,1,2h.222a1,1,0,0,1,.993.883l1.376,11.7A5,5,0,0,0,8.557,19H19a1,1,0,0,0,0-2H8.557a3,3,0,0,1-2.829-2H17.657a5,5,0,0,0,4.921-4.112A1,1,0,0,0,21.771,9.726Z" />
                    </svg>`;
                // Add cases for other statuses
                case 'order_ready':
                    return `<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" height="512" viewBox="0 0 24 24" width="512">
                        <path d="m19 0h-14a5.006 5.006 0 0 0 -5 5v14a5.006 5.006,0 0 0 5 5h14a5.006 5.006,0 0 0 5-5v-14a5.006 5.006,0 0 0 -5-5zm3 5h-7v-3h4a3 3 0 0 1 3 3zm-11-3h2v5a1 1 0 0 1 -2 0zm-6 0h4v3h-7a3 3 0 0 1 3-3zm14 20h-14a3 3 0 0 1 -3-3v-12h7a3 3 0 0 0 6 0h7v12a3 3 0 0 1 -3 3zm1-3a1 1 0 0 1 -1 1h-3a1 1 0 0 1 0-2h3a1 1 0 0 1 1 1z" />
                    </svg>`;
                case 'in_transit':
                    return `<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
            <path d="m19,5h-2.101c-.465-2.279-2.484-4-4.899-4h-7C2.243,1,0,3.243,0,6v9c0,1.881,1.309,3.452,3.061,3.877-.038.204-.061.412-.061.623,0,1.93,1.57,3.5,3.5,3.5s3.5-1.57,3.5-3.5c0-.169-.017-.335-.041-.5h4.082c-.024.165-.041.331-.041.5,0,1.93,1.57,3.5,3.5,3.5s3.5-1.57,3.5-3.5c0-.211-.023-.419-.061-.623,1.752-.425,3.061-1.996,3.061-3.877v-5c0-2.757-2.243-5-5-5Zm3,5v1h-5v-4h2c1.654,0,3,1.346,3,3ZM2,15V6c0-1.654,1.346-3,3-3h7c1.654,0,3,1.346,3,3v11H4c-1.103,0-2-.897-2-2Zm6,4.5c0,.827-.673,1.5-1.5,1.5s-1.5-.673-1.5-1.5c0-.19.039-.356.093-.5h2.814c.054.144.093.31.093.5Zm9.5,1.5c-.827,0-1.5-.673-1.5-1.5,0-.19.039-.356.093-.5h2.814c.054.144.093.31.093.5,0,.827-.673,1.5-1.5,1.5Zm2.5-4h-3v-4h5v2c0,1.103-.897,2-2,2Zm-15.707-6.192c-.391-.391-.391-1.023,0-1.414s1.023-.391,1.414,0l1.402,1.402c.346.346.91.346,1.256,0l2.919-2.995c.386-.395,1.021-.402,1.414-.018.396.386.403,1.019.018,1.415l-2.928,3.003c-.568.568-1.312.852-2.054.852s-1.478-.281-2.039-.843l-1.402-1.402Z"/>
          </svg>`;
                case 'out_for_delivery':
                    return `<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512">
            <path d="M12,6a4,4,0,1,0,4,4A4,4,0,0,0,12,6Zm0,6a2,2,0,1,1,2-2A2,2,0,0,1,12,12Z"/><path d="M12,24a5.271,5.271,0,0,1-4.311-2.2c-3.811-5.257-5.744-9.209-5.744-11.747a10.055,10.055,0,0,1,20.11,0c0,2.538-1.933,6.49-5.744,11.747A5.271,5.271,0,0,1,12,24ZM12,2.181a7.883,7.883,0,0,0-7.874,7.874c0,2.01,1.893,5.727,5.329,10.466a3.145,3.145,0,0,0,5.09,0c3.436-4.739,5.329-8.456,5.329-10.466A7.883,7.883,0,0,0,12,2.181Z"/>
          </svg>`;
                case 'delivered':
                    return `<svg
            xmlns="http://www.w3.org/2000/svg"
            id="Layer_1"
            data-name="Layer 1"
            viewBox="0 0 24 24"
            width="512"
            height="512"
          >
            <path d="m18.214,9.098c.387.394.381,1.027-.014,1.414l-4.426,4.345c-.783.768-1.791,1.151-2.8,1.151-.998,0-1.996-.376-2.776-1.129l-1.899-1.867c-.394-.387-.399-1.02-.012-1.414.386-.395,1.021-.4,1.414-.012l1.893,1.861c.776.75,2.001.746,2.781-.018l4.425-4.344c.393-.388,1.024-.381,1.414.013Zm5.786,2.902c0,6.617-5.383,12-12,12S0,18.617,0,12,5.383,0,12,0s12,5.383,12,12Zm-2,0c0-5.514-4.486-10-10-10S2,6.486,2,12s4.486,10,10,10,10-4.486,10-10Z"/>
          </svg>`;
                default:
                    return ``;
            }
        }

        // Utility function to capitalize the first letter of a string
        function capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }

        // Validation logic for Order Number form
        orderTrackButton.addEventListener('click', function () {
            const orderNumber = document.getElementById('tfs_order_number_tabs');
            const orderEmail = document.getElementById('tfs_order_email_tabs');
            const trackingNumber = document.getElementById('tfs_track_number_tabs');

            let valid = true;

            if (orderNumber.value.trim() === '') {
                valid = false;
                orderNumber.nextElementSibling.style.display = 'block';
                orderNumber.style.border = '1px solid rgba(142,31,11,1)';
            } else {
                orderNumber.nextElementSibling.style.display = 'none';
                orderNumber.style.border = '';
            }

            if (orderEmail.value.trim() === '') {
                valid = false;
                orderEmail.nextElementSibling.style.display = 'block';
                orderEmail.style.border = '1px solid rgba(142,31,11,1)';
            } else {
                orderEmail.nextElementSibling.style.display = 'none';
                orderEmail.style.border = '';
            }

            if (valid) {
                setLoading(orderTrackButton, true); // Show loading

                const apiUrl = `${appUrl}/api/search-tracking-number?shop=${encodeURIComponent(shop_name)}&track_type=order-number&email=${encodeURIComponent(orderEmail.value.trim())}&order_number=${encodeURIComponent(orderNumber.value.trim())}`;


                // Clear orderNumber and orderEmail fields
                trackingNumber.value = '';

                // Update URL with the input values
                updateUrlParams({
                    email: orderEmail.value.trim(),
                    order_number: orderNumber.value.trim()
                }, ['tracking_number']);

                fetch(apiUrl)
                    .then(response => response.json())
                    .then(data => {
                        // Handle the API response here
                        if(data.status=="error"){
                            const resultFrame = document.querySelector('.result_loading_frame');
                            resultFrame.innerHTML=data.message;
                            return;

                        }
                        newData = data.fulfillments
                        getValues(newData)
                    })
                    .catch(error => {
                        console.error('API call error:', error);

                    })
                    .finally(() => {
                        setLoading(orderTrackButton, false); // Hide loading

                    });

                // console.log('Order form is valid.');
            }
        });

        // Validation logic for Tracking Number form
        trackingTrackButton.addEventListener('click', function () {
            const orderNumber = document.getElementById('tfs_order_number_tabs');
            const orderEmail = document.getElementById('tfs_order_email_tabs');
            const trackingNumber = document.getElementById('tfs_track_number_tabs');

            if (trackingNumber.value.trim() === '') {
                trackingNumber.nextElementSibling.style.display = 'block';
                trackingNumber.style.border = '1px solid rgba(142,31,11,1)';
            } else {
                trackingNumber.nextElementSibling.style.display = 'none';
                trackingNumber.style.border = '';

                setLoading(trackingTrackButton, true); // Show loading

                const apiUrl = `${appUrl}/api/search-tracking-number?shop=${encodeURIComponent(shop_name)}&track_type=tracking_number&email=&order_number=&tracking_number=${encodeURIComponent(trackingNumber.value.trim())}`;


                // Clear orderNumber and orderEmail fields
                orderNumber.value = '';
                orderEmail.value = '';

                // Update URL with the input values and remove order_number and email
                updateUrlParams(
                    { tracking_number: trackingNumber.value.trim() },
                    ['order_number', 'email']
                );

                fetch(apiUrl)
                    .then(response => response.json())
                    .then(data => {
                        if(data.status=="error"){
                            const resultFrame = document.querySelector('.result_loading_frame');
                            resultFrame.innerHTML=data.message;
                            return;

                        }
                        // Handle the API response here
                        newData = data.fulfillments
                        getValues(newData)
                    })
                    .catch(error => {
                        console.error('API call error:', error);
                    })
                    .finally(() => {
                        setLoading(trackingTrackButton, false); // Hide loading
                    });
            }
        });

        // Remove error messages on input change and reset border
        const orderNumber = document.getElementById('tfs_order_number_tabs');
        const orderEmail = document.getElementById('tfs_order_email_tabs');
        const trackingNumber = document.getElementById('tfs_track_number_tabs');

        orderNumber.addEventListener('input', function () {
            if (orderNumber.value.trim() !== '') {
                orderNumber.nextElementSibling.style.display = 'none';
                orderNumber.style.border = '';
            }
        });

        orderEmail.addEventListener('input', function () {
            if (orderEmail.value.trim() !== '') {
                orderEmail.nextElementSibling.style.display = 'none';
                orderEmail.style.border = '';
            }
        });

        trackingNumber.addEventListener('input', function () {
            if (trackingNumber.value.trim() !== '') {
                trackingNumber.nextElementSibling.style.display = 'none';
                trackingNumber.style.border = '';
            }
        });
    });
    function widgetConstructor() {
        let content = ``

    }
</script>



