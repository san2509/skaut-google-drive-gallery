<?php
namespace Sgdg\Frontend;

require_once('Option.php');

class BooleanOption extends Option
{
	public function __construct($name, $defaultValue, $section, $title)
	{
		parent::__construct($name, ($defaultValue ? '1' : '0'), $section, $title);
	}

	public function register()
	{
		register_setting('sgdg', $this->name, ['type' => 'boolean', 'sanitize_callback' => [$this, 'sanitize']]);
	}

	public function sanitize($value)
	{
		if(isset($value) && ($value === '1' || $value === 1))
		{
			return 1;
		}
		return 0;
	}

	public function html()
	{
		echo('<input type="checkbox" name="' . $this->name . '" value="1"');
		checked(get_option($this->name, $this->defaultValue), '1');
		echo('>');
	}

	public function get($defaultValue = null)
	{
		return (parent::get($defaultValue) === '1' ? 'true' : 'false');
	}

	public function get_inverted($defaultValue = null)
	{
		return (parent::get($defaultValue) === '1' ? 'false' : 'true');
	}
}
