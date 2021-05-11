<?php
namespace App\Model\Entity;

abstract class CustomFieldType //this is an "enum"
{
    const Text = 1;
    const Checkbox = 2;
    const AgbCheckbox = 3; //must be checked
    const Number = 4;
    const Dropdown = 5;
}