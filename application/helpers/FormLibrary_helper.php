<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
function BuildInput($type,$name,$attribute = array(),$value = '',$values = array())
{
	$empty = false;
	if(is_array($attribute))
	{
		$empty = empty($attribute);
		$attribute ['name'] = $name;
		$attribute ['value'] = set_value($name,$value);
	}
	else
		$empty = true;
	switch($type)
	{
		case 'hidden' : 
			if(is_array($attribute))
            {
                unset($attribute['name']);
                unset($attribute['value']);
                $attribute[$name] = $value;
				echo form_hidden($attribute);
            }
			else
				echo '<div class="error">Failed to build Hidden element please check your code.</div>';
			break;
		case 'password' : 
			if(is_array($attribute))
			{
				unset($attribute['value']);
				echo form_password($attribute);
			}
			else
				echo '<div class="error">Failed to build Password element please check your code.</div>';
			break;
		case 'file' :
			if(is_array($attribute))
				echo form_upload($attribute);
			else
				echo '<div class="error">Failed to build File element please check your code.</div>';
			break;
		case 'textarea' : 
			if(is_array($attribute))
				echo form_textarea($attribute);
			else
				echo '<div class="error">Failed to build Textare element please check your code.</div>';
			break;
		case 'dropdown' : 
			if($empty)
            { 
                if(is_array($attribute))
                    $attribute = '';

				echo form_dropdown($name,$values,set_value($name,$value),$attribute);
            }
			else
				echo '<div class="error">Failed to build Dropdown element please check your code.</div>';
			break;
		case 'multiselect' : 
            if($empty)
            {
                if(is_array($attribute))
                    $attribute = '';
				echo form_multiselect($name,$values,set_value($name,$value),$attribute);
            }
			else
				echo '<div class="error">Failed to build Multi Select element please check your code.</div>';
			break;
		case 'checkbox' : 
			if(is_array($attribute))
				echo form_checkbox($attribute);
			else
				echo '<div class="error">Failed to build Checkbox element please check your code.</div>';
			break;
		case 'radio' : 
			// if(is_array($attribute))
			// 	echo form_radio($attribute);
			// else
				echo '<div class="error">Failed to build Radio element please check your code.</div>';
			break;
		case 'submit' : 
			if(is_array($attribute))
				echo form_submit($attribute);
			else
				echo '<div class="error">Failed to build Submit element please check your code.</div>';
			break;
		case 'reset' : 
			if(is_array($attribute))
				echo form_reset($attribute);
			else
				echo '<div class="error">Failed to build Reset element please check your code.</div>';
			break;
		case 'button' : 
			if(is_array($attribute))
				echo form_button($attribute);
			else
				echo '<div class="error">Failed to build Button element please check your code.</div>';
			break;
		case 'label' : 

			if(is_array($attribute))
			{
				unset($attribute['name']);
				unset($attribute['value']);
				echo form_label($value, $name, $attribute);
			}
			else
				echo '<div class="error">Failed to build Submit element please check your code.</div>';
			break;
		case 'text' : 
		default : 
			if(is_array($attribute))
				echo form_input($attribute);
			else
				echo '<div class="error">Failed to build Text element please check your code.</div>';
			break;
	};
	echo form_error($name, '<div class="error">', '</div>');
}

