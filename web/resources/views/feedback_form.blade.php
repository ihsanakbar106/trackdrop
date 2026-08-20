<!DOCTYPE html>
<html lang="en">
<head>
    <title>True Reviews</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Font Awesome -->
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        rel="stylesheet"
    />
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap"
        rel="stylesheet"
    />
    <!-- MDB -->
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.1/mdb.min.css"
        rel="stylesheet"
    />
    <style>
        .feedback-stars li {
            list-style: none;
            cursor: pointer;
        }
    </style>
    <style>
        .feedback-stars .fa-star {
            cursor: pointer;
            color: #484949 !important; /* Default color */
        }

        .feedback-stars .fa-star.hovered,
        .feedback-stars .fa-star.selected {
            color: #00CDFC !important; /* Hover and selected color */

        }
    </style>
</head>
<body>

<div class="container  mt-5  px-lg-5">
    @if($feedback == null)
        @if(isset($order))
            <div class="mx-0 mx-sm-auto feedback-row">
                <form id="submit-form" class="px-2"
                      action="{{route('feedback-submit',$order->shopify_order_id)}}">
                    @csrf
                    <h2 class="text-center">Your Feedback Matters – {{$order->name}}</h2>
                    <p class="text-center mb-1"><strong>How do you rate product price?</strong></p>
                    <ul class="h2 feedback-stars d-flex rating justify-content-center pb-2 pl-0"
                        data-mdb-rating-init
                        data-mdb-toggle="rating" data-feedback-type="price">
                        <li><i class="far fa-star fa-sm text-primary" title="Bad" data-value="1"></i></li>
                        <li><i class="far fa-star fa-sm text-primary" title="Poor" data-value="2"></i></li>
                        <li><i class="far fa-star fa-sm text-primary" title="OK" data-value="3"></i></li>
                        <li><i class="far fa-star fa-sm text-primary" title="Good" data-value="4"></i></li>
                        <li><i class="far fa-star fa-sm text-primary" title="Excellent" data-value="5"></i></li>
                    </ul>

                    <p class="text-center mb-1"><strong>How do you rate product quality?</strong></p>
                    <ul class="h2 feedback-stars d-flex rating justify-content-center pb-2 pl-0"
                        data-mdb-rating-init
                        data-mdb-toggle="rating" data-feedback-type="quality">
                        <li><i class="far fa-star fa-sm text-primary" title="Bad" data-value="1"></i></li>
                        <li><i class="far fa-star fa-sm text-primary" title="Poor" data-value="2"></i></li>
                        <li><i class="far fa-star fa-sm text-primary" title="OK" data-value="3"></i></li>
                        <li><i class="far fa-star fa-sm text-primary" title="Good" data-value="4"></i></li>
                        <li><i class="far fa-star fa-sm text-primary" title="Excellent" data-value="5"></i></li>
                    </ul>

                    <p class="text-center mb-1"><strong>How do you rate order delivery time?</strong></p>
                    <ul class="h2 feedback-stars d-flex rating justify-content-center pb-2 pl-0"
                        data-mdb-rating-init
                        data-mdb-toggle="rating" data-feedback-type="delivery">
                        <li><i class="far fa-star fa-sm text-primary" title="Bad" data-value="1"></i></li>
                        <li><i class="far fa-star fa-sm text-primary" title="Poor" data-value="2"></i></li>
                        <li><i class="far fa-star fa-sm text-primary" title="OK" data-value="3"></i></li>
                        <li><i class="far fa-star fa-sm text-primary" title="Good" data-value="4"></i></li>
                        <li><i class="far fa-star fa-sm text-primary" title="Excellent" data-value="5"></i></li>
                    </ul>

                    <p class="text-center"><strong>What could we improve?</strong></p>

                    <!-- Message input -->
                    <div data-mdb-input-init class="form-outline mb-4 w-50 m-auto">
                        <textarea class="form-control feedback-text" id="form4Example6" rows="4"></textarea>
                        <label class="form-label" for="form4Example6">Your feedback</label>
                    </div>
                </form>
                <div class="w-100 mx-auto text-center">
                    <button type="button" data-mdb-button-init data-mdb-ripple-init id="submit-feedback"
                            class="btn btn-primary submit-button">Submit
                    </button>
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-md-6 mx-auto">
                    <div class="alert alert-primary" role="alert" data-mdb-color="primary" data-mdb-alert-init=""
                         data-mdb-alert-initialized="true">
                        <i class="fas fa-info-circle me-3"></i>This order not found!
                    </div>
                </div>
            </div>
        @endif

    @else
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="alert alert-primary" role="alert" data-mdb-color="primary" data-mdb-alert-init=""
                     data-mdb-alert-initialized="true">
                    <i class="fas fa-info-circle me-3"></i>This survey already submitted!
                </div>
            </div>
        </div>
    @endif

</div>

</body>
<!-- MDB -->
<script
    type="text/javascript"
    src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.1/mdb.umd.min.js"
></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('.feedback-stars .fa-star').on('mouseover', function () {
            var index = $(this).data('value');
            var $parent = $(this).closest('.feedback-stars');
            $parent.find('.fa-star').each(function (i) {
                if (i < index) {
                    $(this).addClass('hovered');
                } else {
                    $(this).removeClass('hovered');
                }
            });
        });

        $('.feedback-stars').on('mouseleave', function () {
            $(this).find('.fa-star').removeClass('hovered');
        });

        $('.feedback-stars .fa-star').on('click', function () {
            var index = $(this).data('value');
            var $parent = $(this).closest('.feedback-stars');
            var feedbackType = $parent.data('feedback-type');
            $parent.find('.fa-star').each(function (i) {
                if (i < index) {
                    $(this).addClass('selected');
                    $(this).removeClass('hovered');
                } else {
                    $(this).removeClass('selected');
                }
            });
            console.log('Rated ' + index + ' stars for ' + feedbackType);
        });
    });
</script>
<script>
    $(document).ready(function () {
        // Collect star ratings
        $('.feedback-stars i').on('click', function () {
            var $this = $(this);
            // var ratingType = $this.closest('ul').data('feedback-type');
            var ratingValue = $this.data('value');
            // $this.siblings('i').removeClass('fas').addClass('far');
            // $this.prevAll().addBack().removeClass('far').addClass('fas');
            $this.closest('ul').data('selected-value', ratingValue);
        });

        // Submit feedback
        $('#submit-feedback').on('click', function () {
            var priceRating = $('ul[data-feedback-type="price"]').data('selected-value');
            var qualityRating = $('ul[data-feedback-type="quality"]').data('selected-value');
            var deliveryRating = $('ul[data-feedback-type="delivery"]').data('selected-value');
            var feedbackText = $('#form4Example6').val();

            var feedbackData = {
                _token: '{{ csrf_token() }}',
                feedback: {
                    feedbackText: feedbackText,
                    priceRating: priceRating,
                    qualityRating: qualityRating,
                    deliveryRating: deliveryRating,
                }
            };

            console.log("feedbackData", feedbackData)

            $.ajax({
                url: $('#submit-form').attr('action'),
                type: 'POST',
                data: feedbackData,
                success: function (response) {

                    if (response.status === 'success') {
                        $('.feedback-row').html(`<div class="row">
                <div class="col-md-6 mx-auto">
                    <div class="alert alert-primary" role="alert" data-mdb-color="primary" data-mdb-alert-init=""
                         data-mdb-alert-initialized="true">
                        <i class="fas fa-info-circle me-3"></i>Thank You! Feedback successfully submitted.
                    </div>
                </div>
            </div>`);
                    } else {
                        alert('Somethoing went wrong!')
                    }

                    // You can add additional success actions here
                },
                error: function (xhr) {
                    alert('Something went wrong. Please try again.');
                    // You can add additional error actions here
                }
            });
        });
    });
</script>
</html>



