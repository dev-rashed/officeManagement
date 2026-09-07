@extends('layouts.public')

@section('title', 'HashTag Research & Technology Ltd. | Smart IT Solutions & Digital Innovation')
@section('description', 'HashTag delivers software development, web applications, AI solutions, digital marketing, and IT consulting for clients across 25+ countries.')

@section('content')
<section class="hero section" id="home">
    <div class="container hero-grid">
        @foreach($sections as $section)
            @if($section->section_key == 'hero')
            <div class="hero-copy reveal">
                <p class="eyebrow">{{ $section->subtitle ?? 'Global IT solutions from Bangladesh' }}</p>
                <h1>{{ $section->title }}</h1>
                <p class="hero-lede">{{ $section->description ?? 'Delivering cutting-edge software, web applications, AI solutions, and digital marketing services to clients across 25+ countries.' }}</p>

                <div class="hero-actions" aria-label="Primary actions">
                    @if($section->button_text && $section->button_link)
                    <a class="btn btn-primary" href="{{ $section->button_link }}">{{ $section->button_text }}</a>
                    @endif
                    <a class="btn btn-secondary" href="{{ route('portfolio') }}">View Portfolio</a>
                </div>

                <div class="trust-strip" aria-label="Company performance metrics">
                    <div>
                        <strong>1000+</strong>
                        <span>projects completed</span>
                    </div>
                    <div>
                        <strong>25+</strong>
                        <span>countries served</span>
                    </div>
                    <div>
                        <strong>2000+</strong>
                        <span>trainees mentored</span>
                    </div>
                </div>
            </div>
            @endif
        @endforeach

        @foreach($sections as $section)
            @if($section->section_key == 'hero-visual')
            <div class="hero-visual reveal" aria-label="Agency delivery dashboard preview">
                <div class="dashboard-shell">
                    <div class="dashboard-top">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <div class="dashboard-body">
                        <div class="metric-panel">
                            <span>Delivery coverage</span>
                            <strong>25+</strong>
                            <small>countries served</small>
                        </div>
                        <div class="chart-panel" aria-hidden="true">
                            <span style="height: 42%"></span>
                            <span style="height: 66%"></span>
                            <span style="height: 54%"></span>
                            <span style="height: 82%"></span>
                            <span style="height: 72%"></span>
                        </div>
                        <div class="work-panel">
                            <div>
                                <span class="status-dot"></span>
                                Requirement analysis
                            </div>
                            <div>
                                <span class="status-dot"></span>
                                Design and development
                            </div>
                            <div>
                                <span class="status-dot"></span>
                                Testing and deployment
                            </div>
                        </div>
                    </div>
                </div>

                <div class="floating-proof">
                    <span>Avg. speed score</span>
                    <strong>2017</strong>
                </div>
            </div>
            @endif
        @endforeach
    </div>
</section>

<section class="logo-band" aria-label="Trusted client regions">
    <div class="container logo-grid">
        <span>USA</span>
        <span>Canada</span>
        <span>Germany</span>
        <span>Australia</span>
        <span>United Kingdom</span>
        <span>Bangladesh</span>
    </div>
</section>

@foreach($sections as $section)
    @if($section->section_key == 'services')
    <section class="section services" id="services">
        <div class="container">
            <div class="section-heading reveal">
                <p class="eyebrow">{{ $section->subtitle ?? 'Our core services' }}</p>
                <h2>{{ $section->title }}</h2>
                <p>{{ $section->description ?? 'We combine strategy, engineering, design, and marketing to help companies launch faster, operate smarter, and grow with confidence.' }}</p>
            </div>

            <div class="services-grid">
                @foreach($sections as $serviceSection)
                    @if($serviceSection->section_key == 'service-item' && $serviceSection->visibility)
                    <article class="service-card reveal">
                        <a href="{{ $serviceSection->button_link ?? route('services.show', 'web-design') }}">
                            <span class="service-index">{{ $loop->iteration }}</span>
                            <h3>{{ $serviceSection->title }}</h3>
                            <p>{{ $serviceSection->description ?? 'Fast, SEO-friendly websites, portals, and e-commerce experiences that convert visitors into customers.' }}</p>
                            <ul>
                                @foreach(explode("\n", $serviceSection->button_text ?? '') as $feature)
                                    @if(trim($feature) !== '')
                                        <li>{{ trim($feature) }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </a>
                    </article>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
    @endif
@endforeach

@foreach($sections as $section)
    @if($section->section_key == 'courses')
    <section class="section courses" id="courses">
        <div class="container">
            <div class="section-heading split reveal">
                <div>
                    <p class="eyebrow">{{ $section->subtitle ?? 'Professional courses' }}</p>
                    <h2>{{ $section->title }}</h2>
                </div>
                <p>{{ $section->description ?? 'Our courses combine live guidance, practical projects, and portfolio-ready assignments for students, professionals, and teams.' }}</p>
            </div>

            <div class="courses-grid">
                @foreach($courses as $course)
                <article class="course-card reveal">
                    <a href="{{ route('courses.show', $course->slug) }}">
                        @if($course->featured_image)
                        <div class="course-image">
                            <img src="{{ asset('storage/' . $course->featured_image) }}" alt="{{ $course->title }}" width="640" height="360" loading="lazy">
                        </div>
                        @endif
                        <span class="course-tag">{{ $course->category ? $course->category->name : 'Course' }}</span>
                        <h3>{{ $course->title }}</h3>
                        <p>{{ $course->short_description }}</p>
                        <div class="course-meta">
                            <span>{{ $course->duration ?? 'Duration' }}</span>
                            <span>{{ $course->class_type ?? 'Offline' }}</span>
                        </div>
                    </a>
                </article>
                @endforeach
            </div>

            <div class="section-action reveal">
                <a class="btn btn-primary" href="{{ route('courses.index') }}">Explore All Courses</a>
            </div>
        </div>
    </section>
    @endif
@endforeach

@foreach($sections as $section)
    @if($section->section_key == 'about')
    <section class="section about" id="about">
        <div class="container about-grid">
            <div class="section-heading reveal">
                <p class="eyebrow">{{ $section->subtitle ?? 'About HashTag Research & Technology Ltd.' }}</p>
                <h2>{{ $section->title }}</h2>
            </div>
            <div class="about-copy reveal">
                <p>{{ $section->description ?? 'HashTag Research & Technology Ltd. is a leading IT services and consulting company in Bangladesh, committed to innovation, quality, and client success. Since 2017, we have delivered scalable software, digital solutions, and training programs to global clients.' }}</p>
                <p>{{ $section->button_text ?? 'Our team partners with startups, businesses, public-sector projects, and international clients to plan, build, launch, and improve digital products that create measurable value.' }}</p>
                <a class="btn btn-secondary" href="{{ $section->button_link ?? route('about') }}">{{ $section->button_text ?? 'Learn More About Us' }}</a>
            </div>
        </div>
    </section>
    @endif
@endforeach

<section class="stats-section" id="achievements" aria-label="Key achievements">
    <div class="container stats-grid">
        <div class="stat-card reveal">
            <strong>1000+</strong>
            <span>Projects Completed</span>
        </div>
        <div class="stat-card reveal">
            <strong>25+</strong>
            <span>Countries Served</span>
        </div>
        <div class="stat-card reveal">
            <strong>2000+</strong>
            <span>Students Trained</span>
        </div>
        <div class="stat-card reveal">
            <strong>7+</strong>
            <span>Years Experience</span>
        </div>
    </div>
</section>

@foreach($sections as $section)
    @if(in_array($section->section_key, ['portfolio', 'work'], true))
    <section class="section portfolio" id="portfolio">
        <div class="container">
            <div class="section-heading split reveal">
                <div>
                    <p class="eyebrow">{{ $section->subtitle ?? 'Our portfolio' }}</p>
                    <h2>{{ $section->title }}</h2>
                </div>
                <p>{{ $section->description ?? 'From ERP systems to international e-commerce builds and training programs, our portfolio blends practical engineering with long-term support.' }}</p>
            </div>

            <div class="project-grid">
                <article class="project-card reveal">
                    <div class="project-media media-commerce" aria-label="Commerce platform interface preview">
                        <div class="mock-window">
                            <span></span>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                    <div class="project-content">
                        <p class="project-kicker">ERP system</p>
                        <h3>Baby Super Shop ERP for smarter retail operations.</h3>
                        <p>Inventory, sales, reporting, and store operations organized in a scalable business management system.</p>
                        <div class="result-row">
                            <strong>ERP</strong>
                            <span>retail operations platform</span>
                        </div>
                    </div>
                </article>

                <article class="project-card reveal">
                    <div class="project-media media-analytics" aria-label="Analytics dashboard interface preview">
                        <div class="analytics-board">
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                    <div class="project-content">
                        <p class="project-kicker">E-commerce</p>
                        <h3>International e-commerce development for a USA client.</h3>
                        <p>Modern storefront architecture, product management, checkout experience, and performance-ready delivery.</p>
                        <div class="result-row">
                            <strong>USA</strong>
                            <span>global client delivery</span>
                        </div>
                    </div>
                </article>

                <article class="project-card reveal">
                    <div class="project-media media-finance" aria-label="Mobile finance app interface preview">
                        <div class="phone-mock">
                            <span></span>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                    <div class="project-content">
                        <p class="project-kicker">Training programs</p>
                        <h3>Government and professional training initiatives.</h3>
                        <p>Structured technology training programs designed to build practical skills and career-ready capability.</p>
                        <div class="result-row">
                            <strong>2000+</strong>
                            <span>students trained</span>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>
    @endif
@endforeach

<section class="section process" id="process">
    <div class="container">
        <div class="section-heading reveal">
            <p class="eyebrow">Our working process</p>
            <h2>A proven process from requirement to optimization.</h2>
            <p>We keep each project structured, collaborative, and transparent from first conversation to post-launch support.</p>
        </div>

        <div class="process-track">
            <article class="process-step reveal">
                <span>01</span>
                <h3>Requirement Analysis</h3>
                <p>We understand your business goals, users, technical needs, and success criteria before planning the solution.</p>
            </article>
            <article class="process-step reveal">
                <span>02</span>
                <h3>Strategy & Planning</h3>
                <p>We define the roadmap, architecture, features, timeline, and delivery milestones for focused execution.</p>
            </article>
            <article class="process-step reveal">
                <span>03</span>
                <h3>Design & Development</h3>
                <p>Our team creates polished interfaces and reliable software through iterative, collaborative sprints.</p>
            </article>
            <article class="process-step reveal">
                <span>04</span>
                <h3>Testing & Deployment</h3>
                <p>We test functionality, performance, security, and responsiveness before launch.</p>
            </article>
            <article class="process-step reveal">
                <span>05</span>
                <h3>Support & Optimization</h3>
                <p>We monitor, support, and improve your solution as your business grows.</p>
            </article>
        </div>
    </div>
</section>

<section class="section proof" id="testimonials">
    <div class="container proof-grid">
        <div class="proof-copy reveal">
            <p class="eyebrow">Testimonials</p>
            <h2>Trusted by businesses, founders, and training partners.</h2>
            <p>Our clients value clear communication, reliable delivery, and solutions that are built for real operational outcomes.</p>
            <div class="proof-list">
                <div><strong>Global</strong><span>experience serving clients across multiple countries</span></div>
                <div><strong>Reliable</strong><span>structured delivery, testing, and launch support</span></div>
                <div><strong>Skilled</strong><span>software, marketing, cloud, and training expertise</span></div>
            </div>
        </div>

        <div class="testimonial-stack">
            <article class="testimonial reveal">
                <div class="avatar" aria-hidden="true">JM</div>
                <blockquote>HashTag delivered beyond expectations. Their team understood our requirements clearly and built a solution that improved our daily operations.</blockquote>
                <p><strong>International Client</strong><span>E-commerce Business, USA</span></p>
            </article>
            <article class="testimonial reveal">
                <div class="avatar" aria-hidden="true">SL</div>
                <blockquote>The training program was practical, organized, and career-focused. Our participants gained confidence with real technology skills.</blockquote>
                <p><strong>Program Coordinator</strong><span>Government Training Initiative</span></p>
            </article>
        </div>
    </div>
</section>

@foreach($sections as $section)
    @if($section->section_key == 'contact-cta')
    <section class="section final-cta" id="contact">
        <div class="container cta-panel reveal">
            <div class="cta-copy">
                <p class="eyebrow">{{ $section->subtitle ?? 'Let us build your next digital solution' }}</p>
                <h2>{{ $section->title }}</h2>
                <div class="contact-actions">
                    <a class="btn btn-secondary light" href="tel:+8801234567890">Contact Us</a>
                </div>
                <p class="cta-note">{{ $section->description ?? 'Prefer email? hello@hashtag.com' }}</p>
            </div>

            <form class="quote-form" data-quote-form>
                <label>
                    <span>Name</span>
                    <input type="text" name="name" autocomplete="name" required>
                </label>
                <label>
                    <span>Email</span>
                    <input type="email" name="email" autocomplete="email" required>
                </label>
                <label>
                    <span>Project type</span>
                    <select name="project" required>
                        <option value="">Select one</option>
                        <option>Software development</option>
                        <option>Web development</option>
                        <option>Mobile app</option>
                        <option>Digital marketing</option>
                        <option>Cloud, IT consulting, or AI</option>
                    </select>
                </label>
                <label>
                    <span>Budget range</span>
                    <select name="budget" required>
                        <option value="">Select one</option>
                        <option>$3k - $8k</option>
                        <option>$8k - $20k</option>
                        <option>$20k - $50k</option>
                        <option>$50k+</option>
                    </select>
                </label>
                <label class="form-wide">
                    <span>What should we build?</span>
                    <textarea name="message" rows="4" required></textarea>
                </label>
                <button class="btn btn-primary form-wide" type="submit">Get a Quote</button>
            </form>
        </div>
    </section>
    @endif
@endforeach

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('[data-quote-form]');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const name = form.querySelector('[name="name"]').value;
            const email = form.querySelector('[name="email"]').value;
            const project = form.querySelector('[name="project"]').value;
            const budget = form.querySelector('[name="budget"]').value;
            const message = form.querySelector('[name="message"]').value;

            const subject = `Quote Request: ${project}`;
            const body = `Name: ${name}\nEmail: ${email}\nProject: ${project}\nBudget: ${budget}\n\nMessage:\n${message}`;

            window.location.href = `mailto:hello@hashtag.com?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
        });
    }
});
</script>
@endsection
