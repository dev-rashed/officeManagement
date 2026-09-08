<?php

/**
 * The catalogue of things people can be notified about.
 *
 * Adding an event is two steps: add it here, then call
 * NotificationDispatcher::dispatch('your.event', $record) where it happens.
 * The rules screen picks the list up from this file automatically.
 */
return [

    'events' => [

        'income.created' => [
            'label' => 'New income entry added',
            'description' => 'Someone recorded a new income entry.',
            'group' => 'Finance',
            'icon' => 'arrow-down-circle',
        ],

        'expense.created' => [
            'label' => 'New expense entry added',
            'description' => 'Someone recorded a new expense entry.',
            'group' => 'Finance',
            'icon' => 'arrow-up-circle',
        ],

    ],

    /**
     * Channels a rule can use.
     *
     * Database is written synchronously so the bell updates immediately with no
     * queue worker running. Mail depends on MAIL_MAILER actually being
     * configured -- with the default `log` mailer, mail is written to
     * storage/logs and nobody receives anything.
     */
    'channels' => [
        'database' => 'In-app (bell)',
        'mail' => 'Email',
    ],

];
