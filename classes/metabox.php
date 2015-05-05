<?php

namespace medias;

class metabox {

	public function startTable() {
		echo '<table class="form-table">';
	}

	public function endTable() {
		echo '</table>';
	}

	public function beginRow() {
		echo '<tr>';
	}

	public function endRow() {
		echo '</tr>';
	}

	public function label($for, $text) {
		echo '<label for="' . $for . '">' . $text . '</label>';
	}

	public function input($type, $name, $value, $classes = '', $checked = false) {
		$output = '<input type="' . $type . '" name="' . $name . '" id="' . $name . '" value="' . $value . '" class="' . $classes .'"';
		if($type == 'checkbox' && $checked === true) {
			$output .= ' checked';
		}
		$output .= '>';
		echo $output;
	}

	public function text($content) {
		echo $content;
	}

}
