<?php

namespace bestform\diaborg;


use Symfony\Component\HttpFoundation\Request;

class DiaborgValidator {

    /**
     * @param Request $request
     *
     * @return array - an Array of \bestform\diaborg\DiaborgValidationError
     */
    public function validateDataForNewEntry(Request $request)
    {
        $errors = array();
        $values = array();
        foreach(array("year", "month", "day", "hour", "minute") as $key){
            $values[$key] = $request->get($key);
            if(empty($values[$key])){
                $errors[] = new DiaborgValidationError(DiaborgValidationError::$LEVEL_ERROR, $key, "Field cannot be empty");
            } else {
                // TODO: don't force non-zero start but sanatize input if there is a zero
                if(0 === preg_match('/^[1-9][0-9]*$/', $values[$key])){
                    $errors[] = new DiaborgValidationError(DiaborgValidationError::$LEVEL_ERROR, $key, "Field must be only numbers and cannot start with a zero");
                }
            }
        }

        if(count($errors > 0)){
            return $errors;
        }

        // TODO: do some more checks like sane hour/minute format

        return $errors;

    }
}