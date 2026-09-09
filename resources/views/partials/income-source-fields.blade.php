{{--
    Where the income came from.

    Expects: $projects, $contributors, and optionally $income when editing.
--}}
@php
    $current = old('source_type', $income->source_type ?? \App\Models\IncomeEntry::SOURCE_OTHER);
    $currentProject = old('project_id', $income->project_id ?? null);
    $currentContributor = old('contributor_id', $income->contributor_id ?? auth()->id());
@endphp

<div class="lg:col-span-2">
    <span class="is-label">{{ __('Where did this money come from?') }}</span>

    <div class="is-choices" data-income-source>
        <label class="is-choice {{ $current === 'project' ? 'is-on' : '' }}">
            <input type="radio" name="source_type" value="project" @checked($current === 'project')>
            <span class="is-choice-body">
                <span class="is-choice-title">{{ __('A project') }}</span>
                <span class="is-choice-note">{{ __('Course fees, training revenue or a grant tied to a project.') }}</span>
            </span>
        </label>

        <label class="is-choice {{ $current === 'contribution' ? 'is-on' : '' }}">
            <input type="radio" name="source_type" value="contribution" @checked($current === 'contribution')>
            <span class="is-choice-body">
                <span class="is-choice-title">{{ __('Personal contribution') }}</span>
                <span class="is-choice-note">{{ __('Money put in by the Chairman, an MD or a Director.') }}</span>
            </span>
        </label>

        <label class="is-choice {{ $current === 'other' ? 'is-on' : '' }}">
            <input type="radio" name="source_type" value="other" @checked($current === 'other')>
            <span class="is-choice-body">
                <span class="is-choice-title">{{ __('Other') }}</span>
                <span class="is-choice-note">{{ __('Anything not tied to a project or a person.') }}</span>
            </span>
        </label>
    </div>

    @error('source_type') <p class="is-err">{{ $message }}</p> @enderror

    <div class="is-extra {{ $current === 'project' ? '' : 'hidden' }}" data-source-project>
        <label for="project_id" class="is-label">{{ __('Which project?') }}</label>
        <select id="project_id" name="project_id" class="is-input">
            <option value="">{{ __('Select a project') }}</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}" @selected((int) $currentProject === $project->id)>
                    {{ $project->title }}{{ $project->status ? ' — ' . ucfirst(str_replace('_', ' ', $project->status)) : '' }}
                </option>
            @endforeach
        </select>
        @error('project_id') <p class="is-err">{{ $message }}</p> @enderror
    </div>

    <div class="is-extra {{ $current === 'contribution' ? '' : 'hidden' }}" data-source-contribution>
        <label for="contributor_id" class="is-label">{{ __('Who contributed it?') }}</label>
        <select id="contributor_id" name="contributor_id" class="is-input">
            <option value="">{{ __('Select a person') }}</option>
            @foreach ($contributors as $person)
                <option value="{{ $person->id }}" @selected((int) $currentContributor === $person->id)>
                    {{ $person->name }}{{ $person->role ? ' — ' . ucwords(str_replace('_', ' ', $person->role)) : '' }}
                </option>
            @endforeach
        </select>
        <p class="is-hint">{{ __('It appears on their dashboard as a contribution they have made.') }}</p>
        @error('contributor_id') <p class="is-err">{{ $message }}</p> @enderror
    </div>
</div>

@once
    <style>
        .is-label { color:#334155; display:block; font-size:.82rem; font-weight:600; margin-bottom:.45rem; }
        .is-choices { display:grid; gap:.6rem; grid-template-columns:1fr; }
        @media (min-width:768px){ .is-choices{grid-template-columns:repeat(3,minmax(0,1fr))} }
        .is-choice { align-items:flex-start; background:#fff; border:1px solid #e2e8f0; border-radius:11px; cursor:pointer; display:flex; gap:.55rem; padding:.7rem .8rem; transition:border-color .15s ease, background .15s ease; }
        .is-choice:hover { border-color:#cbd5e1; }
        .is-choice.is-on { background:#ecfdf5; border-color:#6ee7b7; box-shadow:0 0 0 2px rgba(5,150,105,.12); }
        .is-choice input { accent-color:#059669; margin-top:.15rem; }
        .is-choice-body { display:flex; flex-direction:column; gap:.15rem; }
        .is-choice-title { color:#0f172a; font-size:.8rem; font-weight:600; }
        .is-choice-note { color:#64748b; font-size:.7rem; line-height:1.35; }
        .is-extra { margin-top:.7rem; }
        .is-extra.hidden { display:none; }
        .is-input { background:#fff; border:1px solid #e2e8f0; border-radius:9px; font-size:.82rem; padding:.5rem .65rem; width:100%; }
        .is-input:focus { border-color:#059669; box-shadow:0 0 0 2px rgba(5,150,105,.16); outline:none; }
        .is-hint { color:#94a3b8; font-size:.7rem; margin-top:.35rem; }
        .is-err { color:#e11d48; font-size:.72rem; margin-top:.35rem; }
        .dark .is-label, .dark .is-choice-title { color:#e4e4e7; }
        .dark .is-choice, .dark .is-input { background:#18181b; border-color:#3f3f46; color:#fafafa; }
        .dark .is-choice.is-on { background:#0d2a20; border-color:#047857; }
    </style>

    <script>
        (() => {
            const init = () => {
                document.querySelectorAll('[data-income-source]').forEach((group) => {
                    if (group.dataset.bound === '1') return;
                    group.dataset.bound = '1';

                    const holder = group.parentElement;
                    const projectBox = holder.querySelector('[data-source-project]');
                    const contribBox = holder.querySelector('[data-source-contribution]');

                    group.addEventListener('change', () => {
                        group.querySelectorAll('.is-choice').forEach((c) => {
                            c.classList.toggle('is-on', c.querySelector('input').checked);
                        });

                        const value = group.querySelector('input:checked')?.value;
                        projectBox?.classList.toggle('hidden', value !== 'project');
                        contribBox?.classList.toggle('hidden', value !== 'contribution');
                    });
                });
            };

            document.addEventListener('DOMContentLoaded', init);
            document.addEventListener('livewire:navigated', init);
            init();
        })();
    </script>
@endonce
