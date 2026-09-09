<x-layouts::app :title="$category->name . ' — Income Category'">
    @include('partials.category-detail', [
        'kind' => __('Income'),
        'accent' => 'emerald',
        'backRoute' => route('income.categories'),
        'entryRouteName' => 'income.show',
    ])
</x-layouts::app>
