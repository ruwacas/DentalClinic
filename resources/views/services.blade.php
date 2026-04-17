@extends('layouts.app', ['title' => 'Our Services | Aquino Dental Clinic'])

@section('content')
<section class="hero-mountain">
    <p class="hero-kicker">Our Services</p>
    <h2>Clinic Service Menu</h2>
    <p>Complete list of consultation, orthodontic treatment, and surgery services available in the clinic.</p>
</section>

<section class="card services-box">
    @foreach ($serviceMenu as $category)
        <section class="services-category-block">
            <h3>{{ $category['category'] }}</h3>

            <div class="services-items-grid">
                @foreach ($category['services'] as $service)
                    <div class="service-item">
                        <h4>{{ $service['name'] }}</h4>

                        @if (! empty($service['sub_services']))
                            <ul>
                                @foreach ($service['sub_services'] as $subService)
                                    <li>{{ $subService }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach
</section>
@endsection