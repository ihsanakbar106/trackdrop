<style>
    #MainContent {
        background: #fff;
    }

    .tf_tracking_content {
        --heading-h1: #000;
        /* Custom property for heading color */
        --tracking-button-bg-color: #000;
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
        text-align: left !important;
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
        padding: 5px 12px;
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

    .tf_tracking_content .progress-bar-style>.progress-bar-node>svg {
        position: absolute;
        top: -50px;
        left: -12px;
        width: 40px;
        height: 40px;
        fill: #4a4949;
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
        float: left;
        width: 68%;
        box-sizing: border-box;
        padding: 0 16px 16px;
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
    }

    .tf_timeline .timeline-badge {
        float: left;
        position: relative;
        width: 35px;
        height: 35px;
        top: 22px;
        left: 8px;
    }

    .tf_timeline .timeline-badge-userpic {
        width: 35px;
        border: 4px #f5f6fa solid;
        -webkit-border-radius: 50% !important;
        -moz-border-radius: 50% !important;
        border-radius: 50% !important;
    }

    .tf_timeline .timeline-body {
        position: relative;
        padding: 0 12px;
        margin-top: 20px;
        margin-left: 65px;
        background-color: #f5f6fa;
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        -ms-border-radius: 4px;
        -o-border-radius: 4px;
        border-radius: 4px;
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
        float: left;
        width: 100%;
    }

    .tf_timeline .timeline-body-head-caption {
        float: left;
        line-height: 44px;
        color: #aaa;
        font-size: 16px;
    }

    .tf_timeline .timeline-body-head-caption {
        color: #000000;
    }

    .tf_timeline .timeline-body-head-actions {
        float: right;
    }

    .tf_timeline .timeline-body-content {
        font-size: 16px;
        float: left;
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

    .tf_tracking_content>.tf_tracking_right>.tf_tracking_info_parent_ {
        border-radius: 8px;
        border: 1px solid #D9D9D9;
    }

    .tf_tracking_content>.tf_tracking_right>.tf_tracking_info_parent_>.tf_tracking_info_parent {
        margin-block-end: 0;
        display: flex;
        flex-direction: column;
        padding: 0 20px 0 20px;
    }

    .tf_tracking_content>.tf_tracking_right>.tf_tracking_info_parent_>.tf_tracking_info_parent>li {
        margin-bottom: 20px;
        float: left;
        width: 100%;
        list-style: none;
    }

    .tf_tracking_content .tf_tracking_info_title {
        color: #000 !important;
        font-weight: 600;
    }

    .tf_tracking_content>.tf_tracking_right>.tf_tracking_info_parent_>.tf_tracking_info_parent>li>.tf_tracking_info_title {
        font-size: 16px;
        color: #999;
        line-height: 40px;
    }

    .tf_tracking_content>.tf_tracking_right>.tf_tracking_info_parent_>.tf_tracking_info_parent>li>.tf_tracking_info {
        float: left;
        line-height: 30px;
        font-size: 16px;
        font-weight: 400;
        word-break: break-word;
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
</style>


<div class="tf_tracking_content" style="max-width: 1100px; margin: 54px auto; width: 100%;">
    <div class="tf_tracking_form_div">
        <h1 class="tf_tracking_title">TRACK YOUR ORDER</h1>
        <div class="tf_tracking_form_in">
            <!-- Order Number Form -->
            <div class="tf_tracking_form_order">
                <form action="" method="get" class="tf_tracking_form" id="orderForm">
                    <div class="tf_tracking_parent">
                        <div class="tf_tracking_span"><label for="tfs_order_number">Order number</label></div>
                        <div class="tf_tracking_input">
                            <input id="tfs_order_number" class="field__input tr_Dawn_input" type="text" name="order"
                                   placeholder="Order Number">
                            <span class="tf_tracking_alert" style="display: none;">Please enter order number.</span>
                        </div>
                    </div>
                    <div class="tf_tracking_parent">
                        <div class="tf_tracking_span"><label for="tfs_order_email">Email or Phone number</label></div>
                        <div class="tf_tracking_input">
                            <input id="tfs_order_email" class="field__input tr_Dawn_input" type="text" name="email"
                                   placeholder="Email or Phone Number">
                            <span class="tf_tracking_alert" style="display: none;">Please enter your email or phone number.</span>
                        </div>
                    </div>
                    <div class="tf_tracking_button">
                        <button class="button-enter btn button styled-submit" type="button">TRACK</button>
                    </div>
                </form>
            </div>

            <!-- Divider -->
            <div class="tf_tracking_line_center">
                <div class="tf_tracking_line"><img src="//s.trackingmore.com/shopify/images/or-line.svg" alt=""></div>
                <div class="tf_tracking_word">OR</div>
                <div class="tf_tracking_line"><img src="//s.trackingmore.com/shopify/images/or-line.svg" alt=""></div>
            </div>

            <!-- Tracking Number Form -->
            <div class="tf_tracking_form_number">
                <form action="" method="get" class="tf_tracking_form" id="trackingForm">
                    <div class="tf_tracking_parent">
                        <div class="tf_tracking_span"><label for="tfs_track_number">Tracking Number</label></div>
                        <div class="tf_tracking_input">
                            <input id="tfs_track_number" class="field__input tr_Dawn_input" type="text" name="nums"
                                   placeholder="Tracking Number">
                            <span class="tf_tracking_alert" style="display: none;">Please enter tracking number.</span>
                        </div>
                    </div>
                    <div class="tf_tracking_button">
                        <button class="button-enter btn button styled-submit" type="button">TRACK</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="tf_track_copy_right">
            <div>
                <a target="_blank"
                   href="#">Powered
                    by TrackDrop</a>
            </div>
        </div>
    </div>
</div>

<div class="tf_tracking_content" style="max-width: 1100px; margin: 54px auto; width: 100%;">
    <div class="result_loading_frame"></div>
</div>

<script>



    // -------------------- SECOND FORM --------------------

    document.addEventListener('DOMContentLoaded', function () {
        // Common Validation Function
        const appUrl="https://phpstack-1329250-4863091.cloudwaysapps.com";
        const shop_name=Shopify.shop;
        function validateInput(inputElement, errorMessage) {
            if (inputElement.value.trim() === '') {
                inputElement.nextElementSibling.style.display = 'block';
                inputElement.style.border = '1px solid rgba(142,31,11,1)';
                return false;
            } else {
                inputElement.nextElementSibling.style.display = 'none';
                inputElement.style.border = '';
                return true;
            }
        }

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

            document.getElementById('tfs_order_number').value = orderNumber;
            document.getElementById('tfs_order_email').value = orderEmail;
            document.getElementById('tfs_track_number').value = trackingNumber;
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

        // Add loading state to the button
        function setLoading(button, isLoading) {
            if (isLoading) {
                button.disabled = true;
                button.innerHTML = 'Tracking...'; // You can customize this to show a spinner or different text
            } else {
                button.disabled = false;
                button.innerHTML = 'Track'; // Restore the original button text
            }
        }

        // Order Number Form
        const orderTrackButton = document.querySelector('.tf_tracking_form_order .button-enter');
        orderTrackButton.addEventListener('click', function () {
            const orderNumber = document.getElementById('tfs_order_number');
            const orderEmail = document.getElementById('tfs_order_email');
            const trackingNumber = document.getElementById('tfs_track_number');

            const isOrderNumberValid = validateInput(orderNumber, 'Please enter order number.');
            const isOrderEmailValid = validateInput(orderEmail, 'Please enter your email or phone number');

            if (isOrderNumberValid && isOrderEmailValid) {
                setLoading(orderTrackButton, true); // Show loading

                const apiUrl = `${appUrl}/api/search-tracking-number?track_type=order-number&email=${encodeURIComponent(orderEmail.value.trim())}&order_number=${encodeURIComponent(orderNumber.value.trim())}`;

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
                        console.log('Order form is valid. API response:', data);
                        // Handle the API response here
                    })
                    .catch(error => {
                        console.error('API call error:', error);
                    }).finally(() => {
                    setLoading(orderTrackButton, false); // Hide loading
                });;
                console.log('Order form is valid.');
            }
        });

        // Tracking Number Form
        const trackingTrackButton = document.querySelector('.tf_tracking_form_number .button-enter');
        trackingTrackButton.addEventListener('click', function () {
            const orderNumber = document.getElementById('tfs_order_number');
            const orderEmail = document.getElementById('tfs_order_email');
            const trackingNumber = document.getElementById('tfs_track_number');

            const isTrackingNumberValid = validateInput(trackingNumber, 'Please enter tracking number');

            if (isTrackingNumberValid) {
                setLoading(trackingTrackButton, true); // Show loading

                const apiUrl = `${appUrl}/api/search-tracking-number?track_type=tracking_number&email=&order_number=&tracking_number=${encodeURIComponent(trackingNumber.value.trim())}`;

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
                        console.log('Tracking form is valid. API response:', data);
                        // Handle the API response here
                    })
                    .catch(error => {
                        console.error('API call error:', error);
                    }).finally(() => {
                    setLoading(trackingTrackButton, false); // Hide loading
                });;
            }
        });

        // Remove error messages on input change
        document.querySelectorAll('.field__input').forEach(input => {
            input.addEventListener('input', function () {
                validateInput(input, '');
            });
        });
    });
    function widgetConstructor() {
        let content = ``

    }
</script>

