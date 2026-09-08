<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\PortfolioItem;
use App\Models\SeoMeta;
use App\Models\SeoSetting;
use App\Models\Service;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SeoController extends Controller
{
    use HandlesImageUploads;

    /** Models whose records can carry their own SEO. */
    private const SEOABLE = [
        Course::class => ['label' => 'Courses', 'title' => 'title', 'route' => 'courses.show'],
        Service::class => ['label' => 'Services', 'title' => 'title', 'route' => 'services.show'],
        PortfolioItem::class => ['label' => 'Portfolio', 'title' => 'title', 'route' => null],
    ];

    // ---------------------------------------------------------------- settings

    public function settings()
    {
        Gate::authorize('cms.manage');

        $settings = SeoSetting::current();
        $organizationTypes = SeoSetting::ORGANIZATION_TYPES;

        return view('pages.admin.cms.seo.settings', compact('settings', 'organizationTypes'));
    }

    public function updateSettings(Request $request)
    {
        Gate::authorize('cms.manage');

        $settings = SeoSetting::current();

        $data = $request->validate([
            'site_name' => ['nullable', 'string', 'max:255'],
            'title_template' => ['required', 'string', 'max:255'],
            'default_meta_description' => ['nullable', 'string', 'max:320'],
            'canonical_base_url' => ['nullable', 'url', 'max:255'],
            'twitter_handle' => ['nullable', 'string', 'max:50'],

            // GA4 ids look like G-XXXXXXXXXX; the older UA- form is dead.
            'google_analytics_id' => ['nullable', 'string', 'max:40', 'regex:/^G-[A-Z0-9]{6,}$/i'],
            'google_tag_manager_id' => ['nullable', 'string', 'max:40', 'regex:/^GTM-[A-Z0-9]{4,}$/i'],
            'google_site_verification' => ['nullable', 'string', 'max:120'],
            'bing_site_verification' => ['nullable', 'string', 'max:120'],

            'organization_type' => ['required', 'string', Rule::in(array_keys(SeoSetting::ORGANIZATION_TYPES))],
            'social_profiles' => ['nullable', 'array'],
            'social_profiles.*' => ['nullable', 'url', 'max:255'],

            'custom_head_snippet' => ['nullable', 'string', 'max:8000'],
            'custom_body_snippet' => ['nullable', 'string', 'max:8000'],
            'robots_txt' => ['nullable', 'string', 'max:8000'],
            'is_indexable' => ['boolean'],

            'default_og_image' => ['nullable', 'image', 'max:2048'],
            'organization_logo' => ['nullable', 'image', 'max:2048'],
        ], [
            'google_analytics_id.regex' => 'A GA4 measurement ID looks like G-XXXXXXXXXX.',
            'google_tag_manager_id.regex' => 'A GTM container ID looks like GTM-XXXXXXX.',
        ]);

        $data['is_indexable'] = $request->boolean('is_indexable');
        $data['social_profiles'] = array_values(array_filter($data['social_profiles'] ?? []));

        // A share image should stay near 1200px; a logo keeps its alpha and is
        // encoded losslessly.
        foreach (['default_og_image' => 'social', 'organization_logo' => 'logo'] as $field => $preset) {
            if ($request->hasFile($field) || $request->boolean('remove_'.$field)) {
                $data[$field] = $this->resolveUpload($request, $field, $settings->{$field}, 'images/seo', $preset);
            } else {
                unset($data[$field]);
            }
        }

        $settings->update($data);

        notify('SEO and analytics settings saved.', 'Saved', 'success');

        return back();
    }

    // ------------------------------------------------------------- page meta

    /**
     * Every page that can carry SEO: the static routes plus each published
     * record, with whatever meta it already has.
     */
    public function pages()
    {
        Gate::authorize('cms.manage');

        $statics = SeoMeta::query()->whereNotNull('page_key')->get()->keyBy('page_key');

        $rows = [];

        foreach (SeoService::STATIC_PAGES as $key => [$label, $routeName]) {
            $meta = $statics->get($key);

            $rows[] = [
                'group' => 'Static pages',
                'identifier' => 'page:'.$key,
                'title' => $label,
                'url' => route($routeName),
                'meta' => $meta,
            ];
        }

        foreach (self::SEOABLE as $class => $config) {
            foreach ($class::query()->orderBy($config['title'])->get() as $record) {
                $meta = SeoMeta::query()
                    ->where('seoable_type', $record->getMorphClass())
                    ->where('seoable_id', $record->getKey())
                    ->first();

                $rows[] = [
                    'group' => $config['label'],
                    'identifier' => 'model:'.urlencode($class).':'.$record->getKey(),
                    'title' => $record->{$config['title']},
                    'url' => $config['route'] && $record->slug ? route($config['route'], $record->slug) : null,
                    'meta' => $meta,
                ];
            }
        }

        return view('pages.admin.cms.seo.pages', compact('rows'));
    }

    /**
     * Save the meta for one page, whether static or a model.
     */
    public function updatePage(Request $request)
    {
        Gate::authorize('cms.manage');

        $data = $request->validate([
            'identifier' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:320'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'noindex' => ['boolean'],
            'nofollow' => ['boolean'],
        ]);

        $target = $this->resolveIdentifier($data['identifier']);

        $attributes = [
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'og_title' => $data['og_title'] ?? null,
            'og_description' => $data['og_description'] ?? null,
            'canonical_url' => $data['canonical_url'] ?? null,
            'noindex' => $request->boolean('noindex'),
            'nofollow' => $request->boolean('nofollow'),
        ];

        $meta = SeoMeta::updateOrCreate($target, $attributes);

        if ($request->hasFile('og_image') || $request->boolean('remove_og_image')) {
            $request->validate(['og_image' => ['nullable', 'image', 'max:2048']]);

            $meta->update([
                'og_image' => $this->resolveUpload($request, 'og_image', $meta->og_image, 'images/seo', 'social'),
            ]);
        }

        return response()->json(['message' => 'SEO settings saved for this page.']);
    }

    /**
     * Turn the composite identifier the table uses back into the columns that
     * address a seo_meta row.
     */
    private function resolveIdentifier(string $identifier): array
    {
        if (str_starts_with($identifier, 'page:')) {
            $key = substr($identifier, 5);

            abort_unless(array_key_exists($key, SeoService::STATIC_PAGES), 404);

            return ['page_key' => $key];
        }

        if (str_starts_with($identifier, 'model:')) {
            [, $class, $id] = explode(':', $identifier, 3);
            $class = urldecode($class);

            abort_unless(array_key_exists($class, self::SEOABLE), 404);

            $record = $class::findOrFail((int) $id);

            return [
                'seoable_type' => $record->getMorphClass(),
                'seoable_id' => $record->getKey(),
            ];
        }

        abort(404);
    }
}
