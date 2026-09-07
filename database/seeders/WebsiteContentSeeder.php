<?php

namespace Database\Seeders;

use App\Models\ContactSetting;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\PageSection;
use App\Models\PortfolioItem;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class WebsiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPageSections();
        $this->seedCourses();
        $this->seedServices();
        $this->seedPortfolio();
        $this->seedTeam();
        $this->seedContact();
    }

    private function seedPageSections(): void
    {
        $sections = [
            ['home', 'hero', 'Smart IT Solutions & Digital Innovation', 'Global IT solutions from Bangladesh', 'Delivering cutting-edge software, web applications, AI solutions, and digital marketing services to clients across 25+ countries.', 'Get a Quote', '/contact', 10],
            ['home', 'hero-visual', 'Delivery dashboard', null, null, null, null, 20],
            ['home', 'services', 'Technology services built for measurable growth', 'Our core services', 'We combine strategy, engineering, design, and marketing to help companies launch faster, operate smarter, and grow with confidence.', null, null, 30],
            ['home', 'service-item', 'Web Design & Development', null, 'Responsive business websites, portals, and e-commerce experiences.', "Responsive UI\nSEO friendly\nPerformance focused", '/services/web-design', 31],
            ['home', 'service-item', 'Custom Web Applications', null, 'Reliable software for operations, reporting, workflows, and automation.', "Laravel apps\nAdmin systems\nAPI integrations", '/services/custom-web-application', 32],
            ['home', 'service-item', 'Digital Marketing', null, 'Search, social, and campaign support for stronger online growth.', "SEO\nSocial media\nCampaign strategy", '/services/digital-marketing', 33],
            ['home', 'courses', 'Skill-focused offline training programs', 'Professional courses', 'Our courses combine live guidance, practical projects, and portfolio-ready assignments.', null, null, 40],
            ['home', 'about', 'A practical technology partner for ambitious teams', 'About HashTag Research & Technology Ltd.', 'Since 2017, we have delivered scalable software, digital solutions, and training programs to global clients.', 'Learn More About Us', '/about', 50],
            ['home', 'portfolio', 'Digital solutions delivered for real business needs', 'Our portfolio', 'From ERP systems to international e-commerce builds and training programs, our portfolio blends practical engineering with long-term support.', null, null, 60],
            ['home', 'contact-cta', 'Ready to plan your next project?', 'Let us build your next digital solution', 'Prefer email? hello@hashtag.com', null, null, 90],
            ['about', 'main', 'HashTag Research & Technology Ltd.', 'About Us', 'HashTag Research & Technology Ltd. is a leading IT services and consulting company in Bangladesh, committed to innovation, quality, and client success.', null, null, 10],
            ['mission', 'main', 'Empowering Innovation Through Technology', 'Our Mission', 'Our mission is to deliver cutting-edge IT solutions that empower businesses and individuals to succeed in the digital age.', null, null, 10],
            ['vision', 'main', 'Building the Future of Digital Solutions', 'Our Vision', 'We envision a world where innovative technology is accessible to businesses of all sizes, creating opportunities and driving sustainable growth.', null, null, 10],
            ['team', 'main', 'Talented Professionals Committed to Excellence', 'Meet the Team', 'Our team of experienced professionals is dedicated to delivering exceptional results.', null, null, 10],
            ['portfolio', 'main', 'Digital Solutions Delivered for Real Business Needs', 'Portfolio', 'Explore selected software, e-commerce, training, and digital transformation work delivered by the HashTag team.', null, null, 10],
            ['contact', 'main', "Let's Talk About Your Next Project", 'Get in Touch', "We'd love to hear from you. Fill out the form on our homepage or reach out directly.", null, null, 10],
        ];

        foreach ($sections as [$page, $key, $title, $subtitle, $description, $buttonText, $buttonLink, $sort]) {
            PageSection::updateOrCreate(
                ['page_slug' => $page, 'section_key' => $key, 'title' => $title],
                [
                    'subtitle' => $subtitle,
                    'description' => $description,
                    'button_text' => $buttonText,
                    'button_link' => $buttonLink,
                    'visibility' => true,
                    'sort_order' => $sort,
                ],
            );
        }
    }

    private function seedCourses(): void
    {
        $category = CourseCategory::updateOrCreate(
            ['slug' => 'professional-it-training'],
            ['name' => 'Professional IT Training', 'description' => 'Offline practical training programs.', 'status' => 'published'],
        );

        $courses = [
            ['web-design-development', 'Web Design & Development', 'Practical offline training for responsive websites and frontend development.', '3 Months'],
            ['digital-marketing', 'Digital Marketing', 'Hands-on SEO, social media, and campaign training for career-ready skills.', '2 Months'],
            ['office-skills', 'Office Skills & Productivity', 'Offline computer office applications and workplace productivity training.', '6 Weeks'],
        ];

        foreach ($courses as [$slug, $title, $short, $duration]) {
            Course::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'short_description' => $short,
                    'full_description' => $short,
                    'category_id' => $category->id,
                    'duration' => $duration,
                    'class_type' => 'offline',
                    'location' => 'Rangpur, Bangladesh',
                    'schedule_batch' => 'Weekend and weekday batches',
                    'fee' => 'Contact for fee',
                    'status' => 'published',
                    'sort_order' => 10,
                ],
            );
        }
    }

    private function seedServices(): void
    {
        $category = ServiceCategory::updateOrCreate(
            ['slug' => 'it-solutions'],
            ['name' => 'IT Solutions', 'description' => 'Software, web, marketing, and consulting services.', 'status' => 'published'],
        );

        $services = [
            ['web-design', 'Web Design', 'Responsive business websites that communicate clearly and convert visitors.'],
            ['custom-web-application', 'Custom Web Application', 'Tailored web systems for operations, workflows, reporting, and automation.'],
            ['digital-marketing', 'Digital Marketing', 'SEO, social media, and campaign support for measurable online growth.'],
        ];

        foreach ($services as [$slug, $title, $short]) {
            Service::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'short_description' => $short,
                    'full_description' => $short,
                    'service_category_id' => $category->id,
                    'status' => 'published',
                    'show_on_homepage' => true,
                    'sort_order' => 10,
                ],
            );
        }
    }

    private function seedPortfolio(): void
    {
        $items = [
            ['baby-super-shop-erp', 'Baby Super Shop ERP', 'Inventory, sales, reporting, and retail operations in a scalable business management system.', ['ERP', 'Retail', 'Inventory']],
            ['usa-ecommerce-platform', 'USA E-commerce Platform', 'Modern storefront architecture, product management, checkout, and performance-ready delivery.', ['E-commerce', 'Web Development']],
            ['government-training-programs', 'Government Training Programs', 'Structured technology training programs designed to build practical career-ready skills.', ['Training', 'Education']],
        ];

        foreach ($items as [$slug, $title, $description, $technologies]) {
            PortfolioItem::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'description' => $description,
                    'technologies' => $technologies,
                    'status' => 'published',
                    'sort_order' => 10,
                ],
            );
        }
    }

    private function seedTeam(): void
    {
        $members = [
            ['Rashedul Hasan', 'Founder & CEO'],
            ['Fatima Khan', 'Head of Web Development'],
            ['Ahmed Rahman', 'Lead Instructor'],
            ['Israela Sultana', 'Digital Marketing Lead'],
        ];

        foreach ($members as $index => [$name, $position]) {
            TeamMember::updateOrCreate(
                ['email' => 'team'.($index + 1).'@hashtag.com'],
                [
                    'name' => $name,
                    'position' => $position,
                    'bio' => 'Experienced technology professional helping HashTag deliver practical digital solutions.',
                    'status' => 'active',
                    'sort_order' => $index + 1,
                ],
            );
        }
    }

    private function seedContact(): void
    {
        ContactSetting::instance()->update([
            'email' => 'hello@hashtag.com',
            'phone' => '+880 123-456-7890',
            'address' => 'Rangpur, Bangladesh',
            'social_links' => [],
        ]);
    }
}
