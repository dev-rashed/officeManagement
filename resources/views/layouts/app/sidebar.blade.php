<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clock" :href="route('activity.log')" :current="request()->routeIs('activity.log')" wire:navigate>
                        {{ __('Activity Log') }}
                    </flux:sidebar.item>
                    <flux:navlist.group
                        expandable
                        :expanded="request()->routeIs('income.*')"
                        :heading="__('Income Management')"
                    >
                        <flux:sidebar.item :href="route('income.index')" :current="request()->routeIs('income.index', 'income.create', 'income.show', 'income.edit')" wire:navigate>
                            {{ __('Incomes') }}
                        </flux:sidebar.item>
                        @can('finance.categories.manage')
                            <flux:sidebar.item :href="route('income.categories')" :current="request()->routeIs('income.categories')" wire:navigate>
                                {{ __('Categories') }}
                            </flux:sidebar.item>
                        @endcan
                    </flux:navlist.group>
                    <flux:navlist.group
                        expandable
                        :expanded="request()->routeIs('expense.*')"
                        :heading="__('Expense Management')"
                    >
                        <flux:sidebar.item :href="route('expense.index')" :current="request()->routeIs('expense.index', 'expense.create', 'expense.show', 'expense.edit')" wire:navigate>
                            {{ __('Expenses') }}
                        </flux:sidebar.item>
                        @can('finance.categories.manage')
                            <flux:sidebar.item :href="route('expense.categories')" :current="request()->routeIs('expense.categories')" wire:navigate>
                                {{ __('Categories') }}
                            </flux:sidebar.item>
                        @endcan
                    </flux:navlist.group>
                    <flux:navlist.group
                        expandable
                        :expanded="request()->routeIs('projects.*')"
                        :heading="__('Project Management')"
                    >
                        <flux:sidebar.item :href="route('projects.index')" :current="request()->routeIs('projects.index')" wire:navigate>
                            {{ __('Projects') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item :href="route('projects.categories.index')" :current="request()->routeIs('projects.categories.*')" wire:navigate>
                            {{ __('Categories') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item :href="route('projects.trainees.index')" :current="request()->routeIs('projects.trainees.*')" wire:navigate>
                            {{ __('Trainee') }}
                        </flux:sidebar.item>
                    </flux:navlist.group>
                    <flux:navlist.group
                        expandable
                        :expanded="request()->routeIs('admin.page-sections.*', 'admin.team-members.*', 'admin.portfolio.*', 'admin.contact-settings.*', 'admin.seo.*', 'admin.analytics.*')"
                        :heading="__('Website CMS')"
                    >
                        <flux:sidebar.item :href="route('admin.page-sections.index')" :current="request()->routeIs('admin.page-sections.*')" wire:navigate>
                            {{ __('Page Sections') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item :href="route('admin.team-members.index')" :current="request()->routeIs('admin.team-members.*')" wire:navigate>
                            {{ __('Team Members') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item :href="route('admin.portfolio.index')" :current="request()->routeIs('admin.portfolio.*')" wire:navigate>
                            {{ __('Portfolio') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item :href="route('admin.contact-settings.edit')" :current="request()->routeIs('admin.contact-settings.*')" wire:navigate>
                            {{ __('Contact Settings') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item :href="route('admin.analytics.index')" :current="request()->routeIs('admin.analytics.*')" wire:navigate>
                            {{ __('Website Analytics') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item :href="route('admin.seo.settings')" :current="request()->routeIs('admin.seo.settings')" wire:navigate>
                            {{ __('SEO & Analytics') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item :href="route('admin.seo.pages')" :current="request()->routeIs('admin.seo.pages')" wire:navigate>
                            {{ __('Per-page SEO') }}
                        </flux:sidebar.item>
                    </flux:navlist.group>
                    <flux:navlist.group
                        expandable
                        :expanded="request()->routeIs('admin.courses.*', 'admin.course-categories.*')"
                        :heading="__('Course CMS')"
                    >
                        <flux:sidebar.item :href="route('admin.courses.index')" :current="request()->routeIs('admin.courses.*')" wire:navigate>
                            {{ __('Courses') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item :href="route('admin.course-categories.index')" :current="request()->routeIs('admin.course-categories.*')" wire:navigate>
                            {{ __('Categories') }}
                        </flux:sidebar.item>
                    </flux:navlist.group>
                    <flux:navlist.group
                        expandable
                        :expanded="request()->routeIs('admin.services.*', 'admin.service-categories.*')"
                        :heading="__('Service CMS')"
                    >
                        <flux:sidebar.item :href="route('admin.services.index')" :current="request()->routeIs('admin.services.*')" wire:navigate>
                            {{ __('Services') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item :href="route('admin.service-categories.index')" :current="request()->routeIs('admin.service-categories.*')" wire:navigate>
                            {{ __('Categories') }}
                        </flux:sidebar.item>
                    </flux:navlist.group>
                    @can('settings.manage')
                        <flux:sidebar.item icon="bell" :href="route('admin.notification-rules.index')" :current="request()->routeIs('admin.notification-rules.*')" wire:navigate>
                            {{ __('Notification Rules') }}
                        </flux:sidebar.item>
                    @endcan
                    @can('assets.manage')
                        <flux:sidebar.item icon="archive-box" :href="route('assets.index')" :current="request()->routeIs('assets.*')" wire:navigate>
                            {{ __('Asset Management') }}
                        </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <div class="hidden items-center justify-between gap-2 px-2 pb-1 lg:flex">
                <a href="{{ route('notifications.index') }}" class="text-xs font-medium text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">{{ __('Notifications') }}</a>
                @include('partials.notification-bell')
            </div>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @include('lara-izitoast::toast')
        <x-global-popup />
        @fluxScripts
    </body>
</html>
