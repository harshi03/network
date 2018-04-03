<?php

namespace App\Validation;


use InvalidArgumentException;
use Respect\Validation\Validator as Respect;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Rules\AllOf;
 
class Validator
{
	protected $errors;

	public function validate($request, array $rules)
	{
		foreach ($rules as $key => $rule) {
			try {
				$rule->setName($key)->assert($request->getParam($key));
			} catch (NestedValidationException $e) {
				$this->errors[] = array('message' => json_encode($e->getMessages()), 'field' => $key);
			}
			
		}

		return $this;
		
	}  

	public function isValid()
	{
		return !empty($this->errors);
	}

	public function getErrors()
    {
        if (!empty($key)) {
            if (!empty($group)) {
                return $this->errors[$group][$key] ?? [];
            }
            return $this->errors[$key] ?? [];
        }
        return $this->errors;
    }
}
 
?>