<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Track - Pricing</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* General Styling */
        body {
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            /*height: 100vh;*/
            margin: 0;
            background-color: #f7f9fc;
        }

        /* Centered Content */
        .container {
            text-align: center;
        }

        .header {
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 36px;
            font-weight: bold;
            color: #333;
        }

        /* Logo styling */
        .logo {
            max-width: 150px;
            margin: 20px auto;
        }

        /* Plan Card Styling */
        .plan-card {
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            padding: 25px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 20px;
            /*display: inline-block;*/
        }

        .plan-card:hover {
            /*transform: scale(1.05);*/
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.15);
        }

        .plan-header {
            font-size: 26px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .plan-subheader {
            font-size: 16px;
            color: #6c757d;
            margin-bottom: 15px;
        }

        .price {
            font-size: 32px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 20px;
        }

        .shipping {
            font-size: 16px;
            color: #555;
            margin-bottom: 15px;
        }

        .upgrade-btn {
            background-color: #007bff;
            color: white;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .upgrade-btn:hover {
            background-color: #0056b3;
        }

        /* Responsive Layout */
        @media (max-width: 768px) {
            .plan-card {
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>

<!-- Centered App Name and Logo -->
<div class="container">
    <div class="header">
        <img src="{{asset('autotrack.png')}}" alt="Logo" class="logo">

        <h1>Auto Track - Pricing</h1>
    </div>

    <!-- Logo -->

    <div class="row mt-4">
        <!-- Starter Plan -->
        {{--<div class="col-md-6 col-lg-6 mb-4">
            <div class="plan-card">
                <h3 class="plan-header">{{$starter_plans[0]->name}}</h3>
                <p class="plan-subheader">Perfect for new business</p>
                <p class="price">{{$starter_plans[0]->price==0?"Free":$starter_plans[0]->price}}</p>
                <p class="shipping">Track Shipment per month</p>
                <p class="shipping">10</p>
                <div>
                    <br>
                    <p>Unavailable after quota is exceeded</p>
                    <hr>
                    <p>
                        {!! $starter_plans[0]->terms !!}
                    </p>
                </div>

            </div>
        </div>--}}

        <!-- Growth Plan -->
        <div class="col-md-12 col-lg-12 mb-4">
            <div class="plan-card">
                <h3 class="plan-header">{{$growth_plans[0]->name}}</h3>
                <p class="plan-subheader">Advanced tools for growing brands</p>
                @foreach($growth_plans as $index=> $plan)
                <p class="price growth_plans {{$plan->response_limit}}"  @if($index>0) style="display:none;" @endif >${{$plan->price}}/month</p>
                @endforeach
                <p class="shipping">Track Shipment per month </p>
                <select class="form-control d-inline growth_plans_select" style="width: 50%;">
                    @foreach($growth_plans as $plan)
                        <option value="{{$plan->response_limit}}">{{$plan->response_limit}}</option>
                    @endforeach

                </select>

                @foreach($growth_plans as $index=> $plan)
                    <div class="growth_plans {{$plan->response_limit}}" @if($index>0) style="display:none;" @endif>
                        <br>
                        <p>${{$plan->usage_charges}} per shipment tracking</p>
                        <p>Trial days: {{$plan->trial_days}}</p>
                        <hr>
                        <p>
                            {!! $plan->terms !!}
                        </p>
                    </div>
                @endforeach

            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $('body').on('change','.growth_plans_select',function () {
        $('.growth_plans').hide();
        var val=$(this).val();
        $('.'+val).show();
    })
</script>
</body>
</html>
