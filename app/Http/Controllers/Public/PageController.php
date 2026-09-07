<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;
use App\Models\Course;
use App\Models\PageSection;
use App\Models\PortfolioItem;
use App\Models\Service;
use App\Models\TeamMember;
use Illuminate\Support\Collection;

class PageController extends Controller
{
    public function home()
    {
        return view('pages.public.home', [
            'sections' => $this->sections('home', [
                ['hero', 'Global IT solutions from Bangladesh', 'Smart IT Solutions & Digital Innovation', 'Delivering cutting-edge software, web applications, AI solutions, and digital marketing services to clients across 25+ countries.', 'Get a Quote', route('contact')],
                ['hero-visual', null, 'Delivery dashboard', null],
                ['services', 'Our core services', 'Technology services built for measurable growth', 'We combine strategy, engineering, design, and marketing to help companies launch faster, operate smarter, and grow with confidence.'],
                ['service-item', null, 'Web Design & Development', "Responsive business websites, portals, and e-commerce experiences.", "Responsive UI\nSEO friendly\nPerformance focused", route('services.show', 'web-design')],
                ['service-item', null, 'Custom Web Applications', "Reliable software for operations, reporting, workflows, and automation.", "Laravel apps\nAdmin systems\nAPI integrations", route('services.show', 'custom-web-application')],
                ['service-item', null, 'Digital Marketing', "Search, social, and campaign support for stronger online growth.", "SEO\nSocial media\nCampaign strategy", route('services.show', 'digital-marketing')],
                ['courses', 'Professional courses', 'Skill-focused offline training programs', 'Our courses combine live guidance, practical projects, and portfolio-ready assignments.'],
                ['about', 'About HashTag Research & Technology Ltd.', 'A practical technology partner for ambitious teams', 'Since 2017, we have delivered scalable software, digital solutions, and training programs to global clients.', 'Learn More About Us', route('about')],
                ['portfolio', 'Our portfolio', 'Digital solutions delivered for real business needs', 'From ERP systems to international e-commerce builds and training programs, our portfolio blends practical engineering with long-term support.'],
                ['contact-cta', 'Let us build your next digital solution', 'Ready to plan your next project?', 'Prefer email? hello@hashtag.com'],
            ]),
            'courses' => Course::with('category')->published()->sorted()->take(3)->get(),
            'services' => Service::with('category')->published()->sorted()->take(6)->get(),
            'portfolioItems' => PortfolioItem::visible()->sorted()->take(3)->get(),
            'contact' => ContactSetting::instance(),
        ]);
    }

    public function about()
    {
        return $this->simplePage('about', 'pages.public.about');
    }

    public function mission()
    {
        return $this->simplePage('mission', 'pages.public.mission');
    }

    public function vision()
    {
        return $this->simplePage('vision', 'pages.public.vision');
    }

    public function team()
    {
        return view('pages.public.team', [
            'sections' => $this->sections('team'),
            'teamMembers' => TeamMember::visible()->sorted()->get(),
        ]);
    }

    public function contact()
    {
        return view('pages.public.contact', [
            'sections' => $this->sections('contact'),
            'contact' => ContactSetting::instance(),
        ]);
    }

    public function portfolio()
    {
        return view('pages.public.portfolio', [
            'sections' => $this->sections('portfolio')->whenEmpty(fn () => $this->sections('work')),
            'portfolioItems' => PortfolioItem::visible()->sorted()->get(),
        ]);
    }

    private function simplePage(string $page, string $view)
    {
        return view($view, [
            'sections' => $this->sections($page),
        ]);
    }

    private function sections(string $page, array $fallback = []): Collection
    {
        $sections = PageSection::visible()
            ->where('page_slug', $page)
            ->sorted()
            ->get();

        if ($sections->isNotEmpty() || $fallback === []) {
            return $sections;
        }

        return collect($fallback)->map(function (array $section, int $index) use ($page) {
            return (object) [
                'page_slug' => $page,
                'section_key' => $section[0],
                'subtitle' => $section[1] ?? null,
                'title' => $section[2] ?? null,
                'description' => $section[3] ?? null,
                'button_text' => $section[4] ?? null,
                'button_link' => $section[5] ?? null,
                'image_path' => null,
                'visibility' => true,
                'sort_order' => $index,
            ];
        });
    }
}
