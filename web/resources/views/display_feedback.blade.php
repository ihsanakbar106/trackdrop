

@php
    $stars_counts = [0, 0, 0, 0, 0];
    $total_record_average = 0;

    foreach ($lineitems as $lineitem) {
        $single_record_average = 0;
        if (isset($lineitem->hasFeedbacks)) {
            $record = json_decode($lineitem->hasFeedbacks->feedback, true);

            $ratings = array_map('intval', array_intersect_key($record, array_flip(['priceRating', 'qualityRating', 'deliveryRating'])));
            $count = count(array_filter($ratings));

            if ($count) {
                $single_record_average = ceil(array_sum($ratings) / $count);
                $total_record_average += $single_record_average;
                $stars_counts[$single_record_average - 1]++;
            }
        }
    }

    $total_feedback_count = array_sum($stars_counts);
    $stars_avgs = 0;
    if($total_feedback_count !== 0){
        $total_record_average = ceil($total_record_average / $total_feedback_count);
        $stars_avgs = array_map(fn($count) => ($count / $total_feedback_count) * 100, $stars_counts);
    }


@endphp

@if($total_record_average !== 0 && $total_feedback_count !== 0)
    <div class="container-c">
        <div class="col-md-c1">
            <div>
                <span class="heading">Customers Rating</span>
                <div>
                    @for ($i = 0; $i < 5; $i++)
                        <span class="fa fa-star @if($total_record_average > $i) checked @endif"></span>
                    @endfor
                </div>
                <p class="avg-feedback">{{ $total_record_average }} average based on {{ $total_feedback_count }} reviews.</p>
                <hr style="border: 3px solid #f1f1f1; margin: 15px 0;">
            </div>

            @for ($i = 5; $i >= 1; $i--)
                <div class="row-c1">
                    <div class="side">
                        @for ($j = 0; $j < 5; $j++)
                            <span class="fa fa-star @if($j < $i) checked @endif"></span>
                        @endfor
                    </div>
                    <div class="middle">
                        <div class="bar-container">
                            <div class="bar-{{ $i }}" style="width: {{ $stars_avgs[$i - 1] }}%"></div>
                        </div>
                    </div>
                    <div class="side right">
                        <div>{{ $stars_counts[$i - 1] }}</div>
                    </div>
                </div>
            @endfor

            @if ($lineitems->count())
                <div class="row-c2 single-feedback-section">
                    @foreach ($lineitems as $lineitem)
                        @if (isset($lineitem->hasFeedbacks))
                            @php
                                $record = json_decode($lineitem->hasFeedbacks->feedback, true);
                                $single_record_average = ceil(array_sum(array_map('intval', array_intersect_key($record, array_flip(['priceRating', 'qualityRating', 'deliveryRating'])))) / count(array_filter($ratings)));
                                $customer = optional(optional($lineitem)->order)->customer;
                                $customer = $customer ? json_decode($customer) : (object) ['first_name' => '', 'last_name' => ''];
                            @endphp
                            <div class="col-md-c3">
                                <p class="name-c"><b>{{ $customer->first_name }} {{ $customer->last_name }}</b></p>
                                <p class="date-c">{{ \Carbon\Carbon::parse($lineitem->hasFeedbacks->created_at)->format('Y/m/d')}}</p>
                                <div class="feedback-rating-c">
                                    @for ($i = 0; $i < 5; $i++)
                                        <span class="fa fa-star @if($single_record_average > $i) checked @endif"></span>
                                    @endfor
                                </div>
                                <div class="feedback-rating-review">
                                    <p class="feedback-rating-review-p">{{ $record['feedbackText'] ?? '' }}</p>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif

