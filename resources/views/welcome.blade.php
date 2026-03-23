@extends('layouts.app', ['title' => 'Aquino Dental Clinic Appointment Management System'])

@section('content')
<section class="hero-mountain">
    <p class="hero-kicker">Aquino Dental Clinic Appointment Management System</p>
    <h2>Where Every Smile Shines Like a Pearl.</h2>
    <p>Modern dental care with a calm, highland-inspired experience. Book in minutes and enjoy attentive care from consultation to follow-up.</p>
    <div class="hero-actions">
        @guest
            <a class="btn" href="{{ route('register.form') }}">Book an Appointment</a>
            <a class="btn btn-ghost" href="{{ route('login.form') }}">Patient Login</a>
        @else
            <a class="btn" href="{{ route('dashboard.redirect') }}">Go to Your Dashboard</a>
        @endguest
    </div>
</section>

<section class="card intro-slab">
    <h3>Modern Dentistry, Highland Calm.</h3>
    <p>We combine advanced clinical technology with gentle care in a soothing environment inspired by the Cordillera landscape.</p>
</section>

<section class="grid cards-4 service-grid">
    <article class="card service-card">
        <div class="service-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none"><path d="M7.5 4.5c-1.8 0-3.5 1.6-3.5 4 0 5.2 3.1 10.8 5.4 10.8 1 0 1.2-1.7 1.6-3 .3-1 .7-1.8 1.5-1.8s1.2.8 1.5 1.8c.4 1.3.6 3 1.6 3 2.3 0 5.4-5.6 5.4-10.8 0-2.4-1.7-4-3.5-4-1.7 0-2.9.8-4 1.6-1.1-.8-2.3-1.6-4-1.6Z" stroke="currentColor" stroke-width="1.5"/></svg>
        </div>
        <h3>General Dentistry</h3>
        <p>Preventive care, diagnostics, and regular cleaning for long-term oral health.</p>
    </article>
    <article class="card service-card">
        <div class="service-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none"><path d="M12 3.5 13.8 8l4.7.2-3.7 2.9 1.2 4.6L12 13l-4 2.7 1.2-4.6-3.7-2.9 4.7-.2L12 3.5Z" stroke="currentColor" stroke-width="1.5"/></svg>
        </div>
        <h3>Cosmetic Dentistry</h3>
        <p>Whitening, veneers, and smile design focused on natural, confident results.</p>
    </article>
    <article class="card service-card">
        <div class="service-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none"><rect x="4" y="8" width="16" height="8" rx="4" stroke="currentColor" stroke-width="1.5"/><path d="M7 12h10M9 10.5v3M12 10.5v3M15 10.5v3" stroke="currentColor" stroke-width="1.5"/></svg>
        </div>
        <h3>Orthodontics</h3>
        <p>Braces and clear aligners for healthier alignment, bite balance, and comfort.</p>
    </article>
    <article class="card service-card">
        <div class="service-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none"><path d="M12 3v6m0 0 3-2m-3 2-3-2M9 10.5h6M9.5 14h5M10 17h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><rect x="8" y="9" width="8" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/></svg>
        </div>
        <h3>Implant Dentistry</h3>
        <p>Reliable implant restorations built for function, stability, and natural feel.</p>
    </article>
</section>

<section class="grid two-col feature-band">
    <article class="card media-card">
        <img src="https://images.unsplash.com/photo-1629909613654-28e377c37b09?auto=format&fit=crop&w=900&q=80" alt="Dentist consulting with patient" class="feature-image">
        <h3>Trusted Care, Modern Technology</h3>
        <p>Digital workflows and gentle treatment plans designed for comfort, precision, and clarity at every visit.</p>
    </article>
    <article class="card media-card alt">
        <img src="https://images.unsplash.com/photo-1588776814546-1ffcf47267a5?auto=format&fit=crop&w=900&q=80" alt="Clean and modern dental clinic interior" class="feature-image">
        <h3>Calm Clinical Environment</h3>
        <p>Bright spaces, organized appointments, and supportive staff that make each visit easy and stress-free.</p>
    </article>
</section>

<section class="testimonial-section card">
    <h3 class="centered-title">What Our Patients Say</h3>
    <div class="testimonial-grid">
        <div class="testimonial-card">
            <p class="testimonial-quote">"The most professional and calming dental experience I've ever had. The clinic feels like a spa, and Dr. Cruz is fantastic. My smile has never been better!"</p>
            <div class="testimonial-author">
                <img src="https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?auto=format&fit=crop&w=120&q=80" alt="Patient portrait">
                <div>
                    <div class="testimonial-author-name">Anna Reyes</div>
                    <div class="testimonial-author-info">Cosmetic Dentistry Patient</div>
                </div>
            </div>
        </div>
        <div class="testimonial-card">
            <p class="testimonial-quote">"Booking an appointment online was so easy. The staff are friendly, and the clinic is spotless. Highly recommend Mountain Pearl for the whole family."</p>
            <div class="testimonial-author">
                <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=120&q=80" alt="Patient portrait">
                <div>
                    <div class="testimonial-author-name">Mark Santos</div>
                    <div class="testimonial-author-info">General Check-up</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="card clinics-panel">
    <h3>Our Clinics</h3>
    <p>Consistent quality care across our network of locations, with the same Mountain Pearl standard in every branch.</p>
    <div class="clinic-tags">
        <span class="chip">Makati</span>
        <span class="chip">BGC</span>
        <span class="chip">Alabang</span>
        <span class="chip">Cebu</span>
        <span class="chip">Ortigas</span>
    </div>
</section>

<section class="card location-panel" id="location">
    <h3>Find Us</h3>
    <p>Visit us at 2nd Floor, Laperal Building, Session Road Baguio City.</p>
    <div class="location-layout">
        <div class="location-map-wrap">
            <iframe
                class="location-map"
                src="https://www.google.com/maps?q=16.4119385,120.5978973&z=17&output=embed"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                title="Aquino Dental Clinic Appointment Management System Location">
            </iframe>
        </div>
        <div class="location-actions">
            <a class="btn" href="https://maps.app.goo.gl/akp4WVi9G4G4Yhun9" target="_blank" rel="noopener noreferrer">Open in Google Maps</a>
            <a class="btn btn-ghost" href="https://www.google.com/maps/dir/?api=1&destination=16.4119385,120.5978973" target="_blank" rel="noopener noreferrer">Get Directions</a>
        </div>
    </div>
</section>

<section class="card cta-band">
    <h3>Ready to Begin Your Journey?</h3>
    <p>Book your appointment in minutes and get clear confirmations and reminders.</p>
    <div class="hero-actions">
        <a class="btn" href="{{ route('register.form') }}">Book an Appointment</a>
        <a class="btn btn-ghost" href="{{ route('login.form') }}">I'm a Returning Patient</a>
    </div>
</section>
@endsection
