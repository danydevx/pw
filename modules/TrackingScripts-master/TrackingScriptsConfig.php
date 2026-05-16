<?php namespace ProcessWire;

class TrackingScriptsConfig extends ModuleConfig
{
    public function __construct()
    {
        $consentOptions = [
            'statistics' => $this->_('Statistics'),
            'marketing' => $this->_('Marketing'),
            'functional' => $this->_('Functional'),
            'external_media' => $this->_('External Media'),
        ];

        $positionOptions = [
            'head' => 'Head',
            'body' => 'Body',
        ];

        $this->add([
            // --- Google Analytics ---
            [
                'name' => 'ga_fieldset',
                'type' => 'fieldset',
                'label' => $this->_('Google Analytics'),
                'children' => [
                    [
                        'name' => 'ga_enabled',
                        'type' => 'checkbox',
                        'label' => $this->_('Enable Google Analytics'),
                        'columnWidth' => 25,
                    ],
                    [
                        'name' => 'ga_id',
                        'type' => 'text',
                        'label' => $this->_('Measurement ID'),
                        'description' => $this->_('Example: G-XXXXXXXXXX'),
                        'columnWidth' => 25,
                        'stripTags' => 1,
                    ],
                    [
                        'name' => 'ga_position',
                        'type' => 'select',
                        'label' => $this->_('Position'),
                        'options' => $positionOptions,
                        'value' => 'head',
                        'columnWidth' => 25,
                    ],
                    [
                        'name' => 'ga_consent_category',
                        'type' => 'select',
                        'label' => $this->_('PrivacyWire Category'),
                        'description' => $this->_('Used only if PrivacyWire integration is enabled.'),
                        'options' => $consentOptions,
                        'value' => 'statistics',
                        'columnWidth' => 25,
                    ],
                ],
            ],

            // --- Google Ads ---
            [
                'name' => 'gads_fieldset',
                'type' => 'fieldset',
                'label' => $this->_('Google Ads'),
                'children' => [
                    [
                        'name' => 'gads_enabled',
                        'type' => 'checkbox',
                        'label' => $this->_('Enable Google Ads'),
                        'columnWidth' => 25,
                    ],
                    [
                        'name' => 'gads_id',
                        'type' => 'text',
                        'label' => $this->_('Ads ID'),
                        'description' => $this->_('Example: AW-XXXXXXXXX'),
                        'columnWidth' => 25,
                        'stripTags' => 1,
                    ],
                    [
                        'name' => 'gads_position',
                        'type' => 'select',
                        'label' => $this->_('Position'),
                        'options' => $positionOptions,
                        'value' => 'head',
                        'columnWidth' => 25,
                    ],
                    [
                        'name' => 'gads_consent_category',
                        'type' => 'select',
                        'label' => $this->_('PrivacyWire Category'),
                        'description' => $this->_('Used only if PrivacyWire integration is enabled.'),
                        'options' => $consentOptions,
                        'value' => 'marketing',
                        'columnWidth' => 25,
                    ],
                ],
            ],

            // --- Facebook Pixel ---
            [
                'name' => 'fbpixel_fieldset',
                'type' => 'fieldset',
                'label' => $this->_('Facebook Pixel'),
                'children' => [
                    [
                        'name' => 'fbpixel_enabled',
                        'type' => 'checkbox',
                        'label' => $this->_('Enable Facebook Pixel'),
                        'columnWidth' => 25,
                    ],
                    [
                        'name' => 'fbpixel_id',
                        'type' => 'text',
                        'label' => $this->_('Pixel ID'),
                        'description' => $this->_('Example: 123456789012345'),
                        'columnWidth' => 25,
                        'stripTags' => 1,
                    ],
                    [
                        'name' => 'fbpixel_position',
                        'type' => 'select',
                        'label' => $this->_('Position'),
                        'options' => $positionOptions,
                        'value' => 'head',
                        'columnWidth' => 25,
                    ],
                    [
                        'name' => 'fbpixel_consent_category',
                        'type' => 'select',
                        'label' => $this->_('PrivacyWire Category'),
                        'description' => $this->_('Used only if PrivacyWire integration is enabled.'),
                        'options' => $consentOptions,
                        'value' => 'marketing',
                        'columnWidth' => 25,
                    ],
                ],
            ],

            // --- Custom Code ---
            [
                'name' => 'custom_fieldset',
                'type' => 'fieldset',
                'label' => $this->_('Custom Tracking Code'),
                'description' => $this->_('Paste any third-party tracking code. Content is encoded automatically to bypass firewall restrictions.'),
                'children' => [
                    [
                        'name' => 'custom_head',
                        'type' => 'textarea',
                        'label' => $this->_('Custom Code — Head'),
                        'description' => $this->_('Injected before </head>'),
                        'rows' => 6,
                        'columnWidth' => 50,
                    ],
                    [
                        'name' => 'custom_body',
                        'type' => 'textarea',
                        'label' => $this->_('Custom Code — Body'),
                        'description' => $this->_('Injected before </body>'),
                        'rows' => 6,
                        'columnWidth' => 50,
                    ],
                ],
            ],

            // --- PrivacyWire Integration ---
            [
                'name' => 'privacywire_fieldset',
                'type' => 'fieldset',
                'label' => $this->_('PrivacyWire Integration'),
                'children' => [
                    [
                        'name' => 'privacywire_enabled',
                        'type' => 'checkbox',
                        'label' => $this->_('Enable PrivacyWire integration'),
                        'description' => $this->_('If enabled, tracking codes will use data-category attributes so they only load after user consent.'),
                    ],
                ],
            ],

            // --- Robots & LLM ---
            [
                'name' => 'txtfiles_fieldset',
                'type' => 'fieldset',
                'label' => $this->_('Robots.txt & LLMs.txt'),
                'description' => $this->_('Content is written to the site root on save.'),
                'children' => [
                    [
                        'name' => 'robots_txt',
                        'type' => 'textarea',
                        'label' => 'robots.txt',
                        'description' => $this->_('Content for /robots.txt'),
                        'rows' => 10,
                        'columnWidth' => 50,
                    ],
                    [
                        'name' => 'llms_txt',
                        'type' => 'textarea',
                        'label' => 'llms.txt',
                        'description' => $this->_('Content for /llms.txt'),
                        'rows' => 10,
                        'columnWidth' => 50,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Decode base64 custom fields for display in admin.
     * Load current robots.txt/llms.txt from disk.
     */
    public function getInputfields(): InputfieldWrapper
    {
        $inputfields = parent::getInputfields();

        // Load current file contents into textareas if fields are empty
        $root = $this->wire('config')->paths->root;
        $files = [
            'robots_txt' => 'robots.txt',
            'llms_txt' => 'llms.txt',
        ];
        foreach ($files as $fieldName => $fileName) {
            $f = $inputfields->getChildByName($fieldName);
            if ($f && empty($f->value)) {
                $path = $root . $fileName;
                if (is_file($path)) {
                    $f->value = file_get_contents($path);
                }
            }
        }

        return $inputfields;
    }
}
