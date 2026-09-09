<x-layouts::app :title="$category->name . ' — Expense Category'">
    @include('partials.category-detail', [
        'kind' => __('Expense'),
        'accent' => 'amber',
        'backRoute' => route('expense.categories'),
        'entryRouteName' => 'expense.show',
    ])
</x-layouts::app>
