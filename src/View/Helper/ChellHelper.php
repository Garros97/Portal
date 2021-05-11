<?php
namespace App\View\Helper;

use Cake\View\Helper;
use Cake\View\View;

/**
 * Chell helper
 *
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \Cake\View\Helper\FormHelper $Form
 */
class ChellHelper extends Helper
{
    public $helpers = ['Html', 'Form'];

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];


    public function actionLink($icon, $title, $url, $options = [])
    {
        $html = '<li>';

        if (!isset($options['confirm'])) {
            $html .= $this->Html->link($this->Html->icon($icon) . '&nbsp;' . $title, $url, $options + ['escape' => false]);
        } else {
            $html .= $this->Form->postLink($this->Html->icon($icon) . '&nbsp;' . $title, $url, $options + ['escape' => false]);
        }
        $html .= '</li>';

        return $html;
    }
}
