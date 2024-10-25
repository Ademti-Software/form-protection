<?php

namespace Ademti\FormProtection;

use Statamic\Facades\Form;
use Statamic\Forms\Tags;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;
use function __;

class ServiceProvider extends AddonServiceProvider
{
    /**
     * @return void
     */
    public function bootAddon()
    {
        $this->addSettingToFormForm();
        $this->addClassToProtectedForms();
        $this->addNoscriptToProtectedForms();
        $this->addJavascriptToProtectedForms();
    }

    /**
     * Add our settings to the "Edit form" form.
     *
     * @return void
     */
    private function addSettingToFormForm()
    {
        Form::appendConfigFields(
            '*',
            __('Form protection'),
            [
                'afp_dw_protection'        => [
                    'display'      => __('Protect with "David Walsh" javascript technique'),
                    'type'         => 'toggle',
                    'instructions' => __('Some instructions. FIXME'),
                ],
                'afp_dw_noscript_location' => [
                    'display' => __('Where to show "Javascript" requirement'),
                    'type'    => 'select',
                    'options' => [
                        [
                            'key'   => 'start',
                            'value' => __('Start of form'),
                        ],
                        [
                            'key'   => 'end',
                            'value' => __('End of form'),
                        ],
                        [
                            'key'   => 'hidden',
                            'value' => __('Hidden')
                        ],
                    ],
                    'default' => 'start',
                ]
            ]
        );
    }

    /**
     * @return void
     */
    private function addClassToProtectedForms()
    {
        $serviceProvider = $this;
        Tags::hook('attrs', function (array $payload, $next) use ($serviceProvider) {
            if ( ! $serviceProvider->isFormProtected($payload)) {
                return $next($payload);
            }
            if ( ! empty($payload['attrs']['class'])) {
                $payload['attrs']['class'] .= ' afp-form';
            } else {
                $payload['attrs']['class'] = 'afp-form';
            }

            return $next($payload);
        });
    }

    /**
     * @return void
     */
    private function addNoscriptToProtectedForms()
    {
        $serviceProvider = $this;
        Tags::hook('after-open', function (array $payload, $next) use ($serviceProvider) {
            if ( ! $serviceProvider->isFormProtected($payload)) {
                return $next($payload);
            }
            $location = $payload['data']['form_config']['afp_dw_noscript_location'] ?? 'start';
            if ($location !== 'start') {
                return $next($payload);
            }
            $payload['html'] .= <<<HTML
<noscript><div class="afp-noscript-container"><p><strong>Please enable JavaScript in your browser to complete this form.</strong></p></div></noscript>
HTML;

            return $next($payload);
        });
        Tags::hook('before-close', function (array $payload, $next) use ($serviceProvider) {
            if ( ! $serviceProvider->isFormProtected($payload)) {
                return $next($payload);
            }
            $location = $payload['data']['form_config']['afp_dw_noscript_location'] ?? 'start';
            if ($location !== 'end') {
                return $next($payload);
            }
            $payload['html'] .= <<<HTML
<noscript><div class="afp-noscript-container"><p><strong>Please enable JavaScript in your browser to complete this form.</strong></p></div></noscript>
HTML;

            return $next($payload);
        });
    }

    /**
     * @return void
     */
    private function addJavascriptToProtectedForms()
    {
        $serviceProvider = $this;
        Tags::hook('before-close', function (array $payload, $next) use ($serviceProvider) {
            if ( ! $serviceProvider->isFormProtected($payload)) {
                return $next($payload);
            }
            $scripts = Statamic::availableScripts(request());
            if (isset($scripts['afp_dw_script'])) {
                return $next($payload);
            }
            $payload['html'] .= '<script>' . file_get_contents(__DIR__ . '/../js/afp_dw.js') . '</script>';

            return $next($payload);
        });
    }

    /**
     * @param  array  $payload
     *
     * @return bool
     */
    public function isFormProtected(array $payload)
    {
        return isset($payload['data']['form_config']['afp_dw_protection']) &&
               $payload['data']['form_config']['afp_dw_protection'];
    }
}
