<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{--    <title>Document</title>--}}
    <style>
        .container {
            width: 100%;
            max-width: 600px;
            margin: auto;
        }

        .header {
            background: none;
            border: 1px solid lightgrey;
            text-align: center;
            padding: 20px;
            background-color: #fafafa;
        }

        .container-body {
            padding: 20px 20px 0px 20px;
            background-color: #fafafa;
        }
        th{
            border:none;
        }
        td{
            border:none;
        }
        .footer {
            text-align: center;
            padding: 2px 0px;
            background-color: #fafafa;
            color: black;

        }

        .title {
            font-family: Helvetica, Arial, sans-serif;
            text-align: left !important;
            color: black !important;
        }

        .message {
            font-size: 16px;
            line-height: 25px;
            font-family: Helvetica, Arial, sans-serif;
            color: #666666;
        }

        .action {
            display: inline-block;
            margin: auto;
            width: 100%;
            text-align: center;
        }

        .prd-button {
            padding: 15px 25px;
            display: block;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: Helvetica, Arial, sans-serif;
            font-weight: 500;
            background-color: #44ce96;
            color: #fff !important;
            text-decoration: none;
            border: 2px solid transparent;
            width: 50%;
            margin: auto;
        }
        .button1 {padding: 6px 10px;}
        .prd-button:hover {
            background-color: #fff;
            border: 2px solid #000;
            color: #000 !important;
        }

        .contaner_contaner {
            width: 100%;
        }

        #mailsys p {
            color: black;
            font-size: 16px;
        }
        .h3{
            color: black;
            font-size: 24px;
        }
        tr th {
            padding: 12px;
        }
        td{
            padding-left: 10px;
        }
        @media only screen and (max-width: 601px) {
            td[class=column] {
                display: block !important;
                width: 100% !important;
            }
        }
        /*#mailsys p {*/
        /*    color: black;*/
        /*}*/

    </style>

</head>
<body>
<div class="container">
    <div class="contaner_contaner">
        <div class="header">
{{--            <img src="https://phpstack-606044-2006407.cloudwaysapps.com/images/website_logo.png" style="max-width: 280px;">--}}
            <h2 style="text-align: center;">Order Tracking Page</h2>
        </div>
    </div>
    <div class="contaner_contaner">
        <div class="container-body">
            <div class="title">
                <p style="color: black">Your shopify order is {{$order->name}}.</p>
            </div>
            <div>
{{--                <table>--}}
{{--                    <thead>--}}
{{--                    <tr>--}}
{{--                        <th>Tracking#</th>--}}
{{--                        <th>Current Shipping Status</th>--}}
{{--                        <th>Tracking URL</th>--}}
{{--                    </tr>--}}
{{--                    </thead>--}}
{{--                    <tbody>--}}
{{--                    <tr>--}}
{{--                        <td>{{$fulfillment->tracking_number}}</td>--}}
{{--                        <td>{{$fulfillment->shipment_status}}</td>--}}
{{--                        <td>{{$fulfillment->tracking_url}}</td>--}}
{{--                    </tr>--}}
{{--                    </tbody>--}}
{{--                </table>--}}
                <table >
                    <tr>
                        <th class="column">
                            Tracking#
                        </th>
                        <td class="column">
                            {{$fulfillment->tracking_number}}
                        </td>
                    </tr>
                    <tr>
                        <th class="column">
                            Shipping Status
                        </th>
                        <td class="column">
                            {{$fulfillment->shipment_status}}
                        </td>
                    </tr>
                    <tr>
                        <th class="column">
                            Tracking URL
                        </th>
                        <td class="column">
                            <a href="{{'https://'.$user_shop->shop.'/apps/view/track-order/'.$fulfillment->tracking_number}}" target="_blank"><button class="button button1">View Tracking</button></a>
                        </td>
                    </tr>
                </table>
            </div>

        </div>
    </div>
</body>
</html>
