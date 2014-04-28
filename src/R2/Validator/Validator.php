<?php

namespace R2\Validator;

use R2\Translation\TranslatorInterface;

class Validator
{
    protected $rules;
    protected $groups;
    protected $translator;
    protected $data;

    /**
     * Constructor.
     *
     * @param array $rules
     * @param array $groups
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(array $rules, array $groups, TranslatorInterface $translator)
    {
        $this->rules      = $rules;
        $this->groups     = $groups;
        $this->translator = $translator;
    }

    /**
     * Check entity and return array of error messages (empty array ig no error).
     * @param  object|array         $entity
     * @param  string|string[]|NULL $group
     * @return array
     */
    public function validate($entity, $group = null)
    {
        $errors = [];
        if (null === $group && is_object($entity)) {
            $class = get_class($entity);
            while ($class) {
                if (array_key_exists($class, $this->groups)) {
                    $group = $class;
                    break;
                }
                $class = get_parent_class($class);
            }
        }
        if ($group) {
            $this->data = (array) $entity;
            // Find property name in each of validation group
            foreach ((array) $group as $groupName) {
                if (empty($this->groups[$groupName])) {
                    continue;
                }
                $rules = $this->groups[$groupName];
                foreach ($this->data as $name => $value) {
                    //
                    if (isset($rules[$name]) && !$this->checkRules($rules[$name], $value)) {
                        $message = isset($rules[$name]['message'])
                            ? $rules[$name]['message']
                            : 'Wrong '.$name;
                        $errors[$name] = $this->translator->t($message, 'validators');
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Check one value against its rules list.
     * Every rule is either protected "check*" method or config-defined regexp.
     * @param  array                     $rules
     * @param  mixed                     $value
     * @return boolean
     * @throws \InvalidArgumentException
     */
    private function checkRules($rules, $value)
    {
        if (empty($rules)) {
            return true;
        }
        // Avoid to use error message text as rule
        unset($rules['message']);
        foreach ($rules as $ruleName => $ruleData) {
            // Check custom rules first
            if (array_key_exists($ruleName, $this->rules)) {
                if (!$this->checkRegex($value, $this->rules[$ruleName])) {
                    return false;
                }
            } elseif (method_exists($this, $methodName = 'check'.$ruleName)) {
                if (!$this->$methodName($value, $ruleData)) {
                    return false;
                }
            } else {
                throw new \InvalidArgumentException("Wrong validation rule \"{$ruleName}\"");
            }
        }

        return true;
    }

    protected function checkNotBlank($value, $ruleData)
    {
        return trim($value) !== '';
    }

    protected function checkMinLength($value, $ruleData)
    {
        return mb_strlen($value) >= $ruleData;
    }

    protected function checkMaxLength($value, $ruleData)
    {
        return mb_strlen($value) <= $ruleData;
    }

    protected function checkRegex($value, $ruleData)
    {
        return preg_match($ruleData, $value) === 1;
    }

    protected function checkEmail($value, $ruleData)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    protected function checkTheSame($value, $ruleData)
    {
        return $value === $this->data[$ruleData];
    }

    protected function checkUrl($value, $ruleData)
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
}
