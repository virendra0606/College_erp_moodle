<?php
namespace theme_erp\output;

defined('MOODLE_INTERNAL') || die();

class theme_settings {

    protected $theme;

    public function __construct() {
        $this->theme = \theme_config::load('erp');
    }

    public function get(string $key, string $default = ''): string {
        return (string)($this->theme->settings->$key ?? $default);
    }

    public function erpurl(): string {
        return rtrim($this->get('erpurl'), '/');
    }

    public function collegename(): string {
        return $this->get('collegename', 'College ERP');
    }
}
