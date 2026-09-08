<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Who gets told about what.
     *
     * A rule points an event at either a role (everyone holding it, including
     * people hired later) or one specific user.
     */
    public function up(): void
    {
        Schema::create('notification_rules', function (Blueprint $table): void {
            $table->id();

            $table->string('event', 60);
            $table->string('recipient_type', 10);   // role | user
            $table->string('recipient_value', 100); // role name, or a user id

            $table->boolean('via_database')->default(true);
            $table->boolean('via_mail')->default(false);
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['event', 'recipient_type', 'recipient_value'], 'notification_rules_unique');
            $table->index(['event', 'is_active']);
        });

        // Sensible starting point: the people who sign the money off hear about
        // new entries. In-app only -- mail is opt-in per rule.
        $now = now();
        $rows = [];

        foreach (['income.created', 'expense.created'] as $event) {
            foreach (['admin', 'accountant', 'managing_director'] as $role) {
                $rows[] = [
                    'event' => $event,
                    'recipient_type' => 'role',
                    'recipient_value' => $role,
                    'via_database' => true,
                    'via_mail' => false,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('notification_rules')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_rules');
    }
};
