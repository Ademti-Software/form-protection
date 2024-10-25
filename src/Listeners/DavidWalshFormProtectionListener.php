<?php

namespace Ademti\FormProtection\Listeners;

use Statamic\Events\FormSubmitted;
use function request;

class DavidWalshFormProtectionListener
{
    /**
     * Handle the event.
     */
    public function __invoke(FormSubmitted $event): bool
    {
        $form = $event->submission->form();

        // Ignore anything that isn't protected.
        if ( ! $form->afp_dw_protection) {
            return true;
        }

        // Check for the presence of our field. The submission is valid if we find it.
        if (request()->post('as_dw_submission') === 'asfp') {
            return true;
        }

        // If it's missing, reject the submission.
        return false;
    }
}
