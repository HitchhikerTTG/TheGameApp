<?php

// This is supposed to present errors to the world after validation

function display_form_errors($validation, $field){

	if ($validation->hasError($field)){
		return $validation->getError($field);
	}

}


