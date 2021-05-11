<?php
namespace App\View\Widget;

use Cake\View\Form\ContextInterface;
use Cake\View\StringTemplate;
use Cake\View\View;
use Cake\View\Widget\WidgetInterface;

class DatePickerWidget implements WidgetInterface
{
    /** @var  StringTemplate */
    protected $_templates;

    /** @var  View */
    protected $_view;

    public function __construct(StringTemplate $templates, View $view)
    {
        $this->_templates = $templates;
        $this->_view = $view;
    }

    public function render(array $data, ContextInterface $context)
    {
        $data += [
            'name' => '',
            'val' => null,
            'showTime' => false,
        ];

        $showTime = $data['showTime'];
        unset($data['showTime']);

        $data += ['data-provide' => $showTime ? 'datetimepicker' : 'datepicker'];

        $date = $data['val']; //might be either a DateTime object (when fresh from DB), or a string (user input)
        unset($data['val']);
        if ($date instanceof \DateTime) {
            $data['value'] = $date->format($showTime ? 'd.m.Y H:m' : 'd.m.Y');
        } else{
            $data['value'] = $date;
        }

        $this->_view->assign('has-datepicker', true);

        return $this->_templates->format('input', [
            'name' => $data['name'],
            'type' => 'text',
            'attrs' => $this->_templates->formatAttributes($data, ['name'])
        ]);
    }

    public function secureFields(array $data)
    {
        return [$data['name']];
    }
}