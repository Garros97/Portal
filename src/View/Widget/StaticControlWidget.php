<?php
namespace App\View\Widget;

use Cake\View\Form\ContextInterface;
use Cake\View\StringTemplate;
use Cake\View\Widget\WidgetInterface;

class StaticControlWidget implements WidgetInterface
{
    /** @var StringTemplate */
    protected $_templates;

    public function __construct(StringTemplate $templates)
    {
        $this->_templates = $templates;
    }

    public function render(array $data, ContextInterface $context)
    {
        $data += [
            'name' => '',
            'val' => '',
            'escape' => true,
        ];

        $val = $data['val'];

        return $this->_templates->format('staticControl', [
            'value' => $data['escape'] ? h($val) : $val
        ]);
    }

    public function secureFields(array $data)
    {
        return [$data['name']];
    }
}